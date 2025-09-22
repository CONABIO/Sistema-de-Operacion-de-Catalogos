<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use App\Models\Nombre;
use Illuminate\Support\Facades\DB;

class RequestEquivalencia extends FormRequest
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
            'params.taxonAct.completo.categoria.IdNivel1' => ['required', 'integer'],
            'params.taxonActRel.completo.categoria.IdNivel1' => ['required', 'integer'],
        ];
    }

    public function withValidator($validator){
        //Log::info('Request completo:', $this->all());

        $validator->after(function ($validator){

            $taxonAct = $this->input('params.taxonAct', []); 
            $taxonRel = $this->input('params.taxonActRel', []);
            $idTipoRel = $this->input('params.tipRelacion', 0);

            // === Filtrado de registros validos ===
            $filtraVal = $taxonAct['relaciones'];

             // === Filtrado de registros relacionados ===
            $filtraRel = $taxonRel['relaciones'];

            // === Conteo de registros validos ===
            $contValidoAct = $filtraVal->contains(function ($rel) {
                return in_array(data_get($rel, 'Nombrecompleto.estatus'), ['Válido', 'Correcto'])
                        || data_get($rel, 'TipoRelacion.idTipoRel' == 2);
            });

            // === Conteo de registros relacionados ===
            $conValidoRel = $filtraRel->contains(function ($rel) {
                return in_array(data_get($rel, 'Nombrecompleto.estatus'), ['Válido', 'Correcto'])
                        || data_get($rel, 'TipoRelacion.idTipoRel' == 2);
            });

            $sistClasAct = data_get($taxonAct, 'completo.SistClasCatDicc');
            $sistClasRel = data_get($taxonRel, 'completo.SistClasCatDicc');
            $categoriaAct = data_get($taxonAct, 'completo.categoria.NombreCategoriaTaxonomica');
            $categoriaRel = data_get($taxonRel, 'completo.categoria.NombreCategoriaTaxonomica');
            $nivelActNiv1 = data_get($taxonAct, 'completo.categoria.IdNivel1');
            $nivelRelNiv1 = data_get($taxonRel, 'completo.categoria.IdNivel1');
            $nivelActNiv3 = data_get($taxonAct, 'completo.categoria.IdNivel3');
            $nivelRelNiv3 = data_get($taxonRel, 'completo.categoria.IdNivel3');

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

            //Valida que el sistema de clasificación no sea el mismo en ambos taxones
            if($sistClasAct === $sistClasRel){
                $validator->errors()->add('validos', 'El sistema de clasifición no debe de ser igual en ambos taxones');
            }

            //Valida que el sistema de clasificación no se a NA o ND 
            if($sistClasAct === 'NA' || $sistClasAct === 'ND' ||
               $sistClasRel === 'NA' || $sistClasRel === 'ND'){
                $validator->errors()->add('validos', 'El sistema de clasificación no debe de ser NA o ND en alguno de los dos taxones');
            }

            //Valida que la categoria taxonomica sea la misma en ambos taxones
            if ($categoriaAct === $categoriaRel){
                $validator->errors()->add('validos', 'La categoria taxonomica no es la misma en ambos taxones');
            } 

            //Valida que ambos taxones sean de categoria genero o superiores
            if(($nivelActNiv1 < 7 && $nivelActNiv3 === 0) || ($nivelRelNiv1 < 7 && $nivelRelNiv1 === 0)){
                $validator->errors()->add('validos', 'La categoria de ambos taxones debe ser genero o superior');
            }
        });
    }
}
