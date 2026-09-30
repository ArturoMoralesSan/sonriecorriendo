<script setup lang="ts">
import {
    ArrowRight,
    Images,
} from 'lucide-vue-next';

interface GalleryRace {
    id: number;
    name: string;
    slug: string;
    status: string;
}

interface GalleryItem {
    id: number;
    race_id: number;
    image: string;
    sort_order: number;
    is_active: boolean;
    race?: GalleryRace | null;
}

interface GalleryCard {
    raceId: number;
    year: number;
    title: string;
    image: string;
    url: string;
}

const props = defineProps<{
    gallery: GalleryItem[];
    showAllLink?: boolean;
    limit?: number | null;
}>();

const getImageUrl = (image: string): string => {
    if (!image) {
        return '';
    }

    if (
        image.startsWith('http://') ||
        image.startsWith('https://')
    ) {
        return image;
    }

    return `/storage/${image}`;
};

const getRaceYear = (
    race: GalleryRace | null | undefined,
): number => {
    if (!race) {
        return new Date().getFullYear();
    }

    const match = race.name.match(/\b(20\d{2})\b/);

    if (match) {
        return Number(match[1]);
    }

    return new Date().getFullYear();
};

const getGalleryCards = (): GalleryCard[] => {
    const grouped = new Map<number, GalleryItem>();

    for (const item of props.gallery) {
        if (!item.race || !item.is_active) {
            continue;
        }

        if (!grouped.has(item.race_id)) {
            grouped.set(item.race_id, item);
        }
    }

    let cards = Array.from(grouped.values())
        .map((item) => {
            const race = item.race;

            if (!race) {
                return null;
            }

            const year = getRaceYear(race);

            return {
                raceId: item.race_id,
                year,
                title: race.name,
                image: getImageUrl(item.image),
                url: `/galeria/${race.slug}`,
            };
        })
        .filter(
            (item): item is GalleryCard =>
                item !== null,
        )
        .sort((a, b) => b.year - a.year);

    if (props.limit !== null) {
        cards = cards.slice(0, props.limit ?? 4);
    }

    return cards;
};

const galleries = getGalleryCards();
</script>

<template>
    <section
        v-if="galleries.length"
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
                    :key="gallery.raceId"
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

            <div
                v-if="props.showAllLink"
                class="landing-gallery-footer"
            >
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