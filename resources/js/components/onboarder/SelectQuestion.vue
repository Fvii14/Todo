<template>
    <div>
        <select
            :value="value"
            @change="handleChange"
            :disabled="blocked"
            :class="[
                'w-full px-4 py-3 border rounded-lg focus:outline-none focus:ring-2 focus:border-transparent transition-colors',
                blocked
                    ? 'border-yellow-300 bg-yellow-50 text-gray-600 cursor-not-allowed'
                    : 'border-gray-300 focus:ring-blue-500',
            ]"
        >
            <option value="">Selecciona una opción...</option>
            <option
                v-for="option in options"
                :key="option"
                :value="option"
            >
                {{ option }}
            </option>
        </select>
    </div>
</template>

<script setup>
const props = defineProps({
    question: Object,
    questionIndex: Number,
    value: String,
    blocked: {
        type: Boolean,
        default: false,
    },
    options: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits(["update"]);

const handleChange = (event) => {
    if (!props.blocked) {
        emit("update", props.question.question.id, event.target.value);
    }
};
</script>
