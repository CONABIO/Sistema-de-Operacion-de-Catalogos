<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RelNomNomComunRegionBiblioController extends Controller
{
   public function obtenerBiblioNomComunRegion($idNomComun, $idRegion, $idNombre){
    try {
        $bibliografias = DB::connection('catcentral')
            ->table('RelNomNomComunRegionBiblio as pivot')
            ->leftJoin('Bibliografia as b', 'pivot.IdBibliografia', '=', 'b.IdBibliografia')
            ->where('pivot.IdNomComun', $idNomComun)
            ->where('pivot.IdRegion', $idRegion)
            ->where('pivot.IdNombre', $idNombre)
            ->select(
                'b.IdBibliografia',
                'b.Autor',
                'b.Anio',
                'b.CitaCompleta',
                'pivot.Observaciones'
            )
            ->get();

        return response()->json($bibliografias);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}


    public function asociarBiblioNomComunRegion(Request $request) {
    $idNC   = $request->input('IdNomComun');
    $idReg  = $request->input('IdRegion');
    $idNom  = $request->input('IdNombre');
    $idsBib = $request->input('idsBibliografias');

    try {
        foreach ($idsBib as $idBiblio) {
            $existe = DB::connection('catcentral')
                ->table('RelNomNomComunRegionBiblio')
                ->where('IdNomComun', $idNC)
                ->where('IdNombre', $idNom)
                ->where('IdRegion', $idReg)
                ->where('IdBibliografia', $idBiblio)
                ->exists();

            if (!$existe) {
                DB::connection('catcentral')
                    ->table('RelNomNomComunRegionBiblio')
                    ->insert([
                        'IdNomComun'     => $idNC,
                        'IdNombre'       => $idNom,
                        'IdRegion'       => $idReg,
                        'IdBibliografia' => $idBiblio,
                        'FechaCaptura'   => now(),
                    ]);
            }
        }
        return response()->json(['message' => 'Guardado con éxito']);
    } catch (\Exception $e) {
        return response()->json([
            'error_real' => $e->getMessage()
        ], 500);
    }
}



public function eliminarBiblioCaractRegion(Request $request)
{
    try {
        DB::connection('catcentral')
            ->table('RelNombreCatalogoRegionBiblio')
            ->where('IdNombre', $request->IdNombre)
            ->where('IdCatNombre', $request->IdCatNombre)
            ->where('IdRegion', $request->IdRegion)
            ->where('IdTipoDistribucion', $request->IdTipoDistribucion)
            ->where('IdBibliografia', $request->IdBibliografia)
            ->delete();

        return response()->json(['message' => 'Relación eliminada'], 200);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}

public function eliminarBiblioCaractSolo(Request $request)
{
    try {
        DB::connection('catcentral')
            ->table('RelNombreCatalogoBiblio')
            ->where('IdNombre', $request->IdNombre)
            ->where('IdCatNombre', $request->IdCatNombre)
            ->where('IdBibliografia', $request->IdBibliografia)
            ->delete();

        return response()->json(['message' => 'Relación eliminada'], 200);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}

public function eliminarBiblioNomComunRegion(Request $request)
{
    try {
        DB::connection('catcentral')
            ->table('RelNomNomComunRegionBiblio')
            ->where('IdNombre', $request->IdNombre)
            ->where('IdNomComun', $request->IdNomComun)
            ->where('IdRegion', $request->IdRegion)
            ->where('IdBibliografia', $request->IdBibliografia)
            ->delete();

        return response()->json(['message' => 'Relación eliminada'], 200);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}


}
