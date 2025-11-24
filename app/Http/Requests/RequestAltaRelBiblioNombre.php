<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use App\Models\Nombre;
use Illuminate\Support\Facades\DB;

class RequestAltaRelBiblioNombre extends FormRequest   
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
            'data.biblioRel' => ['required','array', 'min:1'],
            
            'data.biblioRel.*' => ['integer',
                        function ($attribute, $value, $fail) {
                                        $exists = DB::connection('catcentral')
                                                    ->table('Bibliografia')
                                                    ->where('IdBibliografia', $value)
                                                    ->exists();
                                        if (!$exists) {
                                            $fail("El $attribute no existe en la base de datos.");
                                        }
                                    }],

            'data.taxAct' => ['required','integer', 
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
            'data.biblioRel.required' => 'Debe seleccionar al menos una bibliografía.',
            'data.biblioRel.array'    => 'El campo bibliografía debe ser un arreglo.',

            'data.biblioRel.*.integer' => 'Cada IdBibliografía debe ser un número.',

            'data.taxAct.required' => 'El campo taxAct es obligatorio.',
            'data.taxAct.integer'  => 'taxAct debe ser un número.',
        ];
    }
}