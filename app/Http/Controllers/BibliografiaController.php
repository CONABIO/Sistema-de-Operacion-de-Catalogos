<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Bibliografia;
use Exception;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Illuminate\Support\Facades\Validator;

class BibliografiaController extends Controller
{
    private $sortableColumns = [
        'Autor',
        'Anio',
        'TituloPublicacion',
        'TituloSubPublicacion',
        'EditorialPaisPagina',
        'NumeroVolumenAnio',
        'EditoresCompiladores',
        'ISBNISSN'
    ];

    public function index(Request $request)
    {
        $query = $this->buildQuery($request);

        $perPage = $request->input('perPage', 100);
        $result = $query->paginate($perPage);

        return Inertia::render('Socat/Bibliografia/indexBibliografia', [
            'bibliografiaData' => [
                'data' => $result->items(),
                'totalItems' => $result->total(),
                'currentPage' => $result->currentPage(),
                'lastPage' => $result->lastPage(),
                'nextPageUrl' => $result->nextPageUrl(),
                'prevPageUrl' => $result->previousPageUrl(),
            ],
        ]);
    }


    public function indexApi(Request $request)
    {
        $query = $this->buildQuery($request);

        $perPage = $request->input('perPage', 100);
        $result = $query->paginate($perPage);

        return response()->json([
            'data' => $result->items(),
            'totalItems' => $result->total(),
            'currentPage' => $result->currentPage(),
            'lastPage' => $result->lastPage(),
            'nextPageUrl' => $result->nextPageUrl(),
            'prevPageUrl' => $result->previousPageUrl(),
        ]);
    }

    private function buildQuery(Request $request)
    {
        $validated = $request->validate([
            'filtros' => 'nullable|array',
            'filtros.*' => 'nullable|string',
            'tipo_busqueda' => 'nullable|string|in:inicia,contiene,termina',
            'sortBy' => ['nullable', 'string', \Illuminate\Validation\Rule::in($this->sortableColumns)],
            'sortOrder' => 'nullable|string|in:asc,desc,ascending,descending',
        ]);

        $filtros = $validated['filtros'] ?? [];
        $tipo_busqueda = $validated['tipo_busqueda'] ?? 'contiene';
        $sortBy = $validated['sortBy'] ?? null;
        $sortOrder = $validated['sortOrder'] ?? null;

        $query = Bibliografia::query();

        if (!empty($filtros)) {
            $query->where(function ($q) use ($filtros, $tipo_busqueda) {
                foreach ($filtros as $campo => $valor) {
                    if (!empty($valor)) {
                        if (in_array($campo, ['Autor', 'Anio', 'TituloPublicacion', 'TituloSubPublicacion', 'ISBNISSN'])) {
                            switch ($tipo_busqueda) {
                                case 'inicia':
                                    $q->where(DB::raw("LOWER(`{$campo}`)"), 'like', strtolower($valor) . '%');
                                    break;
                                case 'termina':
                                    $q->where(DB::raw("LOWER(`{$campo}`)"), 'like', '%' . strtolower($valor));
                                    break;
                                default:
                                    $q->where(DB::raw("LOWER(`{$campo}`)"), 'like', '%' . strtolower($valor) . '%');
                                    break;
                            }
                        }
                    }
                }
            });
        }

        if ($sortBy && $sortOrder) {
            $query->orderByRaw("LOWER(`{$sortBy}`) {$sortOrder}");
        } else {
            $query->orderByRaw('LOWER(`Autor`) asc');
        }

        return $query;
    }


    public function store(Request $request)
    {
        // Validación de duplicado global (Autor + Año + Titulo)
        $existing = Bibliografia::where('Autor', $request->Autor)
            ->where('Anio', $request->Anio)
            ->where('TituloPublicacion', $request->TituloPublicacion)
            ->first();

        if ($existing) {
            return response()->json([
                'status' => 400,
                'message' => 'Ya existe una referencia bibliográfica con los mismos datos.',
                'idExistente' => $existing->IdBibliografia
            ], 400);
        }

        $request->validate([
            'Autor' => 'required|string',
            'Anio' => 'required|string',
            'TituloPublicacion' => 'required|string',
        ]);

        try {
            $biblio = new Bibliografia;
            $biblio->Autor = $request->Autor;
            $biblio->Anio = $request->Anio;
            $biblio->TituloPublicacion = $request->TituloPublicacion;
            $biblio->TituloSubPublicacion = $request->TituloSubPublicacion;
            $biblio->EditoresCompiladores = $request->EditoresCompiladores;
            $biblio->EditorialPaisPagina = $request->EditorialPaisPagina;
            $biblio->NumeroVolumenAnio = $request->NumeroVolumenAnio;
            $biblio->OrdenCitaCompleta = $request->OrdenCitaCompleta;
            $biblio->CitaCompleta = $request->citaCompleta;
            $biblio->FechaModificacion = now()->toDateTimeString();
            $biblio->save();

            return response()->json(['message' => 'Guardado con éxito', 'data' => $biblio], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al crear'], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $biblio = Bibliografia::find($id);
        if (!$biblio) return response()->json(['message' => 'No encontrado'], 404);

        // Validación de duplicado (excluyendo el actual)
        $existing = Bibliografia::where('Autor', $request->Autor)
            ->where('Anio', $request->Anio)
            ->where('TituloPublicacion', $request->TituloPublicacion)
            ->where('IdBibliografia', '!=', $id)
            ->first();

        if ($existing) {
            return response()->json([
                'status' => 400,
                'message' => 'Ya existe otro registro con estos datos.',
                'idExistente' => $existing->IdBibliografia
            ], 400);
        }

        try {
            $biblio->fill($request->all());
            $biblio->FechaModificacion = now()->toDateTimeString();
            $biblio->save();
            return response()->json(['message' => 'Actualizado con éxito'], 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'Error al actualizar'], 500);
        }
    }

    public function obtenerPaginaDeBiblio(Request $request)
    {
        $id = $request->id;
        $perPage = $request->perPage ?? 100;
        $sortBy = $request->sortBy ?? 'Autor';
        $sortOrder = $request->sortOrder ?? 'asc';

        $registroReferencia = Bibliografia::find($id);
        if (!$registroReferencia) return response()->json(['page' => 1]);

        $operador = (strtolower($sortOrder) === 'asc') ? '<' : '>';

        $posicion = Bibliografia::where(function ($query) use ($registroReferencia, $operador, $sortBy) {
            $valor = $registroReferencia->{$sortBy};
            $query->where(DB::raw("LOWER(`$sortBy`)"), $operador, strtolower($valor))
                ->orWhere(function ($q) use ($registroReferencia, $sortBy, $valor) {
                    $q->where(DB::raw("LOWER(`$sortBy`)"), '=', strtolower($valor))
                        ->where('IdBibliografia', '<', $registroReferencia->IdBibliografia);
                });
        })->count();

        $pagina = floor($posicion / $perPage) + 1;
        return response()->json(['page' => (int)$pagina]);
    }

    public function destroy($id)
    {
        try {
            $biblio = Bibliografia::where('IdBibliografia', $id)->firstOrFail();
            $biblio->delete();
            return response()->json([
                'message' => 'Bibliografia eliminada con éxito'
            ], 200);
        } catch (\Illuminate\Database\QueryException $e) {
            return response()->json([
                'message' => 'No se puede eliminar: Esta bibliografía está asociada a taxones o grupos.'
            ], 422);
        } catch (\Exception $e) {
            Log::error("Error deleting Bibliografia: {$e->getMessage()}");
            return response()->json([
                'message' => 'Error interno: ' . $e->getMessage()
            ], 500);
        }
    }


    public function getGruposTaxonomicos($bibliografiaId)
    {
        try {
            $grupos = DB::connection('catcentral')->table('RelBiblioGrupoSCAT')
                ->join('GrupoSCAT', 'RelBiblioGrupoSCAT.IdGrupoSCAT', '=', 'GrupoSCAT.IdGrupoSCAT')
                ->where('RelBiblioGrupoSCAT.IdBibliografia', $bibliografiaId)
                ->select(
                    'RelBiblioGrupoSCAT.IdBibliografia',
                    'RelBiblioGrupoSCAT.IdGrupoSCAT',
                    'GrupoSCAT.GrupoSCAT as grupo',
                    'RelBiblioGrupoSCAT.Observaciones as observaciones'
                )
                ->get();
            return response()->json($grupos);
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error("Error en getGruposTaxonomicos: " . $e->getMessage());
            return response()->json(['message' => 'Error en la consulta a la base de datos.', 'error' => $e->getMessage()], 500);
        }
    }


    public function asociarGrupo(Request $request)
    {
        $datosValidados = $request->validate([
            'IdBibliografia' => 'required|integer|exists:catcentral.Bibliografia,IdBibliografia',
            'IdGrupoSCAT' => 'required|integer|exists:catcentral.GrupoSCAT,IdGrupoSCAT',
        ]);

        $idBibliografia = $datosValidados['IdBibliografia'];
        $idGrupoSCAT = $datosValidados['IdGrupoSCAT'];

        try {
            $existe = DB::connection('catcentral')->table('RelBiblioGrupoSCAT')
                ->where('IdBibliografia', $idBibliografia)
                ->where('IdGrupoSCAT', $idGrupoSCAT)
                ->exists();
            if ($existe) {
                return response()->json(['message' => 'Este grupo ya está asociado a la bibliografía.'], 409);
            }
            DB::connection('catcentral')->table('RelBiblioGrupoSCAT')->insert([
                'IdBibliografia' => $idBibliografia,
                'IdGrupoSCAT' => $idGrupoSCAT,
                'FechaCaptura' => now(),
                'FechaModificacion' => now(),
            ]);
            $grupoAsociado = DB::connection('catcentral')->table('RelBiblioGrupoSCAT')
                ->join('GrupoSCAT', 'RelBiblioGrupoSCAT.IdGrupoSCAT', '=', 'GrupoSCAT.IdGrupoSCAT')
                ->where('RelBiblioGrupoSCAT.IdBibliografia', $idBibliografia)
                ->where('RelBiblioGrupoSCAT.IdGrupoSCAT', $idGrupoSCAT)
                ->select('GrupoSCAT.GrupoSCAT as grupo', 'RelBiblioGrupoSCAT.Observaciones as observaciones')
                ->first();

            return response()->json([
                'message' => 'Grupo asociado correctamente.',
                'grupo' => $grupoAsociado
            ], 201);
        } catch (\Exception $e) {
            Log::error("Error en asociarGrupo: " . $e->getMessage());
            return response()->json(['message' => 'Error inesperado al asociar el grupo.', 'error' => $e->getMessage()], 500);
        }
    }



    public function actualizarAsociacion(Request $request)
    {
        $datosValidados = $request->validate([
            'IdBibliografia' => 'required|integer|exists:catcentral.Bibliografia,IdBibliografia',
            'IdGrupoSCAT' => 'required|integer|exists:catcentral.GrupoSCAT,IdGrupoSCAT',
            'Observaciones' => 'nullable|string',
        ]);

        try {
            $affectedRows = DB::connection('catcentral')->table('RelBiblioGrupoSCAT')
                ->where('IdBibliografia', $datosValidados['IdBibliografia'])
                ->where('IdGrupoSCAT', $datosValidados['IdGrupoSCAT'])
                ->update([
                    'Observaciones' => $datosValidados['Observaciones'],
                    'FechaModificacion' => now(),
                ]);

            if ($affectedRows === 0) {
                return response()->json(['message' => 'No se encontró la asociación para actualizar.'], 404);
            }

            return response()->json(['message' => 'Observaciones actualizadas con éxito.'], 200);
        } catch (\Exception $e) {
            Log::error("Error en actualizarAsociacion: " . $e->getMessage());
            return response()->json(['message' => 'Error al actualizar las observaciones.'], 500);
        }
    }


    public function eliminarAsociacion(Request $request)
    {
        $datosValidados = $request->validate([
            'IdBibliografia' => 'required|integer|exists:catcentral.Bibliografia,IdBibliografia',
            'IdGrupoSCAT' => 'required|integer|exists:catcentral.GrupoSCAT,IdGrupoSCAT',
        ]);

        try {
            $deletedRows = DB::connection('catcentral')->table('RelBiblioGrupoSCAT')
                ->where('IdBibliografia', $datosValidados['IdBibliografia'])
                ->where('IdGrupoSCAT', $datosValidados['IdGrupoSCAT'])
                ->delete();

            if ($deletedRows === 0) {
                return response()->json(['message' => 'No se encontró la asociación para eliminar.'], 404);
            }

            return response()->json(['message' => 'Asociación eliminada con éxito.'], 200);
        } catch (\Exception $e) {
            Log::error("Error en eliminarAsociacion: " . $e->getMessage());
            return response()->json(['message' => 'Error al eliminar la asociación.'], 500);
        }
    }


    public function getObjetosExternos($bibliografiaId)
    {
        try {
            if (!is_numeric($bibliografiaId)) {
                return response()->json(['message' => 'ID de bibliografía inválido.'], 400);
            }

            $objetos = DB::connection('catcentral')
                ->table('RelObjetoExternoBiblio')
                ->join('ObjetoExterno', 'RelObjetoExternoBiblio.IdObjetoExterno', '=', 'ObjetoExterno.IdObjetoExterno')
                ->where('RelObjetoExternoBiblio.IdBibliografia', $bibliografiaId)
                ->select(
                    'RelObjetoExternoBiblio.IdBibliografia',
                    'RelObjetoExternoBiblio.IdObjetoExterno',
                    'ObjetoExterno.NombreObjeto as objeto',
                    'RelObjetoExternoBiblio.Observaciones as observaciones'
                )
                ->get();

            return response()->json($objetos);
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error("Error de SQL en getObjetosExternos: " . $e->getMessage());
            return response()->json([
                'message' => 'Error en la consulta a la base de datos al obtener objetos externos.',
                'error_sql' => $e->getMessage()
            ], 500);
        } catch (\Exception $e) {
            Log::error("Error general en getObjetosExternos: " . $e->getMessage());
            return response()->json(['message' => 'Ocurrió un error inesperado en el servidor.'], 500);
        }
    }


    public function asociarObjetoExterno(Request $request)
    {
        $datosValidados = $request->validate([
            'IdBibliografia' => 'required|integer|exists:catcentral.Bibliografia,IdBibliografia',
            'IdObjetoExterno' => 'required|integer|exists:catcentral.ObjetoExterno,IdObjetoExterno',
        ]);

        $idBibliografia = $datosValidados['IdBibliografia'];
        $idObjetoExterno = $datosValidados['IdObjetoExterno'];

        try {
            $existe = DB::connection('catcentral')->table('RelObjetoExternoBiblio')
                ->where('IdBibliografia', $idBibliografia)
                ->where('IdObjetoExterno', $idObjetoExterno)
                ->exists();

            if ($existe) {
                return response()->json(['message' => 'Este objeto ya está asociado a la bibliografía.'], 409);
            }

            DB::connection('catcentral')->table('RelObjetoExternoBiblio')->insert([
                'IdBibliografia' => $idBibliografia,
                'IdObjetoExterno' => $idObjetoExterno,
                'FechaCaptura' => now(),
                'FechaModificacion' => now(),
            ]);

            return response()->json(['message' => 'Objeto externo asociado correctamente.'], 201);
        } catch (\Exception $e) {
            Log::error("Error en asociarObjetoExterno: " . $e->getMessage());
            return response()->json(['message' => 'Error inesperado al asociar el objeto.', 'error' => $e->getMessage()], 500);
        }
    }

    public function eliminarAsociacionObjeto(Request $request)
    {
        $datosValidados = $request->validate([
            'IdBibliografia' => 'required|integer|exists:catcentral.Bibliografia,IdBibliografia',
            'IdObjetoExterno' => 'required|integer|exists:catcentral.ObjetoExterno,IdObjetoExterno',
        ]);

        try {
            DB::connection('catcentral')->table('RelObjetoExternoBiblio')
                ->where('IdBibliografia', $datosValidados['IdBibliografia'])
                ->where('IdObjetoExterno', $datosValidados['IdObjetoExterno'])
                ->delete();

            return response()->json(['message' => 'Asociación de objeto externo eliminada con éxito.'], 200);
        } catch (\Exception $e) {
            Log::error("Error en eliminarAsociacionObjeto: " . $e->getMessage());
            return response()->json(['message' => 'Error al eliminar la asociación del objeto.'], 500);
        }
    }
}
