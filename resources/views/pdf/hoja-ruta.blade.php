<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <style>
        * { box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1f2937; margin: 0; padding: 0; }
        .header { border-bottom: 3px solid #0f766e; padding-bottom: 12px; margin-bottom: 16px; }
        .header h1 { margin: 0; font-size: 20px; color: #0f766e; text-transform: uppercase; letter-spacing: 1px; }
        .header .sub { color: #6b7280; font-size: 10px; }
        .title-row { display: flex; justify-content: space-between; margin-top: 6px; }
        .info-box { width: 100%; border: 1px solid #d1d5db; border-radius: 6px; padding: 10px 12px; margin-bottom: 16px; }
        .info-grid { width: 100%; }
        .info-grid td { padding: 3px 6px; vertical-align: top; }
        .info-grid td.label { font-weight: bold; color: #374151; width: 120px; }
        table.list { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        table.list th { background: #0f766e; color: #fff; padding: 7px 8px; text-align: left; font-size: 10px; text-transform: uppercase; }
        table.list td { border: 1px solid #e5e7eb; padding: 6px 8px; }
        table.list tr:nth-child(even) td { background: #f9fafb; }
        .right { text-align: right; }
        .totals { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        .totals td { padding: 5px 8px; border: 1px solid #e5e7eb; }
        .totals .amount { text-align: right; font-weight: bold; }
        .totals .grand td { background: #0f766e; color: #fff; font-weight: bold; font-size: 12px; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 10px; font-size: 9px; font-weight: bold; }
        .badge-entregado { background: #d1fae5; color: #065f46; }
        .badge-incidencia { background: #fee2e2; color: #991b1b; }
        .badge-pendiente { background: #fef3c7; color: #92400e; }
        .badge-otro { background: #e5e7eb; color: #374151; }
        .footer { margin-top: 24px; border-top: 1px solid #d1d5db; padding-top: 8px; color: #9ca3af; font-size: 9px; text-align: center; }
        .estado-ruta { font-size: 10px; }
    </style>
</head>
<body>
    @php
        $repartidor = $hojaRuta->repartidor;
        $usuarioRepartidor = $repartidor?->usuario;
        $nombreRepartidor = $usuarioRepartidor ? trim($usuarioRepartidor->nombre . ' ' . $usuarioRepartidor->apellido) : '-';
        $vehiculo = $hojaRuta->vehiculo;
        $estados = [
            'planificado' => 'Planificada',
            'en_progreso' => 'En progreso',
            'completado' => 'Completada',
            'cancelado' => 'Cancelada',
        ];
        $badge = function ($estado) {
            return in_array($estado, ['entregado', 'incidencia', 'pendiente']) ? $estado : 'otro';
        };
        $totalEfectivo = (float) $hojaRuta->cobrado_efectivo;
        $totalQr = (float) $hojaRuta->cobrado_qr;
        $totalDespachos = $hojaRuta->detalles->sum(fn($d) => $d->despachos->count());
        $completados = $hojaRuta->detalles->sum(fn($d) => $d->despachos->whereIn('estado_despacho', ['entregado', 'incidencia'])->count());
    @endphp

    <div class="header">
        <h1>Hoja de Ruta N.º {{ $hojaRuta->id_hoja_ruta }}</h1>
        <div class="title-row">
            <span class="sub">Sistema de Distribución</span>
            <span class="estado-ruta">Estado: {{ $estados[$hojaRuta->estado_ruta] ?? $hojaRuta->estado_ruta }}</span>
        </div>
    </div>

    <div class="info-box">
        <table class="info-grid">
            <tr>
                <td class="label">Fecha:</td>
                <td>{{ $hojaRuta->fecha ? $hojaRuta->fecha->format('d/m/Y') : '-' }}</td>
                <td class="label">Repartidor:</td>
                <td>{{ $nombreRepartidor }} (CI: {{ $repartidor?->ci ?? '-' }})</td>
            </tr>
            <tr>
                <td class="label">Hora salida:</td>
                <td>{{ $hojaRuta->hora_salida ?: '-' }}</td>
                <td class="label">Hora llegada:</td>
                <td>{{ $hojaRuta->hora_llegada ?: '-' }}</td>
            </tr>
            <tr>
                <td class="label">Vehículo:</td>
                <td>{{ $vehiculo ? trim(($vehiculo->marca ?? '') . ' ' . ($vehiculo->modelo ?? '') . ' - ' . $vehiculo->placa) : '-' }}</td>
                <td class="label">Paradas:</td>
                <td>{{ $completados }} / {{ $totalDespachos }}</td>
            </tr>
        </table>
    </div>

    <table class="list">
        <thead>
            <tr>
                <th style="width: 30px;">#</th>
                <th>Farmacia</th>
                <th>Dirección</th>
                <th style="width: 70px;">Estado</th>
                <th style="width: 80px;">Pago</th>
                <th style="width: 90px;" class="right">Monto</th>
                <th style="width: 70px;">Evidencias</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($hojaRuta->detalles as $detalle)
                @php
                    $despacho = $detalle->despachos->first();
                    $estadoDespacho = $despacho?->estado_despacho ?? 'pendiente';
                    $metodo = $despacho?->metodo_pago ? strtoupper($despacho->metodo_pago) : '-';
                    $monto = $despacho ? (float) $despacho->monto_recibido : 0;
                    $numEvidencias = $despacho ? $despacho->evidencias->count() : 0;
                @endphp
                <tr>
                    <td>{{ $detalle->orden_visita }}</td>
                    <td>{{ $detalle->pedido?->farmacia?->nombre ?? 'Sin farmacia' }}</td>
                    <td>{{ $detalle->pedido?->farmacia?->direccion ?? '-' }}</td>
                    <td><span class="badge badge-{{ $badge($estadoDespacho) }}">{{ ucfirst($estadoDespacho) }}</span></td>
                    <td>{{ $metodo }}</td>
                    <td class="right">Bs {{ number_format($monto, 2, ',', '.') }}</td>
                    <td class="right">{{ $numEvidencias }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align: center; color: #6b7280;">Sin paradas registradas.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <table class="totals">
        <tr>
            <td>Cobrado en efectivo</td>
            <td class="amount">Bs {{ number_format($totalEfectivo, 2, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Cobrado por QR</td>
            <td class="amount">Bs {{ number_format($totalQr, 2, ',', '.') }}</td>
        </tr>
        <tr class="grand">
            <td>Total cobrado</td>
            <td class="amount">Bs {{ number_format($totalEfectivo + $totalQr, 2, ',', '.') }}</td>
        </tr>
    </table>

    <div class="footer">
        Documento generado automáticamente el {{ now()->format('d/m/Y H:i') }} · Distribuidora
    </div>
</body>
</html>
