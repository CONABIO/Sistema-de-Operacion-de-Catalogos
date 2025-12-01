<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use App\Models\Nombre;
use App\Models\Tipo_Relacion;
use Illuminate\Support\Facades\DB;

class RequestActualizaNombreRelBiblio extends FormRequest   
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
            'data.idNombre' => ['required','integer',
                                    function ($attribute, $value, $fail) {
                                        $exists = DB::connection('catcentral')
                                                    ->table('Nombre')
                                                    ->where('IdNombre', $value)
                                                    ->exists();
                                        if (!$exists) {
                                            $fail("El $attribute no existe en la base de datos.");
                                        }
                                    }],
            'data.idBiblio' => ['required','integer', 
                                    function ($attribute, $value, $fail) {
                                        $exists = DB::connection('catcentral')
                                                    ->table('Bibliografia')
                                                    ->where('IdBibliografia', $value)
                                                    ->exists();
                                        if (!$exists) {
                                            $fail("El $attribute no existe en la base de datos.");
                                        }
                                    }],
            'data.observacion' => 'string|nullable'
        ];
    }

    public function messages(): array
    {
        return [
            'idNombre.required' => 'El campo IdNombre es obligatorio.',
            'idNombre.integer'  => 'IdNombre debe ser un número.',

            'idBiblio.required' => 'El campo IdNombreRel es obligatorio.',
            'idBiblio.integer'  => 'IdNombreRel debe ser un número.',
        ];
    }

}
