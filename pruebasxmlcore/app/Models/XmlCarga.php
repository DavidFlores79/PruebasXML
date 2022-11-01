<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class XmlCarga extends Model
{
    use HasFactory;

    protected $fillable = [
        'documento',
        'referencia',
        'tipo_xml',
        'proveedor',
        'sociedad',
        'ejercicio',
        'archivo',
        'xml',
    ];
}
