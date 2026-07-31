<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use App\Models\Nombre;
use App\Models\NomComun;
use App\Models\TipoRegion;
use App\Models\Region;
use App\Models\RelNomNomComunRegion;
use Illuminate\Support\Facades\DB;

class RequestAltaNomNomComun extends FormRequest
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
            'idNomComun' => ['required', 'integer', 
                                        function ($attribute, $value, $fail) {                                            
                                            if (!NomComun::where('IdNomComun', $value)->exists()) {
                                                $fail("El $attribute no existe en la base de datos.");
                                            }
                                        }],
            'idTipoReg' => ['required', 'integer', 
                                        function ($attribute, $value, $fail) {                                            
                                            if (!TipoRegion::where('IdTipoRegion', $value)->exists()) {
                                                $fail("El $attribute no existe en la base de datos.");
                                            }
                                        }],
            'idRegion' => ['required', 'integer', 
                                        function ($attribute, $value, $fail) {                                            
                                            if (!Region::where('idRegion', $value)->exists()) {
                                                $fail("El $attribute no existe en la base de datos.");
                                            }
                                        }],
        ];
    }

    public function withValidator($validator){

        $validator->after(function ($validator){
           
            $exists = RelNomNomComunRegion::where('IdNombre', $this->idNombre)
                                          ->where('IdNomComun', $this->idNomComun)
                                          ->where('IdRegion', $this->idRegion)
                                          ->exists();

            if($exists){
                log::info("La relación que intenta crear ya existe");
                $validator->errors()->add('relacion', 'La relación entre taxón, nombre común y región ya existe.');
            }
        });
    }
}
