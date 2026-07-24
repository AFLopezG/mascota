<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePersonaRequest extends FormRequest
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
        ];
    }
}
