<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lógica de cuestionarios | Collector</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%);
        }

        .gradient-header {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            box-shadow: 0 4px 20px rgba(99, 102, 241, 0.15);
        }

        .card-hover {
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px -5px rgba(99, 102, 241, 0.25);
        }

        .badge-pill {
            transition: all 0.2s ease;
        }

        .badge-pill:hover {
            transform: translateY(-2px);
        }

        .floating {
            animation: floating 3s ease-in-out infinite;
        }

        .floating-delay-1 {
            animation-delay: 0.2s;
        }

        .floating-delay-2 {
            animation-delay: 0.4s;
        }

        @keyframes floating {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-6px);
            }
        }

        .fade-in {
            animation: fadeIn 0.5s ease-in;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .glow {
            box-shadow: 0 0 15px rgba(139, 92, 246, 0.3);
        }

        .table-row {
            transition: all 0.2s ease;
        }

        .table-row:hover {
            background-color: rgba(249, 250, 251, 0.7);
        }

        .section-divider {
            border-top: 1px dashed rgba(209, 213, 219, 0.5);
        }

        .modal-overlay {
            background-color: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(8px);
        }

        .rotate-icon {
            transition: transform 0.3s ease;
        }

        .rotate-180 {
            transform: rotate(180deg);
        }

        .text-gradient {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .border-gradient {
            position: relative;
            border: 1px solid transparent;
            background-clip: padding-box;
            border-radius: 0.5rem;
        }

        .border-gradient::after {
            content: '';
            position: absolute;
            top: -1px;
            left: -1px;
            right: -1px;
            bottom: -1px;
            z-index: -1;
            border-radius: 0.5rem;
            background: linear-gradient(135deg, #e0e7ff 0%, #ede9fe 100%);
        }

        .animate-float {
            animation: float 1s ease-in-out infinite;
        }

        .delay-100 {
            animation-delay: 0.1s;
        }

        .delay-200 {
            animation-delay: 0.2s;
        }

        .delay-300 {
            animation-delay: 0.3s;
        }

        .delay-400 {
            animation-delay: 0.4s;
        }

        .delay-500 {
            animation-delay: 0.5s;
        }

        .delay-600 {
            animation-delay: 0.6s;
        }

        .delay-700 {
            animation-delay: 0.7s;
        }

        .delay-800 {
            animation-delay: 0.8s;
        }

        /* Añade hasta delay-800 */
        @keyframes float {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-3px);
            }
        }
    </style>
</head>

<body class="min-h-screen">
    <!-- Header -->
    @include('layouts.headerbackoffice')

    <main class="container mx-auto px-6 py-8 space-y-8">

    <!-- Título y subrayado degradado -->
    <section class="fade-in">
      <h2 class="text-3xl font-bold text-gray-800">{{ $questionnaire->name }}</h2>
      <p class="text-gray-600 mt-1">Administra las relaciones entre preguntas y respuestas</p>
      <div class="mt-4 w-24 h-1 bg-gradient-to-r from-[#54debd] to-indigo-500 rounded-full"></div>
    </section>

    <!-- Cards de transiciones -->
    <section class="grid gap-6">
      @foreach ($transiciones->groupBy('question.text') as $texto => $grupo)
        <div class="bg-white rounded-xl shadow-sm border-gradient card-hover overflow-hidden fade-in"
             style="animation-delay: {{ $loop->index * 0.05 }}s">
          <!-- Header de card -->
          <div class="px-6 py-4 bg-indigo-50 flex items-center justify-between">
            <div class="flex items-center space-x-4">
              <div class="w-10 h-10 bg-[#54debd] rounded-lg flex items-center justify-center">
                <i class="fas fa-question text-white"></i>
              </div>
              <h3 class="text-lg font-semibold text-gray-800">{{ $texto }}</h3>
            </div>
            <span class="inline-block bg-[#54debd] bg-opacity-10 text-[#54debd] text-sm font-medium px-3 py-1 rounded-full">
              {{ $grupo->count() }} transición{{ $grupo->count()>1?'es':'' }}
            </span>
          </div>
          <!-- Tabla -->
          <div class="overflow-x-auto">
            <table class="w-full text-left">
              <thead class="bg-white border-b">
                <tr>
                  <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase">Condición</th>
                  <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase">Próxima pregunta</th>
                </tr>
              </thead>
              <tbody>
                @foreach ($grupo as $t)
                  <tr class="table-row hover:bg-gray-50 transition">
                    <td class="px-6 py-4">
                      <div class="flex flex-wrap gap-2">
                        @php
                          $opts = json_decode($t->condition, true);
                        @endphp
                        @foreach($opts as $val)
                          <span class="inline-block bg-[#54debd] bg-opacity-20 text-[#54debd] text-sm px-3 py-1 rounded-full">
                            {{ $val }}
                          </span>
                        @endforeach
                      </div>
                    </td>
                    <td class="px-6 py-4 text-gray-700 flex items-center">
                      @if($t->nextQuestion)
                        <i class="fas fa-arrow-right text-[#54debd] mr-2"></i>
                        {{ $t->nextQuestion->text }}
                      @else
                        <span class="text-gray-400">—</span>
                      @endif
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
      @endforeach
    </section>

    <!-- Estado vacío -->
    @if ($transiciones->isEmpty())
      <section class="text-center py-16 fade-in">
        <div class="mx-auto w-20 h-20 bg-[#54debd] bg-opacity-20 rounded-full flex items-center justify-center mb-4">
          <i class="fas fa-question text-[#54debd] text-2xl"></i>
        </div>
        <h3 class="text-lg font-medium text-gray-700 mb-2">No hay transiciones</h3>
        <p class="text-gray-500 mb-4">Crea tu primera transición para empezar.</p>
        <button
          class="bg-[#54debd] hover:bg-[#43c5a9] text-white px-6 py-2 rounded-lg shadow transition">
          <i class="fas fa-plus-circle mr-2"></i> Crear transición
        </button>
      </section>
    @endif

  </main>

  <!-- Footer -->
  <footer class="bg-white border-t border-gray-200 py-6">
    <div class="max-w-7xl mx-auto px-6 flex justify-between items-center">
      <span class="text-gray-600">&copy; Collector</span>
      <a href="https://tutramitefacil.es/" class="text-[#54debd] hover:underline">TuTrámiteFácil</a>
    </div>
  </footer>
</body>

</html>
