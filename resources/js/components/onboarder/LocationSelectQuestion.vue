<template>
    <div class="space-y-4">
        <!-- Comunidad Autónoma -->
        <div v-if="question.question?.slug === 'comunidad_autonoma'">
            <label class="block text-sm font-medium text-gray-700 mb-2">
                {{ question.question?.text }}
            </label>
            <Multiselect
                :modelValue="value"
                @update:modelValue="handleChange"
                :options="ccaaOptions"
                :loading="loading"
                :disabled="blocked"
                placeholder="Selecciona una comunidad autónoma"
                :clearable="true"
                :searchable="true"
                :close-on-select="true"
                :single="true"
                :custom-label="(option) => option.label || option.nombre || option"
                :custom-search="customSearch"
                :class="blocked ? 'opacity-50 cursor-not-allowed' : ''"
            />
        </div>

        <!-- Provincia -->
        <div v-else-if="question.question?.slug === 'provincia'">
            <label class="block text-sm font-medium text-gray-700 mb-2">
                {{ question.question?.text }}
            </label>
            <div class="relative">
                <div class="relative">
                    <input
                        ref="provinciaInputRef"
                        :value="value ? getProvinciaLabel(value) : provinciaSearchQuery"
                        type="text"
                        :placeholder="
                            props.selectedCcaa
                                ? 'Selecciona una provincia'
                                : 'Selecciona una provincia (todas las opciones)'
                        "
                        :disabled="blocked"
                        class="w-full px-3 py-2 pr-8 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        @focus="handleProvinciaFocus"
                        @blur="handleProvinciaBlur"
                        @input="handleProvinciaInput"
                    />

                    <button
                        v-if="value"
                        type="button"
                        @click="clearProvincia"
                        class="absolute right-2 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600"
                        :disabled="blocked"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"
                            ></path>
                        </svg>
                    </button>

                    <div
                        class="absolute right-2 top-1/2 transform -translate-y-1/2 pointer-events-none"
                    >
                        <svg
                            class="w-4 h-4 text-gray-400"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M19 9l-7 7-7-7"
                            ></path>
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

        <!-- Municipio -->
        <div v-else-if="question.question?.slug === 'municipio'">
            <label class="block text-sm font-medium text-gray-700 mb-2">
                {{ question.question?.text }}
            </label>
            <div class="relative">
                <div class="relative">
                    <input
                        ref="municipioInputRef"
                        :value="value ? getMunicipioLabel(value) : municipioSearchQuery"
                        type="text"
                        :placeholder="
                            props.selectedProvincia
                                ? 'Selecciona un municipio'
                                : 'Selecciona un municipio (todas las opciones)'
                        "
                        :disabled="blocked || verifyingMunicipio"
                        class="w-full px-3 py-2 pr-8 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        :class="verifyingMunicipio ? 'opacity-60' : ''"
                        @focus="handleMunicipioFocus"
                        @blur="handleMunicipioBlur"
                        @input="handleMunicipioInput"
                    />

                    <button
                        v-if="value && !verifyingMunicipio"
                        type="button"
                        @click="clearMunicipio"
                        class="absolute right-2 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600"
                        :disabled="blocked"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"
                            ></path>
                        </svg>
                    </button>

                    <div
                        v-if="verifyingMunicipio"
                        class="absolute right-2 top-1/2 transform -translate-y-1/2"
                    >
                        <svg
                            class="animate-spin h-4 w-4 text-blue-500"
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                        >
                            <circle
                                class="opacity-25"
                                cx="12"
                                cy="12"
                                r="10"
                                stroke="currentColor"
                                stroke-width="4"
                            ></circle>
                            <path
                                class="opacity-75"
                                fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                            ></path>
                        </svg>
                    </div>

                    <div
                        v-else
                        class="absolute right-2 top-1/2 transform -translate-y-1/2 pointer-events-none"
                    >
                        <svg
                            class="w-4 h-4 text-gray-400"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M19 9l-7 7-7-7"
                            ></path>
                        </svg>
                    </div>
                </div>

                <div
                    v-if="
                        showMunicipioDropdown &&
                        filteredMunicipioOptions.length > 0 &&
                        !verifyingMunicipio
                    "
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

                <div
                    v-if="verifyingMunicipio"
                    class="absolute z-10 w-full mt-1 bg-blue-50 border border-blue-200 rounded-md shadow-sm px-3 py-2"
                >
                    <div class="flex items-center text-sm text-blue-600">
                        <svg
                            class="animate-spin h-4 w-4 mr-2"
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                        >
                            <circle
                                class="opacity-25"
                                cx="12"
                                cy="12"
                                r="10"
                                stroke="currentColor"
                                stroke-width="4"
                            ></circle>
                            <path
                                class="opacity-75"
                                fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                            ></path>
                        </svg>
                        Verificando municipio...
                    </div>
                </div>
            </div>
        </div>

        <!-- Select normal (fallback) -->
        <div v-else>
            <label class="block text-sm font-medium text-gray-700 mb-2">
                {{ question.question?.text }}
            </label>
            <Multiselect
                :modelValue="value"
                @update:modelValue="handleChange"
                :options="normalOptions"
                :disabled="blocked"
                placeholder="Selecciona una opción"
                :clearable="true"
                :searchable="true"
                :close-on-select="true"
                :single="true"
                :custom-label="(option) => option.label || option.value || option"
                :custom-search="customSearch"
                :class="blocked ? 'opacity-50 cursor-not-allowed' : ''"
            />
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import axios from 'axios'
import Multiselect from 'vue-multiselect'
import 'vue-multiselect/dist/vue-multiselect.min.css'

const props = defineProps({
    question: {
        type: Object,
        required: true,
    },
    value: {
        type: [String, Number],
        default: '',
    },
    answers: {
        type: Object,
        default: () => ({}),
    },
    options: {
        type: Array,
        default: () => [],
    },
    selectedCcaa: {
        type: String,
        default: null,
    },
    selectedProvincia: {
        type: String,
        default: null,
    },
    blocked: {
        type: Boolean,
        default: false,
    },
    verifyingMunicipio: {
        type: Boolean,
        default: false,
    },
})

const emit = defineEmits(['update'])

// Registrar Multiselect como componente local
const components = {
    Multiselect,
}

const normalizeText = (text) => {
    if (!text) return ''
    return text
        .toLowerCase()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
}

const customSearch = (query, label) => {
    if (!query) return true
    const normalizedQuery = normalizeText(query)
    const normalizedLabel = normalizeText(label)

    return normalizedLabel.includes(normalizedQuery)
}

const loading = ref(false)
const ccaaOptions = ref([])
const provinciaOptions = ref([])
const municipioOptions = ref([])

const municipioSearchQuery = ref('')
const showMunicipioDropdown = ref(false)
const municipioInputRef = ref(null)

const provinciaSearchQuery = ref('')
const showProvinciaDropdown = ref(false)
const provinciaInputRef = ref(null)

// Computed para opciones normales
const normalOptions = computed(() => {
    if (!props.question.question?.options) return []
    return Object.entries(props.question.question.options).map(([key, value]) => ({
        value: key,
        label: value,
    }))
})

const filteredMunicipioOptions = computed(() => {
    if (!municipioSearchQuery.value) return municipioOptions.value

    const normalizedQuery = normalizeText(municipioSearchQuery.value)

    return municipioOptions.value.filter((option) => {
        const label = option.label || option.nombre || option
        const normalizedLabel = normalizeText(label)
        return normalizedLabel.includes(normalizedQuery)
    })
})

const filteredProvinciaOptions = computed(() => {
    if (!provinciaSearchQuery.value) return provinciaOptions.value

    const normalizedQuery = normalizeText(provinciaSearchQuery.value)

    return provinciaOptions.value.filter((option) => {
        const label = option.label || option.nombre || option
        const normalizedLabel = normalizeText(label)
        return normalizedLabel.includes(normalizedQuery)
    })
})

const loadCcaaOptions = async () => {
    try {
        loading.value = true
        const response = await axios.get('/admin/searchCCAA')
        let options = response.data
        if (props.options && props.options.length > 0) {
            const conditionalValues = props.options.map((opt) =>
                typeof opt === 'string' ? opt : opt.value || opt.text || opt,
            )

            options = options.filter((option) => {
                const optionValue = option.value || option.text || option
                const optionLabel = option.label || option.nombre || option

                const matches = conditionalValues.some(
                    (conditionalValue) =>
                        conditionalValue === optionValue || conditionalValue === optionLabel,
                )

                return matches
            })
        }

        ccaaOptions.value = options
    } catch (error) {
        console.error('Error loading CCAA options:', error)
    } finally {
        loading.value = false
    }
}

const loadProvinciaOptions = async (ccaa = null) => {
    try {
        loading.value = true
        const params = ccaa ? { ccaa } : {}
        const response = await axios.get('/admin/searchProvincias', { params })
        let options = response.data

        if (props.options && props.options.length > 0) {
            const conditionalValues = props.options.map((opt) =>
                typeof opt === 'string' ? opt : opt.value || opt.text || opt,
            )

            options = options.filter((option) => {
                const optionValue = option.value || option.text || option
                const optionLabel = option.label || option.nombre || option

                return conditionalValues.some(
                    (conditionalValue) =>
                        conditionalValue === optionValue || conditionalValue === optionLabel,
                )
            })
        }

        provinciaOptions.value = options
    } catch (error) {
        console.error('Error loading provincia options:', error)
    } finally {
        loading.value = false
    }
}

const loadMunicipioOptions = async (provincia = null) => {
    try {
        loading.value = true
        const params = provincia ? { provincia } : {}
        const response = await axios.get('/admin/searchMunicipios', { params })
        let options = response.data

        if (props.options && props.options.length > 0) {
            const conditionalValues = props.options.map((opt) =>
                typeof opt === 'string' ? opt : opt.value || opt.text || opt,
            )

            options = options.filter((option) => {
                const optionValue = option.value || option.text || option
                const optionLabel = option.label || option.nombre || option

                return conditionalValues.some(
                    (conditionalValue) =>
                        conditionalValue === optionValue || conditionalValue === optionLabel,
                )
            })
        }

        municipioOptions.value = options
    } catch (error) {
        console.error('Error loading municipio options:', error)
    } finally {
        loading.value = false
    }
}

const handleChange = (selectedValue) => {
    if (props.blocked) return

    // Multiselect devuelve directamente el valor cuando es single
    const value = selectedValue || null
    emit('update', props.question.question.id, value)

    // Si es CCAA, cargar provincias (filtradas si hay CCAA seleccionada)
    if (props.question.question?.slug === 'comunidad_autonoma') {
        loadProvinciaOptions(value)
    }

    // Si es provincia, cargar municipios (filtrados si hay provincia seleccionada)
    if (props.question.question?.slug === 'provincia') {
        loadMunicipioOptions(value)
    }
}

const selectMunicipio = (option) => {
    const value = option.value || option
    handleChange(value)
    municipioSearchQuery.value = ''
    showMunicipioDropdown.value = false
}

const clearMunicipio = () => {
    handleChange(null)
    municipioSearchQuery.value = ''
}

const getMunicipioLabel = (value) => {
    const option = municipioOptions.value.find((opt) => (opt.value || opt) === value)
    return option ? option.label || option.nombre || option : value
}

const selectProvincia = (option) => {
    const value = option.value || option
    handleChange(value)
    provinciaSearchQuery.value = ''
    showProvinciaDropdown.value = false
}

const clearProvincia = () => {
    handleChange(null)
    provinciaSearchQuery.value = ''
}

const getProvinciaLabel = (value) => {
    const option = provinciaOptions.value.find((opt) => (opt.value || opt) === value)
    return option ? option.label || option.nombre || option : value
}

const handleMunicipioBlur = (event) => {
    const inputValue = event.target.value

    setTimeout(() => {
        showMunicipioDropdown.value = false

        if (inputValue && !props.value) {
            const normalizedInput = normalizeText(inputValue)
            const exactMatch = municipioOptions.value.find((option) => {
                const label = option.label || option.nombre || option
                return normalizeText(label) === normalizedInput
            })

            if (exactMatch) {
                selectMunicipio(exactMatch)
            } else {
                municipioSearchQuery.value = ''
                if (municipioInputRef.value) {
                    municipioInputRef.value.value = ''
                }
            }
        } else if (!inputValue) {
            municipioSearchQuery.value = ''
        }
    }, 200)
}

const handleMunicipioFocus = () => {
    showMunicipioDropdown.value = true
    if (props.value) {
        municipioSearchQuery.value = ''
    }
}

const handleMunicipioInput = (event) => {
    const inputValue = event.target.value
    municipioSearchQuery.value = inputValue
    showMunicipioDropdown.value = true

    if (inputValue) {
        const normalizedInput = normalizeText(inputValue)
        const exactMatch = municipioOptions.value.find((option) => {
            const label = option.label || option.nombre || option
            return normalizeText(label) === normalizedInput
        })

        if (exactMatch) {
            selectMunicipio(exactMatch)
        } else {
            if (props.value) {
                handleChange(null)
            }
        }
    } else {
        if (props.value) {
            handleChange(null)
        }
    }
}

const handleProvinciaBlur = (event) => {
    const inputValue = event.target.value

    setTimeout(() => {
        showProvinciaDropdown.value = false

        if (inputValue && !props.value) {
            const normalizedInput = normalizeText(inputValue)
            const exactMatch = provinciaOptions.value.find((option) => {
                const label = option.label || option.nombre || option
                return normalizeText(label) === normalizedInput
            })

            if (exactMatch) {
                selectProvincia(exactMatch)
            } else {
                provinciaSearchQuery.value = ''
                if (provinciaInputRef.value) {
                    provinciaInputRef.value.value = ''
                }
            }
        } else if (!inputValue) {
            provinciaSearchQuery.value = ''
        }
    }, 200)
}

const handleProvinciaFocus = () => {
    showProvinciaDropdown.value = true
    if (props.value) {
        provinciaSearchQuery.value = ''
    }
}

const handleProvinciaInput = (event) => {
    const inputValue = event.target.value
    provinciaSearchQuery.value = inputValue
    showProvinciaDropdown.value = true

    if (inputValue) {
        const normalizedInput = normalizeText(inputValue)
        const exactMatch = provinciaOptions.value.find((option) => {
            const label = option.label || option.nombre || option
            return normalizeText(label) === normalizedInput
        })

        if (exactMatch) {
            selectProvincia(exactMatch)
        } else {
            if (props.value) {
                handleChange(null)
            }
        }
    } else {
        if (props.value) {
            handleChange(null)
        }
    }
}

onMounted(() => {
    if (props.question.question?.slug === 'comunidad_autonoma') {
        loadCcaaOptions()
    } else if (props.question.question?.slug === 'provincia') {
        // Cargar todas las provincias o filtradas si hay CCAA
        loadProvinciaOptions(props.selectedCcaa)
    } else if (props.question.question?.slug === 'municipio') {
        // Cargar todos los municipios o filtrados si hay provincia
        loadMunicipioOptions(props.selectedProvincia)
    }
})

watch(
    () => props.question.question?.slug,
    (newSlug) => {
        if (newSlug === 'comunidad_autonoma') {
            loadCcaaOptions()
        } else if (newSlug === 'provincia') {
            loadProvinciaOptions(props.selectedCcaa)
        } else if (newSlug === 'municipio') {
            loadMunicipioOptions(props.selectedProvincia)
        }
    },
)

watch(
    () => props.selectedCcaa,
    (newCcaa) => {
        if (props.question.question?.slug === 'provincia') {
            loadProvinciaOptions(newCcaa)
        }
    },
)

watch(
    () => props.selectedProvincia,
    (newProvincia) => {
        if (props.question.question?.slug === 'municipio') {
            loadMunicipioOptions(newProvincia)
        }
    },
)

watch(
    () => props.options,
    () => {
        if (props.question.question?.slug === 'comunidad_autonoma') {
            loadCcaaOptions()
        } else if (props.question.question?.slug === 'provincia') {
            loadProvinciaOptions(props.selectedCcaa)
        } else if (props.question.question?.slug === 'municipio') {
            loadMunicipioOptions(props.selectedProvincia)
        }
    },
    { deep: true },
)
</script>
