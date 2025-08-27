<?php

namespace App\Http\Controllers;

use App\Models\GrupoScat;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class GrupoTaxonomicoController extends Controller
{
    public function index(Request $request)
    {
        $isModal = $request->boolean('modal');
        if ($isModal) {
            Inertia::setRootView('modal');
        }
        $grupo = GrupoScat::orderBy('GrupoSCAT')->paginate(100);
        return Inertia::render('Socat/Grupos/GrupoTaxonomico', [
            'isModal' => $isModal,
            'datosGrupo' => [
                'data' => $grupo->items(),
                'totalItems' => $grupo->total(),
                'currentPage' => $grupo->currentPage(),
                'nextPageUrl' => $grupo->nextPageUrl(),
                'prevPageUrl' => $grupo->previousPageUrl(),
            ],
        ]);
    }


    //---------NO BORRAR ESTA PARTE DEL CODIGO, PUEDE SERVIR A FUTURO----------------

    /* public function buscaGrupo(Request $request)
    {
        $nombre = $request->input('grupo', '');

        // Realizar la consulta con el filtro de búsqueda y paginación
        $grupoTax = GrupoTaxonomico::where('Nombre', 'like', "%$nombre%")
            ->orWhere('Descripcion', 'like', "%$nombre%") // Asume que tienes un campo 'Descripcion'
            ->orderBy('Nombre')
            ->paginate(100);

        return Inertia::render('Socat/GruposTaxonomicos/GrupoTaxonomico', [
            'datosGrupo' => [
                'data' => $grupoTax->items(),
                'totalItems' => $grupoTax->total(),
                'currentPage' => $grupoTax->currentPage(),
                'nextPageUrl' => $grupoTax->nextPageUrl(),
                'prevPageUrl' => $grupoTax->previousPageUrl(),
            ],
        ]);
    } */

    // -------------------------------------------------------------------------------------


    //ESTA ES OTRA MANERA DE BUSCAR PERO NO ES CON INERTIA

    public function buscaGrupo(Request $request)
    {
        $validated = $request->validate([
            'filtros' => 'nullable|array',
            'filtros.GrupoSCAT' => 'nullable|string',
            'filtros.GrupoAbreviado' => 'nullable|string',
            'filtros.GrupoSNIB' => 'nullable|string',
            'tipo_busqueda' => 'nullable|string|in:inicia,contiene,termina',
            'page' => 'nullable|integer|min:1',
            'perPage' => 'nullable|integer|min:1',
            'sortBy' => 'nullable|string|in:GrupoSCAT,GrupoAbreviado,GrupoSNIB',
            'sortOrder' => 'nullable|string|in:asc,desc,ascending,descending',
        ]);

        $filtros = $validated['filtros'] ?? [];
        $tipo_busqueda = $validated['tipo_busqueda'] ?? 'contiene';
        $page = $validated['page'] ?? 1;
        $perPage = $validated['perPage'] ?? 100;
        $sortBy = $validated['sortBy'] ?? null;
        $sortOrderInput = $validated['sortOrder'] ?? null;

        $query = GrupoScat::query();

        foreach ($filtros as $campo => $valor) {
            if (!empty($valor)) {
                if (in_array($campo, ['GrupoSCAT', 'GrupoAbreviado', 'GrupoSNIB'])) {
                    switch ($tipo_busqueda) {
                        case 'inicia':
                            $query->where(DB::raw("LOWER(`{$campo}`)"), 'like', strtolower($valor) . '%');
                            break;
                        case 'termina':
                            $query->where(DB::raw("LOWER(`{$campo}`)"), 'like', '%' . strtolower($valor));
                            break;
                        default: // 'contiene'
                            $query->where(DB::raw("LOWER(`{$campo}`)"), 'like', '%' . strtolower($valor) . '%');
                            break;
                    }
                }
            }
        }

        if ($sortBy && $sortOrderInput) {
            $sortOrder = (in_array($sortOrderInput, ['ascending', 'asc'])) ? 'asc' : 'desc';
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderBy('GrupoSCAT', 'asc');
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

    public function store(Request $request)
    {
        $grupo = GrupoScat::where('GrupoScat', $request->nomGrupoScat)
            ->get();

        if ($grupo->count() === 0) {

            $grupoScat = new GrupoScat;
            $grupoScat->GrupoScat = $request->GrupoSCAT;
            $grupoScat->GrupoAbreviado = $request->GrupoAbreviado;
            $grupoScat->GrupoSNIB = $request->GrupoSNIB;
            $grupoScat->FechaModificacion = now()->toDateTimeString();
            $grupoScat->save();

            return  response()->json([
                'status' => 200,
                'message' => 'El grupo scat se dio de alta con exito',
            ]);
        } else {
            return  response()->json([
                'status' => 400,
                'message' => 'El grupo scat que intenta guardar ya existe',
            ]);
        }
    }



    public function update(Request $request, $IdGrupoSCAT)
    {
        // dd($request->all());
        $grupoTaxonomico = GrupoScat::find($IdGrupoSCAT);

        if (!$grupoTaxonomico) {
            return response()->json(['message' => 'GrupoTaxonomico not found'], 404);
        }

        $grupoTaxonomico->GrupoSCAT = $request->input('GrupoSCAT');
        $grupoTaxonomico->GrupoAbreviado = $request->input('GrupoAbreviado');
        $grupoTaxonomico->GrupoSNIB = $request->input('GrupoSNIB');

        try {
            $grupoTaxonomico->save();
            return response()->json([
                'data' => $grupoTaxonomico,
                'message' => 'GrupoTaxonomico Actualizado Exitosamente',
            ], 200);
        } catch (Exception $e) {
            Log::error("Error updating GrupoTaxonomico: {$e->getMessage()}");

            return response()->json(['message' => 'Error updating GrupoTaxonomico'], 500);
        }
    }


    public function destroy($IdGrupoSCAT)
    {
        $isAssociated = DB::connection('catcentral')->table('RelBiblioGrupoSCAT')->where('IdGrupoSCAT', $IdGrupoSCAT)->exists();

        if ($isAssociated) {
            return response()->json([
                'message' => 'El grupo está asociado a información y no puede ser eliminado.'
            ], 409);
        }

        Log::info("Intentando eliminar grupo taxonómico con ID: {$IdGrupoSCAT}");
        $grupoTaxonomico = GrupoScat::find($IdGrupoSCAT);

        if (!$grupoTaxonomico) {
            Log::warning("GrupoTaxonomico no encontrado con ID: {$IdGrupoSCAT}");
            return response()->json(['message' => 'Grupo no encontrado.'], 404);
        }

        try {
            $grupoTaxonomico->delete();
            Log::info("GrupoTaxonomico eliminado con ID: {$IdGrupoSCAT}");
            return response()->json(['message' => 'Grupo eliminado correctamente.'], 200);
        } catch (\Exception $e) {
            Log::error("Error al eliminar GrupoTaxonomico con ID: {$IdGrupoSCAT}: " . $e->getMessage());
            return response()->json(['message' => 'Error al eliminar el Grupo'], 500);
        }
    }
}
