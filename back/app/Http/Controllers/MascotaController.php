<?php

namespace App\Http\Controllers;

use App\Models\Mascota;
use App\Models\Persona;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class MascotaController extends Controller
{
    public function index(Request $request)
    {
        $query = Mascota::query()->with(['persona', 'vacunas']);

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

        return Mascota::with('persona', 'vacunas')->find($mascota->id);
    }

    public function show(Mascota $mascota)
    {
        return response()->json([
            'data' => $mascota->load(['persona', 'vacunas']),
        ]);
    }

    public function update(Request $request, Mascota $mascota)
    {
        $data = $this->validateMascota($request);
        $mascota = Mascota::find($request->id);
        // verificar si a cambiado el codigo sea unico sin contar la mascota actual
        if(Mascota::where('codigo', $data['codigo'])->where('id', '!=', $mascota->id)->exists()){
            
        }
        
        $mascota->nombre=strtoupper($request->nombre); //$mascota->nombre=$request->nombre;
        $mascota->tipo=strtoupper($request->tipo); //$mascota->tipo=$request->tipo;
        $mascota->fec_nac=$request->fec_nac;
        $mascota->edad=$request->edad;
        $mascota->raza=strtoupper($request->raza); //$mascota->raza=$request->raza;
        $mascota->color=strtoupper($request->color); //$mascota->color=$request->color;
        $mascota->tamano=strtoupper($request->tamano); //$mascota->tamano=$request->tamano;
        $mascota->peso=$request->peso;
        $mascota->estado=$request->estado;
        $mascota->observacion=$request->observacion;
        $mascota->sexo=$request->sexo;
        $mascota->categoria=$request->categoria;
        $mascota->esterilizado=$request->esterilizado;
        $mascota->persona_id=$data['persona_id'];
        
        $mascota->save();

        return Mascota::with('persona', 'vacunas')->find($mascota->id);
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
            'nombre' => ['required', 'string'],
            'tipo' => ['required', 'string'],
            'fec_nac' => ['nullable', 'date'],
            'edad' => ['nullable', 'integer', 'min:0'],
            'raza' => ['required', 'string'],
            'color' => ['required', 'string'],
            'tamano' => ['nullable', 'numeric'],
            'peso' => ['nullable', 'numeric'],
            'estado' => ['required', 'string'],
            'observacion' => ['nullable', 'string'],
            'sexo' => ['required', 'string'],
            'categoria' => ['required', 'string'],
            'esterilizado' => ['nullable', 'boolean'],
            'foto' => ['nullable', 'file', 'image', 'max:4096'],
        ]);
    }

    private function fillMascota(Mascota $mascota, array $data, Request $request): void
    {
        $mascota->nombre = mb_strtoupper(trim($data['nombre']));
        $mascota->tipo = mb_strtoupper(trim($data['tipo']));
        $mascota->fec_nac = $data['fec_nac'] ?? null;
        $mascota->edad = $data['edad'] ?? null;
        $mascota->raza = mb_strtoupper(trim($data['raza']));
        $mascota->color = mb_strtoupper(trim($data['color']));
        $mascota->tamano = $data['tamano'] ?? null;
        $mascota->peso = $data['peso'] ?? null;
        $mascota->estado = mb_strtoupper(trim($data['estado']));
        $mascota->observacion = $data['observacion'] ?? null;
        $mascota->sexo = mb_strtoupper(trim($data['sexo']));
        $mascota->categoria = mb_strtoupper(trim($data['categoria']));
        $mascota->esterilizado = (bool) ($data['esterilizado'] ?? false);

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
