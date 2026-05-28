<?php

namespace App\Http\Controllers;

use App\Models\ObjetoExterno;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use App\Models\Mime;

class ObjetoExternoController extends Controller
{

    public function index()
    {
        return Inertia::render('Socat/ObjetosExternos/indexObjetos');
    }


    public function apiIndex(Request $request)
    {
        $query = ObjetoExterno::with('mime');

        if ($request->has('filters')) {
            $filters = $request->filters;
            if (isset($filters['NombreObjeto']) && !empty($filters['NombreObjeto'])) {
                $query->where('NombreObjeto', 'like', '%' . $filters['NombreObjeto'] . '%');
            }
        }

        if ($request->has('sort_by')) {
            $direction = $request->input('sort_dir', 'asc');
            $query->orderBy($request->sort_by, $direction);
        } else {
            $query->orderBy('NombreObjeto', 'asc');
        }
        $perPage = $request->input('per_page', 100);
        $objetos = $query->paginate($perPage);

        return response()->json($objetos);
    }



    public function store(Request $request)
    {
        $rules = [
            'NombreObjeto' => 'required|string|max:100|unique:catcentral.ObjetoExterno,NombreObjeto',
            'IdMime' => 'required',
            'NombreSitio' => 'nullable|string|max:255',
            'Ruta' => 'nullable|string|max:255',
            'Protocolo' => 'nullable|string|max:10',
            'Usuario' => 'nullable|string|max:15',
            'Password' => 'nullable|string|max:15',
            'UnidadLogica' => 'nullable|string|max:1',
            'Autor' => 'nullable|string|max:255',
            'Institucion' => 'nullable|string|max:255',
            'Titulo' => 'nullable|string|max:255',
            'Fecha' => 'nullable|date',
            'Observaciones' => 'nullable|string|max:255',
        ];

        $messages = [
            'NombreObjeto.required' => 'El nombre del archivo es obligatorio.',
            'NombreObjeto.unique' => 'El objeto externo que desea ingresar ya existe.',
        ];
        $validatedData = $request->validate($rules, $messages);
        $idMimeOriginal = $request->input('IdMime');
        if (!is_numeric($idMimeOriginal)) {
            $extension = strtoupper($idMimeOriginal);
            $mimeEncontrado = Mime::where('Extension', $extension)->first();
            if ($mimeEncontrado) {
                $validatedData['IdMime'] = $mimeEncontrado->IdMime;
            } else {
                $nuevoMime = Mime::create([
                    'MIME' => $extension . " FILE",
                    'Extension' => $extension,
                    'FechaCaptura' => now(),
                    'FechaModificacion' => '9999-12-31 00:00:00',
                    'IdOriginal' => null,
                    'Catalogo' => null
                ]);
                $validatedData['IdMime'] = $nuevoMime->IdMime;
            }
        }
        $validatedData['FechaCaptura'] = now();
        ObjetoExterno::create($validatedData);

        return response()->json(['message' => 'Objeto externo y nuevo tipo de archivo guardados.'], 201);
    }


    public function update(Request $request, ObjetoExterno $objetoExterno)
    {
        $validatedData = $request->validate([
            'NombreObjeto' => ['required', 'string', 'max:100', Rule::unique('catcentral.ObjetoExterno')->ignore($objetoExterno->IdObjetoExterno, 'IdObjetoExterno')],
            'IdMime' => 'required|integer|exists:catcentral.MIME,IdMime',
            'NombreSitio' => 'nullable|string|max:255',
            'Ruta' => 'nullable|string|max:255',
            'Protocolo' => 'nullable|string|max:10',
            'Usuario' => 'nullable|string|max:15',
            'Password' => 'nullable|string|max:15',
            'UnidadLogica' => 'nullable|string|max:1',
            'Titulo' => 'required|string|max:255',
            'Autor' => 'nullable|string|max:255',
            'Institucion' => 'nullable|string|max:255',
            'Fecha' => 'nullable|date',
            'Observaciones' => 'nullable|string|max:255',
        ]);
        $validatedData['FechaModificacion'] = now();
        $objetoExterno->update($validatedData);
        return response()->json(['message' => 'Objeto externo actualizado correctamente.']);
    }


    public function destroy(ObjetoExterno $objetoExterno)
    {
        try {
            $objetoExterno->delete();
            return response()->json(['message' => 'Objeto externo eliminado correctamente.']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'No se puede eliminar el objeto externo porque está asociado a otros registros.'], 409);
        }
    }
}
