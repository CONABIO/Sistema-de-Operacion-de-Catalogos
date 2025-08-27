<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Nombre;
use App\Models\CategoriasTaxonomicas;
use App\Models\AutorTaxon;
use App\Models\GrupoScat;
use App\Models\Nombre_Relacion;
use App\Models\Scat;
use App\Models\RelNombreAutor;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\StoreRelacionRequest;
use Exception;


class RelNombresController extends Controller
{
    public function altaRelaciones(StoreRelacionRequest $request)
    {   
        log::info("Este es el request del controlador");
        log::info($request);

        $data =$request->validated();
        
        Log::info("Llegue al controlador"); 
        log::info($data);
        
        $data = $request->validated();

        return $data;
        
    }
}
