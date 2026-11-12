<script setup lang="ts">
import MediaGalleryPage from '@/components/landing/MediaGalleryPage.vue';

defineProps<{
    race: {
        id: number;
        name: string;
        slug: string;
    };
    gallery: {
        id: number;
        image: string;
        sort_order?: number;
        race?: {
            id: number;
            name: string;
            slug: string;
        };
    }[];
}>();
</script>

<template>
    <MediaGalleryPage
        :race="race"
        :gallery="gallery"
    />
</template>