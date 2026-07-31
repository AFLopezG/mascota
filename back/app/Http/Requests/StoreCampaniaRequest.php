<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCampaniaRequest extends FormRequest
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
            'nombre' => ['required', 'string', 'max:255'],
            'fec_ini' => ['required', 'date'],
            'fec_fin' => ['required', 'date', 'after_or_equal:fec_ini'],
            'lugar' => ['required', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string', 'max:1000'],
            'estado' => ['nullable', 'string', 'in:ACTIVA,ANULADA', 'max:50'],
            'campania_tipo_id' => ['required', 'integer', 'exists:campania_tipos,id'],
        ];
    }
}
