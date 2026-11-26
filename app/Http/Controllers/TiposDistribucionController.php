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

    public function cargaTiposDist(Request $request){

        if($request->origen === 'caracteristicas'){
            $tipoDistrib = TipoDistribucion::orderBy('Descripcion')
                                        ->get();
        }
        else {
            $tipoDistrib = TipoDistribucion::orderBy('Descripcion')
                                        ->select('IdTipoDistribucion as id', 'Descripcion as descripcion')
                                        ->get();
        }
        return response()->json($tipoDistrib);
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
        if (!$tipoDistribucion){
            return response()->json(['message' => 'Not found'], 404);
        }

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
        if (!$registroReferencia){
            return response()->json(['page' => 1]);
        }

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
        $tipoDistribucion = TipoDistribucion::find($IdTipoDistribucion);

        if (!$tipoDistribucion) {
            Log::warning("TipoDistribucion no encontrado con ID: {$IdTipoDistribucion}");
            return response()->json(['message' => 'Tipo de distribución no encontrado'], 404);
        }

        try {
            $conexion = 'catcentral';
            if (DB::connection($conexion)->getSchemaBuilder()->hasTable('RelNombreRegion')) {
                $enNombreRegion = DB::connection($conexion)->table('RelNombreRegion')
                    ->where('IdTipoDistribucion', $IdTipoDistribucion)
                    ->exists();

                if ($enNombreRegion) {
                    return response()->json([
                        'message' => 'No se puede eliminar el tipo de distribución está asignado a especies por región.',
                        'origen' => 'RelNombreRegion'
                    ], 409);
                }
            }

            if (DB::connection($conexion)->getSchemaBuilder()->hasTable('RelNombreCatalogoRegion')) {
                $enNombreCatRegion = DB::connection($conexion)->table('RelNombreCatalogoRegion')
                    ->where('IdTipoDistribucion', $IdTipoDistribucion)
                    ->exists();

                if ($enNombreCatRegion) {
                    return response()->json([
                        'message' => 'No se puede eliminar el tipo de distribución está asignado a catálogos de especies por región.',
                        'origen' => 'RelNombreCatalogoRegion'
                    ], 409);
                }
            }
            $tipoDistribucion->delete();
            return response()->json([
                'message' => 'Tipo de distribución eliminado exitosamente'
            ], 200);

        } catch (\Exception $e) {
            Log::error("Error al eliminar TipoDistribucion con ID {$IdTipoDistribucion}: " . $e->getMessage());
            return response()->json([
                'message' => 'Error al eliminar el tipo de distribución: ' . $e->getMessage()
            ], 500);
        }
    }


    public function relaciones($IdTipoDistribucion)
    {
        $tipoDistribucion = TipoDistribucion::find($IdTipoDistribucion);

        if (!$tipoDistribucion) {
            return response()->json(['message' => 'Tipo de distribución no encontrado'], 404);
        }

        $relaciones = DB::table('RelNombreRegion as rnr')
            ->join('Nombre as n', 'n.IdNombre', '=', 'rnr.IdNombre')
            ->leftJoin('Region as r', 'r.IdRegion', '=', 'rnr.IdRegion')
            ->where('rnr.IdTipoDistribucion', $IdTipoDistribucion)
            ->select('n.IdNombre', 'n.Nombre', 'r.IdRegion', 'r.NombreRegion')
            ->distinct()
            ->orderBy('n.Nombre')
            ->get();

        return response()->json(['relaciones' => $relaciones]);
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
