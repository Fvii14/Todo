<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="UTF-8" />
   <meta name="viewport" content="width=device-width, initial-scale=1" />
   <title>Panel de documentación usuarios</title>
   <script src="https://cdn.tailwindcss.com"></script>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
</head>

<body>
   <header class="bg-indigo-600 text-white shadow-lg">
      <div class="container mx-auto px-4 py-3 sm:px-6 sm:py-4">
         <div class="flex items-center justify-between">
            <!-- Logo y nombre -->
            <div class="flex items-center space-x-3">
               <div
                  class="w-10 h-10 sm:w-12 sm:h-12 bg-indigo-500 rounded-xl flex items-center justify-center flex-shrink-0">
                  <i class="fas fa-folder-open text-lg sm:text-xl"></i>
               </div>
               <h1 class="text-xl sm:text-2xl font-bold truncate">
                  <a href="/dashboard" class="text-white hover:text-indigo-100 inline-flex">
                     <span class="animate-float">C</span>
                     <span class="animate-float delay-100">o</span>
                     <span class="animate-float delay-200">l</span>
                     <span class="animate-float delay-300">l</span>
                     <span class="animate-float delay-400">e</span>
                     <span class="animate-float delay-500">c</span>
                     <span class="animate-float delay-600">t</span>
                     <span class="animate-float delay-700">o</span>
                     <span class="animate-float delay-800">r</span>
                  </a>
                  <span class="text-indigo-200">Panel de Trámites</span>
               </h1>
            </div>
            <!-- User info y logout -->
            <div class="flex items-center space-x-4">
               <!-- Admin badge -->
               <div class="hidden sm:flex items-center bg-indigo-800 px-3 py-1 rounded-full text-xs font-medium">
                  <i class="fas fa-shield-alt mr-1"></i>
                  Administrador
               </div>
               <!-- User info - Solo muestra icono en mobile -->
               <div class="flex items-center">
                  <div class="w-8 h-8 rounded-full bg-indigo-400 flex items-center justify-center flex-shrink-0">
                     <i class="fas fa-user text-sm"></i>
                  </div>
                  <span
                     class="font-medium ml-2 hidden sm:inline truncate max-w-[100px] md:max-w-none">{{ Auth::user()->name }}</span>
               </div>
               <!-- Logout button -->
               <form action="{{ route('logout') }}" method="POST">
                  @csrf
                  <button type="submit"
                     class="flex items-center justify-center w-8 h-8 sm:w-auto sm:px-2 sm:h-auto text-indigo-100 hover:text-white transition-colors">
                     <i class="fas fa-sign-out-alt"></i>
                     <span class="hidden sm:inline ml-1">Salir</span>
                  </button>
               </form>
            </div>
         </div>
      </div>
   </header>
   <main class="container mx-auto p-6 justify-center">
      <!-- Título con estilo -->
      <h1 class="text-3xl font-bold text-indigo-700 mb-6">
         Documentación del usuario {{ $user->name ?? 'X' }}
      </h1>

      <div class="overflow-hidden rounded-lg border border-gray-200">
         <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                  <tr>
                     <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Documento</th>
                     <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 relative group cursor-help">
                        ENLACE <i class="fas fa-question-circle ml-1 text-gray-400 text-xs"></i>
                        <div
                           class="absolute left-1/2 transform -translate-x-1/2 mt-1 w-48 text-center bg-gray-700 text-white text-xs rounded-lg py-2 px-3 opacity-0 group-hover:opacity-100 transition-opacity duration-300 z-10 shadow-lg">
                           RECUERDA: Por seguridad, los enlaces solo funcionarán por 10 minutos desde la carga de esta página. Si pasados esos minutos necesitas volver a verlos, simplemente recarga la página
                        </div>
                     </th>
                  </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                  @foreach($documentosUsuario as $documento)
                  <tr class="hover:bg-gray-50">
                     <td class="px-4 py-3 whitespace-nowrap">
                        <div class="font-medium text-gray-900 capitalize">
                              {{ str_replace('_', ' ', $documento['document_id']) }}
                        </div>
                     </td>
                     <td class="px-4 py-3 whitespace-nowrap">
                        <a href="{{ $documento['temporaryUrl'] }}" target="_blank"
                           class="text-indigo-600 hover:text-indigo-900 font-medium flex items-center">
                              <i class="fas fa-external-link-alt mr-1 text-xs"></i> Abrir
                        </a>
                     </td>
                  </tr>
                  @endforeach
            </tbody>
         </table>
      </div>
   </main>
</body>

</html>
