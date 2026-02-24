<?php

namespace App\Http\Controllers;

use App\Models\Region;
use App\Models\TipoRegion;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class RegionController extends Controller
{
    public function index()
    {
        $todosLosNodosRegiones = Region::orderBy('NombreRegion')->get()->all();
        $treeData = $this->buildRegionTree($todosLosNodosRegiones);
        $todosLosTiposDeRegion = TipoRegion::orderBy('Nivel1')->orderBy('Nivel2')->orderBy('Nivel3')->orderBy('Nivel4')->orderBy('Nivel5')->get();
        $tiposDeRegionTree = $this->buildTipoRegionTree($todosLosTiposDeRegion);
        return Inertia::render('Socat/Regiones/indexRegion', [
            'treeDataProp' => $treeData,
            'tiposDeRegionProp' => $todosLosTiposDeRegion,
            'tiposDeRegionTreeProp' => $tiposDeRegionTree,
        ]);
    }

    private function buildRegionTree(array $elements): array
    {
        if (count($elements) === 0) {
            return [];
        }
        $nodes = [];
        foreach ($elements as $el) {
            $el->children = [];
            $nodes[$el->IdRegion] = $el;
        }
        $rootNodes = [];
        foreach ($nodes as $nodeId => $node) {
            if ($node->IdRegionAsc && isset($nodes[$node->IdRegionAsc]) && $node->IdRegion != $node->IdRegionAsc) {
                $parent = $nodes[$node->IdRegionAsc];
                $tempChildren = $parent->children;
                $tempChildren[] = $node;
                $parent->children = $tempChildren;
            } else {
                $rootNodes[] = $node;
            }
        }
        return $rootNodes;
    }

    private function buildTipoRegionTree(Collection $nodes): array
    {
        if ($nodes->isEmpty()) return [];
        $tree = [];
        $nodeMap = [];
        foreach ($nodes as $node) {
            $nodeArray = $node->toArray();
            $nodeArray['children'] = [];
            $key = $this->getNodeKeyFromLevels($node);
            $nodeMap[$key] = $nodeArray;
        }
        foreach ($nodeMap as $key => &$node) {
            $parentKey = $this->getParentKeyFromNodeKey($key);
            if ($parentKey && isset($nodeMap[$parentKey])) {
                $nodeMap[$parentKey]['children'][] = &$node;
            } else {
                $tree[] = &$node;
            }
        }
        return $tree;
    }


    private function getParentKeyFromNodeKey($key)
    {
        $parts = explode('_', substr($key, 1));
        array_pop($parts);
        if (empty($parts)) {
            return null;
        }
        return 'L' . implode('_', $parts);
    }


    private function getNodeKeyFromLevels($node)
    {
        $levels = [];
        for ($i = 1; $i <= 5; $i++) {
            if ($node->{"Nivel{$i}"} > 0) {
                $levels[] = $node->{"Nivel{$i}"};
            } else {
                break;
            }
        }
        return 'L' . implode('_', $levels);
    }



    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'NombreRegion' => 'required|string|max:255',
            'IdTipoRegion' => 'required|integer',
            'ClaveRegion' => 'nullable|string|max:255',
            'Abreviado' => 'nullable|string|max:255',
            'opcionNivel' => 'required|string|in:raiz,mismo,inferior',
            'idNodoReferencia' => 'nullable|integer',
        ]);

        DB::transaction(function () use ($validatedData) {
            $idPadreFinal = null;
            $nodoReferencia = null;

            if ($validatedData['idNodoReferencia']) {
                $nodoReferencia = Region::find($validatedData['idNodoReferencia']);
            }
            if ($validatedData['opcionNivel'] === 'mismo' && $nodoReferencia) {
                $idPadreFinal = $nodoReferencia->IdRegionAsc;
            } elseif ($validatedData['opcionNivel'] === 'inferior' && $nodoReferencia) {
                $idPadreFinal = $nodoReferencia->IdRegion;
            } else {
                $cualquierNodo = Region::first();
                $idPadreFinal = $cualquierNodo ? $cualquierNodo->IdRegion : null;
            }
            $nuevaRegion = Region::create([
                'NombreRegion' => $validatedData['NombreRegion'],
                'IdTipoRegion' => $validatedData['IdTipoRegion'],
                'ClaveRegion' => $validatedData['ClaveRegion'],
                'Abreviado' => $validatedData['Abreviado'],
                'IdRegionAsc' => $idPadreFinal ?? 1, 
                'DatoActivo' => 1,
                'FechaCaptura' => now(),
            ]);
            $esHermanoDeRaiz = ($validatedData['opcionNivel'] === 'mismo' && $nodoReferencia && $nodoReferencia->IdRegion == $nodoReferencia->IdRegionAsc);

            if ($validatedData['opcionNivel'] === 'raiz' || $esHermanoDeRaiz) {
                $nuevaRegion->IdRegionAsc = $nuevaRegion->IdRegion;
                $nuevaRegion->save();
            }
        });

        return redirect()->route('regiones.index')->with('success', 'Región creada correctamente.');
    }


    public function update(Request $request, Region $region)
    {
        $validatedData = $request->validate([
            'NombreRegion' => 'required|string|max:255',
            'IdTipoRegion' => 'required|integer',
            'ClaveRegion' => 'nullable|string|max:255',
            'Abreviado' => 'nullable|string|max:255',
        ]);
        $region->update($validatedData);
        return redirect()->route('regiones.index')->with('success', 'Región actualizada.');
    }


    public function destroy(Region $region)
    {
        $tieneHijosInternos = Region::where('IdRegionAsc', $region->IdRegion)
            ->where('IdRegion', '!=', $region->IdRegion)
            ->exists();
        if ($tieneHijosInternos) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'message' => 'No se puede eliminar porque tiene sub-regiones dependientes.'
            ]);
        }
        if ($region->IdRegion == $region->IdRegionAsc) {
            $otroNodo = Region::where('IdRegion', '!=', $region->IdRegion)->first();
            if ($otroNodo) {
                $region->IdRegionAsc = $otroNodo->IdRegion;
                $region->save();
            } else {
                DB::statement('SET FOREIGN_KEY_CHECKS=0');
            }
        }
        try {
            $region->delete();
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() == "23000") {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'message' => 'No es posible eliminar la región seleccionada, ya que está asociada a un nombre común o a alguna característica asociada al taxón.'
                ]);
            }
            throw $e;
        } finally {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }

        return redirect()->route('regiones.index')->with('success', 'Región eliminada correctamente.');
    }
}
