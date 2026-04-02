<template>
    <!-- Modal para crear/editar producto -->
    <div
        v-if="modelValue"
        class="modal-overlay"
        @click.self="handleClose"
    >
        <div class="modal-content" :class="{ 'max-w-md': showNewServicioModal }">
            <div class="modal-header">
                <h3 class="modal-title">
                    {{ editingProduct ? 'Editar Producto' : 'Crear Nuevo Producto' }}
                </h3>
                <button @click="handleClose" class="modal-close-button">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <form @submit.prevent="saveProduct" class="modal-body">
                <div class="form-group">
                    <label for="product_name" class="form-label">
                        Nombre del Producto <span class="text-red-500">*</span>
                    </label>
                    <input
                        id="product_name"
                        v-model="productForm.product_name"
                        type="text"
                        class="form-input"
                        placeholder="Ej: Plan Básico"
                        required
                    />
                </div>

                <div class="form-group">
                    <label for="price" class="form-label">
                        Precio
                    </label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500">€</span>
                        <input
                            id="price"
                            v-model="productForm.price"
                            type="number"
                            step="0.01"
                            min="0"
                            class="form-input pl-8"
                            placeholder="0.00"
                        />
                    </div>
                </div>

                <div class="form-group">
                    <label for="commission_pct" class="form-label">
                        Comisión (%)
                    </label>
                    <input
                        id="commission_pct"
                        v-model="productForm.commission_pct"
                        type="number"
                        step="0.01"
                        min="0"
                        max="100"
                        class="form-input"
                        placeholder="Ej: 10 para un 10%"
                    />
                </div>

                <div class="form-group">
                    <label for="currency" class="form-label">
                        Moneda
                    </label>
                    <select
                        id="currency"
                        v-model="productForm.currency"
                        class="form-input"
                    >
                        <option value="EUR">EUR</option>
                        <option value="USD">USD</option>
                        <option value="GBP">GBP</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="payment_type" class="form-label">
                        Tipo de Pago <span class="text-red-500">*</span>
                    </label>
                    <select
                        id="payment_type"
                        v-model="productForm.payment_type"
                        class="form-input"
                        required
                    >
                        <option value="">Selecciona un tipo de pago</option>
                        <option value="monthly">Mensual (monthly)</option>
                        <option value="annual">Anual (annual)</option>
                        <option value="one_time">Pago Único (one_time)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="stripe_product_id" class="form-label">
                        Stripe Product ID
                    </label>
                    <input
                        id="stripe_product_id"
                        v-model="productForm.stripe_product_id"
                        type="text"
                        class="form-input"
                        placeholder="prod_xxxxx"
                    />
                </div>

                <div class="form-group">
                    <label for="price_id" class="form-label">
                        Stripe Price ID
                    </label>
                    <input
                        id="price_id"
                        v-model="productForm.price_id"
                        type="text"
                        class="form-input"
                        placeholder="price_xxxxx"
                    />
                </div>

                <!-- Gestión de Servicios -->
                <div class="form-group">
                    <label class="form-label">
                        Servicios Incluidos
                    </label>
                    <div class="space-y-3">
                        <!-- Lista de servicios disponibles -->
                        <div class="border border-gray-300 rounded-lg p-3 max-h-48 overflow-y-auto">
                            <div v-if="availableServicios.length === 0" class="text-gray-500 text-sm text-center py-2">
                                No hay servicios disponibles. Crea uno nuevo.
                            </div>
                            <div v-else class="space-y-2">
                                <label
                                    v-for="servicio in availableServicios"
                                    :key="servicio.id"
                                    class="flex items-center p-2 hover:bg-gray-50 rounded cursor-pointer"
                                >
                                    <input
                                        type="checkbox"
                                        :value="servicio.id"
                                        v-model="productForm.servicios"
                                        class="mr-3 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                    />
                                    <i :class="servicio.icono || 'fas fa-check-circle'" class="mr-2 text-green-500"></i>
                                    <span class="text-sm">{{ servicio.nombre }}</span>
                                </label>
                            </div>
                        </div>

                        <!-- Crear nuevo servicio -->
                        <div class="border-t pt-3">
                            <button
                                type="button"
                                @click="showNewServicioModal = true"
                                class="w-full px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition-colors text-sm flex items-center justify-center"
                            >
                                <i class="fas fa-plus mr-2"></i>
                                Crear Nuevo Servicio
                            </button>
                        </div>

                        <!-- Servicios seleccionados (ordenados) -->
                        <div v-if="productForm.servicios.length > 0" class="mt-3">
                            <p class="text-sm font-medium text-gray-700 mb-2">Servicios seleccionados (arrastra para reordenar):</p>
                            <div class="space-y-2">
                                <div
                                    v-for="(servicioId, index) in productForm.servicios"
                                    :key="servicioId"
                                    class="flex items-center p-2 bg-blue-50 rounded border border-blue-200"
                                >
                                    <i class="fas fa-grip-vertical text-gray-400 mr-2 cursor-move"></i>
                                    <i 
                                        :class="getServicioById(servicioId)?.icono || 'fas fa-check-circle'" 
                                        :style="{ color: getServicioById(servicioId)?.color || '#10b981' }"
                                        class="mr-2"
                                    ></i>
                                    <span class="flex-1 text-sm">{{ getServicioById(servicioId)?.nombre }}</span>
                                    <button
                                        type="button"
                                        @click="removeServicioFromProduct(index)"
                                        class="ml-2 text-red-500 hover:text-red-700"
                                    >
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button
                        type="button"
                        @click="handleClose"
                        class="btn btn-secondary"
                    >
                        Cancelar
                    </button>
                    <button
                        type="submit"
                        :disabled="saving"
                        class="btn btn-primary"
                    >
                        <span v-if="saving">Guardando...</span>
                        <span v-else>{{ editingProduct ? 'Actualizar' : 'Crear' }}</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- Modal para crear nuevo servicio -->
        <div v-if="showNewServicioModal" class="modal-overlay" @click.self="showNewServicioModal = false">
            <div class="modal-content max-w-md">
                <div class="modal-header">
                    <h3 class="modal-title">Crear Nuevo Servicio</h3>
                    <button @click="showNewServicioModal = false" class="modal-close-button">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <form @submit.prevent="createServicio" class="modal-body">
                    <div class="form-group">
                        <label for="servicio_nombre" class="form-label">
                            Nombre del Servicio <span class="text-red-500">*</span>
                        </label>
                        <input
                            id="servicio_nombre"
                            v-model="newServicioForm.nombre"
                            type="text"
                            class="form-input"
                            placeholder="Ej: Preparación completa"
                            required
                        />
                    </div>

                    <div class="form-group">
                        <label for="servicio_descripcion" class="form-label">
                            Descripción
                        </label>
                        <textarea
                            id="servicio_descripcion"
                            v-model="newServicioForm.descripcion"
                            class="form-input"
                            rows="3"
                            placeholder="Descripción opcional del servicio"
                        ></textarea>
                    </div>

                    <div class="form-group">
                        <label for="servicio_icono" class="form-label">
                            Icono (FontAwesome)
                        </label>
                        
                        <!-- Vista previa del icono seleccionado -->
                        <div class="mb-3 p-3 bg-gray-50 rounded-lg border border-gray-200 flex items-center justify-center">
                            <div class="text-center">
                                <i 
                                    :class="newServicioForm.icono || 'fas fa-question'" 
                                    :style="{ color: newServicioForm.color || '#ef4444' }"
                                    class="text-4xl mb-2"
                                ></i>
                                <p class="text-xs text-gray-600">{{ newServicioForm.icono || 'Ningún icono seleccionado' }}</p>
                            </div>
                        </div>

                        <!-- Selector de color -->
                        <div class="mb-3">
                            <label class="form-label mb-2 block">Color del icono</label>
                            <div class="flex items-center gap-3">
                                <input
                                    v-model="newServicioForm.color"
                                    type="color"
                                    class="h-10 w-20 rounded border border-gray-300 cursor-pointer"
                                />
                                <input
                                    v-model="newServicioForm.color"
                                    type="text"
                                    class="form-input flex-1"
                                    placeholder="#ef4444"
                                    pattern="^#[0-9A-Fa-f]{6}$"
                                />
                                <div class="flex gap-1">
                                    <button
                                        v-for="presetColor in presetColors"
                                        :key="presetColor.value"
                                        type="button"
                                        @click="newServicioForm.color = presetColor.value"
                                        :class="[
                                            'w-8 h-8 rounded border-2 transition-all',
                                            newServicioForm.color === presetColor.value ? 'border-gray-800 scale-110' : 'border-gray-300'
                                        ]"
                                        :style="{ backgroundColor: presetColor.value }"
                                        :title="presetColor.name"
                                    ></button>
                                </div>
                            </div>
                        </div>

                        <!-- Búsqueda de iconos -->
                        <div class="mb-3">
                            <input
                                v-model="iconSearchQuery"
                                type="text"
                                class="form-input"
                                placeholder="Buscar icono (ej: check, file, user)..."
                                @input="filterIcons"
                            />
                        </div>

                        <!-- Selector de iconos comunes -->
                        <div class="border border-gray-300 rounded-lg p-3 max-h-64 overflow-y-auto bg-white">
                            <div class="grid grid-cols-6 gap-2">
                                <button
                                    v-for="icon in filteredIcons"
                                    :key="icon"
                                    type="button"
                                    @click="selectIcon(icon)"
                                    :class="[
                                        'p-3 rounded-lg border-2 transition-all',
                                        newServicioForm.icono === icon ? 'border-blue-500 bg-blue-100' : 'border-gray-200 hover:bg-gray-50'
                                    ]"
                                    :title="icon"
                                >
                                    <i 
                                        :class="icon" 
                                        :style="{ color: newServicioForm.color || '#ef4444' }"
                                        class="text-xl"
                                    ></i>
                                </button>
                            </div>
                        </div>

                        <!-- Input manual (opcional) -->
                        <div class="mt-3">
                            <label class="text-xs text-gray-600 mb-1 block">O escribe manualmente:</label>
                            <input
                                id="servicio_icono"
                                v-model="newServicioForm.icono"
                                type="text"
                                class="form-input text-sm"
                                placeholder="fas fa-check-circle"
                            />
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button
                            type="button"
                            @click="showNewServicioModal = false"
                            class="btn btn-secondary"
                        >
                            Cancelar
                        </button>
                        <button
                            type="submit"
                            :disabled="savingServicio"
                            class="btn btn-primary"
                        >
                            <span v-if="savingServicio">Guardando...</span>
                            <span v-else>Crear Servicio</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script>
import { ref, reactive, watch, onMounted } from 'vue';

export default {
    name: 'ProductModal',
    props: {
        modelValue: {
            type: Boolean,
            required: true
        },
        csrf: {
            type: String,
            required: true
        },
        editingProduct: {
            type: Object,
            default: null
        }
    },
    emits: ['update:modelValue', 'saved', 'closed'],
    setup(props, { emit }) {
        const saving = ref(false);

        const productForm = reactive({
            product_name: '',
            price: null,
            commission_pct: null,
            currency: 'EUR',
            payment_type: '',
            stripe_product_id: '',
            price_id: '',
            servicios: []
        });

        // Estado para servicios
        const availableServicios = ref([]);
        const showNewServicioModal = ref(false);
        const savingServicio = ref(false);
        const newServicioForm = reactive({
            nombre: '',
            descripcion: '',
            icono: 'fas fa-check-circle',
            color: '#ef4444'
        });

        // Colores predefinidos
        const presetColors = [
            { name: 'Rojo', value: '#ef4444' },
            { name: 'Azul', value: '#3b82f6' },
            { name: 'Verde', value: '#10b981' },
            { name: 'Amarillo', value: '#f59e0b' },
            { name: 'Morado', value: '#8b5cf6' },
            { name: 'Rosa', value: '#ec4899' },
            { name: 'Naranja', value: '#f97316' },
            { name: 'Cian', value: '#06b6d4' },
            { name: 'Gris', value: '#6b7280' },
            { name: 'Negro', value: '#000000' }
        ];

        // Estado para selector de iconos
        const iconSearchQuery = ref('');
        const commonIcons = [
            'fas fa-check-circle', 'fas fa-check', 'fas fa-check-double', 'fas fa-check-square',
            'fas fa-file-alt', 'fas fa-file', 'fas fa-file-pdf', 'fas fa-file-word', 'fas fa-file-excel',
            'fas fa-folder', 'fas fa-folder-open', 'fas fa-archive',
            'fas fa-user', 'fas fa-user-check', 'fas fa-user-tie', 'fas fa-users', 'fas fa-user-friends',
            'fas fa-envelope', 'fas fa-envelope-open', 'fas fa-comments', 'fas fa-comment-dots',
            'fas fa-phone', 'fas fa-phone-alt', 'fas fa-headset',
            'fas fa-calendar', 'fas fa-calendar-check', 'fas fa-calendar-alt', 'fas fa-clock',
            'fas fa-hourglass-half', 'fas fa-stopwatch',
            'fas fa-shield-alt', 'fas fa-lock', 'fas fa-key', 'fas fa-fingerprint',
            'fas fa-credit-card', 'fas fa-money-bill', 'fas fa-money-bill-wave', 'fas fa-euro-sign',
            'fas fa-wallet', 'fas fa-receipt',
            'fas fa-bell', 'fas fa-bell-slash', 'fas fa-exclamation-circle', 'fas fa-info-circle',
            'fas fa-arrow-right', 'fas fa-arrow-left', 'fas fa-arrow-up', 'fas fa-arrow-down',
            'fas fa-search', 'fas fa-filter', 'fas fa-sort',
            'fas fa-tools', 'fas fa-wrench', 'fas fa-cog', 'fas fa-cogs', 'fas fa-screwdriver',
            'fas fa-star', 'fas fa-star-half-alt', 'fas fa-heart', 'fas fa-thumbs-up',
            'fas fa-home', 'fas fa-building', 'fas fa-map-marker-alt', 'fas fa-globe',
            'fas fa-car', 'fas fa-bus', 'fas fa-train', 'fas fa-plane',
            'fas fa-heartbeat', 'fas fa-hospital', 'fas fa-ambulance', 'fas fa-pills',
            'fas fa-graduation-cap', 'fas fa-book', 'fas fa-book-open', 'fas fa-chalkboard-teacher',
            'fas fa-laptop', 'fas fa-mobile-alt', 'fas fa-tablet-alt', 'fas fa-desktop',
            'fas fa-wifi', 'fas fa-server',
            'fas fa-handshake', 'fas fa-gift', 'fas fa-trophy', 'fas fa-medal',
            'fas fa-lightbulb', 'fas fa-magic', 'fas fa-rocket', 'fas fa-chart-line'
        ];
        const filteredIcons = ref([...commonIcons]);

        const loadServicios = async () => {
            try {
                const response = await fetch('/admin/servicios', {
                    headers: {
                        'X-CSRF-TOKEN': props.csrf,
                        'Accept': 'application/json'
                    }
                });

                if (response.ok) {
                    const data = await response.json();
                    availableServicios.value = data.servicios || [];
                } else {
                    console.error('Error cargando servicios');
                }
            } catch (error) {
                console.error('Error cargando servicios:', error);
            }
        };

        const createServicio = async () => {
            savingServicio.value = true;
            try {
                const response = await fetch('/admin/servicios', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': props.csrf,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(newServicioForm)
                });

                if (response.ok) {
                    const data = await response.json();
                    availableServicios.value.push(data.servicio);
                    productForm.servicios.push(data.servicio.id);
                    
                    newServicioForm.nombre = '';
                    newServicioForm.descripcion = '';
                    newServicioForm.icono = 'fas fa-check-circle';
                    newServicioForm.color = '#ef4444';
                    iconSearchQuery.value = '';
                    filteredIcons.value = [...commonIcons];
                    showNewServicioModal.value = false;
                } else {
                    const errorData = await response.json();
                    alert(errorData.message || 'Error al crear el servicio');
                }
            } catch (error) {
                console.error('Error creando servicio:', error);
                alert('Error al crear el servicio');
            } finally {
                savingServicio.value = false;
            }
        };

        const getServicioById = (servicioId) => {
            return availableServicios.value.find(s => s.id === servicioId);
        };

        const removeServicioFromProduct = (index) => {
            productForm.servicios.splice(index, 1);
        };

        const selectIcon = (icon) => {
            newServicioForm.icono = icon;
        };

        const filterIcons = () => {
            if (!iconSearchQuery.value.trim()) {
                filteredIcons.value = [...commonIcons];
                return;
            }

            const query = iconSearchQuery.value.toLowerCase();
            filteredIcons.value = commonIcons.filter(icon => {
                const iconName = icon.replace('fas fa-', '').replace(/-/g, ' ');
                return iconName.includes(query) || icon.includes(query);
            });
        };

        const resetForm = () => {
            productForm.product_name = '';
            productForm.price = null;
            productForm.commission_pct = null;
            productForm.currency = 'EUR';
            productForm.payment_type = '';
            productForm.stripe_product_id = '';
            productForm.price_id = '';
            productForm.servicios = [];
        };

        const populateFormFromProduct = (product) => {
            if (!product) {
                resetForm();
                return;
            }

            productForm.product_name = product.product_name || '';
            productForm.price = product.price || null;
            productForm.commission_pct = product.commission_pct || null;
            productForm.currency = product.currency || 'EUR';
            productForm.payment_type = product.payment_type || '';
            productForm.stripe_product_id = product.stripe_product_id || '';
            productForm.price_id = product.price_id || '';
            productForm.servicios = product.servicios ? product.servicios.map(s => s.id) : [];
        };

        const saveProduct = async () => {
            saving.value = true;
            try {
                const url = props.editingProduct
                    ? `/admin/products/${props.editingProduct.id}`
                    : '/admin/products';
                
                const method = props.editingProduct ? 'PATCH' : 'POST';

                const response = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': props.csrf,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(productForm)
                });

                if (response.ok) {
                    const data = await response.json();
                    emit('saved', data.product);
                    handleClose();
                } else {
                    const errorData = await response.json();
                    alert(errorData.message || 'Error al guardar el producto');
                }
            } catch (error) {
                console.error('Error guardando producto:', error);
                alert('Error al guardar el producto');
            } finally {
                saving.value = false;
            }
        };

        const handleClose = () => {
            emit('update:modelValue', false);
            emit('closed');
        };

        watch(
            () => props.editingProduct,
            (newProduct) => {
                populateFormFromProduct(newProduct);
            },
            { immediate: true }
        );

        onMounted(() => {
            loadServicios();
        });

        return {
            saving,
            productForm,
            availableServicios,
            showNewServicioModal,
            savingServicio,
            newServicioForm,
            presetColors,
            iconSearchQuery,
            filteredIcons,
            getServicioById,
            removeServicioFromProduct,
            selectIcon,
            filterIcons,
            createServicio,
            saveProduct,
            handleClose
        };
    }
};
</script>

<style scoped>
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
    padding: 1rem;
}

.modal-content {
    background: white;
    border-radius: 12px;
    width: 100%;
    max-width: 600px;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.5rem;
    border-bottom: 1px solid #e5e7eb;
}

.modal-title {
    font-size: 1.25rem;
    font-weight: 600;
    color: #111827;
}

.modal-close-button {
    background: none;
    border: none;
    font-size: 1.5rem;
    color: #6b7280;
    cursor: pointer;
    padding: 0.25rem;
    transition: color 0.2s ease;
}

.modal-close-button:hover {
    color: #111827;
}

.modal-body {
    padding: 1.5rem;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-label {
    display: block;
    font-size: 0.875rem;
    font-weight: 500;
    color: #374151;
    margin-bottom: 0.5rem;
}

.form-input {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 0.875rem;
    transition: border-color 0.2s ease;
}

.form-input:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 1rem;
    padding: 1.5rem;
    border-top: 1px solid #e5e7eb;
}

.btn {
    padding: 0.75rem 1.5rem;
    border-radius: 6px;
    font-size: 0.875rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
    border: none;
}

.btn-primary {
    background: #3b82f6;
    color: white;
}

.btn-primary:hover:not(:disabled) {
    background: #2563eb;
}

.btn-primary:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.btn-secondary {
    background: #f3f4f6;
    color: #374151;
}

.btn-secondary:hover {
    background: #e5e7eb;
}
</style>



