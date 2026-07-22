<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RelNombreCatalogoRegionBiblioController extends Controller
{
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
                ->select('b.IdBibliografia', 'b.Autor', 'b.Anio', 'b.CitaCompleta')
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
                ->select('b.IdBibliografia', 'b.Autor', 'b.Anio', 'b.CitaCompleta')
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
                        'IdNombre' => $request->IdNombre,
                        'IdCatNombre' => $request->IdCatNombre,
                        'IdRegion' => $request->IdRegion,
                        'IdTipoDistribucion' => $request->IdTipoDistribucion,
                        'IdBibliografia' => $request->IdBibliografia
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
                        'IdNombre' => $request->IdNombre,
                        'IdCatNombre' => $request->IdCatNombre,
                        'IdBibliografia' => $request->IdBibliografia
                    ],
                    [
                        'usuario' => $request->usuario ?? 'sistema',
                        'FechaModificacion' => now()
                    ]
                );
            return response()->json(['message' => 'Asociación general (sin región) guardada']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
