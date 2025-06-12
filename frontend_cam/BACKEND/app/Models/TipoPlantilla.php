<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoPlantilla extends Model
{
    use HasFactory;
    protected $table = 'tipo_plantilla';
    protected $primaryKey = 'id_tipo_plantilla'; // Verifica que la clave primaria es 'id'
}
