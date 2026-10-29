<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\RelNombreCatalogoBiblio;
use App\Models\RelNombreCatalogoRegionBiblio;

class RelNombreCatalogoRegionBiblioController extends Controller
{
    private const MENSAJE_ERROR = "Asociación general (sin región) guardada";
    /**
     * CASO 1: Obtener bibliografía vinculada a una característica Y una región específica
     */
    public function obtenerBiblioCaractRegion(Request $request) {
        try {
            $bibliografias = DB::connection('catcentral')
                ->table('RelNombreCatalogoRegionBiblio as pivot')
                ->join('Bibliografia as b', 'pivot.IdBibliografia', '=', 'b.IdBibliografia')
                ->where('pivot.IdNombre', $request->query('IdNombre'))
                ->where('pivot.IdCatNombre', $request->query('IdCatNombre'))
                ->where('pivot.IdRegion', $request->query('IdRegion'))
                ->where('pivot.IdTipoDistribucion', $request->query('IdTipoDistribucion'))
                ->select('b.IdBibliografia', 'b.Autor', 'b.Anio', 'b.CitaCompleta', 'pivot.Observaciones')
                ->get();
            return response()->json($bibliografias);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * CASO 2: Obtener bibliografía vinculada SOLO a la característica (Sin región)
     * Esta consulta busca en la tabla RelNombreCatalogoBiblio (la de tu primera imagen)
     */
    public function obtenerBiblioCaractSolo(Request $request) {
        try {
            $bibliografias = DB::connection('catcentral')
                ->table('RelNombreCatalogoBiblio as pivot')
                ->join('Bibliografia as b', 'pivot.IdBibliografia', '=', 'b.IdBibliografia')
                ->where('pivot.IdNombre', $request->query('IdNombre'))
                ->where('pivot.IdCatNombre', $request->query('IdCatNombre'))
                ->select('b.IdBibliografia', 'b.Autor', 'b.Anio', 'b.CitaCompleta', 'pivot.Observaciones')
                ->get();

            return response()->json($bibliografias);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * GUARDADO 1: Asociar bibliografía a Característica + Región
     */
    public function asociarBiblioCaractRegion(Request $request) {
        try {
            DB::connection('catcentral')
                ->table('RelNombreCatalogoRegionBiblio')
                ->updateOrInsert(
                    [
                        'IdNombre' => $request->params['idNombre'],
                        'IdCatNombre' => $request->params['idCaract'],
                        'IdRegion' => $request->params['idRegion'],
                        'IdTipoDistribucion' => $request->params['idTipDis'],
                        'IdBibliografia' => $request->params['Biblio'],
                        'Observaciones' => $request->params['observaciones']
                    ],
                    [
                        'usuario' => $request->usuario ?? 'sistema',
                        'FechaModificacion' => now()
                    ]
                );
            return response()->json(['message' => 'Asociación con región guardada con éxito']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * GUARDADO 2: Asociar bibliografía SOLO a la Característica (Tabla RelNombreCatalogoBiblio)
     */
    public function asociarBiblioCaractSolo(Request $request) {
        try {
            DB::connection('catcentral')
                ->table('RelNombreCatalogoBiblio')
                ->updateOrInsert(
                    [
                        'IdNombre' => $request->params['idNombre'],
                        'IdCatNombre' => $request->params['idCaract'],
                        'IdBibliografia' => $request->params['Biblio']
                    ],
                    [
                        'usuario' => $request->usuario ?? 'sistema',
                        'FechaModificacion' => now()
                    ]
                );
            return response()->json(['message' => self::MENSAJE_ERROR]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function eliminarBiblioCaractSolo(Request $request)
    {
        try {
            DB::connection('catcentral')
                ->table('RelNombreCatalogoBiblio')
                ->where('IdNombre', $request->params['IdNombre'])
                ->where('IdCatNombre', $request->params['IdCatNombre'])
                ->where('IdBibliografia',$request->params['Biblio'])
                ->delete();

            return response()->json(['message' => 'Relación eliminada'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function eliminarBiblioCaractRegion(Request $request)
    {   
        try {
            DB::connection('catcentral')
                ->table('RelNombreCatalogoRegionBiblio')
                ->where('IdNombre', $request->idNombre)
                ->where('IdCatNombre', $request->idCatNombre)
                ->where('IdRegion', $request->idRegion)
                ->where('IdTipoDistribucion', $request->idTipDist)
                ->where('IdBibliografia', $request->biblio)
                ->delete();

            return response()->json(['message' => 'Relación eliminada'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function actualizaObsBiblioCaract(Request $request){       
        try {
            log::info("Estos son los parametros: ");
            log::info($request);
            $relBiblio = RelNombreCatalogoBiblio::where('IdNombre', $request->params['idNombre'])
                                                ->where('IdCatNombre',  $request->params['idCaract'])
                                                ->where('IdBibliografia',  $request->params['Biblio'])
                                                ->update(['Observaciones' => $request->params['observaciones'],
                                                                'usuario' => $request->params['usuario'] ?? 'sistema']);

            return response()->json(['message' => self::MENSAJE_ERROR]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function actualizaObsBiblioCaractReg(Request $request){
        log::info("Estos son los parametros: ");
        log::info($request);
        try {
             $relBiblio = RelNombreCatalogoRegionBiblio::where('IdNombre', $request->params['idNombre'])
                                                       ->where('IdCatNombre', $request->params['idCaract'])
                                                       ->where('IdRegion', $request->params['idRegion'])
                                                       ->where('IdTipoDistribucion', $request->params['idTipDis'])
                                                       ->where('IdBibliografia',  $request->params['Biblio'])
                                                       ->update(['Observaciones' => $request->params['observaciones'],
                                                                       'usuario' => $request->params['usuario'] ?? 'sistema']);

            return response()->json(['message' => self::MENSAJE_ERROR]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
