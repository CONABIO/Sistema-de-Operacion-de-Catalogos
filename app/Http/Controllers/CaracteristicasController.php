<?php

namespace App\Http\Controllers;

use App\Models\CatalogoNombre;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class CaracteristicasController extends Controller
{
    public function index(Request $request): InertiaResponse
    {
        $todosLosNodosPlanos = CatalogoNombre::orderBy('Nivel1')
            ->orderBy('Nivel2')
            ->orderBy('Nivel3')
            ->orderBy('Nivel4')
            ->orderBy('Nivel5')
            ->orderBy('Nivel6')
            ->orderBy('Nivel7')
            ->orderBy('Descripcion')
            ->get();

        $treeDataParaVisualizacion = $this->buildTreeOptimized($todosLosNodosPlanos);

        return Inertia::render('Socat/Caracteristicas/indexCaracteristicas', [
            'treeDataProp' => $treeDataParaVisualizacion,
            'flatTreeDataProp' => $todosLosNodosPlanos,
            'errors' => session('errors') ? session('errors')->getBag('default')->getMessages() : (object) [],
            

        ]);
    }

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
            $errorMsg = 'No se puede eliminar la característica "' . ($nodo->Descripcion ?? $nodo->IdCatNombre) . '" porque tiene hijos asociados.';
            return response()->json(['error' => $errorMsg], 409); // 409 Conflict es un buen código para esto
        }

        try {
            $nodo->delete();
            return response()->json(['message' => 'Característica eliminada correctamente.'], 200);
        } catch (\Exception $e) {
            Log::error("Error al eliminar característica {$id}: " . $e->getMessage());
            return response()->json(['error' => 'Ocurrió un error del servidor al intentar eliminar.'], 500);
        }
    }


    public function update(Request $request, $id)
    {
        $nodo = CatalogoNombre::find($id);

        if (!$nodo) {
            // El return aquí debería ser con flash para que el frontend lo muestre.
            return redirect()->back()->with('error', 'Característica no encontrada.');
        }

        $validatedData = $request->validate([
            'Descripcion' => [
                'required',
                'string',
                'max:255',
                Rule::unique('catcentral.CatalogoNombre', 'Descripcion')
                    ->ignore($nodo->IdCatNombre, $nodo->getKeyName())
            ],
        ], [
            'Descripcion.required' => 'La descripción es obligatoria.',
            'Descripcion.unique' => 'La descripción ingresada ya existe en el catálogo.',
            'Descripcion.max' => 'La descripción no puede tener más de 255 caracteres.',
        ]);

        try {
            $nodo->Descripcion = $validatedData['Descripcion'];
            $nodo->save();

            // CAMBIO 2: Añadimos el ID del nodo actualizado a la sesión flash.
            // Ahora, después de editar, el frontend también sabrá qué nodo enfocar.
            return redirect()->route('caracteristicas-taxon.index')
                ->with('success', 'Característica "' . $nodo->Descripcion . '" actualizada correctamente.')
                ->with('newNodeId', $nodo->IdCatNombre);
        } catch (\Exception $e) {
            Log::error("Error al actualizar característica {$id}: " . $e->getMessage());
            return redirect()->back()->with('error', 'Ocurrió un error al intentar actualizar la característica.');
        }
    }

    public function store(Request $request)
    {
        $maxNiveles = 7;
        $validationRules = [ ];
        Validator::make($request->all(), $validationRules, [ /* ... */])->after(function ($validator) { /* ... */
        })->validate();


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

        Log::info('ID Generado después de guardar: ' . $caracteristica->IdCatNombre);
        if (!$caracteristica->IdCatNombre) {
            Log::error('El IdCatNombre es nulo después de guardar la característica.');
        }

        if (!$caracteristica->IdOriginal && $caracteristica->IdCatNombre) {
            $caracteristica->IdOriginal = $caracteristica->IdCatNombre;
            $caracteristica->save();
        }

        return redirect()->route('caracteristicas-taxon.index')
            ->with('success', 'Característica creada correctamente.')
            ->with('newNodeId', $caracteristica->IdCatNombre);
    }
}
