<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Nombre_Relacion;
use App\Models\RelNombreAutor;
use App\Models\RelacionBibliografia;
use App\Models\Nombre;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\RequestAltaNomNomComun;
use App\Models\RelNomNomComunRegion;
use Exception;


class RelNombreNomComunController extends Controller
{
    //public function altaRelNomComun(RequestAltaNomNomComun $request){ 
    public function altaRelNomComun(RequestAltaNomNomComun $request){   

        
        try{
            DB::beginTransaction();

            $rel = RelNomNomComunRegion::create([
                'IdNombre' => $request->idNombre,
                'IdNomComun' => $request->idNomComun,
                'IdRegion' => $request->idRegion
            ]);

            DB::commit();

            return response()->json([
                'message' => "La relación entre taxón, nombre común y región se ha generado correctamente."
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
