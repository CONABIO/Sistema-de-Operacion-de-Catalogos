<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mime extends Model
{
    use HasFactory;

    protected $connection = 'catcentral';
    protected $table = 'MIME'; 
    protected $primaryKey = 'IdMime'; 
    public $timestamps = false;

    protected $fillable = [
        'MIME',
        'Extension',
        'Catalogo',
        'IdOriginal',
        'FechaCaptura',
        'FechaModificacion'
    ];
}
