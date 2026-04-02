@php
    $code = $exception->getStatusCode();
    $titles = [
        400 => 'Solicitud incorrecta',
        401 => 'No autorizado',
        403 => 'Acceso denegado',
        404 => 'Página no encontrada',
        419 => 'Sesión expirada',
        429 => 'Demasiadas peticiones',
        500 => 'Error interno del servidor',
        503 => 'Servicio no disponible',
    ];
    $messages = [
        400 => 'La solicitud no se pudo procesar correctamente.',
        401 => 'Necesitas iniciar sesión para acceder a esta página.',
        403 => 'No tienes permisos suficientes.',
        404 => 'La página que buscas no existe o fue eliminada.',
        419 => 'Tu sesión ha expirado, actualiza la página e inténtalo de nuevo.',
        429 => 'Has hecho demasiadas peticiones en poco tiempo.',
        500 => 'Ha ocurrido un error inesperado en el servidor.',
        503 => 'Estamos en mantenimiento, vuelve pronto.',
    ];
    $title = $titles[$code] ?? 'Error';
    $message = $messages[$code] ?? 'Ha ocurrido un error inesperado.';
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error {{ $code ?? 'Error' }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Ubuntu:wght@400;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        body {
            background-color: #e9ecef;
            font-family: 'Ubuntu', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            overflow: hidden;
        }

        canvas#background-canvas {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
            pointer-events: none;
        }

        .card {
            position: relative;
            z-index: 1;
            border: none;
            border-radius: 1rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
            animation: fadeInUp 0.9s ease;
            background-color: #ffffff;
        }

        .error-code {
            font-size: 6rem;
            font-weight: 700;
            color: #dc3545;
            line-height: 1;
        }

        .emoji {
            font-size: 3rem;
        }

        .btn-danger {
            background-color: #dc3545;
            border-color: #dc3545;
        }

        .btn-danger:hover {
            background-color: #c82333;
            border-color: #bd2130;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(40px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        @media (max-width: 576px) {
        .error-code {
            font-size: 3.5rem;
        }

        .emoji {
            font-size: 2rem;
        }

        .card {
            padding: 1.5rem 1rem !important;
        }

        .card-body p {
            font-size: 1rem !important;
        }

        .btn-danger.btn-lg {
            font-size: 1rem;
            padding: 0.75rem 1.25rem;
        }
    }
    </style>
</head>

<body>
<canvas id="background-canvas"></canvas>

<div class="container">
    <div class="card text-center p-5">
        <div class="card-body">
            <div class="emoji mb-3">⚠️</div>
            <div class="error-code">{{ $exception->getStatusCode() ?? 'Error' }}</div>
            <h2 class="mb-3 text-danger">{{ $title ?? '¡Algo salió mal!' }}</h2>
            <p class="fs-5 mb-4">{{ $message ?? 'Intenta volver a la página de inicio o contáctanos si el problema persiste.' }}</p>
            <div class="d-flex gap-2 justify-content-center">
                <a href="{{ route('user.home') }}" class="btn btn-danger btn-lg">Volver al inicio</a>
                <button type="button" class="btn btn-outline-secondary btn-lg" data-bs-toggle="modal" data-bs-target="#reportarProblemaModal">
                    <i class="fas fa-bug me-2"></i>Reportar Problema
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    function getRandomInt(max) {
        return Math.floor(Math.random() * max);
    }

    const canvas = document.getElementById('background-canvas');
    const ctx = canvas.getContext('2d');
    let width, height;
    const waveCount = 6;
    const waves = [];

    function resizeCanvas() {
        width = canvas.width = window.innerWidth;
        height = canvas.height = window.innerHeight;
    }

    function initWaves() {
        waves.length = 0;
        for (let i = 0; i < waveCount; i++) {
            const colors = [
                `rgba(84, 222, 189, ${0.1 + Math.random() * 0.15})`,
                `rgba(255, 51, 146, ${0.1 + Math.random() * 0.15})`,
                `rgba(52, 49, 75, ${0.1 + Math.random() * 0.15})`,
                `rgba(240, 231, 125, ${0.1 + Math.random() * 0.15})`
            ];
            waves.push({
                offset: Math.random() * 1000,
                speed: 0.005,
                amplitude: 350 + Math.random() * 40,
                frequency: 0.00001 + Math.random() * 0.003,
                color: colors[getRandomInt(4)],
                lineWidth: 1.5 + Math.random() * 0.6,
            });
        }
    }

    function draw() {
        ctx.clearRect(0, 0, width, height);
        const centerY = height / 2;

        for (const wave of waves) {
            ctx.beginPath();
            ctx.strokeStyle = wave.color;
            ctx.lineWidth = wave.lineWidth;

            for (let x = 0; x < width; x++) {
                const y = centerY + Math.sin(x * wave.frequency + wave.offset) * wave.amplitude;
                ctx.lineTo(x, y);
            }

            ctx.stroke();
            wave.offset += wave.speed;
        }

        requestAnimationFrame(draw);
    }

    window.addEventListener('resize', () => {
        resizeCanvas();
        initWaves();
    });

    resizeCanvas();
    initWaves();
    draw();
</script>

<!-- Modal para reportar problema -->
<div class="modal fade" id="reportarProblemaModal" tabindex="-1" aria-labelledby="reportarProblemaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="reportarProblemaModalLabel">
                    <i class="fas fa-bug text-warning me-2"></i>Reportar Problema
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="reportarProblemaForm">
                <div class="modal-body">
                    <div id="loginRequired" style="display: none;">
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Necesitas iniciar sesión:</strong> Para reportar un problema, primero debes iniciar sesión en tu cuenta.
                        </div>
                        <div class="text-center">
                            <a href="{{ route('login') }}" class="btn btn-primary btn-lg">
                                <i class="fas fa-sign-in-alt me-2"></i>Iniciar Sesión
                            </a>
                        </div>
                    </div>
                    
                    <div id="reportForm">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Información automática:</strong> Se incluirá automáticamente la URL del error, tu navegador y sistema operativo.
                        </div>
                        
                        <div class="mb-3">
                            <label for="descripcion" class="form-label">
                                <strong>Descripción del problema:</strong>
                            </label>
                            <textarea class="form-control" id="descripcion" name="descripcion" rows="4" 
                                      placeholder="Describe qué estabas haciendo cuando ocurrió el error, qué esperabas que pasara y qué pasó en su lugar..."></textarea>
                            <div class="form-text">Cuanto más detallada sea tu descripción, más fácil será solucionar el problema.</div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" id="submitBtn" class="btn btn-primary" style="display: none;">
                        <i class="fas fa-paper-plane me-2"></i>Enviar Reporte
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Verificar autenticación cuando se abre el modal
document.getElementById('reportarProblemaModal').addEventListener('show.bs.modal', function() {
    // Verificar si el usuario está autenticado haciendo una petición al servidor
    fetch('/api/check-auth', {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.authenticated) {
            // Usuario autenticado - mostrar formulario
            document.getElementById('loginRequired').style.display = 'none';
            document.getElementById('reportForm').style.display = 'block';
            document.getElementById('submitBtn').style.display = 'inline-block';
        } else {
            // Usuario no autenticado - mostrar mensaje de login
            document.getElementById('loginRequired').style.display = 'block';
            document.getElementById('reportForm').style.display = 'none';
            document.getElementById('submitBtn').style.display = 'none';
        }
    })
    .catch(error => {
        console.error('Error verificando autenticación:', error);
        // En caso de error, mostrar formulario de login
        document.getElementById('loginRequired').style.display = 'block';
        document.getElementById('reportForm').style.display = 'none';
        document.getElementById('submitBtn').style.display = 'none';
    });
});

document.getElementById('reportarProblemaForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData();
    formData.append('url_error', window.location.href);
    
    // Detectar navegador de forma más precisa
    const userAgent = navigator.userAgent;
    let navegador = 'Desconocido';
    if (userAgent.includes('Chrome')) navegador = 'Chrome';
    else if (userAgent.includes('Firefox')) navegador = 'Firefox';
    else if (userAgent.includes('Safari')) navegador = 'Safari';
    else if (userAgent.includes('Edge')) navegador = 'Edge';
    else if (userAgent.includes('Opera')) navegador = 'Opera';
    
    formData.append('navegador', navegador);
    formData.append('version_navegador', navigator.appVersion.substring(0, 50)); // Limitar a 50 caracteres
    
    // Detectar sistema operativo de forma más precisa
    const platform = navigator.platform;
    let so = 'Desconocido';
    if (platform.includes('Win')) so = 'Windows';
    else if (platform.includes('Mac')) so = 'macOS';
    else if (platform.includes('Linux')) so = 'Linux';
    else if (platform.includes('Android')) so = 'Android';
    else if (platform.includes('iOS')) so = 'iOS';
    
    formData.append('so', so);
    formData.append('descripcion', document.getElementById('descripcion').value);
    
    // Log para debug
    console.log('Datos a enviar:');
    for (let [key, value] of formData.entries()) {
        console.log(key + ':', value);
    }
    
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Enviando...';
    submitBtn.disabled = true;
    
    fetch('/tickets/create', {
        method: 'POST',
        body: formData,
        headers: {
            'Accept': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Mostrar mensaje de éxito
            const modal = bootstrap.Modal.getInstance(document.getElementById('reportarProblemaModal'));
            modal.hide();
            
            // Crear y mostrar alerta de éxito
            const alertDiv = document.createElement('div');
            alertDiv.className = 'alert alert-success alert-dismissible fade show position-fixed';
            alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
            alertDiv.innerHTML = `
                <i class="fas fa-check-circle me-2"></i>
                <strong>¡Reporte enviado!</strong> Hemos recibido tu reporte y lo revisaremos pronto.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            document.body.appendChild(alertDiv);
            
            // Remover la alerta después de 5 segundos
            setTimeout(() => {
                if (alertDiv.parentNode) {
                    alertDiv.remove();
                }
            }, 5000);
            
            // Limpiar el formulario
            document.getElementById('descripcion').value = '';
        } else {
            throw new Error(data.message || 'Error al enviar el reporte');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        
        // Mostrar mensaje de error
        const alertDiv = document.createElement('div');
        alertDiv.className = 'alert alert-danger alert-dismissible fade show position-fixed';
        alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        alertDiv.innerHTML = `
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>Error:</strong> ${error.message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        document.body.appendChild(alertDiv);
        
        // Remover la alerta después de 5 segundos
        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.remove();
            }
        }, 5000);
    })
    .finally(() => {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
});
</script>
</body>
</html>
