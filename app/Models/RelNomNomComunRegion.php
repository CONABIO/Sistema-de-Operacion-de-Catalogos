<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HasCompositePrimaryKey;

class RelNomNomComunRegion extends Model
{
    use HasFactory, HasCompositePrimaryKey;

    protected $connection = 'catcentral';// Conexión a 'catalogocentralizado'

    //Con esta instruccion se omite el ingreso de fechas en el modelo 
    public $timestamps = false;

    //Se asigna el nombre de la Tabla 
    protected $table = 'RelNomNomComunRegion';

    //Se asigna el nombre del campo llave primaria
    protected $primaryKey=['IdNomComun', 'IdNombre', 'IdRegion'];
}
