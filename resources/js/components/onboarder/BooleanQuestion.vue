<template>
    <div class="space-y-3">
        <div class="flex space-x-4">
            <label
                :class="[
                    'flex items-center',
                    blocked ? 'cursor-not-allowed' : 'cursor-pointer',
                ]"
            >
                <input
                    type="radio"
                    :name="`question-${question.question.id}`"
                    :value="true"
                    :checked="value === 'Sí'"
                    @change="handleChange('Sí')"
                    :disabled="blocked"
                    :class="[
                        'w-4 h-4 text-blue-600 focus:ring-blue-500 border-gray-300',
                        blocked ? 'opacity-50 cursor-not-allowed' : '',
                    ]"
                />
                <span
                    :class="[
                        'ml-2 font-medium',
                        blocked ? 'text-gray-500' : 'text-gray-700',
                    ]"
                    >Sí</span
                >
            </label>

            <label
                :class="[
                    'flex items-center',
                    blocked ? 'cursor-not-allowed' : 'cursor-pointer',
                ]"
            >
                <input
                    type="radio"
                    :name="`question-${question.question.id}`"
                    :value="false"
                    :checked="value === 'No'"
                    @change="handleChange('No')"
                    :disabled="blocked"
                    :class="[
                        'w-4 h-4 text-blue-600 focus:ring-blue-500 border-gray-300',
                        blocked ? 'opacity-50 cursor-not-allowed' : '',
                    ]"
                />
                <span
                    :class="[
                        'ml-2 font-medium',
                        blocked ? 'text-gray-500' : 'text-gray-700',
                    ]"
                    >No</span
                >
            </label>
        </div>
    </div>
</template>

<script setup>
const props = defineProps({
    question: Object,
    questionIndex: Number,
    value: [Boolean, String],
    blocked: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(["update"]);

const handleChange = (value) => {
    if (!props.blocked) {
        emit("update", props.question.question.id, value);
    }
};
</script>
