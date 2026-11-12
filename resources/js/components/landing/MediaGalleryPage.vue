
<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    ArrowLeft,
    Camera,
    ChevronLeft,
    ChevronRight,
    Play,
    X,
    Image as ImageIcon,
    Film,
    Images,
} from 'lucide-vue-next';
import {
    computed,
    onBeforeUnmount,
    onMounted,
    ref,
} from 'vue';

import LandingFooter from '@/components/landing/LandingFooter.vue';
import LandingHeader from '@/components/landing/LandingHeader.vue';

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

interface GalleryItem {
    id: number;
    image: string;
    sort_order?: number;
    race?: {
        id: number;
        name: string;
        slug: string;
    };
}

interface RaceItem {
    id: number;
    name: string;
    slug: string;
}

interface MediaItem {
    id: number;
    file: string;
    title: string | null;
    sort_order: number;
}

const props = defineProps<{
    route?: RouteItem;
    race?: RaceItem;
    gallery?: GalleryItem[];
}>();

const isRoute = computed(() => !!props.route);

const pageTitle = computed(() =>
    props.route?.title ?? props.race?.name ?? 'Galería',
);

const pageDescription = computed(() =>
    props.route?.description ?? null,
);

const backUrl = computed(() =>
    props.route
        ? '/rutas'
        : props.race
            ? `/carreras/${props.race.slug}`
            : '/rutas',
);

const backText = computed(() =>
    props.route ? 'Volver a las rutas' : 'Volver a la carrera',
);

const media = computed<MediaItem[]>(() => {
    const items: MediaItem[] = props.route
        ? (props.route.media ?? []).map((item) => ({
            id: item.id,
            file: item.file,
            title: item.title,
            sort_order: item.sort_order ?? 0,
        }))
        : (props.gallery ?? []).map((item) => ({
            id: item.id,
            file: item.image,
            title: null,
            sort_order: item.sort_order ?? 0,
        }));

    return [...items].sort(
        (a, b) => a.sort_order - b.sort_order,
    );
});

const selectedIndex = ref<number | null>(null);

const selectedMedia = computed(() =>
    selectedIndex.value === null
        ? null
        : media.value[selectedIndex.value] ?? null,
);

const imageCount = computed(() =>
    media.value.filter((item) => !isVideo(item.file)).length,
);

const videoCount = computed(() =>
    media.value.filter((item) => isVideo(item.file)).length,
);

const mediaUrl = (file: string | null | undefined): string => {
    if (!file) return '';

    if (
        file.startsWith('http://') ||
        file.startsWith('https://') ||
        file.startsWith('/')
    ) {
        return file;
    }

    return `/storage/${file}`;
};

const isVideo = (file: string | null | undefined): boolean =>
    !!file && /\.(mp4|webm|mov|m4v|ogg)$/i.test(file);

const openMedia = (index: number) => {
    selectedIndex.value = index;
    document.body.style.overflow = 'hidden';
};

const closeMedia = () => {
    selectedIndex.value = null;
    document.body.style.overflow = '';
};

const previousMedia = () => {
    if (selectedIndex.value === null || media.value.length === 0) return;

    selectedIndex.value =
        selectedIndex.value === 0
            ? media.value.length - 1
            : selectedIndex.value - 1;
};

const nextMedia = () => {
    if (selectedIndex.value === null || media.value.length === 0) return;

    selectedIndex.value =
        selectedIndex.value === media.value.length - 1
            ? 0
            : selectedIndex.value + 1;
};

const handleKeydown = (event: KeyboardEvent) => {
    if (selectedIndex.value === null) return;

    if (event.key === 'Escape') closeMedia();
    if (event.key === 'ArrowLeft') previousMedia();
    if (event.key === 'ArrowRight') nextMedia();
};

onMounted(() => {
    window.addEventListener('keydown', handleKeydown);
});

onBeforeUnmount(() => {
    document.body.style.overflow = '';
    window.removeEventListener('keydown', handleKeydown);
});
</script>

<template>
    <Head :title="`${isRoute ? 'Ruta' : 'Galería'} - ${pageTitle}`">
        <meta
            name="description"
            :content="pageDescription || `Revive los mejores momentos de ${pageTitle} en Sonríe Corriendo.`"
        />
    </Head>

    <div class="gallery-page">
        <LandingHeader />

        <main>
            <section class="gallery-hero">
                <div class="hero-background">
                    <div class="hero-grid"></div>
                    <span class="hero-orb hero-orb-one"></span>
                    <span class="hero-orb hero-orb-two"></span>
                    <span class="hero-orb hero-orb-three"></span>
                </div>

                <div class="gallery-container hero-content">
                    <Link :href="backUrl" class="gallery-back">
                        <span class="back-icon">
                            <ArrowLeft :size="17" />
                        </span>
                        {{ backText }}
                    </Link>

                    <div class="hero-main">
                        <div class="gallery-kicker">
                            <span></span>
                            SONRÍE CORRIENDO
                        </div>

                        <h1>{{ pageTitle }}</h1>

                        <div v-if="media.length" class="hero-stats">
                            <div class="hero-stat">
                                <Images :size="18" />
                                <strong>{{ media.length }}</strong>
                                <span>{{ media.length === 1 ? 'archivo' : 'archivos' }}</span>
                            </div>

                            <span class="stat-divider"></span>

                            <div v-if="imageCount" class="hero-stat">
                                <ImageIcon :size="18" />
                                <strong>{{ imageCount }}</strong>
                                <span>Fotos</span>
                            </div>

                            <span
                                v-if="imageCount && videoCount"
                                class="stat-divider"
                            ></span>

                            <div v-if="videoCount" class="hero-stat">
                                <Film :size="18" />
                                <strong>{{ videoCount }}</strong>
                                <span>Videos</span>
                            </div>
                        </div>
                    </div>

                    <div class="hero-art">
                        <div class="art-ring ring-one"></div>
                        <div class="art-ring ring-two"></div>
                        <div class="art-ring ring-three"></div>

                        <div class="art-center">
                            <Camera :size="43" />
                        </div>

                        <span class="art-dot dot-one"></span>
                        <span class="art-dot dot-two"></span>
                        <span class="art-dot dot-three"></span>
                    </div>
                </div>

                <div class="hero-bottom"></div>
            </section>

            <section class="gallery-section">
                <div class="gallery-container">
                    <div
                        v-if="pageDescription"
                        class="description-card"
                    >
                        <div class="description-icon">
                            <Camera :size="23" />
                        </div>

                        <div class="description-content">
                            <span class="description-label">
                                DESCUBRE ESTA EXPERIENCIA
                            </span>
                            <p>{{ pageDescription }}</p>
                        </div>

                        <div class="description-decoration"></div>
                    </div>

                    <div class="gallery-heading">
                        <div v-if="media.length" class="gallery-count">
                            <span class="count-icon">
                                <Images :size="19" />
                            </span>
                            <span>
                                <strong>{{ media.length }}</strong>
                                {{ media.length === 1 ? 'archivo' : 'archivos' }}
                            </span>
                        </div>
                    </div>

                    <div v-if="media.length" class="gallery-grid">
                        <button
                            v-for="(item, index) in media"
                            :key="item.id"
                            type="button"
                            class="gallery-item"
                            :class="{
                                'gallery-item-video': isVideo(item.file),
                            }"
                            :aria-label="`Ver ${isVideo(item.file) ? 'video' : 'fotografía'} ${index + 1}`"
                            @click="openMedia(index)"
                        >
                            <span class="item-frame">
                                <img
                                    v-if="!isVideo(item.file)"
                                    :src="mediaUrl(item.file)"
                                    :alt="item.title || `${pageTitle} - Foto ${index + 1}`"
                                    loading="lazy"
                                />

                                <video
                                    v-else
                                    :src="mediaUrl(item.file) + '#t=0.5'"
                                    muted
                                    playsinline
                                    preload="metadata"
                                    tabindex="-1"
                                ></video>
                            </span>

                            <span class="item-overlay"></span>

                            <span class="item-top">
                                <span
                                    class="item-type"
                                    :class="isVideo(item.file) ? 'type-video' : 'type-image'"
                                >
                                    <Play
                                        v-if="isVideo(item.file)"
                                        :size="12"
                                        fill="currentColor"
                                    />
                                    <Camera v-else :size="13" />
                                    {{ isVideo(item.file) ? 'VIDEO' : 'FOTOGRAFÍA' }}
                                </span>

                                <span class="item-number">
                                    {{ String(index + 1).padStart(2, '0') }}
                                </span>
                            </span>

                            <span class="item-center">
                                <span class="item-play">
                                    <Play
                                        v-if="isVideo(item.file)"
                                        :size="24"
                                        fill="currentColor"
                                    />
                                    <Camera v-else :size="23" />
                                </span>
                            </span>

                            <span class="item-bottom">
                                <span class="item-text">
                                    <span class="item-eyebrow">
                                        {{ isVideo(item.file) ? 'REVIVE EL MOMENTO' : 'SONRÍE CORRIENDO' }}
                                    </span>
                                    <strong>
                                        {{ item.title || (isVideo(item.file) ? 'Revive este momento' : 'Un momento para recordar') }}
                                    </strong>
                                </span>
                            </span>
                        </button>
                    </div>

                    <div v-else class="gallery-empty">
                        <div class="empty-orbit orbit-one"></div>
                        <div class="empty-orbit orbit-two"></div>

                        <div class="empty-icon">
                            <Camera :size="32" />
                        </div>

                        <span class="empty-eyebrow">PRÓXIMAMENTE</span>

                        <p>
                            Aún no hay fotografías ni videos disponibles.
                        </p>

                        <Link :href="backUrl" class="empty-button">
                            <ArrowLeft :size="16" />
                            {{ backText }}
                        </Link>
                    </div>
                </div>
            </section>
        </main>

        <LandingFooter />

        <Teleport to="body">
            <div
                v-if="selectedMedia"
                class="gallery-lightbox"
                @click.self="closeMedia"
            >
                <div class="lightbox-top">
                    <div class="lightbox-brand">
                        <span class="lightbox-brand-icon">
                            <Camera :size="17" />
                        </span>
                        <span>SONRÍE <strong>CORRIENDO</strong></span>
                    </div>

                    <span class="lightbox-position">
                        {{ String((selectedIndex ?? 0) + 1).padStart(2, '0') }}
                        <i>/</i>
                        {{ String(media.length).padStart(2, '0') }}
                    </span>
                </div>

                <button
                    type="button"
                    class="lightbox-close"
                    aria-label="Cerrar"
                    @click="closeMedia"
                >
                    <X :size="23" />
                </button>

                <button
                    v-if="media.length > 1"
                    type="button"
                    class="lightbox-arrow arrow-left"
                    aria-label="Archivo anterior"
                    @click="previousMedia"
                >
                    <ChevronLeft :size="28" />
                </button>

                <div class="lightbox-content">
                    <img
                        v-if="!isVideo(selectedMedia.file)"
                        :src="mediaUrl(selectedMedia.file)"
                        :alt="selectedMedia.title || `${pageTitle} - Fotografía`"
                    />

                    <video
                        v-else
                        :src="mediaUrl(selectedMedia.file)"
                        controls
                        autoplay
                        playsinline
                        preload="metadata"
                    ></video>

                    <div
                        v-if="selectedMedia.title"
                        class="lightbox-caption"
                    >
                        {{ selectedMedia.title }}
                    </div>
                </div>

                <button
                    v-if="media.length > 1"
                    type="button"
                    class="lightbox-arrow arrow-right"
                    aria-label="Siguiente archivo"
                    @click="nextMedia"
                >
                    <ChevronRight :size="28" />
                </button>

                <div class="lightbox-bottom">
                    <span>{{ pageTitle }}</span>
                    <span>Usa las flechas del teclado para navegar</span>
                </div>
            </div>
        </Teleport>
    </div>
</template>

<style scoped>

/* BASE */
.gallery-page {
    --blue-deep: #12558c;
    --blue: #1769a8;
    --blue-light: #249edb;
    --purple: #6753b7;
    --pink: #d94c9a;
    --pink-light: #f48bb0;
    --text: #172b4d;
    --muted: #64748b;
    --bg: #f8fafc;

    width: 100%;
    min-height: 100vh;
    overflow: hidden;
    background: var(--bg);
    color: var(--text);
}

.gallery-container {
    width: min(1180px, calc(100% - 40px));
    margin: 0 auto;
}

.gallery-page button {
    font: inherit;
}

/* HERO */
.gallery-hero {
    position: relative;
    display: flex;
    min-height: 470px;
    align-items: center;
    overflow: hidden;
    isolation: isolate;
    background: linear-gradient(
        115deg,
        #104b7d 0%,
        #1769a8 36%,
        #249edb 66%,
        #6753b7 100%
    );
}

.hero-background {
    position: absolute;
    z-index: -1;
    inset: 0;
    overflow: hidden;
    background:
        radial-gradient(ellipse at 5% 95%, rgb(36 158 219 / 35%), transparent 40%),
        radial-gradient(ellipse at 90% 15%, rgb(217 76 154 / 35%), transparent 40%);
}

.hero-grid {
    position: absolute;
    inset: 0;
    opacity: .13;
    background-image: radial-gradient(#fff 1px, transparent 1px);
    background-size: 25px 25px;
}

.hero-orb {
    position: absolute;
    display: block;
    border: 1px solid rgb(255 255 255 / 13%);
    border-radius: 50%;
}

.hero-orb-one {
    top: -340px;
    left: -130px;
    width: 580px;
    height: 580px;
}

.hero-orb-two {
    right: -170px;
    bottom: -350px;
    width: 650px;
    height: 650px;
}

.hero-orb-three {
    top: -200px;
    right: 28%;
    width: 300px;
    height: 300px;
}

.hero-content {
    position: relative;
    z-index: 2;
    padding: 48px 0 85px;
}

.gallery-back {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 37px;
    color: rgb(255 255 255 / 85%);
    font-size: 13px;
    font-weight: 700;
    text-decoration: none;
    transition: .25s ease;
}

.gallery-back:hover {
    color: #fff;
    transform: translateX(-4px);
}

.back-icon {
    display: flex;
    width: 37px;
    height: 37px;
    align-items: center;
    justify-content: center;
    border: 1px solid rgb(255 255 255 / 27%);
    border-radius: 12px;
    background: rgb(255 255 255 / 12%);
    backdrop-filter: blur(10px);
}

.hero-main {
    position: relative;
    max-width: 760px;
}

.hero-icon {
    display: flex;
    width: 57px;
    height: 57px;
    align-items: center;
    justify-content: center;
    margin-bottom: 20px;
    border: 1px solid rgb(255 255 255 / 35%);
    border-radius: 18px;
    background: linear-gradient(135deg, rgb(255 255 255 / 24%), rgb(255 255 255 / 9%));
    color: #fff;
    box-shadow: 0 12px 30px rgb(0 0 0 / 12%);
    backdrop-filter: blur(10px);
}

.gallery-kicker {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 13px;
    color: rgb(255 255 255 / 82%);
    font-size: 10px;
    font-weight: 900;
    letter-spacing: 2px;
}

.gallery-kicker > span {
    width: 25px;
    height: 2px;
    border-radius: 5px;
    background: var(--pink-light);
}

.gallery-kicker i {
    width: 5px;
    height: 5px;
    border-radius: 50%;
    background: var(--pink-light);
}

.gallery-hero h1 {
    max-width: 800px;
    margin: 0;
    color: #fff;
    font-size: clamp(42px, 6vw, 72px);
    font-weight: 900;
    line-height: 1.04;
    letter-spacing: -2.8px;
    text-shadow: 0 6px 24px rgb(0 0 0 / 15%);
    text-wrap: balance;
}

.hero-description {
    max-width: 530px;
    margin: 19px 0 0;
    color: rgb(255 255 255 / 85%);
    font-size: 15px;
    line-height: 1.75;
}

.hero-stats {
    display: inline-flex;
    align-items: center;
    gap: 17px;
    margin-top: 29px;
    padding: 13px 18px;
    border: 1px solid rgb(255 255 255 / 23%);
    border-radius: 15px;
    background: rgb(255 255 255 / 12%);
    color: #fff;
    backdrop-filter: blur(12px);
}

.hero-stat {
    display: flex;
    align-items: center;
    gap: 7px;
    color: rgb(255 255 255 / 88%);
    font-size: 12px;
}

.hero-stat strong {
    color: #fff;
    font-size: 15px;
    font-weight: 900;
}

.stat-divider {
    width: 1px;
    height: 18px;
    background: rgb(255 255 255 / 28%);
}

.hero-art {
    position: absolute;
    top: 50%;
    right: 2%;
    width: 310px;
    height: 310px;
    transform: translateY(-50%);
    pointer-events: none;
}

.art-ring {
    position: absolute;
    top: 50%;
    left: 50%;
    border: 1px solid rgb(255 255 255 / 17%);
    border-radius: 50%;
    transform: translate(-50%, -50%);
}

.ring-one {
    width: 100%;
    height: 100%;
}

.ring-two {
    width: 76%;
    height: 76%;
    border-color: rgb(255 255 255 / 23%);
}

.ring-three {
    width: 52%;
    height: 52%;
    border-color: rgb(255 255 255 / 28%);
}

.art-center {
    position: absolute;
    top: 50%;
    left: 50%;
    display: flex;
    width: 100px;
    height: 100px;
    align-items: center;
    justify-content: center;
    border: 1px solid rgb(255 255 255 / 40%);
    border-radius: 30px;
    background: linear-gradient(135deg, rgb(255 255 255 / 28%), rgb(255 255 255 / 8%));
    color: #fff;
    box-shadow: 0 20px 55px rgb(0 0 0 / 14%);
    backdrop-filter: blur(12px);
    transform: translate(-50%, -50%) rotate(-10deg);
}

.art-dot {
    position: absolute;
    width: 13px;
    height: 13px;
    border: 3px solid rgb(255 255 255 / 70%);
    border-radius: 50%;
    background: var(--pink-light);
    box-shadow: 0 0 20px rgb(244 139 176 / 55%);
}

.dot-one {
    top: 15%;
    right: 23%;
}

.dot-two {
    bottom: 18%;
    left: 13%;
    width: 9px;
    height: 9px;
    background: var(--blue-light);
}

.dot-three {
    right: 8%;
    bottom: 35%;
    width: 7px;
    height: 7px;
}

.hero-bottom {
    position: absolute;
    right: -5%;
    bottom: -82px;
    left: -5%;
    height: 115px;
    border-radius: 50% 50% 0 0;
    background: var(--bg);
}

/* SECCIÓN */
.gallery-section {
    position: relative;
    padding: 44px 0 85px;
}

.description-card {
    position: relative;
    display: flex;
    align-items: flex-start;
    gap: 19px;
    overflow: hidden;
    margin-bottom: 66px;
    padding: 27px 30px;
    border: 1px solid #e4edf5;
    border-radius: 20px;
    background: #fff;
    box-shadow: 0 13px 35px rgb(23 43 77 / 5%);
}

.description-icon {
    position: relative;
    z-index: 1;
    display: flex;
    width: 49px;
    height: 49px;
    flex-shrink: 0;
    align-items: center;
    justify-content: center;
    border-radius: 15px;
    background: linear-gradient(135deg, #eaf6fc, #f8eafa);
    color: var(--blue);
}

.description-content {
    position: relative;
    z-index: 1;
    min-width: 0;
}

.description-label {
    display: block;
    margin-bottom: 8px;
    color: var(--blue);
    font-size: 10px;
    font-weight: 900;
    letter-spacing: 1.5px;
}

.description-content p {
    margin: 0;
    color: #52647b;
    font-size: 14px;
    line-height: 1.85;
}

.description-decoration {
    position: absolute;
    top: -90px;
    right: -45px;
    width: 210px;
    height: 210px;
    border: 27px solid rgb(36 158 219 / 5%);
    border-radius: 50%;
}

/* TÍTULO DE GALERÍA */
.gallery-heading {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 25px;
    margin-bottom: 30px;
}

.section-eyebrow {
    display: flex;
    align-items: center;
    gap: 9px;
    margin-bottom: 12px;
    color: var(--pink);
    font-size: 10px;
    font-weight: 900;
    letter-spacing: 1.8px;
}

.section-eyebrow i {
    width: 25px;
    height: 2px;
    border-radius: 5px;
    background: linear-gradient(90deg, var(--blue-light), var(--pink));
}

.gallery-heading h2 {
    max-width: 650px;
    margin: 0;
    color: var(--text);
    font-size: clamp(30px, 4vw, 42px);
    font-weight: 900;
    line-height: 1.13;
    letter-spacing: -1.7px;
}

.gallery-heading h2 span {
    color: var(--blue-light);
}

.heading-content > p {
    margin: 12px 0 0;
    color: var(--muted);
    font-size: 13px;
    line-height: 1.7;
}

.gallery-count {
    display: flex;
    flex-shrink: 0;
    align-items: center;
    gap: 10px;
    padding: 10px 15px;
    border: 1px solid #e3ebf2;
    border-radius: 14px;
    background: #fff;
    color: var(--muted);
    font-size: 12px;
    font-weight: 700;
}

.gallery-count strong {
    color: var(--text);
    font-size: 15px;
    font-weight: 900;
}

.count-icon {
    display: flex;
    width: 35px;
    height: 35px;
    align-items: center;
    justify-content: center;
    border-radius: 11px;
    background: #eaf6fc;
    color: var(--blue);
}

/* GALERÍA */
.gallery-grid {
    grid-template-columns: repeat(3, minmax(0, 1fr));
    grid-auto-rows: 300px;
    gap: 18px;
    display: grid;
}

.gallery-item {
    position: relative;
    display: block;
    width: 100%;
    min-width: 0;
    height: 100%;
    padding: 3px;
    overflow: hidden;
    border: 0;
    border-radius: 20px;
    background: linear-gradient(135deg, #249edb, #6753b7 55%, #d94c9a);
    cursor: pointer;
    box-shadow: 0 9px 24px rgb(23 43 77 / 10%);
    isolation: isolate;
    transition: transform .35s ease, box-shadow .35s ease;
}

.item-frame {
    position: absolute;
    inset: 3px;
    overflow: hidden;
    border-radius: 17px;
    background: #dce8f2;
}

.item-frame img,
.item-frame video {
    display: block;
    width: 100%;
    height: 100%;
    border-radius: 17px;
    object-fit: cover;
    object-position: center;
    transition: transform .7s cubic-bezier(.2, .7, .2, 1), filter .4s ease;
}

.item-overlay {
    position: absolute;
    z-index: 1;
    inset: 3px;
    border-radius: 17px;
    background: linear-gradient(
        180deg,
        rgb(12 31 53 / 32%) 0%,
        transparent 34%,
        rgb(18 85 140 / 12%) 52%,
        rgb(13 29 51 / 85%) 100%
    );
    transition: background .4s ease;
}

.gallery-item::after {
    position: absolute;
    z-index: 2;
    inset: 3px;
    border: 1px solid rgb(255 255 255 / 35%);
    border-radius: 17px;
    content: '';
    pointer-events: none;
    transition: border-color .3s ease;
}

.gallery-item:hover {
    z-index: 3;
    transform: translateY(-6px);
    box-shadow: 0 20px 40px rgb(23 43 77 / 19%);
}

.gallery-item:hover .item-frame img,
.gallery-item:hover .item-frame video {
    transform: scale(1.09);
    filter: saturate(1.15);
}

.gallery-item:hover .item-overlay {
    background: linear-gradient(
        180deg,
        rgb(18 85 140 / 35%) 0%,
        rgb(103 83 183 / 20%) 42%,
        rgb(65 34 115 / 91%) 100%
    );
}

.gallery-item:hover::after {
    border-color: rgb(255 255 255 / 65%);
}

/* Etiquetas superiores */
.item-top {
    position: absolute;
    z-index: 4;
    top: 15px;
    right: 15px;
    left: 15px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
}

.item-type {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 7px 11px;
    border: 1px solid rgb(255 255 255 / 35%);
    border-radius: 30px;
    background: rgb(14 34 58 / 60%);
    color: #fff;
    font-size: 9px;
    font-weight: 900;
    letter-spacing: .8px;
    backdrop-filter: blur(10px);
}

.type-video {
    background: linear-gradient(135deg, #d94c9a, #a63e9a);
}

.type-image {
    background: linear-gradient(135deg, #1769a8, #249edb);
}

.item-number {
    display: flex;
    width: 30px;
    height: 30px;
    align-items: center;
    justify-content: center;
    border: 1px solid rgb(255 255 255 / 40%);
    border-radius: 50%;
    background: rgb(14 34 58 / 50%);
    color: #fff;
    font-size: 10px;
    font-weight: 900;
    backdrop-filter: blur(8px);
}

/* Botón central */
.item-center {
    position: absolute;
    z-index: 4;
    top: 50%;
    left: 50%;
    display: flex;
    width: 72px;
    height: 72px;
    align-items: center;
    justify-content: center;
    border: 1px solid rgb(255 255 255 / 55%);
    border-radius: 50%;
    background: rgb(255 255 255 / 17%);
    opacity: 0;
    backdrop-filter: blur(10px);
    transform: translate(-50%, -40%) scale(.75);
    transition: opacity .3s ease, transform .3s ease;
}

.item-play {
    display: flex;
    width: 51px;
    height: 51px;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--blue-light), var(--purple), var(--pink));
    color: #fff;
    box-shadow: 0 7px 23px rgb(0 0 0 / 25%);
}

.gallery-item:hover .item-center,
.gallery-item:focus-visible .item-center {
    opacity: 1;
    transform: translate(-50%, -50%) scale(1);
}

/* Texto inferior */
.item-bottom {
    position: absolute;
    z-index: 4;
    right: 17px;
    bottom: 16px;
    left: 17px;
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 10px;
}

.item-text {
    display: flex;
    min-width: 0;
    flex-direction: column;
    gap: 5px;
    text-align: left;
}

.item-eyebrow {
    color: rgb(255 255 255 / 76%);
    font-size: 8px;
    font-weight: 900;
    letter-spacing: 1.3px;
}

.item-text strong {
    display: block;
    overflow: hidden;
    color: #fff;
    font-size: 13px;
    font-weight: 900;
    line-height: 1.4;
    text-overflow: ellipsis;
    text-shadow: 0 2px 8px rgb(0 0 0 / 50%);
}

/* VACÍO */
.gallery-empty {
    position: relative;
    display: flex;
    align-items: center;
    flex-direction: column;
    overflow: hidden;
    padding: 76px 25px;
    border: 1px solid #e3ebf2;
    border-radius: 24px;
    background:
        radial-gradient(circle at 10% 10%, rgb(36 158 219 / 8%), transparent 32%),
        radial-gradient(circle at 90% 90%, rgb(217 76 154 / 8%), transparent 32%),
        #fff;
    text-align: center;
}

.empty-icon {
    position: relative;
    z-index: 1;
    display: flex;
    width: 78px;
    height: 78px;
    align-items: center;
    justify-content: center;
    margin-bottom: 20px;
    border: 1px solid #e5eaf2;
    border-radius: 24px;
    background: linear-gradient(135deg, #eaf6fc, #f8eafa);
    color: var(--blue);
    box-shadow: 0 12px 28px rgb(23 43 77 / 8%);
}

.empty-eyebrow {
    position: relative;
    z-index: 1;
    margin-bottom: 10px;
    color: var(--pink);
    font-size: 10px;
    font-weight: 900;
    letter-spacing: 1.8px;
}

.gallery-empty h3 {
    position: relative;
    z-index: 1;
    max-width: 510px;
    margin: 0;
    color: var(--text);
    font-size: 26px;
    font-weight: 900;
    line-height: 1.25;
}

.gallery-empty p {
    position: relative;
    z-index: 1;
    max-width: 470px;
    margin: 13px 0 24px;
    color: var(--muted);
    font-size: 13px;
    line-height: 1.8;
}

.empty-button {
    position: relative;
    z-index: 1;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 13px 20px;
    border-radius: 12px;
    background: linear-gradient(120deg, var(--blue-light), var(--blue), var(--purple));
    color: #fff;
    font-size: 12px;
    font-weight: 800;
    text-decoration: none;
    box-shadow: 0 8px 20px rgb(23 105 168 / 22%);
    transition: transform .25s ease, box-shadow .25s ease;
}

.empty-button:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 26px rgb(23 105 168 / 30%);
}

.empty-orbit {
    position: absolute;
    width: 190px;
    height: 190px;
    border: 25px solid rgb(36 158 219 / 5%);
    border-radius: 50%;
}

.orbit-one {
    top: -110px;
    left: -80px;
}

.orbit-two {
    right: -100px;
    bottom: -110px;
    border-color: rgb(217 76 154 / 6%);
}

/* CIERRE */
.gallery-bottom {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 18px;
    margin-top: 43px;
    text-align: center;
}

.bottom-line {
    width: 55px;
    height: 1px;
    background: linear-gradient(90deg, transparent, #cbd8e5);
}

.bottom-line:last-child {
    transform: rotate(180deg);
}

.gallery-bottom p {
    margin: 0;
    color: var(--muted);
    font-size: 12px;
}

.gallery-bottom strong {
    color: var(--blue);
    font-weight: 900;
}

/* LIGHTBOX */
.gallery-lightbox {
    position: fixed;
    z-index: 9999;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 75px 80px;
    background: rgb(7 18 34 / 97%);
    backdrop-filter: blur(13px);
    animation: lightbox-fade .2s ease;
}

@keyframes lightbox-fade {
    from { opacity: 0; }
    to { opacity: 1; }
}

.lightbox-top {
    position: absolute;
    top: 22px;
    right: 28px;
    left: 28px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.lightbox-brand {
    display: flex;
    align-items: center;
    gap: 10px;
    color: rgb(255 255 255 / 80%);
    font-size: 10px;
    font-weight: 800;
    letter-spacing: 1.4px;
}

.lightbox-brand strong {
    color: #fff;
}

.lightbox-brand-icon {
    display: flex;
    width: 35px;
    height: 35px;
    align-items: center;
    justify-content: center;
    border: 1px solid rgb(255 255 255 / 20%);
    border-radius: 11px;
    background: rgb(255 255 255 / 9%);
    color: #fff;
}

.lightbox-position {
    color: #fff;
    font-size: 13px;
    font-weight: 900;
}

.lightbox-position i {
    margin: 0 6px;
    color: rgb(255 255 255 / 40%);
    font-style: normal;
}

.lightbox-content {
    position: relative;
    display: flex;
    max-width: min(1100px, 90vw);
    max-height: 82vh;
    align-items: center;
    justify-content: center;
}

.lightbox-content img,
.lightbox-content video {
    display: block;
    max-width: 100%;
    max-height: 76vh;
    border: 1px solid rgb(255 255 255 / 13%);
    border-radius: 13px;
    object-fit: contain;
    box-shadow: 0 25px 90px rgb(0 0 0 / 45%);
}

.lightbox-caption {
    position: absolute;
    right: 0;
    bottom: -40px;
    left: 0;
    color: rgb(255 255 255 / 88%);
    font-size: 12px;
    font-weight: 700;
    text-align: center;
}

.lightbox-close,
.lightbox-arrow {
    position: absolute;
    z-index: 3;
    display: flex;
    width: 47px;
    height: 47px;
    align-items: center;
    justify-content: center;
    padding: 0;
    border: 1px solid rgb(255 255 255 / 20%);
    border-radius: 50%;
    background: rgb(255 255 255 / 9%);
    color: #fff;
    cursor: pointer;
    backdrop-filter: blur(10px);
    transition: background .2s ease, transform .2s ease;
}

.lightbox-close:hover,
.lightbox-arrow:hover {
    background: linear-gradient(135deg, var(--blue), var(--purple), var(--pink));
}

.lightbox-close {
    top: 20px;
    right: 28px;
}

.arrow-left {
    top: 50%;
    left: 25px;
    transform: translateY(-50%);
}

.arrow-right {
    top: 50%;
    right: 25px;
    transform: translateY(-50%);
}

.arrow-left:hover,
.arrow-right:hover {
    transform: translateY(-50%) scale(1.08);
}

.lightbox-bottom {
    position: absolute;
    right: 30px;
    bottom: 22px;
    left: 30px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    color: rgb(255 255 255 / 55%);
    font-size: 11px;
}

.lightbox-bottom span:first-child {
    color: rgb(255 255 255 / 88%);
    font-weight: 800;
}

/* RESPONSIVE */
@media (max-width: 1050px) {
    .hero-art {
        right: -2%;
        opacity: .6;
        transform: translateY(-50%) scale(.82);
    }

    .gallery-grid {
        grid-auto-rows: 215px;
        gap: 15px;
    }
}

@media (max-width: 850px) {
    .gallery-hero {
        min-height: 430px;
    }

    .hero-art {
        right: -9%;
        opacity: .3;
        transform: translateY(-50%) scale(.72);
    }

    .gallery-grid {
        grid-template-columns: repeat(3, minmax(0, 1fr));
        grid-auto-rows: 205px;
        gap: 14px;
    }

    .description-card {
        margin-bottom: 52px;
    }
}

@media (max-width: 600px) {
    .gallery-container {
        width: calc(100% - 30px);
    }

    .gallery-hero {
        min-height: 405px;
    }

    .hero-content {
        padding: 35px 0 68px;
    }

    .gallery-back {
        margin-bottom: 30px;
    }

    .hero-icon {
        width: 48px;
        height: 48px;
        margin-bottom: 16px;
        border-radius: 15px;
    }

    .gallery-kicker {
        gap: 7px;
        font-size: 8px;
        letter-spacing: 1.2px;
    }

    .gallery-hero h1 {
        font-size: clamp(37px, 10vw, 53px);
        letter-spacing: -1.8px;
    }

    .hero-description {
        max-width: 350px;
        font-size: 13px;
    }

    .hero-stats {
        gap: 10px;
        margin-top: 23px;
        padding: 10px 12px;
    }

    .hero-stat {
        gap: 5px;
        font-size: 10px;
    }

    .hero-stat strong {
        font-size: 12px;
    }

    .hero-art {
        display: none;
    }

    .gallery-section {
        padding: 30px 0 55px;
    }

    .description-card {
        gap: 13px;
        margin-bottom: 43px;
        padding: 19px;
        border-radius: 16px;
    }

    .description-icon {
        width: 41px;
        height: 41px;
        border-radius: 12px;
    }

    .description-content p {
        font-size: 12px;
        line-height: 1.7;
    }

    .gallery-heading {
        align-items: flex-start;
        flex-direction: column;
        gap: 17px;
        margin-bottom: 22px;
    }

    .gallery-heading h2 {
        font-size: 30px;
        letter-spacing: -1px;
    }

    .heading-content > p {
        font-size: 12px;
    }

    .gallery-count {
        padding: 7px 11px;
    }

    .count-icon {
        width: 30px;
        height: 30px;
    }

    .gallery-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
        grid-auto-rows: 175px;
        gap: 11px;
    }

    .gallery-item {
        border-radius: 15px;
    }

    .item-frame,
    .item-frame img,
    .item-frame video,
    .item-overlay,
    .gallery-item::after {
        border-radius: 12px;
    }

    .item-top {
        top: 10px;
        right: 10px;
        left: 10px;
    }

    .item-type {
        gap: 4px;
        padding: 6px 8px;
        font-size: 8px;
    }

    .item-number {
        width: 25px;
        height: 25px;
        font-size: 9px;
    }

    .item-center {
        width: 53px;
        height: 53px;
    }

    .item-play {
        width: 39px;
        height: 39px;
    }

    .item-bottom {
        right: 11px;
        bottom: 11px;
        left: 11px;
    }

    .item-eyebrow {
        font-size: 7px;
    }

    .item-text strong {
        font-size: 10px;
    }

    .gallery-empty {
        padding: 55px 20px;
        border-radius: 19px;
    }

    .gallery-empty h3 {
        font-size: 21px;
    }

    .gallery-empty p {
        font-size: 12px;
    }

    .gallery-bottom {
        gap: 10px;
        margin-top: 30px;
    }

    .bottom-line {
        width: 20px;
    }

    .gallery-bottom p {
        font-size: 10px;
        line-height: 1.6;
    }

    .gallery-lightbox {
        padding: 65px 12px;
    }

    .lightbox-top {
        top: 15px;
        right: 15px;
        left: 15px;
    }

    .lightbox-close {
        top: 65px;
        right: 13px;
        width: 40px;
        height: 40px;
    }

    .lightbox-content {
        max-width: 100%;
        max-height: 75vh;
    }

    .lightbox-content img,
    .lightbox-content video {
        max-height: 70vh;
        border-radius: 8px;
    }

    .lightbox-arrow {
        width: 38px;
        height: 38px;
    }

    .arrow-left {
        left: 8px;
    }

    .arrow-right {
        right: 8px;
    }

    .lightbox-bottom {
        right: 15px;
        bottom: 15px;
        left: 15px;
        font-size: 9px;
    }

    .lightbox-bottom span:last-child {
        display: none;
    }
}

@media (max-width: 380px) {
    .gallery-grid {
        grid-auto-rows: 155px;
        gap: 8px;
    }

    .item-text strong {
        font-size: 9px;
    }

    .hero-stats {
        gap: 7px;
    }

    .hero-stat {
        font-size: 9px;
    }
}

@media (prefers-reduced-motion: reduce) {
    .gallery-page *,
    .gallery-page *::before,
    .gallery-page *::after {
        scroll-behavior: auto !important;
        animation-duration: .01ms !important;
        animation-iteration-count: 1 !important;
        transition-duration: .01ms !important;
    }
}

</style>
