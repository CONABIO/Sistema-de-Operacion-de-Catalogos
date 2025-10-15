<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use App\Models\Nombre;
use Illuminate\Support\Facades\DB;

class RequestActualizaNombreRel extends FormRequest   
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
        log::info("Estoy dentro del request");
        return [
            'data.relCompleta.relIdNombre' => 'required|integer',
            'data.relCompleta.relIdNombreRel' => 'required|integer',
            'data.relCompleta.tipoRel' => 'required|integer',
            'data.taxAct' => 'required|integer'
        ];
    }

    public function messages(): array
    {
        return [
            'relIdNombre.required' => 'El campo IdNombre es obligatorio.',
            'relIdNombre.integer'  => 'IdNombre debe ser un número.',

            'relIdNombreRel.required' => 'El campo IdNombreRel es obligatorio.',
            'relIdNombreRel.integer'  => 'IdNombreRel debe ser un número.',

            'tipoRel.required' => 'El campo IdTipoRelalcion es obligatorio.',
            'tipoRel.integer'  => 'IdTipoRelalcion debe ser un número.',
        ];
    }
}
