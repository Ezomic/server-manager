<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps<{
    values: number[];
    max?: number;
}>();

const points = computed(() => {
    if (props.values.length < 2) {
        return '';
    }

    const max = props.max ?? Math.max(...props.values, 1);
    const step = 100 / (props.values.length - 1);

    return props.values
        .map((value, index) => {
            const x = (index * step).toFixed(2);
            const y = (36 - (Math.min(value, max) / max) * 32).toFixed(2);

            return `${x},${y}`;
        })
        .join(' ');
});
</script>

<template>
    <svg viewBox="0 0 100 40" preserveAspectRatio="none" class="h-10 w-full">
        <polyline
            :points="points"
            fill="none"
            stroke="currentColor"
            stroke-width="1.5"
            vector-effect="non-scaling-stroke"
        />
    </svg>
</template>
