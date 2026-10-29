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
        DB::connection('catcentral')->transaction(function () use ($request) {
        
            $idCaract = 0;
            $idTipDist = 0;
            $idReg = 0;

            foreach($request->seleccion as $item) {

                if (isset($item['id'])) {
                    $idCaract = $item['id'];
                }

                Log::info("idCaracteristicas: " . $idCaract);
                $relCaract = RelNombreCatalogo::where('IdNombre',$request['idSin'])
                                            ->where('IdCatNombre', $idCaract)                                          
                                            ->first();

                if($relCaract){

                    $datosRelCaract = $relCaract->getAttributes();
                    $datosRelCaract['IdNombre'] = $request['idVal'];

                    //Insertar un nuevo registro en caracteristicas
                    RelNombreCatalogo::create($datosRelCaract);
                    
                    $biblioCaract = RelNombreCatalogoBiblio::where('IdNombre',$request['idSin'])
                                                        ->where('IdCatNombre', $idCaract)
                                                        ->get();

                    //Cambiamos y eliminamos las bibliografias de las caracteristicas                                        
                    foreach($biblioCaract as $itemBiblio){
                        $originales = $itemBiblio->getAttributes();
                        
                        //cambio de datos
                        $datos = $itemBiblio->getAttributes();
                        $datos['IdNombre'] = $request['idVal'];

                        DB::connection('catcentral')
                            ->table('RelNombreCatalogoBiblio')
                            ->insert($datos);

                        //Eliminar el original
                        $eliminados = DB::connection('catcentral')
                            ->table('RelNombreCatalogoBiblio')
                            ->where('IdNombre', $originales['IdNombre'])
                            ->where('IdCatNombre', $originales['IdCatNombre'])
                            ->where('IdBibliografia', $originales['IdBibliografia'])
                            ->delete();
                    }

                    //Recorremos las regiones 
                    foreach($item['children'] ?? [] as $region){
                        $idReg = $region['id'];
                        $idTipDist = $region['tipDistribucion'];

                        $relRegCaract = RelNombreCatalogoRegion::where('IdNombre',$request['idSin'])
                                                            ->where('IdCatNombre', $idCaract) 
                                                            ->where('IdRegion', $idReg)
                                                            ->where('IdTipoDistribucion', $idTipDist)                                         
                                                            ->first();

                        if($relRegCaract){
                            $originales = $relRegCaract->getAttributes();
                        
                            //cambio de datos
                            $datos = $relRegCaract->getAttributes();
                            $datos['IdNombre'] = $request['idVal'];

                            DB::connection('catcentral')
                                ->table('RelNombreCatalogoRegion')
                                ->insert($datos);

                            $biblioRegCaract = RelNombreCatalogoRegionBiblio::where('IdNombre', $originales['IdNombre'])
                                                                            ->where('IdCatNombre', $originales['IdCatNombre'])
                                                                            ->where('IdRegion', $originales['IdRegion'])
                                                                            ->where('IdTipoDistribucion', $originales['IdTipoDistribucion'])
                                                                            ->get();

                            //Se recorren las bibliografias de las regiones
                            foreach($biblioRegCaract as $biblioCaract){
                                $biblioOrig = $biblioCaract->getAttributes();
                                
                                //Cambio de datos
                                $datosBiblio = $biblioCaract->getAttributes();
                                $datosBiblio['IdNombre'] = $request['idVal'];

                                DB::connection('catcentral')
                                    ->table('RelNombreCatalogoRegionBiblio')
                                    ->insert($datosBiblio);

                                $elimRegBiblio = DB::connection('catcentral')
                                    ->table('RelNombreCatalogoRegionBiblio')
                                    ->where('IdNombre', $biblioOrig['IdNombre'])
                                    ->where('IdCatNombre', $biblioOrig['IdCatNombre'])
                                    ->where('IdRegion', $biblioOrig['IdRegion'])
                                    ->where('IdTipoDistribucion', $biblioOrig['IdTipoDistribucion'])
                                    ->where('IdBibliografia', $biblioOrig['IdBibliografia'])
                                    ->delete();
                            }    

                            //Eliminar el original
                            $eliminados = DB::connection('catcentral')
                                ->table('RelNombreCatalogoRegion')
                                ->where('IdNombre', $originales['IdNombre'])
                                ->where('IdCatNombre', $originales['IdCatNombre'])
                                ->where('IdRegion', $originales['IdRegion'])
                                ->where('IdTipoDistribucion', $originales['IdTipoDistribucion'])
                                ->delete();

                        }

                    }

                }

                //Eliminar original
                $relCaract->delete();
            }
        });
    }
}
