<script setup lang="ts">
import {
    ArrowRight,
    Play,
    Star,
} from 'lucide-vue-next';

interface RouteMedia {
    id: number;
    route_id: number;
    type: 'image' | 'video';
    file: string;
    title: string | null;
    sort_order: number;
}

interface RouteItem {
    id: number;
    title: string;
    description: string | null;
    is_active: boolean;
    sort_order: number;
    media: RouteMedia[];
}

const props = withDefaults(
    defineProps<{
        routes: RouteItem[];
        showAllLink?: boolean;
    }>(),
    {
        showAllLink: true,
    },
);

function getMediaUrl(file: string): string {
    const cleanFile = file
        .replace(/^\/+/, '')
        .replace(/^storage\//, '');

    return `/storage/${cleanFile}`;
}

function getMediaType(
    file: string,
): 'image' | 'video' {
    const extension = file
        .split('?')[0]
        .split('.')
        .pop()
        ?.toLowerCase();

    const videoExtensions = [
        'mp4',
        'webm',
        'mov',
        'm4v',
        'ogg',
    ];

    return videoExtensions.includes(
        extension ?? '',
    )
        ? 'video'
        : 'image';
}

function getFirstImage(route: RouteItem): RouteMedia | null {
    return (
        route.media?.find(
            (media) => getMediaType(media.file) === 'image',
        ) ?? null
    );
}

function hasVideo(route: RouteItem): boolean {
    return (
        route.media?.some(
            (media) => getMediaType(media.file) === 'video',
        ) ?? false
    );
}

function getRouteUrl(route: RouteItem): string {
    return `/rutas/${route.id}`;
}
</script>

<template>
    <section
        v-if="props.routes.length"
        id="rutas"
        class="landing-experience-section"
    >
        <div class="landing-container">
            <div class="landing-section-header">
                <div>
                    <span class="landing-section-eyebrow">
                        CONOCE Y REVIVE TODAS LAS RUTAS
                    </span>

                    <h2 class="landing-section-title">
                        Rutas
                    </h2>

                    <p class="landing-section-subtitle">
                        Elige tu reto y sé parte de la experiencia.
                    </p>
                </div>

                <a
                    v-if="props.showAllLink"
                    href="/rutas"
                    class="landing-see-all"
                >
                    Ver todas las rutas

                    <ArrowRight
                        :size="17"
                        :stroke-width="2"
                    />
                </a>
            </div>

            <div
                class="landing-experience-grid"
                style="
                    grid-template-columns: repeat(2, minmax(0, 1fr));
                    row-gap: 80px;
                "
            >
                <a
                    v-for="route in props.routes"
                    :key="route.id"
                    :href="getRouteUrl(route)"
                    class="landing-experience-visual"
                    style="text-decoration: none; color: inherit;"
                >
                    <div class="experience-image-main">
                        <img
                            v-if="getFirstImage(route)"
                            :src="getMediaUrl(getFirstImage(route)!.file)"
                            :alt="route.title"
                        />

                        <div
                            v-else
                            class="landing-route-image-empty"
                        >
                            Sin imagen
                        </div>
                    </div>

                    <div class="experience-floating-card">
                        <div class="experience-floating-icon">
                            <Play
                                v-if="hasVideo(route)"
                                :size="22"
                                fill="currentColor"
                            />

                            <Star
                                v-else
                                :size="22"
                                fill="currentColor"
                            />
                        </div>

                        <div>
                            <strong>
                                {{ route.title }}
                            </strong>

                            <span v-if="route.description">
                                {{ route.description }}
                            </span>

                            <span v-else>
                                Descubre esta ruta.
                            </span>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </section>
</template>