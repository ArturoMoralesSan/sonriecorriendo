<script setup lang="ts">
/**
 * Cuenta regresiva hacia la salida de la carrera.
 *
 * La fecha/hora se interpreta en horario del centro de México (UTC-6,
 * sin horario de verano desde 2022), igual que la zona de la app.
 */
import { ExternalLink, Flag, Medal, Timer } from 'lucide-vue-next';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';

const props = defineProps<{
    eventDate: string | null | undefined;
    startTime?: string | null;
    endTime?: string | null;
    status?: string | null;
    resultsUrl?: string | null;
}>();

const EVENT_UTC_OFFSET = '-06:00';

const normalizeTime = (value: string | null | undefined): string | null => {
    const match = String(value ?? '').match(/^(\d{1,2}):(\d{2})/);

    return match ? `${match[1].padStart(2, '0')}:${match[2]}:00` : null;
};

const datePart = computed(() => {
    const match = String(props.eventDate ?? '').match(/^\d{4}-\d{2}-\d{2}/);

    return match ? match[0] : null;
});

const toTimestamp = (time: string): number | null => {
    if (!datePart.value) {
        return null;
    }

    const ms = new Date(
        `${datePart.value}T${time}${EVENT_UTC_OFFSET}`,
    ).getTime();

    return Number.isNaN(ms) ? null : ms;
};

const startAt = computed(() =>
    toTimestamp(normalizeTime(props.startTime) ?? '00:00:00'),
);

const endAt = computed(() =>
    toTimestamp(normalizeTime(props.endTime) ?? '23:59:59'),
);

const now = ref(Date.now());
let timer: ReturnType<typeof setInterval> | null = null;

onMounted(() => {
    timer = setInterval(() => {
        now.value = Date.now();
    }, 1000);
});

onBeforeUnmount(() => {
    if (timer) {
        clearInterval(timer);
    }
});

const phase = computed<'upcoming' | 'live' | 'over' | 'none'>(() => {
    if (props.status === 'finished') {
        return 'over';
    }

    if (startAt.value === null) {
        return 'none';
    }

    if (now.value < startAt.value) {
        return 'upcoming';
    }

    if (endAt.value !== null && now.value <= endAt.value) {
        return 'live';
    }

    return 'over';
});

const parts = computed(() => {
    const diff = Math.max(0, (startAt.value ?? 0) - now.value);
    const totalSeconds = Math.floor(diff / 1000);

    return [
        { label: 'Días', value: Math.floor(totalSeconds / 86400) },
        { label: 'Horas', value: Math.floor((totalSeconds % 86400) / 3600) },
        { label: 'Min', value: Math.floor((totalSeconds % 3600) / 60) },
        { label: 'Seg', value: totalSeconds % 60 },
    ];
});

const pad = (value: number): string => String(value).padStart(2, '0');
</script>

<template>
    <div v-if="phase !== 'none'" class="race-countdown-wrap">
        <div
            v-if="phase === 'upcoming'"
            class="race-countdown"
            role="timer"
            aria-live="off"
        >
            <span class="race-countdown-title">
                <Timer :size="15" />
                Faltan para la salida
            </span>

            <div class="race-countdown-grid">
                <div
                    v-for="part in parts"
                    :key="part.label"
                    class="race-countdown-item"
                >
                    <strong>{{ pad(part.value) }}</strong>
                    <span>{{ part.label }}</span>
                </div>
            </div>
        </div>

        <div
            v-else-if="phase === 'live'"
            class="race-countdown race-countdown-live"
        >
            <span class="race-countdown-live-dot"></span>
            <span class="race-countdown-title">
                <Flag :size="15" />
                ¡La carrera está en curso!
            </span>
        </div>

        <div v-else class="race-countdown race-countdown-over">
            <span class="race-countdown-title">
                <Medal :size="15" />
                Carrera finalizada
            </span>

            <a
                v-if="resultsUrl"
                :href="resultsUrl"
                target="_blank"
                rel="noopener noreferrer"
                class="race-countdown-results"
            >
                Ver resultados
                <ExternalLink :size="14" />
            </a>

            <span v-else class="race-countdown-note">
                Los resultados se publicarán próximamente.
            </span>
        </div>
    </div>
</template>

<style scoped>
.race-countdown-wrap {
    display: flex;
    justify-content: center;

    width: 100%;
    margin: 34px 0 30px;
}

.race-countdown {
    display: inline-flex;
    flex-direction: column;
    align-items: center;
    gap: 14px;

    padding: 18px 22px;

    border: 1px solid rgba(255, 255, 255, 0.28);
    border-radius: 18px;

    background: rgba(255, 255, 255, 0.12);
    backdrop-filter: blur(12px);

    box-shadow:
        0 12px 30px rgba(7, 35, 65, 0.14),
        inset 0 1px 0 rgba(255, 255, 255, 0.12);

    color: #fff;
}

.race-countdown-title {
    display: inline-flex;
    align-items: center;
    gap: 7px;

    font-size: 11px;
    font-weight: 800;
    letter-spacing: 0.45px;
    text-transform: uppercase;

    opacity: 0.92;
}

.race-countdown-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(72px, 1fr));
    gap: 12px;
}

.race-countdown-item {
    display: flex;
    flex-direction: column;
    align-items: center;

    padding: 10px 8px;

    border-radius: 12px;

    background: rgba(255, 255, 255, 0.16);
}

.race-countdown-item strong {
    font-size: 30px;
    font-weight: 800;
    line-height: 1.1;
    font-variant-numeric: tabular-nums;
}

.race-countdown-item span {
    margin-top: 3px;

    font-size: 10px;
    font-weight: 700;
    letter-spacing: 0.4px;
    text-transform: uppercase;

    opacity: 0.85;
}

.race-countdown-live {
    flex-direction: row;
    gap: 10px;

    padding: 14px 24px;

    border-radius: 999px;

    background: rgba(217, 76, 154, 0.32);
}

.race-countdown-live .race-countdown-title {
    font-size: 13px;
    opacity: 1;
}

.race-countdown-live-dot {
    width: 10px;
    height: 10px;

    border-radius: 50%;

    background: #fff;

    animation: race-countdown-pulse 1.4s ease-in-out infinite;
}

@keyframes race-countdown-pulse {
    0%,
    100% {
        box-shadow: 0 0 0 0 rgba(255, 255, 255, 0.7);
    }

    50% {
        box-shadow: 0 0 0 8px rgba(255, 255, 255, 0);
    }
}

.race-countdown-over {
    gap: 12px;
    padding: 18px 26px;
}

.race-countdown-over .race-countdown-title {
    font-size: 13px;
    opacity: 1;
}

.race-countdown-results {
    display: inline-flex;
    align-items: center;
    gap: 8px;

    padding: 11px 22px;

    border-radius: 999px;

    background: #fff;

    box-shadow: 0 8px 20px rgba(7, 35, 65, 0.18);

    color: #1769a8;

    font-size: 14px;
    font-weight: 800;
    text-decoration: none;

    transition:
        transform 0.2s ease,
        box-shadow 0.2s ease;
}

.race-countdown-results:hover {
    transform: translateY(-1px);

    box-shadow: 0 12px 26px rgba(7, 35, 65, 0.24);
}

.race-countdown-note {
    font-size: 12px;
    opacity: 0.85;
}

@media (max-width: 520px) {
    .race-countdown-wrap {
        margin: 26px 0 22px;
    }

    .race-countdown:not(.race-countdown-live, .race-countdown-over) {
        display: flex;
        width: 100%;
        box-sizing: border-box;
        padding: 14px;
    }

    .race-countdown-grid {
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 6px;
    }

    .race-countdown-item strong {
        font-size: 22px;
    }
}
</style>
