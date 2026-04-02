<!DOCTYPE html>
<html lang="es" class="h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard · Backoffice</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet" />
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.css"
        rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js">
    </script>
    <style>
        [x-cloak] {
            display: none !important;
        }

        .smooth-transition {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
    </style>
</head>

<body x-data="{ drawerOpen: false }" class="h-full bg-gray-100 relative pt-0">

    @include('layouts.headerbackoffice')

    {{-- Botón fijo a la izquierda para abrir/cerrar --}}
    <button @click="drawerOpen = !drawerOpen"
        class="fixed top-1/2 left-0 transform -translate-y-1/2 bg-white p-2 rounded-r shadow-lg z-50 focus:outline-none">
        <i :class="drawerOpen ? 'bx bx-chevron-left' : 'bx bx-chevron-right'" class="text-2xl"></i>
    </button>

    {{-- Drawer lateral --}}
    <x-sidebar-admin />

    <div class="max-w-6xl mx-auto p-6 transition-all duration-300" :class="{ 'ml-64': drawerOpen }">
        <div class="space-y-6">
            <div class="bg-white rounded-2xl shadow-lg overflow-hidden smooth-transition">
                <div class="p-6 space-y-6">
                    <div class="bg-blue-50/50 border-l-4 border-blue-500 p-5 rounded-xl">
                        <div class="flex items-start">
                            <div class="flex-shrink-0 pt-1">
                                <i class="bx bx-info-circle text-2xl text-blue-600"></i>
                            </div>
                            <div class="ml-4">
                                <h2 class="text-xl font-bold text-blue-800 mb-2">Requisitos
                                    obligatorios</h2>
                                <p class="text-gray-700">Debes marcar como mínimo el email del
                                    usuario (necesario para crear o actualizar un usuario) y el CSV
                                    debe tener grupos de usuarios que compartan la misma
                                    contratación y estado (que se marcan al final de la página, no
                                    lo añadas como columna).</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-yellow-50/50 border-l-4 border-yellow-500 p-5 rounded-xl">
                        <div class="flex items-start">
                            <div class="flex-shrink-0 pt-1">
                                <i class="bx bx-error text-2xl text-yellow-600"></i>
                            </div>
                            <div class="ml-4">
                                <h2 class="text-xl font-bold text-yellow-800 mb-2">Limitaciones
                                    importantes</h2>
                                <ul class="list-disc pl-5 space-y-2 text-gray-700">
                                    <li>Tanto los convivientes como los arrendadores se deberán
                                        insertar manualmente luego, no es posible hacerlo en esta
                                        herramienta</li>
                                    <li>No puedes asignar estado civil ni grupo vulnerable a los
                                        usuarios</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="bg-purple-50/50 border-l-4 border-purple-500 p-5 rounded-xl">
                        <div class="flex items-start">
                            <div class="flex-shrink-0 pt-1">
                                <i class="bx bx-cog text-2xl text-purple-600"></i>
                            </div>
                            <div class="ml-4">
                                <h2 class="text-xl font-bold text-purple-800 mb-2">Configuración
                                    automática</h2>
                                <ul class="list-disc pl-5 space-y-2 text-gray-700">
                                    <li>NO marques nada relacionado con la CCAA. El sistema cargará
                                        automáticamente la CCAA en base a la ayuda contratada.</li>
                                    <li>No marques campos de fechas de creación o documentos - el
                                        sistema asignará automáticamente la fecha/hora actual.</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="bg-green-50/50 border-l-4 border-green-500 p-5 rounded-xl">
                        <div class="flex items-start">
                            <div class="flex-shrink-0 pt-1">
                                <i class="bx bx-data text-2xl text-green-600"></i>
                            </div>
                            <div class="ml-4">
                                <h2 class="text-xl font-bold text-green-800 mb-2">Manejo de datos
                                </h2>
                                <ul class="list-disc pl-5 space-y-2 text-gray-700">
                                    <li>Si existen dos columnas en el CSV para la misma pregunta,
                                        deberás preprocesar el CSV para unificarlas</li>
                                    <li>No vincules columnas con preguntas o documentos. La ayuda,
                                        estado, etc. se configuran en los selectores finales</li>
                                    <li>Las NOTAS de Airtable se añaden seleccionando la opción
                                        "Notas" específicamente</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div x-data="migracionCsv()" class="bg-white rounded shadow p-6 relative">
                <div x-show="importando || importadoOk"
                    class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-50 transition-all duration-500">
                    <div class="flex flex-col items-center">
                        <template x-if="importando">
                            <div class="flex flex-col items-center">
                                <svg class="animate-spin h-10 w-10 text-blue-600 mb-4"
                                    xmlns="http://www.w3.org/2000/svg" fill="none"
                                    viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                        stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8v8z"></path>
                                </svg>
                                <span class="text-white text-lg font-semibold mt-2"
                                    x-text="'Importando fila ' + filaActual + ' de ' + totalFilas + ' (' + progreso + '%)' "></span>
                                <span class="text-white text-sm mt-1" x-show="importando"
                                    x-text="'Tiempo estimado restante: ' + tiempoEstimado + 's'"></span>
                                <span class="text-white text-xl mt-1" x-show="importando"
                                    x-text="'IMPORTANTE: No cierres esta pestaña hasta que el proceso termine'"></span>
                            </div>
                        </template>
                        <template x-if="importadoOk">
                            <svg class="h-10 w-10 text-green-500 mb-4" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M5 13l4 4L19 7" />
                            </svg>
                        </template>
                        <span class="text-white text-lg font-semibold"
                            x-text="importando ? '' : (importadoOk ? '¡Importación completada!' : '')"></span>
                    </div>
                </div>
                <h2 class="text-2xl font-bold mb-4 flex items-center"><i
                        class="bx bx-upload mr-2"></i> Importar datos
                    por CSV</h2>
                <form x-ref="form" @submit.prevent="subirCsv" enctype="multipart/form-data"
                    class="space-y-4">
                    <div>
                        <label class="block font-semibold mb-1">Archivo CSV</label>
                        <input type="file" name="csv_file" x-ref="csv_file" accept=".csv,.txt"
                            class="border rounded px-3 py-2 w-full" required>
                    </div>
                    <button type="submit"
                        class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 flex items-center"><i
                            class="bx bx-search-alt-2 mr-2"></i>Previsualizar CSV</button>
                </form>
                <template x-if="loadingCsv">
                    <div class="flex items-center justify-center py-8">
                        <svg class="animate-spin h-8 w-8 text-blue-600 mr-2"
                            xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10"
                                stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z">
                            </path>
                        </svg>
                        <span class="text-blue-600 font-semibold">Cargando CSV...</span>
                    </div>
                </template>

                <template x-if="header.length">
                    <div class="mt-8">
                        <h3 class="text-lg font-semibold mb-2">Mapeo de columnas</h3>
                        <form @submit.prevent="importarCsv" class="space-y-4">
                            <div class="overflow-x-auto">
                                <table class="min-w-full border mb-4">
                                    <thead>
                                        <tr>
                                            <template x-for="(col, idx) in header"
                                                :key="idx">
                                                <th class="border px-2 py-1 bg-gray-100"
                                                    x-text="col"></th>
                                            </template>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template x-for="row in rows" :key="row">
                                            <tr>
                                                <template x-for="cell in row"
                                                    :key="cell">
                                                    <td class="border px-2 py-1" x-text="cell">
                                                    </td>
                                                </template>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <template x-for="(col, idx) in header" :key="'map-' + idx">
                                    <div class="border rounded p-2 mb-2">
                                        <label class="block font-semibold mb-1"
                                            x-text="col"></label>
                                        <select x-model="mapping[idx].field"
                                            class="border rounded px-2 py-1 w-full">
                                            <option value="">No importar</option>
                                            <option value="user.email">Email del usuario</option>
                                            <option value="answer.answer">Selector de preguntas
                                            </option>
                                            <option value="user_documents.file_path">Selector de
                                                documentos</option>
                                            <option value="notas_contrataciones.nota">Notas
                                            </option>
                                        </select>
                                        <template x-if="mapping[idx].field === 'answer.answer'">
                                            <div
                                                x-effect="
                        setTimeout(() => {
                          const sel = document.getElementById('question-select-'+idx);
                          if (sel && !sel.tomselect) {
                            new TomSelect(sel, {create: false, allowEmptyOption: true, maxOptions: 1000});
                          }
                        }, 0)">
                                                <select :id="'question-select-' + idx"
                                                    x-model="mapping[idx].question_id"
                                                    class="border rounded px-2 py-1 w-full mt-1">
                                                    <option value="">Selecciona pregunta
                                                    </option>
                                                    <template x-for="q in questions"
                                                        :key="q.id">
                                                        <option :value="q.id"
                                                            x-text="q.text"></option>
                                                    </template>
                                                </select>
                                            </div>
                                        </template>
                                        <template
                                            x-if="mapping[idx].field === 'user_documents.file_path'">
                                            <div
                                                x-effect="
                        setTimeout(() => {
                          const sel = document.getElementById('document-select-'+idx);
                          if (sel && !sel.tomselect) {
                            new TomSelect(sel, {create: false, allowEmptyOption: true, maxOptions: 1000});
                          }
                        }, 0)">
                                                <select :id="'document-select-' + idx"
                                                    x-model="mapping[idx].document_id"
                                                    class="border rounded px-2 py-1 w-full mt-1">
                                                    <option value="">Selecciona documento
                                                    </option>
                                                    <template x-for="d in documents"
                                                        :key="d.id">
                                                        <option :value="d.id"
                                                            x-text="d.name"></option>
                                                    </template>
                                                </select>
                                            </div>
                                        </template>
                                    </div>
                                </template>
                            </div>

                            <input type="hidden" x-model="csvPath">
                            <input type="hidden" x-model="fileContent">
                            <button type="submit" @click="importando = true"
                                class="mt-4 bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 flex items-center">
                                <i class="bx bx-import mr-2"></i>Importar datos
                            </button>
                        </form>
                    </div>
                </template>

                <template x-if="resultado">
                    <div class="mt-6">
                        <div class="p-4 rounded bg-green-100 text-green-800 mb-2 flex items-center"
                            x-show="resultado.inserted">
                            <span
                                x-text="resultado.inserted + ' registros importados correctamente.'"></span>
                        </div>
                        <template x-if="resultado.errors && resultado.errors.length">
                            <div
                                class="p-4 rounded bg-red-100 text-red-800 max-h-64 overflow-y-auto">
                                <div class="font-bold mb-2">Errores:</div>
                                <ul class="list-disc ml-6">
                                    <template x-for="err in resultado.errors"
                                        :key="err">
                                        <li x-text="err"></li>
                                    </template>
                                </ul>
                            </div>
                        </template>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <script>
        function migracionCsv() {
            return {
                modelo: '',
                header: [],
                rows: [],
                camposModelo: [],
                mapping: [],
                csvPath: '',
                fileContent: '',
                resultado: null,
                questions: [],
                loadingCsv: false,
                documents: [],
                importando: false,
                importadoOk: false,
                ayudas: [],
                ayudaDetectada: null,
                ayudasDetectadas: [],
                estadoDetectado: null,
                faseDetectada: null,
                estadosContratacion: [],
                fasesDocumentales: [],
                camposUser: [
                    'name', 'email', 'password', 'is_admin', 'ref_code', 'id_unidad_familiar',
                    'brevo_id', 'ref_by'
                ],
                camposContratacion: [
                    'user_id', 'stripe_payment_method', 'product_id', 'card_last4', 'card_brand',
                    'card_exp_month',
                    'card_exp_year', 'card_funding', 'estado', 'fecha_contratacion', 'ayuda_id',
                    'fase',
                    'monto_comision', 'monto_total_ayuda'
                ],
                camposAnswer: [
                    'answer', 'user_id', 'question_id', 'conviviente_id'
                ],
                loteTamano: 10,
                progreso: 0,
                totalFilas: 0,
                erroresAcumulados: [],
                registrosImportados: 0,
                rowsAll: [],
                csvFileName: '',
                init() {
                    fetch('{{ route('admin.migraciones.estadosContratacionList') }}')
                        .then(r => r.json())
                        .then(estados => {
                            this.estadosContratacion = estados;
                        });
                    fetch('{{ route('admin.migraciones.fasesContratacionList') }}')
                        .then(r => r.json())
                        .then(fases => {
                            this.fasesDocumentales = fases;
                        });
                },
                subirCsv() {
                    this.resultado = null;
                    this.loadingCsv = true;
                    fetch('{{ route('admin.migraciones.questionsList') }}')
                        .then(r => r.json())
                        .then(qs => {
                            this.questions = qs;
                        });
                    fetch('{{ route('admin.migraciones.documentsList') }}')
                        .then(r => r.json())
                        .then(ds => {
                            this.documents = ds;
                        });
                    fetch('{{ route('admin.migraciones.ayudasList') }}')
                        .then(r => r.json())
                        .then(as => {
                            this.ayudas = as;
                        });
                    fetch('{{ route('admin.migraciones.estadosContratacionList') }}')
                        .then(r => r.json())
                        .then(estados => {
                            this.estadosContratacion = estados;
                        });
                    fetch('{{ route('admin.migraciones.fasesContratacionList') }}')
                        .then(r => r.json())
                        .then(fases => {
                            this.fasesDocumentales = fases;
                        });
                    const formData = new FormData(this.$refs.form);
                    const fileInput = this.$refs.csv_file;
                    let fileName = '';
                    if (fileInput && fileInput.files && fileInput.files.length > 0) {
                        fileName = fileInput.files[0].name;
                    }
                    this.csvFileName = fileName;
                    fetch('{{ route('admin.migraciones.upload') }}', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            },
                            body: formData
                        })
                        .then(async r => {
                            const data = await r.json();
                            this.loadingCsv = false;
                            if (data.error) {
                                alert('Error al subir el CSV: ' + data.error);
                                return;
                            }
                            this.header = data.header;
                            this.rows = data.rows;
                            this.fileContent = data.file_content;
                            this.mapping = Array(this.header.length).fill('').map(() => ({
                                field: '',
                                question_id: ''
                            }));
                            this.rowsAll = data.rows_all;
                            this.$nextTick();
                            this.autoMapColumns();
                        })
                        .catch(e => {
                            this.loadingCsv = false;
                            alert('Error al subir el CSV: ' + e.message);
                        });
                },
                autoMapColumns() {
                    function normalize(str) {
                        if (!str) return '';
                        return str
                            .replace(/^[^a-zA-Z0-9]+/, '')
                            .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
                            .toLowerCase().trim();
                    }
                    const preguntas = (this.questions || []).map(q => ({
                        id: q.id,
                        text: q.text,
                        norm: normalize(q.text)
                    }));
                    const documentos = (this.documents || []).map(d => ({
                        id: d.id,
                        name: d.name,
                        norm: normalize(d.name)
                    }));
                    this.detectarConfiguracionAutomatica();
                    (this.header || []).forEach((col, idx) => {
                        const colNorm = normalize(col);
                        const pregunta = preguntas.find(q => q.norm === colNorm);
                        if (pregunta) {
                            this.mapping[idx].field = 'answer.answer';
                            this.mapping[idx].question_id = pregunta.id;
                            this.$nextTick(() => {
                                const sel = document.getElementById('question-select-' +
                                    idx);
                                if (sel) {
                                    if (sel.tomselect) {
                                        sel.tomselect.setValue(pregunta.id, true);
                                    } else {
                                        sel.value = pregunta.id;
                                        sel.dispatchEvent(new Event('change', {
                                            bubbles: true
                                        }));
                                    }
                                }
                            });
                            return;
                        }
                        const documento = documentos.find(d => d.norm === colNorm);
                        if (documento) {
                            this.mapping[idx].field = 'user_documents.file_path';
                            this.mapping[idx].document_id = documento.id;
                            this.$nextTick(() => {
                                const sel = document.getElementById('document-select-' +
                                    idx);
                                if (sel) {
                                    if (sel.tomselect) {
                                        sel.tomselect.setValue(documento.id, true);
                                    } else {
                                        sel.value = documento.id;
                                        sel.dispatchEvent(new Event('change', {
                                            bubbles: true
                                        }));
                                    }
                                }
                            });
                            return;
                        }
                        this.mapping[idx].field = '';
                        this.mapping[idx].question_id = '';
                        this.mapping[idx].document_id = '';
                    });
                },

                detectarConfiguracionAutomatica() {
                    let columnaAyuda = -1;
                    let columnaEstado = -1;
                    let columnaFase = -1;

                    this.header.forEach((col, idx) => {
                        if (col === 'ºGENÉRICO-Tipo de ayuda Alquiler') {
                            columnaAyuda = idx;
                        }
                        if (col === 'ºGENÉRICO-Estado proceso') {
                            columnaEstado = idx;
                        }
                        if (col === 'ºDoc-Fase') {
                            columnaFase = idx;
                        }
                    });

                    console.log('Columnas encontradas:', {
                        ayuda: columnaAyuda,
                        estado: columnaEstado,
                        fase: columnaFase
                    });

                    if (columnaAyuda >= 0 && this.rows.length > 0) {
                        const ayudaValue = this.rows[0][columnaAyuda];
                        console.log('Valor de ayuda encontrado:', ayudaValue);
                        if (ayudaValue) {
                            const ayudaEncontrada = this.ayudas.find(a =>
                                ayudaValue.toLowerCase().includes(a.nombre_ayuda.toLowerCase())
                            );
                            if (ayudaEncontrada) {
                                this.ayudaDetectada = ayudaEncontrada;
                                this.ayudasDetectadas = [ayudaEncontrada];
                            }
                        }
                    }

                    if (columnaEstado >= 0 && this.rows.length > 0) {
                        const estadoValue = this.rows[0][columnaEstado];
                        console.log('Valor de estado encontrado:', estadoValue);
                        if (estadoValue && this.estadosContratacion.includes(estadoValue)) {
                            this.estadoDetectado = estadoValue;
                        }
                    }

                    if (columnaFase >= 0 && this.rows.length > 0) {
                        const faseValue = this.rows[0][columnaFase];
                        console.log('Valor de fase encontrado:', faseValue);
                        if (faseValue && this.fasesDocumentales.includes(faseValue)) {
                            this.faseDetectada = faseValue;
                        }
                    }

                    if (!this.ayudaDetectada) {
                        this.ayudasDetectadas = [];
                        for (let row of this.rows) {
                            for (let i = 0; i < row.length; i++) {
                                const cellValue = row[i];
                                if (cellValue && typeof cellValue === 'string') {
                                    const ayudaEncontrada = this.ayudas.find(a =>
                                        cellValue.toLowerCase().includes(a.nombre_ayuda
                                            .toLowerCase())
                                    );
                                    if (ayudaEncontrada && !this.ayudasDetectadas.find(a => a.id ===
                                            ayudaEncontrada.id)) {
                                        this.ayudasDetectadas.push(ayudaEncontrada);
                                    }
                                }
                            }
                        }

                        if (this.ayudasDetectadas.length === 1) {
                            this.ayudaDetectada = this.ayudasDetectadas[0];
                        } else if (this.ayudasDetectadas.length > 1) {
                            this.ayudaDetectada = this.ayudasDetectadas[0];
                        }
                    }
                },
                getCamposModelo() {
                    return [];
                },
                importarCsv() {
                    this.importando = true;
                    this.importadoOk = false;
                    document.body.classList.add('overflow-hidden');
                    this.progreso = 0;
                    this.erroresAcumulados = [];
                    this.totalFilas = this.rowsAll.length;
                    this.registrosImportados = 0;
                    this.tiempoInicio = Date.now();
                    this.tiempoEstimado = 0;
                    this.filaActual = 0;

                    const procesarFila = async (i) => {
                        if (i >= this.rowsAll.length) {
                            this.importando = false;
                            this.importadoOk = true;
                            this.resultado = {
                                inserted: this.registrosImportados,
                                errors: this.erroresAcumulados
                            };
                            setTimeout(() => {
                                this.importadoOk = false;
                                document.body.classList.remove('overflow-hidden');
                            }, 1500);
                            return;
                        }
                        this.filaActual = i + 1;
                        const fila = this.rowsAll[i];
                        const answersEnviados = this.mapping
                            .map((map, idx) => {
                                if (map.field === 'answer.answer' && map.question_id) {
                                    return {
                                        answer: fila[idx],
                                        question_id: map.question_id,
                                        col: this.header[idx]
                                    };
                                }
                                return null;
                            })
                            .filter(Boolean);
                        try {
                            const response = await fetch(
                                '{{ route('admin.migraciones.import') }}', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    },
                                    body: JSON.stringify({
                                        file_content: this.fileContent,
                                        mapping: this.mapping,
                                        ayuda_id: this.ayudaDetectada ? this
                                            .ayudaDetectada.id : null,
                                        estado_contratacion: this.estadoDetectado,
                                        fase: this.faseDetectada,
                                        lote: [fila],
                                        lote_offset: i,
                                        csv_file_name: this.csvFileName || ''
                                    })
                                });
                            const data = await response.json();
                            this.registrosImportados += data.inserted || 0;
                            if (data.errors && data.errors.length) {
                                this.erroresAcumulados.push(...data.errors);
                            }
                        } catch (e) {
                            this.erroresAcumulados.push('Fila ' + (i + 2) +
                                ': Error de red o servidor');
                        }
                        this.progreso = Math.round(((i + 1) / this.rowsAll.length) * 100);
                        const registrosProcesados = i + 1;
                        const tiempoTranscurrido = (Date.now() - this.tiempoInicio) / 1000;
                        const registrosRestantes = this.rowsAll.length - registrosProcesados;
                        const tiempoPorRegistro = tiempoTranscurrido / registrosProcesados;
                        this.tiempoEstimado = Math.max(0, Math.round(registrosRestantes *
                            tiempoPorRegistro));
                        setTimeout(() => procesarFila(i + 1), 10);
                    };
                    procesarFila(0);
                },
                $nextTick() {
                    setTimeout(() => {
                        document.querySelectorAll('select[id^="question-select-"]').forEach(
                            sel => {
                                if (!sel.tomselect) {
                                    new TomSelect(sel, {
                                        create: false,
                                        allowEmptyOption: true,
                                        maxOptions: 1000
                                    });
                                }
                            });
                    }, 0);
                }
            }
        }
        document.addEventListener('alpine:init', () => {
            window.Alpine.data('migracionCsv', migracionCsv);
        });
    </script>
</body>

</html>
