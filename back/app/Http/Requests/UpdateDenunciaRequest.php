<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDenunciaRequest extends FormRequest
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
            'fec_denuncia' => ['required', 'date'],
            'persona_id' => ['required', 'integer', 'exists:personas,id'],
            'mascota_id' => ['required', 'integer', 'exists:mascotas,id'],
            'raza_id' => ['required', 'integer', 'exists:razas,id'],
            'denuncia_tipo_ids' => ['required', 'array', 'min:1'],
            'denuncia_tipo_ids.*' => ['integer', 'exists:denuncia_tipos,id'],
            'direccion' => ['nullable', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string'],
            'zona' => ['nullable', 'string', 'max:255'],
            'color' => ['nullable', 'string', 'max:255'],
            'tamanio' => ['nullable', 'string', 'max:255'],
            'estado' => ['nullable', 'string', 'max:255'],
            'observacion' => ['nullable', 'string'],
            'nom_afectado' => ['nullable', 'string', 'max:255'],
            'edad' => ['nullable', 'string', 'max:255'],
            'telefono' => ['nullable', 'string', 'max:255'],
            'dir_inicidente' => ['nullable', 'string', 'max:255'],
            'tipo_lesion' => ['nullable', 'string', 'max:255'],
            'dias_obser' => ['nullable', 'string', 'max:255'],
            'resultado' => ['nullable', 'string', 'max:255'],
            'obs' => ['nullable', 'string', 'max:255'],
        ];
    }
}
