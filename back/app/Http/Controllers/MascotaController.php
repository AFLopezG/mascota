<?php

namespace App\Http\Controllers;

use App\Models\Campania;
use App\Models\Categoria;
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

    public function store(Request $request)
    {
        $data = $this->validateMascota($request);
        $persona = Persona::findOrFail($data['persona_id']);

        $mascota = new Mascota();
        $mascota->codigo = $this->resolveCodigoForStore($data['codigo'] ?? null);
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

    public function update(Request $request, Mascota $mascota)
    {
        $data = $this->validateMascota($request);
        $mascota->codigo = $this->resolveCodigoForUpdate($mascota->id, $data['codigo'] ?? null);
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

    private function validateMascota(Request $request): array
    {
        return $request->validate([
            'persona_id' => ['required', 'integer', 'exists:personas,id'],
            'codigo' => ['nullable', 'string', 'max:50'],
            'fec_reg' => ['nullable', 'date'],
            'nombre' => ['required', 'string'],
            'especie' => ['required', 'string'],
            'fec_nac' => ['nullable', 'date'],
            'edad' => ['nullable', 'integer', 'min:0'],
            'color_principal' => ['required', 'string'],
            'color_secundario' => ['nullable', 'string'],
            'tamano' => ['nullable', 'string'],
            'peso' => ['nullable', 'numeric'],
            'particular' => ['nullable', 'string'],
            'estado' => ['required', 'string'],
            'observacion' => ['nullable', 'string'],
            'sexo' => ['required', 'string'],
            'esterilizado' => ['nullable', 'boolean'],
            'fec_esterilizacion' => ['nullable', 'date'],
            'campania_id' => ['nullable', 'integer', 'exists:campanias,id'],
            'categoria_id' => ['nullable', 'integer', 'exists:categorias,id'],
            'raza_id' => ['required', 'integer', 'exists:razas,id'],
            'foto' => ['nullable', 'file', 'image', 'max:4096'],
        ]);
    }

    private function fillMascota(Mascota $mascota, array $data, Request $request): void
    {
        $mascota->nombre = mb_strtoupper(trim($data['nombre']));
        $mascota->fec_reg = $data['fec_reg'] ?? now()->toDateString();
        $mascota->especie = $this->resolveEspecieName((int) $data['raza_id'], $data['especie'] ?? null);
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

    private function resolveCodigoForStore(?string $codigo): string
    {
        $codigo = $this->normalizeCodigo($codigo);

        if ($codigo !== '') {
            $this->ensureCodigoDisponible($codigo);
            return mb_strtoupper($codigo);
        }

        do {
            $codigo = 'M-' . now()->format('YmdHis') . '-' . Str::upper(Str::random(4));
        } while ($this->codigoExists($codigo));

        return $codigo;
    }

    private function resolveCodigoForUpdate(int $id, ?string $codigo): string
    {   
        $mascota = Mascota::find($id);
        $codigo = $this->normalizeCodigo($codigo);

        if ($codigo === '') {
            return $mascota->codigo;
        }

        if ($codigo === $this->normalizeCodigo($mascota->codigo)) {
            return $mascota->codigo;
        }

        $this->ensureCodigoDisponible($codigo, $mascota->id);

        return $codigo;
    }

    private function normalizeCodigo(?string $codigo): string
    {
        return mb_strtoupper(trim((string) $codigo));
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

    private function resolveEspecieName(int $razaId, ?string $fallback = null): string
    {
        $raza = Raza::with('especie')->find($razaId);
        $nombreEspecie = $raza?->especie?->nombre;

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
