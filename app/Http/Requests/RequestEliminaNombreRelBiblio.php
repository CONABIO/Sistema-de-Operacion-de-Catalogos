<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use App\Models\Nombre;
use Illuminate\Support\Facades\DB;

class RequestEliminaNombreRelBiblio extends FormRequest   
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
            'taxAct' => ['required','integer',
                                    function ($attribute, $value, $fail) {
                                        $exists = DB::connection('catcentral')
                                                    ->table('Nombre')
                                                    ->where('IdNombre', $value)
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
        ];
    }

    public function messages(): array
    {
        return [
            'taxAct.required' => 'El campo IdNombre es obligatorio.',
            'taxAct.integer'  => 'IdNombre debe ser un número.',

            'idBiblio.required' => 'El campo IdNombreRel es obligatorio.',
            'idBiblio.integer'  => 'IdNombreRel debe ser un número.',
        ];
    }

}
