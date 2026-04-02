<template>
    <div>
        <input
            :type="inputType"
            :value="value"
            @input="handleInput"
            :placeholder="placeholder"
            :disabled="blocked"
            :class="[
                'w-full px-4 py-3 border rounded-lg focus:outline-none focus:ring-2 focus:border-transparent transition-colors',
                blocked
                    ? 'border-yellow-300 bg-yellow-50 text-gray-600 cursor-not-allowed'
                    : 'border-gray-300 focus:ring-blue-500',
            ]"
        />
    </div>
</template>

<script setup>
import { computed } from "vue";

const props = defineProps({
    question: Object,
    questionIndex: Number,
    value: [String, Number],
    blocked: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(["update"]);

const inputType = computed(() => {
    return props.question.question?.type === "number" ? "number" : "text";
});

const placeholder = computed(() => {
    return `Ingresa tu ${props.question.question?.text?.toLowerCase() || "respuesta"}...`;
});

const handleInput = (event) => {
    if (!props.blocked) {
        emit("update", props.question.question.id, event.target.value);
    }
};
</script>
