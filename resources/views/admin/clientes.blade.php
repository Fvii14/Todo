{{-- resources/views/admin/clientes.blade.php --}}
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Clientes · Backoffice</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body class="bg-gray-100">

  {{-- Header --}}
  @include('layouts.headerbackoffice')

  <main class="container mx-auto px-4 py-6 pt-0">
    <h1 class="text-2xl font-bold mb-4">Listado de Clientes</h1>
     {{-- Formulario de filtros --}}
  <form method="GET" class="flex flex-wrap gap-4 items-end">
    {{-- Filtro CCAA --}}
    <div>
      <label for="ccaa" class="block text-sm font-medium text-gray-700">Comunidad Autónoma</label>
      <select name="ccaa" id="ccaa" class="mt-1 block w-full border rounded p-2">
        <option value="">Todas</option>
        @foreach($ccaas as $nombre)
          <option value="{{ $nombre }}" {{ request('ccaa') === $nombre ? 'selected' : '' }}>
            {{ $nombre }}
          </option>
        @endforeach
      </select>
    </div>

    {{-- Filtro nombre/email --}}
    <div>
      <label for="search" class="block text-sm font-medium text-gray-700">Nombre o Email</label>
      <input
        type="text"
        name="search"
        id="search"
        value="{{ request('search') }}"
        placeholder="Buscar..."
        class="mt-1 block w-full border rounded p-2"
      />
    </div>

    <div>
      <button type="submit"
              class="bg-[#54debd] text-white px-4 py-2 rounded hover:opacity-90">
        Filtrar
      </button>
    </div>

    <div>
      <a href="{{ route('admin.clientes.index') }}"
         class="text-gray-600 underline">
        Limpiar
      </a>
    </div>
  </form>

  

    @if($clientes->isEmpty())
      <p class="text-gray-600">No hay clientes registrados.</p>
    @else
      <div class="overflow-x-auto bg-white rounded-lg mt-5 shadow">
        <table class="min-w-full">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-4 py-2 text-left">Cliente</th>
              <th class="px-4 py-2 text-center"># Contrataciones</th>
              <th class="px-4 py-2 text-right">Total recibido (€)</th>
              <th class="px-4 py-2 text-center">% Concedidas</th>
            </tr>
          </thead>
          <tbody>
            @foreach($clientes as $user)
              @php
                $total     = $user->contrataciones_sum_monto ?? 0;
                $count     = $user->contrataciones_count;
                $concedidas = $user->concedidas_count;
                $porcentaje = $count
                  ? round($concedidas / $count * 100, 1)
                  : 0;
              @endphp
              <tr class="border-t hover:bg-gray-50">
                <td class="px-4 py-3">
                  <div class="font-medium">{{ $user->name }}</div>
                  <div class="text-xs text-gray-500">{{ $user->email }}</div>
                </td>
                <td class="px-4 py-3 text-center">{{ $count }}</td>
                <td class="px-4 py-3 text-right">{{ number_format($total, 2, ',', '.') }}</td>
                <td class="px-4 py-3 text-center">{{ $porcentaje }}%</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
      {{-- Paginación --}}
      <div class="mt-4">
        {{ $clientes->links() }}
      </div>
    @endif
  </main>

</body>
</html>
