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


    public function store(Request $request)
    {
        // Validación de duplicado global
        $existing = TipoDistribucion::where(DB::raw('lower(Descripcion)'), strtolower($request->Descripcion))->first();

        if ($existing) {
            return response()->json([
                'status' => 400,
                'message' => 'Ya existe un tipo de distribución con esa descripción.',
                'idExistente' => $existing->IdTipoDistribucion
            ], 400);
        }

        try {
            $tipoDistribucion = new TipoDistribucion();
            $tipoDistribucion->Descripcion = $request->input('Descripcion');
            $tipoDistribucion->save();

            return response()->json([
                'data' => $tipoDistribucion,
                'message' => 'Tipo de distribución creado exitosamente',
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al crear'], 500);
        }
    }

    public function update(Request $request, $IdTipoDistribucion)
    {
        $tipoDistribucion = TipoDistribucion::find($IdTipoDistribucion);
        if (!$tipoDistribucion) return response()->json(['message' => 'Not found'], 404);

        // Validación de duplicado global (excluyendo el actual)
        $existing = TipoDistribucion::where(DB::raw('lower(Descripcion)'), strtolower($request->Descripcion))
            ->where('IdTipoDistribucion', '!=', $IdTipoDistribucion)
            ->first();

        if ($existing) {
            return response()->json([
                'status' => 400,
                'message' => 'Ya existe otro registro con esa descripción.',
                'idExistente' => $existing->IdTipoDistribucion
            ], 400);
        }

        try {
            $tipoDistribucion->Descripcion = $request->input('Descripcion');
            $tipoDistribucion->save();
            return response()->json(['data' => $tipoDistribucion, 'message' => 'Actualizado exitosamente'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al actualizar'], 500);
        }
    }

    public function obtenerPaginaDeTipoDistribucion(Request $request)
    {
        $id = $request->id;
        $perPage = $request->perPage ?? 100;
        $sortBy = $request->sortBy ?? 'Descripcion';
        $sortOrder = $request->sortOrder ?? 'asc';

        $registroReferencia = TipoDistribucion::find($id);
        if (!$registroReferencia) return response()->json(['page' => 1]);

        $operador = (strtolower($sortOrder) === 'asc') ? '<' : '>';

        $posicion = TipoDistribucion::where(function ($query) use ($registroReferencia, $operador, $sortBy) {
            $valor = $registroReferencia->{$sortBy};
            $query->where(DB::raw("LOWER(`$sortBy`)"), $operador, strtolower($valor))
                ->orWhere(function ($q) use ($registroReferencia, $sortBy, $valor) {
                    $q->where(DB::raw("LOWER(`$sortBy`)"), '=', strtolower($valor))
                        ->where('IdTipoDistribucion', '<', $registroReferencia->IdTipoDistribucion);
                });
        })->count();

        $pagina = floor($posicion / $perPage) + 1;
        return response()->json(['page' => (int)$pagina]);
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
