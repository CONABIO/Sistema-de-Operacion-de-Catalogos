<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\RelNombreCatalogo;
use App\Http\Requests\RequestAltaNombreCaract;
use Exception;


class RelNombreCaracteristicasController extends Controller
{
    //public function altaRelNomComun(RequestAltaNomNomComun $request){ 
    public function altaRelTaxCaract(RequestAltaNombreCaract $request){   
        log::info("Estos son los valores que llegan al controlador despues de pasar el request RequestAltaNombreCaract");
        log::info($request);
        log::info("Este es el id nombre: " . $request->idNombre);
        log::info("Este es el id de caracteristica: " . $request->idCaract);
        
        try{
            DB::beginTransaction();

            $rel = RelNombreCatalogo::create([
                'IdNombre' => $request->idNombre,
                'IdCatNombre' => $request->idCaract,
            ]);

            DB::commit();

            return response()->json([
                'message' => "La relación entre taxón y caracteristica se ha generado correctamente."
            ], 200);

        }catch(\Exception $e){
            console.log("Este es el codigo de error", $e);
            DB::rollBack();
            //throw $e;
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
}
