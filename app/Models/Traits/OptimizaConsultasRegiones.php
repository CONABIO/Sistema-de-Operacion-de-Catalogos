<?php

namespace App\Models\Traits;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

trait OptimizaConsultasRegiones
{   
    /*Función para crear la forma de arbol de regiones*/

    private function buildRegionTreeBatch(array $elements): array
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

    /*Función para aplanar la ascendencia de las regiones*/
    private function obtenerAscendencia($nodos, $idBuscado, $ruta = [])
    {
        foreach ($nodos as $nodo) {

            $nuevaRuta = array_merge($ruta, [$nodo->NombreRegion]);

            if ($nodo->IdRegion == $idBuscado) {
                return $nuevaRuta;
            }

            if (!empty($nodo->children)) {

                $resultado = $this->obtenerAscendencia(
                    $nodo->children,
                    $idBuscado,
                    $nuevaRuta
                );

                if ($resultado) {
                    return $resultado;
                }
            }
        }

        return null;
    }

    private function aplanadoAscendencia($treeData, $idsRegiones)
    {
        $regionesPlanas = [];

        foreach($idsRegiones as $region){

            $ascendencia = $this->obtenerAscendencia($treeData, $region);   

            if($ascendencia != '')
            {
                $ruta = implode('/', $ascendencia);

                $regionesPlanas[] = [
                    'IdRegion'=>$region,
                    'Region'=>$ruta
                ];
            }
        }

         /*Con esta función se agrega un indice en la colección para busqueda rapida de datos*/
        $regionesIndexadas = collect($regionesPlanas)
                ->keyBy('IdRegion');
        
        return $regionesIndexadas;
    }
}