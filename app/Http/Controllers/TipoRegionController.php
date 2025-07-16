<?php

namespace App\Http\Controllers;

use App\Models\TipoRegion; 
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class TipoRegionController extends Controller
{
    private const MAX_NIVELES = 5;

    public function index()
    {
        $query = TipoRegion::query();
        for ($i = 1; $i <= self::MAX_NIVELES; $i++) {
            $query->orderBy("Nivel{$i}");
        }
        $todosLosNodosPlanos = $query->get();
        $treeDataParaVisualizacion = $this->buildTreeFromLevels($todosLosNodosPlanos);
        return Inertia::render('Socat/TipoRegion/tipoRegion', [
            'treeDataProp' => $treeDataParaVisualizacion,
            'flatTreeDataProp' => $todosLosNodosPlanos,
        ]);
    }

    private function buildTreeFromLevels(Collection $nodes): array
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
                $levels[] = $node->{"Nivel{$i}"};
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

    public function store(Request $request)
    {
        $levelRules = [];
        for ($i = 1; $i <= self::MAX_NIVELES; $i++) {
            $levelRules["Nivel{$i}"] = 'required|integer';
        }
        $validatedData = $request->validate(array_merge([
            'Descripcion' => ['required', 'string', 'max:255', Rule::unique(TipoRegion::class)],
        ], $levelRules));
        TipoRegion::create($validatedData);
        return redirect()->route('tipos-region.index')->with('success', 'Tipo de Región creado.');
    }

    public function update(Request $request, TipoRegion $tipoRegion)
    {
        $validatedData = $request->validate([
            'Descripcion' => [
                'required', 'string', 'max:255',
                Rule::unique(TipoRegion::class)->ignore($tipoRegion->IdTipoRegion, 'IdTipoRegion')
            ],
        ]);
        $tipoRegion->update($validatedData);
        return redirect()->route('tipos-region.index')->with('success', 'Tipo de Región actualizado.');
    }

    public function destroy(TipoRegion $tipoRegion)
    {
        $query = TipoRegion::query();
        $profundidad = 0;
        for ($i = 1; $i <= self::MAX_NIVELES; $i++) {
            if ($tipoRegion->{"Nivel{$i}"} > 0) {
                $query->where("Nivel{$i}", $tipoRegion->{"Nivel{$i}"});
                $profundidad = $i;
            } else {
                break;
            }
        }
        if ($profundidad < self::MAX_NIVELES) {
            $query->where("Nivel" . ($profundidad + 1), '>', 0);
        }
        if ($query->exists()) {
            throw ValidationException::withMessages(['message' => 'No se puede eliminar porque tiene regiones dependientes.']);
        }
        $tipoRegion->delete();
        return redirect()->route('tipos-region.index')->with('success', 'Tipo de Región eliminado.');
    }
}