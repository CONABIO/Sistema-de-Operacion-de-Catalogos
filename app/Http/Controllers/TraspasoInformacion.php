<?php

namespace App\Http\Controllers;

use App\Models\NombreCatalogoRegion;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use App\Models\RelNomNomComunRegionBiblio;
use App\Models\RelNomNomComunRegion;
use App\Models\RelNombreRegion;
use App\Models\RelNombreRegionBiblio;
use App\Models\RelNombreCatalogo;
use App\Models\RelNombreCatalogoBiblio;
use App\Models\RelNombreCatalogoRegion;
use App\Models\RelNombreCatalogoRegionBiblio;
use Illuminate\Support\Facades\DB;

class TraspasoInformacion extends Controller
{
    
    public function traspasoNomCom(Request $request){
            
        DB::connection('catcentral')->transaction(function () use ($request) {

            $idNomCom = 0;
            $idReg = 0;

            foreach ($request->seleccion as $item) {

                if (isset($item['IdNomComun'])) {
                    $idNomCom = $item['IdNomComun'];
                }

                if (isset($item['IdRegion'])) {
                    $idReg = $item['IdRegion'];
                }

                if($idNomCom > 0 && $idReg > 0)
                {
                    $relNomCom = RelNomNomComunRegion::where('IdNombre',$request['idSin'])
                                                     ->where('IdNomComun', $idNomCom)
                                                     ->where('IdRegion', $idReg)
                                                     ->first();

                    if ($relNomCom) {   

                        $datosRelNom = $relNomCom->getAttributes();
                        $datosRelNom['IdNombre'] = $request['idVal'];

                        // Insertar el nuevo registro
                        RelNomNomComunRegion::create($datosRelNom);

                        $biblio = RelNomNomComunRegionBiblio::where('IdNombre',$request['idSin'])
                                                            ->where('IdNomComun', $idNomCom)
                                                            ->where('IdRegion', $idReg)
                                                            ->get();

                        foreach($biblio as $itemBiblio){
                            $originales = $itemBiblio->getAttributes();
                            //cambio de datos
                            $datos = $itemBiblio->getAttributes();
                            $datos['IdNombre'] = $request['idVal'];

                            // Insertar el nuevo registro
                            //RelNomNomComunRegionBiblio::create($datos);
                            DB::connection('catcentral')
                                ->table('RelNomNomComunRegionBiblio')
                                ->insert($datos);

                            //Elimina el original
                            $eliminados = DB::connection('catcentral')
                                ->table('RelNomNomComunRegionBiblio')
                                ->where('IdNombre', $originales['IdNombre'])
                                ->where('IdNomComun', $originales['IdNomComun'])
                                ->where('IdRegion', $originales['IdRegion'])
                                ->where('IdBibliografia', $originales['IdBibliografia'])
                                ->delete();

                        }

                        //Elimina el original
                        $relNomCom->delete();
                    }
                }

            }
        });
        
    }

    public function traspasoRegiones(Request $request){

        DB::connection('catcentral')->transaction(function () use ($request) {

            $idTipDist = 0;
            $idReg = 0;

            foreach ($request->seleccion as $item) {

                if (isset($item['IdRegion'])) {
                    $idReg = $item['IdRegion'];
                }

                if (isset($item['TipoDistribucion']['id'])) {
                    $idTipDist = $item['TipoDistribucion']['id'];
                }

                if($idTipDist > 0 && $idReg > 0)
                {
                    $relNomReg = RelNombreRegion::where('IdNombre',$request['idSin'])
                                                ->where('IdRegion', $idReg)
                                                ->where('IdTipoDistribucion', $idTipDist)
                                                ->first();

                    if ($relNomReg) { 
                        
                        $datosRelReg = $relNomReg->getAttributes();
                        $datosRelReg['IdNombre'] = $request['idVal'];

                        // Insertar el nuevo registro
                        RelNombreRegion::create($datosRelReg);

                        $biblio = RelNombreRegionBiblio::where('IdNombre',$request['idSin'])
                                                       ->where('IdRegion', $idReg)
                                                       ->where('IdTipoDistribucion', $idTipDist)
                                                       ->get();

                        foreach($biblio as $itemBiblio){
                            $originales = $itemBiblio->getAttributes();
                            //cambio de datos
                            $datos = $itemBiblio->getAttributes();
                            $datos['IdNombre'] = $request['idVal'];
                            
                            DB::connection('catcentral')
                                ->table('RelNombreRegionBiblio')
                                ->insert($datos);

                            //Elimina el original
                            $eliminados = DB::connection('catcentral')
                                ->table('RelNombreRegionBiblio')
                                ->where('IdNombre', $originales['IdNombre'])
                                ->where('IdTipoDistribucion', $originales['IdTipoDistribucion'])
                                ->where('IdRegion', $originales['IdRegion'])
                                ->where('IdBibliografia', $originales['IdBibliografia'])
                                ->delete();

                        }
                    }

                    //Elimina el original
                    $relNomReg->delete();
                }
            }

        });
    }

    public function traspasoCaract(Request $request){ 
        
        $idSin = $request['idSin'];
        $idVal = $request['idVal'];  

        try{

            DB::connection('catcentral')->transaction(function () use ($request, $idSin, $idVal){

                foreach($request->seleccion as $item) {
                    
                    if($item['tipo'] === 'caracteristica') {       

                        $this->valCreaRelCarac($item['id'], $idSin, $idVal);

                        $cont = RelNombreCatalogoRegion::where('IdNombre', $idSin)
                                                    ->where('IdCatNombre', $item['id'])
                                                    ->count();
                        if($cont === 0){                    
                            $eliminados = DB::connection('catcentral')
                                        ->table('RelNombreCatalogo')
                                        ->where('IdNombre', $idSin)
                                        ->where('IdCatNombre', $item['id'])                                  
                                        ->delete();
                        }
                        
                    }else{

                        $this->valCreaRelCaractReg($item, $idSin, $idVal);

                        $idCaract = explode('-', $item['treeKey'])[1];
                        $cont = RelNombreCatalogoRegion::where('IdNombre', $idSin)
                                                    ->where('IdCatNombre', $idCaract)
                                                    ->count();
                        if($cont === 0){
                            $eliminados = DB::connection('catcentral')
                                        ->table('RelNombreCatalogo')
                                        ->where('IdNombre', $idSin)
                                        ->where('IdCatNombre', $idCaract)                                  
                                        ->delete();
                        }
                    }                
                }
            });

        } catch(\Throwable $e){
            Log::error('Error en el traspaso de características: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Ocurrio un error durante el traspaso.',
                'error' => $e->getMessage()
            ], 500);
        }        
    }

    private function valCreaRelCarac($idCatNom, $idSin, $idVal){

        // Se recupera el registro para validar su existencia y el resto de los datos 
        $relCarac = RelNombreCatalogo::where('IdNombre', $idSin)
                                     ->where('IdCatNombre', $idCatNom)
                                     ->first();
        
        if ($relCarac) {
            $datosRelCat = $relCarac->getAttributes();
            $datosRelCat['IdNombre'] = $idVal;

            //Se valida si existe el registro en la tabla antes de continuar 
            $existRelCat = RelNombreCatalogo::where('IdNombre', $datosRelCat['IdNombre'])
                                            ->where('IdCatNombre', $datosRelCat['IdCatNombre'])
                                            ->exists();

            if(!$existRelCat){
                RelNombreCatalogo::create($datosRelCat);
            }

            $biblio = RelNombreCatalogoBiblio::where('IdNombre', $idSin)
                                             ->where('IdCatNombre', $idCatNom)                                                         
                                             ->get();

            foreach($biblio as $itemBiblio){
                $originales = $itemBiblio->getAttributes();
                            //cambio de datos
                $datos = $itemBiblio->getAttributes();
                $datos['IdNombre'] = $idVal;

                $existe = RelNombreCatalogoBiblio::where('IdNombre', $datos['IdNombre'])
                                                 ->where('IdCatNombre', $datos['IdCatNombre'])
                                                 ->where('IdBibliografia', $datos['IdBibliografia'])
                                                 ->exists();
                
                if(!$existe){
                    DB::connection('catcentral')
                        ->table('RelNombreCatalogoBiblio')
                        ->insert($datos);
                }
            }
        }

    }

    private function valCreaRelCaractReg($registro, $idSin, $idVal){

        $idReg = $registro['id'];
        $idCaract = explode('-', $registro['treeKey'])[1];

        $relCarac = RelNombreCatalogoRegion::where('IdNombre', $idSin)
                                           ->where('IdCatNombre', $idCaract)
                                           ->where('IdRegion', $idReg)
                                           ->where('IdTipoDistribucion', $registro['tipDistribucion'])
                                           ->first();        

        $this->valCreaRelCarac($idCaract, $idSin, $idVal);

        $datos = $relCarac->getAttributes();
        $datos['IdNombre'] = $idVal;

        $existe = RelNombreCatalogoRegion::where('IdNombre', $datos['IdNombre'])
                                         ->where('IdCatNombre', $datos['IdCatNombre'])
                                         ->where('IdRegion', $datos['IdRegion'])
                                         ->where('IdTipoDistribucion', $datos['IdTipoDistribucion'])
                                         ->exists();   
        
        if(!$existe){
            DB::connection('catcentral')
                ->table('RelNombreCatalogoRegion')
                ->insert($datos);
        }

        $biblioReg = RelNombreCatalogoRegionBiblio::where('IdNombre', $idSin)
                                                  ->where('IdCatNombre', $idCaract)
                                                  ->where('IdRegion', $idReg)
                                                  ->where('IdTipoDistribucion', $registro['tipDistribucion'])                                                        
                                                  ->get();
        
        foreach($biblioReg as $itemBiblio){

            $originales = $itemBiblio->getAttributes();
                            //cambio de datos
            $datos = $itemBiblio->getAttributes();
            $datos['IdNombre'] = $idVal;

            $existe = RelNombreCatalogoRegionBiblio::where('IdNombre', $datos['IdNombre'])
                                                   ->where('IdCatNombre', $datos['IdCatNombre'])
                                                   ->where('IdRegion', $datos['IdRegion'])
                                                   ->where('IdTipoDistribucion', $datos['IdTipoDistribucion'])
                                                   ->where('IdBibliografia', $datos['IdBibliografia'])
                                                   ->exists();
            
            if(!$existe){
                DB::connection('catcentral')
                    ->table('RelNombreCatalogoRegionBiblio')
                    ->insert($datos);
            }

            //Borrar la bibliografia insertada 
            $eliminados = DB::connection('catcentral')
                                 ->table('RelNombreCatalogoRegionBiblio')
                                 ->where('IdNombre', $originales['IdNombre'])
                                 ->where('IdCatNombre', $originales['IdCatNombre'])
                                 ->where('IdRegion', $originales['IdRegion'])
                                 ->where('IdTipoDistribucion', $datos['IdTipoDistribucion'])
                                 ->where('IdBibliografia', $originales['IdBibliografia'])
                                 ->delete();
        }
        
        $eliminaReg = DB::connection('catcentral')
                             ->table('RelNombreCatalogoRegion')
                             ->where('IdNombre', $relCarac['IdNombre'])
                             ->where('IdCatNombre', $relCarac['IdCatNombre'])
                             ->where('IdRegion', $relCarac['IdRegion'])
                             ->where('IdTipoDistribucion', $relCarac['IdTipoDistribucion'])
                             ->delete();

    }
}
