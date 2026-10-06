<script setup lang="ts">
import { Head } from '@inertiajs/vue3';

import LandingFooter from '@/components/landing/LandingFooter.vue';
import LandingHeader from '@/components/landing/LandingHeader.vue';
import LandingHero from '@/components/landing/LandingHero.vue';
import LandingRoutes from '@/components/landing/LandingRoutes.vue';

defineProps<{
    routes: Array<any>;
    banners: Array<any>;
}>();
</script>

<template>
    <Head title="Rutas">
        <meta
            name="description"
            content="Conoce y revive las rutas de Sonríe Corriendo."
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

            <LandingRoutes
                :routes="routes"
                :show-all-link="false"
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
