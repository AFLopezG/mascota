<?php

namespace App\Http\Requests;

use App\Models\Raza;
use Illuminate\Foundation\Http\FormRequest;

class StoreDenunciaRequest extends FormRequest
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
            'persona_id' => ['nullable', 'integer', 'exists:personas,id'],
            'persona_cinit' => ['required_without:persona_id', 'nullable', 'string', 'max:255'],
            'persona_complemento' => ['nullable', 'string', 'max:255'],
            'persona_nombre' => ['required_without:persona_id', 'nullable', 'string', 'max:255'],
            'persona_paterno' => ['nullable', 'string', 'max:255'],
            'persona_materno' => ['nullable', 'string', 'max:255'],
            'persona_telefono' => ['nullable', 'string', 'max:255'],
            'persona_emergencia' => ['nullable', 'string', 'max:255'],
            'mascota_id' => ['nullable', 'integer', 'exists:mascotas,id'],
            'especie_id' => ['required_without:mascota_id', 'nullable', 'integer', 'exists:especies,id'],
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
            'fiscalia' => ['nullable', 'string', 'max:255'],
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
