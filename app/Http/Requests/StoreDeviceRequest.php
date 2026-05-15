<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDeviceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'codigo'           => ['required', 'string', 'max:50', 'unique:devices,codigo'],
            'nombre'           => ['required', 'string', 'max:100'],
            'ubicacion_aula'   => ['required', 'string', 'max:100'],
            'mac_address'      => ['nullable', 'string', 'max:50', 'unique:devices,mac_address'],
            'firmware_version' => ['required', 'string', 'max:20'],
            'estado'           => ['required', 'in:ONLINE,OFFLINE,MANTENIMIENTO'],
        ];
    }

    public function messages(): array
    {
        return [
            'codigo.required'         => 'El código del dispositivo es obligatorio.',
            'codigo.unique'           => 'El código ya está registrado.',
            'nombre.required'         => 'El nombre descriptivo es obligatorio.',
            'ubicacion_aula.required' => 'La ubicación o aula es obligatoria.',
            'mac_address.unique'      => 'La dirección MAC ya está registrada.',
        ];
    }
}
