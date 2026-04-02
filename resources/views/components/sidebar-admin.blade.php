<aside x-cloak x-show="drawerOpen" @click.away="drawerOpen = false"
    x-transition:enter="transition-transform duration-200 ease-out"
    x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0"
    x-transition:leave="transition-transform duration-200 ease-in"
    x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full"
    class="fixed inset-y-0 left-0 w-64 bg-white border-r shadow-lg overflow-y-auto z-40">
    <nav class="mt-6">
        <a href="{{ route('ayudas.index') }}"
            class="flex items-center px-4 py-2 hover:bg-gray-100 transition-colors">
            <i class="bx bx-donate-heart text-xl"></i>
            <span class="ml-3">Ayudas</span>
        </a>

        <a href="{{ route('questionnaires.index') }}"
            class="flex items-center px-4 py-2 hover:bg-gray-100 transition-colors">
            <i class="bx bx-edit text-xl"></i>
            <span class="ml-3">Formularios</span>
        </a>

        <a href="{{ route('admin.documentos') }}"
            class="flex items-center px-4 py-2 hover:bg-gray-100 transition-colors">
            <i class="bx bx-file text-xl"></i>
            <span class="ml-3">Documentos</span>
        </a>

        <a href="{{ route('admin.preguntas') }}"
            class="flex items-center px-4 py-2 hover:bg-gray-100 transition-colors">
            <i class="bx bx-question-mark text-xl"></i>
            <span class="ml-3">Preguntas</span>
        </a>

        <a href="{{ route('admin.flujos-tramitacion') }}"
            class="flex items-center px-4 py-2 hover:bg-gray-100 transition-colors">
            <i class="bx bx-git-branch text-xl"></i>
            <span class="ml-3">Flujos de Tramitación</span>
        </a>

        <a href="{{ route('admin.historialquestionnaire') }}"
            class="flex items-center px-4 py-2 hover:bg-gray-100 transition-colors">
            <i class="bx bx-history text-xl"></i>
            <span class="ml-3">Historial de formularios</span>
        </a>

        <a href="{{ route('ayudas.editar') }}"
            class="flex items-center px-4 py-2 hover:bg-gray-100 transition-colors">
            <i class="bx bx-data text-xl"></i>
            <span class="ml-3">Datos ayudas</span>
        </a>

        <a href="{{ route('ayudas.recursos') }}"
            class="flex items-center px-4 py-2 hover:bg-gray-100 transition-colors">
            <i class="bx bx-collection text-xl"></i>
            <span class="ml-3">Recursos de ayudas</span>
        </a>

        <a href="{{ route('admin.logicas') }}"
            class="flex items-center px-4 py-2 hover:bg-gray-100 transition-colors">
            <i class="bx bx-question-mark text-xl"></i>
            <span class="ml-3">Lógicas</span>
        </a>

        <a href="{{ route('admin.tickets.index') }}"
            class="flex items-center px-4 py-2 hover:bg-gray-100 transition-colors">
            <i class="bx bx-support text-xl"></i>
            <span class="ml-3">Tickets</span>
        </a>

        <a href="{{ route('ayuda_datos.create') }}"
            class="flex items-center px-4 py-2 hover:bg-gray-100 transition-colors">
            <i class="bx bx-layout text-xl"></i>
            <span class="ml-3">Interfaces</span>
        </a>

        <a href="{{ route('wizards.index') }}"
            class="flex items-center px-4 py-2 hover:bg-gray-100 transition-colors">
            <i class="bx bx-folder text-xl"></i>
            <span class="ml-3">Wizards</span>
        </a>

        <a href="{{ route('admin.simulation.index') }}"
            class="flex items-center px-4 py-2 hover:bg-gray-100 transition-colors">
            <i class="bx bx-test-tube text-xl"></i>
            <span class="ml-3">Simulación</span>
        </a>

        <a href="{{ route('operativa.liquidaciones.index') }}"
            class="flex items-center px-4 py-2 hover:bg-gray-100 transition-colors">
            <i class="bx bx-dollar text-xl"></i>
            <span class="ml-3">Concesiones y pagos</span>
        </a>
        <a href="{{ route('admin.simulation.index') }}"
            class="flex items-center px-4 py-2 hover:bg-gray-100 transition-colors">
            <i class="bx bx-test-tube text-xl"></i>
            <span class="ml-3">Simulación</span>
        </a>

        <a href="{{ route('admin.gestion-ayudas.index') }}"
            class="flex items-center px-4 py-2 hover:bg-gray-100 transition-colors">
            <i class="bx bx-cog text-xl"></i>
            <span class="ml-3">Gestión de ayudas</span>
        </a>
    </nav>

</aside>
