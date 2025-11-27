<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use App\Models\Nombre;
use Illuminate\Support\Facades\DB;

class RequestEliminaRelacionBiblio extends FormRequest   
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
            'relCompleta.relIdNombre' => ['required','integer',
                                    function ($attribute, $value, $fail) {
                                        $exists = DB::connection('catcentral')
                                                    ->table('Nombre')
                                                    ->where('IdNombre', $value)
                                                    ->exists();
                                        if (!$exists) {
                                            $fail("El $attribute no existe en la base de datos.");
                                        }
                                    }],
            'relCompleta.relIdNombreRel' => ['required','integer',
                                    function ($attribute, $value, $fail) {
                                        $exists = DB::connection('catcentral')
                                                    ->table('Nombre')
                                                    ->where('IdNombre', $value)
                                                    ->exists();
                                        if (!$exists) {
                                            $fail("El $attribute no existe en la base de datos.");
                                        }
                                    }],
            'relCompleta.tipoRel' => ['required','integer', 
                                function ($attribute, $value, $fail) {
                                        $exists = DB::connection('catcentral')
                                                    ->table('Tipo_Relacion')
                                                    ->where('IdTipoRelacion', $value)
                                                    ->exists();
                                        if (!$exists) {
                                            $fail("El $attribute no existe en la base de datos.");
                                        }
                                    }],
            'idBiblio' => ['required','integer', 
                        function ($attribute, $value, $fail) {
                                        $exists = DB::connection('catcentral')
                                                    ->table('Bibliografia')
                                                    ->where('IdBibliografia', $value)
                                                    ->exists();
                                        if (!$exists) {
                                            $fail("El $attribute no existe en la base de datos.");
                                        }
                                    }],
            'taxAct' => ['required','integer', 
                        function ($attribute, $value, $fail) {
                                        $exists = DB::connection('catcentral')
                                                    ->table('Nombre')
                                                    ->where('IdNombre', $value)
                                                    ->exists();
                                        if (!$exists) {
                                            $fail("El $attribute no existe en la base de datos.");
                                        }
                                    }]
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

            'idBiblio.required' => 'El campo IdBibliografia es obligatorio.',
            'idBiblio.integer'  => 'IdBibliografia debe ser un número.',
        ];
    }
}