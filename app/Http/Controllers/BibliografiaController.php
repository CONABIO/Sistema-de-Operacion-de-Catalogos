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
        $validator = Validator::make($request->all(), [
            'Autor' => 'required|string|min:1',
            'Anio' => 'required|string|min:1',
            'TituloPublicacion' => 'required|string|min:1',
            'TituloSubPublicacion' => 'nullable|string',
            'EditoresCompiladores' => 'nullable|string',
            'EditorialPaisPagina' => 'nullable|string',
            'NumeroVolumenAnio' => 'nullable|string',
            'OrdenCitaCompleta' => 'nullable|string',
            'citaCompleta' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            $biblio = new Bibliografia;
            $biblio->Autor = $request->input('Autor');
            $biblio->Anio = $request->input('Anio');
            $biblio->TituloPublicacion = $request->input('TituloPublicacion');
            $biblio->TituloSubPublicacion = $request->input('TituloSubPublicacion');
            $biblio->EditoresCompiladores = $request->input('EditoresCompiladores');
            $biblio->EditorialPaisPagina = $request->input('EditorialPaisPagina');
            $biblio->NumeroVolumenAnio = $request->input('NumeroVolumenAnio');
            $biblio->OrdenCitaCompleta = $request->input('OrdenCitaCompleta');
            $biblio->CitaCompleta = $request->input('citaCompleta');
            $biblio->FechaModificacion = now()->toDateTimeString();

            $biblio->save();

            return redirect()->route('bibliografias.index')->with('success', 'La bibliografia taxonómica se dio de alta con éxito');
        } catch (\Exception $e) {
            Log::error("Error creating Bibliografia: " . $e->getMessage());
            return back()->with('error', 'Error al crear la bibliografía: ' . $e->getMessage())->withInput();
        }
    }

    public function update(Request $request, $id)
    {
        $biblio = Bibliografia::find($id);

        if (!$biblio) {
            return response()->json(['message' => 'Bibliografia no encontrada'], 404);
        }

        $biblio->Autor = $request->input('Autor');
        $biblio->Anio = $request->input('Anio');
        $biblio->EditoresCompiladores = $request->input('EditoresCompiladores');
        $biblio->EditorialPaisPagina = $request->input('EditorialPaisPagina');
        $biblio->NumeroVolumenAnio = $request->input('NumeroVolumenAnio');
        $biblio->OrdenCitaCompleta = $request->input('OrdenCitaCompleta');
        $biblio->TituloPublicacion = $request->input('TituloPublicacion');
        $biblio->TituloSubPublicacion = $request->input('TituloSubPublicacion');
        $biblio->CitaCompleta = $request->input('citaCompleta');
        $biblio->FechaModificacion = now()->toDateTimeString();

        try {
            $biblio->save();
            return response()->json(['message' => 'El registro se actualizó con éxito'], 200);
        } catch (Exception $e) {
            Log::error("Error updating Bibliografia: {$e->getMessage()}");
            return response()->json(['message' => 'Error al actualizar la bibliografía: ' . $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $biblio = Bibliografia::findOrFail($id);
            $biblio->delete();
            return response()->json(['status' => 200, 'message' => 'Bibliografia eliminada con éxito']);
        } catch (\Exception $e) {
            Log::error("Error deleting Bibliografia: {$e->getMessage()}");
            return response()->json(['status' => 500, 'message' => 'Error al eliminar la bibliografía: ' . $e->getMessage()]);
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
