<script setup lang="ts">
import {
    ArrowRight,
    MapPin,
} from 'lucide-vue-next';

const clubs = [
    {
        id: 1,
        name: 'Club de Corredores',
        location: 'Durango, Dgo.',
        description: 'Corre, comparte y disfruta cada kilómetro.',
        image:
            'https://images.pexels.com/photos/2402777/pexels-photo-2402777.jpeg?auto=compress&cs=tinysrgb&w=1200',
    },
    {
        id: 2,
        name: 'Running Team',
        location: 'Durango, Dgo.',
        description: 'Una comunidad para disfrutar el running.',
        image:
            'https://images.pexels.com/photos/3768916/pexels-photo-3768916.jpeg?auto=compress&cs=tinysrgb&w=1200',
    },
    {
        id: 3,
        name: 'Comunidad Runner',
        location: 'Durango, Dgo.',
        description: 'Entrena, participa y vive nuevas experiencias.',
        image:
            'https://images.pexels.com/photos/1462399/pexels-photo-1462399.jpeg?auto=compress&cs=tinysrgb&w=1200',
    },
    {
        id: 4,
        name: 'Club de Atletismo',
        location: 'Durango, Dgo.',
        description: 'Metas, kilómetros y experiencias compartidas.',
        image:
            'https://images.pexels.com/photos/11757805/pexels-photo-11757805.jpeg?auto=compress&cs=tinysrgb&w=1200',
    },
];
</script>

<template>
    <section
        id="clubes"
        class="landing-clubs-section"
    >
        <div class="landing-container">
            <div class="landing-section-header">
                <div>
                    <span class="landing-section-eyebrow">
                        COMUNIDAD
                    </span>

                    <h2 class="landing-section-title">
                        Clubes
                    </h2>

                    <p class="landing-section-subtitle">
                        Conoce los clubes y comunidades que forman
                        parte de la experiencia.
                    </p>
                </div>

                <a
                    href="#clubes"
                    class="landing-see-all"
                >
                    Ver todos los clubes

                    <ArrowRight
                        :size="17"
                        :stroke-width="2"
                    />
                </a>
            </div>

            <div class="landing-clubs-grid">
                <article
                    v-for="club in clubs"
                    :key="club.id"
                    class="landing-club-card"
                >
                    <div class="landing-club-image-wrapper">
                        <img
                            :src="club.image"
                            :alt="club.name"
                            class="landing-club-image"
                        />
                    </div>

                    <div class="landing-club-content">
                        <h3 class="landing-club-title">
                            {{ club.name }}
                        </h3>

                        <div class="landing-club-location">
                            <MapPin
                                :size="15"
                                :stroke-width="1.9"
                            />

                            <span>{{ club.location }}</span>
                        </div>

                        <p class="landing-club-description">
                            {{ club.description }}
                        </p>

                        <a
                            href="#"
                            class="landing-club-button"
                        >
                            Ver club

                            <ArrowRight
                                :size="15"
                                :stroke-width="2.2"
                            />
                        </a>
                    </div>
                </article>
            </div>
        </div>
    </section>
</template>