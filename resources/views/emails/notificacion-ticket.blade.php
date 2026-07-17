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
            border-top: 8px solid
                {{ !empty($datos['es_resolucion']) ? '#84cc16' : '#04003B' }}
            ;
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
            font-size: 15px;
            font-weight: bold;
            color: #04003B;
            margin-bottom: 2px;
        }

        .subtitulo-text {
            font-size: 13px;
            color: #64748b;
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
            border-radius: 8px;
            padding: 15px;
            margin: 15px 0;
            background: #f8fafc;
        }

        .info-row {
            margin-bottom: 8px;
        }

        .info-row:last-child {
            margin-bottom: 0;
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
            font-size: 14px;
            font-weight: 600;
        }

        .comment-title {
            font-weight: bold;
            color: #04003B;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 20px;
            margin-bottom: 5px;
        }

        .description-box {
            background-color: #fafafa;
            border: 1.5px dashed
                {{ !empty($datos['es_resolucion']) ? '#84cc16' : '#04003B' }}
            ;
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
        <div class="container" style="border-top: 8px solid {{ $esResolucion ? '#84cc16' : '#04003B' }};">
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
                        <span class="label">Código del Ticket:</span>
                        <span class="value">{{ $ticketCodigo }}</span>
                    </div>
                </div>

                <div class="comment-title">
                    {{ $esResolucion ? 'Comentario de Resolución:' : 'Contenido del Comentario:' }}
                </div>

                <div class="description-box">
                    {!! nl2br(e($contenido)) !!}
                </div>
            </div>
        </div>
</body>
</html>