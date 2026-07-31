<?php

namespace App\Http\Controllers;

use App\Models\Campania;
use App\Models\Categoria;
use App\Http\Requests\StoreMascotaRequest;
use App\Http\Requests\UpdateMascotaRequest;
use App\Models\Especie;
use App\Models\Mascota;
use App\Models\Persona;
use App\Models\Raza;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class MascotaController extends Controller
{
    public function index(Request $request)
    {
        $query = Mascota::query()->with(['persona', 'vacunas', 'categoria', 'raza.especie', 'campania']);

        if ($request->filled('persona_id')) {
            $query->where('persona_id', $request->integer('persona_id'));
        }

        return $query->orderBy('nombre')->get();
    }

    public function store(StoreMascotaRequest $request)
    {
        $data = $request->validated();
        $persona = Persona::findOrFail($data['persona_id']);

        $mascota = new Mascota();
        $mascota->codigo = $this->resolveCodigoForStore($data);
        $this->fillMascota($mascota, $data, $request);
        $mascota->persona_id = $persona->id;
        $mascota->save();

        return response()->json([
            'message' => 'Mascota registrada.',
            'data' => $mascota->fresh(['persona', 'vacunas', 'categoria', 'raza.especie', 'campania']),
        ], 201);
    }

    public function show(Mascota $mascota)
    {
        return response()->json([
            'data' => $mascota->load(['persona', 'vacunas', 'categoria', 'raza.especie', 'campania']),
        ]);
    }

    public function update(UpdateMascotaRequest $request, Mascota $mascota)
    {
        $data = $request->validated();
        $mascota->codigo = $this->resolveCodigoForUpdate($mascota, $data);
        $this->fillMascota($mascota, $data, $request);
        $mascota->persona_id = $data['persona_id'];
        $mascota->save();

        return response()->json([
            'message' => 'Mascota actualizada.',
            'data' => $mascota->fresh(['persona', 'vacunas', 'categoria', 'raza.especie', 'campania']),
        ]);
    }

    public function destroy(Mascota $mascota)
    {
        $this->deleteFoto($mascota);
        $mascota->delete();

        return response()->json([
            'message' => 'Mascota eliminada.',
        ]);
    }

    private function fillMascota(Mascota $mascota, array $data, Request $request): void
    {
        $mascota->nombre = mb_strtoupper(trim($data['nombre']));
        $mascota->fec_reg = $data['fec_reg'] ?? now()->toDateString();
        $mascota->especie = $this->resolveEspecieName((int) $data['especie_id'], $data['especie'] ?? null);
        $mascota->fec_nac = $data['fec_nac'] ?? null;
        $mascota->edad = $data['edad'] ?? null;
        $mascota->color_principal = mb_strtoupper(trim($data['color_principal']));
        $mascota->color_secundario = $this->normalizeOptionalText($data['color_secundario'] ?? null);
        $mascota->tamano = $this->normalizeOptionalText($data['tamano'] ?? null);
        $mascota->peso = $data['peso'] ?? null;
        $mascota->estado = mb_strtoupper(trim($data['estado']));
        $mascota->particular = $this->normalizeOptionalText($data['particular'] ?? null);
        $mascota->observacion = $data['observacion'] ?? null;
        $mascota->sexo = mb_strtoupper(trim($data['sexo']));
        $mascota->fec_esterilizacion = $data['fec_esterilizacion'] ?? null;
        $mascota->esterilizado = (bool) ($data['esterilizado'] ?? false);
        $mascota->campania_id = $data['campania_id'] ?? null;
        $mascota->categoria_id = $data['categoria_id'] ?? null;
        $mascota->raza_id = $data['raza_id'];

        if ($request->hasFile('foto')) {
            $this->storeFoto($mascota, $request->file('foto'));
        }
    }

    private function resolveCodigoForStore(array $data): string
    {
        if (array_key_exists('numero', $data) && $data['numero'] !== null && $data['numero'] !== '') {
            $codigo = $this->buildCodigoFromSpeciesAndNumber($data['especie_id'], $data['numero']);
            $this->ensureCodigoDisponible($codigo);

            return $codigo;
        }

        return $this->resolveCodigoForSpecies($data['especie_id']);
    }

    private function resolveCodigoForUpdate(Mascota $mascota, array $data): string
    {
        $currentEspecieId = $mascota->raza?->especie_id
            ?? Raza::query()->whereKey($mascota->raza_id)->value('especie_id');

        if (array_key_exists('numero', $data) && $data['numero'] !== null && $data['numero'] !== '') {
            $codigo = $this->buildCodigoFromSpeciesAndNumber($data['especie_id'], $data['numero']);

            if ($codigo === $this->normalizeCodigo($mascota->codigo)) {
                return $mascota->codigo;
            }

            $this->ensureCodigoDisponible($codigo, $mascota->id);

            return $codigo;
        }

        if ((int) $currentEspecieId === (int) $data['especie_id'] && !empty($mascota->codigo)) {
            return $mascota->codigo;
        }

        return $this->resolveCodigoForSpecies($data['especie_id'], $mascota->id);
    }

    private function normalizeCodigo(?string $codigo): string
    {
        return mb_strtoupper(trim((string) $codigo));
    }

    private function buildCodigoFromSpeciesAndNumber(int|string $especieId, int|string $numero): string
    {
        $especie = Especie::query()->findOrFail($especieId);
        $numeroValue = filter_var($numero, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

        if ($numeroValue === false) {
            throw ValidationException::withMessages([
                'numero' => 'Ingrese un numero valido para la mascota.',
            ]);
        }

        return mb_strtoupper(trim($especie->codigo)) . '-' . $numeroValue;
    }

    private function resolveCodigoForSpecies(int|string $especieId, ?int $ignoreMascotaId = null): string
    {
        $especie = Especie::query()->findOrFail($especieId);
        $prefix = mb_strtoupper(trim($especie->codigo)) . '-';
        $nextNumber = $this->nextMascotaNumberForSpecies((int) $especie->id, $prefix, $ignoreMascotaId);
        $codigo = $prefix . $nextNumber;

        $this->ensureCodigoDisponible($codigo, $ignoreMascotaId);

        return $codigo;
    }

    private function nextMascotaNumberForSpecies(int $especieId, string $prefix, ?int $ignoreMascotaId = null): int
    {
        $query = Mascota::query()
            ->join('razas', 'mascotas.raza_id', '=', 'razas.id')
            ->where('razas.especie_id', $especieId)
            ->select('mascotas.codigo');

        if ($ignoreMascotaId !== null) {
            $query->where('mascotas.id', '!=', $ignoreMascotaId);
        }

        $maxNumber = $query->get()
            ->map(function ($item) use ($prefix) {
                $codigo = mb_strtoupper(trim((string) $item->codigo));

                if (!str_starts_with($codigo, $prefix)) {
                    return null;
                }

                $suffix = substr($codigo, strlen($prefix));

                if (!preg_match('/^\d+$/', $suffix)) {
                    return null;
                }

                return (int) $suffix;
            })
            ->filter(fn ($value) => $value !== null)
            ->max();

        return ((int) $maxNumber) + 1;
    }

    private function ensureCodigoDisponible(string $codigo, ?int $ignoreId = null): void
    {
        if (!$this->codigoExists($codigo, $ignoreId)) {
            return;
        }

        throw ValidationException::withMessages([
            'codigo' => 'El codigo ya esta registrado.',
        ]);
    }

    private function codigoExists(string $codigo, ?int $ignoreId = null): bool
    {
        $query = Mascota::query()->whereRaw('UPPER(codigo) = ?', [$codigo]);

        if ($ignoreId !== null) {
            $query->where('id', '!=', $ignoreId);
        }

        return $query->exists();
    }

    private function resolveEspecieName(int $especieId, ?string $fallback = null): string
    {
        $nombreEspecie = Especie::query()->find($especieId)?->nombre;

        if (!empty($nombreEspecie)) {
            return mb_strtoupper(trim($nombreEspecie));
        }

        return mb_strtoupper(trim((string) $fallback));
    }

    private function normalizeOptionalText(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : mb_strtoupper($value);
    }

    private function storeFoto(Mascota $mascota, $file): void
    {
        $destinationPath = public_path('mascotas');

        if (!File::exists($destinationPath)) {
            File::makeDirectory($destinationPath, 0755, true, true);
        }

        if (!empty($mascota->foto)) {
            $previous = $destinationPath . DIRECTORY_SEPARATOR . $mascota->foto;
            if (File::exists($previous)) {
                File::delete($previous);
            }
        }

        $extension = $file->getClientOriginalExtension();
        $filename = 'mascota_' . now()->format('YmdHis') . '_' . Str::random(6) . ($extension ? '.' . $extension : '');
        $file->move($destinationPath, $filename);
        $mascota->foto = $filename;
        $mascota->save();
    }

    private function deleteFoto(Mascota $mascota): void
    {
        if (empty($mascota->foto)) {
            return;
        }

        $path = public_path('mascotas' . DIRECTORY_SEPARATOR . $mascota->foto);
        if (File::exists($path)) {
            File::delete($path);
        }
    }
}
