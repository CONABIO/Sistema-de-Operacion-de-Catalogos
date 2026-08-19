<?php

namespace App\Http\Controllers;

use App\Models\CatalogoNombre;
use App\Models\RelNombreCatalogo;
use App\Models\Region;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use App\Models\Traits\OptimizaConsultasRegiones;
use App\Models\Traits\OptimizaConsultasCaracteristicas;

class CaracteristicasController extends Controller
{
    use OptimizaConsultasRegiones;
    use OptimizaConsultasCaracteristicas;

    /*Esta es la modificacion agregada para que sea respuesta AJAX 
        Juan Carlos Mora Morquecho 02/06/2026 
    Esta modificación es para cargar desde el modal la informacion y no depender del inertia */

    public function index(Request $request): InertiaResponse
    {
        $data = $this->cargaInicio();

        return Inertia::render('Socat/Caracteristicas/indexCaracteristicas', [
            'treeDataProp' => $data['treeDataProp'],
            'flatTreeDataProp' => $data['flatTreeDataProp'],
            'errors' => session('errors') ? session('errors')->getBag('default')->getMessages() : (object) [],


        ]);
    }

    private function cargaInicio(){

        $todosLosNodosPlanos = CatalogoNombre::orderBy('Descripcion')
            ->orderBy('Nivel1')
            ->orderBy('Nivel2')
            ->orderBy('Nivel3')
            ->orderBy('Nivel4')
            ->orderBy('Nivel5')
            ->orderBy('Nivel6')
            ->orderBy('Nivel7')
            ->get();

        $treeDataParaVisualizacion = $this->buildTreeOptimized($todosLosNodosPlanos);

        return ['treeDataProp' => $treeDataParaVisualizacion,
                'flatTreeDataProp' => $todosLosNodosPlanos];
    }

    public function cargaCaracteristicas() {
        return response()->json($this->cargaInicio());
    }


    /////////////////////////////////////////////////////////////////
    public function cargaCaracteristicasTaxon($idNombre) {
    /*Aqui se va a cargar las categorias taxonomicas*/
        Log::info("Este es el id que llega a buscar: " . $idNombre);
        $data = RelNombreCatalogo::caracteristicasTaxon($idNombre)->get();
        log::info($data);
        $idsRegiones = $data->pluck('IdRegion')->unique();
        $idsCaract = $data->pluck('IdCatNombre')->unique();
        $idsDistr = $data->pluck('IdTipoDistribucion')->unique();

        $todosLosNodosRegiones = Region::orderBy('NombreRegion')
                                       ->get()
                                       ->keyBy('IdRegion')
                                       ->all();

        $treeData = $this->buildRegionTreeBatch($todosLosNodosRegiones);

        $regionesIndexadas = $this->aplanadoAscendencia($treeData, $idsRegiones);

        $todasCarac = $this->cargaInicio();

        $caractIndexadas = $this->aplanadoAscendenciaCarac($todasCarac['treeDataProp'], $idsCaract);

        $agrupado = [];

        $porCaracteristica = $data->groupBy('IdCatNombre');

        foreach ($porCaracteristica as $idCatNombre => $registros) {

            $primero = $registros->first();

            $biblio = $primero->contBiblio > 0
                        ? '/storage/images/Libro_Verde.svg'
                        : '/storage/images/Libro_Rojo.svg';

            $item = ['IdCatNombre' => $idCatNombre,
                    'Caracteristica' => $caractIndexadas[$idCatNombre]['caracteristica'] ?? '',
                    'BiblioCaract' => ['texto'=> '',
                                       'url'=>$biblio], 
                    'Regiones' => [],
                    'Observaciones' => $primero->RelNomCat,
                    ];

            foreach ($registros as $registro) {

                // El registro con IdRegion = 0 contiene únicamente
                // la información general de la característica
                if ($registro->IdRegion <= 0) {
                    continue;
                }

                $biblioReg = $registro->contBiblioRegion > 0
                                ? '/storage/images/Libro_Verde.svg'
                                : '/storage/images/Libro_Rojo.svg';
                
                $item['Regiones'][] = [
                        'IdRegion' => $registro->IdRegion,
                        'Region' => $regionesIndexadas[$registro->IdRegion]['Region'] ?? '',
                        'TipDistribucion' => ['id' => $registro->IdTipoDistribucion,
                                              'descripcion' => $registro->Descripcion],
                        'Observaciones' => $registro->RelNomCatReg,
                        'Biblio' =>  ['texto'=> '',
                                       'url'=>$biblioReg],
                    ];
            }

            $resultado[] = $item;
        }

        return response()->json($resultado);
    }

    public function cargaRegionesNombre($idNombre){
    $regNombre = Region::regionPorNombre($idNombre)->get();
    $regCaract = Region::regionPorCaract($idNombre)->get();
    $regNomComun = Region::regionPorNomComun($idNombre)->get();

    $idsRegiones = collect($regNombre)->pluck('IdRegion')
         ->merge(collect($regCaract)->pluck('IdRegion'))
         ->merge(collect($regNomComun)->pluck('IdRegion'))
         ->unique()->values()->toArray();
    $todosLosNodosRegiones = Region::orderBy('NombreRegion')->get()->keyBy('IdRegion')->all();
    $treeData = $this->buildRegionTreeBatch($todosLosNodosRegiones);
    $regionesIndexadas = $this->aplanadoAscendencia($treeData, $idsRegiones);

    $regPorNombreMapped = $this->mapeoRegiones($regNombre, $regionesIndexadas, 'nombre');
    $regPorCarct = $this->mapeoRegiones($regCaract, $regionesIndexadas, 'caracteristica');
    $regPorNomCom = $this->mapeoRegiones($regNomComun, $regionesIndexadas, 'nomComun');

    $regPorNombre = collect($regPorNombreMapped)->map(function($item) use ($idNombre) {
        $idTipoDist = $item['IdTipoDistribucion'] ?? ($item['TipoDistribucion']['id'] ?? null);

        if ($idTipoDist) {
            $tieneBiblio = \DB::connection('catcentral')->table('RelNombreRegionBiblio')
                ->where('IdNombre', $idNombre)
                ->where('IdRegion', $item['IdRegion'])
                ->where('IdTipoDistribucion', $idTipoDist)
                ->exists();

            $item['Biblio'] = [
                'url' => $tieneBiblio ? '/storage/images/Libro_Verde.svg' : '/storage/images/Libro_Rojo.svg',
                'texto' => ''
            ];
        }
        return $item;
    });

    $todasLasRegiones = collect()->concat($regPorNombre)->concat($regPorCarct)->concat($regPorNomCom);

    return response()->json([
        'regPorNombre' => $regPorNombre,
        'regPorCaract' => $regPorCarct, 
        'regPorNomCom' => $regPorNomCom,
        'todas' => $todasLasRegiones,
    ]);
}

private function mapeoRegiones($listaReg, $regIndexadas, $origenDatos){
    return $listaReg->map(function ($valor) use ($regIndexadas, $origenDatos){
        $biblio = ($valor->Biblio > 0 || $valor->contBiblio > 0)
                        ? '/storage/images/Libro_Verde.svg'
                        : '/storage/images/Libro_Rojo.svg';
        
        $tipDist = null;
        if(isset($valor->IdTipoDistribucion) || isset($valor->id_tipo)){
            $tipDist = [
                'id'    => $valor->IdTipoDistribucion ?? $valor->id_tipo,
                'label' => $valor->TipoDist ?? $valor->descripcion ?? 'Sin tipo' // IMPORTANTE: 'label'
            ];
        }

        return [
            'IdRegion' => $valor->IdRegion, 
            'Region'   => $regIndexadas[$valor->IdRegion]['Region'] ?? $valor->NombreRegion ?? 'Desconocida',
            'TipoDistribucion' => $tipDist, // Debe coincidir con el 'prop' en Vue
            'origen'           => $origenDatos,
            'Observaciones'    => $valor->Observaciones ?? $valor->observaciones ?? '', 
            'Biblio'           => ['texto' => '', 'url' => $biblio]
        ];
    })->values();
}
    

    /*Esta es la modificacion agregada para que sea respuesta AJAX 
        Juan Carlos Mora Morquecho 22/04/2026 
    Esta modificación es para cargar desde el modal la informacion y no depender del inertia */

    private function buildTreeOptimized(Collection $elements): array
    {
        $nodesById = [];

        foreach ($elements as $elementModel) {
            $nodeData = $elementModel->toArray();
            $nodeData['children'] = [];
            $nodesById[$elementModel->IdCatNombre] = $nodeData;
        }

        $tree = [];
        foreach ($nodesById as $nodeId => &$currentNodeData) {
            $idAscendente = $currentNodeData['IdAscendente'] ?? null;

            if (
                !empty($idAscendente) &&
                $nodeId != $idAscendente &&
                isset($nodesById[$idAscendente])
            ) {
                $nodesById[$idAscendente]['children'][] = &$currentNodeData;
            } else {
                $tree[] = &$currentNodeData;
            }
        }
        unset($currentNodeData);

        return $tree;
    }

    private function convertModelsToArrayTree(array $modelNodes): array
    {
        $arrayTree = [];
        foreach ($modelNodes as $modelNode) {
            $nodeData = $modelNode->toArray();
            if (!empty($modelNode->children)) {
                $nodeData['children'] = $this->convertModelsToArrayTree($modelNode->children);
            } else {
                $nodeData['children'] = [];
            }
            $arrayTree[] = $nodeData;
        }
        return $arrayTree;
    }

    private function buildTreeRecursive(Collection $elements, $parentId = null): array
    {
        $branch = [];
        $children = $elements->filter(function ($element) use ($parentId) {
            return $element->IdAscendente == $parentId;
        });

        foreach ($children as $child) {
            $grandChildren = $this->buildTreeRecursive($elements, $child->IdCatNombre);
            $child->children = !empty($grandChildren) ? $grandChildren : [];
            $branch[] = $child;
        }
        return $branch;
    }


    public function destroy($id)
    {
        $nodo = CatalogoNombre::find($id);
        if (!$nodo) {
            return response()->json(['error' => 'Característica no encontrada.'], 404);
        }
        if ($nodo->tieneHijos()) {
            return response()->json([
                'error' => 'No es posible eliminar el elemento seleccionado ya que tiene características subordinadas.'
            ], 409);
        }
        try {
            $nodo->delete();
            return response()->json(['message' => 'Característica eliminada correctamente.'], 200);
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() == "23000" || str_contains($e->getMessage(), '1451')) {
                return response()->json([
                    'error' => 'No es posible eliminar la característica seleccionada porque se encuentra asociada a un taxón.'
                ], 422); 
            }
            Log::error("Error SQL al eliminar característica {$id}: " . $e->getMessage());
            return response()->json(['error' => 'Ocurrió un error interno en el servidor.'], 500);
        } catch (\Exception $e) {
            Log::error("Error general al eliminar característica {$id}: " . $e->getMessage());
            return response()->json(['error' => 'Ocurrió un error al intentar eliminar.'], 500);
        }
    }


    public function update(Request $request, $id)
    {
        $nodo = CatalogoNombre::find($id);

        if (!$nodo) {
            return redirect()->back()->with('error', 'Característica no encontrada.');
        }

        $validatedData = $request->validate([
            'Descripcion' => [
                'required',
                'string',
                'max:255',
                Rule::unique('catcentral.CatalogoNombre', 'Descripcion')
                    ->where(function ($query) use ($nodo) {
                        return $query->where('IdAscendente', $nodo->IdAscendente);
                    })
                    ->ignore($nodo->IdCatNombre, 'IdCatNombre') 
            ],
        ], [
            'Descripcion.required' => 'La descripción es obligatoria.',
            'Descripcion.unique' => 'Ya existe una característica con ese nombre en este mismo nivel.',
            'Descripcion.max' => 'La descripción no puede tener más de 255 caracteres.',
        ]);

        try {
            $nodo->Descripcion = $validatedData['Descripcion'];
            $nodo->save();

            return redirect()->route('caracteristicas-taxon.index')
                ->with('success', 'Característica actualizada correctamente.')
                ->with('newNodeId', $nodo->IdCatNombre);
        } catch (\Exception $e) {
            Log::error("Error al actualizar característica {$id}: " . $e->getMessage());
            return redirect()->back()->with('error', 'Ocurrió un error al intentar actualizar.');
        }
    }




    public function store(Request $request)
    {
        $request->validate([
            'Descripcion' => [
                'required',
                'string',
                'max:255',
                Rule::unique('catcentral.CatalogoNombre', 'Descripcion')
                    ->where(function ($query) use ($request) {
                        return $query->where('IdAscendente', $request->IdAscendente);
                    }),
            ],
            'IdAscendente' => 'nullable',
        ], [
            'Descripcion.required' => 'La descripción es obligatoria.',
            'Descripcion.unique' => 'No se puede duplicar el nombre en el mismo nivel jerárquico.',
        ]);

        try {
            $caracteristica = new CatalogoNombre();
            $caracteristica->fill($request->only([
                'Descripcion',
                'IdAscendente',
                'Nivel1',
                'Nivel2',
                'Nivel3',
                'Nivel4',
                'Nivel5',
                'Nivel6',
                'Nivel7'
            ]));
            $caracteristica->FechaCaptura = now();
            $caracteristica->save();

            if (!$caracteristica->IdOriginal && $caracteristica->IdCatNombre) {
                $caracteristica->IdOriginal = $caracteristica->IdCatNombre;
                $caracteristica->save();
            }

            return redirect()->route('caracteristicas-taxon.index')
                ->with('success', 'Característica creada correctamente.')
                ->with('newNodeId', $caracteristica->IdCatNombre);
        } catch (\Exception $e) {
            Log::error('Error al crear característica: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al guardar en la base de datos.');
        }
    }
}
