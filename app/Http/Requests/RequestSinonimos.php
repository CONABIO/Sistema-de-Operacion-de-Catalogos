<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use App\Models\Nombre;
use Illuminate\Support\Facades\DB;

class RequestSinonimos extends FormRequest
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

            if( $idTipoRel == 2){
                // === Filtrado de registros validos ===
                $filtraVal = collect($taxonAct['relaciones'] ?? [])
                            ->filter(function ($item) use ($idTipoRel) {
                    return data_get($item, 'TipoRelacion.idTipoRel') == 1;
                });

                // === Filtrado de registros relacionados ===
                $filtraRel = collect($taxonRel['relaciones'] ?? [])
                            ->filter(function ($item) use ($idTipoRel) {
                    return data_get($item, 'TipoRelacion.idTipoRel') == 1;
                });
            }else{
                // === Filtrado de registros validos ===
                $filtraVal = collect($taxonAct['relaciones'] ?? [])
                            ->filter(function ($item) use ($idTipoRel) {
                    return data_get($item, 'TipoRelacion.idTipoRel') == $idTipoRel;
                });

                // === Filtrado de registros relacionados ===
                $filtraRel = collect($taxonRel['relaciones'] ?? [])
                            ->filter(function ($item) use ($idTipoRel) {
                    return data_get($item, 'TipoRelacion.idTipoRel') == $idTipoRel;
                });
            }

            // === Conteo de registros validos ===
            $contValidoAct = $filtraVal->contains(function ($rel) {
                return in_array(data_get($rel, 'Nombrecompleto.estatus'), ['Válido', 'Correcto']);
            });

            // === Conteo de registros relacionados ===
            $conValidoRel = $filtraRel->contains(function ($rel) {
                return in_array(data_get($rel, 'Nombrecompleto.estatus'), ['Válido', 'Correcto']);
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
            
            // 1. No deben tener el mismo estatus
            if ($estatusAct && $estatusRel && $estatusAct === $estatusRel) {
                Log::info("No pase la validacion 1");
                $validator->errors()->add('estatus', 'El taxón actual y el taxón a relacionar no puede tener el mismo estatus.');
            }

            // 2. Si taxonAct es "Sinonimo", no debe tener más de un válido relacionado
            $contValido = collect(data_get($taxonAct, 'relaciones', []))->some(function ($rel) {
                return in_array(data_get($rel, 'NombreCompleto.estatus'), ['Válido', 'Correcto']);
            });

            if ($estatusAct === "Sinonimo" && $contValido && $estatusRel === "Válido") {
                Log::info("No pase la validacion 2");
                $validator->errors()->add('estatus', 'El taxón actual ya tiene una relación con un taxón válido.');
            }

            // 3. No puede ser ND
            if ($estatusAct === "ND") {
                Log::info("No pase la validacion 3");
                $validator->errors()->add('estatus', 'El taxón actual tiene estatus ND y no puede tener relaciones de sinonimia.');
            }

            // 4. No se permiten relaciones si alguno es superior a familia
            if ($idNivelAct < 5 || $idNivelRel < 5) {
                Log::info("No pase la validacion 4");
                $validator->errors()->add('categoria', 'No se puede tener relaciones de sinonimia en taxones de categoría superior a familia.');
            }

            //5. No se permite la relación si el taxón relacionado ya cuenta con un valido
            if ($contValidoAct > 0 || $conValidoRel > 0){
                Log::info("No se paso la validacion 5");
                $validator->errors()->add('validos', 'No se puede tener relaciones de sinonimia si el taxón relacionado tiene una relación valida.');
            } 
        });
    }
}
