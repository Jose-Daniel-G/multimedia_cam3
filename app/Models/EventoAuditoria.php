<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class EventoAuditoria extends Model
{
    use LogsActivity;
    use HasFactory;

    protected $table = 'evento_auditoria';
    protected $primaryKey = 'id_evento';
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

    protected $casts = [
        'datos_adicionales' => 'array',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'idusuario',
                'id_plantilla',
                'id_publi_noti',
                'cont_registros',
                'estado_auditoria',
                'datos_adicionales',
                'fecha_auditoria'
            ])
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn(string $eventName) => "Evento creado");//{$eventName}
    }

    public function shouldLogEvent(string $eventName): bool
    {
        return $eventName === 'created';// Solo queremos log del evento "created"
    }
}
