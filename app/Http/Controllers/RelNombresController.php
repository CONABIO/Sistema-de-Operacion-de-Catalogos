<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Nombre_Relacion;
use App\Models\RelNombreAutor;
use App\Models\Nombre;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\RequestSinonimos;
use App\Http\Requests\RequestBasonimos;
use App\Http\Requests\RequestEquivalencia;
use App\Http\Requests\RequestHuesped;
use App\Http\Requests\RequestParental;
use App\Http\Requests\RequestHomonimo;
use App\Http\Requests\RequestBorradoNombreRel;
use App\Http\Requests\RequestActualizaNombreRel;
use Exception;


class RelNombresController extends Controller
{
    public function cargaRelaciones(Request $request)
    {
        
        $relaciones = Nombre::cargaRelaciones($request->taxAct)
                            ->get();  

        $reldata = $this->relacionNombre($relaciones);
        
        return $reldata;

    }

    public function altaRelaciones(Request $request){   

        $idTipoRel =  $request['params']['tipRelacion'];

        Log::info("Esta es la relación que acaba de pasar " . $idTipoRel);

        switch($idTipoRel){
            case 1: 
                $reqSinonimos = app(RequestSinonimos::class);
                
                $reqSinonimos->validateResolved();
                  
                $data= $reqSinonimos->validated();

                $estatus = $data['params']['taxonAct']['estatus'];
                
                if($estatus === 'Válido' || $estatus === 'Correcto'){
                    $idNombre = $data['params']['taxonAct']['id'];
                    $idNombreRel = $data['params']['taxonActRel']['id'];
                }else{
                    $idNombre = $data['params']['taxonActRel']['id'];
                    $idNombreRel = $data['params']['taxonAct']['id'];
                }

            break;
            case 2: 
                $reqSinonimos = app(RequestSinonimos::class);
                
                $reqSinonimos->validateResolved();
                  
                $data= $reqSinonimos->validated();

                $reqBasonimos = app(RequestBasonimos::class);
                
                $reqBasonimos->validateResolved();
                  
                $data= $reqBasonimos->validated();

                $estatus = $data['params']['taxonAct']['estatus'];
                
                if($estatus === 'Válido' || $estatus === 'Correcto'){
                    $idNombre = $data['params']['taxonAct']['id'];
                    $idNombreRel = $data['params']['taxonActRel']['id'];
                }else{
                    $idNombre = $data['params']['taxonActRel']['id'];
                    $idNombreRel = $data['params']['taxonAct']['id'];
                }
            break;
            case 3:
                $reqEquivalencia= app(RequestEquivalencia::class);
                
                $reqEquivalencia->validateResolved();
                  
                $data= $reqEquivalencia->validated();

                $idNombre = $data['params']['taxonAct']['id'];
                
                $idNombreRel = $data['params']['taxonActRel']['id'];

                $idNombre = $data['params']['taxonAct']['id'];
                $idNombreRel = $data['params']['taxonActRel']['id'];

                break;
            case 7:
                $reqHuesped = app(RequestHuesped::class);
                
                $reqHuesped->validateResolved();
                  
                $data= $reqHuesped->validated();

                $gruposPara = ["ARACH", "COLEO", "DIPTE", "HYMEN", "INSEC", 
                               "NEMAT", "ACANT", "ANNEL", "CESTO", "CRUST", 
                               "MONOG", "PROT", "MYXOZ", "TREMA"];
                
                $taxonActGrp = $data['params']['taxonAct']['completo']['scat']['grupo_scat']['GrupoAbreviado'];
                
                 if(in_array($taxonActGrp, $gruposPara))
                 {
                   $idNombre = $data['params']['taxonAct']['id'];
 
                   $idNombreRel =  $data['params']['taxonActRel']['id'];

                 }else{
                    $idNombre = $data['params']['taxonActRel']['id'];
 
                    $idNombreRel =  $data['params']['taxonAct']['id'];
                 }

                break;
            case 5:
                $reqParental = app(RequestParental::class);
                
                $reqParental->validateResolved();
                  
                $data= $reqParental->validated();

                $idNombre = $data['params']['taxonAct']['id'];
 
                $idNombreRel =  $data['params']['taxonActRel']['id'];
                
                break;
            case 8:
                $reqHomonimo = app(RequestHomonimo::class);

                $reqHomonimo->validateResolved();
                  
                $data= $reqHomonimo->validated();

                $idNombre = $data['params']['taxonAct']['id'];
 
                $idNombreRel =  $data['params']['taxonActRel']['id'];

                break;
        }
        

        try{
            DB::beginTransaction();

            $rel = Nombre_Relacion::create([
                'IdNombre' => $idNombre,
                'IdNombreRel' => $idNombreRel,
                'IdTipoRelacion' => $idTipoRel
            ]);

            DB::commit();

            $relaciones = Nombre::cargaRelaciones($data['params']['taxonAct']['id'])
                            ->get();  

           $reldata = $this->relacionNombre($relaciones);

            return $reldata;

        }catch(\Exception $e){
            DB::rollBack();
            throw $e;
        }
    }

    protected function relacionNombre($relaciones){
        $reldata= [];
        foreach($relaciones as $relacion)
        {
            switch($relacion->Estatus )
            {
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
                    if($relacion->IdNivel2 === 0)
                    {
                        $status = "Correcto";
                    }else{
                        $status = "Válido";
                    }
                break;
            }

            if($relacion->Biblio > 0)
            {
                $biblio = '/storage/images/Libro_Verde.svg';
            }
            else
            {
                $biblio = '/storage/images/Libro_Rojo.svg';
            }
            
            $newRel = [ 'TipoRelacion' => [ 'idTipoRel' => $relacion->IdTipoRelacion,
                                            'texto' => $relacion->Descripcion, 
                                            'svg' => $relacion->TipoRelIcono,
                                            'relCompleta'=>[
                                                'relIdNombre' => $relacion->RelIdNom,
                                                'relIdNombreRel' => $relacion->RelIdNomRel,
                                                'tipoRel' => $relacion->IdTipoRelacion
                                            ]],                    
                        'idNombre' => $relacion->IdNombre, 
                        'Nombrecompleto' => ['texto' => $relacion->NombreCompleto." ".$relacion->NombreAutoridad." - ".$status." - ".$relacion->SistClasCatDicc,
                                               'url' => $relacion->CategIcono,
                                               'estatus' => $status],
                        'Biblio' => ['texto' => '',
                                       'url' => $biblio,
                                       'contBiblio' => $relacion->Biblio],
                        'FechaCaptura' => $relacion->FechaCaptura,
                        'FechaModificacion' => $relacion->FechaModificacion,
                        'Observaciones' => $relacion->Observaciones  
                       ];
            
            array_push($reldata, $newRel);
        }

        return $reldata;
    }

    public function destroy(RequestBorradoNombreRel $request){

        DB::beginTransaction();

        try {
            Nombre_Relacion::where('IdNombre', $request->relCompleta['relIdNombre'])
                ->where('IdNombreRel', $request->relCompleta['relIdNombreRel'])
                ->where('IdTipoRelacion', $request->relCompleta['tipoRel'])
                ->delete();

            DB::commit();

            $relaciones = Nombre::cargaRelaciones($request->taxAct)
                            ->get();  

            $reldata = $this->relacionNombre($relaciones);

            return $reldata;

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['error' => 'Error al eliminar la relación.'], 500);
        }        
    }

    public function update(RequestActualizaNombreRel $request){
        
        $data = $data = $request->input('data');

        DB::beginTransaction();

        try {
            log::info("Estoy antes de la busqueda");
            $relacion = Nombre_Relacion::where('IdNombre', $data['relCompleta']['relIdNombre'])
                                      ->where('IdNombreRel', $data['relCompleta']['relIdNombreRel'])
                                      ->where('IdTipoRelacion', $data['relCompleta']['tipoRel'])
                                      ->first();

            if($relacion){
                $relacion->update(['Observaciones'=> $data['observacion']]);
            }
            DB::commit();

            $relaciones = Nombre::cargaRelaciones($data['taxAct'])
                            ->get();  

            $reldata = $this->relacionNombre($relaciones);

            return $reldata;

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['error' => 'Error al eliminar la relación.'], 500);
        }      
    }
}
