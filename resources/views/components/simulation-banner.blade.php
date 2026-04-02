@php
    $isSimulating = session('is_simulating', false);
@endphp

@if($isSimulating)
    @php
        $originalAdminId = session('original_admin_id');
        $simulatedUserId = session('simulating_user_id');
        $originalAdmin = $originalAdminId ? \App\Models\User::find($originalAdminId) : null;
        $simulatedUser = $simulatedUserId ? \App\Models\User::find($simulatedUserId) : null;
    @endphp
    
    @if($originalAdmin && $simulatedUser)
        <style>
            body { padding-top: 80px !important; }
        </style>
        <div class="alert alert-warning fade show mb-4" role="alert" style="background-color: #fff3cd; border-color: #ffeaa7; color: #856404; position: fixed; top: 0; left: 0; right: 0; z-index: 9999; border-radius: 0; margin: 0; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
            <div class="d-flex align-items-center">
                <i class="fas fa-user-secret me-2"></i>
                <div class="flex-grow-1">
                    <strong>Modo Simulación Activo</strong><br>
                    <small>
                        Administrador: <strong>{{ $originalAdmin->name }}</strong> 
                        simulando como: <strong>{{ $simulatedUser->name }} ({{ $simulatedUser->email }})</strong>
                    </small>
                </div>
                <div class="ms-3">
                    <a href="{{ route('admin.simulation.stop') }}" class="btn btn-sm btn-danger">
                        <i class="fas fa-stop"></i> Detener simulación
                    </a>
                </div>
            </div>

        </div>
    @endif
@endif 