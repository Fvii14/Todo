<template>
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <!-- Header con gradiente -->
        <div
            class="bg-gradient-to-r from-green-600 to-green-800 text-white p-6"
        >
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold">
                        {{ wizard.title || "Wizard Collector" }}
                    </h2>
                    <p class="text-green-100 mt-1">
                        <i :class="currentStepData.icon" class="mr-2"></i>
                        {{ currentStepData.title }} - Paso {{ currentStep }} de
                        {{ steps.length }}
                    </p>
                </div>
                <div class="flex items-center space-x-2">
                    <button
                        @click="saveDraft"
                        :disabled="savingDraft"
                        class="px-4 py-2 bg-green-700 hover:bg-green-800 rounded-md text-sm font-medium transition-colors disabled:opacity-50"
                    >
                        <i class="fas fa-save mr-2"></i>
                        {{ savingDraft ? "Guardando..." : "Guardar borrador" }}
                    </button>
                </div>
            </div>

            <!-- Barra de progreso -->
            <div class="mt-4">
                <div class="w-full bg-green-700 rounded-full h-2">
                    <div
                        class="bg-white h-2 rounded-full transition-all duration-500 ease-out"
                        :style="{ width: progressPercentage + '%' }"
                    ></div>
                </div>
            </div>
        </div>

        <!-- Pasos clickeables -->
        <div class="bg-gray-50 p-4 border-b">
            <div class="grid grid-cols-4 gap-2">
                <button
                    v-for="(step, index) in steps"
                    :key="index + 1"
                    @click="goToStep(index + 1)"
                    :class="[
                        'px-3 py-2 rounded-md text-sm font-medium transition-colors whitespace-nowrap',
                        currentStep === index + 1
                            ? 'bg-green-600 text-white shadow-md'
                            : 'bg-white text-gray-600 hover:bg-gray-100',
                    ]"
                >
                    <i
                        class="fas fa-circle mr-2"
                        :class="
                            currentStep >= index + 1
                                ? 'text-white'
                                : 'text-gray-400'
                        "
                    ></i>
                    {{ step.title }}
                </button>
            </div>
        </div>

        <!-- Contenido del wizard -->
        <div class="p-6">
            <!-- Step 1: Configuración Básica -->
            <div v-if="currentStep === 1">
                <h3 class="text-xl font-semibold text-gray-900 mb-6">
                    <i class="fas fa-cog text-green-600 mr-2"></i>
                    Configuración Básica del Collector
                </h3>

                <div class="space-y-6">
                    <!-- Nombre del collector -->
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                        >
                            Nombre del Collector
                            <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model="formData.name"
                            type="text"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                            placeholder="Ej: Collector para gente de bankflip con NIE"
                            required
                        />
                    </div>

                    <!-- Descripción -->
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                        >
                            Descripción <span class="text-red-500">*</span>
                        </label>
                        <textarea
                            v-model="formData.description"
                            rows="3"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                            placeholder="Describe el propósito de este formulario collector"
                            required
                        ></textarea>
                    </div>
                </div>
            </div>

            <div v-if="currentStep === 2">
                <h3 class="text-xl font-semibold text-gray-900 mb-6">
                    <i class="fas fa-question-circle text-green-600 mr-2"></i>
                    Solicitante
                </h3>

                <WizardCollectorStep2
                    :sections="formData.sections"
                    @update:sections="updateSections"
                    @update:convivienteTypes="updateConvivienteTypes"
                    @show-notification="showNotification"
                />
            </div>

            <div v-if="currentStep === 3">
                <h3 class="text-xl font-semibold text-gray-900 mb-6">
                    <i class="fas fa-home text-green-600 mr-2"></i>
                    Unidad de convivencia
                </h3>

                <WizardCollectorStep3
                    :convivienteTypes="formData.convivienteTypes"
                    :sections="formData.sections"
                    @update:convivienteTypes="updateConvivienteTypes"
                    @show-notification="showNotification"
                />
            </div>
        </div>

        <div v-if="currentStep === 4" class="space-y-6">
            <WizardCollectorStep4
                :wizard-data="{ ...formData, id: wizard.id }"
                :sections="formData.sections"
                :conviviente-types="formData.convivienteTypes"
                @go-to-step="goToStep"
                @show-notification="showNotification"
            />
        </div>

        <!-- Navigation Buttons -->
        <div class="flex justify-between mt-8 px-6 pb-6">
            <button
                v-if="currentStep > 1"
                @click="previousStep"
                class="bg-gray-600 text-white px-6 py-2 rounded-md hover:bg-gray-700 transition-colors"
            >
                <i class="fas fa-arrow-left mr-2"></i>Anterior
            </button>
            <div></div>

            <button
                v-if="currentStep < steps.length"
                @click="nextStep"
                :disabled="!canProceedToNextStep"
                class="bg-green-600 text-white px-6 py-2 rounded-md hover:bg-green-700 transition-colors disabled:opacity-50"
            >
                Siguiente<i class="fas fa-arrow-right ml-2"></i>
            </button>
            <button
                v-else-if="currentStep === steps.length"
                @click="nextStep"
                :disabled="!canProceedToNextStep || finalizing"
                class="bg-green-700 text-white px-6 py-2 rounded-md hover:bg-green-800 transition-colors disabled:opacity-50"
            >
                <i v-if="finalizing" class="fas fa-spinner fa-spin mr-2"></i>
                <i v-else class="fas fa-check mr-2"></i>
                {{ finalizing ? 'Finalizando...' : 'Finalizar wizard' }}
            </button>
        </div>

        <!-- Notifications -->
        <div
            v-if="notification.show"
            :class="[
                'fixed top-4 right-4 p-4 rounded-md shadow-lg z-50 max-w-md',
                notification.type === 'success'
                    ? 'bg-green-500 text-white'
                    : '',
                notification.type === 'error' ? 'bg-red-500 text-white' : '',
                notification.type === 'warning'
                    ? 'bg-yellow-500 text-white'
                    : '',
            ]"
        >
            <div class="flex items-center">
                <i :class="notificationIcon" class="mr-2"></i>
                <div>
                    <div class="font-medium">{{ notification.title }}</div>
                    <div class="text-sm opacity-90">
                        {{ notification.message }}
                    </div>
                </div>
                <button
                    @click="notification.show = false"
                    class="ml-4 opacity-70 hover:opacity-100"
                >
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import WizardCollectorStep2 from "./WizardCollectorStep2.vue";
import WizardCollectorStep3 from "./WizardCollectorStep3.vue";
import WizardCollectorStep4 from "./WizardCollectorStep4.vue";

const props = defineProps({
    wizard: {
        type: Object,
        required: true,
    },
});

// Reactive data
const currentStep = ref(1);
const savingDraft = ref(false);
const finalizing = ref(false);

const notification = ref({
    show: false,
    type: "success",
    title: "",
    message: "",
});

// Steps configuration
const steps = [
    { title: "Nombre y descripción", icon: "fas fa-cog" },
    { title: "Secciones y preguntas de solicitante", icon: "fas fa-flag" },
    { title: "Secciones y preguntas de unidad de convivencia", icon: "fas fa-home" },
    { title: "Revisión final", icon: "fas fa-check-circle" },
];

// Form data
const formData = ref({
    name: "",
    description: "",
    sections: [],
    convivienteTypes: [],
});

// Computed properties
const currentStepData = computed(() => {
    return steps[currentStep.value - 1] || steps[0];
});

const progressPercentage = computed(() => {
    return (currentStep.value / steps.length) * 100;
});

const canProceedToNextStep = computed(() => {
    if (currentStep.value === 1) {
        return (
            formData.value.name.trim() !== "" &&
            formData.value.description.trim() !== ""
        );
    }
    if (currentStep.value === 2) {
        return formData.value.sections.length > 0;
    }
    if (currentStep.value === 3) {
        return formData.value.convivienteTypes.length > 0;
    }
    return true;
});

const notificationIcon = computed(() => {
    switch (notification.value.type) {
        case "success":
            return "fas fa-check-circle";
        case "error":
            return "fas fa-exclamation-circle";
        case "warning":
            return "fas fa-exclamation-triangle";
        default:
            return "fas fa-info-circle";
    }
});

// Methods
const goToStep = (step) => {
    if (step >= 1 && step <= steps.length) {
        currentStep.value = step;
        saveWizard();
    }
};

const nextStep = async () => {
    if (currentStep.value < steps.length) {
        currentStep.value++;
        saveWizard();
    } else if (currentStep.value === steps.length) {
        await finalizeWizard();
    }
};

const previousStep = () => {
    if (currentStep.value > 1) {
        currentStep.value--;
        saveWizard();
    }
};

const saveWizard = async () => {
    try {
        const response = await fetch(`/admin/wizards/${props.wizard.id}`, {
            method: "PUT",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document
                    .querySelector('meta[name="csrf-token"]')
                    .getAttribute("content"),
            },
            body: JSON.stringify({
                data: formData.value,
                current_step: currentStep.value,
            }),
        });

        if (!response.ok) {
            throw new Error("Error al guardar el wizard");
        }
    } catch (error) {
        showNotification("error", "Error", "No se pudo guardar el progreso");
    }
};

const saveDraft = async () => {
    savingDraft.value = true;
    try {
        const response = await fetch(
            `/admin/wizards/${props.wizard.id}/draft`,
            {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document
                        .querySelector('meta[name="csrf-token"]')
                        .getAttribute("content"),
                },
                body: JSON.stringify({
                    data: formData.value,
                }),
            },
        );

        if (response.ok) {
            showNotification(
                "success",
                "Éxito",
                "Borrador guardado correctamente",
            );
        } else {
            throw new Error("Error al guardar el borrador");
        }
    } catch (error) {
        showNotification("error", "Error", "No se pudo guardar el borrador");
    } finally {
        savingDraft.value = false;
    }
};

const showNotification = (type, title, message) => {
    notification.value = {
        show: true,
        type,
        title,
        message,
    };

    setTimeout(() => {
        notification.value.show = false;
    }, 5000);
};

const updateSections = (newSections) => {
    formData.value.sections = newSections;
};

const updateConvivienteTypes = (newConvivienteTypes) => {
    formData.value.convivienteTypes = newConvivienteTypes;
};

const finalizeWizard = async () => {
    finalizing.value = true;
    try {
        const hasBuilders = formData.value.sections.some(section => 
            section.questions && section.questions.some(question => question.type === 'builder')
        ) || formData.value.convivienteTypes.some(type => 
            type.sections && type.sections.some(section => 
                section.questions && section.questions.some(question => question.type === 'builder')
            )
        );

        let builderIdMap = {};
        if (hasBuilders) {
            const builderIdsResponse = await fetch('/admin/questions?type=builder', {
                headers: {
                    'Accept': 'application/json',
                },
            });

            if (builderIdsResponse.ok) {
                const builderData = await builderIdsResponse.json();
                builderIdMap = builderData.questions.reduce((acc, question) => {
                    acc[question.slug] = question.id;
                    return acc;
                }, {});
            }
        }

        const requestData = {
            sections: formData.value.sections.map((section, index) => ({
                name: section.name,
                description: section.description || '',
                order: index,
                skip_condition: section.skipCondition || null,
                is_required: section.isRequired !== false,
                is_skippeable: section.isSkippeable || (section.skipCondition ? true : false),
                questions: (section.questions || []).map((question, qIndex) => {
                    if (question.type === 'builder') {
                        const realQuestionId = builderIdMap[question.slug];
                        return {
                            question_id: parseInt(realQuestionId || question.id),
                            builder_slug: question.slug,
                            order: qIndex,
                            screen: Number(question.screen ?? 0),
                            condition: question.condition || null,
                            required_condition: question.requiredCondition || null,
                            optional_condition: question.optionalCondition || null,
                            block_if_bankflip_filled: question.blockIfBankflipFilled || false,
                            hide_if_bankflip_filled: question.hideIfBankflipFilled || false,
                            is_builder: true,
                            isRequired: question.isRequired || false
                        };
                    } else {
                        return {
                            question_id: parseInt(question.id),
                            builder_slug: null,
                            order: qIndex,
                            screen: Number(question.screen ?? 0),
                            condition: question.condition || null,
                            required_condition: question.requiredCondition || null,
                            optional_condition: question.optionalCondition || null,
                            block_if_bankflip_filled: question.blockIfBankflipFilled || false,
                            hide_if_bankflip_filled: question.hideIfBankflipFilled || false,
                            show_if_bankflip_filled: question.showIfBankflipFilled === null ? null : question.showIfBankflipFilled,
                            is_builder: false,
                            options: question.options || null,
                            conditional_options: question.conditionalOptions || null,
                            selected_options: question.selectedOptions || null,
                            isRequired: question.isRequired || false
                        };
                    }
                })
            })),
            conviviente_types: formData.value.convivienteTypes.map((type, index) => ({
                name: type.name,
                description: type.description || '',
                icon: type.icon || 'fas fa-user',
                order: index,
                sections: (type.sections || []).map((section, sIndex) => ({
                    name: section.name,
                    description: section.description || '',
                    order: sIndex,
                    skip_condition: section.skipCondition || null,
                    is_required: section.isRequired !== false,
                    is_skippeable: section.isSkippeable || (section.skipCondition ? true : false),
                    questions: (section.questions || []).map((question, qIndex) => {
                        if (question.type === 'builder') {
                            return {
                                question_id: parseInt(builderIdMap[question.slug] || question.id),
                                builder_slug: question.slug,
                                order: qIndex,
                                screen: Number(question.screen ?? 0),
                                condition: question.condition || null,
                                required_condition: question.requiredCondition || null,
                                optional_condition: question.optionalCondition || null,
                                block_if_bankflip_filled: question.blockIfBankflipFilled || false,
                                hide_if_bankflip_filled: question.hideIfBankflipFilled || false,
                                is_builder: true,
                                isRequired: question.isRequired || false
                            };
                        } else {
                            return {
                                question_id: parseInt(question.id),
                                builder_slug: null,
                                order: qIndex,
                                screen: Number(question.screen ?? 0),
                                condition: question.condition || null,
                                required_condition: question.requiredCondition || null,
                                optional_condition: question.optionalCondition || null,
                                block_if_bankflip_filled: question.blockIfBankflipFilled || false,
                                hide_if_bankflip_filled: question.hideIfBankflipFilled || false,
                                show_if_bankflip_filled: question.showIfBankflipFilled === null ? null : question.showIfBankflipFilled,
                                is_builder: false,
                                options: question.options || null,
                                conditional_options: question.conditionalOptions || null,
                                selected_options: question.selectedOptions || null,
                                isRequired: question.isRequired || false
                            };
                        }
                    })
                }))
            }))
        };

        const configResponse = await fetch(`/onboarders/wizards/${props.wizard.id}/onboarder-config`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Authorization': `Bearer ${localStorage.getItem('token') || ''}`
            },
            body: JSON.stringify(requestData)
        });

        if (!configResponse.ok) {
            const errorData = await configResponse.json();
            throw new Error(errorData.message || 'Error al guardar la configuración del onboarder');
        }

        const saveWizardResponse = await fetch(`/admin/wizards/${props.wizard.id}`, {
            method: "PUT",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document
                    .querySelector('meta[name="csrf-token"]')
                    .getAttribute("content"),
            },
            body: JSON.stringify({
                data: formData.value,
                current_step: steps.length,
                status: 'completed'
            }),
        });

        if (!saveWizardResponse.ok) {
            throw new Error("Error al guardar el wizard final");
        }

        showNotification(
            "success",
            "¡Felicidades!",
            "Wizard finalizado correctamente. El sistema de onboarders está listo para usar.",
        );

        setTimeout(() => {
            window.location.href = '/admin/wizards';
        }, 2000);

    } catch (error) {
        console.error('Error finalizing wizard:', error);
        showNotification(
            "error",
            "Error",
            error.message || "Error al finalizar el wizard",
        );
    } finally {
        finalizing.value = false;
    }
};

const normalizeQuestions = (sections) => {
    return sections.map(section => ({
        ...section,
        questions: (section.questions || []).map(question => ({
            ...question,
            blockIfBankflipFilled: question.blockIfBankflipFilled || false,
            hideIfBankflipFilled: question.hideIfBankflipFilled || false,
        }))
    }));
};

// Lifecycle
onMounted(() => {
    // Load initial data if wizard has data
    if (props.wizard.data) {
        formData.value = {
            name: "",
            description: "",
            sections: [],
            convivienteTypes: [],
            ...props.wizard.data,
        };

        if (formData.value.sections) {
            formData.value.sections = normalizeQuestions(formData.value.sections);
        }
        if (formData.value.convivienteTypes) {
            formData.value.convivienteTypes = formData.value.convivienteTypes.map(type => ({
                ...type,
                sections: normalizeQuestions(type.sections || [])
            }));
        }
        
        currentStep.value = props.wizard.current_step || 1;
    }
});
</script>
