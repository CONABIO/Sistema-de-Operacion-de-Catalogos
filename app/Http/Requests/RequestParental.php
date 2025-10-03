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
            'params.taxonAct.completo.categoria.IdNivel1' => ['required', 'integer'],
            'params.taxonActRel.completo.categoria.IdNivel1' => ['required', 'integer'],
            'params.taxonAct.completo.categoria.IdNivel3' => ['required', 'integer'],
            'params.taxonActRel.completo.categoria.IdNivel3' => ['required', 'integer'],
        ];
    }

    public function withValidator($validator){
        //Log::info('Request completo:', $this->all());

        $validator->after(function ($validator){

            $taxonAct = $this->input('params.taxonAct.completo.categoria', []); 
            $taxonRel = $this->input('params.taxonActRel.completo.categoria', []);
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

            //Valida que el taxon Actual sea un híbrido
            if(($taxonAct['NombreCategoriaTaxonomica'] ?? '') !== "híbrido"){
                $validator->errors()->add('validos', 'No es posible asociar un parental a un taxón que no es un híbrido');
            }

            //Valida el nivel taxonomico 
            $validaTaxon = (
                (($taxonAct['IdNivel1'] ?? 0) != 6 && ($taxonAct['IdNivel3'] ?? 0) != 0) ||
                (($taxonAct['IdNivel1'] ?? 0) != 7 && ($taxonAct['IdNivel3'] ?? 0) != 0) ||
                (($taxonActRel['IdNivel1'] ?? 0) != 6 && ($taxonActRel['IdNivel3'] ?? 0) != 0) ||
                (($taxonActRel['IdNivel1'] ?? 0) != 7 && ($taxonActRel['IdNivel3'] ?? 0) != 0)
            );

            if($validaTaxon){
                $validator->errors()->add('validos', 'No es posible asociar un parental a un taxón cuya categoría taxonómica sea diferente de género o especie');
            }
        });
    }
}
