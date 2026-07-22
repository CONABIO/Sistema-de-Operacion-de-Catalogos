<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HasCompositePrimaryKey;
use App\Models\RelNombreCatalogoBiblio;
use App\Models\RelNombreCatalogoRegion;
use App\Models\RelNombreCatalogoRegionBiblio;


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

        $consultaSinReg = $query->from('RelNombreCatalogo as rnc')
                            ->join('RelNombreCatalogoBiblio as rncb', function ($join){
                                $join->on('rncb.IdNombre', 'rnc.IdNombre')
                                     ->on('rncb.IdCatNombre', 'rnc.IdCatNombre');
                            })
                            ->selectRaw("rnc.IdNombre,
                                         rnc.IdCatNombre,
                                         rnc.Observaciones AS RelNomCat,
                                         '' AS RelNomCatReg,
                                         0 AS IdRegion, 
                                         0 AS IdTipoDistribucion,
                                         '' AS Descripcion,
                                         COUNT(rncb.IdBibliografia) AS contBiblio,
                                         0 AS contBiblioRegion")
                            ->where('rnc.IdNombre', $idNombre)
                            ->groupBy('rnc.IdNombre', 
                                      'rnc.IdCatNombre', 
                                      'rnc.Observaciones');
        
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
