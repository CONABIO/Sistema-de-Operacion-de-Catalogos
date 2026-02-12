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
        $levelRules = [];
        for ($i = 1; $i <= self::MAX_NIVELES; $i++) {
            $levelRules["Nivel{$i}"] = 'required|integer';
        }
        $levelRules['isModal'] = 'nullable|boolean';

        $niveles = $request->only(['Nivel1', 'Nivel2', 'Nivel3', 'Nivel4', 'Nivel5']);
        $nivelActivo = 1;
        for ($i = self::MAX_NIVELES; $i >= 1; $i--) {
            if ($niveles["Nivel{$i}"] > 0) {
                $nivelActivo = $i;
                break;
            }
        }

        $validatedData = $request->validate(array_merge([
            'Descripcion' => [
                'required',
                'string',
                'max:255',
                Rule::unique(TipoRegion::class)->where(function ($query) use ($niveles, $nivelActivo) {
                    for ($i = 1; $i < $nivelActivo; $i++) {
                        $query->where("Nivel{$i}", $niveles["Nivel{$i}"]);
                    }
                    for ($i = $nivelActivo + 1; $i <= self::MAX_NIVELES; $i++) {
                        $query->where("Nivel{$i}", 0);
                    }
                }),
            ],
        ], $levelRules), [
            'Descripcion.required' => 'La descripción es obligatoria.',
            'Descripcion.unique' => 'Esta descripción ya está registrada en este nivel. Por favor intente con otra.',
        ]);

        TipoRegion::create($validatedData);

        if ($request->boolean('isModal')) {
            return back()->with('success', 'Tipo de Región creado.');
        }
        return redirect()->route('tipos-region.index')->with('success', 'Tipo de Región creado.');
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
                        for ($i = $nivelActivo + 1; $i <= self::MAX_NIVELES; $i++) {
                            $query->where("Nivel{$i}", 0);
                        }
                    })
            ],
            'isModal' => 'nullable|boolean'
        ], [
            'Descripcion.required' => 'La descripción es obligatoria.',
            'Descripcion.unique' => 'Esta descripción ya está registrada en este nivel.',
        ]);

        $tipoRegion->update($validatedData);

        if ($request->boolean('isModal')) {
            return back()->with('success', 'Tipo de Región actualizado.');
        }

        return redirect()->route('tipos-region.index')->with('success', 'Tipo de Región actualizado.');
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
            throw ValidationException::withMessages(['message' => 'No es posible eliminar la relación seleccionada por tener otras relaciones que dependen de ella.']);
        }

        $regionesUsandoEsteTipo = Region::where('IdTipoRegion', $tipoRegion->IdTipoRegion)->exists();

        if ($regionesUsandoEsteTipo) {
            throw ValidationException::withMessages([
                'message' => 'No es posible eliminar el tipo de region seleccionado por tener regiones que dependen de ella.'
            ]);
        }

        $tipoRegion->delete();

        if ($request->boolean('isModal')) {
            return back()->with('success', 'Tipo de Región eliminado.');
        }

        return redirect()->route('tipos-region.index')->with('success', 'Tipo de Región eliminado.');
    }
}
