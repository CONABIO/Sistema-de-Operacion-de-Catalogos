<?php

namespace App\Models\Traits;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

trait OptimizaConsultasCaracteristicas
{   
    private function obtenerAscendenciaCarac($id, $indice)
    {
        $ruta= [];

        while(isset($indice[$id])){
            $nodo = $indice[$id];
            
            array_unshift($ruta, $nodo['Descripcion']);
            $id = $nodo['IdAscendente'];
            
            if(is_null($id)){
                break;
            }
        }

        return $ruta;
    }

    private function indexaArbol($arbol, &$indice=[]){
        
        foreach($arbol as $nodo){
            $indice[$nodo['IdCatNombre']] = $nodo;

            if(!empty($nodo['children'])){
                $this->indexaArbol($nodo['children'], $indice);
            }
        }
        return $indice;
    }

    private function aplanadoAscendenciaCarac($treeData, $idsCaract)
    {
        $indice = $this->indexaArbol($treeData);
    
        $caractPlanas = [];

        foreach($idsCaract as $caract){

            $ascendencia = $this->obtenerAscendenciaCarac($caract, $indice);  

            if($ascendencia != '')
            {
                $ruta = implode('/', $ascendencia);

                $caractPlanas[] = [
                    'idCatNombre'=>$caract,
                    'caracteristica'=>$ruta
                ];
            }
        }

        //Con esta función se agrega un indice en la colección para busqueda rapida de datos
        $caractIndexadas = collect($caractPlanas)
                ->keyBy('idCatNombre');

        return $caractIndexadas;
    }
}