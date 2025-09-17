<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use App\Models\Nombre;
use Illuminate\Support\Facades\DB;

class RequestBasonimos extends FormRequest
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

            $estatusAct = data_get($taxonAct, 'estatus');
            $estatusRel = data_get($taxonRel, 'estatus');
            $idNivelAct = data_get($taxonAct, 'completo.categoria.IdNivel1', 0);
            $idNivelRel = data_get($taxonRel, 'completo.categoria.IdNivel1', 0);

            $idAct = data_get($taxonAct, 'id');
            $idRel = data_get($taxonRel, 'id');

            $exists = DB::connection('catcentral')
                ->table('Nombre_Relacion')
                ->where('IdNombre', $idAct)
                ->where('IdNombreRel', $idRel)
                ->where('IdTipoRelacion', $idTipoRel)
                ->exists();

            if($exists){
                log::info("La relación que intenta crear ya existe");
                $validator->errors()->add('relacion', 'La relación entre estos taxones ya existe.');
            }

            if(($estatusAct === 'Válido' || $estatusAct === 'Correcto') && $idNivelAct < 7){
                $validator->errors()->add('validos', 'El taxón actual es de categoria superior a especie por lo cual no se puede generar la relación de basonimia');
            }

            if(($estatusRel === 'Válido' || $estatusRel === 'Correcto') && $idNivelRel < 7){
                $validator->errors()->add('validos', 'El taxón a relacionar es de categoria superior a especie por lo cual no se puede generar la relación de basonimia');
            }

            //5. No se permite la relación si el taxón relacionado ya cuenta con un valido
            if ($contValidoAct > 0 || $conValidoRel > 0){
                Log::info("No se paso la validacion 5");
                $validator->errors()->add('validos', 'No se puede tener relaciones de basonimia si el taxón sininimo ya tiene una relación valida.');
            } 
        });
    }
}
