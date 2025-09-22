<?php

namespace App\Http\Controllers;

use App\Models\Region;
use App\Models\TipoRegion;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

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
                $tree[] = &$node; // Si no tiene padre, es raíz
            }
        }
        return $tree;
    }

    /* // Helper para la construcción del árbol de Tipos de Región
    private function isDirectChild($item, $allItems, $currentLevel) {
        $parentLevel = $currentLevel - 1;
        $parentLevelKey = 'Nivel' . $parentLevel;
        $currentLevelKey = 'Nivel' . $currentLevel;

        // Raíz
        if ($currentLevel === 1) {
            return $item->Nivel2 == 0;
        }

        // Buscar el padre potencial
        foreach($allItems as $potentialParent) {
            $isParent = true;
            for ($i = 1; $i <= $parentLevel; $i++) {
                if ($potentialParent->{'Nivel'.$i} != $item->{'Nivel'.$i}) {
                    $isParent = false;
                    break;
                }
            }
             // El padre no debe tener niveles más allá de su propio nivel
            if ($isParent && $potentialParent->{'Nivel'.($parentLevel + 1)} == 0) {
                 // El item actual debe ser un hijo directo (no tener nietos en su definición)
                if($item->{'Nivel'.($currentLevel + 1)} == 0) {
                    return true;
                }
            }
        }
        
        // Corrección para encontrar nodos hijos que a su vez son padres
        $isParentCandidate = true;
        for ($i = $currentLevel + 1; $i <= 5; $i++) {
            if ($item->{'Nivel'.$i} != 0) {
                $isParentCandidate = false;
                break;
            }
        }
        if($isParentCandidate) return true;

        return false;
    }
 */

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

        $idPadre = 0;

        if ($validatedData['opcionNivel'] === 'inferior') {
            $idPadre = $validatedData['idNodoReferencia'];
        } elseif ($validatedData['opcionNivel'] === 'mismo') {
            $nodoHermano = Region::find($validatedData['idNodoReferencia']);
            $idPadre = $nodoHermano ? $nodoHermano->IdRegionAsc : 0;
        }

        Region::create([
            'NombreRegion' => $validatedData['NombreRegion'],
            'IdTipoRegion' => $validatedData['IdTipoRegion'],
            'ClaveRegion' => $validatedData['ClaveRegion'],
            'Abreviado' => $validatedData['Abreviado'],
            'IdRegionAsc' => $idPadre,
        ]);

        return redirect()->route('regiones.index')->with('success', 'Región creada.');
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
        if (Region::where('IdRegionAsc', $region->IdRegion)->exists()) {
            throw ValidationException::withMessages(['message' => 'No se puede eliminar porque tiene regiones dependientes.']);
        }
        $region->delete();
        return redirect()->route('regiones.index')->with('success', 'Región eliminada.');
    }
}
