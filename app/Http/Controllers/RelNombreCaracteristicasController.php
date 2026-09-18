<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\RelNombreCatalogo;
use App\Models\RelNombreCatalogoRegion;
use App\Models\RelNombreCatalogoRegionBiblio;
use App\Models\RelNombreCatalogoBiblio;
use App\Http\Requests\RequestAltaNombreCaract;
use App\Http\Requests\RequestAltaNombreCaractReg;
use App\Http\Requests\RequestActualizaNombreCaract;
use App\Http\Requests\RequestActualizaNombreCaractReg;
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
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    public function altaRelTaxCaractReg(RequestAltaNombreCaractReg $request){
        try{
            log::info("Esto es lo que llega en el requets");
            log::info($request);

            DB::beginTransaction();
            
            $existe = RelNombreCatalogo::where('IdNombre', $request['idNombre'])
                                       ->where('IdCatNombre', $request['idCaract'])
                                       ->exists();


            if(!$existe){
                log::info("Si entre al if");
                $relCaract = RelNombreCatalogo::create([
                    'IdNombre' => $request['idNombre'],
                    'IdCatNombre' => $request['idCaract'],
                ]);

            }

            $relCaractReg = RelNombreCatalogoRegion::create([
                'IdNombre' => $request['idNombre'],
                'IdCatNombre' => $request['idCaract'],
                'IdRegion' => $request['idRegion'], 
                'IdTipoDistribucion' => $request['idTipoDistribucion']
            ]);

            DB::commit();

            return response()->json([
                'message' => "La relación entre taxón, caracteristica y region se ha generado correctamente."
            ], 200);

        }catch(\Exception $e){            
            DB::rollBack();
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }

    function actCaractTaxon(RequestActualizaNombreCaract $request){
        try{
            DB::beginTransaction();

            $relacion = RelNombreCatalogo::where('IdNombre', $request['idNombre'])
                                         ->where('IdCatNombre', $request['idCatNombre'])
                                         ->first();

            if($relacion){
                $relacion->update(['Observaciones'=> $request['observaciones']]);
            }

            DB::commit();

            return response()->json([
                'message' => "La relación entre taxón y caracteristica se ha actualizado correctamente.",
            ], 200);

        }catch(\Exception $e){            
            DB::rollBack();
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }

    function actCaractTaxonReg(RequestActualizaNombreCaractReg $request){

        try{
            DB::beginTransaction();

            $relBiblio = RelNombreCatalogoRegionBiblio::where('IdNombre', $request['idNombre'])
                                                      ->where('IdCatNombre', $request['idCatNombre'])
                                                      ->where('IdRegion', $request['idRegion'])
                                                      ->where('IdTipoDistribucion', $request['idTipoDistAct'])
                                                      ->update(['IdTipoDistribucion' => $request['idTipoDistNue']]);

            $relacion = RelNombreCatalogoRegion::where('IdNombre', $request['idNombre'])
                                               ->where('IdCatNombre', $request['idCatNombre'])
                                               ->where('IdRegion', $request['idRegion'])
                                               ->where('IdTipoDistribucion', $request['idTipoDistAct'])
                                               ->update(['IdTipoDistribucion' => $request['idTipoDistNue'],
                                                         'Observaciones' => $request['observaciones']]);   

            DB::commit();;  

            return response()->json([
                'message' => "La relación entre taxón, caracteristica y region se ha actualizado correctamente.",
            ], 200);

        }catch(\Exception $e){            
            DB::rollBack();
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }

    function eliminaRegCaractReg(Request $request){
        
        try{
            DB::beginTransaction();

            $relacionBiblio = RelNombreCatalogoRegionBiblio::where('IdNombre', $request['idNombre'])
                                                           ->where('IdCatNombre', $request['idCaract'])
                                                           ->where('IdRegion', $request['idRegion'])
                                                           ->where('IdTipoDistribucion', $request['idTipoDist'])
                                                           ->delete(); 
            
            $relacion = RelNombreCatalogoRegion::where('IdNombre', $request['idNombre'])
                                               ->where('IdCatNombre', $request['idCaract'])
                                               ->where('IdRegion', $request['idRegion'])
                                               ->where('IdTipoDistribucion', $request['idTipoDist'])                        
                                               ->delete();

            DB::commit();
            return response()->json([
                'message' => "La relación entre taxón, caracteristica y region se ha eliminado correctamente.",
            ], 200);

        }catch(\Exception $e){            
            DB::rollBack();
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }

    function eliminaRegCaract(Request $request){
        
        try{
            DB::beginTransaction();

            $relacionRegBiblio = RelNombreCatalogoRegionBiblio::where('IdNombre', $request['idNombre'])
                                                              ->where('IdCatNombre', $request['idCaract'])
                                                              ->delete(); 
            
            $relacionReg = RelNombreCatalogoRegion::where('IdNombre', $request['idNombre'])
                                                  ->where('IdCatNombre', $request['idCaract'])                       
                                                  ->delete();
            
            $relacionCaractBiblio = RelNombreCatalogoBiblio::where('IdNombre', $request['idNombre'])
                                                           ->where('IdCatNombre', $request['idCaract'])                       
                                                           ->delete();
            
            $relacionCaract = RelNombreCatalogo::where('IdNombre', $request['idNombre'])
                                               ->where('IdCatNombre', $request['idCaract'])                       
                                               ->delete();

            DB::commit();
            return response()->json([
                'message' => "La relación entre taxón, caracteristica y region se ha eliminado correctamente.",
            ], 200);

        }catch(\Exception $e){            
            DB::rollBack();
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
