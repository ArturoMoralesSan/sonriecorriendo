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

    return videoExtensions.includes(extension ?? '')
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

function getFirstVideo(route: RouteItem): RouteMedia | null {
    return (
        route.media?.find(
            (media) => getMediaType(media.file) === 'video',
        ) ?? null
    );
}

function hasVideo(route: RouteItem): boolean {
    return getFirstVideo(route) !== null;
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
                        <!-- Si existe una imagen, tiene prioridad -->
                        <img
                            v-if="getFirstImage(route)"
                            :src="getMediaUrl(getFirstImage(route)!.file)"
                            :alt="route.title"
                            loading="lazy"
                        />

                        <!-- Si no hay imagen, usar el video como portada -->
                        <video
                            v-else-if="getFirstVideo(route)"
                            :src="getMediaUrl(getFirstVideo(route)!.file) + '#t=0.5'"
                            :aria-label="route.title"
                            muted
                            playsinline
                            preload="metadata"
                            tabindex="-1"
                            class="landing-route-video-thumbnail"
                        />

                        <!-- Si no existe ningún archivo multimedia -->
                        <div
                            v-else
                            class="landing-route-image-empty"
                        >
                            <Star :size="36" />
                            <span>Sin imagen</span>
                        </div>

                        <!-- Indicador visual de video -->
                        <div
                            v-if="!getFirstImage(route) && getFirstVideo(route)"
                            class="landing-route-video-play"
                        >
                            <Play
                                :size="28"
                                fill="currentColor"
                            />
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

<style scoped>
.experience-image-main {
    position: relative;
    overflow: hidden;
}

.landing-route-video-thumbnail {
    display: block;
    width: 100%;
    height: 100%;
    object-fit: cover;
    background: #171717;
}

.landing-route-video-play {
    position: absolute;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    pointer-events: none;
}

.landing-route-video-play :deep(svg) {
    padding: 16px;
    box-sizing: content-box;
    background: rgb(0 0 0 / 55%);
    border-radius: 50%;
}

.landing-route-image-empty {
    display: flex;
    min-height: 240px;
    align-items: center;
    justify-content: center;
    flex-direction: column;
    gap: 12px;
    color: #737373;
    background: #f3f4f6;
}
</style>