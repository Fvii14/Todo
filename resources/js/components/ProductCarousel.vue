<template>
    <div class="product-carousel-container">
        <h3 class="text-xl font-semibold text-gray-900 mb-6">
            <i class="fas fa-shopping-cart text-blue-600 mr-2"></i>
            Seleccionar Productos para la Ayuda
        </h3>

        <div v-if="loading" class="text-center py-12">
            <div
                class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto"
            ></div>
            <p class="mt-4 text-gray-600">Cargando productos...</p>
        </div>

        <div v-else class="product-carousel-wrapper">
            <div class="product-carousel" ref="carouselRef">
                <!-- Tarjetas de productos -->
                <div
                    v-for="product in products"
                    :key="product.id"
                    class="product-card"
                    :class="{ selected: isSelected(product.id) }"
                >
                    <div class="product-card-content">
                        <div class="product-card-header">
                            <input
                                type="checkbox"
                                :checked="isSelected(product.id)"
                                @click.stop="toggleProduct(product.id)"
                                class="product-checkbox"
                            />
                            <div v-if="isSelected(product.id)" class="flex items-center gap-2">
                                <label class="flex items-center cursor-pointer">
                                    <input
                                        type="checkbox"
                                        :checked="isRecomendado(product.id)"
                                        @click.stop="toggleRecomendado(product.id)"
                                        class="mr-1 rounded border-gray-300 text-yellow-600 focus:ring-yellow-500"
                                    />
                                    <span class="text-xs text-yellow-600 font-medium">
                                        <i class="fas fa-star mr-1"></i>Recomendado
                                    </span>
                                </label>
                            </div>
                            <button
                                @click.stop="openEditModal(product)"
                                class="edit-button"
                                title="Editar producto"
                            >
                                <i class="fas fa-edit"></i>
                            </button>
                        </div>
                        <div class="product-card-body">
                            <h4 class="product-name">{{ product.product_name }}</h4>
                            <div class="product-details">
                                <p v-if="product.price" class="product-price">
                                    <i class="fas fa-euro-sign mr-1"></i>
                                    {{ formatPrice(product.price) }}
                                </p>
                                <p
                                    v-if="
                                        product.commission_pct !== null &&
                                        product.commission_pct !== undefined
                                    "
                                    class="product-commission"
                                >
                                    Comisión: {{ product.commission_pct }}%
                                </p>
                                <p v-if="product.currency" class="product-currency">
                                    {{ product.currency }}
                                </p>
                                <p v-if="product.payment_type" class="product-payment-type">
                                    <i class="fas fa-credit-card mr-1"></i>
                                    {{ getPaymentTypeLabel(product.payment_type) }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tarjeta para crear nuevo producto -->
                <div class="product-card add-product-card" @click="openCreateModal">
                    <div class="product-card-content">
                        <div class="add-product-icon">
                            <i class="fas fa-plus"></i>
                        </div>
                        <p class="add-product-text">Crear Producto</p>
                    </div>
                </div>
            </div>

            <!-- Botones de navegación del carrusel -->
            <button
                v-if="canScrollLeft"
                @click="scrollLeft"
                class="carousel-nav-button carousel-nav-left"
                aria-label="Desplazar izquierda"
            >
                <i class="fas fa-chevron-left"></i>
            </button>
            <button
                v-if="canScrollRight"
                @click="scrollRight"
                class="carousel-nav-button carousel-nav-right"
                aria-label="Desplazar derecha"
            >
                <i class="fas fa-chevron-right"></i>
            </button>
        </div>

        <!-- Modal para crear/editar producto (componente hijo) -->
        <ProductModal
            v-model="showModal"
            :csrf="csrf"
            :editing-product="editingProduct"
            @saved="handleProductSaved"
            @closed="handleModalClosed"
        />

        <!-- Resumen de productos seleccionados -->
        <div v-if="selectedProductIds.length > 0" class="selected-products-summary">
            <p class="summary-text">
                <i class="fas fa-check-circle text-green-600 mr-2"></i>
                {{ selectedProductIds.length }} producto(s) seleccionado(s)
            </p>
        </div>
    </div>
</template>

<script>
import { ref, onMounted, watch } from 'vue'
import ProductModal from './ProductModal.vue'

export default {
    name: 'ProductCarousel',
    components: {
        ProductModal,
    },
    props: {
        selectedProductIds: {
            type: Array,
            default: () => [],
        },
        csrf: {
            type: String,
            required: true,
        },
    },
    emits: ['update:selected-product-ids'],
    setup(props, { emit }) {
        const products = ref([])
        const loading = ref(true)
        const showModal = ref(false)
        const editingProduct = ref(null)
        const carouselRef = ref(null)
        const canScrollLeft = ref(false)
        const canScrollRight = ref(false)

        // Helper para obtener el ID del producto
        const isProductId = (p) => p.product_id || p.id || p

        // Convertir selectedProductIds a formato de objetos si viene como array simple
        const selectedProductIds = ref(
            props.selectedProductIds.map((id) =>
                typeof id === 'object' ? id : { product_id: id, recomendado: false },
            ),
        )

        // Computed
        const isSelected = (productId) => {
            return selectedProductIds.value.some((p) => isProductId(p) === productId)
        }

        const isRecomendado = (productId) => {
            const product = selectedProductIds.value.find((p) => isProductId(p) === productId)
            return product?.recomendado || false
        }

        // Métodos
        const loadProducts = async () => {
            loading.value = true
            try {
                const response = await fetch('/admin/products', {
                    headers: {
                        'X-CSRF-TOKEN': props.csrf,
                        Accept: 'application/json',
                    },
                })

                if (response.ok) {
                    const data = await response.json()
                    products.value = data.products || []
                } else {
                    console.error('Error cargando productos')
                }
            } catch (error) {
                console.error('Error cargando productos:', error)
            } finally {
                loading.value = false
                setTimeout(checkScrollButtons, 100)
            }
        }

        const toggleProduct = (productId) => {
            const index = selectedProductIds.value.findIndex((p) => isProductId(p) === productId)
            if (index > -1) {
                selectedProductIds.value.splice(index, 1)
            } else {
                selectedProductIds.value.push({
                    product_id: productId,
                    recomendado: false,
                })
            }
            emitSelection()
        }

        const toggleRecomendado = (productId) => {
            const product = selectedProductIds.value.find((p) => isProductId(p) === productId)
            if (product) {
                product.recomendado = !product.recomendado
                emitSelection()
            }
        }

        const emitSelection = () => {
            // Enviar en formato que espera el backend
            emit(
                'update:selected-product-ids',
                selectedProductIds.value.map((p) => ({
                    product_id: isProductId(p),
                    recomendado: p.recomendado || false,
                })),
            )
        }

        const openCreateModal = () => {
            editingProduct.value = null
            showModal.value = true
        }

        const openEditModal = (product) => {
            editingProduct.value = product
            showModal.value = true
        }

        const handleProductSaved = (product) => {
            const index = products.value.findIndex((p) => p.id === product.id)
            if (index > -1) {
                products.value[index] = product
            } else {
                products.value.push(product)
                // Seleccionar automáticamente el nuevo producto
                selectedProductIds.value.push({ product_id: product.id, recomendado: false })
                emitSelection()
            }
        }

        const handleModalClosed = () => {
            editingProduct.value = null
        }

        const formatPrice = (price) => {
            if (!price) return '0.00'
            // El precio está en centavos en la BD, convertir a euros dividiendo por 100
            return (parseFloat(price) / 100).toFixed(2)
        }

        const getPaymentTypeLabel = (paymentType) => {
            const labels = {
                monthly: 'Mensual',
                annual: 'Anual',
                one_time: 'Pago Único',
                'one-time': 'Pago Único', // Por si acaso hay variación
            }
            return labels[paymentType] || paymentType
        }

        const checkScrollButtons = () => {
            if (!carouselRef.value) return

            const container = carouselRef.value
            canScrollLeft.value = container.scrollLeft > 0
            canScrollRight.value =
                container.scrollLeft < container.scrollWidth - container.clientWidth - 10
        }

        const scrollLeft = () => {
            if (carouselRef.value) {
                carouselRef.value.scrollBy({ left: -300, behavior: 'smooth' })
                setTimeout(checkScrollButtons, 300)
            }
        }

        const scrollRight = () => {
            if (carouselRef.value) {
                carouselRef.value.scrollBy({ left: 300, behavior: 'smooth' })
                setTimeout(checkScrollButtons, 300)
            }
        }

        // Watch para sincronizar con props
        watch(
            () => props.selectedProductIds,
            (newIds) => {
                selectedProductIds.value = [...newIds]
            },
            { deep: true },
        )

        // Lifecycle
        onMounted(() => {
            loadProducts()
            if (carouselRef.value) {
                carouselRef.value.addEventListener('scroll', checkScrollButtons)
                window.addEventListener('resize', checkScrollButtons)
            }
        })

        return {
            products,
            loading,
            showModal,
            editingProduct,
            selectedProductIds,
            carouselRef,
            canScrollLeft,
            canScrollRight,
            isSelected,
            toggleProduct,
            openCreateModal,
            openEditModal,
            handleProductSaved,
            handleModalClosed,
            getPaymentTypeLabel,
            formatPrice,
            scrollLeft,
            scrollRight,
            isRecomendado,
            toggleRecomendado,
        }
    },
}
</script>

<style scoped>
.product-carousel-container {
    width: 100%;
}

.product-carousel-wrapper {
    position: relative;
    width: 100%;
}

.product-carousel {
    display: flex;
    gap: 1.5rem;
    overflow-x: auto;
    overflow-y: hidden;
    padding: 1rem 0;
    scroll-behavior: smooth;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: thin;
    scrollbar-color: #cbd5e0 #f7fafc;
}

.product-carousel::-webkit-scrollbar {
    height: 8px;
}

.product-carousel::-webkit-scrollbar-track {
    background: #f7fafc;
    border-radius: 4px;
}

.product-carousel::-webkit-scrollbar-thumb {
    background: #cbd5e0;
    border-radius: 4px;
}

.product-carousel::-webkit-scrollbar-thumb:hover {
    background: #a0aec0;
}

.product-card {
    flex: 0 0 280px;
    min-width: 280px;
    background: white;
    border: 2px solid #e5e7eb;
    border-radius: 12px;
    padding: 1.5rem;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.product-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
    border-color: #3b82f6;
}

.product-card.selected {
    border-color: #3b82f6;
    background: #eff6ff;
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.2);
}

.product-card-content {
    display: flex;
    flex-direction: column;
    height: 100%;
}

.product-card-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1rem;
}

.product-checkbox {
    width: 20px;
    height: 20px;
    cursor: pointer;
    accent-color: #3b82f6;
}

.edit-button {
    background: #f3f4f6;
    border: none;
    border-radius: 6px;
    padding: 0.5rem;
    cursor: pointer;
    color: #6b7280;
    transition: all 0.2s ease;
}

.edit-button:hover {
    background: #e5e7eb;
    color: #3b82f6;
}

.product-card-body {
    flex: 1;
}

.product-name {
    font-size: 1.125rem;
    font-weight: 600;
    color: #111827;
    margin-bottom: 0.75rem;
}

.product-details {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    font-size: 0.875rem;
    color: #6b7280;
}

.product-price {
    font-weight: 600;
    color: #059669;
    font-size: 1rem;
}

.add-product-card {
    display: flex;
    align-items: center;
    justify-content: center;
    border: 2px dashed #cbd5e0;
    background: #f9fafb;
    min-height: 200px;
}

.add-product-card:hover {
    border-color: #3b82f6;
    background: #eff6ff;
}

.add-product-icon {
    font-size: 3rem;
    color: #3b82f6;
    margin-bottom: 0.5rem;
}

.add-product-text {
    font-size: 1rem;
    font-weight: 500;
    color: #6b7280;
}

.carousel-nav-button {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    background: white;
    border: 2px solid #e5e7eb;
    border-radius: 50%;
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    z-index: 10;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    transition: all 0.2s ease;
}

.carousel-nav-button:hover {
    background: #3b82f6;
    color: white;
    border-color: #3b82f6;
}

.carousel-nav-left {
    left: -20px;
}

.carousel-nav-right {
    right: -20px;
}

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

.selected-products-summary {
    margin-top: 1.5rem;
    padding: 1rem;
    background: #f0fdf4;
    border: 1px solid #86efac;
    border-radius: 8px;
}

.summary-text {
    font-size: 0.875rem;
    color: #166534;
    font-weight: 500;
}
</style>
