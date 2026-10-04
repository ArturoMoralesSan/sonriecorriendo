<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    ArrowLeft,
    Camera,
    ChevronLeft,
    ChevronRight,
    X,
} from 'lucide-vue-next';
import {
    computed,
    onBeforeUnmount,
    ref,
} from 'vue';

import LandingFooter from '@/components/landing/LandingFooter.vue';
import LandingHeader from '@/components/landing/LandingHeader.vue';

const props = defineProps<{
    race: {
        id: number;
        name: string;
        slug: string;
    };
    gallery: Array<{
        id: number;
        image: string;
        sort_order?: number;
        race?: {
            id: number;
            name: string;
            slug: string;
        };
    }>;
}>();

const selectedIndex = ref<number | null>(null);

const images = computed(() => {
    return [...(props.gallery ?? [])].sort((a, b) => {
        return (a.sort_order ?? 0) - (b.sort_order ?? 0);
    });
});

const selectedImage = computed(() => {
    if (selectedIndex.value === null) {
        return null;
    }

    return images.value[selectedIndex.value] ?? null;
});

const imageUrl = (
    image: string | null | undefined,
) => {
    if (!image) {
        return '';
    }

    if (
        image.startsWith('http://') ||
        image.startsWith('https://') ||
        image.startsWith('/')
    ) {
        return image;
    }

    return `/storage/${image}`;
};

const isVideo = (
    media: string | null | undefined,
): boolean => {
    if (!media) {
        return false;
    }

    return /\.(mp4|webm|mov|m4v|ogg)$/i.test(media);
};

const mediaUrl = (
    media: string | null | undefined,
): string => {
    return imageUrl(media);
};

const openImage = (index: number) => {
    selectedIndex.value = index;
    document.body.style.overflow = 'hidden';
};

const closeImage = () => {
    selectedIndex.value = null;
    document.body.style.overflow = '';
};

const previousImage = () => {
    if (
        selectedIndex.value === null ||
        images.value.length === 0
    ) {
        return;
    }

    selectedIndex.value =
        selectedIndex.value === 0
            ? images.value.length - 1
            : selectedIndex.value - 1;
};

const nextImage = () => {
    if (
        selectedIndex.value === null ||
        images.value.length === 0
    ) {
        return;
    }

    selectedIndex.value =
        selectedIndex.value === images.value.length - 1
            ? 0
            : selectedIndex.value + 1;
};

const handleKeydown = (
    event: KeyboardEvent,
) => {
    if (selectedIndex.value === null) {
        return;
    }

    if (event.key === 'Escape') {
        closeImage();
    }

    if (event.key === 'ArrowLeft') {
        previousImage();
    }

    if (event.key === 'ArrowRight') {
        nextImage();
    }
};

window.addEventListener(
    'keydown',
    handleKeydown,
);

onBeforeUnmount(() => {
    document.body.style.overflow = '';

    window.removeEventListener(
        'keydown',
        handleKeydown,
    );
});
</script>

<template>
    <Head :title="`Galería - ${race.name}`">
        <meta
            name="description"
            :content="`Galería de fotos y videos de ${race.name} - Sonríe Corriendo.`"
        />
    </Head>

    <div class="gallery-page">
        <LandingHeader />

        <main>
            <!-- HERO -->
            <section class="gallery-hero">
                <div class="gallery-hero-background"></div>

                <div
                    class="gallery-container gallery-hero-content"
                >
                    <Link
                        :href="`/carreras/${race.slug}`"
                        class="gallery-back"
                    >
                        <ArrowLeft :size="17" />

                        Volver a la carrera
                    </Link>

                    <div class="gallery-hero-icon">
                        <Camera :size="25" />
                    </div>

                    <span class="gallery-kicker">
                        Galería de la carrera
                    </span>

                    <h1>
                        {{ race.name }}
                    </h1>

                    <p>
                        Revive los mejores momentos de esta carrera.
                    </p>
                </div>
            </section>

            <!-- GALLERY -->
            <section class="gallery-section">
                <div class="gallery-container">

                    <div class="gallery-heading">
                        <div>
                            <span>
                                Momentos
                            </span>

                            <h2>
                                Fotos y videos de la carrera
                            </h2>
                        </div>

                        <div
                            v-if="images.length"
                            class="gallery-count"
                        >
                            {{ images.length }}

                            {{
                                images.length === 1
                                    ? 'archivo'
                                    : 'archivos'
                            }}
                        </div>
                    </div>

                    <div
                        v-if="images.length"
                        class="gallery-grid"
                    >

                        <button
                            v-for="(item, index) in images"
                            :key="item.id"
                            type="button"
                            class="gallery-item"
                            @click="openImage(index)"
                        >

                            <!-- IMAGEN -->
                            <img
                                v-if="
                                    !isVideo(item.image)
                                "
                                :src="
                                    imageUrl(
                                        item.image,
                                    )
                                "
                                :alt="
                                    `${race.name} - Foto ${index + 1}`
                                "
                                loading="lazy"
                            />

                            <!-- VIDEO -->
                            <video
                                v-else
                                :src="
                                    mediaUrl(
                                        item.image,
                                    )
                                "
                                muted
                                playsinline
                                preload="metadata"
                            />

                            <span
                                class="gallery-item-overlay"
                            >
                                <Camera :size="20" />
                            </span>

                        </button>

                    </div>

                    <div
                        v-else
                        class="gallery-empty"
                    >
                        <div class="gallery-empty-icon">
                            <Camera :size="27" />
                        </div>

                        <h3>
                            Aún no hay fotografías
                        </h3>

                        <p>
                            Las fotografías y videos de esta
                            carrera estarán disponibles
                            próximamente.
                        </p>

                        <Link
                            :href="`/carreras/${race.slug}`"
                            class="gallery-empty-button"
                        >
                            Volver a la carrera
                        </Link>
                    </div>

                </div>
            </section>
        </main>

        <LandingFooter />

        <!-- LIGHTBOX -->
        <Teleport to="body">

            <div
                v-if="selectedImage"
                class="gallery-lightbox"
                @click.self="closeImage"
            >

                <!-- CERRAR -->
                <button
                    type="button"
                    class="lightbox-close"
                    aria-label="Cerrar"
                    @click="closeImage"
                >
                    <X :size="23" />
                </button>

                <!-- ANTERIOR -->
                <button
                    v-if="images.length > 1"
                    type="button"
                    class="lightbox-arrow lightbox-arrow-left"
                    aria-label="Archivo anterior"
                    @click="previousImage"
                >
                    <ChevronLeft :size="28" />
                </button>

                <!-- CONTENIDO -->
                <div class="lightbox-content">

                    <!-- IMAGEN -->
                    <img
                        v-if="
                            !isVideo(
                                selectedImage.image,
                            )
                        "
                        :src="
                            imageUrl(
                                selectedImage.image,
                            )
                        "
                        :alt="
                            `${race.name} - Fotografía`
                        "
                    />

                    <!-- VIDEO -->
                    <video
                        v-else
                        :src="
                            mediaUrl(
                                selectedImage.image,
                            )
                        "
                        controls
                        autoplay
                        playsinline
                        preload="metadata"
                    />

                    <div class="lightbox-counter">
                        {{ (selectedIndex ?? 0) + 1 }}
                        /
                        {{ images.length }}
                    </div>

                </div>

                <!-- SIGUIENTE -->
                <button
                    v-if="images.length > 1"
                    type="button"
                    class="lightbox-arrow lightbox-arrow-right"
                    aria-label="Siguiente archivo"
                    @click="nextImage"
                >
                    <ChevronRight :size="28" />
                </button>

            </div>

        </Teleport>
    </div>
</template>

<style scoped>
/* =========================================================
   BASE
   ========================================================= */

.gallery-page {
    --blue-deep: #12558c;
    --blue: #1769a8;
    --blue-light: #249edb;
    --pink: #d94c9a;
    --pink-light: #f48bb0;

    --text: #172b4d;
    --muted: #64748b;
    --border: #e3ebf2;
    --bg: #f8fafc;

    width: 100%;
    min-height: 100vh;

    overflow-x: hidden;

    background: var(--bg);
    color: var(--text);
}

.gallery-container {
    width: min(1180px, calc(100% - 40px));
    margin: 0 auto;
}


/* =========================================================
   HERO
   ========================================================= */

.gallery-hero {
    position: relative;

    min-height: 350px;

    display: flex;
    align-items: center;

    overflow: hidden;

    background:
        linear-gradient(
            90deg,
            #12558c 0%,
            #1769a8 34%,
            #249edb 60%,
            #d94c9a 100%
        );
}

.gallery-hero-background {
    position: absolute;
    inset: 0;

    pointer-events: none;

    background:
        radial-gradient(
            circle at 80% 20%,
            rgba(255, 255, 255, 0.16),
            transparent 28%
        ),
        radial-gradient(
            circle at 95% 90%,
            rgba(244, 139, 176, 0.22),
            transparent 32%
        );
}

.gallery-hero-background::after {
    content: '';

    position: absolute;

    width: 400px;
    height: 400px;

    right: -130px;
    bottom: -260px;

    border-radius: 50%;

    background: rgba(255, 255, 255, 0.09);

    filter: blur(4px);
}

.gallery-hero-content {
    position: relative;
    z-index: 1;

    padding: 55px 0;
}

.gallery-back {
    display: inline-flex;
    align-items: center;

    gap: 7px;

    margin-bottom: 27px;

    color: rgba(255, 255, 255, 0.86);

    font-size: 13px;
    font-weight: 700;

    text-decoration: none;

    transition:
        color 0.2s ease,
        transform 0.2s ease;
}

.gallery-back:hover {
    color: #fff;
    transform: translateX(-3px);
}

.gallery-hero-icon {
    width: 48px;
    height: 48px;

    display: flex;
    align-items: center;
    justify-content: center;

    margin-bottom: 15px;

    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 14px;

    background: rgba(255, 255, 255, 0.12);

    backdrop-filter: blur(10px);

    color: #fff;
}

.gallery-kicker {
    display: block;

    margin-bottom: 7px;

    color: rgba(255, 255, 255, 0.72);

    font-size: 11px;
    font-weight: 800;

    letter-spacing: 1px;
    text-transform: uppercase;
}

.gallery-hero h1 {
    max-width: 850px;

    margin: 0;

    color: #fff;

    font-size: clamp(38px, 5vw, 62px);
    line-height: 1;
    letter-spacing: -2.5px;

    font-weight: 800;
}

.gallery-hero p {
    margin: 13px 0 0;

    color: rgba(255, 255, 255, 0.86);

    font-size: 15px;
}


/* =========================================================
   GALLERY SECTION
   ========================================================= */

.gallery-section {
    padding: 55px 0 70px;
}

.gallery-heading {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;

    gap: 20px;

    margin-bottom: 25px;
}

.gallery-heading > div:first-child > span {
    display: block;

    margin-bottom: 5px;

    color: var(--blue-light);

    font-size: 10px;
    font-weight: 800;

    letter-spacing: 0.9px;
    text-transform: uppercase;
}

.gallery-heading h2 {
    margin: 0;

    color: var(--text);

    font-size: 30px;
    line-height: 1.1;
    letter-spacing: -1px;

    font-weight: 800;
}

.gallery-count {
    padding: 7px 11px;

    border: 1px solid #dce8f0;
    border-radius: 999px;

    background: #fff;

    color: var(--muted);

    font-size: 11px;
    font-weight: 700;
}


/* =========================================================
   GRID
   ========================================================= */

.gallery-grid {
    display: grid;

    grid-template-columns:
        repeat(4, minmax(0, 1fr));

    gap: 12px;
}

.gallery-item {
    position: relative;

    display: block;

    width: 100%;
    aspect-ratio: 1 / 0.78;

    padding: 0;

    overflow: hidden;

    border: 1px solid #dce6ee;
    border-radius: 15px;

    background: #e8eef3;

    cursor: pointer;

    box-shadow:
        0 8px 25px rgba(23, 43, 77, 0.055);

    transition:
        transform 0.25s ease,
        box-shadow 0.25s ease;
}

.gallery-item:hover {
    transform: translateY(-3px);

    box-shadow:
        0 15px 35px rgba(23, 43, 77, 0.11);
}

.gallery-item img {
    width: 100%;
    height: 100%;

    display: block;

    object-fit: cover;

    transition:
        transform 0.45s ease,
        filter 0.45s ease;
}

.gallery-item:hover img {
    transform: scale(1.06);

    filter: saturate(1.07);
}

.gallery-item-overlay {
    position: absolute;
    inset: 0;

    display: flex;
    align-items: center;
    justify-content: center;

    background:
        linear-gradient(
            135deg,
            rgba(18, 85, 140, 0.2),
            rgba(217, 76, 154, 0.2)
        );

    color: #fff;

    opacity: 0;

    transition: opacity 0.25s ease;
}

.gallery-item-overlay svg {
    padding: 10px;

    width: 45px;
    height: 45px;

    border-radius: 50%;

    background: rgba(255, 255, 255, 0.18);

    backdrop-filter: blur(8px);
}

.gallery-item:hover .gallery-item-overlay {
    opacity: 1;
}


/* =========================================================
   EMPTY
   ========================================================= */

.gallery-empty {
    display: flex;
    align-items: center;
    flex-direction: column;

    padding: 70px 20px;

    border: 1px dashed #cfdae4;
    border-radius: 18px;

    background: #fff;

    text-align: center;
}

.gallery-empty-icon {
    width: 55px;
    height: 55px;

    display: flex;
    align-items: center;
    justify-content: center;

    margin-bottom: 14px;

    border-radius: 15px;

    background:
        linear-gradient(
            135deg,
            #eaf6fc,
            #f8eaf3
        );

    color: var(--blue);
}

.gallery-empty h3 {
    margin: 0;

    color: var(--text);

    font-size: 20px;
    font-weight: 800;
}

.gallery-empty p {
    max-width: 430px;

    margin: 7px 0 18px;

    color: var(--muted);

    font-size: 13px;
    line-height: 1.6;
}

.gallery-empty-button {
    display: inline-flex;
    align-items: center;
    justify-content: center;

    padding: 11px 17px;

    border-radius: 10px;

    background: linear-gradient(
        135deg,
        var(--blue-light),
        var(--blue)
    );

    color: #fff;

    font-size: 13px;
    font-weight: 800;

    text-decoration: none;
}


/* =========================================================
   LIGHTBOX
   ========================================================= */

.gallery-lightbox {
    position: fixed;
    inset: 0;
    z-index: 9999;

    display: flex;
    align-items: center;
    justify-content: center;

    padding: 35px;

    background: rgba(8, 25, 42, 0.92);

    backdrop-filter: blur(8px);
}

.lightbox-content {
    position: relative;

    max-width: min(1100px, 90vw);
    max-height: 88vh;

    display: flex;
    align-items: center;
    justify-content: center;
}

.lightbox-content img {
    display: block;

    max-width: 100%;
    max-height: 82vh;

    object-fit: contain;

    border-radius: 8px;

    box-shadow:
        0 25px 80px rgba(0, 0, 0, 0.35);
}

.lightbox-counter {
    position: absolute;

    left: 50%;
    bottom: -32px;

    transform: translateX(-50%);

    padding: 5px 10px;

    border-radius: 999px;

    background: rgba(255, 255, 255, 0.1);

    color: rgba(255, 255, 255, 0.8);

    font-size: 11px;
    font-weight: 700;
}

.lightbox-close,
.lightbox-arrow {
    position: absolute;

    z-index: 2;

    display: flex;
    align-items: center;
    justify-content: center;

    width: 45px;
    height: 45px;

    padding: 0;

    border: 1px solid rgba(255, 255, 255, 0.15);
    border-radius: 50%;

    background: rgba(255, 255, 255, 0.1);

    color: #fff;

    cursor: pointer;

    backdrop-filter: blur(8px);

    transition:
        background 0.2s ease,
        transform 0.2s ease;
}

.lightbox-close:hover,
.lightbox-arrow:hover {
    background: rgba(255, 255, 255, 0.2);

    transform: scale(1.05);
}

.lightbox-close {
    top: 22px;
    right: 22px;
}

.lightbox-arrow-left {
    left: 24px;
    top: 50%;

    transform: translateY(-50%);
}

.lightbox-arrow-right {
    right: 24px;
    top: 50%;

    transform: translateY(-50%);
}

.lightbox-arrow-left:hover,
.lightbox-arrow-right:hover {
    transform: translateY(-50%) scale(1.05);
}


/* =========================================================
   RESPONSIVE
   ========================================================= */

@media (max-width: 1000px) {
    .gallery-grid {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }
}

@media (max-width: 750px) {
    .gallery-hero {
        min-height: 320px;
    }

    .gallery-hero-content {
        padding: 45px 0;
    }

    .gallery-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .gallery-section {
        padding: 42px 0 55px;
    }
}

@media (max-width: 520px) {
    .gallery-container {
        width: calc(100% - 28px);
    }

    .gallery-hero {
        min-height: 300px;
    }

    .gallery-hero-content {
        padding: 38px 0;
    }

    .gallery-back {
        margin-bottom: 20px;
    }

    .gallery-hero-icon {
        width: 43px;
        height: 43px;

        margin-bottom: 12px;
    }

    .gallery-hero h1 {
        font-size: 38px;
        letter-spacing: -1.5px;
    }

    .gallery-heading {
        align-items: flex-start;
        flex-direction: column;

        gap: 9px;

        margin-bottom: 19px;
    }

    .gallery-heading h2 {
        font-size: 26px;
    }

    .gallery-grid {
        gap: 7px;
    }

    .gallery-item {
        border-radius: 11px;
    }

    .gallery-lightbox {
        padding: 15px;
    }

    .lightbox-close {
        top: 15px;
        right: 15px;
    }

    .lightbox-arrow {
        width: 40px;
        height: 40px;
    }

    .lightbox-arrow-left {
        left: 10px;
    }

    .lightbox-arrow-right {
        right: 10px;
    }
}
</style>