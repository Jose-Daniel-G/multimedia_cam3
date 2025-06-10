<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Curso extends Model
{
    use HasFactory;
    protected $fillable = ['nombre', 'descripcion', 'horas_requeridas'];
    public function clases()
    {
        return $this->hasMany(Clase::class);
    }
}
