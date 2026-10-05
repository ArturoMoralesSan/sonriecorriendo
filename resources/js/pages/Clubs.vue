<script setup lang="ts">
import { Head } from '@inertiajs/vue3';

import LandingClubs from '@/components/landing/LandingClubs.vue';
import LandingFooter from '@/components/landing/LandingFooter.vue';
import LandingHeader from '@/components/landing/LandingHeader.vue';
import LandingHero from '@/components/landing/LandingHero.vue';

defineProps<{
    clubs: Array<any>;
    banners: Array<any>;
}>();
</script>

<template>
    <Head title="Clubes">
        <meta
            name="description"
            content="Conoce los clubes y comunidades que forman parte de Sonríe Corriendo."
        />

        <link
            rel="preconnect"
            href="https://fonts.googleapis.com"
        />

        <link
            rel="preconnect"
            href="https://fonts.gstatic.com"
        />

        <link
            href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap"
            rel="stylesheet"
        />
    </Head>

    <div class="landing-page">
        <LandingHeader />

        <main>
            <LandingHero :banners="banners" />

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
</style>