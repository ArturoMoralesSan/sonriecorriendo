<script setup lang="ts">
const sponsors = [
    {
        id: 1,
        name: 'Sonríe Corriendo',
        logo: 'https://dummyimage.com/400x180/ffffff/249edb.png&text=Sonr%C3%ADe+Corriendo',
        url: '#',
    },
    {
        id: 2,
        name: 'PowerRun',
        logo: 'https://dummyimage.com/400x180/ffffff/249edb.png&text=PowerRun',
        url: '#',
    },
    {
        id: 3,
        name: 'Agua Vital',
        logo: 'https://dummyimage.com/400x180/ffffff/18b89a.png&text=Agua+Vital',
        url: '#',
    },
    {
        id: 4,
        name: 'Sport Life',
        logo: 'https://dummyimage.com/400x180/ffffff/6753b7.png&text=Sport+Life',
        url: '#',
    },
    {
        id: 5,
        name: 'NutriFit',
        logo: 'https://dummyimage.com/400x180/ffffff/d94c9a.png&text=NutriFit',
        url: '#',
    },
    {
        id: 6,
        name: 'Run México',
        logo: 'https://dummyimage.com/400x180/ffffff/1769a8.png&text=Run+M%C3%A9xico',
        url: '#',
    },
    {
        id: 7,
        name: 'Energía Plus',
        logo: 'https://dummyimage.com/400x180/ffffff/e8b84a.png&text=Energ%C3%ADa+Plus',
        url: '#',
    },
    {
        id: 8,
        name: 'DGO Fitness',
        logo: 'https://dummyimage.com/400x180/ffffff/249edb.png&text=DGO+Fitness',
        url: '#',
    },
    {
        id: 9,
        name: 'MoviSport',
        logo: 'https://dummyimage.com/400x180/ffffff/d94c9a.png&text=MoviSport',
        url: '#',
    },
];
</script>

<template>
    <section
        id="patrocinadores"
        class="landing-sponsors-section"
    >
        <div class="landing-container">
            <div class="landing-section-header">
                <div>
                    <span class="landing-section-eyebrow">
                        SONRÍE CORRIENDO
                    </span>

                    <h2 class="landing-section-title">
                        Nuestros patrocinadores
                    </h2>

                    <p class="landing-section-subtitle">
                        Gracias a quienes hacen posible cada experiencia.
                    </p>
                </div>
            </div>

            <div class="landing-sponsors-grid">
                <a
                    v-for="sponsor in sponsors"
                    :key="sponsor.id"
                    :href="sponsor.url"
                    class="landing-sponsor-card"
                    :aria-label="sponsor.name"
                >
                    <img
                        :src="sponsor.logo"
                        :alt="sponsor.name"
                        class="landing-sponsor-logo"
                    >
                </a>
            </div>
        </div>
    </section>
</template>