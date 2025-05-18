<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventoAuditoria extends Model
{
    
    protected $table = 'evento_auditoria'; // si tu tabla se llama así
    protected $primaryKey = 'id_evento'; // 👈 importante
    public $timestamps = false;
    protected $fillable = [
        'idusuario',
        'id_plantilla',
        'id_publi_noti',
        'cont_registros',
        'estado_auditoria',
        'datos_adicionales',
        'fecha_auditoria',
    ];
    use HasFactory;
}
