<!DOCTYPE html>
<html lang="es" class="h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Dashboard · Backoffice</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet" />
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet" />
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
    <script src="{{ asset('js/help-sidebar.js') }}"></script>
    <style>
        [x-cloak] {
            display: none !important;
        }
        
        .help-sidebar {
            transition: transform 0.3s ease-in-out;
        }
        
        .help-sidebar.closed {
            transform: translateX(100%);
        }
        
        .help-sidebar.open {
            transform: translateX(0);
        }
        
        .help-button {
            transition: all 0.2s ease-in-out;
        }
    </style>
</head>

<body x-data="{ drawerOpen: false }" class="h-full bg-gray-100 relative pt-0">

    @include('layouts.headerbackoffice')

    {{-- Botón fijo a la izquierda para abrir/cerrar --}}
    <button @click="drawerOpen = !drawerOpen"
        class="fixed top-1/2 left-0 transform -translate-y-1/2 bg-white p-2 rounded-r shadow-lg z-50 focus:outline-none">
        <i :class="drawerOpen ? 'bx bx-chevron-left' : 'bx bx-chevron-right'" class="text-2xl"></i>
    </button>

    {{-- Drawer lateral --}}
    <x-sidebar-admin />

    <div class="pt-4 px-4 max-w-7xl mx-auto pt-0">
        <nav class="flex" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-2 rtl:space-x-reverse">
                <li class="inline-flex items-center">
                    <a href="{{ route('user.home') }}" class="inline-flex items-center text-sm font-medium text-gray-600 hover:text-primary-600 transition-colors">
                        <i class="fas fa-home mr-2"></i>
                        Inicio
                    </a>
                </li>
                <li>
                    <div class="flex items-center">
                        <i class="fas fa-chevron-right text-gray-300 mx-2 text-xs"></i>
                        <a href="{{ route('wizards.index') }}" class="text-sm font-medium text-gray-600 hover:text-primary-600 transition-colors">
                            Wizards
                        </a>
                    </div>
                </li>
                <li aria-current="page">
                    <div class="flex items-center">
                        <i class="fas fa-chevron-right text-gray-300 mx-2 text-xs"></i>
                        <span class="ms-1 text-sm font-medium text-gray-500 md:ms-2">{{ $wizard->title ?: 'Wizard de Ayuda' }}</span>
                    </div>
                </li>
            </ol>
        </nav>
        
        <div class="py-6">
            <div class="mb-8">
                <div class="flex items-center mb-4">
                    <h1 class="text-3xl font-bold text-gray-900">
                        {{ $wizard->title ?: 'Wizard de Ayuda' }}
                    </h1>
                </div>
                @if($wizard->description)
                    <p class="text-gray-600">{{ $wizard->description }}</p>
                @endif
            </div>

            <!-- Vue Component Container -->
            <div id="wizard-container" 
                data-wizard="{{ json_encode($wizard) }}"
                data-organos="{{ json_encode($organos ?? []) }}"
                data-sectores="{{ json_encode($sectores ?? []) }}"
                data-question-types="{{ json_encode($questionTypes ?? []) }}"
                data-question-sectores="{{ json_encode($questionSectores ?? []) }}"
                data-question-categorias="{{ json_encode($questionCategorias ?? []) }}"
                data-mail-classes="{{ json_encode($mailClasses ?? []) }}"
                data-all-questions="{{ json_encode($allQuestions ?? []) }}"
                data-all-documents="{{ json_encode($allDocuments ?? []) }}"
                data-csrf="{{ csrf_token() }}">
            </div>
        </div>
    </div>

    @vite(['resources/js/app.js'])
    <div id="help-sidebar-app">
        <help-sidebar 
            title="Ayuda - {{ $wizard->title ?: 'Wizard' }}"
            main-title="Configuración de Wizard"
            main-description="Esta sección te permite configurar y completar tu wizard {{ $wizard->title ?: 'de ayuda' }}. Sigue el proceso paso a paso para crear una ayuda completa o configurar un envío de emails."
            :features="[
                'Configuración paso a paso guiada',
                'Interfaz intuitiva y fácil de usar',
                'Validación automática de datos',
                'Previsualización de resultados',
                'Guardado automático del progreso'
            ]"
            :steps="[
                'Completa cada paso del wizard en orden',
                'Rellena toda la información requerida',
                'Revisa y valida los datos introducidos',
                'Guarda el progreso regularmente',
                'Finaliza el wizard cuando esté completo'
            ]"
            additional-info="Este wizard te guiará a través de todo el proceso de configuración. Cada paso debe completarse antes de pasar al siguiente. Los datos se guardan automáticamente, por lo que puedes continuar más tarde si es necesario. El estado del wizard indica tu progreso actual."
            important-note="Completa todos los pasos requeridos antes de finalizar el wizard. Una vez completado, el wizard se activará y estará disponible para su uso. Revisa cuidadosamente toda la información antes de finalizar."
        ></help-sidebar>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            window.HelpSidebar.init('help-sidebar-app');
        });
    </script>
</body>

</html>
