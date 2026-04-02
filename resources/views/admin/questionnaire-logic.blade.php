{{-- resources/views/admin/questionnaire-logic.blade.php --}}
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lógica Visual del Cuestionario</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 min-h-screen">
    @include('layouts.headerbackoffice')
    <main class="container mx-auto px-4 py-8">
        <h1 class="text-2xl font-bold mb-6 flex items-center gap-2">
            <i class="fas fa-project-diagram text-indigo-600"></i>
            Lógica Visual del Cuestionario
        </h1>
        <div class="bg-white rounded-xl shadow p-6">
            <div id="questionnaire-logic-app"
                data-questionnaire-id="{{ $questionnaire->id ?? '' }}"
                data-ayuda-id="{{ $ayuda->id ?? '' }}"
                data-csrf="{{ csrf_token() }}"
                data-questions='@json($questions ?? [])'
                data-conditions='@json($conditions ?? [])'>
                <!-- Aquí se monta el componente Vue -->
            </div>
        </div>
    </main>
</body>
</html> 