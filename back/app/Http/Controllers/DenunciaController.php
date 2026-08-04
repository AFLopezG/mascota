<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDenunciaRequest;
use App\Http\Requests\UpdateDenunciaRequest;
use App\Models\Denuncia;
use App\Models\DenunciaLog;
use App\Models\DenunciaTipo;
use App\Models\Persona;
use App\Models\Proceso;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DenunciaController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(
            Denuncia::with([
                'persona',
                'mascota.raza.especie',
                'mascota.categoria',
                'raza.especie',
                'user',
                'tipos',
                'logs.proceso',
                'logs.user',
            ])->orderByDesc('fec_denuncia')->get()
        );
    }

    public function store(StoreDenunciaRequest $request): JsonResponse
    {
        $data = $request->validated();

        return DB::transaction(function () use ($data, $request) {
            $persona = $this->resolvePersona($data);
            $data['persona_id'] = $persona->id;

            $denuncia = new Denuncia();
            $denuncia->fill($this->mapDenunciaData($data));
            $denuncia->numero = ((int) Denuncia::max('numero')) + 1;
            $denuncia->user_id = $request->user()->id;
            $denuncia->save();

            $tipoIds = $data['denuncia_tipo_ids'];
            $denuncia->tipos()->sync($tipoIds);

            $firstProcess = Proceso::orderBy('orden')->firstOrFail();
            $principalTipoId = (int) $tipoIds[0];
            $denuncia->estado = $firstProcess->descripcion;
            $denuncia->save();

            DenunciaLog::create([
                'fechaHora' => now(),
                'actividad' => 'DENUNCIA REGISTRADA',
                'resultado' => $firstProcess->descripcion,
                'obser' => 'Registro inicial de denuncia',
                'denuncia_id' => $denuncia->id,
                'user_id' => $request->user()->id,
                'denuncia_tipo_id' => $principalTipoId,
                'proceso_id' => $firstProcess->id,
            ]);

            return response()->json([
                'message' => 'Denuncia registrada.',
                'data' => $this->loadDenuncia($denuncia->fresh()),
            ], 201);
        });
    }

    public function show(Denuncia $denuncia): JsonResponse
    {
        return response()->json([
            'data' => $this->loadDenuncia($denuncia),
        ]);
    }

    public function update(UpdateDenunciaRequest $request, Denuncia $denuncia): JsonResponse
    {
        $data = $request->validated();
        if (!array_key_exists('estado', $data) || trim((string) $data['estado']) === '') {
            $data['estado'] = $denuncia->estado;
        }

        return DB::transaction(function () use ($data, $denuncia) {
            $persona = $this->resolvePersona($data);
            $data['persona_id'] = $persona->id;

            $denuncia->fill($this->mapDenunciaData($data));
            $denuncia->save();
            $denuncia->tipos()->sync($data['denuncia_tipo_ids']);

            return response()->json([
                'message' => 'Denuncia actualizada.',
                'data' => $this->loadDenuncia($denuncia->fresh()),
            ]);
        });
    }

    public function destroy(Denuncia $denuncia): JsonResponse
    {
        $denuncia->delete();

        return response()->json([
            'message' => 'Denuncia eliminada.',
        ]);
    }

    public function storeLog(Request $request, Denuncia $denuncia): JsonResponse
    {
        $data = $request->validate([
            'proceso_id' => ['required', 'integer', 'exists:procesos,id'],
            'actividad' => ['required', 'string', 'max:255'],
            'resultado' => ['required', 'string', 'max:255'],
            'obser' => ['nullable', 'string'],
        ]);

        $selectedProcess = Proceso::findOrFail($data['proceso_id']);
        $currentOrder = $this->currentProcessOrder($denuncia);
        $maxOrder = (int) Proceso::max('orden');

        if ($currentOrder >= $maxOrder) {
            throw ValidationException::withMessages([
                'proceso_id' => 'La denuncia ya llego al ultimo proceso.',
            ]);
        }

        if ((int) $selectedProcess->orden <= $currentOrder) {
            throw ValidationException::withMessages([
                'proceso_id' => 'Debes seleccionar un proceso posterior al actual.',
            ]);
        }

        DenunciaLog::create([
            'fechaHora' => now(),
            'actividad' => mb_strtoupper(trim($data['actividad'])),
            'resultado' => mb_strtoupper(trim($data['resultado'])),
            'obser' => $data['obser'] ? trim($data['obser']) : null,
            'denuncia_id' => $denuncia->id,
            'user_id' => $request->user()->id,
            'proceso_id' => $selectedProcess->id,
        ]);

        $denuncia->estado = $selectedProcess->descripcion;
        $denuncia->save();

        return response()->json([
            'message' => 'Log registrado.',
            'data' => $this->loadDenuncia($denuncia->fresh()),
        ], 201);
    }

    private function mapDenunciaData(array $data): array
    {
        $tipoIds = collect($data['denuncia_tipo_ids'] ?? [])->map(fn ($id) => (int) $id)->values()->all();
        $requiresBiteFields = $this->requiresBiteFields($tipoIds);

        $biteFields = $requiresBiteFields
            ? [
                'nom_afectado' => $this->normalizeOptionalText($data['nom_afectado'] ?? null, true),
                'edad' => $this->normalizeOptionalText($data['edad'] ?? null, true),
                'telefono' => $this->normalizeOptionalText($data['telefono'] ?? null, true),
                'dir_inicidente' => $this->normalizeOptionalText($data['dir_inicidente'] ?? null, true),
                'tipo_lesion' => $this->normalizeOptionalText($data['tipo_lesion'] ?? null, true),
                'dias_obser' => $this->normalizeOptionalText($data['dias_obser'] ?? null, true),
                'resultado' => $this->normalizeOptionalText($data['resultado'] ?? null, true),
                'obs' => $this->normalizeOptionalText($data['obs'] ?? null, true),
            ]
            : [
                'nom_afectado' => '',
                'edad' => '',
                'telefono' => '',
                'dir_inicidente' => '',
                'tipo_lesion' => '',
                'dias_obser' => '',
                'resultado' => '',
                'obs' => '',
            ];

        if ($requiresBiteFields) {
            $missing = collect([
                'nom_afectado' => $data['nom_afectado'] ?? null,
                'edad' => $data['edad'] ?? null,
                'telefono' => $data['telefono'] ?? null,
                'dir_inicidente' => $data['dir_inicidente'] ?? null,
                'tipo_lesion' => $data['tipo_lesion'] ?? null,
                'dias_obser' => $data['dias_obser'] ?? null,
                'resultado' => $data['resultado'] ?? null,
                'obs' => $data['obs'] ?? null,
            ])->filter(fn ($value) => trim((string) $value) === '');

            if ($missing->isNotEmpty()) {
                throw ValidationException::withMessages(
                    $missing->mapWithKeys(fn ($_value, $key) => [$key => 'Este campo es requerido cuando existe el tipo de mordedura.'])->all()
                );
            }
        }

        return [
            'fec_denuncia' => $data['fec_denuncia'],
            'direccion' => $this->normalizeOptionalText($data['direccion'] ?? null),
            'descripcion' => $this->normalizeOptionalText($data['descripcion'] ?? null, false),
            'zona' => $this->normalizeOptionalText($data['zona'] ?? null),
            'color' => $this->normalizeOptionalText($data['color'] ?? null),
            'tamanio' => $this->normalizeOptionalText($data['tamanio'] ?? null),
            'estado' => $this->normalizeOptionalText($data['estado'] ?? null),
            'observacion' => $this->normalizeOptionalText($data['observacion'] ?? null, false),
            'fiscalia' => $this->normalizeOptionalText($data['fiscalia'] ?? null, false),
            'nom_afectado' => $biteFields['nom_afectado'],
            'edad' => $biteFields['edad'],
            'telefono' => $biteFields['telefono'],
            'dir_inicidente' => $biteFields['dir_inicidente'],
            'tipo_lesion' => $biteFields['tipo_lesion'],
            'dias_obser' => $biteFields['dias_obser'],
            'resultado' => $biteFields['resultado'],
            'obs' => $biteFields['obs'],
            'raza_id' => (int) $data['raza_id'],
            'persona_id' => (int) $data['persona_id'],
            'mascota_id' => array_key_exists('mascota_id', $data) && $data['mascota_id'] !== null && $data['mascota_id'] !== ''
                ? (int) $data['mascota_id']
                : null,
        ];
    }

    private function loadDenuncia(Denuncia $denuncia): Denuncia
    {
        return $denuncia->load([
            'persona',
            'mascota.raza.especie',
            'mascota.categoria',
            'raza.especie',
            'user',
            'tipos',
            'logs.proceso',
            'logs.user',
        ]);
    }

    private function currentProcessOrder(Denuncia $denuncia): int
    {
        $lastLog = $denuncia->logs()->with('proceso')->get()->sortByDesc(fn ($log) => $log->proceso?->orden ?? 0)->first();

        return (int) ($lastLog?->proceso?->orden ?? 0);
    }

    private function requiresBiteFields(array $tipoIds): bool
    {
        if (empty($tipoIds)) {
            return false;
        }

        return DenunciaTipo::query()
            ->whereIn('id', $tipoIds)
            ->get()
            ->contains(function (DenunciaTipo $tipo) {
                return mb_strtoupper(trim($tipo->nombre)) === 'MORDEDURA';
            });
    }

    private function resolvePersona(array $data): Persona
    {
        if (!empty($data['persona_id'])) {
            $persona = Persona::findOrFail((int) $data['persona_id']);
            $this->fillPersonaFromDenunciaData($persona, $data);
            $persona->save();

            return $persona;
        }

        $cinit = $this->normalizeOptionalText($data['persona_cinit'] ?? null);
        $complemento = $this->normalizeOptionalText($data['persona_complemento'] ?? null);
        $nombre = $this->normalizeOptionalText($data['persona_nombre'] ?? '');

        $query = Persona::query()->where('cinit', $cinit);
        if ($complemento !== null && $complemento !== '') {
            $query->where('complemento', $complemento);
        }

        $persona = $query->first();
        if ($persona) {
            $this->fillPersonaFromDenunciaData($persona, $data);
            $persona->save();

            return $persona;
        }

        $persona = new Persona();
        $persona->cinit = $cinit;
        $persona->complemento = $complemento;
        $persona->nombre = $nombre;
        $this->fillPersonaFromDenunciaData($persona, $data);
        $persona->save();

        return $persona;
    }

    private function fillPersonaFromDenunciaData(Persona $persona, array $data): void
    {
        $persona->cinit = $this->normalizeOptionalText($data['persona_cinit'] ?? null) ?: $persona->cinit;
        $persona->complemento = $this->normalizeOptionalText($data['persona_complemento'] ?? null) ?: null;
        $persona->nombre = $this->normalizeOptionalText($data['persona_nombre'] ?? null) ?: $persona->nombre;
        $persona->paterno = $this->normalizeOptionalText($data['persona_paterno'] ?? null) ?: null;
        $persona->materno = $this->normalizeOptionalText($data['persona_materno'] ?? null) ?: null;
        $persona->telefono = $this->normalizeOptionalText($data['persona_telefono'] ?? null) ?: null;
        $persona->emergencia = $this->normalizeOptionalText($data['persona_emergencia'] ?? null) ?: null;
    }

    private function normalizeOptionalText(?string $value, bool $uppercase = true): string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return '';
        }

        return $uppercase ? mb_strtoupper($value) : $value;
    }
}
