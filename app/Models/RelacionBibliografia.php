<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HasCompositePrimaryKey;

class RelacionBibliografia extends Model
{
    use HasFactory, HasCompositePrimaryKey;

    protected $connection = 'catcentral';// Conexión a 'catalogocentralizado'

    //Con esta instruccion se omite el ingreso de fechas en el modelo 
    public $timestamps = false;

    //Se asigna el nombre de la Tabla 
    protected $table = 'RelacionBibliografia';

    //Se asigna el nombre del campo llave primaria
    //protected $primaryKey = array('IdNombre', 'IdNombreRel', 'IdTipoRelacion', IdBibliografia );
    protected $primaryKey = ['IdNombre', 
                             'IdNombreRel',
                             'IdTipoRelacion',
                             'IdBibliografia'];

    //Se asignan los campos que podran ser actualizados 
    protected $guarded = [];

    //Se declara la relacion de uno a uno de las categorias 
    public function bibliografia()
    {
        return $this->belongsTo(Bibliografia::class, 'IdBibliografia', 'IdBibliografia');
    }

    public function scopeBibliografiaRelacion($query, $idNombre, $idNombreRel, $idTipoRel){
        
        return $query->join('Bibliografia', 'Bibliografia.IdBibliografia', '=', 'RelacionBibliografia.IdBibliografia')
                     ->selectRaw('RelacionBibliografia.Observaciones as ObsRelBiblio, Bibliografia.*')
                     ->where('RelacionBibliografia.IdNombre', $idNombre)
                     ->where('RelacionBibliografia.IdNombreRel', $idNombreRel)
                     ->where('RelacionBibliografia.IdTipoRelacion', $idTipoRel);
    }
}
