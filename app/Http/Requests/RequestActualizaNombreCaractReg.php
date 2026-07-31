<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use App\Models\Nombre;
use App\Models\CatalogoNombre;
use App\Models\RelNombreCatalogoRegion;
use App\Models\TipoDistribucion;
use App\Models\Region;
use Illuminate\Support\Facades\DB;

class RequestActualizaNombreCaractReg extends FormRequest
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
            'idCatNombre' => ['required', 'integer', 
                                        function ($attribute, $value, $fail) {                                            
                                            if (!CatalogoNombre::where('IdCatNombre', $value)->exists()) {
                                                $fail("El $attribute no existe en la base de datos.");
                                            }
                                        }],
            'idRegion' => ['required', 'integer', 
                                        function ($attribute, $value, $fail) {                                            
                                            if (!Region::where('IdRegion', $value)->exists()) {
                                                $fail("El $attribute no existe en la base de datos.");
                                            }
                                        }],  
            'idTipoDistAct' => ['required', 'integer', 
                                        function ($attribute, $value, $fail) {                                            
                                            if (!TipoDistribucion::where('IdTipoDistribucion', $value)->exists()) {
                                                $fail("El $attribute no existe en la base de datos.");
                                            }
                                        }],  
            'idTipoDistNue' => ['required', 'integer', 
                                        function ($attribute, $value, $fail) {                                            
                                            if (!TipoDistribucion::where('IdTipoDistribucion', $value)->exists()) {
                                                $fail("El $attribute no existe en la base de datos.");
                                            }
                                        }],                                                                                   
            'observaciones' =>['nullable',
                               'string']
        ];
    }

    public function withValidator($validator){

        $validator->after(function ($validator){
           
            $exists = RelNombreCatalogoRegion::where('IdNombre', $this->idNombre)
                                             ->where('IdCatNombre', $this->idCatNombre)
                                             ->where('IdRegion', $this->idRegion)
                                             ->where('IdTipoDistribucion', $this->idTipoDistAct)
                                             ->exists();

            if(!$exists){
                $validator->errors()->add('relacion', 'La relación que intenta actualizar no existe.');
            }
        });
    }
}
