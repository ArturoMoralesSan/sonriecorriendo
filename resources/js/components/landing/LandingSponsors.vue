<script setup lang="ts">
interface Sponsor {
    id: number;
    name: string;
    logo: string | null;
    website: string | null;
    is_active: boolean;
}

const props = defineProps<{
    sponsors: Sponsor[];
}>();

const getLogoUrl = (logo: string | null): string => {
    if (!logo) {
        return '';
    }

    if (
        logo.startsWith('http://') ||
        logo.startsWith('https://')
    ) {
        return logo;
    }

    return `/storage/${logo}`;
};

const getSponsorUrl = (website: string | null): string | undefined => {
    if (!website) {
        return undefined;
    }

    if (
        website.startsWith('http://') ||
        website.startsWith('https://')
    ) {
        return website;
    }

    return `https://${website}`;
};
</script>

<template>
    <section
        v-if="props.sponsors.length"
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
                <template
                    v-for="sponsor in props.sponsors"
                    :key="sponsor.id"
                >
                    <a
                        v-if="sponsor.website"
                        :href="getSponsorUrl(sponsor.website)"
                        class="landing-sponsor-card"
                        :aria-label="sponsor.name"
                        target="_blank"
                        rel="noopener noreferrer"
                    >
                        <img
                            v-if="sponsor.logo"
                            :src="getLogoUrl(sponsor.logo)"
                            :alt="sponsor.name"
                            class="landing-sponsor-logo"
                        >

                        <span
                            v-else
                            class="landing-sponsor-name"
                        >
                            {{ sponsor.name }}
                        </span>
                    </a>

                    <div
                        v-else
                        class="landing-sponsor-card"
                        :aria-label="sponsor.name"
                    >
                        <img
                            v-if="sponsor.logo"
                            :src="getLogoUrl(sponsor.logo)"
                            :alt="sponsor.name"
                            class="landing-sponsor-logo"
                        >

                        <span
                            v-else
                            class="landing-sponsor-name"
                        >
                            {{ sponsor.name }}
                        </span>
                    </div>
                </template>
            </div>
        </div>
    </section>
</template>