<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmación de Ayuda</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Ubuntu', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .card {
            border: none;
            border-radius: 1rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            animation: fadeInUp 1s ease;
        }

        .btn-primary {
            background-color: #54debd;
            border-color: #54debd;
        }

        .btn-primary:hover {
            background-color: #40d4b0;
            border-color: #40d4b0;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>

    <!-- Google Tag Manager -->
    <script>
        (function(w, d, s, l, i) {
            w[l] = w[l] || [];
            w[l].push({
                'gtm.start': new Date().getTime(),
                event: 'gtm.js'
            });
            var f = d.getElementsByTagName(s)[0],
                j = d.createElement(s),
                dl = l != 'dataLayer' ? '&l=' + l : '';
            j.async = true;
            j.src =
                'https://www.googletagmanager.com/gtm.js?id=' + i + dl;
            f.parentNode.insertBefore(j, f);
        })(window, document, 'script', 'dataLayer', 'GTM-W9GF583');
    </script>
    <!-- End Google Tag Manager -->
</head>

<body>
    <x-gtm-noscript />
    <div class="container">
        <div class="card text-center p-5">
            <div class="card-body">
                <h1 class="card-title mb-4" style="color: #40d4b0;">¡Enhorabuena!</h1>
                <p class="card-text fs-5 mb-4">
                    Eres <strong>100% beneficiario</strong> de esta ayuda.
                </p>
                <p class="text-muted mb-4">
                    Por el momento no estamos tramitando esta ayuda, pero te confirmamos que reúnes las condiciones para
                    ser beneficiario.
                </p>
                <a href="/home" class="btn btn-primary btn-lg">Continuar</a>
            </div>
        </div>
    </div>

    <!-- Redirección automática después de 13 segundos -->
    <script>
        setTimeout(function() {
            window.location.href = '/home';
        }, 13000);
    </script>

</body>

</html>
