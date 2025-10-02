<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use App\Models\Nombre;

class RequestNombre extends FormRequest
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
            'nombreTaxon.IdAscenOblig' => ['required',
                               'integer',
                               Rule::exists('catcentral.Nombre', 'IdNombre')
                                        ->where(function ($query){
                                            $query->where('EstadoRegistro', 1);
                                        })],
            'nombreTaxon.IdAscendente' => ['required',
                               'integer',
                               Rule::exists('catcentral.Nombre', 'IdNombre')
                                        ->where(function ($query){
                                            $query->where('EstadoRegistro', 1);
                                        })],
            'nombreTaxon.NombreTax.anotacionTaxon' => 'string|nullable',
            'nombreTaxon.NombreTax.citaNomenclatural' => 'string|nullable|max:255',
            'nombreTaxon.NombreTax.estado' => 'integer',
            'nombreTaxon.NombreTax.estatusTax' => 'required|integer',
            'nombreTaxon.NombreTax.nivelRev' => 'string|max:10',
            'nombreTaxon.NombreTax.nombreAutoridad' => 'required|string|max:255',
            'nombreTaxon.NombreTax.nombreTaxon' => 'required|string|max:100',
            'nombreTaxon.NombreTax.otrasObservaciones' => 'string|max:255|nullable',
            'nombreTaxon.NombreTax.sistClassDicc' => 'required|string|max:255|nullable',
            'nombreTaxon.alias' => 'required|string',
            'nombreTaxon.categoria.id' => ['required',
                                           'integer',
                                          Rule::exists('catcentral.CategoriaTaxonomica','IdCategoriaTaxonomica')],
        ];
    }

    /**
     * Para el control de los mensajes personalizados en caso de error 
     */
    public function messages(): array
    {
        return[
            'nombreTaxon.IdAscenOblig.required' => 'El Id del ascendente obligatorio es requerido.',
            'nombreTaxon.IdAscenOblig.integer' => 'El Id del ascendente obligatorio debe ser un número entero.',
            'nombreTaxon.IdAscenOblig.exists' => 'El Id del ascendente obligatorio no existe o no está activo en el catálogo.',
            'nombreTaxon.IdAscendente.exists' => 'El Id del ascendente no existe o está inactivo.',
            'nombreTaxon.NombreTax.nombreTaxon.required' => 'El nombre del taxón es obligatorio.',
            'nombreTaxon.NombreTax.nombreTaxon.max' => 'El nombre del taxón debe ser de maximo 100 caracteres.',
            'nombreTaxon.NombreTax.nombreAutoridad.required' => 'El nombre de la autoridad es obligatorio.',
            'nombreTaxon.NombreTax.estatusTax.required' => 'El campo estatus es obligatorio',
            'nombreTaxon.NombreTax.nivelRev.max' => 'El nivel de revisión debe contener maximo 10 caracteres',
            'nombreTaxon.NombreTax.nombreAutoridad.required' => 'La autoridad es obligatoria.',
            'nombreTaxon.NombreTax.nombreAutoridad.max' => 'La autoridad debe ser como maximo de 255 caracteres.',
            'nombreTaxon.NombreTax.otrasObservaciones.max' => 'El campo otras observaciones debe ser de maximo 255 caracteres.',
            'nombreTaxon.NombreTax.sistClassDicc.max' => 'No es posible ingresar un taxón sin el dato del sistema de clasificación, catálogo de autoridad o diccionario',
            'nombreTaxon.NombreTax.sistClassDicc.required' => 'No es posible ingresar un taxón sin el dato del sistema de clasificación, catálogo de autoridad o diccionario.',
            'nombreTaxon.alias.required' => 'El usuario que realiza el Alta o cambio se debe indicar.',
            'nombreTaxon.categoria.id.required' => 'La categoría taxonómica es obligatoria.',
            'nombreTaxon.categoria.id.integer' => 'El ID de la categoría taxonómica debe de ser de tipo numérico.',
            'nombreTaxon.categoria.id.exists' => 'El ID de la categoría taxonómica no existe.',
        ];
    }

    /**
     * Aqui se controla la respuesta en caso de que se presente un error 
     */
    protected function failedValidation(Validator $validator)
    {
        Log::info("Entre aqui porque encontre un error");
        throw new HttpResponseException(response()->json([
            'status' => 422,
            'message' => 'Error de validación',
            'errors' => $validator->errors()
        ], 422));
    }
}