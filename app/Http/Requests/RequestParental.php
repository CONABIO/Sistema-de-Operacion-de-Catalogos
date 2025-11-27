<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use App\Models\Nombre;
use Illuminate\Support\Facades\DB;

class RequestParental extends FormRequest
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
            'params.taxonAct.completo.categoria.NombreCategoriaTaxonomica' => ['required', 'string'],
            'params.taxonActRel.completo.categoria.NombreCategoriaTaxonomica' => ['required', 'string']
        ];
    }

    public function withValidator($validator){

        $validator->after(function ($validator){

            $taxonAct = $this->input('params.taxonAct', []); 
            $taxonRel = $this->input('params.taxonActRel', []);
            $categAct = $this->input('params.taxonAct.completo.categoria.NombreCategoriaTaxonomica', []); 
            $categRel = $this->input('params.taxonActRel.completo.categoria.NombreCategoriaTaxonomica', []);
            $idTipoRel = $this->input('params.tipRelacion', 0);

            $idAct = data_get($taxonAct, 'id');
            $idRel = data_get($taxonRel, 'id');

            $exists = DB::connection('catcentral')
                ->table('Nombre_Relacion')
                ->where('IdNombre', $idAct)
                ->where('IdNombreRel', $idRel)
                ->where('IdTipoRelacion', $idTipoRel)
                ->exists();

            $categPerm = ["género", "especie", "híbrido"];

            //Valida que la relacion no exista 
            if($exists){
                log::info("La relación que intenta crear ya existe");
                $validator->errors()->add('relacion', 'La relación entre estos taxones ya existe.');
            }

            //Valida que el taxon Actual sea un híbrido
            if($categAct !== "híbrido"){
                $validator->errors()->add('validos', 'No es posible asociar un parental a un taxón que no es un híbrido');
            }

            /*Valida el nivel taxonomico 
            
            $validaTaxon = (
                (($taxonAct['IdNivel1'] ?? 0) != 6 && ($taxonAct['IdNivel3'] ?? 0) != 0) ||
                (($taxonAct['IdNivel1'] ?? 0) != 7 && ($taxonAct['IdNivel3'] ?? 0) != 0) ||
                (($taxonActRel['IdNivel1'] ?? 0) != 6 && ($taxonActRel['IdNivel3'] ?? 0) != 0) ||
                (($taxonActRel['IdNivel1'] ?? 0) != 7 && ($taxonActRel['IdNivel3'] ?? 0) != 0)
            );*/

            if(!(in_array($categAct, $categPerm)) || !(in_array($categRel, $categPerm)) ){
                $validator->errors()->add('validos', 'No es posible asociar un parental a un taxón cuya categoría taxonómica sea diferente de género o especie');
            }
        });
    }
}
