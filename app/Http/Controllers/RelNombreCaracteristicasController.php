<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\RelNombreCatalogo;
use App\Models\RelNombreCatalogoRegion;
use App\Http\Requests\RequestAltaNombreCaract;
use App\Http\Requests\RequestAltaNombreCaractReg;
use Exception;


class RelNombreCaracteristicasController extends Controller
{
    //public function altaRelNomComun(RequestAltaNomNomComun $request){ 
    public function altaRelTaxCaract(RequestAltaNombreCaract $request){   
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
            DB::rollBack();
            //throw $e;
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    public function altaRelTaxCaractReg(RequestAltaNombreCaractReg $request){
        try{
            DB::beginTransaction();

            $rel = RelNombreCatalogoRegion::create([
                'IdNombre' => $request->idNombre,
                'IdCatNombre' => $request->idCaract,
                'IdRegion' => $request->idRegion, 
                'IdTipoDistribucion' => $request->idTipoDistribucion
            ]);

            DB::commit();

            return response()->json([
                'message' => "La relación entre taxón, caracteristica y region se ha generado correctamente."
            ], 200);

        }catch(\Exception $e){            
            DB::rollBack();
            //throw $e;
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
