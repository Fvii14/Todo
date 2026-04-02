<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="user-id" content="{{ $currentUserId ?? Auth::id() }}">
    <title>Bandeja de Trabajo · Backoffice</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet" />
    <style>
        .undo-progress {
            background: conic-gradient(from 0deg, #fbbf24 0deg, transparent 0deg);
            transition: background 0.1s ease;
        }

        .undo-progress.animate {
            animation: countdown 5s linear forwards;
        }

        @keyframes countdown {
            from {
                background: conic-gradient(from 0deg, #fbbf24 0deg, transparent 0deg);
            }

            to {
                background: conic-gradient(from 0deg, transparent 0deg, transparent 360deg);
            }
        }

        .undo-button {
            transition: all 0.3s ease;
        }

        .undo-button:hover {
            transform: scale(1.05);
        }

        .user-result {
            transition: all 0.2s ease;
        }

        .user-result:hover {
            background-color: #f3f4f6;
            transform: translateX(2px);
        }

        .search-results {
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            border: 1px solid #e5e7eb;
        }

        .user-search-input:focus {
            border-color: #54debd;
            box-shadow: 0 0 0 3px rgba(84, 222, 189, 0.1);
        }

        .description-toggle {
            transition: all 0.2s ease;
            cursor: pointer;
            user-select: none;
        }

        .description-toggle:hover {
            text-decoration: underline;
            transform: translateY(-1px);
        }

        .description-content {
            transition: opacity 0.3s ease, max-height 0.3s ease;
            overflow: hidden;
        }

        .description-expanded {
            max-height: none;
        }

        .description-collapsed {
            max-height: 3rem;
        }

        .edit-button {
            transition: all 0.2s ease;
        }

        .edit-button:hover {
            transform: scale(1.05);
        }

        .modal-overlay {
            backdrop-filter: blur(2px);
        }

        .modal-content {
            animation: modalSlideIn 0.3s ease-out;
        }

        @keyframes modalSlideIn {
            from {
                opacity: 0;
                transform: translateY(-20px) scale(0.95);
            }

            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        /* Estilos para el modal de edición */
        .modal-overlay {
            backdrop-filter: blur(4px);
            z-index: 9999 !important;
        }

        .modal-content {
            animation: modalSlideIn 0.3s ease-out;
            max-height: 90vh;
            overflow-y: auto;
            z-index: 10000 !important;
        }

        @keyframes modalSlideIn {
            from {
                opacity: 0;
                transform: translateY(-20px) scale(0.95);
            }

            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        /* Estilos para botones de edición */
        .edit-button:hover {
            background-color: rgba(59, 130, 246, 0.1);
        }

        /* Estilos para el modal de confirmación */
        .modal-overlay.hidden {
            display: none !important;
        }

        /* Estilos para el progreso del undo */
        .undo-progress {
            background: conic-gradient(from 0deg, #fbbf24 0deg, #fbbf24 var(--progress), transparent var(--progress));
            --progress: 0deg;
        }

        /* Estilos para preservar saltos de línea */
        .whitespace-pre-line {
            white-space: pre-line;
        }
    </style>
</head>

<body class="bg-gray-100">
    @include('layouts.headerbackoffice')

    <div class="w-full max-w-screen-xl mx-auto px-4 py-6 space-y-8">
        <h1 class="text-3xl font-bold text-gray-800">Bandeja de Trabajo</h1>

        @php
            use Carbon\Carbon;
        @endphp

        {{-- ==== Panel de estadísticas ==== --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-4 flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Urgentes (≤{{ $umbral }}d)</p>
                    <p class="mt-1 text-2xl font-semibold text-gray-800">{{ $urgentes }}</p>
                </div>
                <i class="bx bx-error text-3xl text-red-500"></i>
            </div>
            <div class="bg-white rounded-lg shadow p-4 flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Productividad</p>
                    <p class="mt-1 text-2xl font-semibold text-gray-800">{{ $productividad }}%</p>
                </div>
                <i class="bx bx-line-chart text-3xl text-gray-800"></i>
            </div>
        </div>

        {{-- Notificaciones --}}
        <div id="notification" class="fixed top-4 right-4 z-50 hidden">
            <div class="bg-white rounded-lg shadow-lg p-4 max-w-sm">
                <div class="flex items-center">
                    <div id="notificationIcon" class="mr-3"></div>
                    <div>
                        <p id="notificationText" class="text-sm font-medium text-gray-800"></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function showNotification(message, isError = false) {
            const notification = document.getElementById('notification');
            const notificationText = document.getElementById('notificationText');
            const notificationIcon = document.getElementById('notificationIcon');

            notificationText.textContent = message;

            if (isError) {
                notificationIcon.innerHTML = '<i class="bx bx-error-circle text-red-500 text-xl"></i>';
                notification.classList.add('border-l-4', 'border-red-500');
            } else {
                notificationIcon.innerHTML =
                    '<i class="bx bx-check-circle text-green-500 text-xl"></i>';
                notification.classList.add('border-l-4', 'border-green-500');
            }

            notification.classList.remove('hidden');

            setTimeout(() => {
                notification.classList.add('hidden');
            }, 3000);
        }

        function setupUserSearch() {
            const searchInput = document.getElementById('userSearch');
            const searchResults = document.getElementById('searchResults');
            const selectedUserId = document.getElementById('selectedUserId');

            if (!searchInput || !searchResults || !selectedUserId) {
                console.error('No se encontraron los elementos del buscador:', {
                    searchInput: !!searchInput,
                    searchResults: !!searchResults,
                    selectedUserId: !!selectedUserId
                });
                return;
            }

            let searchTimeout;

            searchInput.addEventListener('input', function(e) {
                const query = e.target.value.trim();

                if (query.length < 2) {
                    searchResults.classList.add('hidden');
                    return;
                }

                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    searchUsers(query);
                }, 300);
            });

            document.addEventListener('click', function(e) {
                if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
                    searchResults.classList.add('hidden');
                }
            });
        }

        async function searchUsers(query) {
            try {
                const response = await fetch(
                    `/admin/admin-panel/users/search?q=${encodeURIComponent(query)}`);

                if (!response.ok) {
                    throw new Error('Error en la búsqueda');
                }

                const data = await response.json();
                displaySearchResults(data);
            } catch (error) {
                showNotification('Error al buscar usuarios', true);
            }
        }

        function displaySearchResults(data) {
            const searchResults = document.getElementById('searchResults');
            const selectedUserId = document.getElementById('selectedUserId');

            let users = data;
            if (data.users && Array.isArray(data.users)) {
                users = data.users;
            } else if (Array.isArray(data)) {
                users = data;
            } else {
                return;
            }

            if (users.length === 0) {
                searchResults.innerHTML =
                    '<div class="p-3 text-gray-500">No se encontraron usuarios</div>';
                searchResults.classList.remove('hidden');
                return;
            }

            const resultsHtml = users.map(user => `
                <div class="user-result p-3 hover:bg-gray-100 cursor-pointer border-b border-gray-200 last:border-b-0" 
                     onclick="selectUser('${user.id}', '${user.name}')">
                    <div class="font-medium text-gray-800">${user.name}</div>
                    <div class="text-sm text-gray-600">${user.email || 'Sin email'}</div>
                    <div class="text-sm text-gray-500">${user.phone || 'Sin teléfono'}</div>
                </div>
            `).join('');

            searchResults.innerHTML = resultsHtml;
            searchResults.classList.remove('hidden');
        }

        function getSelectedUserName() {
            const searchInput = document.getElementById('userSearch');
            return searchInput.value;
        }

        function showTab(tabName) {
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.add('hidden');
            });

            document.querySelectorAll('#tramitacionTab, #ventasTab').forEach(button => {
                button.classList.remove('bg-[#54debd]', 'text-white', 'hover:bg-[#43c5a9]');
                button.classList.add('bg-gray-200', 'text-gray-700', 'hover:bg-gray-300');

                const counter = button.querySelector('span');
                if (counter) {
                    if (button.id === 'tramitacionTab') {
                        counter.classList.remove('text-[#54debd]');
                        counter.classList.add('text-gray-600');
                    }
                }
            });

            document.getElementById(`${tabName}Content`).classList.remove('hidden');

            const activeButton = document.getElementById(`${tabName}Tab`);
            activeButton.classList.remove('bg-gray-200', 'text-gray-700', 'hover:bg-gray-300');
            activeButton.classList.add('bg-[#54debd]', 'text-white', 'hover:bg-[#43c5a9]');

            const activeCounter = activeButton.querySelector('span');
            if (activeCounter && tabName === 'tramitacion') {
                activeCounter.classList.remove('text-gray-600');
                activeCounter.classList.add('text-[#54debd]');
            }
        }

        function selectUser(userId, userName) {
            const searchInput = document.getElementById('userSearch');
            const selectedUserId = document.getElementById('selectedUserId');
            const searchResults = document.getElementById('searchResults');

            if (searchInput && selectedUserId) {
                searchInput.value = userName;
                selectedUserId.value = userId;
                searchResults.classList.add('hidden');
                showNotification(`Usuario seleccionado: ${userName}`, false);
            }
        }

        function debugHTMLStructure() {}

        function getCurrentUserId() {
            const userIdMeta = document.querySelector('meta[name="user-id"]');

            if (userIdMeta && userIdMeta.content) {
                const userId = parseInt(userIdMeta.content);
                if (!isNaN(userId) && userId > 0) {
                    return userId;
                }
            }

            return null;
        }

        // ====== FUNCIONES DE SCORING ======
        /**
         * Carga el scoring para un usuario específico
         */
        async function loadScoringForUser(container, userId) {
            try {
                const response = await fetch(`/api/admin/scoring/usuario/${userId}`, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                            .content
                    }
                });

                if (response.ok) {
                    const data = await response.json();
                    if (data.success && data.data) {
                        updateScoringDisplay(container, data.data);
                    } else {
                        updateScoringDisplay(container, null, 'Sin scoring');
                    }
                } else {
                    updateScoringDisplay(container, null, 'Error');
                }
            } catch (error) {
                console.error('Error cargando scoring:', error);
                updateScoringDisplay(container, null, 'Error');
            }
        }

        /**
         * Actualiza la visualización del scoring
         */
        function updateScoringDisplay(container, scoringData, errorMessage = null) {
            const scoringValue = container.querySelector('.scoring-value');
            const scoringBadge = container.querySelector('.scoring-badge');

            if (!scoringValue || !scoringBadge) return;

            if (errorMessage) {
                scoringValue.textContent = 'N/A';
                scoringBadge.className =
                    'scoring-badge bg-gray-500 text-white px-4 py-2 rounded-full text-lg font-bold shadow-lg';
                return;
            }

            if (scoringData && scoringData.scoring) {
                const score = scoringData.scoring.score_total;
                const estadoComercial = scoringData.estado_comercial || 'sin_estado';

                scoringValue.textContent = `Score: ${score}`;

                // Cambiar color según el estado comercial
                let badgeClass =
                    'scoring-badge text-white px-4 py-2 rounded-full text-lg font-bold shadow-lg';

                switch (estadoComercial) {
                    case 'caliente':
                        badgeClass += ' bg-gradient-to-r from-red-500 to-orange-500';
                        break;
                    case 'tibio':
                        badgeClass += ' bg-gradient-to-r from-yellow-500 to-orange-500';
                        break;
                    case 'frio':
                        badgeClass += ' bg-gradient-to-r from-blue-500 to-cyan-500';
                        break;
                    default:
                        badgeClass += ' bg-gradient-to-r from-gray-500 to-gray-600';
                        break;
                }

                scoringBadge.className = badgeClass;

                scoringBadge.title =
                    `Estado: ${estadoComercial}\nScore total: ${score}\nUrgencia: ${scoringData.scoring.urgencia_deadline}\nTemperatura: ${scoringData.scoring.temperatura}\nValor estimado: ${scoringData.scoring.valor_estimado_norm}`;
            } else {
                scoringValue.textContent = 'N/A';
                scoringBadge.className =
                    'scoring-badge bg-gray-500 text-white px-4 py-2 rounded-full text-lg font-bold shadow-lg';
            }
        }

        /**
         * Abre el modal del historial de usuario
         */
        function openUserHistoryModal(userId) {
            if (!userId || userId === 'null') {
                showNotification('No se puede abrir el historial: Usuario no válido', true);
                return;
            }

            // DESACOPLADO: apertura de users-history
            // const url = `/dashboardv2/users-history?open_user=${userId}`;
            // window.open(url, '_blank');
            showNotification('Historial de usuarios desactivado temporalmente', true);
        }
    </script>
</body>

</html>
