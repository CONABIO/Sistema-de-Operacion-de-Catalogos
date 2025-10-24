<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use App\Models\Nombre;
use Illuminate\Support\Facades\DB;

class RequestHuesped
 extends FormRequest
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
            'params.tipRelacion' => ['required', 'integer'],
            'params.taxonAct.id' => ['required', 'integer', 
                                        function ($attribute, $value, $fail) {
                                            $exists = DB::connection('catcentral')
                                                        ->table('Nombre')
                                                        ->where('IdNombre', $value)
                                                        ->exists();
                                            if (!$exists) {
                                                $fail("El $attribute no existe en la base de datos.");
                                            }
                                        }],
            'params.taxonActRel.id' => ['required', 'integer',
                                            function ($attribute, $value, $fail) {
                                                $exists = DB::connection('catcentral')
                                                            ->table('Nombre')
                                                            ->where('IdNombre', $value)
                                                            ->exists();
                                                if (!$exists) {
                                                    $fail("El $attribute no existe en la base de datos.");
                                                }
                                            }],
            'params.taxonAct.estatus' => ['required', 'string'],
            'params.taxonActRel.estatus' => ['required', 'string'],
            'params.taxonAct.completo.scat.grupo_scat.GrupoAbreviado' => ['required', 'string'],
            'params.taxonActRel.completo.scat.grupo_scat.GrupoAbreviado' => ['required', 'string'],
        ];
    }

    public function withValidator($validator){
        //Log::info('Request completo:', $this->all());

        $validator->after(function ($validator){

            $gruposPara = ["ARACH", "COLEO", "DIPTE", "HYMEN", "INSEC", 
                          "NEMAT", "ACANT", "ANNEL", "CESTO", "CRUST", 
                          "MONOG", "PROT", "MYXOZ", "TREMA"];
            
            $gruposVert = ["ANFIB", "AVES", "MAMIF", "PECES", "REPTI"];
            
            $estatusPermitido = ["Válido", "Aceptado"];

            $taxonActGrp = $this->input('params.taxonAct.completo.scat.grupo_scat.GrupoAbreviado', []); 
            $taxonRelGrp = $this->input('params.taxonActRel.completo.scat.grupo_scat.GrupoAbreviado', []);
            $taxonActEst = $this->input('params.taxonAct.estatus', '');
            $taxonRelEst = $this->input('params.taxonActRel.estatus','');
            $taxonAct = $this->input('params.taxonAct', []); 
            $taxonRel = $this->input('params.taxonActRel', []);

            $idAct = data_get($taxonAct, 'id');
            $idRel = data_get($taxonRel, 'id');
            $idTipoRel = $this->input('params.tipRelacion', 0);

            $exists = DB::connection('catcentral')
                ->table('Nombre_Relacion')
                ->where('IdNombre', $idAct)
                ->where('IdNombreRel', $idRel)
                ->where('IdTipoRelacion', $idTipoRel)
                ->exists();

            //Valida que la relacion no exista 
            if($exists){
                log::info("La relación que intenta crear ya existe");
                $validator->errors()->add('relacion', 'La relación entre estos taxones ya existe.');
            }

            //Valida que el estatus sea válido o aceptado 
            if(!(in_array($taxonActEst, $estatusPermitido)) || !(in_array($taxonRelEst, $estatusPermitido)))
            {
                $validator->errors()->add('validos', "No se puede generar la relación ya que uno o ambos taxones tienen estatus diferente de válido/correcto");
            }

            //Valida que los grupos pertenecen a los vertebrados o parasitos aceptados
            if(!(
                    (in_array($taxonActGrp, $gruposPara) && in_array($taxonRelGrp, $gruposVert)) ||
                    (in_array($taxonActGrp, $gruposVert) && in_array($taxonRelGrp, $gruposPara))
                )
            ){
                     $validator->errors()->add('validos', "El vertebrado o parásito no pertenece a un grupo válido. 
                                                            Vertebrados: ANFIB, AVES, MAMIF, PECES, REPTI. 
                                                            Parásitos: ARACH, COLEO, DIPTE, HYMEN, INSEC, NEMAT, ACANT, ANNEL, CESTO, CRUST, MONOG, PROT, MYXOZ, TREMA.");
            }
        });
    }
}
