<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de Pagos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Ubuntu', sans-serif;
            list-style: none;
        }

        :root {
            --primary-color: #59edca;
            --primary-dark: rgb(6, 8, 8);
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
            border-color: var(--primary-dark);
        }

        .help-card {
            border-left: 4px solid var(--primary-color);
        }

        .list-group-item.active {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .alert-success {
            background-color: rgba(89, 237, 202, 0.2);
            border-color: var(--primary-color);
        }

        .text-danger {
            color: red;
        }

        .alert-success-custom {
            background-color: #d4edda;
            border-color: #c3e6cb;
            color: #155724;
        }

        .list-group-item-action:hover {
            background-color: #54debd;
        }

        h1 {
            padding: 20px;
            padding-top: 30px;
            font-size: 40px !important;
        }

        .container-fluid {
            min-height: 100vh;
            padding: 2rem 1rem;
            text-align: center;
        }

        @media(max-width: 767px) {
            .sidebar-mobile {
                display: flex;
                flex-direction: row;
                justify-content: space-between;
                padding: 0.5rem;
                width: 100%;
            }

            .sidebar-mobile .list-group {
                display: flex;
                flex-direction: row;
                gap: 0.3rem;
                width: 100%;
            }

            .sidebar-mobile .list-group a {
                flex: 1 1 auto;
                text-align: center;
                font-size: 0.7rem;
                padding: 0.4rem 0.2rem;
                border: 1px solid #ccc;
                border-radius: 0.25rem;
                background-color: #f8f9fa;
                white-space: nowrap;
            }

            .sidebar-mobile .list-group a.active {
                background-color: var(--primary-color);
                color: white;
                border-color: var(--primary-color);
            }

            header {
                width: 100%;
                display: block;
            }

            /* Asegura que .main-content esté debajo */
            .main-content {
                width: 100%;
            }
        }

        @media(max-width: 384px) {
            .col-md-3 {
                display: flex !important;
                flex-direction: row;
                flex-wrap: wrap;
                justify-content: space-around;
                padding: 0.5rem;
            }

            .col-md-3 .list-group {
                display: flex;
                flex-direction: row;
                flex-wrap: wrap;
                width: 100%;
            }

            .col-md-3 .list-group a {
                flex: 1 1 45%;
                text-align: center;
                padding: 0.4rem 0.2rem;
                font-size: 0.75rem;
                border: 1px solid #ddd;
                border-radius: 0.25rem;
                margin: 0.2rem;
            }
        }

        @media(min-width: 768px) {
            .col-md-3 {
                display: block;
            }
        }
    </style>
</head>

<body>
    <!-- Navbar -->
    @include('components.header')
    <x-gtm-noscript />
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 sidebar sidebar-mobile">
                <div class="list-group">
                    <a href="{{ route('user.profile-update') }}" class="list-group-item list-group-item-action">Editar
                        Perfil</a>
                    <a href="{{ route('user.historial-ayudas') }}"
                        class="list-group-item list-group-item-action {{ request()->routeIs('user.historial-ayudas') ? 'active' : '' }}">Historial
                        de Ayudas</a>
                    <a href="{{ route('user.historial-pagos') }}"
                        class="list-group-item list-group-item-action">Historial de Pago</a>
                    <a href="{{ route('editPaymentMethod') }}" class="list-group-item list-group-item-action">Método de
                        Pago</a>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 main-content">
                <h1>Historial de Ayudas</h1>

                <!-- Pagos Tabla -->
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Producto</th>
                                <th>Total Ayuda</th>
                                <th>Comisión</th>
                                <th>Fecha Contratación</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($historialAyudas as $historial)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $historial['producto'] ?? 'Sin producto' }}</td>
                                    <td>
                                        @if ($historial['estado'] === 'concedida')
                                            {{ number_format($historial['monto_total_ayuda'], 2, ',', '.') }}€
                                        @else
                                            @php
                                                $limites = [
                                                    'Ayuda Alquiler' => 6000,
                                                    'Ingreso Mínimo Vital' => 19000,
                                                    'Ayuda 100€ por hijo' => 3600,
                                                ];
                                                $producto = $historial['producto'] ?? '';
                                                $montoMaximo = $limites[$producto] ?? 0;
                                            @endphp
                                            Hasta {{ number_format($montoMaximo, 0, ',', '.') }}€
                                        @endif
                                    </td>
                                    <td>
                                        {{ $historial['estado'] === 'concedida'
                                            ? number_format($historial['monto_comision'], 2, ',', '.') . '%'
                                            : 'No disponible' }}
                                    </td>
                                    <td>{{ $historial['fecha_contratacion'] }}</td>
                                    <td>
                                        @if ($historial['estado'] == 'concedida')
                                            <span class="badge bg-success">Concedida</span>
                                        @elseif($historial['estado'] == 'procesando')
                                            <span class="badge bg-warning">Procesando</span>
                                        @elseif($historial['estado'] == 'tramitada')
                                            <span class="badge bg-warning">Tramitada</span>
                                        @elseif($historial['estado'] == 'rechazada')
                                            <span class="badge bg-danger">Rechazada</span>
                                        @else
                                            <span class="badge bg-danger">{{ ucfirst($historial['estado']) }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6">No hay productos contratados registrados aún.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>

    @include('components.footer')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
