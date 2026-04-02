<template>
    <div class="tech-card">
        <div class="tech-icon">{{ icon }}</div>
        <h3 class="tech-title">{{ title }}</h3>
        <p class="tech-details" v-html="formattedDetails"></p>
        <a :href="link" class="tech-button">{{ linkText }}</a>
    </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
    icon: String,
    title: String,
    details: String,
    link: String,
    linkText: String,
})

const formattedDetails = computed(() => {
    if (!props.details) return ''
    return props.details.replace(/\\n/g, '<br>').replace(/\r\n/g, '<br>').replace(/\n/g, '<br>')
})
</script>

<style scoped>
.tech-card {
    border: 1px solid rgba(84, 222, 189, 0.2);
    border-radius: 8px;
    padding: 2rem;
    text-align: center;
    transition: all 0.3s ease;
    background: rgba(84, 222, 189, 0.15);
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 1rem;
}

.tech-card:hover {
    border-color: var(--vp-c-brand-1);
    transform: translateY(-4px);
    box-shadow: 0 4px 12px rgba(84, 222, 189, 0.15);
}

.dark .tech-card {
    background: rgba(84, 222, 189, 0.1);
    border-color: rgba(84, 222, 189, 0.15);
}

.tech-icon {
    font-size: 3rem;
    line-height: 1;
}

.tech-title {
    font-size: 1.5rem;
    font-weight: 600;
    margin: 0;
    color: var(--vp-c-text-1);
}

.tech-details {
    color: var(--vp-c-text-2);
    line-height: 1.6;
    margin: 0;
    flex-grow: 1;
}

.tech-button {
    display: inline-block;
    padding: 0.75rem 1.5rem;
    background: var(--vp-c-brand-1);
    color: white;
    text-decoration: none;
    border-radius: 6px;
    font-weight: 500;
    transition: all 0.2s ease;
    margin-top: 0.5rem;
}

.tech-button:hover {
    background: var(--vp-c-brand-2);
    transform: scale(1.05);
    color: white;
}
</style>
