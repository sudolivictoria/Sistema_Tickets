<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Reporte Operativo de Tickets</title>
    <style>
        @page {
            margin: 35px 40px;
        }
        
        /* 🌟 TIPOGRAFÍA ÚNICA Y GLOBAL: Todo el reporte usará Helvetica de forma limpia */
        body {
            font-family: 'Helvetica', Arial, sans-serif;
            color: #334155;
            font-size: 10px;
            margin: 0;
            padding: 0;
            background-color: #ffffff;
        }

        /* --------- ENCABEZADO ----------- */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .logo-title {
            font-size: 24px;
            font-weight: bold;
            color: #0f172a;
            letter-spacing: -0.8px;
        }

        .logo-title span {
            color: #008F7E;
        }

        .meta-info {
            text-align: right;
            font-size: 9.5px;
            color: #64748b;
            line-height: 1.5;
        }

        .titulo-reporte {
            font-size: 13px;
            font-weight: bold;
            color: #1e293b;
            margin-bottom: 25px;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            border-bottom: 2px solid #f1f5f9;
            padding-bottom: 6px;
        }

        /* ------ TARJETAS DE ESTADÍSTICAS ------------ */
        .stats-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }

        .stat-card {
            border-radius: 6px;
            padding: 12px 14px;
            border: 1px solid #e2e8f0;
        }

        .card-abierto { background-color: #fff7ed; border-left: 4px solid #ea580c; }
        .card-abierto .stat-label { color: #c2410c; }
        .card-abierto .stat-value { color: #ea580c; }

        .card-proceso { background-color: #eff6ff; border-left: 4px solid #2563eb; }
        .card-proceso .stat-label { color: #1d4ed8; }
        .card-proceso .stat-value { color: #2563eb; }

        .card-cerrado { background-color: #f0fdf4; border-left: 4px solid #16a34a; }
        .card-cerrado .stat-label { color: #15803d; }
        .card-cerrado .stat-value { color: #16a34a; }

        .card-total   { background-color: #faf5ff; border-left: 4px solid #7c3aed; }
        .card-total .stat-label   { color: #6d28d9; }
        .card-total .stat-value   { color: #7c3aed; }

        .stat-label {
            font-size: 9px;
            text-transform: uppercase;
            font-weight: bold;
            display: block;
            margin-bottom: 4px;
            letter-spacing: 0.5px;
        }

        .stat-value {
            font-size: 24px;
            font-weight: bold;
        }

        /* ---- TABLA DE DATOS ---- */
        .tabla-datos {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
        }

        .tabla-datos th {
            background-color: #0f172a;
            color: #ffffff;
            padding: 10px 12px;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 9px;
            text-align: left;
            letter-spacing: 0.6px;
        }

        .tabla-datos td {
            padding: 12px 12px 6px 12px;
            font-size: 10px;
            color: #334155;
            vertical-align: middle;
        }

        .fila-principal td {
            border-top: 1px solid #e2e8f0;
            background-color: #ffffff;
        }

        .fila-detalle td {
            background-color: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            padding: 2px 12px 12px 12px;
        }

        .detalle-container {
            background-color: #f8fafc;
            border-radius: 6px;
            padding: 10px 14px;
            line-height: 1.6;
            font-size: 9.5px;
            color: #475569;
            border: 1px solid #f1f5f9;
        }

        /* 🌟 Estandarización de Etiquetas del bloque inferior en Negrita */
        .detalle-tag {
            font-weight: bold;
            color: #334155;
            text-transform: uppercase;
            font-size: 8.5px;
            letter-spacing: 0.2px;
        }

        /* 🌟 BADGES REFINADOS (Estandarizados en Negrita limpia) */
        .badge {
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 8.5px;
            font-weight: bold;
            text-transform: uppercase;
            display: inline-block;
            letter-spacing: 0.3px;
        }

        .badge-abierto { background-color: #ffedd5; color: #ea580c; }
        .badge-proceso { background-color: #dbeafe; color: #2563eb; }
        .badge-cerrado { background-color: #dcfce7; color: #16a34a; }
        .badge-default { background-color: #f1f5f9; color: #475569; }
        
        .text-abierto { color: #ea580c; }
        .text-proceso { color: #2563eb; }
        .text-cerrado { color: #16a34a; }
        .text-default { color: #0f172a; }
    </style>
</head>

<body>

    <table class="header-table">
        <tr>
            <td class="logo-title">Help<span>Desk</span></td>
            <td class="meta-info">
                <strong>Fecha de Emisión:</strong> {{ date('d/m/Y H:i') }}<br>
                <strong>Generado por:</strong> {{ auth()->user()->name ?? 'Administrador' }}
            </td>
        </tr>
    </table>

    <div class="titulo-reporte">Historial de Tickets ISTU</div>

    <table class="stats-table">
        <tr>
            <td width="23%">
                <div class="stat-card card-abierto">
                    <span class="stat-label">Abiertos</span>
                    <span class="stat-value">
                        {{ $tickets->filter(fn($t) => Str::contains(Str::lower($t->estado->nombre_estado ?? ''), 'abierto'))->count() }}
                    </span>
                </div>
            </td>
            <td width="2.6%"></td>

            <td width="23%">
                <div class="stat-card card-proceso">
                    <span class="stat-label">En Proceso</span>
                    <span class="stat-value">
                        {{ $tickets->filter(fn($t) => Str::contains(Str::lower($t->estado->nombre_estado ?? ''), 'proceso'))->count() }}
                    </span>
                </div>
            </td>
            <td width="2.6%"></td>

            <td width="23%">
                <div class="stat-card card-cerrado">
                    <span class="stat-label">Cerrados</span>
                    <span class="stat-value">
                        {{ $tickets->filter(fn($t) => Str::contains(Str::lower($t->estado->nombre_estado ?? ''), ['equivocado', 'resuelto', 'no corresponde']))->count() }}
                    </span>
                </div>
            </td>
            <td width="2.6%"></td>

            <td width="23%">
                <div class="stat-card card-total">
                    <span class="stat-label">Total Reporte</span>
                    <span class="stat-value">{{ $tickets->count() }}</span>
                </div>
            </td>
        </tr>
    </table>

    <table class="tabla-datos">
        <thead>
            <tr>
                <th width="15%">ID</th>
                <th width="31%">Usuario / Unidad</th>
                <th width="13%">Prioridad</th>
                <th width="15%">Estado</th>
                <th width="13%">Apertura</th>
                <th width="13%">Cierre</th>
            </tr>
        </thead>
        <tbody>
            @foreach($tickets as $ticket)
                @php
                    $estadoNombre = Str::lower($ticket->estado->nombre_estado ?? '');
                    $badgeClass = 'badge-default';
                    $textClass = 'text-default';

                    if (Str::contains($estadoNombre, 'abierto')) {
                        $badgeClass = 'badge-abierto';
                        $textClass = 'text-abierto';
                    } elseif (Str::contains($estadoNombre, 'proceso')) {
                        $badgeClass = 'badge-proceso';
                        $textClass = 'text-proceso';
                    } elseif (Str::contains($estadoNombre, ['equivocado', 'resuelto', 'no corresponde'])) {
                        $badgeClass = 'badge-cerrado';
                        $textClass = 'text-cerrado';
                    }
                @endphp

                <tr class="fila-principal">
                    <td>
                        <strong class="{{ $textClass }}" style="font-size: 11px;">
                            #TK{{ str_pad($ticket->id, 5, '0', STR_PAD_LEFT) }}
                        </strong>
                    </td>
                    <td>
                        <strong style="color: #0f172a; font-weight: bold; font-size: 10.5px;">{{ $ticket->user->name ?? 'N/A' }}</strong><br>
                        <span style="color: #64748b; font-size: 8.5px; text-transform: uppercase; font-weight: bold;">
                            {{ $ticket->user->unidad->nombre_unidad ?? 'Sin Unidad' }}
                        </span>
                    </td>
                    <td><span style="color: #475569; font-weight: bold;">{{ $ticket->prioridad->nombre_prioridad ?? 'Media' }}</span></td>
                    <td>
                        <span class="badge {{ $badgeClass }}">
                            {{ $ticket->estado->nombre_estado ?? 'N/A' }}
                        </span>
                    </td>
                    <td><span style="color: #64748b; font-weight: bold;">{{ $ticket->created_at->format('d/m/Y') }}</span></td>
                    <td><span style="color: #64748b; font-weight: bold;">{{ $ticket->fecha_cierre ? date('d/m/Y', strtotime($ticket->fecha_cierre)) : '-------' }}</span></td>
                </tr>

                <tr class="fila-detalle">
                    <td colspan="6">
                        <div class="detalle-container">
                            <div style="margin-bottom: 6px; padding-bottom: 5px; border-bottom: 1px solid #e2e8f0;">
                                <span class="detalle-tag">Técnico:</span> <span style="color: #0f172a; font-weight: bold;">{{ $ticket->tecnico->name ?? 'No asignado' }}</span>
                                <span style="color: #cbd5e1; margin: 0 8px;">•</span>

                                <span class="detalle-tag">Categoría:</span> <span style="color: #334155; font-weight: bold;">{{ $ticket->categoria->nombre_categoria ?? 'N/A' }}</span>
                                <span style="color: #cbd5e1; margin: 0 8px;">•</span>

                                <span class="detalle-tag">Tipo:</span> <span style="color: #334155; font-weight: bold;">{{ $ticket->tipo_solicitud->nombre_tipo_solicitud ?? 'N/A' }}</span>
                                <span style="color: #cbd5e1; margin: 0 8px;">•</span>

                                <span class="detalle-tag">Asunto:</span> <span style="color: #0f172a; font-weight: bold;">{{ $ticket->asunto ?? 'Sin asunto' }}</span>
                            </div>

                            <div>
                                <span class="detalle-tag">Descripción:</span>
                                <span style="color: #334155; font-weight: bold; font-style: italic;">"{{ $ticket->descripcion ?? 'Sin descripción proporcionada.' }}"</span>
                            </div>
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

</body>

</html>