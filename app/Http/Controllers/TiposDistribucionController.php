<?php

namespace App\Http\Controllers;

use App\Models\TipoDistribucion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class TiposDistribucionController extends Controller
{

    public function index()
    {
        Log::info("Mostrando el índice de TipoDistribucion");

        $tipoDistribucion = TipoDistribucion::orderBy('Descripcion')->paginate(100);

        return Inertia::render('Socat/TiposDistribucion/TipoDistribucion', [
            'datosTipoDistribucion' => [
                'data' => $tipoDistribucion->items(),
                'totalItems' => $tipoDistribucion->total(),
                'currentPage' => $tipoDistribucion->currentPage(),
                'nextPageUrl' => $tipoDistribucion->nextPageUrl(),
                'prevPageUrl' => $tipoDistribucion->previousPageUrl(),
            ],
        ]);
    }


    public function create()
    {
        return response()->json(['message' => 'Método create no implementado'], 501);
    }


    public function store(Request $request)
    {
        Log::info("Creando un nuevo TipoDistribucion");

        $request->validate([
            'Descripcion' => 'required|string|max:255',
        ]);

        try {
            $tipoDistribucion = new TipoDistribucion();
            $tipoDistribucion->Descripcion = $request->input('Descripcion');
            $tipoDistribucion->save();

            return response()->json([
                'data' => $tipoDistribucion,
                'message' => 'TipoDistribucion creado exitosamente',
            ], 201);
        } catch (\Exception $e) {
            Log::error("Error al crear TipoDistribucion: {$e->getMessage()}");
            return response()->json(['message' => 'Error al crear TipoDistribucion'], 500);
        }
    }


    public function show($id)
    {
        $tipoDistribucion = TipoDistribucion::find($id);

        if (!$tipoDistribucion) {
            return response()->json(['message' => 'TipoDistribucion no encontrado'], 404);
        }

        return response()->json($tipoDistribucion);
    }


    public function edit($id)
    {
        return response()->json(['message' => 'Método edit no implementado'], 501);
    }


    public function update(Request $request, $IdTipoDistribucion)
    {
        Log::info("Actualizando TipoDistribucion con ID: {$IdTipoDistribucion}");
        // dd($request->all());
        $tipoDistribucion = TipoDistribucion::find($IdTipoDistribucion);

        if (!$tipoDistribucion) {
            Log::warning("TipoDistribucion no encontrado con ID: {$IdTipoDistribucion}");
            return response()->json(['message' => 'TipoDistribucion no encontrado'], 404);
        }

        $request->validate([
            'Descripcion' => 'required|string|max:255',

        ]);

        try {
            $tipoDistribucion->Descripcion = $request->input('Descripcion');
            $tipoDistribucion->save();

            Log::info("TipoDistribucion actualizado con ID: {$IdTipoDistribucion}");
            return response()->json([
                'data' => $tipoDistribucion,
                'message' => 'TipoDistribucion actualizado exitosamente',
            ], 200);
        } catch (\Exception $e) {
            Log::error("Error al actualizar TipoDistribucion con ID: {$IdTipoDistribucion}: {$e->getMessage()}");
            return response()->json(['message' => 'Error al actualizar TipoDistribucion'], 500);
        }
    }


    public function destroy($IdTipoDistribucion)
    {
        Log::info("Eliminando TipoDistribucion con ID: {$IdTipoDistribucion}");
        try {
            $tipoDistribucion = TipoDistribucion::findOrFail($IdTipoDistribucion);
            $tipoDistribucion->delete();

            Log::info("TipoDistribucion eliminado con ID: {$IdTipoDistribucion}");
            return response()->json(['message' => 'TipoDistribucion eliminado exitosamente'], 200);
        } catch (\Exception $e) {
            Log::error("Error al eliminar TipoDistribucion con ID: {$IdTipoDistribucion}: {$e->getMessage()}");
            return response()->json(['message' => 'Error al eliminar TipoDistribucion'], 500);
        }
    }

    public function buscaTipoDistribucion(Request $request)
    {
        $validated = $request->validate([
            'filtros' => 'nullable|array',
            'filtros.Descripcion' => 'nullable|string',
            'tipo_busqueda' => 'nullable|string|in:inicia,contiene,termina',
            'page' => 'nullable|integer|min:1',
            'perPage' => 'nullable|integer|min:1',
            'sortBy' => 'nullable|string|in:Descripcion',
            'sortOrder' => 'nullable|string|in:asc,desc,ascending,descending',
        ]);

        $filtros = $validated['filtros'] ?? [];
        $tipo_busqueda = $validated['tipo_busqueda'] ?? 'contiene';
        $page = $validated['page'] ?? 1;
        $perPage = $validated['perPage'] ?? 100;
        $sortBy = $validated['sortBy'] ?? null;
        $sortOrderInput = $validated['sortOrder'] ?? null;

        $query = TipoDistribucion::query();

        foreach ($filtros as $campo => $valor) {
            if (!empty($valor)) {
                if ($campo === 'Descripcion') {
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
            $query->orderByRaw('LOWER(`Descripcion`) asc');
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
