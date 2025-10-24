<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use App\Models\Nombre;
use Illuminate\Support\Facades\DB;

class RequestHomonimo extends FormRequest   
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
            'params.taxonAct.completo.TaxonCompleto' => ['required', 'string'],
            'params.taxonActRel.completo.TaxonCompleto' => ['required', 'string']
        ];
    }

    public function withValidator($validator){

        $validator->after(function ($validator){

            $taxonAct = $this->input('params.taxonAct', []); 
            $taxonRel = $this->input('params.taxonActRel', []);
            $nomAct = $this->input('params.taxonAct.completo.TaxonCompleto'); 
            $nomRel = $this->input('params.taxonActRel.completo.TaxonCompleto');
            $idTipoRel = $this->input('params.tipRelacion', 0);

            $idAct = data_get($taxonAct, 'id');
            $idRel = data_get($taxonRel, 'id');

            $exists = DB::connection('catcentral')
                ->table('Nombre_Relacion')
                ->where('IdNombre', $idAct)
                ->where('IdNombreRel', $idRel)
                ->where('IdTipoRelacion', $idTipoRel)
                ->exists();

            //Valida que la relacion no exista 
            if($exists){
                $validator->errors()->add('relacion', 'La relación entre estos taxones ya existe.');
            }

            //Valida que el taxon Actual sea un híbrido
            if($idAct === $idRel){
                $validator->errors()->add('validos', 'Está tratando de relacionar el nombre a sí mismo, lo cual no es posible');
            }

            if($nomAct !== $nomRel ){
                $validator->errors()->add('validos', 'Está tratando de relacionar dos taxones con diferente nombre esto no es posible');
            }
        });
    }
}
