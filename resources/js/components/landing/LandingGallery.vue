<script setup lang="ts">
import {
    ArrowRight,
    Images,
} from 'lucide-vue-next';

const galleries = [
    {
        year: 2026,
        title: 'Galería 2026',
        image: 'https://images.unsplash.com/photo-1552674605-db6ffd4facb5?auto=format&fit=crop&w=1400&q=85',
        url: '/galeria/2026',
    },
    {
        year: 2025,
        title: 'Galería 2025',
        image: 'https://images.unsplash.com/photo-1552674605-db6ffd4facb5?auto=format&fit=crop&w=1400&q=85',
        url: '/galeria/2025',
    },
    {
        year: 2024,
        title: 'Galería 2025',
        image: 'https://images.unsplash.com/photo-1552674605-db6ffd4facb5?auto=format&fit=crop&w=1400&q=85',
        url: '/galeria/2025',
    },
    {
        year: 2023,
        title: 'Galería 2025',
        image: 'https://images.unsplash.com/photo-1552674605-db6ffd4facb5?auto=format&fit=crop&w=1400&q=85',
        url: '/galeria/2025',
    },
];
</script>

<template>
    <section
        id="galeria"
        class="landing-gallery-section"
    >
        <div class="landing-container">
            <div class="landing-section-header">
                <div>
                    <span class="landing-section-eyebrow">
                        Revive nuestros mejores momentos
                    </span>

                    <h2 class="landing-section-title">
                        GALERÍA
                    </h2>

                    <p class="landing-section-subtitle">
                        Vuelve a vivir la emoción de cada edición
                        a través de nuestras fotografías.
                    </p>
                </div>
            </div>

            <div class="landing-gallery-cards">
                <a
                    v-for="gallery in galleries"
                    :key="gallery.year"
                    :href="gallery.url"
                    class="landing-gallery-card"
                >
                    <img
                        :src="gallery.image"
                        :alt="gallery.title"
                        class="landing-gallery-card-image"
                    >

                    <div class="landing-gallery-card-overlay">
                        <div>
                            <span class="landing-gallery-card-year">
                                Edición {{ gallery.year }}
                            </span>

                            <h3>
                                {{ gallery.title }}
                            </h3>
                        </div>

                        <span class="landing-gallery-card-arrow">
                            <ArrowRight :size="20" />
                        </span>
                    </div>
                </a>
            </div>

            <div class="landing-gallery-footer">
                <a
                    href="/galeria"
                    class="landing-gallery-button"
                >
                    <Images :size="18" />

                    Ver toda la galería

                    <ArrowRight :size="18" />
                </a>
            </div>
        </div>
    </section>
</template>