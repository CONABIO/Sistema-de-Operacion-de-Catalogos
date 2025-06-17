<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class RequestScat extends FormRequest
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
            'scat.Grupo' => 'integer|nullable',
            'scat.HomonimiaSnib' => 'string|nullable|max:255',
            'scat.IdInvasora' => 'integer|nullable',
            'scat.NivelRevision' => 'string|nullable|max:10',
            'scat.ValidacionSnib' => 'string|nullable|max:15',
            'scat.idCites' => 'string|nullable|max:255',
            'scat.idCol' => 'string|nullable|max:255',
            'scat.idIUCN' => 'integer|nullable',
        ];
    }

    /**
     * Para el control de los mensajes personalizados en caso de error 
     */
    public function messages(): array
    {
        return[
            'scat.Grupo.integer' => 'El IdGrupotaxonomico debe de ser de tipo numerico.',
            'scat.HomonimiaSnib.string' => 'La homonimia SNIB debe de ser de tipo cadena de caracteres.',
            'scat.HomonimiaSnib.max' => 'La homonimia SNIB debe de ser de maximo 255 caracteres.',
            'scat.NivelRevision.string' => 'El nivel de revisión debe de ser de tipo cadena de caracteres.',
            'scat.NivelRevision.max' => 'El nivel de revisión debe de ser de maximo 10 caracteres.',
            'scat.ValidacionSnib.string' => 'La validación SNIB debe de ser de tipo cadena de caracteres.',
            'scat.ValidacionSnib.max' => 'La validación SNIB debe de ser de maximo 15 caracteres.',
            'scat.idCites.string' => 'El id Cites debe de ser de tipo cadena de caracteres.',
            'scat.idCites.max' => 'El id Cites debe de ser de maximo 255 caracteres.',
            'scat.idCol.string' => 'El id Col debe de ser de tipo cadena de caracteres.',
            'scat.idCol.max' => 'El id Col debe de ser de maximo 255 caracteres.',
            'scat.idIUCN.integer' => 'El id IUCN debe de ser de tipo numerico.',
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