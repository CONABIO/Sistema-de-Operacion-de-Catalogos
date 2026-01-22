<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\AutorTaxon;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Http\Requests\RequestAutorTaxon;

class AutorTaxonController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $autor = AutorTaxon::orderBy('NombreAutoridad')->paginate(100);

        return Inertia::render('Socat/Autores/AutorTaxon', [
            'datosAutor' => [
                'data' => $autor->items(),
                'totalItems' => $autor->total(),
                'currentPage' => $autor->currentPage(),
                'nextPageUrl' => $autor->nextPageUrl(),
                'prevPageUrl' => $autor->previousPageUrl(),
            ],
        ]);
    }

    public function buscaAutoresRel(Request $request)
    {
        Log::info("Entre a buscar lo autores: " . $request->ids);
        $ids = explode(',', $request->ids);
        $autores = AutorTaxon::whereIn('IdAutorTaxon', $ids)->get();
        Log::info(json_encode($autores, JSON_PRETTY_PRINT));
        return response()->json([
            'status' => 200,
            'autores' => $autores
        ], 200);
    }

    //---------NO BORRAR ESTA PARTE DEL CODIGO, PUEDE SERVIR A FUTURO----------------

    /* public function buscaAutor(Request $request)
    {
        $nombre = $request->input('autor', '');

        // Realizar la consulta con el filtro de búsqueda y paginación
        $autorTax = AutorTaxon::where('NombreAutoridad', 'like', "%$nombre%")
            ->orWhere('NombreCompleto', 'like', "%$nombre%")
            ->orWhere('GrupoTaxonomico', 'like', "%$nombre%")
            ->orderBy('NombreAutoridad')
            ->paginate(100);

        return Inertia::render('Socat/Autores/AutorTaxon', [
            'datosAutor' => [
                'data' => $autorTax->items(),
                'totalItems' => $autorTax->total(),
                'currentPage' => $autorTax->currentPage(),
                'nextPageUrl' => $autorTax->nextPageUrl(),
                'prevPageUrl' => $autorTax->previousPageUrl(),
            ],
        ]);
    } */

    // -------------------------------------------------------------------------------------


    //ESTA ES OTRA MANERA DE BUSCAR PERO NO ES CON INERTIA

    public function buscaAutor(Request $request)
    {
        $validated = $request->validate([
            'filtros' => 'nullable|array',
            'filtros.NombreAutoridad' => 'nullable|string',
            'filtros.NombreCompleto' => 'nullable|string',
            'filtros.GrupoTaxonomico' => 'nullable|string',
            'tipo_busqueda' => 'nullable|string|in:inicia,contiene,termina',
            'page' => 'nullable|integer|min:1',
            'perPage' => 'nullable|integer|min:1',
            'sortBy' => 'nullable|string|in:NombreAutoridad,NombreCompleto,GrupoTaxonomico',
            'sortOrder' => 'nullable|string|in:asc,desc,ascending,descending',
        ]);

        $filtros = $validated['filtros'] ?? [];
        $tipo_busqueda = $validated['tipo_busqueda'] ?? 'contiene';
        $page = $validated['page'] ?? 1;
        $perPage = $validated['perPage'] ?? 100;
        $sortBy = $validated['sortBy'] ?? null;
        $sortOrderInput = $validated['sortOrder'] ?? null;

        $query = AutorTaxon::query();

        foreach ($filtros as $campo => $valor) {
            if (!empty($valor)) {
                if (in_array($campo, ['NombreAutoridad', 'NombreCompleto', 'GrupoTaxonomico'])) {
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
            $query->orderByRaw('LOWER(`NombreAutoridad`) asc');
        }

        $result = $query->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'data' => $result->items(),
            'total' => $result->total(),
            'totalItems' => $result->total(),
            'currentPage' => $result->currentPage(),
            'nextPageUrl' => $result->nextPageUrl(),
            'prevPageUrl' => $result->previousPageUrl(),
        ]);
    }


    public function store(RequestAutorTaxon $request)
    {

        $validatedData = $request->validated();

        try {
            $existingAutorTaxon = AutorTaxon::where(DB::raw('lower(NombreAutoridad)'), strtolower($validatedData['nombreAutoridad']))
                ->where(DB::raw('lower(GrupoTaxonomico)'), strtolower($validatedData['grupoTaxonomico']))
                ->first();

            if ($existingAutorTaxon) {
                return response()->json([
                    'status' => 400,
                    'message' => 'Ya existe un autor con el mismo Nombre de Autoridad y Grupo Taxonómico.',
                    'idExistente' => $existingAutorTaxon->IdAutorTaxon
                ], 400);
            }

            $autorTaxon = new AutorTaxon();
            $autorTaxon->NombreAutoridad = $validatedData['nombreAutoridad'];
            $autorTaxon->NombreCompleto = $validatedData['nombreCompleto'] ?? null;
            $autorTaxon->GrupoTaxonomico = $validatedData['grupoTaxonomico'];
            $autorTaxon->FechaModificacion = now()->toDateTimeString();
            $autorTaxon->save();

            return response()->json([
                'status' => 200,
                'message' => 'La autoridad taxonómica se dio de alta con éxito',
                'autorTaxon' => $autorTaxon
            ], 200);
        } catch (Exception $e) {
            Log::error("Error creating AutorTaxon: " . $e->getMessage());
            return response()->json([
                'status' => 500,
                'message' => 'Error al crear la autoridad taxonómica: ' . $e->getMessage(),
                'error' => $e->__toString(),
            ], 500);
        }
    }



    public function update(Request $request, $id)
    {
        $autorTaxon = AutorTaxon::find($id);

        if (!$autorTaxon) {
            return response()->json(['message' => 'AutorTaxon not found'], 404);
        }

        $existing = AutorTaxon::where(DB::raw('lower(NombreAutoridad)'), strtolower($request->input('nombreAutoridad')))
            ->where(DB::raw('lower(GrupoTaxonomico)'), strtolower($request->input('grupoTaxonomico')))
            ->where('IdAutorTaxon', '!=', $id) 
            ->first();

        if ($existing) {
            return response()->json([
                'status' => 400,
                'message' => 'Ya existe otro autor con estos datos.',
                'idExistente' => $existing->IdAutorTaxon
            ], 400);
        }

        try {
            $autorTaxon->NombreAutoridad = $request->input('nombreAutoridad');
            $autorTaxon->NombreCompleto = $request->input('nombreCompleto');
            $autorTaxon->GrupoTaxonomico = $request->input('grupoTaxonomico');
            $autorTaxon->save();

            return response()->json([
                'data' => $autorTaxon,
                'message' => 'AutorTaxon Actualizado Exitosamente',
            ], 200);
        } catch (Exception $e) {
            Log::error("Error updating AutorTaxon: {$e->getMessage()}");
            return response()->json(['message' => 'Error updating AutorTaxon'], 500);
        }
    }

    public function destroy($id)
    {
        Log::info("Intentando eliminar autor con ID: {$id}");
        $autorTaxon = AutorTaxon::find($id);
        Log::info("AutorTaxon encontrado:", (array) $autorTaxon);
        if (!$autorTaxon) {
            Log::warning("AutorTaxon no encontrado con ID: {$id}");
            return response()->json(['message' => 'AutorTaxon not found'], 404);
        }
        try {
            $autorTaxon->delete();
            Log::info("AutorTaxon eliminado con ID: {$id}");
            return response()->json(['message' => 'AutorTaxon deleted successfully'], 200);
        } catch (\Exception $e) {
            Log::error("Error al eliminar AutorTaxon con ID: {$id}: " . $e->getMessage());
            return response()->json(['message' => 'Error al eliminar el AutorTaxon'], 500);
        }
    }


    public function obtenerPaginaDeAutor(Request $request)
    {
        $id = $request->id;
        $perPage = $request->perPage ?? 100;
        $sortBy = $request->sortBy ?? 'NombreAutoridad';
        $sortOrder = $request->sortOrder ?? 'asc';
        $registroReferencia = AutorTaxon::find($id);
        if (!$registroReferencia) return response()->json(['page' => 1]);
        $operador = (strtolower($sortOrder) === 'asc') ? '<' : '>';
        $posicion = AutorTaxon::where(function ($query) use ($registroReferencia, $operador, $sortBy) {
            $valor = $registroReferencia->{$sortBy};
            $query->where(DB::raw("LOWER(`$sortBy`)"), $operador, strtolower($valor))
                ->orWhere(function ($q) use ($registroReferencia, $sortBy, $valor) {
                    $q->where(DB::raw("LOWER(`$sortBy`)"), '=', strtolower($valor))
                        ->where('IdAutorTaxon', '<', $registroReferencia->IdAutorTaxon);
                });
        })->count();
        $pagina = floor($posicion / $perPage) + 1;
        return response()->json(['page' => (int)$pagina]);
    }
}
