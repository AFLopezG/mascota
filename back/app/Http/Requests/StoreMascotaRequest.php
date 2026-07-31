<?php

namespace App\Http\Requests;

use App\Models\Raza;
use Illuminate\Foundation\Http\FormRequest;

class StoreMascotaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'persona_id' => ['required', 'integer', 'exists:personas,id'],
            'codigo' => ['nullable', 'string', 'max:50'],
            'numero' => ['nullable', 'integer', 'min:1'],
            'fec_reg' => ['nullable', 'date'],
            'nombre' => ['required', 'string'],
            'especie' => ['nullable', 'string'],
            'especie_id' => ['required', 'integer', 'exists:especies,id'],
            'fec_nac' => ['nullable', 'date'],
            'edad' => ['nullable', 'integer', 'min:0'],
            'color_principal' => ['required', 'string'],
            'color_secundario' => ['nullable', 'string'],
            'tamano' => ['nullable', 'string', 'max:50'],
            'peso' => ['nullable', 'numeric'],
            'particular' => ['nullable', 'string'],
            'estado' => ['required', 'string'],
            'observacion' => ['nullable', 'string'],
            'sexo' => ['required', 'string'],
            'esterilizado' => ['nullable', 'boolean'],
            'fec_esterilizacion' => ['required_if:esterilizado,1,true', 'nullable', 'date'],
            'campania_id' => ['nullable', 'integer', 'exists:campanias,id'],
            'categoria_id' => ['nullable', 'integer', 'exists:categorias,id'],
            'raza_id' => ['required', 'integer', 'exists:razas,id'],
            'foto' => ['nullable', 'file', 'image', 'max:4096'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if (!$this->filled('especie_id') || !$this->filled('raza_id')) {
                return;
            }

            $matches = Raza::query()
                ->where('id', $this->integer('raza_id'))
                ->where('especie_id', $this->integer('especie_id'))
                ->exists();

            if (!$matches) {
                $validator->errors()->add('raza_id', 'La raza no pertenece a la especie seleccionada.');
            }
        });
    }
}
