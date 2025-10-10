<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use App\Models\Nombre;
use Illuminate\Support\Facades\DB;

class RequestBorradoNombreRel extends FormRequest   
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
            'relCompleta.relIdNombre' => 'required|integer',
            'relCompleta.relIdNombreRel' => 'required|integer',
            'relCompleta.tipoRel' => 'required|integer'
        ];
    }

    public function messages(): array
    {
        return [
            'relIdNombre.required' => 'El campo IdNombre es obligatorio.',
            'relIdNombre.integer'  => 'IdNombre debe ser un número.',
            'relIdNombre.exists'   => 'El registro IdNombre no existe.',

            'relIdNombreRel.required' => 'El campo IdNombreRel es obligatorio.',
            'relIdNombreRel.integer'  => 'IdNombreRel debe ser un número.',
            'relIdNombreRel.exists'   => 'El registro IdNombreRel no existe.',

            'tipoRel.required' => 'El campo IdTipoRelalcion es obligatorio.',
            'tipoRel.integer'  => 'IdTipoRelalcion debe ser un número.',
            'tipoRel.exists'   => 'El tipo de relación no existe.',
        ];
    }
}
