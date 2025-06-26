<?php

namespace App\Http\Controllers;

use App\Models\NomComun;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class NombreComunController extends Controller
{
    public function index()
    {
        Log::info("Mostrando el índice de Nombres Comunes");

        $nombresComunes = NomComun::orderBy('NomComun')->paginate(100);

        return Inertia::render('Socat/Nombres/NombreComun', [
            'datosNombreComun' => [
                'data' => $nombresComunes->items(),
                'totalItems' => $nombresComunes->total(),
                'currentPage' => $nombresComunes->currentPage(),
                'nextPageUrl' => $nombresComunes->nextPageUrl(),
                'prevPageUrl' => $nombresComunes->previousPageUrl(),
            ],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return response()->json(['message' => 'Método create no implementado'], 501);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        Log::info("Creando un nuevo Nombre Común");

        $request->validate([
            'NomComun' => 'required|string|max:255',
            'Observaciones' => 'nullable|string',
            'Lengua' => 'nullable|string',
            'FechaCaptura' => 'nullable|date',
            'FechaModificacion' => 'nullable|date',
            'IdOriginal' => 'nullable|integer',
            'Catalogo' => 'nullable|string',
        ]);

        try {
            $nombreComun = new NomComun();
            $nombreComun->NomComun = $request->input('NomComun');
            $nombreComun->Observaciones = $request->input('Observaciones');
            $nombreComun->Lengua = $request->input('Lengua');
            $nombreComun->FechaCaptura = $request->input('FechaCaptura');
            $nombreComun->FechaModificacion = $request->input('FechaModificacion');
            $nombreComun->IdOriginal = $request->input('IdOriginal');
            $nombreComun->Catalogo = $request->input('Catalogo');
            $nombreComun->save();

            return response()->json([
                'data' => $nombreComun,
                'message' => 'Nombre Común creado exitosamente',
            ], 201);
        } catch (\Exception $e) {
            Log::error("Error al crear Nombre Común: {$e->getMessage()}");
            return response()->json(['message' => 'Error al crear Nombre Común'], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $nombreComun = NomComun::find($id);

        if (!$nombreComun) {
            return response()->json(['message' => 'Nombre Común no encontrado'], 404);
        }

        return response()->json($nombreComun);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        return response()->json(['message' => 'Método edit no implementado'], 501);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $IdNomComun
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $IdNomComun)
    {
        $nombreComun = NomComun::find($IdNomComun);

        if (!$nombreComun) {
            Log::warning("Nombre Común no encontrado con ID: {$IdNomComun}");
            return response()->json(['message' => 'Nombre Común no encontrado'], 404);
        }

        $request->validate([
            'NomComun' => 'required|string|max:255',
            'Observaciones' => 'nullable|string',
            'Lengua' => 'nullable|string',
            'FechaCaptura' => 'nullable|date',
            'FechaModificacion' => 'nullable|date',
            'IdOriginal' => 'nullable|integer',
            'Catalogo' => 'nullable|string',
        ]);

        try {
            $nombreComun->NomComun = $request->input('NomComun');
            $nombreComun->Observaciones = $request->input('Observaciones');
            $nombreComun->Lengua = $request->input('Lengua');
            $nombreComun->FechaCaptura = $request->input('FechaCaptura');
            $nombreComun->FechaModificacion = $request->input('FechaModificacion');
            $nombreComun->IdOriginal = $request->input('IdOriginal');
            $nombreComun->Catalogo = $request->input('Catalogo');
            $nombreComun->save();

            Log::info("Nombre Común actualizado con ID: {$IdNomComun}");
            return response()->json([
                'data' => $nombreComun,
                'message' => 'Nombre Común actualizado exitosamente',
            ], 200);
        } catch (\Exception $e) {
            Log::error("Error al actualizar Nombre Común con ID: {$IdNomComun}: {$e->getMessage()}");
            return response()->json(['message' => 'Error al actualizar Nombre Común'], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $IdNomComun
     * @return \Illuminate\Http\Response
     */
    public function destroy($IdNomComun)
    {
        Log::info("Eliminando Nombre Común con ID: {$IdNomComun}");
        try {
            $nombreComun = NomComun::findOrFail($IdNomComun);
            $nombreComun->delete();

            Log::info("Nombre Común eliminado con ID: {$IdNomComun}");
            return response()->json(['message' => 'Nombre Común eliminado exitosamente'], 200);
        } catch (\Exception $e) {
            Log::error("Error al eliminar Nombre Común con ID: {$IdNomComun}: {$e->getMessage()}");
            return response()->json(['message' => 'Error al eliminar Nombre Común'], 500);
        }
    }

    public function buscaNombreComun(Request $request)
    {
        $validated = $request->validate([
            'filtros' => 'nullable|array',
            'filtros.NomComun' => 'nullable|string',
            'filtros.Observaciones' => 'nullable|string',
            'filtros.Lengua' => 'nullable|string',
            'tipo_busqueda' => 'nullable|string|in:inicia,contiene,termina',
            'page' => 'nullable|integer|min:1',
            'perPage' => 'nullable|integer|min:1',
            'sortBy' => 'nullable|string|in:NomComun,Observaciones,Lengua',
            'sortOrder' => 'nullable|string|in:asc,desc,ascending,descending',
        ]);

        $filtros = $validated['filtros'] ?? [];
        $tipo_busqueda = $validated['tipo_busqueda'] ?? 'contiene';
        $page = $validated['page'] ?? 1;
        $perPage = $validated['perPage'] ?? 100;
        $sortBy = $validated['sortBy'] ?? null;
        $sortOrderInput = $validated['sortOrder'] ?? null;

        $query = NomComun::query();

        foreach ($filtros as $campo => $valor) {
            if (!empty($valor)) {
                if (in_array($campo, ['NomComun', 'Observaciones', 'Lengua'])) {
                    switch ($tipo_busqueda) {
                        case 'inicia':
                            $query->where(DB::raw("LOWER(`{$campo}`)"), 'like', strtolower($valor) . '%');
                            break;
                        case 'termina':
                            $query->where(DB::raw("LOWER(`{$campo}`)"), 'like', '%' . strtolower($valor));
                            break;
                        default:
                            $query->where(DB::raw("LOWER(`{$campo}`)"), 'like', '%' . strtolower($valor) . '%');
                            break;
                    }
                }
            }
        }

        if ($sortBy && $sortOrderInput) {
            $sortOrder = (in_array($sortOrderInput, ['ascending', 'asc'])) ? 'asc' : 'desc';
            $query->orderByRaw("LOWER(`{$sortBy}`) {$sortOrder}");
        } else {
            $query->orderByRaw('LOWER(`NomComun`) asc');
        }

        $result = $query->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'data' => $result->items(),
            'totalItems' => $result->total(),
            'currentPage' => $result->currentPage(),
            'nextPageUrl' => $result->nextPageUrl(),
            'prevPageUrl' => $result->previousPageUrl(),
        ]);
    }
}
