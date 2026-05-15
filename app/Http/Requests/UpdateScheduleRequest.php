<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id'           => 'required|exists:users,id',
            'dia_semana'        => 'required|in:LUNES,MARTES,MIERCOLES,JUEVES,VIERNES,SABADO',
            'materia'           => 'required|string|max:255',
            'codigo_materia'    => 'nullable|string|max:20',
            'aula'              => 'required|string|max:50',
            'hora_inicio'       => 'required',
            'hora_fin'          => 'required|after:hora_inicio',
            'tolerancia_minutos'=> 'nullable|integer|min:0|max:60',
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.required'          => 'Seleccione un docente.',
            'user_id.exists'            => 'El docente seleccionado no existe.',
            'dia_semana.required'       => 'Seleccione el día de la semana.',
            'materia.required'          => 'El nombre de la materia es obligatorio.',
            'aula.required'             => 'El aula es obligatoria.',
            'hora_inicio.required'      => 'La hora de inicio es obligatoria.',
            'hora_fin.required'         => 'La hora de fin es obligatoria.',
            'hora_fin.after'            => 'La hora de fin debe ser posterior a la de inicio.',
        ];
    }
}
