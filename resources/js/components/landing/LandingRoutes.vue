
<script setup lang="ts">
import {
    ArrowRight,
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

function getMediaType(file: string): 'image' | 'video' {
    const extension = file
        .split('?')[0]
        .split('.')
        .pop()
        ?.toLowerCase();

    return ['mp4', 'webm', 'mov', 'm4v', 'ogg'].includes(extension ?? '')
        ? 'video'
        : 'image';
}

function getFirstImage(route: RouteItem): RouteMedia | null {
    return route.media?.find(
        (media) => getMediaType(media.file) === 'image',
    ) ?? null;
}

function getFirstVideo(route: RouteItem): RouteMedia | null {
    return route.media?.find(
        (media) => getMediaType(media.file) === 'video',
    ) ?? null;
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
                    <ArrowRight :size="17" />
                </a>
            </div>

            <div class="landing-experience-grid landing-routes-grid">
                <a
                    v-for="route in props.routes"
                    :key="route.id"
                    :href="getRouteUrl(route)"
                    class="landing-route-card"
                    :aria-label="`${route.title}${hasVideo(route) ? ', video del recorrido disponible' : ''}`"
                >
                    <div class="landing-route-media">
                        <img
                            v-if="getFirstImage(route)"
                            :src="getMediaUrl(getFirstImage(route)!.file)"
                            :alt="route.title"
                            loading="lazy"
                        />

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

                        <div
                            v-else
                            class="landing-route-image-empty"
                        >
                            <Star :size="36" />
                            <span>Sin imagen</span>
                        </div>

                        <div class="landing-route-overlay"></div>

                        <div
                            v-if="hasVideo(route)"
                            class="landing-video-label"
                        >
                            <span class="landing-video-dot"></span>
                            VIDEO DEL RECORRIDO
                        </div>

                        <div
                            v-if="hasVideo(route)"
                            class="landing-video-center"
                            aria-hidden="true"
                        >
                            <span class="landing-video-play">
                                <span class="landing-video-triangle"></span>
                            </span>
                        </div>

                        <div class="landing-route-content">
                            <div class="landing-route-text">
                                <span
                                    v-if="hasVideo(route)"
                                    class="landing-route-type"
                                >
                                    DESCUBRE LA RUTA
                                </span>

                                <h3>{{ route.title }}</h3>

                                <p v-if="route.description">
                                    {{ route.description }}
                                </p>
                            </div>

                            <span class="landing-route-arrow">
                                <ArrowRight :size="21" />
                            </span>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </section>
</template>

<style scoped>
/* Grid */
.landing-routes-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 30px;
}

/* Tarjeta con marco degradado */
.landing-route-card {
    display: block;
    min-width: 0;
    padding: 3px;
    border-radius: 25px;
    color: inherit;
    text-decoration: none;
    background: linear-gradient(
        135deg,
        #249edb 0%,
        #6753b7 50%,
        #d94c9a 100%
    );
    box-shadow: 0 12px 28px rgb(23 43 77 / 13%);
    transition:
        transform 0.35s ease,
        box-shadow 0.35s ease;
}

/* Imagen más alta */
.landing-route-media {
    position: relative;
    isolation: isolate;
    overflow: hidden;
    width: 100%;
    aspect-ratio: 5 / 4;
    padding: 5px;
    border-radius: 22px;
}

/* Borde interior */
.landing-route-media::before {
    content: '';
    position: absolute;
    inset: 5px;
    z-index: 5;
    border: 1px solid rgb(255 255 255 / 75%);
    border-radius: 17px;
    pointer-events: none;
}

/* Imagen y video */
.landing-route-media img,
.landing-route-video-thumbnail {
    display: block;
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 17px;
    transition: transform 0.55s ease;
}

/* Overlay */
.landing-route-overlay {
    position: absolute;
    inset: 5px;
    z-index: 1;
    border-radius: 17px;
    background: linear-gradient(
        180deg,
        rgb(15 27 48 / 15%) 0%,
        rgb(15 27 48 / 10%) 25%,
        rgb(15 27 48 / 48%) 58%,
        rgb(10 20 40 / 90%) 100%
    );
    transition: background 0.35s ease;
}

/* Etiqueta de video */
.landing-video-label {
    position: absolute;
    top: 20px;
    left: 20px;
    z-index: 7;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 9px 14px;
    border: 1px solid rgb(255 255 255 / 65%);
    border-radius: 30px;
    background: #fff;
    color: #1769a8;
    font-size: 10px;
    font-weight: 800;
    letter-spacing: 1px;
    box-shadow: 0 3px 12px rgb(0 0 0 / 12%);
}

.landing-video-dot {
    width: 8px;
    height: 8px;
    flex-shrink: 0;
    border-radius: 50%;
    background: #d94c9a;
}

/* Play central */
.landing-video-center {
    position: absolute;
    top: 50%;
    left: 50%;
    z-index: 10;
    display: flex;
    width: 100px;
    height: 100px;
    align-items: center;
    justify-content: center;
    transform: translate(-50%, -50%);
    pointer-events: none;
}

.landing-video-play {
    position: relative;
    display: flex;
    width: 82px;
    height: 82px;
    align-items: center;
    justify-content: center;
    border: 4px solid #fff;
    border-radius: 50%;
    background: linear-gradient(135deg, #249edb 0%, #6753b7 100%);
    box-shadow:
        0 0 0 8px rgb(255 255 255 / 30%),
        0 8px 28px rgb(0 0 0 / 45%);
    transition:
        transform 0.3s ease,
        background 0.3s ease,
        box-shadow 0.3s ease;
}

.landing-video-triangle {
    display: block;
    width: 0;
    height: 0;
    margin-left: 7px;
    border-top: 15px solid transparent;
    border-bottom: 15px solid transparent;
    border-left: 23px solid #fff;
    filter: drop-shadow(0 1px 2px rgb(0 0 0 / 20%));
}

/* Texto inferior */
.landing-route-content {
    position: absolute;
    right: 0;
    bottom: 0;
    left: 0;
    z-index: 4;
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 15px;
    padding: 28px 26px;
}

.landing-route-text {
    min-width: 0;
}

.landing-route-type {
    display: block;
    margin-bottom: 7px;
    color: #bce9ff;
    font-size: 10px;
    font-weight: 800;
    letter-spacing: 1.5px;
}

.landing-route-content h3 {
    margin: 0;
    color: #249edb;
    font-size: clamp(21px, 2.2vw, 29px);
    font-weight: 900;
    line-height: 1.2;
    text-shadow: 0 2px 8px rgb(0 0 0 / 65%);
    transition:
        color 0.3s ease,
        transform 0.3s ease;
}

.landing-route-content p {
    margin: 10px 0 0;
    color: #fff;
    font-size: 14px;
    font-weight: 500;
    line-height: 1.55;
    text-shadow: 0 2px 7px rgb(0 0 0 / 90%);
}

/* Flecha */
.landing-route-arrow {
    display: flex;
    width: 46px;
    height: 46px;
    flex-shrink: 0;
    align-items: center;
    justify-content: center;
    border: 1px solid rgb(255 255 255 / 65%);
    border-radius: 50%;
    background: rgb(255 255 255 / 18%);
    color: white;
    backdrop-filter: blur(8px);
    transition:
        background 0.3s ease,
        transform 0.3s ease;
}

/* Hover */
.landing-route-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 19px 38px rgb(23 43 77 / 22%);
}

.landing-route-card:hover .landing-route-media img,
.landing-route-card:hover .landing-route-video-thumbnail {
    transform: scale(1.045);
}

.landing-route-card:hover .landing-route-overlay {
    background: linear-gradient(
        180deg,
        rgb(15 27 48 / 20%) 0%,
        rgb(15 27 48 / 15%) 25%,
        rgb(39 25 85 / 55%) 58%,
        rgb(34 20 75 / 94%) 100%
    );
}

.landing-route-card:hover .landing-route-content h3 {
    color: #a78bfa;
    transform: translateX(3px);
}

.landing-route-card:hover .landing-video-play {
    transform: scale(1.1);
    background: linear-gradient(135deg, #6753b7, #d94c9a);
    box-shadow:
        0 0 0 10px rgb(255 255 255 / 35%),
        0 10px 30px rgb(0 0 0 / 45%);
}

.landing-route-card:hover .landing-route-arrow {
    transform: translateX(3px);
    background: #6753b7;
}

/* Sin imagen */
.landing-route-image-empty {
    display: flex;
    width: 100%;
    height: 100%;
    align-items: center;
    justify-content: center;
    flex-direction: column;
    gap: 12px;
    border-radius: 17px;
    background: #f3f4f6;
    color: #737373;
}

/* Accesibilidad */
.landing-route-card:focus-visible {
    outline: 3px solid #249edb;
    outline-offset: 5px;
}

/* Tablet */
@media (max-width: 768px) {
    .landing-routes-grid {
        gap: 18px;
    }

    .landing-route-card {
        padding: 3px;
        border-radius: 19px;
    }

    .landing-route-media {
        aspect-ratio: 4 / 5;
        padding: 4px;
        border-radius: 16px;
    }

    .landing-route-media::before {
        inset: 4px;
        border-radius: 12px;
    }

    .landing-route-media img,
    .landing-route-video-thumbnail {
        border-radius: 12px;
    }

    .landing-route-overlay {
        inset: 4px;
        border-radius: 12px;
    }

    .landing-video-label {
        top: 13px;
        left: 13px;
        padding: 7px 10px;
        font-size: 8px;
    }

    .landing-video-play {
        width: 68px;
        height: 68px;
    }

    .landing-video-triangle {
        border-top-width: 12px;
        border-bottom-width: 12px;
        border-left-width: 19px;
    }

    .landing-route-content {
        padding: 20px 16px;
        gap: 8px;
    }

    .landing-route-content h3 {
        font-size: 19px;
    }

    .landing-route-content p {
        font-size: 12px;
    }

    .landing-route-arrow {
        width: 36px;
        height: 36px;
    }
}

/* Móvil */
@media (max-width: 480px) {
    .landing-routes-grid {
        grid-template-columns: 1fr;
        gap: 24px;
    }

    .landing-route-media {
        aspect-ratio: 5 / 4;
    }

    .landing-route-content {
        padding: 22px;
    }

    .landing-route-content h3 {
        font-size: 23px;
    }

    .landing-route-content p {
        font-size: 13px;
    }

    .landing-video-play {
        width: 74px;
        height: 74px;
    }
}

/* Movimiento reducido */
@media (prefers-reduced-motion: reduce) {
    .landing-route-card,
    .landing-route-media img,
    .landing-route-video-thumbnail,
    .landing-video-play,
    .landing-route-arrow,
    .landing-route-content h3 {
        transition: none;
    }
}
</style>
