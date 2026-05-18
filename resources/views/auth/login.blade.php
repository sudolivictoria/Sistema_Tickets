<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Help Desk ISTU</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bree+Serif&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">


</head>

<body>
    <div class="login-container">
        <div class="login-card">
            <div class="bg1"></div>
            <div class="bg2"></div>
            <div class="card-content">
                <div style="text-align: center; margin-bottom: 16px;">
                    <a href="/" style="display:block;">
                        <img src="{{ asset('images/logo_istu.png') }}" alt="Logo"
                            style="height: 140px; width: 140px; object-fit: contain; margin:auto;">
                    </a>
                </div>
                <h1>Help Desk Istu</h1>

                @if ($errors->has('session_expired'))
                    <div class="error"
                        style="background-color: #fff3cd; color: #856404; border-left: 4px solid #eab308; padding: 12px; margin-bottom: 15px; border-radius: 6px;">
                        <p style="margin: 0; font-weight: 500;">{{ $errors->first('session_expired') }}</p>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="error">
                        <ul style="margin:0; padding-left: 18px;">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('login.post') }}"
                    style="display:flex; flex-direction:column; gap: 15px;">
                    @csrf
                    <div class="field">
                        <label for="email">Correo</label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus>
                    </div>

                    <div class="field" style="position: relative;">
                        <label for="password">Contraseña</label>
                        <input id="password" type="password" name="password" required>
                        <button type="button" id="toggle-password"
                            style="position: absolute; top: 36px; right: 12px; background: transparent; border: none; cursor: pointer; padding: 0;">
                            <svg id="eye-open" xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="#005c0c"
                                viewBox="0 0 24 24">
                                <path
                                    d="M12 5c-7.633 0-11 6.52-11 7s3.367 7 11 7 11-6.52 11-7-3.367-7-11-7zm0 12c-2.761 0-5-2.239-5-5s2.239-5 5-5 5 2.239 5 5-2.239 5-5 5zm0-8.5c-1.931 0-3.5 1.569-3.5 3.5s1.569 3.5 3.5 3.5 3.5-1.569 3.5-3.5-1.569-3.5-3.5-3.5z" />
                            </svg>
                            <svg id="eye-closed" xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                                fill="#005c0c" viewBox="0 0 24 24" style="display:none;">
                                <path
                                    d="M12 5c-7.633 0-11 6.52-11 7s3.367 7 11 7c1.645 0 3.223-.314 4.692-.884l3.186 3.186 1.414-1.414-18-18-1.414 1.414 3.705 3.705c-2.216 1.381-3.947 3.268-4.972 4.778 1.112 1.618 3.385 4.096 6.667 5.238l-1.38-1.38c-2.142-.702-3.715-2.43-4.268-3.506.982-1.336 3.124-3.932 7.481-3.932 1.763 0 3.34.408 4.683 1.09l1.937-1.937c-1.787-.926-3.847-1.561-6.62-1.561zm3.931 10.931l-1.655-1.655c.435-.484.724-1.121.724-1.776 0-1.378-1.122-2.5-2.5-2.5-.655 0-1.292.289-1.776.724l-1.655-1.655c.888-.66 1.957-1.064 3.431-1.064 2.761 0 5 2.239 5 5 0 1.474-.404 2.543-1.069 3.431z" />
                            </svg>
                        </button>
                    </div>

                    <button type="submit" class="button">Iniciar sesión</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        const toggleBtn = document.getElementById('toggle-password');
        const passwordInput = document.getElementById('password');
        const eyeOpen = document.getElementById('eye-open');
        const eyeClosed = document.getElementById('eye-closed');

        toggleBtn.addEventListener('click', function () {
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeOpen.style.display = 'none';
                eyeClosed.style.display = 'inline';
            } else {
                passwordInput.type = 'password';
                eyeOpen.style.display = 'inline';
                eyeClosed.style.display = 'none';
            }
        });
    </script>

</body>

</html>