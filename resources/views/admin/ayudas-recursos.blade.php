<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Recursos de las ayudas</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="bg-gradient-to-br from-gray-50 to-gray-100 font-sans text-gray-800 min-h-screen">
    @include('layouts.headerbackoffice')
    <div class="w-full max-w-screen-xl mx-auto px-4 py-6 space-y-8">
        <nav class="flex" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-2 rtl:space-x-reverse">
                <li class="inline-flex items-center">
                    <a href="{{ route('admin.dashboardv2') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600">
                        <i class="fas fa-home mr-2"></i>
                        Inicio
                    </a>
                </li>
                <li aria-current="page">
                    <div class="flex items-center">
                        <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
                        <span class="ms-1 text-sm font-medium text-gray-500 md:ms-2">Recursos de las ayudas</span>
                    </div>
                </li>
            </ol>
        </nav>

        <h1 class="text-3xl font-bold text-gray-800">Recursos de las ayudas</h1>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($ayudas as $ayuda)
                <div class="bg-white rounded-lg shadow p-4 hover:shadow-md transition-shadow duration-300">
                    <h2 class="text-lg font-bold">
                        <a href="{{ route('ayudas.recursos.edit', $ayuda->id) }}" class="text-blue-600 hover:text-blue-800 hover:underline">
                            {{ $ayuda->nombre_ayuda }}
                        </a>
                    </h2>
                    <p class="text-sm text-gray-500">Número de recursos: {{ $ayuda->recursos->count() }}</p>
                </div>
            @endforeach
        </div>
    </div>
</body>

</html>