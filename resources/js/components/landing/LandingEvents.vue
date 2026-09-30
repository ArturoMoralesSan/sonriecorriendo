<script setup lang="ts">
import {
    ArrowRight,
    CalendarDays,
    MapPin,
} from 'lucide-vue-next';

interface RacePrice {
    id: number;
    name: string;
    price: number | string;
    starts_at: string | null;
    ends_at: string | null;
    capacity: number | null;
    sort_order: number;
    is_active: boolean;
}

interface RaceDistance {
    id: number;
    name: string;
    distance: number | string;
    unit: string;
    prices?: RacePrice[];
}

interface Race {
    id: number;
    name: string;
    slug: string;
    event_date: string;
    location: string | null;
    banner: string | null;
    distances?: RaceDistance[];
}

const props = defineProps<{
    events: Race[];
    showAllLink?: boolean;
}>();

const getTypeClass = (index: number): string => {
    const classes = [
        'blue',
        'purple',
        'pink',
        'green',
    ];

    return classes[index % classes.length];
};

const getDistanceLabel = (event: Race): string => {
    const distances = event.distances ?? [];

    if (!distances.length) {
        return 'Carrera';
    }

    return distances
        .map((distance) => {
            const value = Number(distance.distance);

            if (!Number.isNaN(value)) {
                return `${value}${distance.unit || 'K'}`;
            }

            return distance.name || 'Carrera';
        })
        .join(' · ');
};

const getPrice = (event: Race): string => {
    const prices = (event.distances ?? [])
        .flatMap((distance) => distance.prices ?? [])
        .filter((price) => price.is_active)
        .map((price) => Number(price.price))
        .filter((price) => !Number.isNaN(price));

    if (!prices.length) {
        return 'Consultar';
    }

    return `$${Math.min(...prices).toLocaleString('es-MX', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 2,
    })}`;
};

const formatDate = (date: string): string => {
    if (!date) {
        return '';
    }

    const parsedDate = new Date(date);

    if (Number.isNaN(parsedDate.getTime())) {
        return date;
    }

    const parts = new Intl.DateTimeFormat('es-MX', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        timeZone: 'America/Mexico_City',
    }).formatToParts(parsedDate);

    const day = parts.find((part) => part.type === 'day')?.value ?? '';
    const month = parts.find((part) => part.type === 'month')?.value ?? '';
    const year = parts.find((part) => part.type === 'year')?.value ?? '';

    const formattedMonth =
        month.charAt(0).toUpperCase() + month.slice(1).replace('.', '');

    return `${day} ${formattedMonth} ${year}`;
};

const getImageUrl = (banner: string | null): string => {
    if (!banner) {
        return 'https://images.pexels.com/photos/2402777/pexels-photo-2402777.jpeg?auto=compress&cs=tinysrgb&w=1200';
    }

    if (
        banner.startsWith('http://') ||
        banner.startsWith('https://')
    ) {
        return banner;
    }

    return `/storage/${banner}`;
};
</script>

<template>
    <section
        v-if="props.events.length"
        id="eventos"
        class="landing-events-section"
    >
        <div class="landing-container">
            <div class="landing-section-header">
                <div>
                    <span class="landing-section-eyebrow">
                        SONRÍE CORRIENDO
                    </span>

                    <h2 class="landing-section-title">
                        Eventos
                    </h2>

                    <p class="landing-section-subtitle">
                        Elige tu reto y sé parte de la experiencia.
                    </p>
                </div>

                <a
                    v-if="props.showAllLink"
                    href="/carreras"
                    class="landing-see-all"
                >
                    Ver todos los eventos

                    <ArrowRight
                        :size="17"
                        :stroke-width="2"
                    />
                </a>
            </div>

            <div class="landing-events-grid">
                <article
                    v-for="(event, index) in props.events"
                    :key="event.id"
                    class="landing-event-card"
                >
                    <div class="landing-event-image-wrapper">
                        <img
                            :src="getImageUrl(event.banner)"
                            :alt="event.name"
                            class="landing-event-image"
                        />

                        <span
                            class="landing-event-badge"
                            :class="`badge-${getTypeClass(index)}`"
                        >
                            {{ getDistanceLabel(event) }}
                        </span>
                    </div>

                    <div class="landing-event-body">
                        <h3 class="landing-event-title">
                            {{ event.name }}
                        </h3>

                        <div class="landing-event-info">
                            <div class="landing-event-info-row">
                                <CalendarDays
                                    :size="16"
                                    :stroke-width="1.9"
                                />

                                <span>
                                    {{ formatDate(event.event_date) }}
                                </span>
                            </div>

                            <div class="landing-event-info-row">
                                <MapPin
                                    :size="16"
                                    :stroke-width="1.9"
                                />

                                <span>
                                    {{ event.location || 'Por confirmar' }}
                                </span>
                            </div>
                        </div>

                        <div class="landing-event-footer">
                            <div class="landing-event-price">
                                <strong>
                                    {{ getPrice(event) }}
                                </strong>

                                <span>MXN</span>
                            </div>

                            <a
                                :href="`/carreras/${event.slug}`"
                                class="landing-event-button"
                            >
                                Inscribirme

                                <ArrowRight
                                    :size="16"
                                    :stroke-width="2.2"
                                />
                            </a>
                        </div>
                    </div>
                </article>
            </div>
        </div>
    </section>
</template>