<?php

namespace App\Http\Controllers;

use App\Models\NombreCatalogoRegion;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use App\Models\RelNomNomComunRegionBiblio;
use App\Models\RelNomNomComunRegion;
use Illuminate\Support\Facades\DB;

class TraspasoInformacion extends Controller
{
    
    public function traspasoNomCom(Request $request){

        Log::info("Esto vale request: " . $request);
            
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

    /*public function update(Request $request, Region $region)
    {
        $validatedData = $request->validate([
            'NombreRegion' => 'required|string|max:255',
            'IdTipoRegion' => 'required|integer',
            'ClaveRegion' => 'nullable|string|max:255',
            'Abreviado' => 'nullable|string|max:255',
        ]);
        $region->update($validatedData);
        return back()->with('success', 'Región actualizada.');
    }


    public function destroy(Region $region)
    {
        $tieneHijosInternos = Region::where('IdRegionAsc', $region->IdRegion)
            ->where('IdRegion', '!=', $region->IdRegion)
            ->exists();
        if ($tieneHijosInternos) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'message' => 'No se puede eliminar porque tiene sub-regiones dependientes.'
            ]);
        }
        if ($region->IdRegion == $region->IdRegionAsc) {
            $otroNodo = Region::where('IdRegion', '!=', $region->IdRegion)->first();
            if ($otroNodo) {
                $region->IdRegionAsc = $otroNodo->IdRegion;
                $region->save();
            } else {
                DB::statement('SET FOREIGN_KEY_CHECKS=0');
            }
        }
        try {
            $region->delete();
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() == "23000") {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'message' => 'No es posible eliminar la región seleccionada, ya que está asociada a un taxón.'
                ]);
            }
            throw $e;
        } finally {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }

        return back()->with('success', 'Región eliminada correctamente.');
    }*/
}
