<?php

namespace App\Http\Controllers;

use App\Models\Persona;
use Illuminate\Http\Request;

class PersonaController extends Controller
{
    public function index(Request $request)
    {
        $query = Persona::query()->with(['mascotas.categoria', 'mascotas.raza.especie', 'mascotas.campania', 'mascotas.vacunas']);

        foreach (['cinit', 'nombre', 'paterno', 'materno', 'correo', 'zona', 'distrito'] as $field) {
            if ($request->filled($field)) {
                $query->whereRaw('UPPER(' . $field . ') LIKE ?', ['%' . $this->normalizeText($request->input($field)) . '%']);
            }
        }

        return $query->orderBy('nombre')->get();
    }

    public function store(Request $request)
    {
        $data = $this->validatePersona($request);
        $persona = $this->findByDocument($data['cinit'], $data['complemento'] ?? null) ?? new Persona();
        $wasNew = !$persona->exists;

        $this->fillPersona($persona, $data);
        $persona->save();

        return response()->json([
            'message' => $wasNew ? 'Persona registrada.' : 'Persona actualizada.',
            'data' => $persona->fresh(['mascotas.categoria', 'mascotas.raza.especie', 'mascotas.campania', 'mascotas.vacunas']),
        ], $wasNew ? 201 : 200);
    }

    public function show(Persona $persona)
    {
        return response()->json([
            'data' => $persona->load(['mascotas.categoria', 'mascotas.raza.especie', 'mascotas.campania', 'mascotas.vacunas']),
        ]);
    }

    public function update(Request $request, Persona $persona)
    {
        $data = $this->validatePersona($request, $persona->id);
        $this->fillPersona($persona, $data);
        $persona->save();

        return response()->json([
            'message' => 'Persona actualizada.',
            'data' => $persona->fresh(['mascotas.categoria', 'mascotas.raza.especie', 'mascotas.campania', 'mascotas.vacunas']),
        ]);
    }

    public function destroy(Persona $persona)
    {
        $persona->delete();

        return response()->json([
            'message' => 'Persona eliminada.',
        ]);
    }

    public function buscarDocumento(Request $request)
    {
        $data = $request->validate([
            'cinit' => ['required', 'string'],
            'complemento' => ['nullable', 'string'],
        ]);

        $cinit = $this->normalizeText($data['cinit'] ?? null);  
        $complemento = $this->normalizeOptionalText($data['complemento'] ?? null);

        $query = Persona::with(['mascotas.categoria', 'mascotas.raza.especie', 'mascotas.campania', 'mascotas.vacunas'])->where('cinit', $cinit);

        if ($complemento !== null && $complemento !== '') {
            $query->where('complemento', $complemento);
        }

        $persona = $query->first();

        return $persona;
    }

    private function validatePersona(Request $request, ?int $personaId = null): array
    {
        return $request->validate([
            'cinit' => ['required', 'string'],
            'complemento' => ['nullable', 'string'],
            'nombre' => ['required', 'string'],
            'paterno' => ['nullable', 'string'],
            'materno' => ['nullable', 'string'],
            'direccion' => ['nullable', 'string'],
            'telefono' => ['nullable', 'string'],
            'emergencia' => ['nullable', 'string'],
            'lat' => ['nullable', 'string'],
            'lng' => ['nullable', 'string'],
            'luz_agua' => ['nullable', 'string'],
            'correo' => ['nullable', 'string'],
            'zona' => ['nullable', 'string'],
            'distrito' => ['nullable', 'string'],
            'fecha' => ['nullable', 'date'],
        ]);
    }

    private function fillPersona(Persona $persona, array $data): void
    {
        $persona->cinit = $this->normalizeText($data['cinit']);
        $persona->complemento = $this->normalizeOptionalText($data['complemento'] ?? null);
        $persona->nombre = $this->normalizeText($data['nombre']);
        $persona->paterno = $this->normalizeOptionalText($data['paterno'] ?? null);
        $persona->materno = $this->normalizeOptionalText($data['materno'] ?? null);
        $persona->direccion = $this->normalizeOptionalText($data['direccion'] ?? null);
        $persona->telefono = $this->normalizeOptionalText($data['telefono'] ?? null);
        $persona->emergencia = $this->normalizeOptionalText($data['emergencia'] ?? null);
        $persona->lat = $this->normalizeOptionalText($data['lat'] ?? null);
        $persona->lng = $this->normalizeOptionalText($data['lng'] ?? null);
        $persona->luz_agua = $this->normalizeOptionalText($data['luz_agua'] ?? null);
        $persona->correo = $this->normalizeEmail($data['correo'] ?? null);
        $persona->zona = $this->normalizeOptionalText($data['zona'] ?? null);
        $persona->distrito = $this->normalizeOptionalText($data['distrito'] ?? null);
        $persona->fecha = $data['fecha'] ?? null;
    }

    private function findByDocument(?string $cinit, ?string $complemento = null): ?Persona
    {
        $cinit = $this->normalizeOptionalText($cinit);
        if (!$cinit) {
            return null;
        }

        $query = Persona::query()->where('cinit', $cinit);
        $complemento = $this->normalizeOptionalText($complemento);

        if ($complemento !== null && $complemento !== '') {
            $query->where('complemento', $complemento);
        }

        return $query->first();
    }

    private function normalizeText(?string $value): string
    {
        return mb_strtoupper(trim((string) $value));
    }

    private function normalizeOptionalText(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : mb_strtoupper($value);
    }

    private function normalizeEmail(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : mb_strtolower($value);
    }
}
