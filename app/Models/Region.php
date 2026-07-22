<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\RelNombreRegion;
use App\Models\RelNombreRegionBiblio;
use App\Models\RelNombreCatalogoRegion;
use App\Models\RelNombreCatalogoRegionBiblio;
use App\Models\RelNomNomComunRegion;
use App\Models\RelNomNomComunRegionBiblio;

class Region extends Model
{
    use HasFactory;

    protected $connection = 'catcentral';
    
    protected $table = 'Region';

   
    protected $primaryKey = 'IdRegion';

    
    public $timestamps = false;

    
    protected $fillable = [
        'NombreRegion',
        'IdTipoRegion',
        'ClaveRegion',
        'IdRegionAsc',
        'IdOriginal',
        'Catalogo',
        'DatoActivo',
        'Abreviado',
        'Marca',
        'IdRegNvo',
        'FechaCaptura',
        'FechaModificacion',
    ];

    /*Este es el SCOPE de regiones asociadas a nombre*/
    public function scopeRegionPorNombre($query, $idNombre)
    {
        return $query->from('RelNombreRegion as r')
                     ->join('RelNombreRegionBiblio as rb', function($join){
                        $join->on('rb.IdNombre', 'r.IdNombre')
                             ->on('rb.IdRegion', 'r.IdRegion')
                             ->on('rb.IdTipoDistribucion', 'r.IdTipoDistribucion');
                     })
                     ->join('TipoDistribucion as td', function($join){
                        $join->on('td.IdTipoDistribucion', 'rb.IdTipoDistribucion');
                     })
                     ->select('r.IdNombre', 'r.IdRegion', 'r.IdTipoDistribucion', 'td.Descripcion AS TipoDist')
                     ->selectRaw('COUNT(rb.IdBibliografia) AS Biblio')
                     ->where('r.IdNombre', $idNombre)
                     ->groupBy('r.IdNombre', 'r.IdRegion', 'r.IdTipoDistribucion', 'td.Descripcion')
                     ->orderBy('r.IdNombre')
                     ->orderBy('r.IdRegion');
    }
    /*Este es el SCOPE de regiones asociadas a nombre comun*/
    public function scopeRegionPorCaract($query, $idNombre)
    {
        return $query->from('RelNombreCatalogoRegion as rnc')
                     ->join('RelNombreCatalogoRegionBiblio as rncb', function($join){
                        $join->on('rncb.IdNombre', 'rnc.IdNombre')
                             ->on('rncb.IdRegion', 'rnc.IdRegion')
                             ->on('rncb.IdTipoDistribucion', 'rnc.IdTipoDistribucion');
                     })
                     ->join('TipoDistribucion as td', function($join){
                        $join->on('td.IdTipoDistribucion', 'rnc.IdTipoDistribucion');
                     })
                     ->select('rnc.IdNombre', 'rnc.IdRegion', 'rnc.IdTipoDistribucion', 'td.Descripcion AS TipoDist')
                     ->selectRaw('COUNT(rncb.IdBibliografia) AS Biblio')
                     ->where('rnc.IdNombre', $idNombre)
                     ->groupBy('rnc.IdNombre', 'rnc.IdRegion', 'rnc.IdTipoDistribucion', 'td.Descripcion')
                     ->orderBy('rnc.IdNombre')
                     ->orderBy('rnc.IdRegion');
    }
    /*Este es el SCOPE de regiones asociadas a caracteristicas*/
    public function scopeRegionPorNomComun($query, $idNombre)
    {
        return $query->from('RelNomNomComunRegion as rncr')
                     ->join('RelNomNomComunRegionBiblio as rncrb', function($join){
                        $join->on('rncrb.IdNombre', 'rncr.IdNombre')
                             ->on('rncrb.IdNomComun', 'rncr.IdNomComun')
                             ->on('rncrb.IdRegion', 'rncr.IdRegion');
                     })
                     ->select('rncr.IdNombre', 'rncr.IdRegion')
                     ->selectRaw('COUNT(rncrb.IdBibliografia) AS Biblio')
                     ->where('rncr.IdNombre', $idNombre)
                     ->groupBy('rncr.IdNombre', 'rncr.IdRegion')
                     ->orderBy('rncr.IdNombre')
                     ->orderBy('rncr.IdRegion');
                     
    }
}