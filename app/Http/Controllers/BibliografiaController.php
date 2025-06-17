<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Bibliografia;
use App\Models\RelBiblioGrupoSCAT;
use App\Models\BibliografiaBorra;
use App\Http\Requests\BibliografiaRequest;
use Exception;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Illuminate\Support\Facades\Validator;

class BibliografiaController extends Controller
{
    private $sortableColumns = [
        'Autor', 'Anio', 'TituloPublicacion', 'TituloSubPublicacion', 
        'EditorialPaisPagina', 'NumeroVolumenAnio', 'EditoresCompiladores', 'ISBNISSN'
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


    private function buildQuery(Request $request)
    {
        $validated = $request->validate([
            'buscado' => 'nullable|string',
            'sortBy' => ['nullable', 'string', \Illuminate\Validation\Rule::in($this->sortableColumns)],
            'sortOrder' => 'nullable|string|in:asc,desc',
            'opciones' => 'nullable|array',
        ]);
        
        $buscado = $validated['buscado'] ?? null;
        $sortBy = $validated['sortBy'] ?? null;
        $sortOrder = $validated['sortOrder'] ?? null;
        $opciones = $validated['opciones'] ?? [];

        $query = Bibliografia::query();

        if ($buscado) {
            $query->where(function ($q) use ($buscado, $opciones) {
                if (empty($opciones)) {
                    $opciones = ['Autor(es)', 'Año(s)', 'Titulo de la publicación', 'Cita bibliográfica', 'Titulo de la subpublicación', 'ISBN/ISSN'];
                }

                $columnMapping = [
                    'Autor(es)' => 'Autor',
                    'Año(s)' => 'Anio',
                    'Titulo de la publicación' => 'TituloPublicacion',
                    'Cita bibliográfica' => 'CitaCompleta',
                    'Titulo de la subpublicación' => 'TituloSubPublicacion',
                    'ISBN/ISSN' => 'ISBNISSN',
                ];

                foreach($opciones as $opcion) {
                    if (isset($columnMapping[$opcion])) {
                        $columna = $columnMapping[$opcion];
                        $q->orWhere(DB::raw("LOWER(`{$columna}`)"), 'like', '%' . strtolower($buscado) . '%');
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
            return back()->withErrors($validator)->withInput(); // Devuelve los errores a la vista
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

            return redirect()->route('bibliografias.index')->with('success', 'La bibliografia taxonómica se dio de alta con éxito'); // Redirige con mensaje de éxito
        } catch (\Exception $e) {
            Log::error("Error creating Bibliografia: " . $e->getMessage());
            return back()->with('error', 'Error al crear la bibliografía: ' . $e->getMessage())->withInput(); // Redirige con mensaje de error
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
            return response()->json(['status' => 200, 'message' => 'Bibliografia eliminada con éxito']);  // Enviar respuesta JSON
        } catch (\Exception $e) {
            Log::error("Error deleting Bibliografia: {$e->getMessage()}");
            return response()->json(['status' => 500, 'message' => 'Error al eliminar la bibliografía: ' . $e->getMessage()]); // Enviar respuesta JSON
        }
    }
}
