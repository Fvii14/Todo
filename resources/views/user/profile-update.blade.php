<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil de Usuario</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .is-invalid {
            border-color: #e53e3e !important;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23e53e3e'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath stroke-linejoin='round' d='M5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23e53e3e' stroke='none'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right calc(0.375em + 0.1875rem) center;
            background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
        }

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
            --dark-color: #2d3748;
            --light-color: #f8fafc;
            --gray-light: #e2e8f0;
            --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.1);
            --shadow-md: 0 4px 6px rgba(0, 0, 0, 0.1);
            --radius-lg: 12px;
            --radius-md: 8px;
            --transition: all 0.3s ease;
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            color: var(--dark-color);
            font-weight: 500;
            padding: 0.75rem 1.5rem;
            border-radius: var(--radius-md);
            transition: var(--transition);
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
            border-color: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: var(--shadow-sm);
        }

        body {
            background-color: #f5f7fa;
            color: var(--dark-color);
            line-height: 1.6;
        }

        .container-fluid {
            min-height: 100vh;
            padding: 2rem 1rem;
        }

        .profile-container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-md);
            overflow: hidden;
        }

        .sidebar {
            color: white;
            padding: 2rem 1rem;
        }

        @media (min-width: 768px) {
            .sidebar {
                min-height: calc(100vh - 150px);
                border-radius: var(--radius-lg) 0 0 var(--radius-lg);
            }
        }

        .sidebar .list-group {
            border-radius: var(--radius-md);
        }

        .sidebar .list-group-item {
            background: transparent;
            color: var(--gray-light);
            border: none;
            padding: 0.75rem 1.25rem;
            margin-bottom: 0.5rem;
            border-radius: var(--radius-md);
            transition: var(--transition);
        }

        .sidebar .list-group-item:hover {
            background: rgba(255, 255, 255, 0.1);
            color: white;
        }

        .sidebar .list-group-item.active {
            background: var(--primary-color);
            color: var(--dark-color);
            font-weight: 500;
        }

        .main-content {
            padding: 2.5rem;
        }

        h1 {
            color: var(--dark-color);
            font-weight: 600;
            margin-bottom: 1.5rem;
            position: relative;
            padding-bottom: 0.5rem;
        }

        h1::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 60px;
            height: 4px;
            background: var(--primary-color);
            border-radius: 2px;
        }

        .form-label {
            font-weight: 500;
            color: var(--dark-color);
            margin-bottom: 0.5rem;
        }

        .form-control {
            padding: 0.75rem 1rem;
            border: 1px solid var(--gray-light);
            border-radius: var(--radius-md);
            transition: var(--transition);
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(89, 237, 202, 0.25);
        }

        .btn-danger {
            padding: 0.75rem 1.5rem;
            border-radius: var(--radius-md);
            transition: var(--transition);
        }

        .btn-danger:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-sm);
        }

        .alert-success-custom {
            background-color: rgba(89, 237, 202, 0.2);
            border-color: var(--primary-color);
            color: #155724;
            border-radius: var(--radius-md);
            padding: 1rem;
            margin-bottom: 1.5rem;
        }

        .text-danger {
            color: #e53e3e;
        }

        .profile-section {
            margin-bottom: 2rem;
        }

        .section-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--dark-color);
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid var(--gray-light);
        }

        /* Responsive styles */
        @media (max-width: 767px) {
            .sidebar-mobile {
                background: var(--dark-color);
                padding: 1rem;
                border-radius: var(--radius-lg) var(--radius-lg) 0 0;
            }

            .sidebar-mobile .list-group {
                display: flex;
                flex-direction: row;
                gap: 0.5rem;
                overflow-x: auto;
                padding-bottom: 0.5rem;
            }

            .sidebar-mobile .list-group-item {
                white-space: nowrap;
                padding: 0.5rem 1rem;
                font-size: 0.85rem;
            }

            .main-content {
                padding: 1.5rem;
            }
        }

        @media (max-width: 384px) {
            .sidebar-mobile .list-group {
                flex-wrap: wrap;
            }

            .sidebar-mobile .list-group-item {
                flex: 1 1 100%;
                text-align: center;
            }
        }

        .form-floating {
            position: relative;
            margin-bottom: 1.5rem;
        }

        .form-floating label {
            position: absolute;
            top: 0;
            left: 0;
            height: 100%;
            padding: 1rem 0.75rem;
            pointer-events: none;
            border: 1px solid transparent;
            transform-origin: 0 0;
            transition: opacity .1s ease-in-out, transform .1s ease-in-out;
        }

        .form-floating input:not(:placeholder-shown)~label,
        .form-floating input:focus~label {
            transform: scale(.85) translateY(-.75rem) translateX(.15rem);
            opacity: .65;
        }


        .nav-tabs .nav-link {
            color: #6c757d;
            /* gris Bootstrap para las pestañas inactivas */
        }

        .nav-tabs .nav-link.active {
            background-color: #e3f2fd;
            /* color de fondo activo */
            color: #0d6efd !important;
            /* azul Bootstrap */
            border-color: #dee2e6 #dee2e6 #fff;
        }
    </style>
</head>

<body>
    @include('components.header')
    <x-gtm-noscript />
    <div class="container-fluid">
        <div class="alert alert-info d-flex align-items-center mb-4"
            style="border-left: 4px solid var(--primary-color); background-color: rgba(89, 237, 202, 0.1); max-width: 1200px; margin: 0 auto;">
            <i class="fas fa-check-circle me-3" style="color: var(--primary-color); font-size: 1.5rem;"></i>
            <div>
                <p class="mb-0">Mantén tus datos actualizados para que podamos gestionar tus ayudas más rápido.</p>
            </div>
        </div>

        {{-- <div class="profile-container">
            <ul class="nav nav-tabs mb-4" id="profileTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="perfil-tab" data-bs-toggle="tab" data-bs-target="#perfil"
                        type="button" role="tab">Mi perfil</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="referidos-tab" data-bs-toggle="tab" data-bs-target="#referidos"
                        type="button" role="tab">Usuarios referidos</button>
                </li>
            </ul>

            <div class="row g-0">
                <div class="col-md-12 main-content">
                    <div class="mb-5">
                        <h1 class="display-5 fw-bold mb-3">Mi perfil y facturación</h1>
                        <p class="text-muted">Administra tu información personal y configuración</p>
                    </div>

                    <!-- Mostrar mensaje de éxito si existe -->
                    @if (session('success'))
                        <div class="alert alert-success-custom alert-dismissible fade show">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"
                                aria-label="Close"></button>
                        </div>
                    @endif

                    <!-- Formulario para mostrar y editar los datos -->
                    <form method="POST" action="{{ route('user.profile.update') }}">
                        @csrf

                        <div class="profile-section">
                            <h3 class="section-title">Información personal</h3>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="email" class="form-control" id="email" name="email"
                                            value="{{ auth()->user()->email }}" placeholder="Email">
                                        <label for="email">Email</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="tel" class="form-control" id="telefono" name="telefono"
                                            value="{{ old('telefono', $profileData['telefono'] ?? '') }}"
                                            placeholder="Teléfono">
                                        <label for="telefono">Teléfono</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="profile-section">
                            <h3 class="section-title">Datos fiscales</h3>
                            <div class="form-floating">
                                <input type="text" class="form-control" id="domicilio" name="domicilio"
                                    value="{{ old('domicilio', $profileData['domicilio'] ?? '') }}"
                                    placeholder="Domicilio Fiscal">
                                <label for="domicilio">Domicilio Fiscal</label>
                            </div>
                        </div>

                        <div class="profile-section">
                            <h3 class="section-title">Datos bancarios</h3>
                            <div class="form-floating">
                                <input type="text" class="form-control" id="cuenta_banco" name="cuenta_banco"
                                    value="{{ old('cuenta_banco', $profileData['cuenta_banco'] ?? '') }}"
                                    placeholder="Nº de cuenta bancaria (IBAN)">
                                <label for="cuenta_banco">Nº de cuenta bancaria (IBAN)</label>
                            </div>
                        </div>

                        <div class="profile-section">
                            <h3 class="section-title">Cambiar contraseña</h3>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="password" minlength="8" class="form-control" id="contrasena"
                                            name="contrasena" value="" autocomplete="new-password"
                                            placeholder="Nueva contraseña">
                                        <label for="contrasena">Nueva contraseña</label>
                                        <small class="form-text text-muted">Mínimo 8 caracteres. Dejar en blanco si no
                                            deseas cambiarla.</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="password" class="form-control" minlength="8"
                                            id="repetir_contrasena" name="contrasena_confirmation"
                                            placeholder="Repetir nueva contraseña">
                                        <label for="repetir_contrasena">Repetir nueva contraseña</label>
                                        <small id="repetirContrasenaHelp" class="form-text text-danger"
                                            style="display:none;">
                                            Las contraseñas no coinciden.
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Guardar cambios
                            </button>
                            <a href="{{ route('logout') }}" class="btn btn-danger">
                                <i class="fas fa-sign-out-alt me-2"></i>Cerrar sesión
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div> --}}



        <div class="profile-container">
            <!-- PESTAÑAS -->
            <ul class="nav nav-tabs mb-4" id="profileTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active fw-bold" id="perfil-tab" data-bs-toggle="tab"
                        data-bs-target="#perfil" type="button" role="tab">Mi perfil</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-bold" id="referidos-tab" data-bs-toggle="tab" data-bs-target="#referidos"
                        type="button" role="tab">Tus referidos</button>
                </li>
            </ul>

            <div class="tab-content">
                <!-- PESTAÑA PERFIL -->
                <div class="tab-pane fade show active" id="perfil" role="tabpanel" aria-labelledby="perfil-tab">
                    <div class="row g-0">
                        <div class="col-md-12 main-content">
                            <div class="mb-5">
                                <h1 class="display-5 fw-bold mb-3">Mi perfil y facturación</h1>
                                <p class="text-muted">Administra tu información personal y configuración</p>
                            </div>

                            @if (session('success'))
                                <div class="alert alert-success-custom alert-dismissible fade show">
                                    {{ session('success') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"
                                        aria-label="Close"></button>
                                </div>
                            @endif

                            <form method="POST" action="{{ route('user.profile.update') }}">
                                @csrf

                                <!-- Información personal -->
                                <div class="profile-section">
                                    <h3 class="section-title">Información personal</h3>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <div class="form-floating">
                                                <input type="email" class="form-control" id="email" name="email"
                                                    value="{{ auth()->user()->email }}" placeholder="Email">
                                                <label for="email">Email</label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-floating">
                                                <input type="tel" class="form-control" id="telefono"
                                                    name="telefono"
                                                    value="{{ old('telefono', $profileData['telefono'] ?? '') }}"
                                                    placeholder="Teléfono">
                                                <label for="telefono">Teléfono</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Datos fiscales -->
                                <div class="profile-section">
                                    <h3 class="section-title">Datos fiscales</h3>
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="domicilio" name="domicilio"
                                            value="{{ old('domicilio', $profileData['domicilio'] ?? '') }}"
                                            placeholder="Domicilio Fiscal">
                                        <label for="domicilio">Domicilio Fiscal</label>
                                    </div>
                                </div>

                                <!-- Datos bancarios -->
                                <div class="profile-section">
                                    <h3 class="section-title">Datos bancarios</h3>
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="cuenta_banco" name="cuenta_banco"
                                            value="{{ old('cuenta_banco', $profileData['cuenta_banco'] ?? '') }}"
                                            placeholder="Nº de cuenta bancaria (IBAN)">
                                        <label for="cuenta_banco">Nº de cuenta bancaria (IBAN)</label>
                                    </div>
                                </div>

                                <!-- Contraseña -->
                                <div class="profile-section">
                                    <h3 class="section-title">Cambiar contraseña</h3>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <div class="form-floating">
                                                <input type="password" minlength="8" class="form-control"
                                                    id="contrasena" name="contrasena" value=""
                                                    autocomplete="new-password" placeholder="Nueva contraseña">
                                                <label for="contrasena">Nueva contraseña</label>
                                                <small class="form-text text-muted">Mínimo 8 caracteres. Dejar en
                                                    blanco si no
                                                    deseas cambiarla.</small>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-floating">
                                                <input type="password" class="form-control" minlength="8"
                                                    id="repetir_contrasena" name="contrasena_confirmation"
                                                    placeholder="Repetir nueva contraseña">
                                                <label for="repetir_contrasena">Repetir nueva contraseña</label>
                                                <small id="repetirContrasenaHelp" class="form-text text-danger"
                                                    style="display:none;">
                                                    Las contraseñas no coinciden.
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Botones -->
                                <div class="d-flex justify-content-between align-items-center mt-4">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-2"></i>Guardar cambios
                                    </button>
                                    <a href="{{ route('logout') }}" class="btn btn-danger">
                                        <i class="fas fa-sign-out-alt me-2"></i>Cerrar sesión
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- PESTAÑA REFERIDOS -->
                <div class="tab-pane fade" id="referidos" role="tabpanel" aria-labelledby="referidos-tab">
                    <div class="mt-5">
                        <h3 class="section-title ms-4">Usuarios que usaron tu código de referido</h3>

                        @if ($referredUsers->isEmpty())
                            <p class="text-muted m-3">Todavía nadie ha usado tu código de referido. ¡Compártelo y gana
                                recompensas! 🎁</p>
                        @else
                            <div class="table-responsive mt-4 ">
                                <table class="table table-striped ml-1">
                                    <thead>
                                        <tr>
                                            <th>Nombre</th>
                                            <th>Email</th>
                                            <th>¿Contrató?</th>
                                            <th>Fecha de registro</th>

                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($referredUsers as $ref)
                                            <tr>
                                                <td>{{ $ref->nombre_real ?? 'Sin nombre' }}</td>
                                                <td>{{ $ref->email }}</td>
                                                <td>
                                                    @php
                                                        $haContratadoBonoCultural = $ref->contrataciones->contains(
                                                            'ayuda_id',
                                                            44,
                                                        );
                                                    @endphp

                                                    @if ($haContratadoBonoCultural)
                                                        <span class="badge bg-success">Sí</span>
                                                    @else
                                                        <span class="badge bg-secondary">No</span>
                                                    @endif
                                                </td>

                                                <td>{{ $ref->created_at->format('d/m/Y') }}</td>

                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

    </div>
    @include('components.footer')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Añadir efecto de floating labels para navegadores que no lo soportan nativamente
        document.querySelectorAll('.form-floating input').forEach(input => {
            input.addEventListener('focus', function() {
                this.parentNode.querySelector('label').classList.add('active');
            });

            input.addEventListener('blur', function() {
                if (this.value === '') {
                    this.parentNode.querySelector('label').classList.remove('active');
                }
            });

            // Inicializar estados
            if (input.value !== '') {
                input.parentNode.querySelector('label').classList.add('active');
            }
        });
        document.addEventListener('DOMContentLoaded', function() {
            const contrasena = document.getElementById("contrasena");
            const repetirContrasena = document.getElementById("repetir_contrasena");
            const mensajeError = document.getElementById("repetirContrasenaHelp");
            const form = document.querySelector('form');

            function validarPassword() {
                if (contrasena.value && repetirContrasena.value) {
                    if (contrasena.value !== repetirContrasena.value) {
                        mensajeError.style.display = "block";
                        repetirContrasena.classList.add('is-invalid');
                        return false;
                    } else {
                        mensajeError.style.display = "none";
                        repetirContrasena.classList.remove('is-invalid');
                    }
                }
                return true;
            }

            // Validación mientras escribe
            contrasena.addEventListener('input', validarPassword);
            repetirContrasena.addEventListener('input', validarPassword);

            // Validación al enviar
            form.addEventListener('submit', function(e) {
                if (contrasena.value.length > 0) {
                    if (contrasena.value.length < 8) {
                        e.preventDefault();
                        alert("La contraseña debe tener al menos 8 caracteres.");
                        contrasena.focus();
                        return false;
                    }

                    if (!validarPassword()) {
                        e.preventDefault();
                        repetirContrasena.focus();
                        return false;
                    }
                }
            });
        });
    </script>
</body>

</html>
