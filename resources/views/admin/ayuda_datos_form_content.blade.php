{{-- Paso 1: Selección de Ayuda --}}
<div class="mb-8 bg-white rounded-2xl p-6 shadow-xl border border-[#54debd]/30">
    <div class="flex items-center mb-6">
        <div
            class="w-8 h-8 bg-[#54debd] rounded-full flex items-center justify-center text-white font-bold mr-3">
            1</div>
        <h3 class="text-xl font-bold text-[#54debd] tracking-wide">Selecciona una Ayuda</h3>
    </div>

    {{-- Buscador de Ayudas --}}
    <div class="mb-6">
        <div class="relative">
            <input type="text" x-model="busquedaAyuda" placeholder="Buscar ayuda por nombre..."
                class="w-full pl-12 pr-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#54debd] focus:border-[#54debd] text-lg">
            <i
                class="bx bx-search absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400 text-xl"></i>
        </div>
    </div>

    {{-- Lista de Ayudas --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 max-h-96 overflow-y-auto">
        <template x-for="(ayuda, id) in ayudasFiltradas" :key="id">
            <div class="relative">
                <input type="radio" :id="`ayuda_${id}`" :value="id"
                    x-model="ayuda_id" @change="onAyudaChange()" class="sr-only">
                <label :for="`ayuda_${id}`"
                    class="block p-4 border-2 rounded-xl cursor-pointer transition-all duration-300 hover:shadow-lg"
                    :class="ayuda_id == id ?
                        'border-[#54debd] bg-gradient-to-r from-[#54debd]/10 to-[#43c5a9]/10 shadow-lg scale-105' :
                        'border-gray-200 hover:border-[#54debd]/50 hover:bg-gray-50'">

                    <div class="flex items-center space-x-3">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center"
                                :class="ayuda_id == id ?
                                    'bg-[#54debd] text-white' :
                                    'bg-gray-100 text-gray-600'">
                                <i class="bx bx-help-circle text-lg"></i>
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h5 class="font-semibold text-gray-900 truncate" x-text="ayuda"></h5>
                            <p class="text-sm text-gray-500">ID: <span x-text="id"></span></p>
                        </div>
                        <div x-show="ayuda_id == id" class="flex-shrink-0">
                            <i class="bx bx-check-circle text-[#54debd] text-xl"></i>
                        </div>
                    </div>
                </label>
            </div>
        </template>
    </div>

    {{-- Botón de copiar datos - Solo visible cuando se selecciona una ayuda --}}
    <div x-show="ayuda_id" x-transition class="mt-6 text-center">
        <button type="button" @click="openCopiarModal()"
            class="px-8 py-3 bg-gradient-to-r from-blue-500 to-blue-600 text-white font-bold rounded-full shadow-xl hover:scale-105 hover:shadow-2xl transition-all duration-300 text-lg tracking-wider futuristic-btn">
            <i class="fas fa-copy mr-2"></i>
            Copiar todos los datos de esta ayuda a otras
        </button>
    </div>
</div>

{{-- Paso 3: Selección de Preguntas --}}
<div x-show="ayuda_id && tareaActiva && opcionTareaActiva" x-transition
    class="mb-8 bg-white rounded-2xl p-6 shadow-xl border border-[#54debd]/30">
    <div class="flex items-center mb-6">
        <div
            class="w-8 h-8 bg-[#54debd] rounded-full flex items-center justify-center text-white font-bold mr-3">
            3</div>
        <h3 class="text-xl font-bold text-[#54debd] tracking-wide">Añadir Preguntas</h3>
    </div>

    {{-- Resumen de configuración --}}
    <div
        class="mb-6 p-4 bg-gradient-to-r from-[#54debd]/10 to-[#43c5a9]/10 rounded-xl border border-[#54debd]/20">
        <div class="flex items-center space-x-4">
            <div class="flex-shrink-0">
                <div
                    class="w-12 h-12 bg-gradient-to-r from-[#54debd] to-[#43c5a9] rounded-full flex items-center justify-center">
                    <i class="bx bx-check text-white text-xl"></i>
                </div>
            </div>
            <div class="flex-1">
                <h4 class="font-semibold text-[#54debd] text-lg">Configuración Actual</h4>
                <div class="mt-1">
                    <p class="text-gray-800 font-medium" x-text="getAyudaNombre()"></p>
                    <p class="text-gray-600 text-sm"
                        x-text="getTareaNombre() + ' - ' + getOpcionTareaNombre()"></p>
                </div>
            </div>
            <div class="flex-shrink-0">
                <div class="text-right">
                    <div class="text-2xl font-bold text-[#54debd]"
                        x-text="getTotalDatosConfigurados()"></div>
                    <div class="text-xs text-gray-600">preguntas configuradas</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabs de tipos de dato --}}
    <div class="mb-6">
        <h4 class="text-lg font-semibold mb-4 text-gray-800">Selecciona el tipo de dato</h4>
        <div class="flex flex-wrap gap-2 mb-6">
            <template x-for="tipo in tiposDato" :key="tipo.value">
                <button type="button" @click="setTipoDatoActivo(tipo.value)"
                    :class="tipoDatoActivo === tipo.value ?
                        'bg-gradient-to-r from-[#54debd] to-[#43c5a9] text-white shadow-lg scale-105' :
                        'bg-white text-[#54debd] border border-[#54debd] hover:bg-[#e6fcf7]'"
                    class="transition-all duration-300 px-4 py-2 rounded-full font-semibold text-sm tracking-wide focus:outline-none futuristic-tab">
                    <span x-text="tipo.label"></span>
                    <span
                        class="ml-2 bg-white text-[#54debd] rounded-full px-2 py-1 text-xs font-bold"
                        x-text="getDatosCountByTipo(tipo.value)"></span>
                </button>
            </template>
        </div>

        {{-- Panel de selección de preguntas por tipo --}}
        <div class="bg-gray-50 rounded-xl p-6 border border-[#54debd]/20">
            <div class="flex justify-between items-center mb-4">
                <h5 class="text-lg font-semibold text-[#54debd]">
                    Añadir preguntas para: <span
                        x-text="tiposDato.find(t=>t.value===tipoDatoActivo)?.label"></span>
                </h5>
                <div class="flex items-center space-x-4 text-sm">
                    <div class="text-gray-600">
                        <svg class="inline h-4 w-4 text-yellow-500 mr-1" fill="currentColor"
                            viewBox="0 0 20 20">
                            <path
                                d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                        </svg>
                        Preguntas ya asociadas a esta ayuda
                    </div>
                    <div class="text-gray-600">
                        <span class="font-medium" x-text="getPreguntasDisponiblesCount()"></span>
                        disponibles para <span
                            x-text="tiposDato.find(t => t.value === tipoDatoActivo)?.label"></span>
                    </div>
                    <div class="text-green-600">
                        <span class="font-medium" x-text="getPreguntasAñadidasCount()"></span> ya
                        añadidas en <span
                            x-text="tiposDato.find(t => t.value === tipoDatoActivo)?.label"></span>
                    </div>
                </div>
            </div>

            <input type="text" placeholder="Buscar pregunta..." x-model="searchTerm"
                class="w-full mb-4 p-3 border border-[#54debd] rounded-lg bg-white text-[#23272f] focus:outline-none focus:ring-2 focus:ring-[#54debd]" />

            <div class="max-h-64 overflow-y-auto border border-[#54debd] rounded-lg p-4 bg-white">
                <template x-for="q in filteredQuestions()" :key="q.slug">
                    <label class="flex items-start space-x-3 mb-3 transition"
                        :class="isQuestionAddedInCurrentTask(q.slug) ?
                            'text-gray-500 cursor-not-allowed' :
                            'text-[#23272f] hover:text-[#54debd]'">

                        <input type="checkbox" :value="q.slug" x-model="selectedQuestions"
                            :disabled="isQuestionAddedInCurrentTask(q.slug)"
                            :checked="isQuestionAddedInCurrentTask(q.slug)"
                            class="h-5 w-5 text-[#54debd] focus:ring-[#54debd] rounded disabled:opacity-50" />

                        <div class="flex flex-col flex-1">
                            <div class="flex items-center space-x-2">
                                <span class="font-medium"
                                    :class="isQuestionAddedInCurrentTask(q.slug) ? 'line-through' : ''"
                                    x-text="q.text"></span>

                                <!-- Indicador de pregunta ya asociada a la ayuda -->
                                <template x-if="isQuestionAssociated(q.slug)">
                                    <svg class="h-4 w-4 text-yellow-500" fill="currentColor"
                                        viewBox="0 0 20 20">
                                        <path
                                            d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                    </svg>
                                </template>

                                <!-- Indicador de pregunta ya añadida en este tipo de dato -->
                                <template x-if="isQuestionAddedInCurrentTask(q.slug)">
                                    <div class="flex items-center space-x-1">
                                        <svg class="h-4 w-4 text-green-500" fill="currentColor"
                                            viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                clip-rule="evenodd" />
                                        </svg>
                                        <span class="text-xs text-green-600 font-medium"
                                            x-text="'Ya añadida en ' + tiposDato.find(t => t.value === tipoDatoActivo)?.label"></span>
                                    </div>
                                </template>
                            </div>
                            <span class="text-xs text-gray-400" x-text="'('+q.slug+')'"></span>
                        </div>
                    </label>
                </template>
            </div>

            <div class="mt-4 flex justify-end">
                <button type="button" @click="addSelectedForTipo()"
                    class="px-6 py-2 bg-gradient-to-r from-[#54debd] to-[#43c5a9] text-white rounded-full hover:scale-105 hover:shadow-xl transition-all duration-300">
                    Añadir preguntas seleccionadas
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Paso 4: Gestión de Datos --}}
<div x-show="ayuda_id && tareaActiva && opcionTareaActiva" x-transition
    class="mb-8 bg-white rounded-2xl p-6 shadow-xl border border-[#54debd]/30">
    <div class="flex items-center mb-6">
        <div
            class="w-8 h-8 bg-[#43c5a9] rounded-full flex items-center justify-center text-white font-bold mr-3">
            4</div>
        <h3 class="text-xl font-bold text-[#43c5a9] tracking-wide">Gestionar Datos Configurados
        </h3>
    </div>

    {{-- Mensajes de error --}}
    <div x-show="errores.length > 0" class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
        <ul class="text-red-600 text-sm">
            <template x-for="error in errores" :key="error">
                <li x-text="error"></li>
            </template>
        </ul>
    </div>

    {{-- Datos asociados (drag & drop) --}}
    <div
        class="mt-8 bg-white rounded-2xl p-8 shadow-2xl border border-[#54debd]/30 relative overflow-hidden">
        <h3 class="text-xl font-bold mb-6 text-[#54debd] tracking-wide">Datos asociados a <span
                x-text="getTareaNombre()"></span> - <span x-text="getOpcionTareaNombre()"></span>
        </h3>

        {{-- Tabs de tipos de dato para mostrar datos --}}
        <div class="mb-6 flex flex-wrap gap-2">
            <template x-for="tipo in tiposDato" :key="tipo.value">
                <button type="button" @click="setTipoDatoMostrar(tipo.value)"
                    :class="tipoDatoMostrar === tipo.value ?
                        'bg-gradient-to-r from-[#54debd] to-[#43c5a9] text-white shadow-lg scale-105' :
                        'bg-white text-[#54debd] border border-[#54debd] hover:bg-[#e6fcf7]'"
                    class="transition-all duration-300 px-4 py-2 rounded-full font-semibold text-sm tracking-wide focus:outline-none futuristic-tab">
                    <span x-text="tipo.label"></span>
                    <span
                        class="ml-2 bg-white text-[#54debd] rounded-full px-2 py-1 text-xs font-bold"
                        x-text="getDatosCountByTipo(tipo.value)"></span>
                </button>
            </template>
        </div>

        <div x-ref="list" x-init="initSortable()" class="space-y-6 min-h-[80px]">
            <template x-for="(d, i) in datosTareaActivaFiltrados"
                :key="`${d.question_slug}-${d.tipo_dato}-${i}`">
                <div
                    class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end bg-white p-6 rounded-xl draggable-item cursor-move futuristic-item border border-[#54debd]/20 transition-all duration-300">
                    {{-- Pregunta --}}
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-[#54debd]">Pregunta</label>
                        <p class="mt-1 text-[#23272f]" x-text="d.question_text"></p>
                    </div>
                    {{-- Slug --}}
                    <div>
                        <label class="block text-sm font-medium text-[#54debd]">Slug</label>
                        <input type="text" :name="`datos[${i}][question_slug]`"
                            x-model="d.question_slug" readonly
                            class="mt-1 w-full p-2 border border-[#54debd]/40 rounded bg-white text-[#23272f]" />
                    </div>
                    {{-- Tipo de dato --}}
                    <div>
                        <label class="block text-sm font-medium text-[#54debd]">Tipo de
                            dato</label>
                        <input type="hidden" :name="`datos[${i}][tipo_dato]`"
                            :value="d.tipo_dato">
                        <input type="text" x-model="d.tipo_dato" readonly
                            class="mt-1 w-full p-2 border border-[#54debd]/40 rounded bg-white text-[#23272f] font-semibold" />
                    </div>
                    {{-- Eliminar --}}
                    <div class="text-right">
                        <button type="button" @click="removeDato(i)"
                            class="px-4 py-2 bg-gradient-to-r from-red-500 to-pink-500 text-white rounded-full hover:scale-110 hover:shadow-xl transition-all duration-300">Eliminar</button>
                    </div>

                    <!-- Condiciones -->
                    <div class="col-span-4 mt-4">
                        <label
                            class="block text-sm font-medium text-[#54debd] mb-2">Condiciones</label>

                        <template x-for="(cond, ci) in d.condiciones || []"
                            :key="ci">
                            <div
                                class="flex flex-wrap items-end gap-2 mb-2 bg-gray-50 p-3 rounded-lg border border-[#54debd]/20">
                                <!-- Selección de pregunta -->
                                <div class="max-w-xs" style="min-width: 180px;">
                                    <div class="relative">
                                        <input type="text"
                                            :placeholder="cond.question_slug ? 'Buscar pregunta...' :
                                                'Buscar pregunta...'"
                                            x-model="cond.searchTerm"
                                            @focus="cond.showDropdown = true; if (!cond.searchTerm && cond.question_slug) cond.searchTerm = questions.find(q => q.slug === cond.question_slug)?.text || ''"
                                            @blur="setTimeout(() => cond.showDropdown = false, 200)"
                                            class="p-2 border rounded w-full truncate" />
                                        <input type="hidden"
                                            :name="`datos[${i}][condiciones][${ci}][question_slug]`"
                                            x-model="cond.question_slug">
                                        <input type="text" x-model="cond.question_slug"
                                            readonly
                                            class="p-2 border rounded w-full truncate bg-gray-100" />

                                        <div x-show="cond.showDropdown && cond.searchTerm"
                                            x-transition
                                            class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-y-auto">
                                            <template
                                                x-for="q in getFilteredQuestionsForCondition(cond.searchTerm)"
                                                :key="q.slug">
                                                <div @click="selectQuestionForCondition(i, ci, q.slug)"
                                                    class="px-3 py-2 hover:bg-gray-100 cursor-pointer border-b border-gray-100 last:border-b-0">
                                                    <div class="font-medium" x-text="q.text">
                                                    </div>
                                                    <div class="text-xs text-gray-500"
                                                        x-text="q.slug"></div>
                                                </div>
                                            </template>
                                            <template
                                                x-if="getFilteredQuestionsForCondition(cond.searchTerm).length === 0">
                                                <div class="px-3 py-2 text-gray-500 text-sm">No se
                                                    encontraron preguntas</div>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                                <!-- Operador -->
                                <div>
                                    <input type="hidden"
                                        :name="`datos[${i}][condiciones][${ci}][operador]`"
                                        x-model="cond.operador">
                                    <select x-model="cond.operador" class="p-2 border rounded">
                                        <option value="=">=</option>
                                        <option value="!=">≠</option>
                                        <option value=">">&gt;</option>
                                        <option value="<">&lt;</option>
                                        <option value=">=">≥</option>
                                        <option value="<=">≤</option>
                                        <option value="in">En</option>
                                        <option value="not in">No en</option>
                                    </select>
                                </div>
                                <!-- Valor esperado -->
                                <div>
                                    <template
                                        x-if="cond.question_slug && getCondTipo(cond.question_slug)==='boolean'">
                                        <input type="hidden"
                                            :name="`datos[${i}][condiciones][${ci}][valor]`"
                                            x-model="cond.valor">
                                        <select x-model="cond.valor" class="p-2 border rounded">
                                            <option value="1">Sí</option>
                                            <option value="0">No</option>
                                        </select>
                                    </template>
                                    <template
                                        x-if="cond.question_slug && getCondTipo(cond.question_slug)==='select'">
                                        <input type="hidden"
                                            :name="`datos[${i}][condiciones][${ci}][valor]`"
                                            x-model="cond.valor">
                                        <select x-model="cond.valor" class="p-2 border rounded">
                                            <template
                                                x-for="(opt, oi) in getCondOptions(cond.question_slug)"
                                                :key="oi">
                                                <option :value="oi" x-text="opt">
                                                </option>
                                            </template>
                                        </select>
                                    </template>
                                    <template
                                        x-if="cond.question_slug && getCondTipo(cond.question_slug)==='multiple'">
                                        <div class="flex flex-wrap gap-2">
                                            <template
                                                x-for="(opt, oi) in getCondOptions(cond.question_slug)"
                                                :key="oi">
                                                <label class="inline-flex items-center">
                                                    <input type="checkbox" :value="oi"
                                                        x-model="cond.valor"
                                                        :name="`datos[${i}][condiciones][${ci}][valor][]`"
                                                        class="mr-1" />
                                                    <span x-text="opt"></span>
                                                </label>
                                            </template>
                                        </div>
                                    </template>
                                    <template
                                        x-if="cond.question_slug && ['text','number','date'].includes(getCondTipo(cond.question_slug))">
                                        <input
                                            :type="getCondTipo(cond.question_slug) === 'number' ?
                                                'number' : 'text'"
                                            :name="`datos[${i}][condiciones][${ci}][valor]`"
                                            x-model="cond.valor" class="p-2 border rounded" />
                                    </template>
                                </div>
                                <!-- Eliminar condición -->
                                <button type="button" @click="removeCondicion(i, ci)"
                                    class="ml-2 px-2 py-1 bg-red-200 text-red-700 rounded">Eliminar</button>
                            </div>
                        </template>
                        <button type="button" @click="addCondicion(i)"
                            class="mt-2 px-3 py-1 bg-[#54debd] text-white rounded hover:bg-[#43c5a9]">Añadir
                            condición</button>
                    </div>

                </div>
            </template>
        </div>
        <template x-if="datosTareaActivaFiltrados.length === 0">
            <div class="text-center text-gray-400 py-8 text-lg">No hay datos.</div>
        </template>
        @error('datos')
            <p class="text-red-500 text-sm">{{ $message }}</p>
        @enderror
        @error('datos.*.question_slug')
            <p class="text-red-500 text-sm">{{ $message }}</p>
        @enderror
        @error('datos.*.tipo_dato')
            <p class="text-red-500 text-sm">{{ $message }}</p>
        @enderror
    </div>

    {{-- Botones de acción --}}
    <div class="mt-8 flex justify-between items-center">
        <template x-if="!puedeGuardar">
            <div class="text-red-500 text-sm">Debes completar todos los campos obligatorios (tipo
                de dato) para cada pregunta antes de guardar.</div>
        </template>

        <div class="flex space-x-4">
            <!-- Botón de guardar -->
            <button type="button" @click="guardarDatos()" :disabled="!puedeGuardar || guardando"
                class="px-10 py-4 bg-gradient-to-r from-[#54debd] to-[#43c5a9] text-white font-bold rounded-full shadow-xl hover:scale-105 hover:shadow-2xl transition-all duration-300 text-lg tracking-wider futuristic-btn disabled:opacity-50 disabled:cursor-not-allowed">
                <span x-show="!guardando">Guardar configuración</span>
                <span x-show="guardando" class="flex items-center">
                    <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white"
                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10"
                            stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                    </svg>
                    Guardando...
                </span>
            </button>
        </div>
    </div>
</div>

<!-- Modal de Copiar Datos de Ayuda -->
<div id="copiar-datos-modal"
    class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
    style="display: none;">
    <div class="bg-white rounded-xl p-6 w-full max-w-4xl mx-4 max-h-[90vh] overflow-y-auto">
        <h3 class="text-xl font-bold mb-4">Copiar Todos los Datos de Ayuda</h3>

        <form id="copiar-datos-form" onsubmit="window.submitCopiarDatos(event)">
            <!-- Ayuda Origen (solo mostrar, no seleccionar) -->
            <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                <h4 class="text-sm font-medium text-blue-800 mb-2">Ayuda Origen</h4>
                <div class="flex items-center justify-between">
                    <span class="text-blue-900 font-medium" id="ayuda-origen-nombre-datos"></span>
                    <span class="text-blue-600 text-sm" id="ayuda-origen-id-datos"></span>
                </div>
            </div>

            <!-- Ayudas Destino -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Seleccionar Ayudas
                    Destino</label>
                <div class="relative">
                    <input type="text" id="ayudas-destino-search-datos"
                        placeholder="Buscar ayudas destino..."
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                        onkeyup="filtrarAyudasDestinoDatos()"
                        onfocus="mostrarListaAyudasDestinoDatos()"
                        onblur="ocultarListaAyudasDestinoDatos()">
                    <div id="lista-ayudas-destino-datos"
                        class="absolute z-10 w-full bg-white border border-gray-300 rounded-lg shadow-lg mt-1 max-h-60 overflow-y-auto hidden">
                        <!-- Se cargará dinámicamente -->
                    </div>
                </div>
                <div id="ayudas-destino-seleccionadas-datos" class="mt-2 space-y-1">
                    <!-- Se mostrarán las ayudas seleccionadas -->
                </div>
            </div>

            <!-- Opciones de copia -->
            <div class="mb-6 p-4 bg-gray-50 border border-gray-200 rounded-lg">
                <h4 class="text-sm font-medium text-gray-700 mb-3">Opciones de copia</h4>
                <div class="space-y-2">
                    <label class="flex items-center space-x-2">
                        <input type="checkbox" id="sobrescribir-datos" name="sobrescribir"
                            class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <span class="text-sm text-gray-700">Sobrescribir datos existentes</span>
                    </label>
                    <p class="text-xs text-gray-500 ml-6">
                        Si está marcado, los datos existentes en las ayudas destino serán
                        reemplazados.
                        Si no, se saltarán los datos que ya existen.
                    </p>
                </div>
            </div>

            <!-- Vista previa de los datos origen -->
            <div id="vista-previa-datos-origen" class="mb-6">
                <h4 class="text-sm font-medium text-gray-700 mb-3">Datos que se copiarán</h4>
                <div id="contenido-vista-previa-datos"
                    class="border border-gray-300 rounded-lg p-4 max-h-40 overflow-y-auto bg-gray-50">
                    <!-- Se cargará dinámicamente -->
                </div>
            </div>

            <div class="flex justify-end space-x-3">
                <button type="button" onclick="window.closeCopiarDatosModal()"
                    class="px-4 py-2 text-gray-600 hover:text-gray-800 transition-colors">
                    Cancelar
                </button>
                <button type="submit" id="btn-copiar-datos-submit"
                    class="px-6 py-2 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-lg hover:from-blue-600 hover:to-blue-700 transition-all duration-200"
                    disabled>
                    <i class="fas fa-copy mr-2"></i>
                    Copiar todos los datos
                </button>
            </div>
        </form>
    </div>
</div>

<style>
    .futuristic-tab {
        box-shadow: 0 2px 16px 0 #54debd33;
        border: none;
        letter-spacing: 0.05em;
    }

    .futuristic-btn {
        box-shadow: 0 2px 16px 0 #54debd33;
        border: none;
        letter-spacing: 0.05em;
    }

    .futuristic-item {
        box-shadow: 0 2px 24px 0 #54debd22;
        border: 1.5px solid transparent;
    }

    .futuristic-item:hover {
        border-color: #54debd;
        background: #fff;
    }
</style>
