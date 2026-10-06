<script setup lang="ts">
import {
    ArrowRight,
} from 'lucide-vue-next';

interface WinnerRace {
    id: number;
    name: string;
    event_date: string;
    results_url: string;
}

const props = defineProps<{
    race: WinnerRace | null;
}>();

const getRaceYear = (): string => {
    if (!props.race?.event_date) {
        return '';
    }

    return new Date(props.race.event_date).getFullYear().toString();
};
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
                    <template v-if="props.race">
                        Conoce nuestros ganadores {{ getRaceYear() }}
                    </template>

                    <template v-else>
                        Próximamente
                    </template>
                </h2>

                <p class="landing-winners-description">
                    <template v-if="props.race">
                        Descubre a quienes llevaron cada kilómetro
                        hasta el límite y conoce los resultados
                        de la edición {{ getRaceYear() }}.
                    </template>

                    <template v-else>
                        Los resultados de nuestra próxima carrera
                        estarán disponibles próximamente.
                    </template>
                </p>

                <a
                    v-if="props.race"
                    :href="props.race.results_url"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="landing-winners-button"
                >
                    Ver ganadores {{ getRaceYear() }}

                    <ArrowRight :size="18" />
                </a>
            </div>
        </div>
    </section>
</template>
