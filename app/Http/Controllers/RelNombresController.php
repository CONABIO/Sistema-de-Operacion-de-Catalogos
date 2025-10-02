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

    public function altaRelaciones(Request $request)
    {   

        $idTipoRel =  $request['params']['tipRelacion'];

        Log::info("Esta es la relación que acaba de pasar " . $idTipoRel);

        switch($idTipoRel){
            case 1: 
                $reqSinonimos = app(RequestSinonimos::class);
                
                $reqSinonimos->validateResolved();
                  
                $data= $reqSinonimos->validated();

                $estatus = $data['params']['taxonAct']['estatus'];

                if($estatus === 'Válido'){
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

                if($estatus === 'Válido'){
                    $idNombre = $data['params']['taxonAct']['id'];
                    $idNombreRel = $data['params']['taxonActRel']['id'];
                }else{
                    $idNombre = $data['params']['taxonActRel']['id'];
                    $idNombreRel = $data['params']['taxonAct']['id'];
                }
            break;
            case 3:
                $reqSinonimos = app(RequestEquivalencia::class);
                
                $reqSinonimos->validateResolved();
                  
                $data= $reqSinonimos->validated();

                $idNombre = $data['params']['taxonAct']['id'];
                
                $idNombreRel = $data['params']['taxonActRel']['id'];

                break;
            case 7:
                Log::info('Entre a la validacion de Huesped ');

                $reqHuesped = app(RequestHuesped::class);
                
                $reqHuesped->validateResolved();
                  
                $data= $reqHuesped->validated();

                Log::info("Esto es lo que llego a data");

                $gruposPara = ["ARACH", "COLEO", "DIPTE", "HYMEN", "INSEC", 
                               "NEMAT", "ACANT", "ANNEL", "CESTO", "CRUST", 
                               "MONOG", "PROT", "MYXOZ", "TREMA"];

                $taxonActGrp = $this->input('params.taxonAct.completo.scat.grupo_scat.GrupoAbreviado', []); 

                Log::info("Este es el vaor del grupo en el controlador");

                 if(in_array($taxonActGrp, $gruposPara))
                 {
                    Log::info("Es parasito");
                 }else{
                    Log::info("Es vertebrado");
                 }

                Log::info($data);

                break;
            case 5:
                 $reqParental = app(RequestParental::class);
                
                $reqParental->validateResolved();
                  
                $data= $reqParental->validated();
                
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
                                            'svg' => $relacion->TipoRelIcono],                    
                        'idNombre' => $relacion->IdNombre, 
                        'Nombrecompleto' => ['texto' => $relacion->NombreCompleto." ".$relacion->NombreAutoridad." - ".$status." - ".$relacion->SistClasCatDicc,
                                               'url' => $relacion->CategIcono,
                                               'estatus' => $status],
                        'Biblio' => ['texto'=> '',
                                       'url'=>$biblio],
                        'FechaCaptura' => $relacion->FechaCaptura,
                        'FechaModificacion' => $relacion->FechaModificacion,
                        'Observaciones' => $relacion->Observaciones  
                       ];
            
            array_push($reldata, $newRel);
        }

        return $reldata;
    }
}
