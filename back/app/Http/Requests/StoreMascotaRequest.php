<?php

namespace App\Http\Requests;

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
        ];
    }
}
