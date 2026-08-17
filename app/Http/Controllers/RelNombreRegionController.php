<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RelNombreRegionController extends Controller
{
      public function store(Request $request)
    {
        $request->validate([
            'IdNombre' => 'required|integer',
            'IdRegion' => 'required|integer',
            'IdTipoDistribucion' => 'required|integer',
        ]);

        DB::connection('catcentral')->table('RelNombreRegion')->updateOrInsert(
            ['IdNombre' => $request->IdNombre, 'IdRegion' => $request->IdRegion],
            [
                'IdTipoDistribucion' => $request->IdTipoDistribucion,
                'Observaciones' => $request->Observaciones,
            ]
        );

        return response()->json(['message' => 'Guardado con éxito'], 200);
    }



    public function asociarBiblio(Request $request)
{
    $request->validate([
        'IdNombre' => 'required|integer',
        'IdRegion' => 'required|integer',
        'IdTipoDistribucion' => 'required|integer',
        'IdBibliografia' => 'required|integer',
    ]);

    DB::connection('catcentral')->table('RelNombreRegionBiblio')->updateOrInsert(
        [
            'IdNombre' => $request->IdNombre,
            'IdRegion' => $request->IdRegion,
            'IdTipoDistribucion' => $request->IdTipoDistribucion,
            'IdBibliografia' => $request->IdBibliografia
        ],
        [
            'FechaCaptura' => now(),
            'Observaciones' => $request->Observaciones ?? null
        ]
    );

    return response()->json(['message' => 'Bibliografía asociada correctamente'], 200);
}



public function eliminarRegionTaxon(Request $request)
{
    $conn = DB::connection('catcentral');
    try {
        $conn->beginTransaction();
        $v_idNombre = $request->input('IdNombre');
        $v_idRegion = $request->input('IdRegion');
        $conn->table('RelNombreRegionBiblio')
            ->where('IdNombre', $v_idNombre)
            ->where('IdRegion', $v_idRegion)
            ->delete();
        $conn->table('RelNombreRegion')
            ->where('IdNombre', $v_idNombre)
            ->where('IdRegion', $v_idRegion)
            ->delete();

        $conn->commit();
        return response()->json([
            'message' => "La región y su bibliografía asociada se han eliminado correctamente de la base de datos."
        ], 200);
    } catch (\Exception $e) {
        $conn->rollBack();
        return response()->json([
            'message' => "Error al eliminar la región en catcentral: " . $e->getMessage()
        ], 500);
    }
}


public function cargaRegionesNombre($id)
{
    $regPorCaract = DB::connection('catcentral')->table('RelNombreCatalogoRegion as rncr')
        ->join('Region as r', 'rncr.IdRegion', '=', 'r.IdRegion')
        ->leftJoin('TipoDistribucion as td', 'rncr.IdTipoDistribucion', '=', 'td.IdTipoDistribucion')
        ->where('rncr.IdNombre', $id)
        ->select(
            'r.NombreRegion as RegionNombre', 
            'td.Descripcion as TipoDistLabel',
            'rncr.IdNombre',
            'rncr.IdCatNombre',
            'rncr.IdRegion',
            'rncr.IdTipoDistribucion'
        )
        ->get()
        ->map(function ($item) use ($id) {
            $tieneBiblio = DB::connection('catcentral')->table('RelNombreCatalogoRegionBiblio')
                ->where('IdNombre', $id)
                ->where('IdCatNombre', $item->IdCatNombre)
                ->where('IdRegion', $item->IdRegion)
                ->where('IdTipoDistribucion', $item->IdTipoDistribucion)
                ->exists();

            return [
                'Region' => $item->RegionNombre, 
                'TipDistribucion' => [           
                    'id' => $item->IdTipoDistribucion,
                    'label' => $item->TipoDistLabel ?? 'Sin tipo'
                ],
                'Biblio' => [                    
                    'url' => $tieneBiblio ? '/storage/images/Libro_Verde.svg' : '/storage/images/Libro_Rojo.svg',
                    'texto' => ''
                ]
            ];
        });

    $regPorNombre = DB::connection('catcentral')->table('RelNombreRegion as rnr')
        ->join('Region as r', 'rnr.IdRegion', '=', 'r.IdRegion')
        ->leftJoin('TipoDistribucion as td', 'rnr.IdTipoDistribucion', '=', 'td.IdTipoDistribucion')
        ->where('rnr.IdNombre', $id)
        ->select('r.NombreRegion', 'td.Descripcion', 'rnr.IdTipoDistribucion', 'rnr.IdRegion', 'rnr.Observaciones')
        ->get()
        ->map(function($item) use ($id) {
            $tieneBiblio = DB::connection('catcentral')->table('RelNombreRegionBiblio')
                ->where('IdNombre', $id)->where('IdRegion', $item->IdRegion)->exists();
            return [
                'IdRegion' => $item->IdRegion,
                'Region' => $item->NombreRegion,
                'TipoDistribucion' => [
                    'id' => $item->IdTipoDistribucion,
                    'label' => $item->Descripcion ?? 'Sin tipo'
                ],
                'Observaciones' => $item->Observaciones,
                'Biblio' => ['url' => $tieneBiblio ? '/storage/images/Libro_Verde.svg' : '/storage/images/Libro_Rojo.svg', 'texto' => '']
            ];
        });

    return response()->json([
        'regPorNombre' => $regPorNombre,
        'regPorCaract' => $regPorCaract, 
        'regPorNomCom' => []
    ]);
}


public function obtenerPagina(Request $request)
{
    $id = $request->id;
    $perPage = 100; 
    $registro = DB::connection('catcentral')->table('NomComun')->where('IdNomComun', $id)->first();
    if (!$registro) return response()->json(['page' => 1]);
    $nombre = $registro->NomComun;
    $posicion = DB::connection('catcentral')->table('NomComun')
        ->where(function($query) use ($nombre, $id) {
            $query->where('NomComun', '<', $nombre)
                  ->orWhere(function($sub) use ($nombre, $id) {
                      $sub->where('NomComun', '=', $nombre)
                          ->where('IdNomComun', '<', $id);
                  });
        })
        ->count();
    $pagina = (int) floor($posicion / $perPage) + 1;

    return response()->json(['page' => $pagina]);
}


public function obtenerBiblioRegionTaxon($idNombre, $idRegion, $idTipoDist)
{
    $bibliografias = DB::connection('catcentral')->table('RelNombreRegionBiblio as rnrb')
        ->join('Bibliografia as b', 'rnrb.IdBibliografia', '=', 'b.IdBibliografia')
        ->where('rnrb.IdNombre', $idNombre)
        ->where('rnrb.IdRegion', $idRegion)
        ->where('rnrb.IdTipoDistribucion', $idTipoDist)
        ->select(
            'b.IdBibliografia',
            'b.Autor',
            'b.Anio',
            'b.TituloPublicacion',    
            'b.TituloSubPublicacion',
            'b.CitaCompleta',
            'rnrb.Observaciones' 
        )
        ->get();

    return response()->json($bibliografias);
}

}
