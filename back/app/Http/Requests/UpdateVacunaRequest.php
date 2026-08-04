<?php

namespace App\Http\Requests;

use App\Models\Campania;
use Illuminate\Foundation\Http\FormRequest;

class UpdateVacunaRequest extends FormRequest
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
            'mascota_id' => ['required', 'integer', 'exists:mascotas,id'],
            'campania_id' => ['required', 'integer', 'exists:campanias,id'],
            'fecha' => ['required', 'date'],
            'fecha_prox' => ['nullable', 'date'],
            'tipo' => ['required', 'string', 'max:255'],
            'lugar' => ['required', 'string', 'max:255'],
            'num_lote' => ['nullable', 'string', 'max:255'],
            'observacion' => ['nullable', 'string'],
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
