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


    public function show($id)
    {
        $nombreComun = NomComun::find($id);

        if (!$nombreComun) {
            return response()->json(['message' => 'Nombre Común no encontrado'], 404);
        }

        return response()->json($nombreComun);
    }


    public function edit($id)
    {
        return response()->json(['message' => 'Método edit no implementado'], 501);
    }



    public function store(Request $request)
    {
        $existing = NomComun::where(DB::raw('lower(NomComun)'), strtolower($request->NomComun))
            ->where(DB::raw('lower(Lengua)'), strtolower($request->Lengua ?? ''))
            ->first();

        if ($existing) {
            return response()->json([
                'status' => 400,
                'message' => 'Ya existe un registro con el mismo nombre y lengua.',
                'idExistente' => $existing->IdNomComun
            ], 400);
        }

        try {
            $nombreComun = new NomComun();
            $nombreComun->NomComun = $request->NomComun;
            $nombreComun->Observaciones = $request->Observaciones;
            $nombreComun->Lengua = $request->Lengua;
            $nombreComun->FechaCaptura = now();
            $nombreComun->FechaModificacion = now();
            $nombreComun->save();

            return response()->json([
                'data' => $nombreComun,
                'message' => 'Nombre Común creado exitosamente',
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al crear'], 500);
        }
    }

    public function update(Request $request, $IdNomComun)
    {
        $nombreComun = NomComun::find($IdNomComun);
        if (!$nombreComun) return response()->json(['message' => 'Not found'], 404);
        $existing = NomComun::where(DB::raw('lower(NomComun)'), strtolower($request->NomComun))
            ->where(DB::raw('lower(Lengua)'), strtolower($request->Lengua ?? ''))
            ->where('IdNomComun', '!=', $IdNomComun)
            ->first();

        if ($existing) {
            return response()->json([
                'status' => 400,
                'message' => 'Ya existe otro registro con los mismos datos.',
                'idExistente' => $existing->IdNomComun
            ], 400);
        }

        try {
            $nombreComun->NomComun = $request->NomComun;
            $nombreComun->Observaciones = $request->Observaciones;
            $nombreComun->Lengua = $request->Lengua;
            $nombreComun->FechaModificacion = now();
            $nombreComun->save();

            return response()->json(['data' => $nombreComun, 'message' => 'Actualizado exitosamente'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al actualizar'], 500);
        }
    }

    public function obtenerPaginaDeNombreComun(Request $request)
    {
        $id = $request->id;
        $perPage = $request->perPage ?? 100;
        $sortBy = $request->sortBy ?? 'NomComun';
        $sortOrder = $request->sortOrder ?? 'asc';

        $registroReferencia = NomComun::find($id);
        if (!$registroReferencia) return response()->json(['page' => 1]);

        $operador = (strtolower($sortOrder) === 'asc') ? '<' : '>';

        $posicion = NomComun::where(function ($query) use ($registroReferencia, $operador, $sortBy) {
            $valor = $registroReferencia->{$sortBy};
            $query->where(DB::raw("LOWER(`$sortBy`)"), $operador, strtolower($valor))
                ->orWhere(function ($q) use ($registroReferencia, $sortBy, $valor) {
                    $q->where(DB::raw("LOWER(`$sortBy`)"), '=', strtolower($valor))
                        ->where('IdNomComun', '<', $registroReferencia->IdNomComun);
                });
        })->count();

        $pagina = floor($posicion / $perPage) + 1;
        return response()->json(['page' => (int)$pagina]);
    }


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
