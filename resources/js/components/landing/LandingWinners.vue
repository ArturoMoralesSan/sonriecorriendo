<script setup lang="ts">
import {
    ArrowRight,
} from 'lucide-vue-next';

const resultsUrl = 'https://TU-ENLACE-DE-RESULTADOS.com';
</script>

<template>
    <section
        id="ganadores"
        class="landing-winners-section"
    >
        <div class="landing-container">
            <div class="landing-winners-content">
                <span class="landing-winners-label">
                    RESULTADOS
                </span>

                <h2>
                    Conoce nuestros ganadores 2026
                </h2>

                <p class="landing-winners-description">
                    Descubre a quienes llevaron cada kilómetro
                    hasta el límite y conoce los resultados
                    de la edición 2026.
                </p>

                <a
                    :href="resultsUrl"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="landing-winners-button"
                >
                    Ver ganadores 2026

                    <ArrowRight :size="18" />
                </a>
            </div>
        </div>
    </section>
</template>