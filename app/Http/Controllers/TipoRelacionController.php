<?php

namespace App\Http\Controllers;

use App\Models\Tipo_Relacion;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class TipoRelacionController extends Controller
{
    //
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

    public function cargaRelacionesInicio(Request $request)
    {
        log::info("llegue al controlador de relaciones");
        log::info($request->tipRel);

        switch ($request->tipRel){
            case 1:
                if($request->status)
                break;
        }
    }
}
