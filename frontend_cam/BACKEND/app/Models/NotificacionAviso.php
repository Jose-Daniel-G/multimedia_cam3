<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificacionAviso extends Model
{
    use HasFactory;

    protected $table = 'notificaciones_avisos';
    protected $primaryKey = 'id_notificacion';
    public $timestamps = false; // <--- Aquí está la clave

    protected $fillable = [
        'publi_notificacion',
        'fk_idorganismo', // <- este
        'fk_idusuario',
        'fk_idtp_imp',
        'fk_tipo_plantilla',
        'ruta_archivos',
        'nombre_ciudadano',
        'cedula_identificacion',
        'fecha_publicacion',
        'fecha_desfijacion',
        'id_predio',
        'objeto_contrato',
        'num_predial',
        'fk_tipo_acto_tramite',
        'fk_estado_publicacion',
        'fk_tipo_causa_devolucion',
        'json_plantilla',
        'id_estado_auditoria',
    ];
}
