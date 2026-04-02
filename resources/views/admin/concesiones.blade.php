<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <title>Operativa · Liquidaciones</title>

    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">


    <style>
        [x-cloak] {
            display: none !important
        }
    </style>

    {{-- Si ya lo cargas en el layout, puedes quitar estas dos líneas --}}
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>

<body class="bg-gray-50 text-gray-900">
    @include('layouts.headerbackoffice')

    @php $tab = request('tab','concesiones'); @endphp

    <div class="mb-4 border-b border-gray-200">
        {{-- Navegación entre pestañas --}}
        <nav class="flex gap-4 text-sm">
            <a href="{{ route('operativa.liquidaciones.index', array_merge(request()->except('page'), ['tab' => 'concesiones'])) }}"
                class=" px-3 py-2 border-b-2 {{ $tab === 'concesiones' ? 'border-blue-600 text-blue-700 bg-emerald-50 rounded-md font-bold' : 'border-transparent text-gray-600 hover:text-gray-800 ' }} ">
                Indicar concesiones y comisión
            </a>
            <a href="{{ route('operativa.liquidaciones.index', array_merge(request()->except('page'), ['tab' => 'pagos'])) }}"
                class="px-3 py-2 border-b-2 {{ $tab === 'pagos' ? 'border-blue-600 text-blue-700 bg-emerald-50 rounded-md font-bold' : 'border-transparent text-gray-600 hover:text-gray-800' }}">
                Registrar pagos de la Administración
            </a>
        </nav>
    </div>
    <div class="bg-white rounded-xl shadow-sm ring-1 ring-gray-100 p-4 sm:p-6 mt-4">
        {{-- === PESTAÑA 1: Concesiones y comisión === --}}
        @if ($tab === 'concesiones')
            @include('admin.components_admin.concesiones_component')
            @include('admin.components_admin.modal_concesion')
        @endif

        {{-- === PESTAÑA 2: Pagos de la Administración === --}}
        @if ($tab === 'pagos')
            @include('admin.components_admin.pagos_administracion')
            @include('admin.components_admin.modal_pagos_administracion')
        @endif
    </div>
    {{-- Helpers globales para abrir/cerrar modal de concesión --}}
    <script>
        window.openMontosModal = function(params) {
            window.dispatchEvent(new CustomEvent('open-montos', {
                detail: params
            }));
        };
        window.closeMontosModal = function() {
            window.dispatchEvent(new CustomEvent('close-montos'));
        };
    </script>

    {{-- Helpers globales para abrir/cerrar modal de pagos administración --}}
    <script>
        window.openPagoAdminModal = function(params) {
            window.dispatchEvent(new CustomEvent('open-pago-admin', {
                detail: params
            }));
        };
        window.closePagoAdminModal = function() {
            window.dispatchEvent(new CustomEvent('close-pago-admin'));
        };
    </script>
</body>

</html>
