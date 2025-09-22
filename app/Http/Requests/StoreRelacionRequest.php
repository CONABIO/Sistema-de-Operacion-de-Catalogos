<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;

class StoreRelacionRequest extends FormRequest
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
            'tipRelacion' => ['required', 'integer'],
            'taxonAct.id' => ['required', 'integer', 'exists:taxones,id'],
            'taxonActRel.id' => ['required', 'integer', 'exists:taxones,id'],
            'taxonAct.estatus' => ['required', 'string'],
            'taxonActRel.estatus' => ['required', 'string'],
            'taxonAct.completo.categoria.IdNivel1' => ['required', 'integer'],
            'taxonActRel.completo.categoria.IdNivel' => ['required', 'integer'],
        ];
    }

    public function withValidator($validator){
        //Log::info('Request completo:', $this->all());

        $validator->after(function ($validator){
            $taxonAct = $this->input('taxonAct', []); 
            $taxonRel = $this->input('taxonActRel', []);

            $estatusAct = data_get($taxonAct, 'estatus');
            $estatusRel = data_get($taxonRel, 'estatus');
            $idNivelAct = data_get($taxonAct, 'completo', 0);
            $idNivelRel = data_get($taxonRel, 'completo.categoria.0.IdNivel1', 0);

            Log::info('Estos son los valores recuperados');
            Log::info('Estatus actual');
            Log::info($taxonAct);
            Log::info('Estatus relacionado');
            Log::info($taxonRel);

            // 1. No deben tener el mismo estatus
            if ($estatusAct && $estatusRel && $estatusAct === $estatusRel) {
                Log::info('Entre a la funcion estatus:');
                $validator->errors()->add('estatus', 'El taxón actual y el taxón a relacionar no puede tener el mismo estatus.');
            }

            // 2. Si taxonAct es "Sinonimo", no debe tener más de un válido relacionado
            $contValido = collect(data_get($taxonAct, 'relaciones', []))->some(function ($rel) {
                Log::info('Entre a validar valido correcto:');
                return in_array(data_get($rel, 'NombreCompleto.estatus'), ['Válido', 'Correcto']);
            });

            if ($estatusAct === "Sinonimo" && $contValido && $estatusRel === "Válido") {
                Log::info('Entre a validar sinonimo');
                $validator->errors()->add('estatus', 'El taxón actual ya tiene una relación con un taxón válido.');
            }

            // 3. No puede ser ND
            if ($estatusAct === "ND") {
                Log::info('Entre a validar ND');
                $validator->errors()->add('estatus', 'El taxón actual tiene estatus ND y no puede tener relaciones de sinonimia.');
            }

            // 4. No se permiten relaciones si alguno es superior a familia
            if ($idNivelAct < 5 || $idNivelRel < 5) {
                $validator->errors()->add('categoria', 'No se puede tener relaciones de sinonimia en taxones de categoría superior a familia.');
            }
        });
    }
}
