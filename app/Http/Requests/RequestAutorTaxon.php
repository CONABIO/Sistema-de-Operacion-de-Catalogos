<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class RequestAutorTaxon extends FormRequest
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
            'nombreCompleto' => $this->nombreCompleto ?? "",
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
            'nombreAutoridad' => 'required|string|min:1|max:100',
            'nombreCompleto' => 'string|nullable',
            'grupoTaxonomico' => 'required|string|min:1|max:255',
        ];
    }

    /**
     * Para el control de los mensajes personalizados en caso de error 
     */
    public function messages(): array
    {
        return[
            'nombreAutoridad.required' => 'El nombre de la autoridad es obligatorio.',
            'nombreAutoridad.max' => 'El nombre de la autoridad no debe exceder los 100 caracteres.',
            'grupoTaxonomico.required' => 'El grupo taxonómico es obligatorio.',
            'grupoTaxonomico.max' => 'El grupo taxonómico no debe exceder los 255 caracteres.'
        ];
    }

    /**
     * Aqui se controla la respuesta en caso de que se presente un error 
     */
    protected function failedValidation(Validator $validator)
    {
        Log::info("Entre a esta seccion por no pasar taxonomico");
        throw new HttpResponseException(response()->json([
            'status' => 422,
            'message' => 'Error de validación',
            'errors' => $validator->errors()
        ], 422));
    }
}
