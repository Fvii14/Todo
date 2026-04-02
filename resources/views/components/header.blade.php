<link href="https://cdn.jsdelivr.net/npm/tailwindcss@latest/dist/tailwind.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />

<header class="bg-white border-b border-gray-300 sticky top-0 z-10">
    <div class="flex items-center justify-between px-4 py-3 md:px-8">
        <!-- Logo -->
        <a href="{{ route('user.home') }}">
            <img src="{{ asset('imagenes/cropped-ttflogo_back-192x192-4.png') }}" alt="Logo" class="w-12 h-12" />
        </a>

        <!-- Icono hamburguesa solo en móvil -->
        <div class="md:hidden">
            <button id="menu-btn" class="text-2xl text-[#3c3a60]">
                <i class="fas fa-bars"></i>
            </button>
        </div>

        <!-- Menú de navegación (escritorio) -->
        <nav class="hidden md:flex space-x-6">
            <a href="{{ route('user.home') }}" class="desktop-link text-[#3c3a60] font-normal text-lg">Mis Ayudas</a>
            <a href="{{ route('user.consultas') }}" class="desktop-link text-[#3c3a60] font-normal text-lg">Consultas</a>
            <a href="{{ route('user.profile-update') }}" class="desktop-link text-[#3c3a60] font-normal text-lg">Mi Cuenta</a>
            @auth
                @if(auth()->user()->contrataciones()->exists())
                    <a href="{{ route('user.recursos') }}" class="desktop-link text-[#3c3a60] font-normal text-lg">Recursos</a>
                @endif
            @endauth
        </nav>
    </div>

    <!-- Menú móvil -->
    <div id="mobile-menu" class="fixed top-16 left-0 w-full bg-white text-center shadow-md z-50 hidden md:hidden">
        <a href="{{ route('user.home') }}" class="mobile-link block py-3 border-b border-gray-200 text-[#3c3a60] font-normal text-lg">Mis Ayudas</a>
        <a href="{{ route('user.consultas') }}" class="mobile-link block py-3 border-b border-gray-200 text-[#3c3a60] font-normal text-lg">Consultas</a>
        <a href="{{ route('user.profile-update') }}" class="mobile-link block py-3 border-b border-gray-200 text-[#3c3a60] font-normal text-lg">Mi Cuenta</a>
        @auth
            @if(auth()->user()->contrataciones()->exists())
                <a href="{{ route('user.recursos') }}" class="mobile-link block py-3 border-b border-gray-200 text-[#3c3a60] font-normal text-lg">Recursos</a>
            @endif
        @endauth
    </div>

    <script>
        const btn = document.getElementById('menu-btn');
        const menu = document.getElementById('mobile-menu');

        btn.addEventListener('click', () => {
            menu.classList.toggle('hidden');
        });

        document.addEventListener("DOMContentLoaded", () => {
            // ACTIVAR ENLACE ACTIVO EN ESCRITORIO
            const desktopLinks = document.querySelectorAll(".desktop-link");
            desktopLinks.forEach(link => {
                const href = link.getAttribute("href");
                if (href && window.location.href.includes(href)) {
                    link.style.color = "#54debd";
                    link.style.fontWeight = "bold";
                }
            });

            // ACTIVAR ENLACE ACTIVO EN MÓVIL
            const mobileLinks = document.querySelectorAll(".mobile-link");
            mobileLinks.forEach(link => {
                const href = link.getAttribute("href");
                if (href && window.location.href.includes(href)) {
                    link.style.backgroundColor = "#54debd";
                    link.style.color = "white";
                    link.style.fontWeight = "bold";
                }
            });
        });
    </script>
</header>
