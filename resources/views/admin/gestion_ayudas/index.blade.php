<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Gestión de ayudas - Backoffice TTF</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet" />
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="{{ asset('js/help-sidebar.js') }}"></script>
    <style>
        .help-sidebar {
            transition: transform 0.3s ease-in-out;
        }
        
        .help-sidebar.closed {
            transform: translateX(100%);
        }
        
        .help-sidebar.open {
            transform: translateX(0);
        }
    </style>
</head>

<body class="bg-gray-50">
    @include('layouts.headerbackoffice')
    <div id="gestion-ayudas-app" 
        data-ayudas="{{ json_encode($ayudas) }}"
        data-organos="{{ json_encode($organos ?? []) }}"
        data-cuestionarios="{{ json_encode($questionnaires ?? []) }}">
    </div>

    <div id="help-sidebar-app">
        <help-sidebar 
            title="Gestión de ayudas y cuestionarios"
            main-title="Gestión de los requisitos y condiciones de las distintas ayudas y cuestionarios"
            main-description="Este gestor permite editar los requisitos y condiciones de las distintas ayudas y cuestionarios. Permitiendo así un correcto mantenimiento tras su creación mediante Wizards."
            :features="[
                'Editar requisitos de ayudas (usuario beneficiario/no beneficiario)',
                'Editar condiciones (saltos dentro del cuestionario)'
            ]"
            :steps="[
                'Navega a la pestaña de ayudas o cuestionarios, según lo que quieras modificar',
                'Busca la ayuda o cuestionario que quieras modificar y dale a su respectivo botón de acción',
                'Modifica los distintos requisitos y condiciones del elegido'
            ]"
            additional-info="El listado de ayudas tiene una columna llamada  <strong>cuestionario</strong> la cual, en caso de existir un cuestionario vinculado a dicha ayuda, te llevará dentro de su listado. Y viceversa con los cuestionarios."
        ></help-sidebar>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            window.HelpSidebar.init('help-sidebar-app');
        });
    </script>
</body>

</html>