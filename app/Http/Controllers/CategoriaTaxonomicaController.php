<?php

namespace App\Http\Controllers;

use App\Models\CategoriasTaxonomicas;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class CategoriaTaxonomicaController extends Controller
{
    private const MAX_NIVELES = 12;

    public function index()
    {
        $todosLosNodosPlanos = CategoriasTaxonomicas::all();
        $treeDataParaVisualizacion = $this->buildTreeFromParentId($todosLosNodosPlanos);

        return Inertia::render('Socat/Categorias/CategoriaTaxonomica', [
            'treeDataProp' => $treeDataParaVisualizacion,
            'flatTreeDataProp' => $todosLosNodosPlanos,
        ]);
    }


    private function buildTreeFromParentId(Collection $nodes): array
    {
        if ($nodes->isEmpty()) {
            return [];
        }

        $tree = [];
        $nodeMap = [];
        $primaryKeyName = 'IdCategoriaTaxonomica';

        foreach ($nodes as $node) {
            $nodeArray = $node->toArray();
            $nodeArray['children'] = [];
            $nodeMap[$node->$primaryKeyName] = $nodeArray;
        }

        foreach ($nodeMap as $nodeId => &$node) {
            $parentId = $node['IdAscendente'];
            if (!empty($parentId) && isset($nodeMap[$parentId])) {
                $nodeMap[$parentId]['children'][] = &$node;
            } else {
                $tree[] = &$node;
            }
        }

        $sortChildren = function (&$nodes) use (&$sortChildren) {
            usort($nodes, function ($a, $b) {
                for ($i = 1; $i <= self::MAX_NIVELES; $i++) {
                    $levelKey = "IdNivel{$i}";
                    $diff = $a[$levelKey] <=> $b[$levelKey];
                    if ($diff !== 0) {
                        return $diff;
                    }
                }
                return 0;
            });

            foreach ($nodes as &$node) {
                if (!empty($node['children'])) {
                    $sortChildren($node['children']);
                }
            }
        };
        $sortChildren($tree);
        return $tree;
    }


    /* private function buildTreeFromLevels(Collection $nodes): array
    {
        if ($nodes->isEmpty()) {
            return [];
        }

        $tree = [];
        $nodeMap = [];

        foreach ($nodes as $node) {
            $nodeArray = $node->toArray();
            $nodeArray['children'] = [];

            $levels = [];
            for ($i = 1; $i <= self::MAX_NIVELES; $i++) {
                $levels[] = $node->{"IdNivel{$i}"};
            }

            $key = 'L' . implode('_', array_filter($levels, fn($v) => $v > 0));
            $nodeMap[$key] = $nodeArray;

            $parentKeyArray = array_filter($levels, fn($v) => $v > 0);
            array_pop($parentKeyArray);
            $parentKey = 'L' . implode('_', $parentKeyArray);

            if (count($parentKeyArray) > 0 && isset($nodeMap[$parentKey])) {
                $nodeMap[$parentKey]['children'][] = &$nodeMap[$key];
            } else {
                $tree[] = &$nodeMap[$key];
            }
        }

        return $tree;
    }
 */

    public function store(Request $request)
    {
        $levelRules = [];
        for ($i = 1; $i <= self::MAX_NIVELES; $i++) {
            $levelRules["IdNivel{$i}"] = 'required|integer';
        }

        $validatedData = $request->validate(array_merge([
            'NombreCategoriaTaxonomica' => ['required', 'string', 'max:255'],
        ], $levelRules));

        $validatedData['RutaIcono'] = '/storage/images/RERJvyv0qvxOR9of8BRobZjiodN2DK4euvMWNYkZ.png';

        $categoria = CategoriasTaxonomicas::create($validatedData);

        return redirect()->route('categorias-taxonomicas.index')
            ->with('flash', ['newNodeId' => $categoria->IdCategoria]);
    }

    public function update(Request $request, CategoriasTaxonomicas $categoria_taxonomica)
    {
        $validatedData = $request->validate([
            'NombreCategoriaTaxonomica' => [
                'required',
                'string',
                'max:255',

            ],
        ]);

        $categoria_taxonomica->update($validatedData);

        return redirect()->route('categorias-taxonomicas.index')
            ->with('success', 'Categoría actualizada correctamente.')
            ->with('flash', ['newNodeId' => $categoria_taxonomica->IdCategoria]);
    }


    public function destroy(CategoriasTaxonomicas $categoria_taxonomica)
    {
        $query = CategoriasTaxonomicas::query();
        $profundidad = 0;
        for ($i = 1; $i <= self::MAX_NIVELES; $i++) {
            if ($categoria_taxonomica->{"IdNivel{$i}"} > 0) {
                $query->where("IdNivel{$i}", $categoria_taxonomica->{"IdNivel{$i}"});
                $profundidad = $i;
            } else {
                break;
            }
        }

        if ($profundidad < self::MAX_NIVELES) {
            $query->where("IdNivel" . ($profundidad + 1), '>', 0);
        }

        $tieneHijos = $profundidad < self::MAX_NIVELES && $query->exists();

        if ($tieneHijos) {
            throw ValidationException::withMessages([
                'message' => 'No se puede eliminar porque tiene categorías dependientes.'
            ]);
        }

        $categoria_taxonomica->delete();

        return redirect()->route('categorias-taxonomicas.index')->with('success', 'Categoría eliminada con éxito.');
    }



    public function updateIcon(Request $request, CategoriasTaxonomicas $categoriaTaxonomica)
    {
        $request->validate([
            'RutaIcono' => 'nullable|string',
        ]);

        // Usamos la variable con el nuevo nombre
        $categoriaTaxonomica->update([
            'RutaIcono' => $request->RutaIcono,
        ]);

        return redirect()->route('categorias-taxonomicas.index')
            ->with('success', 'Ícono actualizado.');
    }
}
