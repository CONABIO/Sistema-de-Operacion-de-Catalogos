<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HasCompositePrimaryKey;
use App\Models\RelNombreCatalogoBiblio;
use App\Models\RelNombreCatalogoRegion;
use App\Models\RelNombreCatalogoRegionBiblio;
use Illuminate\Support\Facades\DB;


class RelNombreCatalogo extends Model
{
    use HasFactory, HasCompositePrimaryKey;

    protected $connection = 'catcentral';// Conexión a 'catalogocentralizado'

    //Con esta instruccion se omite el ingreso de fechas en el modelo 
  public $timestamps = false;

  //Se asigna el nombre de la Tabla 
  protected $table = 'RelNombreCatalogo';

  //Se asigna el nombre del campo llave primaria
    //protected $primaryKey = array('IdNombre', 'IdNombreRel', 'IdTipoRelacion', IdBibliografia );
    protected $primaryKey = ['IdNombre', 'IdCatNombre'];

    //Se asignan los campos que podran ser actualizados 
    protected $guarded = [];
 
    //Se declara la relacion de uno a uno de los datos de catalogonombre 
    public function catalogonombre()
    {
        return $this->belongsToMany(CatalogoNombre::class, 'IdCatNombre');
    }

    public function nombre()
    {
        return $this->belongsToMany(Nombre::class, 'IdNombre');
    }

    public function scopeCaracteristicasTaxon($query, $idNombre)
    {

        $consultaSinReg = RelNombreCatalogo::query()
            ->from('RelNombreCatalogo as rnc')
            ->select([
                'rnc.IdNombre',
                'rnc.IdCatNombre',
                DB::raw('rnc.Observaciones AS RelNomCat'),
                DB::raw("'' AS RelNomCatReg"),
                DB::raw('0 AS IdRegion'),
                DB::raw('0 AS IdTipoDistribucion'),
                DB::raw("'' AS Descripcion"),
            ])
            ->selectSub(
                RelNombreCatalogoBiblio::query()
                    ->selectRaw('COUNT(IdBibliografia)')
                    ->whereColumn('RelNombreCatalogoBiblio.IdNombre', 'rnc.IdNombre')
                    ->whereColumn('RelNombreCatalogoBiblio.IdCatNombre', 'rnc.IdCatNombre'),
                'contBiblio'
            )
            ->selectRaw('0 AS contBiblioRegion')
            ->where('rnc.IdNombre', $idNombre)
            ->groupBy(
                'rnc.IdNombre',
                'rnc.IdCatNombre',
                'rnc.Observaciones'
            );
        
        $consultaConReg = RelNombreCatalogoRegion::from('RelNombreCatalogoRegion AS rncr')
                            ->join('TipoDistribucion as td', 'td.IdTipoDistribucion', 'rncr.IdTipoDistribucion')
                            ->selectRaw("rncr.IdNombre,
                                         rncr.IdCatNombre,
                                         '' AS RelNomCat,
                                         rncr.Observaciones AS RelNomCatReg, 
                                         rncr.IdRegion,
                                         rncr.IdTipoDistribucion,
                                         td.Descripcion")
                            ->selectSub(
                                RelNombreCatalogoBiblio::query()
                                    ->selectRaw('COUNT(IdBibliografia)')
                                    ->whereColumn('IdNombre', 'rncr.IdNombre')
                                    ->whereColumn('IdCatNombre', 'rncr.IdCatNombre'),
                                'contBiblio'
                            )
                            ->selectSub(
                                RelNombreCatalogoRegionBiblio::query()
                                    ->selectRaw('COUNT(IdBibliografia)')
                                    ->whereColumn('IdNombre', 'rncr.IdNombre')
                                    ->whereColumn('IdCatNombre', 'rncr.IdCatNombre')
                                    ->whereColumn('IdRegion', 'rncr.IdRegion')
                                    ->whereColumn('IdTipoDistribucion', 'rncr.IdTipoDistribucion'),
                                'contBiblioRegion'
                            )
                            ->where('rncr.IdNombre', $idNombre)
                            ->groupBy('rncr.IdNombre',
                                      'rncr.IdCatNombre',
                                      'rncr.Observaciones',
                                      'rncr.IdRegion',
                                      'rncr.IdTipoDistribucion',
                                      'td.Descripcion'
                            );

        return $consultaSinReg->union($consultaConReg);

    }
}
