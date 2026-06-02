<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Reporte Operativo de Tickets</title>
    <style>
        @page {
            margin: 35px 40px;
        }

        body {
            font-family: 'Helvetica', Arial, sans-serif;
            color: #334155;
            font-size: 10px;
            margin: 0;
            padding: 0;
            background-color: #ffffff;
        }

        /* --------- ENCABEZADO Y ESTADÍSTICAS ----------- */
        .header-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .logo-title { font-size: 24px; font-weight: bold; color: #0f172a; letter-spacing: -0.8px; }
        .logo-title span { color: #008F7E; }
        .meta-info { text-align: right; font-size: 9.5px; color: #64748b; line-height: 1.5; }
        .titulo-reporte { font-size: 13px; font-weight: bold; color: #1e293b; margin-bottom: 20px; text-transform: uppercase; letter-spacing: 0.8px; border-bottom: 2px solid #f1f5f9; padding-bottom: 6px; }

        .stats-table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        .stat-card { border-radius: 6px; padding: 12px 14px; border: 1px solid #e2e8f0; }
        .card-abierto { background-color: #fff7ed; border-left: 4px solid #ea580c; }
        .card-abierto .stat-label { color: #c2410c; }
        .card-abierto .stat-value { color: #ea580c; }
        .card-proceso { background-color: #eff6ff; border-left: 4px solid #2563eb; }
        .card-proceso .stat-label { color: #1d4ed8; }
        .card-proceso .stat-value { color: #2563eb; }
        .card-cerrado { background-color: #f0fdf4; border-left: 4px solid #16a34a; }
        .card-cerrado .stat-label { color: #15803d; }
        .card-cerrado .stat-value { color: #16a34a; }
        .card-total { background-color: #faf5ff; border-left: 4px solid #7c3aed; }
        .card-total .stat-label { color: #6d28d9; }
        .card-total .stat-value { color: #7c3aed; }
        .stat-label { font-size: 9px; text-transform: uppercase; font-weight: bold; display: block; margin-bottom: 4px; }
        .stat-value { font-size: 24px; font-weight: bold; }

         /* --------- TARJETAS DE TICKETS ----------- */
        .ticket-card {
            border-radius: 6px;
            margin-bottom: 18px;
            background-color: #ffffff;
            page-break-inside: avoid;
            border-top: 1px solid #e2e8f0;
            border-right: 1px solid #e2e8f0;
            border-bottom: 1px solid #e2e8f0;
        }

         /* --------- SECCION SUPERIOR CARD ----------- */
        .card-header { width: 100%; border-collapse: collapse; border-bottom: 1px solid #e2e8f0; }
        .card-header td { padding: 14px 16px; vertical-align: middle; }
        
        .col-id { font-size: 22px; font-weight: bold; width: 1%; white-space: nowrap; }
        .col-separador { color: #cbd5e1; font-size: 22px; width: 1%; padding: 0 10px; font-weight: 300; }
        .col-user { width: 50%; }
        .user-name { font-size: 14px; font-weight: bold; color: #0f172a; margin-bottom: 3px; }
        .user-unit { font-size: 10px; color: #64748b; font-weight: bold; }
        
        .col-prio { width: 20%; text-align: right; padding-right: 15px !important; }
        .col-status { width: 1%; text-align: right; white-space: nowrap; }

        /* BADGES */
        .badge {
            padding: 5px 12px;
            border-radius: 4px;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            display: inline-block;
            letter-spacing: 0.5px;
            white-space: nowrap; /* Evita que el texto se rompa */
        }

        /*  SECCION MEDIA CARD CAJON INFO  */
        .card-body { width: 100%; border-collapse: collapse; }
        .card-body td { padding: 14px 16px; vertical-align: top; }
        
        .info-label { font-size: 8.5px; text-transform: uppercase; color: #64748b; font-weight: bold; margin-bottom: 5px; display: block; letter-spacing: 0.3px; }
        .info-value { font-size: 11px; font-weight: bold; color: #0f172a; word-wrap: break-word; overflow-wrap: break-word; display: block; }

        /* SECCION INFERIOR DESCRIPCION Y FECHAS*/
        .card-footer-container { padding: 0 16px 16px 16px; }
        .inner-box { background-color: #f8fafc; border: 1px solid #f1f5f9; border-radius: 6px; padding: 12px 14px; }
        .desc-row { margin-bottom: 10px; }
        .desc-label { font-size: 9px; font-weight: bold; color: #475569; text-transform: uppercase; margin-right: 8px; }
        .desc-text { font-size: 10.5px; color: #0f172a; font-style: italic; font-weight: bold; overflow-wrap: break-word; display: block;}
        .inner-divider { border: 0; border-bottom: 1px solid #e2e8f0; margin: 8px 0 10px 0; }
        .dates-table { width: 100%; border-collapse: collapse; }
        .dates-table td { font-size: 10.5px; color: #0f172a; font-weight: bold; }
        .dates-label { font-size: 9px; color: #64748b; font-weight: bold; text-transform: uppercase; margin-right: 4px; }
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
            <td width="23%"><div class="stat-card card-abierto"><span class="stat-label">Abiertos</span><span class="stat-value">{{ $tickets->filter(fn($t) => Str::contains(Str::lower($t->estado->nombre_estado ?? ''), 'abierto'))->count() }}</span></div></td><td width="2.6%"></td>
            <td width="23%"><div class="stat-card card-proceso"><span class="stat-label">En Proceso</span><span class="stat-value">{{ $tickets->filter(fn($t) => Str::contains(Str::lower($t->estado->nombre_estado ?? ''), 'procesando'))->count() }}</span></div></td><td width="2.6%"></td>
            <td width="23%"><div class="stat-card card-cerrado"><span class="stat-label">Cerrados</span><span class="stat-value">{{ $tickets->filter(fn($t) => Str::contains(Str::lower($t->estado->nombre_estado ?? ''), ['equivocado', 'resuelto', 'no corresponde']))->count() }}</span></div></td><td width="2.6%"></td>
            <td width="23%"><div class="stat-card card-total"><span class="stat-label">Total Reporte</span><span class="stat-value">{{ $tickets->count() }}</span></div></td>
        </tr>
    </table>

    <div class="titulo-reporte" style="font-size: 11px; margin-top: 10px;">Registros Detallados de Incidentes</div>

    @foreach($tickets as $ticket)
        @php
            // ========ESTADOS==============
            $estado = strtolower($ticket->estado->nombre_estado ?? 'abierto');
            //--text
            $themeColor = match ($estado) {
                'abierto' => '#c2410c',         
                'procesando' => '#1d4ed8',     
                'resuelto' => '#008F7E',        
                'equivocado' => '#b91c1c',      
                'no corresponde' => '#a16207', 
                default => '#475569',           
            };

            //---bg
            $badgeBg = match ($estado) {
                'abierto' => '#ffedd5',        
                'procesando' => '#dbeafe',     
                'resuelto' => '#dcfce7',       
                'equivocado' => '#fee2e2',     
                'no corresponde' => '#fef9c3',  
                default => '#f1f5f9',           
            };

            //------BORDER------------------
            $badgeBorder = match ($estado) {
                'abierto' => '#fed7aa',         
                'procesando' => '#bfdbfe',      
                'resuelto' => '#bbf7d0',        
                'equivocado' => '#fecaca',     
                'no corresponde' => '#fef08a', 
                default => '#e2e8f0',           
            };


            //-----------PRIORIDADES----------------
            $prioridadId = $ticket->prioridad_id ?? 3; // Media por defecto
            $prioName = $ticket->prioridad->nombre_prioridad ?? 'Media';

            $prioBg = match ($prioridadId) {
                1 => '#b91c1c', 
                2 => '#ea580c', 
                3 => '#eab308',
                4 => '#10b981',
                default => '#94a3b8', 
            };
            $prioColor = '#ffffff';   //texto
        @endphp

        <div class="ticket-card" style="border-left: 5px solid {{ $themeColor }};">
            <table class="card-header">
                <tr>
                    <td class="col-id" style="color: {{ $themeColor }};">#TK{{ str_pad($ticket->id, 5, '0', STR_PAD_LEFT) }}</td>
                    <td class="col-separador">|</td>
                    <td class="col-user">
                        <div class="user-name">{{ $ticket->user->name ?? 'N/A' }}</div>
                        <div class="user-unit">{{ $ticket->user->unidad->nombre_unidad ?? 'Sin Unidad' }}</div>
                    </td>
                    <td class="col-prio">
                        <div class="info-value">
                            <span class="badge" style="background-color: {{ $prioBg }}; color: {{ $prioColor }}; border: 1px solid {{ $prioBg }};">
                                {{ $prioName }}
                            </span>
                        </div>
                    </td>
                    <td class="col-status">
                        <span class="badge" style="background-color: {{ $badgeBg }}; color: {{ $themeColor }}; border: 1px solid {{ $badgeBorder }};">
                            {{ $ticket->estado->nombre_estado ?? 'N/A' }}
                        </span>
                    </td>
                </tr>
            </table>

            <table class="card-body">
                <tr>
                    <td width="25%">
                        <span class="info-label">TÉCNICO</span>
                        <span class="info-value">{{ $ticket->tecnico->name ?? 'No asignado' }}</span>
                    </td>
                    <td width="25%">
                        <span class="info-label">CATEGORÍA</span>
                        <span class="info-value">{{ $ticket->categoria->nombre_categoria ?? 'N/A' }}</span>
                    </td>
                    <td width="25%">
                        <span class="info-label">SOLICITUD</span>
                        <span class="info-value">{{ $ticket->tipo_solicitud->nombre_tipo_solicitud ?? 'N/A' }}</span>
                    </td>
                    <td width="25%">
                        <span class="info-label">ASUNTO</span>
                        <span class="info-value">{{ $ticket->asunto ?? 'Sin asunto' }}</span>
                    </td>
                </tr>
            </table>

            <div class="card-footer-container">
                <div class="inner-box">
                    <div class="desc-row">
                        <span class="desc-label">DESCRIPCIÓN:</span>
                        <span class="desc-text">{{ $ticket->descripcion ?? 'Sin descripción proporcionada.' }}</span>
                    </div>
                    
                    <hr class="inner-divider">
                    
                    <table class="dates-table">
                        <tr>
                            <td width="50%">
                                <span class="dates-label">APERTURA:</span> 
                                {{ $ticket->created_at->format('d/m/Y') }}
                            </td>
                            <td width="50%">
                                <span class="dates-label">CIERRE:</span> 
                                {{ $ticket->fecha_cierre ? date('d/m/Y', strtotime($ticket->fecha_cierre)) : '-------' }}
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
            
        </div>
    @endforeach

</body>
</html>
