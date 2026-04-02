<template>
    <div>
        <input
            type="date"
            :value="value"
            @input="handleInput"
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
const props = defineProps({
    question: Object,
    questionIndex: Number,
    value: String,
    blocked: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(["update"]);

const handleInput = (event) => {
    if (!props.blocked) {
        emit("update", props.question.question.id, event.target.value);
    }
};
</script>
