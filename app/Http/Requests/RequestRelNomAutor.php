<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class RequestRelNomAutor extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    // Funcion para convertir de null a vacio solo para campos específicos
    protected function prepareForValidation()
    {
        $this->merge([
            'CadInicio' => $this->textoInicio ?? "",
            'CadFinal' => $this->textoInicio ?? "",
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'relNombreAutor.listAutor' => 'required',
            'relNombreAutor.listAutor.*.IdAutorTaxon' => ['required',
                                                        'integer',
                                                        Rule::exists('catcentral.AutorTaxon', 'IdAutorTaxon')],
            'relNombreAutor.listAutor.*.CadInicio' => 'string|nullable|max:15',
            'relNombreAutor.listAutor.*.CadFinal' => 'string|nullable|max:15',
        ];
    }

    /**
     * Para el control de los mensajes personalizados en caso de error 
     */
    public function messages(): array
    {
        return[
            '*.required' => 'Debe indicar por lo menos un autor relacionado.',
            'relNombreAutor.listAutor.required' => 'Debe proporcionar al menos un autor.',
            'relNombreAutor.listAutor.*.IdAutorTaxon.required' => 'Se debe ingresar la relación entre el nombre taxonómico y el autor del taxón.',
            'relNombreAutor.listAutor.*.IdAutorTaxon.integer' => 'El ID del autor debe de ser de tipo numérico.',
            'relNombreAutor.listAutor.*.IdAutorTaxon.exists' => 'El ID del autor no existe en el catálogo.',
            'relNombreAutor.listAutor.*.CadInicio' => 'El valor de inicio de la autoridad debe de ser de tipo cadena de caracteres.',
            'relNombreAutor.listAutor.*.CadInicio' => 'El número máximo de caracteres del texto de inicio debe de ser como máximo de 15 caracteres.',
            'relNombreAutor.listAutor.*.CadFinal' => 'El valor de final de la autoridad debe de ser de tipo cadena de caracteres.',
            'relNombreAutor.listAutor.*.CadFinal' => 'El número máximo de caracteres del texto final debe de ser como máximo de 15 caracteres.'
        ];
    }

    /**
     * Aqui se controla la respuesta en caso de que se presente un error 
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'status' => 422,
            'message' => 'Error de validación',
            'errors' => $validator->errors()
        ], 422));
    }
}