<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificacionesAvisosHacienda extends Model
{
    use HasFactory;

    protected $table = 'notificaciones_avisos_hacienda';
    protected $primaryKey = 'id_notificacion_hacienda';
    public $timestamps = true;

    protected $fillable = [
        'fk_idorganismo_hacienda',
        'fk_idusuario_hacienda',
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
        'id_estado_auditoria'
    ];
}
