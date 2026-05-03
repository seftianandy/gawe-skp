<script setup lang="ts">
import type { HTMLAttributes } from 'vue';

defineOptions({
    inheritAttrs: false,
});

type Props = {
    className?: HTMLAttributes['class'];
};

defineProps<Props>();
</script>

<template>
    <svg
        xmlns="http://www.w3.org/2000/svg"
        viewBox="0 0 40 42"
        class="gawe-skp-logo"
    >
        <!-- Layer Utama: Hijau Vue.js (#42b883) -->
        <path
            fill="#42b883"
            fill-rule="evenodd"
            clip-rule="evenodd"
            d="M20 0L2.68 10v22L20 42l17.32-10V10L20 0zm13.32 29.85L20 37.55l-13.32-7.7V12.15L20 4.45l13.32 7.7v17.7z"
        />
        <!-- Layer Detail: Hijau Gelap / Slate (#35495e) -->
        <path
            fill="#35495e"
            d="M20 12l-8 4.5v9l8 4.5 8-4.5V21h-3v3.25L20 27l-5-2.85V17.8l5-2.85 5 2.85V19h3v-2.5L20 12z"
        />
        <!-- Titik Aksen -->
        <circle cx="20" cy="21" r="2" fill="#42b883" />
    </svg>

</template>
