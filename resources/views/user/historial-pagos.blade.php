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
            --primary-dark: #40d4b0;
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
            /* Verde claro */
            border-color: #c3e6cb;
            /* Borde verde claro */
            color: #155724;
            /* Texto verde oscuro */
        }

        .list-group-item-action:hover {
            background-color: #54debd;
        }

        .form-label {
            text-align: left;
            display: block;
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
                /* cada item ocupa casi la mitad */
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
                        class="list-group-item list-group-item-action">Historial de Ayudas</a>
                    <a href="{{ route('user.historial-pagos') }}"
                        class="list-group-item list-group-item-action active {{ request()->routeIs('user.profile-update') ? 'active' : '' }}">Historial
                        de Pago</a>
                    <a href="{{ route('editPaymentMethod') }}" class="list-group-item list-group-item-action">Método de
                        Pago</a>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 main-content">
                <h1>Historial de Pagos</h1>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Producto</th>
                                <th>Monto</th>
                                <th>Fecha de Pago</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($historialPagos as $pago)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $pago['producto'] ?? 'Sin producto' }}</td>
                                    <td>{{ number_format($pago['monto'], 2, ',', '.') }}€</td>
                                    <td>{{ $pago['fecha_pago'] }}</td>
                                    <td>
                                        @switch($pago['estado'])
                                            @case('exitoso')
                                            @case('succeded')

                                            @case('completed')
                                                <span class="badge bg-success">Completado</span>
                                            @break

                                            @case('pendiente')
                                            @case('pending')
                                                <span class="badge bg-warning">Pendiente</span>
                                            @break

                                            @case('rechazado')
                                            @case('error')

                                            @case('failed')
                                            @case('canceled')
                                                <span class="badge bg-danger">Fallido</span>
                                            @break

                                            @default
                                                <span class="badge bg-secondary">{{ ucfirst($pago['estado']) }}</span>
                                        @endswitch
                                    </td>
                                </tr>
                                @empty
                                    <tr>
                                        <td colspan="5">No hay pagos registrados aún.</td>
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
