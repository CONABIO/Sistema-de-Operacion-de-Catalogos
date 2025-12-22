<?php

namespace App\Http\Controllers;

use App\Models\Tipo_Relacion;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;


use App\Models\Nombre;
use App\Models\Nombre_Relacion;
use App\Models\RelacionBibliografia;

class TipoRelacionController extends Controller
{
    public function inicioTipRel()
    {

        $arbolCompleto = $this->construirArbolRelaciones(0, 1);

        return response()->json($arbolCompleto);
    }

    private function construirArbolRelaciones($nivelPadre, $nivelActual = 1, $valoresAnteriores = [])
    {
        $query = Tipo_Relacion::select(
            'IdTipoRelacion AS value',
            'Descripcion AS label',
            'RutaIcono AS icono',
            'Nivel1',
            'Nivel2',
            'Nivel3',
            'Nivel4',
            'Nivel5'
        );

        // Filtrar niveles anteriores (modificado)
        foreach ($valoresAnteriores as $nivel => $valor) {
            $numeroNivel = str_replace('Nivel', '', $nivel); // Extrae solo el número
            $query->where("Nivel$numeroNivel", $valor);
        }

        // Filtrar el nivel actual
        if ($nivelPadre > 0) {
            $query->where("Nivel$nivelActual", $nivelPadre);
        } else {
            $query->where("Nivel$nivelActual", '>', 0);
        }

        // Filtrar niveles superiores
        for ($i = $nivelActual + 1; $i <= 5; $i++) {
            $query->where("Nivel$i", 0);
        }

        $query->orderBy("Nivel$nivelActual");

        $registros = $query->get();

        if ($registros->isNotEmpty() && $nivelActual < 5) {
            foreach ($registros as $registro) {
                $nuevosValores = $valoresAnteriores;
                // Modificado para guardar solo el número como clave
                $nuevosValores[$nivelActual] = $registro->{"Nivel$nivelActual"};
                $registro->children = $this->construirArbolRelaciones(0, $nivelActual + 1, $nuevosValores);
            }
        }

        return $registros;
    }

    public function cargaRelaciones(Request $request)
    {

        $ids = collect($request->ids)->pluck('idNom')->toArray();

        $tipRel = collect($request->ids)->pluck('idTipRel')->unique()->toArray();

        $iconTipRel = Tipo_Relacion::whereIn('IdTipoRelacion', $tipRel)
            ->get();

        $nombres = Nombre::whereIn('IdNombre', $ids)
            ->with(['nombreRel', 'categoria'])
            ->get();



        $data = [];
        foreach ($nombres as $nombre) {
            $taxRel = collect($request->ids)->firstWhere('idNom', $nombre->IdNombre);

            $icoTip = collect($iconTipRel)->firstWhere('IdTipoRelacion', $taxRel['idTipRel']);

            $biblio = RelacionBibliografia::where('IdNombre', $request->idAct)
                ->where('IdNombreRel', $nombre->IdNombre)
                ->where('IdTipoRelacion', $taxRel['idTipRel'])
                ->with(['bibliografia'])
                ->get();

            $todasBiblio = $biblio->pluck('bibliografia')->filter()->values()->toArray();

            if ($nombre->EstadoRegistro === 1) {
                switch ($nombre->Estatus) {
                    case 1:
                        $status = "Sinonimo";
                        break;
                    case 6;
                        $status = "NA";
                        break;
                    case -9:
                        $status = "ND";
                        break;
                    default:
                        switch ($nombre->Nombre) {
                            case 'Animalia':
                                $status = "Válido";
                                break;
                            case 'Plantae':
                            case 'Fungi':
                            case 'Protozoa':
                            case 'Archaea':
                            case 'Bacteria':
                            case 'Chromista':
                                $status = "Correcto";
                                break;
                            default:
                                if ($nombre->categoria->IdNivel2 === 0) {
                                    $status = "Correcto";
                                } else {
                                    $status = "Válido";
                                }
                        }
                        break;
                }

                $nomCat = $nombre->categoria->NombreCategoriaTaxonomica .
                    " - Autor taxón Estatus Sist. Clas./Catálogo de autoridad/Diccionario ";

                $etiqueta = $nombre->NombreCompleto . " " . $nombre->NombreAutoridad . " - " . $status . " - " . $nombre->SistClasCatDicc;

                $newHijo = [
                    'id' => $nombre->IdNombre,
                    'label' => $etiqueta,
                    'texto' => $nomCat,
                    'estatus' => $status,
                    'completo' => $nombre,
                    'iconoCat' => $nombre->categoria->RutaIcono,
                    'desIconCat' => $nombre->categoria->NombreCategoriaTaxonomica,
                    'iconoTipRel' => $icoTip->RutaIcono,
                    'desIconTip' => $icoTip->Descripcion,
                    'observaciones' => $taxRel['obser'] ?? null,
                    'fechaCaptura' => $taxRel['fechaCap'] ?? null,
                    'fechaModificacion' => $taxRel['fechaMod'] ?? null,
                    'Bibliografia' => $todasBiblio
                ];

                array_push($data, $newHijo);
            }
        }

        return $data;
    }

    //==============================================================================
    public function index()
    {
        $todosLosNodosPlanos = Tipo_Relacion::orderBy('Nivel1')
            ->orderBy('Nivel2')
            ->orderBy('Nivel3')
            ->orderBy('Nivel4')
            ->orderBy('Nivel5')
            ->get();

        $treeDataParaVisualizacion = $this->buildTreeFromLevels($todosLosNodosPlanos);

        return Inertia::render('Socat/TipoRelacion/indexTipoRelacion', [
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
            $key = 'L' . implode('_', array_filter([$node->Nivel1, $node->Nivel2, $node->Nivel3, $node->Nivel4, $node->Nivel5], fn($v) => $v > 0));
            $nodeMap[$key] = $nodeArray;
            $parentKeyArray = array_filter([$node->Nivel1, $node->Nivel2, $node->Nivel3, $node->Nivel4, $node->Nivel5], fn($v) => $v > 0);
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
        $validatedData = $request->validate([
            'Descripcion' => ['required', 'string', 'max:255', Rule::unique(Tipo_Relacion::class)],
            'Direccionalidad' => 'required|integer|in:1,2,3',
            'Nivel1' => 'required|integer',
            'Nivel2' => 'required|integer',
            'Nivel3' => 'required|integer',
            'Nivel4' => 'required|integer',
            'Nivel5' => 'required|integer',
        ], [
            // Agregamos este arreglo con el mensaje personalizado
            'Descripcion.unique' => 'La descripción ya ha sido registrada anteriormente.',
        ]);

        $tipoRelacion = Tipo_Relacion::create($validatedData);

        return redirect()->route('tipos-relacion.index')
            ->with('success', 'Tipo de Relación creado correctamente.')
            ->with('flash', ['newNodeId' => $tipoRelacion->IdTipoRelacion]);
    }

    public function update(Request $request, Tipo_Relacion $tipoRelacion)
    {
        if ($tipoRelacion->IdTipoRelacion <= 8) {
            throw ValidationException::withMessages([
                'protected' => 'La relación seleccionada no puede ser modificada por ser un registro protegido del sistema.'
            ]);
        } else {
            $validatedData = $request->validate([
                'Descripcion' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique(Tipo_Relacion::class)->ignore($tipoRelacion->IdTipoRelacion, 'IdTipoRelacion')
                ],
                'Direccionalidad' => 'required|integer|in:1,2,3',
            ], [
                // Agregamos este arreglo con el mensaje personalizado
                'Descripcion.unique' => 'La descripción ya ha sido registrada anteriormente.',
            ]);

            $tipoRelacion->update($validatedData);

            return redirect()->route('tipos-relacion.index')
                ->with('success', 'Tipo de Relación actualizado correctamente.')
                ->with('flash', ['newNodeId' => $tipoRelacion->IdTipoRelacion]);
        }
    }

    public function destroy(Tipo_Relacion $tipoRelacion)
    {
        if ($tipoRelacion->IdTipoRelacion <= 8) {
            return Redirect::back()->withErrors(['message' => 'La relación seleccionada no puede ser eliminada por ser un registro protegido del sistema.']);
        }

        $query = Tipo_Relacion::query();
        $profundidad = 0;
        for ($i = 1; $i <= 5; $i++) {
            if ($tipoRelacion->{"Nivel{$i}"} > 0) {
                $query->where("Nivel{$i}", $tipoRelacion->{"Nivel{$i}"});
                $profundidad = $i;
            } else {
                break;
            }
        }
        if ($profundidad < 5) {
            $query->where("Nivel" . ($profundidad + 1), '>', 0);
        }
        if ($profundidad < 5 && $query->exists()) {
            return Redirect::back()->withErrors(['message' => 'No se puede eliminar un elemento que tiene subordinados en el árbol.']);
        }

        try {
            $tipoRelacion->delete();
            return Redirect::back()->with('success', 'Tipo de Relación eliminado con éxito.');
        } catch (QueryException $e) {
            $errorCode = $e->errorInfo[1];
            if ($errorCode == 1451) {
                return Redirect::back()->withErrors(['message' => 'No se puede eliminar. Este tipo de relación está en uso en otra parte del sistema.']);
            }

            return Redirect::back()->withErrors(['message' => 'Error de base de datos al intentar eliminar el registro.']);
        }
    }


    public function updateIcon(Request $request, Tipo_Relacion $tipoRelacion)
    {
        $request->validate([
            'RutaIcono' => 'nullable|string',
        ]);

        $tipoRelacion->update([
            'RutaIcono' => $request->RutaIcono,
        ]);

        return redirect()->route('tipos-relacion.index')
            ->with('success', 'Ícono actualizado.');
    }
}
