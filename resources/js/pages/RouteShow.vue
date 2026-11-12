<script setup lang="ts">
import MediaGalleryPage from '@/components/landing/MediaGalleryPage.vue';

defineProps<{
    route: {
        id: number;
        title: string;
        description: string | null;
        is_active: boolean;
        sort_order: number;
        media: {
            id: number;
            route_id: number;
            type: 'image' | 'video';
            file: string;
            title: string | null;
            sort_order: number;
        }[];
    };
}>();
</script>

<template>
    <MediaGalleryPage :route="route" />
</template>