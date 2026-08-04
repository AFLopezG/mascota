<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Campania;
use App\Models\Categoria;
use App\Http\Requests\StoreMascotaRequest;
use App\Http\Requests\UpdateMascotaRequest;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use App\Models\Especie;
use App\Models\Mascota;
use App\Models\Persona;
use App\Models\Raza;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Endroid\QrCode\Writer\PngWriter;

class MascotaController extends Controller
{
    public function index(Request $request)
    {
        $query = Mascota::query()->with(['persona', 'vacunas', 'categoria', 'raza.especie', 'campania']);

        if ($request->filled('persona_id')) {
            $query->where('persona_id', $request->integer('persona_id'));
        }

        if ($request->filled('q')) {
            $term = mb_strtoupper(trim((string) $request->input('q')));
            $query->where(function ($subQuery) use ($term) {
                $subQuery->whereRaw('UPPER(codigo) LIKE ?', ['%' . $term . '%'])
                    ->orWhereRaw('UPPER(nombre) LIKE ?', ['%' . $term . '%']);
            });
        }

        $limit = $request->integer('limit');
        if ($limit > 0) {
            $query->limit(min($limit, 20));
        }

        return $query->orderBy('nombre')->get();
    }

    public function publicShow(string $codigo): JsonResponse
    {
        $codigo = $this->normalizeCodigo($codigo);

        $mascota = Mascota::query()
            ->with(['persona', 'raza.especie', 'categoria'])
            ->whereRaw('UPPER(codigo) = ?', [$codigo])
            ->firstOrFail();

        return response()->json([
            'data' => $this->buildPublicMascotaData($mascota),
        ]);
    }

    public function publicCredentialPdf(string $codigo)
    {
        $codigo = $this->normalizeCodigo($codigo);

        $mascota = Mascota::query()
            ->with(['persona', 'raza.especie', 'categoria'])
            ->whereRaw('UPPER(codigo) = ?', [$codigo])
            ->firstOrFail();

        $data = $this->buildCredentialPdfData($mascota);

        return Pdf::loadView('pdf.credencial-mascota', $data)
            ->setPaper('letter', 'portrait')
            ->stream('credencial-' . $codigo . '.pdf');
    }

    public function store(StoreMascotaRequest $request)
    {
        $data = $request->validated();
        $persona = Persona::findOrFail($data['persona_id']);

        $mascota = new Mascota();
        $mascota->codigo = $this->resolveCodigoForStore($data);
        $mascota->numero = $this->resolveNumeroForStore($data, $mascota->codigo);
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
        $mascota->numero = $this->resolveNumeroForUpdate($mascota, $data, $mascota->codigo);
        $this->fillMascota($mascota, $data, $request);
        $mascota->persona_id = $data['persona_id'];
        $mascota->save();

        return response()->json([
            'message' => 'Mascota actualizada.',
            'data' => $mascota->fresh(['persona', 'vacunas', 'categoria', 'raza.especie', 'campania']),
        ]);
    }

    public function updateFoto(Request $request, Mascota $mascota): JsonResponse
    {
        $validated = $request->validate([
            'foto' => ['required', 'image', 'max:4096'],
        ]);

        $this->storeFoto($mascota, $validated['foto']);

        return response()->json([
            'message' => 'Foto actualizada correctamente.',
            'data' => $mascota->fresh(['persona', 'raza']),
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

    private function resolveNumeroForStore(array $data, ?string $codigo = null): int
    {
        if (array_key_exists('numero', $data) && $data['numero'] !== null && $data['numero'] !== '') {
            $numeroValue = filter_var($data['numero'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

            if ($numeroValue === false) {
                throw ValidationException::withMessages([
                    'numero' => 'Ingrese un numero valido para la mascota.',
                ]);
            }

            return $numeroValue;
        }

        if (!empty($codigo)) {
            $numeroValue = $this->extractNumeroFromCodigo($codigo);

            if ($numeroValue !== null) {
                return $numeroValue;
            }
        }

        $codigo = $codigo ?? $this->resolveCodigoForSpecies($data['especie_id']);
        $numeroValue = $this->extractNumeroFromCodigo($codigo);

        if ($numeroValue === null) {
            throw ValidationException::withMessages([
                'numero' => 'No se pudo generar el numero de la mascota.',
            ]);
        }

        return $numeroValue;
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

    private function resolveNumeroForUpdate(Mascota $mascota, array $data, ?string $codigo = null): int
    {
        if (array_key_exists('numero', $data) && $data['numero'] !== null && $data['numero'] !== '') {
            $numeroValue = filter_var($data['numero'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

            if ($numeroValue === false) {
                throw ValidationException::withMessages([
                    'numero' => 'Ingrese un numero valido para la mascota.',
                ]);
            }

            return $numeroValue;
        }

        $currentEspecieId = $mascota->raza?->especie_id
            ?? Raza::query()->whereKey($mascota->raza_id)->value('especie_id');

        if ((int) $currentEspecieId === (int) $data['especie_id'] && !empty($mascota->numero)) {
            return (int) $mascota->numero;
        }

        if (!empty($codigo)) {
            $numeroValue = $this->extractNumeroFromCodigo($codigo);

            if ($numeroValue !== null) {
                return $numeroValue;
            }
        }

        if ((int) $currentEspecieId === (int) $data['especie_id']) {
            $numeroValue = $this->extractNumeroFromCodigo($mascota->codigo);

            if ($numeroValue !== null) {
                return $numeroValue;
            }
        }

        $codigo = $this->resolveCodigoForSpecies($data['especie_id'], $mascota->id);
        $numeroValue = $this->extractNumeroFromCodigo($codigo);

        if ($numeroValue === null) {
            throw ValidationException::withMessages([
                'numero' => 'No se pudo generar el numero de la mascota.',
            ]);
        }

        return $numeroValue;
    }

    private function normalizeCodigo(?string $codigo): string
    {
        return mb_strtoupper(trim((string) $codigo));
    }

    private function buildPublicMascotaData(Mascota $mascota): array
    {
        return [
            'id' => $mascota->id,
            'codigo' => $mascota->codigo,
            'nombre' => $mascota->nombre,
            'foto' => $mascota->foto,
            'fotoUrl' => !empty($mascota->foto) ? asset('mascotas/' . $mascota->foto) : null,
            'especie' => $mascota->raza?->especie?->nombre ?? $mascota->especie,
            'raza' => $mascota->raza?->nombre ?? '',
            'color_principal' => $mascota->color_principal,
            'color_secundario' => $mascota->color_secundario,
            'tamano' => $mascota->tamano,
            'estado' => $mascota->estado,
            'persona' => [
                'id' => $mascota->persona?->id,
                'cinit' => $mascota->persona?->cinit,
                'nombre' => $mascota->persona?->nombre,
                'paterno' => $mascota->persona?->paterno,
                'materno' => $mascota->persona?->materno,
                'telefono' => $mascota->persona?->telefono,
                'direccion' => $mascota->persona?->direccion,
                'zona' => $mascota->persona?->zona,
                'distrito' => $mascota->persona?->distrito,
            ],
        ];
    }

    private function buildCredentialPdfData(Mascota $mascota): array
    {
        $publicLink = $this->buildPublicCredentialUrl($mascota->codigo);
        $persona = $mascota->persona;
        $nombrePropietario = trim(implode(' ', array_filter([
            $persona?->nombre,
            $persona?->paterno,
            $persona?->materno,
        ]))) ?: '-';
        $fotoPath = $mascota->foto ? public_path('mascotas' . DIRECTORY_SEPARATOR . $mascota->foto) : null;

        return [
            'mascota' => [
                'id' => $mascota->id,
                'codigo' => $mascota->codigo,
                'nombre' => $mascota->nombre,
                'fotoUrl' => $this->buildDataUriFromPath($fotoPath),
                'especie' => $mascota->raza?->especie?->nombre ?? $mascota->especie,
                'raza' => $mascota->raza?->nombre ?? '-',
                'color_principal' => $mascota->color_principal,
                'color_secundario' => $mascota->color_secundario,
                'tamano' => $mascota->tamano,
                'estado' => $mascota->estado,
                'publicLink' => $publicLink,
                'qrSrc' => $this->buildQrDataUri($publicLink),
            ],
            'persona' => [
                'nombre' => $nombrePropietario,
                'cinit' => $persona?->cinit ?? '-',
                'complemento' => $persona?->complemento ?? '',
                'telefono' => $persona?->telefono ?? '-',
                'direccion' => $persona?->direccion ?? '-',
                'zona' => $persona?->zona ?? '-',
                'distrito' => $persona?->distrito ?? '-',
            ],
        ];
    }

    private function buildPublicCredentialUrl(string $codigo): string
    {
        return url(env('URL_FRONT') . rawurlencode($this->normalizeCodigo($codigo)));
    }

    private function buildQrDataUri(string $value): string
    {
        if ($value === '') {
            return '';
        }

        $result = Builder::create()
            ->writer(new PngWriter())
            ->data($value)
            ->encoding(new Encoding('UTF-8'))
            ->errorCorrectionLevel(ErrorCorrectionLevel::Medium)
            ->size(260)
            ->margin(10)
            ->build();

        return $result->getDataUri();
    }

    private function buildDataUriFromPath(?string $path): ?string
    {
        if (empty($path) || !File::exists($path)) {
            return null;
        }

        $mime = File::mimeType($path) ?: 'image/jpeg';

        return 'data:' . $mime . ';base64,' . base64_encode(File::get($path));
    }

    private function extractNumeroFromCodigo(?string $codigo): ?int
    {
        $codigo = $this->normalizeCodigo($codigo);

        if ($codigo === '' || !preg_match('/-(\d+)$/', $codigo, $matches)) {
            return null;
        }

        return (int) $matches[1];
    }

    private function buildCodigoFromSpeciesAndNumber(int|string $especieId, int $numero): string
    {
        $especie = Especie::query()->findOrFail($especieId);
        $numeroValue = filter_var($numero, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);// 

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
