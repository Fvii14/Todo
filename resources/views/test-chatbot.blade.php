<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Página de Pruebas - Chatbot Dify</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            color: #333;
            margin-bottom: 20px;
        }

        p {
            color: #666;
            line-height: 1.6;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Página de Pruebas - Chatbot Dify</h1>
        <p>Esta es una página de pruebas para el chatbot Dify. El chatbot debería aparecer en la
            esquina inferior derecha de la página.</p>
        <p>Puedes interactuar con el chatbot haciendo clic en el botón flotante.</p>
    </div>

    <script>
        window.difyChatbotConfig = {
            token: 'fhinTQkFBxoIetDn',
            inputs: {

            },
            systemVariables: {

            },
            userVariables: {

            },
        }
    </script>
    <script src="https://udify.app/embed.min.js" id="fhinTQkFBxoIetDn" defer></script>

    <style>
        #dify-chatbot-bubble-button {
            background-color: #1C64F2 !important;
        }

        #dify-chatbot-bubble-window {
            width: 24rem !important;
            height: 40rem !important;
        }
    </style>
</body>

</html>
