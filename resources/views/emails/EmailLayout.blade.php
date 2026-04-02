<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Correo de Tu Trámite Fácil')</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }

        .email-container {
            font-family: Arial, sans-serif;
            padding: 24px;
            background-color: #ffffff;
            max-width: 600px;
            margin: 0 auto;
            border-radius: 12px;
            border: 1px solid #eee;
        }

        .email-header {
            text-align: center;
            margin-bottom: 24px;
        }

        .email-header img {
            max-width: 180px;
            height: auto;
        }

        footer {
            font-size: 12px;
            color: #54debd;
            margin-top: 24px;
        }

        footer a {
            color: #54debd;
            text-decoration: none;
        }

        hr {
            margin: 24px 0;
        }

        .email-button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #54debd;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
            font-weight: bold;
        }

        .email-button:hover {
            background-color: #45c0a3;
        }
    </style>
</head>

<body>
    <div class="email-container">
        <!-- HEADER CON LOGO -->
        <div class="email-header">
            <img src="https://tutramitefacil.es/wp-content/uploads/2024/04/LOGO-NUEVO-TTF-2024.png"
                alt="Tu Trámite Fácil">
        </div>

        <!-- CONTENIDO DEL EMAIL -->
        @yield('content')

        <hr />

        <!-- FOOTER -->
        <footer style="font-size: 14px; color: white; margin-top: 32px; text-align: center; line-height: 1.6; background-color: #3c3b60">
            <hr style="margin: 24px 0; border: none; border-top: 1px solid #eee;" />

            <p style="margin: 0;">
                Contacto: <a href="tel:+34650514166" style="color: #54debd; text-decoration: none;">650 514 166</a><br>
                Email: <a href="mailto:info@tutramitefacil.es"
                    style="color: #54debd; text-decoration: none;">info@tutramitefacil.es</a>
            </p>

            <div style="margin: 24px 0 12px;">
                <p style="font-weight: bold; text-decoration: underline; margin-bottom: 8px;">Redes sociales</p>
                <a href="https://www.instagram.com/Tutramitefacil.es/"
                    style="color: #54debd; text-decoration: none; display: inline-block; margin: 6px 12px;">
                    📸 Instagram
                </a>
                <a href="https://www.tiktok.com/@tutramitefacil.es"
                    style="color: #54debd; text-decoration: none; display: inline-block; margin: 6px 12px;">
                    🎵 TikTok
                </a>
                <a href="https://tutramitefacil.es/blog/"
                    style="color: #54debd; text-decoration: none; display: inline-block; margin: 6px 12px;">
                    📝 Blog
                </a>
            </div>

            <p style="color: #cec1c1; font-size: 12px; margin-top: 16px;">
                © 2025 Tu Trámite Fácil. Todos los derechos reservados.
            </p>
        </footer>

    </div>
</body>

</html>
