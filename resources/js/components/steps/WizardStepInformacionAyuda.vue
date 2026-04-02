<template>
    <div>
        <h3 class="text-xl font-semibold text-gray-900 mb-6">
            <i class="fas fa-hands-helping text-blue-600 mr-2"></i>
            Información de la Ayuda
        </h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Nombre de la ayuda <span class="text-red-500">*</span>
                </label>
                <input 
                    :value="formData.ayuda.nombre_ayuda"
                    @input="handleNombreAyudaInput($event)"
                    type="text"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    placeholder="Ej: Ayuda para jóvenes emprendedores"
                >
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Slug (URL amigable)
                </label>
                <input 
                    :value="formData.ayuda.slug"
                    @input="updateField('slug', $event.target.value)"
                    type="text"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    placeholder="Se genera automáticamente"
                >
            </div>
            
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Descripción
                </label>
                <textarea 
                    :value="formData.ayuda.description"
                    @input="updateField('description', $event.target.value)"
                    rows="3"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    placeholder="Descripción detallada de la ayuda"
                ></textarea>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Sector <span class="text-red-500">*</span>
                </label>
                <select 
                    :value="formData.ayuda.sector"
                    @change="updateField('sector', $event.target.value)"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                >
                    <option value="">Seleccionar sector</option>
                    <option v-for="sector in sectores" :key="sector" :value="sector">
                        {{ capitalizeFirst(sector) }}
                    </option>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Órgano <span class="text-red-500">*</span>
                </label>
                <select 
                    :value="formData.ayuda.organo_id"
                    @change="updateField('organo_id', $event.target.value)"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                >
                    <option value="">Seleccionar órgano</option>
                    <option v-for="organo in organos" :key="organo.id" :value="organo.id">
                        {{ organo.nombre_organismo }}
                    </option>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Presupuesto <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500">€</span>
                    <input 
                        :value="formData.ayuda.presupuesto"
                        @input="formatCurrency('presupuesto', $event.target.value)"
                        type="text"
                        class="w-full pl-8 pr-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="0"
                    >
                </div>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Cuantía por usuario <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500">€</span>
                    <input 
                        :value="formData.ayuda.cuantia_usuario"
                        @input="formatCurrency('cuantia_usuario', $event.target.value)"
                        type="text"
                        class="w-full pl-8 pr-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="0"
                    >
                </div>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Fecha de inicio <span class="text-red-500">*</span>
                </label>
                <input 
                    :value="formData.ayuda.fecha_inicio"
                    @input="updateField('fecha_inicio', $event.target.value)"
                    type="date"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    required
                >
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Fecha de fin
                </label>
                <input 
                    :value="formData.ayuda.fecha_fin"
                    @input="updateField('fecha_fin', $event.target.value)"
                    type="date"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                >
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Fecha de inicio del período
                </label>
                <p class="text-xs text-gray-500 mb-2">
                    Fecha de inicio del período de solicitud de la ayuda. Por ejemplo para recibos
                </p>
                <input 
                    :value="formData.ayuda.fecha_inicio_periodo"
                    @input="updateField('fecha_inicio_periodo', $event.target.value)"
                    type="date"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                >
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Fecha de fin del período
                </label>
                <p class="text-xs text-gray-500 mb-2">
                    Fecha de fin del período de solicitud de la ayuda. Por ejemplo para recibos
                </p>
                <input 
                    :value="formData.ayuda.fecha_fin_periodo"
                    @input="updateField('fecha_fin_periodo', $event.target.value)"
                    type="date"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                >
            </div>
            
            <div class="md:col-span-2">
                <label class="flex items-center">
                    <input 
                        :checked="formData.ayuda.activo"
                        @change="updateField('activo', $event.target.checked)"
                        type="checkbox"
                        class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                    >
                    <span class="ml-2 text-sm text-gray-700">Ayuda activa</span>
                </label>
            </div>
        </div>
    </div>
</template>

<script>
import { generateSlug, formatCurrency as formatCurrencyUtil, capitalizeFirst } from '../../utils/formatters';

export default {
    name: 'WizardStepInformacionAyuda',
    props: {
        formData: {
            type: Object,
            required: true
        },
        organos: {
            type: Array,
            default: () => []
        },
        sectores: {
            type: Array,
            default: () => []
        }
    },
    emits: ['update:formData'],
    setup(props, { emit }) {
        const updateField = (field, value) => {
            emit('update:formData', {
                ...props.formData,
                ayuda: {
                    ...props.formData.ayuda,
                    [field]: value
                }
            });
        };

        const generateAyudaSlug = () => {
            if (props.formData.ayuda.nombre_ayuda) {
                const slug = generateSlug(props.formData.ayuda.nombre_ayuda);
                updateField('slug', slug);
            }
        };

        const formatCurrency = (field, value) => {
            const formatted = formatCurrencyUtil(value);
            updateField(field, formatted);
        };

        const handleNombreAyudaInput = (event) => {
            updateField('nombre_ayuda', event.target.value);
            generateAyudaSlug();
        };

        return {
            updateField,
            generateAyudaSlug,
            formatCurrency,
            capitalizeFirst,
            handleNombreAyudaInput
        };
    }
};
</script>

