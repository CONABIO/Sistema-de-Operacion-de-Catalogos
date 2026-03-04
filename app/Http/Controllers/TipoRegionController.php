<?php

namespace App\Http\Controllers;

use App\Models\Region;
use App\Models\TipoRegion;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class TipoRegionController extends Controller
{
    private const MAX_NIVELES = 5;


    public function index(Request $request)
    {
        $isModalMode = $request->query('modal', false);
        $query = TipoRegion::query();
        for ($i = 1; $i <= self::MAX_NIVELES; $i++) {
            $query->orderBy("Nivel{$i}");
        }
        $todosLosNodosPlanos = $query->get();
        $treeDataParaVisualizacion = $this->buildTreeFromLevels($todosLosNodosPlanos);

        return Inertia::render('Socat/TipoRegion/tipoRegion', [
            'treeDataProp' => $treeDataParaVisualizacion,
            'flatTreeDataProp' => $todosLosNodosPlanos,
            'isModal' => $isModalMode,
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
        $niveles = $request->only(['Nivel1', 'Nivel2', 'Nivel3', 'Nivel4', 'Nivel5']);
        $nivelActivo = 1;
        for ($i = self::MAX_NIVELES; $i >= 1; $i--) {
            if ($niveles["Nivel{$i}"] > 0) {
                $nivelActivo = $i;
                break;
            }
        }

        $validatedData = $request->validate([
            'Nivel1' => 'required|integer',
            'Nivel2' => 'required|integer',
            'Nivel3' => 'required|integer',
            'Nivel4' => 'required|integer',
            'Nivel5' => 'required|integer',
            'isModal' => 'nullable|boolean',
            'Descripcion' => [
                'required',
                'string',
                'max:255',
                Rule::unique(TipoRegion::class)->where(function ($query) use ($niveles, $nivelActivo) {
                    for ($i = 1; $i < $nivelActivo; $i++) {
                        $query->where("Nivel{$i}", $niveles["Nivel{$i}"]);
                    }
                    $query->where("Nivel{$nivelActivo}", '>', 0);
                    for ($i = $nivelActivo + 1; $i <= self::MAX_NIVELES; $i++) {
                        $query->where("Nivel{$i}", 0);
                    }
                }),
            ],
        ], [
            'Descripcion.unique' => 'Esta descripción ya está registrada en este nivel.',
        ]);

        TipoRegion::create($validatedData);

        return $request->boolean('isModal') ? back() : redirect()->route('tipos-region.index');
    }

    public function update(Request $request, TipoRegion $tipoRegion)
    {
        $nivelActivo = 1;
        for ($i = self::MAX_NIVELES; $i >= 1; $i--) {
            if ($tipoRegion->{"Nivel{$i}"} > 0) {
                $nivelActivo = $i;
                break;
            }
        }

        $validatedData = $request->validate([
            'Descripcion' => [
                'required',
                'string',
                'max:255',
                Rule::unique(TipoRegion::class)
                    ->ignore($tipoRegion->IdTipoRegion, 'IdTipoRegion')
                    ->where(function ($query) use ($tipoRegion, $nivelActivo) {
                        for ($i = 1; $i < $nivelActivo; $i++) {
                            $query->where("Nivel{$i}", $tipoRegion->{"Nivel{$i}"});
                        }
                        $query->where("Nivel{$nivelActivo}", '>', 0);

                        for ($i = $nivelActivo + 1; $i <= self::MAX_NIVELES; $i++) {
                            $query->where("Nivel{$i}", 0);
                        }
                    })
            ],
            'isModal' => 'nullable|boolean'
        ]);

        $tipoRegion->update($validatedData);
        return $request->boolean('isModal') ? back() : redirect()->route('tipos-region.index');
    }

    public function destroy(Request $request, TipoRegion $tipoRegion)
    {
        $query = TipoRegion::query();
        $query->where('IdTipoRegion', '!=', $tipoRegion->IdTipoRegion);

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
            throw ValidationException::withMessages(['message' => 'No es posible eliminar el tipo de región seleccionado por tener regiones asociadas.']);
        }

        $regionesUsandoEsteTipo = Region::where('IdTipoRegion', $tipoRegion->IdTipoRegion)->exists();

        if ($regionesUsandoEsteTipo) {
            throw ValidationException::withMessages([
                'message' => 'No es posible eliminar el tipo de región seleccionado por tener regiones asociadas'
            ]);
        }

        $tipoRegion->delete();

        if ($request->boolean('isModal')) {
            return back()->with('success', 'Tipo de Región eliminado.');
        }

        return redirect()->route('tipos-region.index')->with('success', 'Tipo de Región eliminado.');
    }
}
