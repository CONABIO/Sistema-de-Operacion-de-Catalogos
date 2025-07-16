<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoRegion extends Model
{
    use HasFactory;

    protected $connection = 'catcentral';


    protected $table = 'TipoRegion';

    
    protected $primaryKey = 'IdTipoRegion';

    
    public $timestamps = false;

    
    protected $fillable = [
        'Descripcion',
        'Nivel1',
        'Nivel2',
        'Nivel3',
        'Nivel4',
        'Nivel5',
    ];
}