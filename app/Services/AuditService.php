<?php

namespace App\Services;

use App\Models\Seguridad\Auditoria;
use App\Models\Seguridad\Accion;
use App\Models\Seguridad\TablaSistema;

class AuditService
{
    public static function log(int $idUsuario, string $accionNombre, string $tablaNombre, int $registroId): void
    {
        $accion = Accion::where('nombre', $accionNombre)->first();
        $tabla = TablaSistema::where('nombre', $tablaNombre)->first();

        if (!$accion || !$tabla) {
            return;
        }

        Auditoria::create([
            'id_usuario' => $idUsuario,
            'id_accion' => $accion->id_accion,
            'id_tabla' => $tabla->id_tabla,
            'registro_id' => $registroId,
            'fecha_hora' => now(),
        ]);
    }
}
