<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use App\Models\Nombre;
use App\Models\CatalogoNombre;
use App\Models\RelNombreCatalogo;
use Illuminate\Support\Facades\DB;

class RequestAltaNombreCaract extends FormRequest
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
            'idNombre' => ['required', 'integer', 
                                        function ($attribute, $value, $fail) {                                            
                                            if (!Nombre::where('IdNombre', $value)->exists()) {
                                                $fail("El $attribute no existe en la base de datos.");
                                            }
                                        }],
            'idCaract' => ['required', 'integer', 
                                        function ($attribute, $value, $fail) {                                            
                                            if (!CatalogoNombre::where('IdCatNombre', $value)->exists()) {
                                                $fail("El $attribute no existe en la base de datos.");
                                            }
                                        }],
        ];
    }

    public function withValidator($validator){

        $validator->after(function ($validator){
           
            $exists = RelNombreCatalogo::where('IdNombre', $this->idNombre)
                                          ->where('IdCatNombre', $this->idCaract)
                                          ->exists();

            if($exists){
                log::info("La relación que intenta crear ya existe");
                $validator->errors()->add('relacion', 'La relación entre taxón, y catalogo nombre ya existe.');
            }
        });
    }
}
