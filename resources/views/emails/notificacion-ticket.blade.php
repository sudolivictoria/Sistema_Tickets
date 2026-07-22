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

        .subtitulo-text {
            font-size: 14px;
            color: #334155;
            margin-top: 0;
            margin-bottom: 20px;
        }

        .divider-green {
            height: 3px;
            width: 50px;
            background-color: #84cc16;
            margin-bottom: 20px;
        }

        .info-card {
            border: 1px solid #e2e8f0;
            border-left: 6px solid #84cc16;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            background: #f8fafc;
        }

        .info-row {
            padding-bottom: 12px;
            margin-bottom: 12px;
            border-bottom: 1px solid #84cc16;
        }

        .info-row:last-child {
            padding-bottom: 0;
            margin-bottom: 0;
            border-bottom: none;
        }

        .label {
            display: block;
            font-weight: bold;
            color: #04003B;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }

        .value {
            display: block;
            color: #334155;
            font-size: 14px;
            font-weight: 700;
        }

        .comment-title {
            font-weight: 800;
            color: #04003B;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 25px;
            margin-bottom: 8px;
        }

        .description-box {
            background-color: #fafafa;
            border: 1.5px dashed #84cc16;
            padding: 15px;
            border-radius: 8px;
            color: #334155;
            font-size: 14px;
            margin-top: 5px;
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
        <div class="container" style="border-top: 8px solid {{ !empty($esResolucion) ? '#84cc16' : '#04003B' }};">
            <div class="header">
                <div class="logo-container">
                    <img src="{{ $message->embed(public_path('images/logo_istu.png')) }}" alt="Logo ISTU">
                </div>
                <h2>{{ $titulo }}</h2>
            </div>

            <div class="content">
                <p class="welcome-text">{{ $subtitulo }}</p>
                <div class="divider-green"></div>

                <div class="info-card">
                    <div class="info-row">
                        <div class="label">Código del Ticket:</div>
                        <div class="value">{{ $ticketCodigo }}</div>
                    </div>

                    @if (!empty($ticketAsunto))
                        <div class="info-row">
                            <div class="label">Asunto:</div>
                            <div class="value">{{ $ticketAsunto }}</div>
                        </div>
                    @endif

                    @if (!empty($nombreUsuario))
                        <div class="info-row">
                            <div class="label">Comentado por:</div>
                            <div class="value">{{ $nombreUsuario }}</div>
                        </div>
                    @endif

                    @if (!empty($nombreUnidad))
                        <div class="info-row">
                            <div class="label">Unidad:</div>
                            <div class="value">{{ $nombreUnidad }}</div>
                        </div>
                    @endif
                </div>

                <div class="comment-title">
                    {{ !empty($esResolucion) ? 'Comentario de Resolución:' : 'Comentario:' }}
                </div>

                <div class="description-box">
                    {!! nl2br(e($contenido)) !!}
                </div>

                <div class="btn-container" style="text-align: center; margin-top: 25px;">
                    <a href="{{ url('/login') }}" class="btn"
                        style="background-color: #84cc16; color: white !important; padding: 12px 25px; text-decoration: none; border-radius: 8px; font-weight: bold; display: inline-block;">
                        ACCEDER AL SISTEMA
                    </a>
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
