<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
}