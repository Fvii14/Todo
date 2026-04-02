<template>
    <div class="space-y-6">
        <div v-if="question.question?.slug === 'calculadora'">
            <div class="bg-white rounded-lg p-4 mb-6 border border-gray-200">
                <div class="mb-4">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-sm text-gray-700">Meses utilizados (últimos 12)</span>
                        <span class="text-sm font-medium" :class="remainingMonths === 0 ? 'text-red-600' : 'text-gray-800'">{{ totalMonths }} / 12</span>
                    </div>
                    <div class="w-full h-2 rounded bg-gray-200 overflow-hidden">
                        <div
                            class="h-2 transition-all"
                            :class="totalMonths < 9 ? 'bg-green-500' : (totalMonths < 12 ? 'bg-yellow-500' : 'bg-red-500')"
                            :style="{ width: Math.min(100, Math.round((totalMonths/12)*100)) + '%' }"
                        ></div>
                    </div>
                    <div class="mt-1 text-xs" :class="remainingMonths === 0 ? 'text-red-600' : 'text-gray-500'">
                        {{ remainingMonths === 0 ? 'Has alcanzado el límite de 12 meses' : `Te quedan ${remainingMonths} mes(es) disponibles` }}
                    </div>
                </div>

                <div class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded">
                    <p class="text-sm text-blue-900">
                        Introduce tus ingresos brutos de los <strong>últimos 12 meses</strong>. Puedes repartirlos por tipo (Trabajo, Pensión, Prestación, etc.), pero el <strong>total de meses no puede superar 12</strong>.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                            >Tipo de ingreso</label
                        >
                        <select
                            v-model="newIncome.type"
                            :disabled="blocked"
                            :class="[
                                'w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2',
                                blocked
                                    ? 'border-yellow-300 bg-yellow-50 text-gray-600 cursor-not-allowed'
                                    : 'border-gray-300 focus:ring-blue-500',
                            ]"
                        >
                            <option value="">Seleccionar...</option>
                            <option value="Trabajo">Trabajo</option>
                            <option value="Pensión">Pensión</option>
                            <option value="Prestación">Prestación</option>
                            <option value="Renta">Renta</option>
                            <option value="Otros">Otros</option>
                        </select>
                    </div>

                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                            >Meses percibidos</label
                        >
                        <input
                            v-model.number="newIncome.months"
                            type="number"
                            min="1"
                            :max="remainingMonths"
                            :disabled="blocked || remainingMonths === 0"
                            :class="[
                                'w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2',
                                (blocked || remainingMonths === 0)
                                    ? 'border-yellow-300 bg-yellow-50 text-gray-600 cursor-not-allowed'
                                    : 'border-gray-300 focus:ring-blue-500',
                            ]"
                            placeholder="12"
                        />
                        <p v-if="monthsValidationMessage" class="text-xs text-red-600 mt-1">{{ monthsValidationMessage }}</p>
                    </div>

                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                            >Importe medio</label
                        >
                        <input
                            v-model.number="newIncome.amount"
                            type="number"
                            step="0.01"
                            min="0"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="1200"
                        />
                    </div>

                    <div>
                        <button
                            @click="addIncome"
                            :disabled="!canAddIncome || remainingMonths === 0"
                            class="w-full bg-green-600 hover:bg-green-700 disabled:bg-gray-400 disabled:cursor-not-allowed text-white px-4 py-2 rounded-md font-medium transition-colors flex items-center justify-center"
                        >
                            <i class="fas fa-plus mr-2"></i>
                            Añadir ingreso
                        </button>
                    </div>
                </div>
            </div>

            <div
                v-if="incomes.length > 0"
                class="bg-white rounded-lg border border-gray-200 overflow-hidden"
            >
                <div class="px-4 py-3 bg-gray-50 border-b border-gray-200">
                    <div class="grid grid-cols-4 gap-4 text-sm font-medium text-gray-700">
                        <div>Tipo</div>
                        <div>Meses</div>
                        <div>Importe medio</div>
                        <div>Importe anual</div>
                    </div>
                </div>
                <div class="divide-y divide-gray-200">
                    <div
                        v-for="(income, index) in incomes"
                        :key="index"
                        class="px-4 py-3 hover:bg-gray-50"
                    >
                        <div class="grid grid-cols-4 gap-4 items-center">
                            <div class="text-sm text-gray-900">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">{{ income.type }}</span>
                            </div>
                            <div class="text-sm text-gray-900">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">{{ income.months }} meses</span>
                            </div>
                            <div class="text-sm text-gray-900">
                                {{ formatCurrency(income.amount) }}
                            </div>
                            <div class="text-sm text-gray-900 font-medium">
                                {{ formatCurrency(income.annual) }}
                            </div>
                            <div class="col-span-4 text-right">
                                <button
                                    @click="removeIncome(index)"
                                    class="text-red-600 hover:text-red-800 text-sm font-medium"
                                >
                                    Eliminar
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="px-4 py-3 bg-gray-50 flex items-center justify-end gap-6">
                        <div class="text-sm text-gray-700">
                            Total anual: <span class="font-semibold">{{ formatCurrency(totalGrossIncome) }}</span>
                        </div>
                        <div class="text-sm text-gray-500">Deducciones estimadas: {{ formatCurrency(estimatedDeductions) }}</div>
                        <div class="text-sm text-gray-900 font-semibold">Neto estimado: {{ formatCurrency(netIncome) }}</div>
                    </div>
                </div>
            </div>

            <div
                v-if="incomes.length > 0"
                class="bg-white rounded-lg p-4 border border-gray-200 mt-4"
            >
                <h5 class="text-md font-semibold text-gray-900 mb-3">
                    Resumen de Ingresos
                </h5>
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600"
                            >Total ingresos brutos estimados:</span
                        >
                        <span class="text-sm font-medium text-gray-900">{{
                            formatCurrency(totalGrossIncome)
                        }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600"
                            >Deducciones estimadas:</span
                        >
                        <span class="text-sm font-medium text-gray-900">{{
                            formatCurrency(estimatedDeductions)
                        }}</span>
                    </div>
                    <div
                        class="flex justify-between border-t border-gray-200 pt-2"
                    >
                        <span class="text-sm font-semibold text-gray-900"
                            >Ingresos netos estimados:</span
                        >
                        <span class="text-sm font-semibold text-gray-900">{{
                            formatCurrency(netIncome)
                        }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div v-else-if="question.question?.slug === 'education-builder'">
            <div class="flex items-center mb-6">
                <i
                    class="fas fa-graduation-cap text-2xl text-blue-600 mr-3"
                ></i>
                <h4 class="text-lg font-semibold text-gray-900">
                    Tus estudios y formación
                </h4>
            </div>

            <div class="mb-6 flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <button
                        @click="addStudy"
                        :disabled="hasNoStudies"
                        :class="[
                            'px-4 py-2 rounded-lg font-medium transition-colors flex items-center',
                            hasNoStudies
                                ? 'bg-gray-400 cursor-not-allowed text-white'
                                : 'bg-blue-600 hover:bg-blue-700 text-white'
                        ]"
                    >
                        <i class="fas fa-plus mr-2"></i>
                        Añadir estudio
                    </button>
                    <button
                        v-if="studies.length === 0"
                        @click="markNoStudies"
                        class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg font-medium transition-colors flex items-center"
                    >
                        <i class="fas fa-times mr-2"></i>
                        No tengo estudios
                    </button>
                </div>
                <div v-if="studies.length > 0" class="text-sm text-gray-600">
                    <span class="font-medium">{{ studies.length }}</span> estudio(s) añadido(s)
                </div>
            </div>

            <div v-if="studies.length > 0" class="space-y-4">
                <div
                    v-for="(study, index) in studies"
                    :key="index"
                    class="bg-white rounded-lg border border-gray-200 p-4"
                >
                    <div class="flex justify-between items-start mb-3">
                        <div class="flex-1">
                            <h5 class="font-semibold text-gray-900 text-lg">
                                {{ study.name }}
                            </h5>
                            <p v-if="!study.isNoStudies" class="text-gray-600 text-sm">
                                {{ getProvinciaLabel(study.provincia) }}, {{ getMunicipioLabel(study.municipio) }}
                            </p>
                            <div class="flex items-center mt-2">
                                <span
                                    :class="[
                                        'px-2 py-1 rounded-full text-xs font-medium',
                                        study.isNoStudies
                                            ? 'bg-gray-100 text-gray-800'
                                            : study.status === 'completed'
                                            ? 'bg-green-100 text-green-800'
                                            : 'bg-blue-100 text-blue-800',
                                    ]"
                                >
                                    {{
                                        study.isNoStudies
                                            ? "Sin estudios"
                                            : study.status === "completed"
                                            ? "Finalizado"
                                            : "En curso"
                                    }}
                                </span>
                                <span v-if="!study.isNoStudies" class="ml-3 text-sm text-gray-500">
                                    {{ getEducationLevelLabel(study.level) }}
                                </span>
                            </div>
                        </div>
                        <div class="flex space-x-2">
                            <button
                                v-if="!study.isNoStudies"
                                @click="editStudy(index)"
                                class="text-blue-600 hover:text-blue-800 text-sm font-medium"
                            >
                                Editar
                            </button>
                            <button
                                @click="removeStudy(index)"
                                class="text-red-600 hover:text-red-800 text-sm font-medium"
                            >
                                {{ study.isNoStudies ? 'Cambiar' : 'Eliminar' }}
                            </button>
                        </div>
                    </div>

                    <div
                        v-if="!study.isNoStudies"
                        class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-600"
                    >
                        <div v-if="study.status === 'completed'">
                            <span class="font-medium">Fin:</span>
                            {{ formatDate(study.endDate) }}
                        </div>
                        <div v-else>
                            <span class="font-medium">Inicio:</span>
                            {{ formatDate(study.startDate) }}
                        </div>
                        <div>
                            <span class="font-medium">Titularidad:</span>
                            {{ getOwnershipLabel(study.ownership) }}
                        </div>
                        <div>
                            <span class="font-medium">Modalidad:</span>
                            {{ getModalityLabel(study.modality) }}
                        </div>
                        <div>
                            <span class="font-medium">Oficialidad:</span>
                            {{ study.isOfficial ? "Oficial" : "No oficial" }}
                        </div>
                        <div v-if="study.status === 'ongoing'">
                            <span class="font-medium">Matriculado:</span>
                            {{ study.isEnrolled ? "Sí" : "No" }}
                        </div>
                    </div>
                </div>
            </div>

            <div v-else class="text-center py-8 text-gray-500">
                <i class="fas fa-graduation-cap text-4xl mb-4"></i>
                <p>No hay estudios añadidos</p>
                <p class="text-sm">Haz clic en "Añadir" para comenzar</p>
            </div>
        </div>

        <div v-else class="bg-gray-50 rounded-lg p-6">
            <h4 class="text-lg font-semibold text-gray-900 mb-4">Builder</h4>
            <p class="text-gray-600">
                Builder type: {{ question.question?.slug }}
            </p>
        </div>

        <div
            v-if="showAddStudyModal"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
        >
            <div
                class="bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto"
            >
                <div class="p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-xl font-semibold text-gray-900">
                            {{
                                editingStudyIndex !== null
                                    ? "Editar estudio"
                                    : "Añadir estudio"
                            }}
                        </h3>
                        <button
                            @click="closeStudyModal"
                            class="text-gray-400 hover:text-gray-600"
                        >
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>

                    <form @submit.prevent="saveStudy" class="space-y-6">
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-2"
                            >
                                Nivel de estudio
                            </label>
                            <select
                                v-model="studyForm.level"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            >
                                <option value="">Selecciona nivel</option>
                                <option value="bachillerato">
                                    Bachillerato
                                </option>
                                <option value="fp_grado_medio">
                                    FP Grado Medio
                                </option>
                                <option value="fp_grado_superior">
                                    FP Grado Superior
                                </option>
                                <option value="grado">
                                    Grado Universitario
                                </option>
                                <option value="master">
                                    Máster Universitario
                                </option>
                                <option value="doctorado">Doctorado</option>
                                <option value="curso">Curso</option>
                                <option value="certificacion">
                                    Certificación
                                </option>
                            </select>
                        </div>

                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-2"
                            >
                                Tipo de estudio
                            </label>
                            <div class="grid grid-cols-2 gap-4">
                                <label class="flex items-center cursor-pointer">
                                    <input
                                        v-model="studyForm.status"
                                        type="radio"
                                        value="ongoing"
                                        class="w-4 h-4 text-blue-600 focus:ring-blue-500 border-gray-300"
                                    />
                                    <span class="ml-2 text-gray-700"
                                        >En curso</span
                                    >
                                </label>
                                <label class="flex items-center cursor-pointer">
                                    <input
                                        v-model="studyForm.status"
                                        type="radio"
                                        value="completed"
                                        class="w-4 h-4 text-blue-600 focus:ring-blue-500 border-gray-300"
                                    />
                                    <span class="ml-2 text-gray-700"
                                        >Finalizado</span
                                    >
                                </label>
                            </div>
                        </div>


                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-2"
                            >
                                Provincia
                            </label>
                            <div class="relative">
                                <div class="relative">
                                    <input
                                        ref="provinciaInputRef"
                                        :value="studyForm.provincia ? getProvinciaLabel(studyForm.provincia) : provinciaSearchQuery"
                                        type="text"
                                        placeholder="Selecciona una provincia"
                                        class="w-full px-3 py-2 pr-8 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                        @focus="handleProvinciaFocus"
                                        @blur="handleProvinciaBlur"
                                        @input="handleProvinciaInput"
                                    />

                                    <button
                                        v-if="studyForm.provincia"
                                        type="button"
                                        @click="clearProvincia"
                                        class="absolute right-2 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </button>

                                    <div class="absolute right-2 top-1/2 transform -translate-y-1/2 pointer-events-none">
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    </div>
                                </div>

                                <div
                                    v-if="showProvinciaDropdown && filteredProvinciaOptions.length > 0"
                                    class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-y-auto"
                                >
                                    <div
                                        v-for="(option, index) in filteredProvinciaOptions"
                                        :key="index"
                                        class="px-3 py-2 hover:bg-gray-100 cursor-pointer text-sm"
                                        @mousedown="selectProvincia(option)"
                                    >
                                        {{ option.label || option.nombre || option }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-2"
                            >
                                Municipio
                            </label>
                            <div class="relative">
                                <div class="relative">
                                    <input
                                        ref="municipioInputRef"
                                        :value="studyForm.municipio ? getMunicipioLabel(studyForm.municipio) : municipioSearchQuery"
                                        type="text"
                                        :placeholder="
                                            studyForm.provincia
                                                ? 'Selecciona un municipio'
                                                : 'Selecciona un municipio (todas las opciones)'
                                        "
                                        class="w-full px-3 py-2 pr-8 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                        @focus="handleMunicipioFocus"
                                        @blur="handleMunicipioBlur"
                                        @input="handleMunicipioInput"
                                    />

                                    <button
                                        v-if="studyForm.municipio"
                                        type="button"
                                        @click="clearMunicipio"
                                        class="absolute right-2 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </button>

                                    <div class="absolute right-2 top-1/2 transform -translate-y-1/2 pointer-events-none">
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    </div>
                                </div>

                                <div
                                    v-if="showMunicipioDropdown && filteredMunicipioOptions.length > 0"
                                    class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-y-auto"
                                >
                                    <div
                                        v-for="(option, index) in filteredMunicipioOptions"
                                        :key="index"
                                        class="px-3 py-2 hover:bg-gray-100 cursor-pointer text-sm"
                                        @mousedown="selectMunicipio(option)"
                                    >
                                        {{ option.label || option.nombre || option }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-2"
                            >
                                Titularidad
                            </label>
                            <div class="grid grid-cols-3 gap-4">
                                <label class="flex items-center cursor-pointer">
                                    <input
                                        v-model="studyForm.ownership"
                                        type="radio"
                                        value="publico"
                                        class="w-4 h-4 text-blue-600 focus:ring-blue-500 border-gray-300"
                                    />
                                    <span class="ml-2 text-gray-700"
                                        >Público</span
                                    >
                                </label>
                                <label class="flex items-center cursor-pointer">
                                    <input
                                        v-model="studyForm.ownership"
                                        type="radio"
                                        value="concertado"
                                        class="w-4 h-4 text-blue-600 focus:ring-blue-500 border-gray-300"
                                    />
                                    <span class="ml-2 text-gray-700"
                                        >Concertado</span
                                    >
                                </label>
                                <label class="flex items-center cursor-pointer">
                                    <input
                                        v-model="studyForm.ownership"
                                        type="radio"
                                        value="privado"
                                        class="w-4 h-4 text-blue-600 focus:ring-blue-500 border-gray-300"
                                    />
                                    <span class="ml-2 text-gray-700"
                                        >Privado</span
                                    >
                                </label>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div v-if="studyForm.status === 'completed'">
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-2"
                                >
                                    Fecha de fin
                                </label>
                                <VueDatePicker
                                    v-model="studyForm.endDate"
                                    month-picker
                                    :enable-time-picker="false"
                                    :auto-apply="true"
                                    :clearable="true"
                                    placeholder="Selecciona mes y año"
                                    :format="'MM/yyyy'"
                                    :preview-format="'MMMM yyyy'"
                                    locale="es"
                                    class="w-full"
                                />
                            </div>
                            <div v-else>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-2"
                                >
                                    Fecha de inicio
                                </label>
                                <VueDatePicker
                                    v-model="studyForm.startDate"
                                    month-picker
                                    :enable-time-picker="false"
                                    :auto-apply="true"
                                    :clearable="true"
                                    placeholder="Selecciona mes y año"
                                    :format="'MM/yyyy'"
                                    :preview-format="'MMMM yyyy'"
                                    locale="es"
                                    class="w-full"
                                />
                            </div>
                        </div>

                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-2"
                            >
                                Modalidad
                            </label>
                            <div class="grid grid-cols-3 gap-4">
                                <label class="flex items-center cursor-pointer">
                                    <input
                                        v-model="studyForm.modality"
                                        type="radio"
                                        value="presencial"
                                        class="w-4 h-4 text-blue-600 focus:ring-blue-500 border-gray-300"
                                    />
                                    <span class="ml-2 text-gray-700"
                                        >Presencial</span
                                    >
                                </label>
                                <label class="flex items-center cursor-pointer">
                                    <input
                                        v-model="studyForm.modality"
                                        type="radio"
                                        value="semipresencial"
                                        class="w-4 h-4 text-blue-600 focus:ring-blue-500 border-gray-300"
                                    />
                                    <span class="ml-2 text-gray-700"
                                        >Semipresencial</span
                                    >
                                </label>
                                <label class="flex items-center cursor-pointer">
                                    <input
                                        v-model="studyForm.modality"
                                        type="radio"
                                        value="online"
                                        class="w-4 h-4 text-blue-600 focus:ring-blue-500 border-gray-300"
                                    />
                                    <span class="ml-2 text-gray-700"
                                        >Online</span
                                    >
                                </label>
                            </div>
                        </div>

                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-2"
                            >
                                Oficialidad
                            </label>
                            <div class="grid grid-cols-2 gap-4">
                                <label class="flex items-center cursor-pointer">
                                    <input
                                        v-model="studyForm.isOfficial"
                                        type="radio"
                                        :value="true"
                                        class="w-4 h-4 text-blue-600 focus:ring-blue-500 border-gray-300"
                                    />
                                    <span class="ml-2 text-gray-700"
                                        >Oficial</span
                                    >
                                </label>
                                <label class="flex items-center cursor-pointer">
                                    <input
                                        v-model="studyForm.isOfficial"
                                        type="radio"
                                        :value="false"
                                        class="w-4 h-4 text-blue-600 focus:ring-blue-500 border-gray-300"
                                    />
                                    <span class="ml-2 text-gray-700"
                                        >No oficial</span
                                    >
                                </label>
                            </div>
                        </div>

                        <div v-if="studyForm.status === 'ongoing'">
                            <label
                                class="block text-sm font-medium text-gray-700 mb-2"
                            >
                                Matriculado
                            </label>
                            <div class="grid grid-cols-2 gap-4">
                                <label class="flex items-center cursor-pointer">
                                    <input
                                        v-model="studyForm.isEnrolled"
                                        type="radio"
                                        :value="true"
                                        class="w-4 h-4 text-blue-600 focus:ring-blue-500 border-gray-300"
                                    />
                                    <span class="ml-2 text-gray-700">Sí</span>
                                </label>
                                <label class="flex items-center cursor-pointer">
                                    <input
                                        v-model="studyForm.isEnrolled"
                                        type="radio"
                                        :value="false"
                                        class="w-4 h-4 text-blue-600 focus:ring-blue-500 border-gray-300"
                                    />
                                    <span class="ml-2 text-gray-700">No</span>
                                </label>
                            </div>
                        </div>

                        <div
                            class="flex justify-end space-x-3 pt-4 border-t border-gray-200"
                        >
                            <button
                                type="button"
                                @click="closeStudyModal"
                                class="px-4 py-2 text-gray-600 hover:text-gray-800 transition-colors"
                            >
                                Cancelar
                            </button>
                            <button
                                type="submit"
                                :disabled="!canSaveStudy"
                                class="px-6 py-2 bg-blue-600 hover:bg-blue-700 disabled:bg-gray-400 disabled:cursor-not-allowed text-white rounded-lg transition-colors"
                            >
                                {{
                                    editingStudyIndex !== null
                                        ? "Actualizar"
                                        : "Guardar estudio"
                                }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from "vue";
import axios from "axios";
import VueDatePicker from '@vuepic/vue-datepicker';
import '@vuepic/vue-datepicker/dist/main.css';

const props = defineProps({
    question: Object,
    questionIndex: Number,
    value: [String, Number, Object],
    blocked: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(["update"]);

const components = {
    VueDatePicker
};

// Datos para la calculadora
const newIncome = ref({
    type: "",
    months: 12,
    amount: 0,
});

const incomes = ref([]);

const studies = ref([]);
const showAddStudyModal = ref(false);
const editingStudyIndex = ref(null);
const provinciaOptions = ref([]);
const municipioOptions = ref([]);
const provinciaSearchQuery = ref('');
const municipioSearchQuery = ref('');
const showProvinciaDropdown = ref(false);
const showMunicipioDropdown = ref(false);
const provinciaInputRef = ref(null);
const municipioInputRef = ref(null);
const studyForm = ref({
    status: "ongoing",
    level: "",
    name: "",
    provincia: "",
    municipio: "",
    ownership: "publico",
    startDate: "",
    endDate: "",
    modality: "presencial",
    isOfficial: true,
    isEnrolled: true,
});

// Computed properties
const totalMonths = computed(() => incomes.value.reduce((sum, i) => sum + (Number(i.months) || 0), 0));
const remainingMonths = computed(() => Math.max(0, 12 - totalMonths.value));

const canAddIncome = computed(() => {
    const monthsToAdd = Number(newIncome.value.months) || 0;
    const withinMonths = monthsToAdd > 0 && monthsToAdd <= remainingMonths.value;
    return (
        withinMonths &&
        newIncome.value.type &&
        newIncome.value.amount > 0
    );
});

const totalGrossIncome = computed(() => {
    return incomes.value.reduce((total, income) => total + income.annual, 0);
});

const estimatedDeductions = computed(() => {
    // Estimación del 20% de deducciones (IRPF, SS, etc.)
    return Math.round(totalGrossIncome.value * 0.2);
});

const netIncome = computed(() => {
    return totalGrossIncome.value - estimatedDeductions.value;
});

const canSaveStudy = computed(() => {
    return (
        studyForm.value.level &&
        studyForm.value.status &&
        studyForm.value.provincia &&
        studyForm.value.municipio &&
        studyForm.value.ownership &&
        studyForm.value.modality &&
        (studyForm.value.status === "completed"
            ? studyForm.value.endDate
            : studyForm.value.startDate)
    );
});

const hasNoStudies = computed(() => {
    return studies.value.some(study => study.isNoStudies === true);
});

const normalizeText = (text) => {
    if (!text) return '';
    return text
        .toLowerCase()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '');
};

const filteredProvinciaOptions = computed(() => {
    if (!provinciaSearchQuery.value) return provinciaOptions.value;
    
    const normalizedQuery = normalizeText(provinciaSearchQuery.value);
    
    return provinciaOptions.value.filter(option => {
        const label = option.label || option.nombre || option;
        const normalizedLabel = normalizeText(label);
        return normalizedLabel.includes(normalizedQuery);
    });
});

const filteredMunicipioOptions = computed(() => {
    if (!municipioSearchQuery.value) return municipioOptions.value;
    
    const normalizedQuery = normalizeText(municipioSearchQuery.value);
    
    return municipioOptions.value.filter(option => {
        const label = option.label || option.nombre || option;
        const normalizedLabel = normalizeText(label);
        return normalizedLabel.includes(normalizedQuery);
    });
});

// Methods
const addIncome = () => {
    if (!canAddIncome.value) return;
    const monthsToAdd = Number(newIncome.value.months) || 0;
    if (monthsToAdd > remainingMonths.value) {
        newIncome.value.months = remainingMonths.value;
        return;
    }

    const income = {
        type: newIncome.value.type,
        months: newIncome.value.months,
        amount: newIncome.value.amount,
        annual: newIncome.value.amount * newIncome.value.months,
    };

    incomes.value.push(income);

    // Reset form
    newIncome.value = {
        type: "",
        months: 12,
        amount: 0,
    };

    // Emit update
    emitUpdate();
};

const removeIncome = (index) => {
    incomes.value.splice(index, 1);
    emitUpdate();
};

const addStudy = () => {
    showAddStudyModal.value = true;
    editingStudyIndex.value = null;
    resetStudyForm();
    loadProvinciaOptions();
};

const editStudy = (index) => {
    editingStudyIndex.value = index;
    const study = studies.value[index];
    studyForm.value = { ...study };

    if (study.startDate && typeof study.startDate === 'string') {
        const [year, month] = study.startDate.split('-');
        studyForm.value.startDate = new Date(parseInt(year), parseInt(month) - 1, 1);
    }
    if (study.endDate && typeof study.endDate === 'string') {
        const [year, month] = study.endDate.split('-');
        studyForm.value.endDate = new Date(parseInt(year), parseInt(month) - 1, 1);
    }
    
    showAddStudyModal.value = true;
    loadProvinciaOptions();
    if (study.provincia) {
        loadMunicipioOptions(study.provincia);
    }
};

const removeStudy = (index) => {
    studies.value.splice(index, 1);
    emitUpdate();
};

const markNoStudies = () => {
    const noStudiesEntry = {
        status: "completed",
        level: "sin_estudios",
        name: "No tengo estudios",
        provincia: null,
        municipio: null,
        ownership: null,
        startDate: null,
        endDate: null,
        modality: null,
        isOfficial: null,
        isEnrolled: null,
        isNoStudies: true
    };
    
    studies.value = [noStudiesEntry];
    emitUpdate();
};

const saveStudy = () => {
    if (!canSaveStudy.value) return;

    const study = { ...studyForm.value };
    
    if (study.startDate instanceof Date) {
        const year = study.startDate.getFullYear();
        const month = study.startDate.getMonth() + 1;
        study.startDate = `${year}-${String(month).padStart(2, '0')}`;
    } else if (study.startDate && typeof study.startDate === 'object' && study.startDate.year && study.startDate.month !== undefined) {
        const year = study.startDate.year;
        const month = study.startDate.month + 1;
        study.startDate = `${year}-${String(month).padStart(2, '0')}`;
    }
    
    if (study.endDate instanceof Date) {
        const year = study.endDate.getFullYear();
        const month = study.endDate.getMonth() + 1;
        study.endDate = `${year}-${String(month).padStart(2, '0')}`;
    } else if (study.endDate && typeof study.endDate === 'object' && study.endDate.year && study.endDate.month !== undefined) {
        const year = study.endDate.year;
        const month = study.endDate.month + 1;
        study.endDate = `${year}-${String(month).padStart(2, '0')}`;
    }

    if (editingStudyIndex.value !== null) {
        studies.value[editingStudyIndex.value] = study;
    } else {
        studies.value.push(study);
    }

    closeStudyModal();
    emitUpdate();
};

const closeStudyModal = () => {
    showAddStudyModal.value = false;
    editingStudyIndex.value = null;
    resetStudyForm();
};

const resetStudyForm = () => {
    studyForm.value = {
        status: "ongoing",
        level: "",
        name: "",
        provincia: "",
        municipio: "",
        ownership: "publico",
        startDate: "",
        endDate: "",
        modality: "presencial",
        isOfficial: true,
        isEnrolled: true,
    };
};

const loadProvinciaOptions = async () => {
    try {
        const response = await axios.get("/admin/searchProvincias");
        provinciaOptions.value = response.data;
    } catch (error) {
        console.error("Error loading provincia options:", error);
    }
};

const loadMunicipioOptions = async (provincia = null) => {
    try {
        const params = provincia ? { provincia } : {};
        const response = await axios.get("/admin/searchMunicipios", { params });
        municipioOptions.value = response.data;
    } catch (error) {
        console.error("Error loading municipio options:", error);
    }
};

const selectProvincia = (option) => {
    const value = option.value || option;
    studyForm.value.provincia = value;
    provinciaSearchQuery.value = '';
    showProvinciaDropdown.value = false;
    
    loadMunicipioOptions(value);
    
    studyForm.value.municipio = '';
};

const clearProvincia = () => {
    studyForm.value.provincia = '';
    provinciaSearchQuery.value = '';
    studyForm.value.municipio = '';
    municipioSearchQuery.value = '';
};

const getProvinciaLabel = (value) => {
    const option = provinciaOptions.value.find(opt => (opt.value || opt) === value);
    return option ? (option.label || option.nombre || option) : value;
};

const handleProvinciaFocus = () => {
    showProvinciaDropdown.value = true;
    if (studyForm.value.provincia) {
        provinciaSearchQuery.value = '';
    }
};

const handleProvinciaInput = (event) => {
    const inputValue = event.target.value;
    provinciaSearchQuery.value = inputValue;
    showProvinciaDropdown.value = true;

    if (inputValue) {
        const normalizedInput = normalizeText(inputValue);
        const exactMatch = provinciaOptions.value.find(option => {
            const label = option.label || option.nombre || option;
            return normalizeText(label) === normalizedInput;
        });
        
        if (exactMatch) {
            selectProvincia(exactMatch);
        } else {
            studyForm.value.provincia = '';
        }
    } else {
        studyForm.value.provincia = '';
    }
};

const handleProvinciaBlur = (event) => {
    const inputValue = event.target.value;
    
    setTimeout(() => {
        showProvinciaDropdown.value = false;
        
        if (inputValue && !studyForm.value.provincia) {
            const normalizedInput = normalizeText(inputValue);
            const exactMatch = provinciaOptions.value.find(option => {
                const label = option.label || option.nombre || option;
                return normalizeText(label) === normalizedInput;
            });
            
            if (exactMatch) {
                selectProvincia(exactMatch);
            } else {
                provinciaSearchQuery.value = '';
                if (provinciaInputRef.value) {
                    provinciaInputRef.value.value = '';
                }
            }
        } else if (!inputValue) {
            provinciaSearchQuery.value = '';
        }
    }, 200);
};

const selectMunicipio = (option) => {
    const value = option.value || option;
    studyForm.value.municipio = value;
    municipioSearchQuery.value = '';
    showMunicipioDropdown.value = false;
};

const clearMunicipio = () => {
    studyForm.value.municipio = '';
    municipioSearchQuery.value = '';
};

const getMunicipioLabel = (value) => {
    const option = municipioOptions.value.find(opt => (opt.value || opt) === value);
    return option ? (option.label || option.nombre || option) : value;
};

const handleMunicipioFocus = () => {
    showMunicipioDropdown.value = true;
    if (studyForm.value.municipio) {
        municipioSearchQuery.value = '';
    }
};

const handleMunicipioInput = (event) => {
    const inputValue = event.target.value;
    municipioSearchQuery.value = inputValue;
    showMunicipioDropdown.value = true;

    if (inputValue) {
        const normalizedInput = normalizeText(inputValue);
        const exactMatch = municipioOptions.value.find(option => {
            const label = option.label || option.nombre || option;
            return normalizeText(label) === normalizedInput;
        });
        
        if (exactMatch) {
            selectMunicipio(exactMatch);
        } else {
            studyForm.value.municipio = '';
        }
    } else {
        studyForm.value.municipio = '';
    }
};

const handleMunicipioBlur = (event) => {
    const inputValue = event.target.value;
    
    setTimeout(() => {
        showMunicipioDropdown.value = false;
        
        if (inputValue && !studyForm.value.municipio) {
            const normalizedInput = normalizeText(inputValue);
            const exactMatch = municipioOptions.value.find(option => {
                const label = option.label || option.nombre || option;
                return normalizeText(label) === normalizedInput;
            });
            
            if (exactMatch) {
                selectMunicipio(exactMatch);
            } else {
                municipioSearchQuery.value = '';
                if (municipioInputRef.value) {
                    municipioInputRef.value.value = '';
                }
            }
        } else if (!inputValue) {
            municipioSearchQuery.value = '';
        }
    }, 200);
};

const getEducationLevelLabel = (level) => {
    const labels = {
        bachillerato: "Bachillerato",
        fp_grado_medio: "FP Grado Medio",
        fp_grado_superior: "FP Grado Superior",
        grado: "Grado Universitario",
        master: "Máster Universitario",
        doctorado: "Doctorado",
        curso: "Curso",
        certificacion: "Certificación",
        sin_estudios: "Sin estudios",
    };
    return labels[level] || level;
};

const getOwnershipLabel = (ownership) => {
    const labels = {
        publico: "Público",
        concertado: "Concertado",
        privado: "Privado",
    };
    return labels[ownership] || ownership;
};

const getModalityLabel = (modality) => {
    const labels = {
        presencial: "Presencial",
        semipresencial: "Semipresencial",
        online: "Online",
    };
    return labels[modality] || modality;
};

const formatDate = (dateValue) => {
    if (!dateValue) return "";
    if (dateValue instanceof Date) {
        const year = dateValue.getFullYear();
        const month = dateValue.getMonth() + 1;
        const result = `${String(month).padStart(2, '0')}/${year}`;
        return result;
    }

    if (typeof dateValue === 'string' && dateValue.includes("-")) {
        const [year, month] = dateValue.split("-");
        return `${month}/${year}`;
    }

    if (typeof dateValue === 'string') {
        return dateValue;
    }

    if (typeof dateValue === 'object' && dateValue !== null) {
        if (dateValue.year && dateValue.month !== undefined) {
            const month = dateValue.month + 1;
            return `${String(month).padStart(2, '0')}/${dateValue.year}`;
        }
        if (dateValue.toString && dateValue.toString() !== '[object Object]') {
            return dateValue.toString();
        }
    }

    return String(dateValue);
};

const formatCurrency = (amount) => {
    return new Intl.NumberFormat("es-ES", {
        style: "currency",
        currency: "EUR",
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(amount);
};

const emitUpdate = () => {
    let data = {};

    if (props.question.question?.slug === "calculadora") {
        data = {
            incomes: incomes.value,
            totalGrossIncome: totalGrossIncome.value,
            estimatedDeductions: estimatedDeductions.value,
            netIncome: netIncome.value,
        };
    } else if (props.question.question?.slug === "education-builder") {
        data = {
            studies: studies.value,
            totalStudies: studies.value.length,
            completedStudies: studies.value.filter(
                (s) => s.status === "completed",
            ).length,
            ongoingStudies: studies.value.filter((s) => s.status === "ongoing")
                .length,
        };
    }

    emit("update", props.question.question.id, data);
};

// Load existing data
watch(
    () => props.value,
    (newValue) => {
        if (newValue && typeof newValue === "object") {
            if (
                props.question.question?.slug === "calculadora" &&
                newValue.incomes
            ) {
                incomes.value = newValue.incomes || [];
            } else if (
                props.question.question?.slug === "education-builder" &&
                newValue.studies
            ) {
                studies.value = newValue.studies || [];
            }
        }
    },
    { immediate: true },
);

// Watch for changes and emit updates
watch(
    [incomes, studies],
    () => {
        emitUpdate();
    },
    { deep: true },
);
</script>
