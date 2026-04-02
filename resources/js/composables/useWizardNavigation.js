import { ref, computed } from 'vue';
import { TOTAL_STEPS, getStepData } from '../constants/wizardSteps';

/**
 * Composable para manejar la navegación del wizard
 */
export function useWizardNavigation(initialStep = 1) {
    const currentStep = ref(Number(initialStep) || 1);

    const totalSteps = computed(() => TOTAL_STEPS);
    
    const stepsArray = computed(() => {
        return Array.from({ length: totalSteps.value }, (_, i) => i + 1);
    });

    const currentStepData = computed(() => {
        return getStepData(currentStep.value);
    });

    const progressPercentage = computed(() => {
        return (currentStep.value / totalSteps.value) * 100;
    });

    const nextStep = () => {
        if (currentStep.value < totalSteps.value) {
            currentStep.value++;
        }
    };

    const previousStep = () => {
        if (currentStep.value > 1) {
            currentStep.value--;
        }
    };

    const goToStep = (step) => {
        if (step >= 1 && step <= totalSteps.value) {
            currentStep.value = step;
        }
    };

    return {
        currentStep,
        totalSteps,
        stepsArray,
        currentStepData,
        progressPercentage,
        nextStep,
        previousStep,
        goToStep
    };
}

