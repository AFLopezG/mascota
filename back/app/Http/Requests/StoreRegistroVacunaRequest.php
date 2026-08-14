<?php

namespace App\Http\Requests;

use App\Models\Campania;
use Illuminate\Foundation\Http\FormRequest;

class StoreRegistroVacunaRequest extends FormRequest
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
            'cedula' => ['nullable', 'string', 'max:255'],
            'nombre' => ['nullable', 'string', 'max:255'],
            'domicilio' => ['nullable', 'string', 'max:255'],
            'celular' => ['nullable', 'string', 'max:255'],
            'nombre_mascota' => ['nullable', 'string', 'max:255'],
            'especie' => ['nullable', 'string', 'max:255'],
            'raza' => ['nullable', 'string', 'max:255'],
            'menor' => ['nullable', 'boolean'],
            'foto' => ['nullable', 'image', 'max:5120'],
            'lat' => ['nullable', 'string', 'max:255'],
            'lng' => ['nullable', 'string', 'max:255'],
            'fecha_vacuna' => ['required', 'date'],
            'campania_id' => ['required', 'integer', 'exists:campanias,id'],
            'especie_id' => ['required', 'integer', 'exists:especies,id'],
            'raza_id' => ['nullable', 'integer', 'exists:razas,id'],
            'place_id' => ['required', 'integer', 'exists:places,id'],
            'health_center_id' => ['nullable', 'integer', 'exists:health_centers,id'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if (!$this->filled('campania_id')) {
                return;
            }

            $campania = Campania::query()->find($this->integer('campania_id'));

            if ($campania === null || $campania->isLocked()) {
                $validator->errors()->add('campania_id', 'Seleccione una campania de vacunacion vigente.');
            }
        });
    }
}
