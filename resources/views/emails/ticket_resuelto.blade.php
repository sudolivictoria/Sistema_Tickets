<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #1e293b;
            background-color: #f1f5f9;
            margin: 0;
            padding: 0;
        }

        .wrapper {
            background-color: #f1f5f9;
            padding: 20px 10px;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            border: 1px solid #e2e8f0;
            border-top: 8px solid #84cc16;
        }

        .header {
            background-color: #04003B;
            color: #ffffff;
            padding: 15px 20px;
            text-align: center;
        }

        .logo-container {
            margin-bottom: 5px;
        }

        .logo-container img {
            max-height: 85px;
            width: auto;
            display: block;
            margin: 0 auto;
        }

        .header h2 {
            margin: 5px 0 0 0;
            font-size: 18px;
            text-transform: uppercase;
            letter-spacing: 2px;
            font-weight: 800;
        }

        .content {
            padding: 30px;
        }

        .welcome-text {
            font-size: 14px;
            font-weight: bold;
            color: #04003B;
            margin-bottom: 5px;
        }


        .divider-green {
            height: 3px;
            width: 50px;
            background-color: #84cc16;
            margin-bottom: 20px;
        }

        .info-card {
            border: 2px solid #f1f5f9;
            border-left: 5px solid #84cc16;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            background: #fafafa;
        }

        .info-row {
            margin-bottom: 12px;
            padding-bottom: 5px;
            border-bottom: 1px solid #84cc16;
        }

        .info-row:last-child {
            border: none;
        }

        .label {
            font-weight: bold;
            color: #04003B;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .value {
            color: #334155;
            font-size: 15px;
            font-weight: 600;
        }

        .description-box {
            background-color: #f8fafc;
            border: 1.5px dashed #84cc16;
            padding: 15px;
            border-radius: 8px;
            color: #475569;
            font-style: italic;
            margin-top: 10px;
        }

        .footer {
            font-size: 11px;
            color: #64748b;
            text-align: center;
            padding: 20px;
            background: #f8fafc;
            border-top: 1px solid #e2e8f0;
        }

        .footer strong {
            color: #04003B;
        }
    </style>
</head>

<body>
    <div class="wrapper">
        <div class="container">
            <div class="header">
                <div class="logo-container">
                    <img src="{{ $message->embed(public_path('images/logo_istu.png')) }}" alt="Logo ISTU">
                </div>
                <h2>Ticket Resuelto</h2>
            </div>

            <div class="content">
                <p class="welcome-text">Hola, {{ $ticket->user->name }}</p>
                <div class="divider-green"></div>

                <p style="color: #04003B; font-weight: 500;">Nos complace informarle que el ticket que reportó ha sido
                    cerrado.</p>

                <div class="info-card">
                    <div class="info-row">
                        <span class="label">ID del Ticket:</span><br>
                        <span class="value">
                            <span>#TK</span>{{ str_pad($ticket->id, 5, '0', STR_PAD_LEFT) }}
                        </span>
                    </div>
                    <div class="info-row">
                        <span class="label">Asunto:</span><br>
                        <span class="value">{{ $ticket->asunto }}</span>
                    </div>
                    <div class="info-row">
                        <span class="label">Fecha de Cierre:</span><br>
                        <span class="value">{{ $ticket->fecha_cierre->format('d/m/Y') }} a las
                            {{ $ticket->fecha_cierre->format('H:i') }}</span>
                    </div>
                    <div class="info-row">
                        <span class="label">Técnico Responsable:</span><br>
                        <span class="value">{{ $ticket->tecnico->name ?? 'N/A' }}</span>
                    </div>
                </div>
            </div>

            <div class="footer">
                <strong>HELP DESK ISTU</strong><br>
                Instituto Salvadoreño de Turismo<br>
                &copy; {{ date('Y') }} Todos los derechos reservados.
            </div>
        </div>
    </div>
</body>

</html>