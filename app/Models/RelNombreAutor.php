<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RelNombreAutor extends Model
{
    use HasFactory;

    protected $connection = 'catcentral';// Conexión a 'catalogocentralizado'

    //Se asigna el nombre de la Tabla 
    protected $table = 'RelNombreAutor';

    //Se asigna el nombre del campo llave primaria
    protected $primaryKey='IdNombre';

    //Con esta instruccion se omite el ingreso de fechas en el modelo 
    public $timestamps = false;

    // Desactiva la conversión para este modelo específico
    public $preserveEmptyStrings = true;
   
}