<script setup lang="ts">
import {
    ArrowRight,
    CalendarDays,
    MapPin,
} from 'lucide-vue-next';

const events = [
    {
        id: 1,
        type: '5K',
        typeClass: 'blue',
        name: 'Carrera Sonríe Corriendo',
        date: '25 Jul 2026',
        location: 'Parque Metropolitano',
        price: '$350',
        image:
            'https://images.pexels.com/photos/2402777/pexels-photo-2402777.jpeg?auto=compress&cs=tinysrgb&w=1200',
    },
    {
        id: 2,
        type: '10K',
        typeClass: 'purple',
        name: 'Maratón Kids',
        date: '22 Ago 2026',
        location: 'Parque Metropolitano',
        price: '$250',
        image:
            'https://images.pexels.com/photos/11757805/pexels-photo-11757805.jpeg?auto=compress&cs=tinysrgb&w=1200',
    },
    {
        id: 3,
        type: '10K',
        typeClass: 'pink',
        name: 'Carrera 10K',
        date: '14 Sep 2026',
        location: 'Parque Metropolitano',
        price: '$450',
        image:
            'https://images.pexels.com/photos/3768916/pexels-photo-3768916.jpeg?auto=compress&cs=tinysrgb&w=1200',
    },
    {
        id: 4,
        type: 'Kids',
        typeClass: 'green',
        name: 'Carrera Familiar',
        date: '20 Oct 2026',
        location: 'Parque Guadiana',
        price: '$350',
        image:
            'https://images.pexels.com/photos/1462399/pexels-photo-1462399.jpeg?auto=compress&cs=tinysrgb&w=1200',
    },
];
</script>

<template>
    <section
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
                    href="#eventos"
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
                    v-for="event in events"
                    :key="event.id"
                    class="landing-event-card"
                >
                    <div class="landing-event-image-wrapper">
                        <img
                            :src="event.image"
                            :alt="event.name"
                            class="landing-event-image"
                        />

                        <span
                            class="landing-event-badge"
                            :class="`badge-${event.typeClass}`"
                        >
                            {{ event.type }}
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

                                <span>{{ event.date }}</span>
                            </div>

                            <div class="landing-event-info-row">
                                <MapPin
                                    :size="16"
                                    :stroke-width="1.9"
                                />

                                <span>{{ event.location }}</span>
                            </div>
                        </div>

                        <div class="landing-event-footer">
                            <div class="landing-event-price">
                                <strong>{{ event.price }}</strong>
                                <span>MXN</span>
                            </div>

                            <a
                                href="#"
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