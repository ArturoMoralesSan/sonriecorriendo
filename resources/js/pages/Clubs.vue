<script setup lang="ts">
import { Head } from '@inertiajs/vue3';

import LandingClubs from '@/components/landing/LandingClubs.vue';
import LandingFooter from '@/components/landing/LandingFooter.vue';
import LandingHeader from '@/components/landing/LandingHeader.vue';

defineProps<{
    clubs: Array<any>;
}>();
</script>

<template>
    <Head title="Clubes">
        <meta
            name="description"
            content="Conoce los clubes y comunidades que forman parte de Sonríe Corriendo."
        />

        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link rel="preconnect" href="https://fonts.gstatic.com" />
        <link
            href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap"
            rel="stylesheet"
        />
    </Head>

    <div class="landing-page">
        <LandingHeader />

        <main>
            <section class="clubs-page-hero">
                <div class="landing-container">
                    <span class="clubs-page-eyebrow">
                        COMUNIDAD
                    </span>

                    <h1>
                        Clubes
                    </h1>

                    <p>
                        Conoce los clubes y comunidades que forman
                        parte de la experiencia Sonríe Corriendo.
                    </p>
                </div>
            </section>

            <LandingClubs
                :clubs="clubs"
            />
        </main>

        <LandingFooter />
    </div>
</template>

<style scoped>
.landing-page {
    width: 100%;
    min-height: 100vh;
    overflow-x: hidden;
    background: #f8fafc;
}

/* =========================================================
   HERO
   ========================================================= */

.clubs-page-hero {
    position: relative;
    overflow: hidden;
    padding: 78px 0 82px;
    background:
        linear-gradient(
            90deg,
            #12558cfa 0%,
            #1769a8e8 30%,
            #249edba3 54%,
            #d94c9a7a 78%,
            #f48bb052 100%
        );
}

.clubs-page-hero::before {
    content: '';
    position: absolute;
    width: 380px;
    height: 380px;
    right: -130px;
    top: -230px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.08);
    pointer-events: none;
}

.clubs-page-hero::after {
    content: '';
    position: absolute;
    width: 280px;
    height: 280px;
    left: -170px;
    bottom: -220px;
    border-radius: 50%;
    background: rgba(244, 139, 176, 0.1);
    pointer-events: none;
}

.clubs-page-eyebrow {
    position: relative;
    z-index: 1;
    display: inline-block;
    margin-bottom: 8px;
    color: rgba(255, 255, 255, 0.72);
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 1px;
    text-transform: uppercase;
}

.clubs-page-hero h1 {
    position: relative;
    z-index: 1;
    max-width: 850px;
    margin: 0;
    color: #fff;
    font-size: clamp(42px, 6vw, 68px);
    line-height: 1;
    letter-spacing: -2.8px;
    font-weight: 800;
}

.clubs-page-hero p {
    position: relative;
    z-index: 1;
    max-width: 620px;
    margin: 14px 0 0;
    color: rgba(255, 255, 255, 0.84);
    font-size: 16px;
    line-height: 1.6;
}

/* =========================================================
   RESPONSIVE
   ========================================================= */

@media (max-width: 620px) {
    .clubs-page-hero {
        padding: 60px 0 65px;
    }

    .clubs-page-hero h1 {
        font-size: 42px;
        letter-spacing: -1.6px;
    }

    .clubs-page-hero p {
        font-size: 15px;
    }
}
</style>