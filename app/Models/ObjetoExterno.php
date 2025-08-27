<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ObjetoExterno extends Model
{
    use HasFactory;


    protected $connection = 'catcentral';


    protected $table = 'ObjetoExterno';


    protected $primaryKey = 'IdObjetoExterno';


    public $timestamps = false;

    protected $fillable = [
        'IdMime',
        'NombreObjeto',
        'NombreSitio',
        'Ruta',
        'Protocolo',
        'Usuario',
        'Password',
        'UnidadLogica',
        'Titulo',
        'Autor',
        'Institucion',
        'Fecha',
        'Observaciones',
        'FechaCaptura', 
        'FechaModificacion',
    ];

     protected $casts = [
        'Fecha' => 'datetime',
        'FechaCaptura' => 'datetime',
        'FechaModificacion' => 'datetime',
    ];


    public function mime()
    {
        return $this->belongsTo(Mime::class, 'IdMime');
    }
}
