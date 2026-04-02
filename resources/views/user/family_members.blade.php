@include('components.header')

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3 sidebar sidebar-mobile">
            <div class="list-group">
                <a href="{{ route('user.profile-update') }}" class="list-group-item list-group-item-action">
                    Editar Perfil
                </a>
                <a href="{{ route('user.family_members') }}"
                    class="list-group-item list-group-item-action {{ request()->routeIs('user.family_members') ? 'active' : '' }}">
                    Unidad Familiar
                </a>
                <a href="{{ route('user.historial-ayudas') }}" class="list-group-item list-group-item-action">
                    Historial de Ayudas
                </a>
                <a href="{{ route('user.historial-pagos') }}" class="list-group-item list-group-item-action">
                    Historial de Pago
                </a>
                <a href="{{ route('editPaymentMethod') }}" class="list-group-item list-group-item-action">
                    Método de Pago
                </a>
            </div>
        </div>

        <!-- Contenido principal -->
        <div class="col-md-9 main-content">
            <h1 class="h4 fw-bold mb-4">Miembros de tu unidad familiar</h1>
            <p class="mb-4">Comparte tu enlace de invitación con tus amigos y familiares para que se unan a tu unidad
                familiar.</p>

            <!-- Enlace de invitación -->
            <div class="mb-3">
                <input type="text" id="referralLink" value="{{ url('/login?ref_code=' . $ref_code_user) }}" readonly
                    class="form-control">
            </div>

            <div class="mb-3">
                <button onclick="copyReferralLink()" class="btn btn-success me-2">Copiar enlace</button>
                <button onclick="deleteUnit()" class="btn btn-danger">Salir de la unidad familiar</button>
            </div>

            <!-- Miembros -->
            @if($familyMembers->isEmpty())
                <p class="text-muted">No tienes miembros en tu unidad familiar.</p>
            @else
                <table class="table table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>Nombre</th>
                            <th>Email</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($familyMembers as $member)
                            <tr>
                                <td>{{ $member->name }}</td>
                                <td>{{ $member->email }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
</div>

<!-- Modal de confirmación -->
<div id="confirmationModal"
    class="position-fixed top-0 start-0 w-100 h-100 bg-dark bg-opacity-50 d-none justify-content-center align-items-center z-50">
    <div class="bg-white p-4 rounded shadow" style="max-width: 400px; width: 100%;">
        <h5 class="mb-3">¿Estás seguro de que deseas salir de la unidad familiar?</h5>
        <div class="d-flex justify-content-between">
            <button onclick="confirmDelete()" class="btn btn-danger">Sí, eliminar</button>
            <button onclick="cancelDelete()" class="btn btn-secondary">Cancelar</button>
        </div>
    </div>
</div>

@include('components.footer')

<!-- Toast -->
<div id="toast"
    class="toast position-fixed bottom-0 start-50 translate-middle-x bg-success text-white px-3 py-2 rounded d-none"
    role="alert">
    Enlace copiado al portapapeles
</div>

<!-- Estilos personalizados -->
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
        margin-top: 10px;
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

    .btn-danger{
        margin-bottom: 10px;
    }

    .btn-success{
        margin-bottom: 10px;
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

    .toast {
        visibility: hidden;
        min-width: 250px;
        margin-left: -125px;
        /* Centrar el toast */
        background-color: #4CAF50;
        color: white;
        text-align: center;
        border-radius: 2px;
        padding: 8px;
        position: fixed;
        z-index: 1;
        left: 50%;
        bottom: 30px;
        /* Mostrar en la parte inferior de la pantalla */
        font-size: 17px;
    }

    .toast.show {
        visibility: visible;
        animation: fadein 0.5s, fadeout 1.5s 2.5s;
    }

    /* Animación para el fade-in y fade-out */
    @keyframes fadein {
        from {
            bottom: 0;
            opacity: 0;
        }

        to {
            bottom: 30px;
            opacity: 1;
        }
    }

    @keyframes fadeout {
        from {
            opacity: 1;
        }

        to {
            opacity: 0;
        }
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

        .boton{
            margin-bottom:10px;
        }
    }

    @media(max-width: 475px) {
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

<!-- Script funcionalidad -->
<script>
    function copyReferralLink() {
        var copyText = document.getElementById("referralLink");
        copyText.select();
        copyText.setSelectionRange(0, 99999);
        document.execCommand("copy");

        var toast = document.getElementById("toast");
        toast.classList.remove("d-none");
        toast.classList.add("show");

        setTimeout(() => {
            toast.classList.remove("show");
            toast.classList.add("d-none");
        }, 3000);
    }

    function deleteUnit() {
        document.getElementById('confirmationModal').classList.remove('d-none');
    }

    function cancelDelete() {
        document.getElementById('confirmationModal').classList.add('d-none');
    }

    function confirmDelete() {
        fetch('{{ route('user.updateUnidadFamiliar') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                user_id: '{{ auth()->user()->id }}'
            })
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = "{{ route('user.family_members') }}";
                } else {
                    alert("Hubo un error al cambiar el ID de la unidad familiar.");
                }
            })
            .catch(error => console.error('Error:', error));
    }
</script>
