<script setup lang="ts">
import {
    ArrowRight,
    MapPin,
    Play,
} from 'lucide-vue-next';

const routes = [
    {
        id: 1,
        name: 'Ruta 5K',
        distance: '5 kilómetros',
        image:
            'https://images.pexels.com/photos/2402777/pexels-photo-2402777.jpeg?auto=compress&cs=tinysrgb&w=1200',
    },
    {
        id: 2,
        name: 'Ruta 10K',
        distance: '10 kilómetros',
        image:
            'https://images.pexels.com/photos/3768916/pexels-photo-3768916.jpeg?auto=compress&cs=tinysrgb&w=1200',
    },
];
</script>

<template>
    <section
        id="rutas"
        class="landing-routes-section"
    >
        <div class="landing-container">
            <div class="landing-section-header">
                <div>
                    <span class="landing-section-eyebrow">
                        DESCUBRE EL RECORRIDO
                    </span>

                    <h2 class="landing-section-title">
                        Conoce nuestras rutas
                    </h2>

                    <p class="landing-section-subtitle">
                        Prepárate para cada kilómetro y conoce el recorrido
                        de nuestras carreras.
                    </p>
                </div>
            </div>

            <div class="landing-routes-grid">
                <article
                    v-for="route in routes"
                    :key="route.id"
                    class="landing-route-card"
                >
                    <div class="landing-route-image-wrapper">
                        <img
                            :src="route.image"
                            :alt="route.name"
                            class="landing-route-image"
                        />

                        <button
                            type="button"
                            class="landing-route-play"
                            aria-label="Ver recorrido"
                        >
                            <Play
                                :size="19"
                                fill="currentColor"
                            />
                        </button>
                    </div>

                    <div class="landing-route-content">
                        <div>
                            <span class="landing-route-label">
                                <MapPin :size="15" />
                                {{ route.distance }}
                            </span>

                            <h3>{{ route.name }}</h3>
                        </div>

                        <a
                            href="#"
                            class="landing-see-all"
                        >
                            Ver ruta

                            <ArrowRight :size="16" />
                        </a>
                    </div>
                </article>
            </div>
        </div>
    </section>
</template>