<?php

namespace App\Http\Controllers;

use App\Models\ObjetoExterno;
use App\Models\Mime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class ObjetoExternoController extends Controller
{
    public function index()
    {
        return Inertia::render('Socat/ObjetosExternos/indexObjetos');
    }

    public function buscaObjetoExterno(Request $request)
    {
        $validated = $request->validate([
            'filtros' => 'nullable|array',
            'tipo_busqueda' => 'nullable|string|in:inicia,contiene,termina',
            'page' => 'nullable|integer|min:1',
            'perPage' => 'nullable|integer|min:1',
            'sortBy' => 'nullable|string',
            'sortOrder' => 'nullable|string|in:asc,desc,ascending,descending',
        ]);
        $filtros = $validated['filtros'] ?? [];
        $tipo_busqueda = $validated['tipo_busqueda'] ?? 'contiene';
        $page = $validated['page'] ?? 1;
        $perPage = $validated['perPage'] ?? 100;
        $sortBy = $validated['sortBy'] ?? 'NombreObjeto';
        $sortOrderInput = $validated['sortOrder'] ?? 'asc';
        $query = ObjetoExterno::query()
            ->leftJoin('MIME', 'ObjetoExterno.IdMime', '=', 'MIME.IdMime')
            ->select('ObjetoExterno.*', 'MIME.Extension as extension', 'MIME.MIME as tipo');

        foreach ($filtros as $campo => $valor) {
            if (!empty($valor)) {
                $columnaReal = match($campo) {
                    'extension' => 'MIME.Extension',
                    'tipo' => 'MIME.MIME',
                    default => "ObjetoExterno.{$campo}"
                };
                switch ($tipo_busqueda) {
                    case 'inicia':
                        $query->where(DB::raw("LOWER({$columnaReal})"), 'like', strtolower($valor) . '%');
                        break;
                    case 'termina':
                        $query->where(DB::raw("LOWER({$columnaReal})"), 'like', '%' . strtolower($valor));
                        break;
                    default:
                        $query->where(DB::raw("LOWER({$columnaReal})"), 'like', '%' . strtolower($valor) . '%');
                        break;
                }
            }
        }

        if ($sortBy) {
            $sortOrder = (in_array($sortOrderInput, ['ascending', 'asc'])) ? 'asc' : 'desc';
            $colOrder = match($sortBy) {
                'extension' => 'MIME.Extension',
                'tipo' => 'MIME.MIME',
                default => "ObjetoExterno.{$sortBy}"
            };
            $query->orderByRaw("LOWER({$colOrder}) {$sortOrder}");
        }

        $result = $query->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'data' => $result->items(),
            'totalItems' => $result->total(),
            'currentPage' => $result->currentPage(),
        ]);
    }

    public function store(Request $request)
    {
        $existing = ObjetoExterno::where(DB::raw('lower(NombreObjeto)'), strtolower($request->NombreObjeto))->first();
        if ($existing) {
            return response()->json([
                'status' => 400,
                'message' => 'El objeto externo que desea ingresar ya existe.',
                'idExistente' => $existing->IdObjetoExterno
            ], 400);
        }
        $data = $request->all();
        if (!is_numeric($request->IdMime)) {
            $ext = strtoupper($request->IdMime);
            $mime = Mime::firstOrCreate(['Extension' => $ext], [
                'MIME' => $ext . " FILE",
                'FechaCaptura' => now(),
                'FechaModificacion' => '9999-12-31 00:00:00'
            ]);
            $data['IdMime'] = $mime->IdMime;
        }
        $data['FechaCaptura'] = now();
        $objeto = ObjetoExterno::create($data);
        return response()->json(['data' => $objeto, 'message' => 'Ingresado correctamente'], 201);
    }

    public function update(Request $request, $id)
    {
        $objeto = ObjetoExterno::findOrFail($id);
        $existing = ObjetoExterno::where(DB::raw('lower(NombreObjeto)'), strtolower($request->NombreObjeto))
            ->where('IdObjetoExterno', '!=', $id)
            ->first();
        if ($existing) {
            return response()->json([
                'status' => 400,
                'message' => 'El objeto externo ya existe.',
                'idExistente' => $existing->IdObjetoExterno
            ], 400);
        }
        $data = $request->all();
        $data['FechaModificacion'] = now();
        $objeto->update($data);

        return response()->json(['data' => $objeto, 'message' => 'Actualizado correctamente']);
    }

    public function obtenerPaginaDeObjeto(Request $request)
    {
        $id = $request->id;
        $perPage = $request->perPage ?? 100;
        $sortBy = $request->sortBy ?? 'NombreObjeto';
        $sortOrder = $request->sortOrder ?? 'asc';
        $registroReferencia = ObjetoExterno::find($id);
        if (!$registroReferencia) return response()->json(['page' => 1]);
        $operador = (strtolower($sortOrder) === 'asc') ? '<' : '>';
        $posicion = ObjetoExterno::where(function ($query) use ($registroReferencia, $operador, $sortBy) {
            $valor = $registroReferencia->{$sortBy};
            $query->where(DB::raw("LOWER(`{$sortBy}`)"), $operador, strtolower($valor))
                ->orWhere(function ($q) use ($registroReferencia, $sortBy, $valor) {
                    $q->where(DB::raw("LOWER(`{$sortBy}`)"), '=', strtolower($valor))
                        ->where('IdObjetoExterno', '<', $registroReferencia->IdObjetoExterno);
                });
        })->count();
        $pagina = floor($posicion / $perPage) + 1;
        return response()->json(['page' => (int)$pagina]);
    }

  public function destroy($id)
{
    try {
        $objeto = ObjetoExterno::findOrFail($id);
        $relaciones = [
            'RelObjetoExternoBiblio'            => 'Bibliografías',
            'RelObjetoExternoEjemplar'          => 'Ejemplares',
            'RelObjetoExternoNombre'            => 'Nombres',
            'RelObjetoExternoSitio'             => 'Sitios',
            'RelEstudioObjetoExterno'           => 'Estudios',
            'RelEstudioCatColObjetoExterno'     => 'Estudios de Catálogo Ecológico',
            'RelRegionEstudioSitioCatColObjetoExterno' => 'Región-Estudio (CatCol)',
        ];

        $tablasConDatos = [];

        foreach ($relaciones as $tabla => $nombreVisible) {
            if (\Illuminate\Support\Facades\Schema::connection('catcentral')->hasTable($tabla)) {
                $existe = DB::connection('catcentral')
                    ->table($tabla)
                    ->where('IdObjetoExterno', $id)
                    ->exists();

                if ($existe) {
                    $tablasConDatos[] = $nombreVisible;
                }
            }
        }

        if (count($tablasConDatos) > 0) {
            $listaTablas = implode(', ', array_slice($tablasConDatos, 0, -1));
            if (count($tablasConDatos) > 1) {
                $listaTablas .= ' y ' . end($tablasConDatos);
            } else {
                $listaTablas = $tablasConDatos[0];
            }

            return response()->json([
                'message' => "El registro no se puede eliminar porque está relacionado con {$listaTablas}."
            ], 422);
        }

        $objeto->delete();

        return response()->json([
            'message' => 'El objeto externo fue eliminado correctamente.'
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Error al intentar eliminar el registro. Es posible que existan dependencias a nivel de base de datos no controladas.'
        ], 500);
    }
}

}
