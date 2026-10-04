<script setup lang="ts">
import { Head } from '@inertiajs/vue3';

import LandingBenefits from '@/components/landing/LandingBenefits.vue';
import LandingClubs from '@/components/landing/LandingClubs.vue';
import LandingCta from '@/components/landing/LandingCta.vue';
import LandingEvents from '@/components/landing/LandingEvents.vue';
import LandingExperience from '@/components/landing/LandingExperience.vue';
import LandingFooter from '@/components/landing/LandingFooter.vue';
import LandingGallery from '@/components/landing/LandingGallery.vue';
import LandingHeader from '@/components/landing/LandingHeader.vue';
import LandingHero from '@/components/landing/LandingHero.vue';
import LandingRoutes from '@/components/landing/LandingRoutes.vue';
import LandingShop from '@/components/landing/LandingShop.vue';
import LandingSponsors from '@/components/landing/LandingSponsors.vue';
import LandingWinners from '@/components/landing/LandingWinners.vue';
import WhatsAppButton from '@/components/landing/WhatsAppButton.vue';

defineProps<{
    races: Array<any>;
    clubs: Array<any>;
    products: Array<any>;
    gallery: Array<any>;
    sponsors: Array<any>;
}>();
</script>

<template>
    <Head title="Sonríe Corriendo">
        <meta
            name="description"
            content="Encuentra carreras, inscríbete a eventos y vive la experiencia Sonríe Corriendo."
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
            <LandingHero />

            <LandingBenefits />

            <LandingEvents
                :events="races"
                :show-all-link="true"
            />

            <LandingExperience />

            <LandingRoutes />

            <LandingWinners />

            <LandingGallery :gallery="gallery" :show-all-link="true" />

            <LandingShop
                :products="products"
                :show-all-link="true"
            />

            <LandingSponsors :sponsors="sponsors" />

            <LandingClubs
                :clubs="clubs"
                :show-all-link="true"
            />

            <LandingCta />
            <WhatsAppButton
                phone="526181234567"
                message="Hola, quiero obtener información sobre una carrera."
            />
        </main>

        <LandingFooter />
    </div>
</template>