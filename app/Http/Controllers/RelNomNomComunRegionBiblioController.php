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
                'b.TituloPublicacion', 
                'b.TituloSubPublicacion',
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


public function actualizarObservacion(Request $request) {
    try {
        DB::connection('catcentral')
            ->table('RelNomNomComunRegionBiblio')
            ->where('IdNomComun', $request->IdNomComun)
            ->where('IdRegion', $request->IdRegion)
            ->where('IdNombre', $request->IdNombre)
            ->where('IdBibliografia', $request->IdBibliografia)
            ->update([
                'Observaciones' => $request->Observaciones,
                'FechaCaptura'  => now()
            ]);

        return response()->json(['message' => 'Observación actualizada con éxito'], 200);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}

public function actualizarObsRelacionBase(Request $request) {
    try {
        DB::connection('catcentral')
            ->table('RelNomNomComunRegion')
            ->where('IdNomComun', $request->IdNomComun)
            ->where('IdNombre', $request->IdNombre)
            ->where('IdRegion', $request->IdRegion)
            ->update([
                'Observaciones' => $request->Observaciones,
                'FechaModificacion' => now()
            ]);

        return response()->json(['message' => 'Observación de relación actualizada'], 200);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}

public function actualizarObsNomComunBase(Request $request) {
    try {
        DB::connection('catcentral')
            ->table('NomComun')
            ->where('IdNomComun', $request->IdNomComun)
            ->update([
                'Observaciones' => $request->Observaciones,
                'FechaModificacion' => now()
            ]);

        return response()->json(['message' => 'Observación actualizada'], 200);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}


public function cargaNombresComunes($id) {
    try {
        $nombres = DB::connection('catcentral')
            ->table('NomComun as nc')
            ->join('RelNomNomComunRegion as rel', 'nc.IdNomComun', '=', 'rel.IdNomComun')
            ->join('Region as r', 'rel.IdRegion', '=', 'r.IdRegion')
            ->where('rel.IdNombre', $id)
            ->select(
                'nc.IdNomComun',
                'nc.NomComun as NombreComun',
                'nc.Lengua',
                'nc.Observaciones as ObsGeneral',
                'r.IdRegion',
                'r.NombreRegion as Region',
                'rel.Observaciones as ObsRelacion'
            )
            ->get();

        $nombresFormateados = $nombres->groupBy('IdNomComun')->map(function ($group) {
            $primer = $group->first();
            return [
                'id' => $primer->IdNomComun,
                'IdNomComun' => $primer->IdNomComun,
                'NombreComun' => $primer->NombreComun,
                'Lengua' => $primer->Lengua,
                'Observaciones' => $primer->ObsGeneral ?? '',
                'Regiones' => $group->map(function ($item) {
                    return [
                        'id' => 'reg-' . $item->IdRegion . '-' . $item->IdNomComun,
                        'Region' => $item->Region,
                        'IdRegion' => $item->IdRegion,
                        'IdNomComun' => $item->IdNomComun,
                        'Observaciones' => $item->ObsRelacion ?? ''
                    ];
                })->values()
            ];
        })->values();

        return response()->json($nombresFormateados);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}


public function altaRelNomComun(Request $request) {
    try {
        $idNombre = $request->idNombre;
        $idNomComun = $request->idNomComun;
        $idRegion = $request->idRegion;
        $existe = DB::connection('catcentral')
            ->table('RelNomNomComunRegion')
            ->where('IdNombre', $idNombre)
            ->where('IdNomComun', $idNomComun)
            ->where('IdRegion', $idRegion)
            ->exists();
        if (!$existe) {
            DB::connection('catcentral')
                ->table('RelNomNomComunRegion')
                ->insert([
                    'IdNombre'     => $idNombre,
                    'IdNomComun'   => $idNomComun,
                    'IdRegion'     => $idRegion,
                    'FechaCaptura' => now(),
                ]);
            return response()->json(['message' => 'Relación creada en la base de datos'], 200);
        }
        return response()->json(['message' => 'Esta relación ya existe'], 200);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}



public function eliminarRelacionNomComun(Request $request)
{
    $conn = DB::connection('catcentral');
    try {
        $conn->beginTransaction();
        $v_idNombre = $request->input('idNombre');
        $v_idNomComun = $request->input('idNomComun');
        $v_idRegion = $request->input('idRegion');

        if ($v_idRegion) {
            $conn->table('RelNomNomComunRegionBiblio')
                ->where('IdNombre', $v_idNombre)
                ->where('IdNomComun', $v_idNomComun)
                ->where('IdRegion', $v_idRegion)
                ->delete();
            $conn->table('RelNomNomComunRegion')
                ->where('IdNombre', $v_idNombre)
                ->where('IdNomComun', $v_idNomComun)
                ->where('IdRegion', $v_idRegion)
                ->delete();
            $mensaje = "La región y su bibliografía asociada se han eliminado correctamente.";
        } else {
            $conn->table('RelNomNomComunRegionBiblio')
                ->where('IdNombre', $v_idNombre)
                ->where('IdNomComun', $v_idNomComun)
                ->delete();
            $conn->table('RelNomNomComunRegion')
                ->where('IdNombre', $v_idNombre)
                ->where('IdNomComun', $v_idNomComun)
                ->delete();
            $mensaje = "El nombre común y todas sus relaciones asociadas se han eliminado.";
        }
        $conn->commit();
        return response()->json(['message' => $mensaje], 200);
    } catch (\Exception $e) {
        $conn->rollBack();
        return response()->json([
            'message' => "Error al eliminar en catcentral: " . $e->getMessage()
        ], 500);
    }
}

}
