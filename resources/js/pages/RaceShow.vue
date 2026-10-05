<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import {
    CalendarDays,
    Camera,
    Check,
    ChevronLeft,
    ChevronRight,
    Clock3,
    ExternalLink,
    MapPin,
    Medal,
    Tag,
    Ticket,
    Trophy,
    Users,
    X,
} from 'lucide-vue-next';
import {
    computed,
    onBeforeUnmount,
    ref,
} from 'vue';

import LandingFooter from '@/components/landing/LandingFooter.vue';
import LandingHeader from '@/components/landing/LandingHeader.vue';

const { race } = defineProps<{
    race: any;
}>();

/*
|--------------------------------------------------------------------------
| FECHAS
|--------------------------------------------------------------------------
*/

const safeDate = (
    value: string | null | undefined,
): Date | null => {
    if (!value) {
        return null;
    }

    const date = new Date(value);

    if (Number.isNaN(date.getTime())) {
        return null;
    }

    return date;
};

const formatDate = (
    value: string | null | undefined,
) => {
    const date = safeDate(value);

    if (!date) {
        return 'Por confirmar';
    }

    return new Intl.DateTimeFormat('es-MX', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    }).format(date);
};

const formatDateTime = (
    value: string | null | undefined,
) => {
    const date = safeDate(value);

    if (!date) {
        return 'Por confirmar';
    }

    return new Intl.DateTimeFormat('es-MX', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
        hour: 'numeric',
        minute: '2-digit',
        hour12: true,
    }).format(date);
};

const formatTime = (
    value: string | null | undefined,
) => {
    if (!value) {
        return 'Por confirmar';
    }

    const normalized = String(value).trim();

    if (!normalized) {
        return 'Por confirmar';
    }

    const match = normalized.match(
        /^(\d{1,2}):(\d{2})(?::(\d{2}))?/,
    );

    if (!match) {
        return normalized;
    }

    const hours = Number(match[1]);
    const minutes = Number(match[2]);

    if (
        Number.isNaN(hours) ||
        Number.isNaN(minutes) ||
        hours < 0 ||
        hours > 23 ||
        minutes < 0 ||
        minutes > 59
    ) {
        return normalized;
    }

    const date = new Date(
        1970,
        0,
        1,
        hours,
        minutes,
    );

    return new Intl.DateTimeFormat('es-MX', {
        hour: 'numeric',
        minute: '2-digit',
        hour12: true,
    }).format(date);
};

const formatPrice = (
    price: string | number | null | undefined,
) => {
    if (
        price === null ||
        price === undefined ||
        price === ''
    ) {
        return 'Consultar';
    }

    const numericPrice = Number(price);

    if (Number.isNaN(numericPrice)) {
        return 'Consultar';
    }

    return new Intl.NumberFormat('es-MX', {
        style: 'currency',
        currency: 'MXN',
    }).format(numericPrice);
};

/*
|--------------------------------------------------------------------------
| DISTANCIAS
|--------------------------------------------------------------------------
*/

const getDistanceTitle = (
    distance: any,
): string => {
    if (
        distance?.name &&
        String(distance.name).trim()
    ) {
        return String(distance.name).trim();
    }

    const value = Number(distance?.distance);

    if (!Number.isNaN(value)) {
        return `${value}${distance?.unit || 'KM'}`;
    }

    return 'Distancia';
};

const getPriceLabel = (
    price: any,
): string => {
    const name = String(
        price?.name || '',
    ).trim();

    if (!name) {
        return 'Inscripción';
    }

    const normalized = name
        .toLowerCase()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '');

    if (
        normalized.includes('preventa') ||
        normalized.includes('pre-venta') ||
        normalized.includes('pre venta')
    ) {
        return 'Preventa';
    }

    if (
        normalized === 'venta' ||
        normalized.includes('venta regular') ||
        normalized.includes('venta normal') ||
        normalized.includes('regular') ||
        normalized.includes('normal')
    ) {
        return 'Venta';
    }

    return name;
};

/*
|--------------------------------------------------------------------------
| ESTATUS
|--------------------------------------------------------------------------
*/

const statusLabel = (
    status: string,
) => {
    const labels: Record<string, string> = {
        published: 'Próximamente',
        registration_open: 'Inscripciones abiertas',
        registration_closed: 'Inscripciones cerradas',
        finished: 'Evento realizado',
        cancelled: 'Carrera cancelada',
        draft: 'Borrador',
    };

    return labels[status] ?? status;
};

/*
|--------------------------------------------------------------------------
| CATEGORÍAS
|--------------------------------------------------------------------------
*/

const genderLabel = (
    gender: string | null | undefined,
) => {
    if (!gender) {
        return '';
    }

    const labels: Record<string, string> = {
        male: 'Varonil',
        female: 'Femenil',
        mixed: 'Mixta',
        both: 'Varonil y femenil',
    };

    return labels[gender] ?? gender;
};

/*
|--------------------------------------------------------------------------
| FECHAS
|--------------------------------------------------------------------------
*/

const formatDateOnly = (
    value: string | null | undefined,
) => {
    if (!value) {
        return null;
    }

    const stringValue = String(value);

    if (stringValue.includes('T')) {
        return stringValue.split('T')[0];
    }

    if (stringValue.includes(' ')) {
        return stringValue.split(' ')[0];
    }

    return stringValue;
};

/*
|--------------------------------------------------------------------------
| IMÁGENES
|--------------------------------------------------------------------------
*/

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

/*
|--------------------------------------------------------------------------
| VIDEOS
|--------------------------------------------------------------------------
*/

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

/*
|--------------------------------------------------------------------------
| IMÁGENES DEL KIT
|--------------------------------------------------------------------------
*/

const getKitImages = () => {
    return race.kit_images ?? race.kitImages ?? [];
};

/*
|--------------------------------------------------------------------------
| LIGHTBOX - GALERÍA
|--------------------------------------------------------------------------
*/

const selectedGalleryIndex = ref<number | null>(null);

const galleryImages = computed(() => {
    return race.gallery ?? [];
});

const selectedGalleryImage = computed(() => {
    if (selectedGalleryIndex.value === null) {
        return null;
    }

    return (
        galleryImages.value[
            selectedGalleryIndex.value
        ] ?? null
    );
});

const openGalleryImage = (
    index: number,
) => {
    if (!galleryImages.value.length) {
        return;
    }

    selectedGalleryIndex.value = index;

    document.body.style.overflow = 'hidden';
};

const closeGalleryImage = () => {
    selectedGalleryIndex.value = null;

    if (selectedKitIndex.value === null) {
        document.body.style.overflow = '';
    }
};

const previousGalleryImage = () => {
    if (
        selectedGalleryIndex.value === null ||
        galleryImages.value.length <= 1
    ) {
        return;
    }

    selectedGalleryIndex.value =
        selectedGalleryIndex.value === 0
            ? galleryImages.value.length - 1
            : selectedGalleryIndex.value - 1;
};

const nextGalleryImage = () => {
    if (
        selectedGalleryIndex.value === null ||
        galleryImages.value.length <= 1
    ) {
        return;
    }

    selectedGalleryIndex.value =
        selectedGalleryIndex.value ===
        galleryImages.value.length - 1
            ? 0
            : selectedGalleryIndex.value + 1;
};

/*
|--------------------------------------------------------------------------
| LIGHTBOX - KIT
|--------------------------------------------------------------------------
*/

const selectedKitIndex = ref<number | null>(null);

const kitImages = computed(() => {
    return getKitImages();
});

const selectedKitImage = computed(() => {
    if (selectedKitIndex.value === null) {
        return null;
    }

    return (
        kitImages.value[
            selectedKitIndex.value
        ] ?? null
    );
});

const getKitMedia = (
    image: any,
): string => {
    return (
        image?.image ??
        image?.path ??
        image?.image_path ??
        image?.url ??
        ''
    );
};

const openKitImage = (
    index: number,
) => {
    if (!kitImages.value.length) {
        return;
    }

    selectedKitIndex.value = index;

    document.body.style.overflow = 'hidden';
};

const closeKitImage = () => {
    selectedKitIndex.value = null;

    if (selectedGalleryIndex.value === null) {
        document.body.style.overflow = '';
    }
};

const previousKitImage = () => {
    if (
        selectedKitIndex.value === null ||
        kitImages.value.length <= 1
    ) {
        return;
    }

    selectedKitIndex.value =
        selectedKitIndex.value === 0
            ? kitImages.value.length - 1
            : selectedKitIndex.value - 1;
};

const nextKitImage = () => {
    if (
        selectedKitIndex.value === null ||
        kitImages.value.length <= 1
    ) {
        return;
    }

    selectedKitIndex.value =
        selectedKitIndex.value ===
        kitImages.value.length - 1
            ? 0
            : selectedKitIndex.value + 1;
};

/*
|--------------------------------------------------------------------------
| TECLADO
|--------------------------------------------------------------------------
*/

const handleLightboxKeydown = (
    event: KeyboardEvent,
) => {
    /*
     * GALERÍA
     */
    if (selectedGalleryIndex.value !== null) {
        if (event.key === 'Escape') {
            closeGalleryImage();
            return;
        }

        if (event.key === 'ArrowLeft') {
            previousGalleryImage();
            return;
        }

        if (event.key === 'ArrowRight') {
            nextGalleryImage();
            return;
        }

        return;
    }

    /*
     * KIT
     */
    if (selectedKitIndex.value !== null) {
        if (event.key === 'Escape') {
            closeKitImage();
            return;
        }

        if (event.key === 'ArrowLeft') {
            previousKitImage();
            return;
        }

        if (event.key === 'ArrowRight') {
            nextKitImage();
        }
    }
};

window.addEventListener(
    'keydown',
    handleLightboxKeydown,
);

onBeforeUnmount(() => {
    document.body.style.overflow = '';

    window.removeEventListener(
        'keydown',
        handleLightboxKeydown,
    );
});
</script>

<template>
    <Head :title="race.name">
        <meta
            name="description"
            :content="
                race.description ||
                `Conoce todos los detalles de ${race.name}.`
            "
        />

        <link
            rel="preconnect"
            href="https://fonts.googleapis.com"
        />

        <link
            rel="preconnect"
            href="https://fonts.gstatic.com"
        />

        <link
            href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap"
            rel="stylesheet"
        />
    </Head>

    <div class="landing-page">
        <LandingHeader />

        <main>
            <!-- HERO -->
            <section
                class="race-hero"
                :style="
                    race.banner
                        ? {
                              backgroundImage: `linear-gradient(90deg, rgba(23, 105, 168, 0.94), rgba(36, 158, 219, 0.55)), url('${imageUrl(race.banner)}')`,
                          }
                        : {}
                "
            >
                <div class="race-hero-overlay">
                    <div class="race-container">
                        <div class="race-hero-content">
                            <span class="race-status">
                                {{ statusLabel(race.status) }}
                            </span>

                            <h1>
                                {{ race.name }}
                            </h1>
                        </div>
                    </div>
                </div>
            </section>

            <!-- RESUMEN -->
            <section class="race-summary">
                <div class="race-container">
                    <div class="race-summary-grid">
                        <div class="race-summary-card">
                            <CalendarDays :size="24" />

                            <div>
                                <span>Fecha</span>

                                <strong>
                                    {{
                                        formatDate(
                                            race.event_date,
                                        )
                                    }}
                                </strong>
                            </div>
                        </div>

                        <div class="race-summary-card">
                            <MapPin :size="24" />

                            <div>
                                <span>Lugar</span>

                                <strong>
                                    {{
                                        race.location ||
                                        race.city ||
                                        'Por confirmar'
                                    }}
                                </strong>
                            </div>
                        </div>

                        <div class="race-summary-card">
                            <Clock3 :size="24" />

                            <div>
                                <span>Salida</span>

                                <strong>
                                    {{
                                        formatTime(
                                            race.start_time,
                                        )
                                    }}
                                </strong>
                            </div>
                        </div>

                        <div class="race-summary-card">
                            <Medal :size="24" />

                            <div>
                                <span>Distancias</span>

                                <strong>
                                    {{
                                        race.distances?.length ||
                                        0
                                    }}
                                    opciones
                                </strong>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- DETALLES GENERALES -->
            <section class="race-section">
                <div class="race-container">
                    <div class="race-section-heading">
                        <span class="race-section-kicker">
                            Conoce el evento
                        </span>

                        <h2>
                            Todo sobre la carrera
                        </h2>
                    </div>

                    <div class="race-details-grid">
                        <div class="race-description-card">
                            <h3>Descripción</h3>

                            <div
                                v-if="race.description"
                                class="race-description"
                            >
                                {{ race.description }}
                            </div>

                            <p
                                v-else
                                class="race-empty"
                            >
                                Próximamente encontrarás aquí toda la
                                información de esta carrera.
                            </p>
                        </div>

                        <div class="race-location-card">
                            <div class="race-location-icon">
                                <MapPin :size="26" />
                            </div>

                            <div>
                                <h3>Ubicación</h3>

                                <p v-if="race.location">
                                    {{ race.location }}
                                </p>

                                <p v-if="race.address">
                                    {{ race.address }}
                                </p>

                                <p
                                    v-if="
                                        race.city ||
                                        race.state ||
                                        race.country
                                    "
                                >
                                    {{
                                        [
                                            race.city,
                                            race.state,
                                            race.country,
                                        ]
                                            .filter(Boolean)
                                            .join(', ')
                                    }}
                                </p>

                                <p
                                    v-if="
                                        !race.location &&
                                        !race.address &&
                                        !race.city
                                    "
                                    class="race-empty"
                                >
                                    Ubicación por confirmar.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- =====================================================
                 GALERÍA DEL KIT
                 ===================================================== -->
            <section
                v-if="getKitImages().length"
                class="race-section"
            >
                <div class="race-container">
                    <div class="race-section-heading">
                        <span class="race-section-kicker">
                            Conoce tu kit
                        </span>

                        <h2>
                            Kit de la carrera
                        </h2>

                        <p>
                            Conoce los artículos que forman parte del kit
                            de esta carrera.
                        </p>
                    </div>

                    <div class="race-gallery-grid">
                        <button
                            v-for="(image, index) in getKitImages()"
                            :key="image.id ?? index"
                            type="button"
                            class="race-gallery-item race-gallery-button"
                            :aria-label="`Abrir imagen del kit ${index + 1}`"
                            @click="openKitImage(index)"
                        >
                            <video
                                v-if="
                                    isVideo(
                                        getKitMedia(image),
                                    )
                                "
                                :src="
                                    mediaUrl(
                                        getKitMedia(image),
                                    )
                                "
                                muted
                                playsinline
                                preload="metadata"
                            />

                            <img
                                v-else
                                :src="
                                    imageUrl(
                                        getKitMedia(image),
                                    )
                                "
                                :alt="`${race.name} - Kit ${index + 1}`"
                                loading="lazy"
                            />

                            <span class="race-gallery-overlay">
                                <Camera :size="21" />
                            </span>
                        </button>
                    </div>
                </div>
            </section>

            <!-- DISTANCIAS -->
            <section
                v-if="race.distances?.length"
                class="race-section race-section-light"
            >
                <div class="race-container">
                    <div class="race-section-heading">
                        <span class="race-section-kicker">
                            Elige tu reto
                        </span>

                        <h2>Distancias</h2>

                        <p>
                            Selecciona la distancia que mejor se adapte
                            a ti.
                        </p>
                    </div>

                    <div class="distance-grid">
                        <article
                            v-for="distance in race.distances"
                            :key="distance.id"
                            class="distance-card"
                        >
                            <div class="distance-card-top">
                                <div class="distance-icon">
                                    <Trophy :size="25" />
                                </div>

                                <span
                                    v-if="distance.capacity"
                                    class="distance-capacity"
                                >
                                    <Users :size="15" />

                                    {{ distance.capacity }}
                                    lugares
                                </span>
                            </div>

                            <h3>
                                {{ getDistanceTitle(distance) }}
                            </h3>

                            <div
                                v-if="distance.start_time"
                                class="distance-start"
                            >
                                <Clock3 :size="16" />

                                Salida:
                                {{
                                    formatTime(
                                        distance.start_time,
                                    )
                                }}
                            </div>

                            <div
                                v-if="distance.prices?.length"
                                class="distance-prices"
                            >
                                <h4>Inscripciones</h4>

                                <div
                                    v-for="price in distance.prices"
                                    :key="price.id"
                                    class="price-row"
                                >
                                    <div>
                                        <strong class="price-name">
                                            {{
                                                getPriceLabel(
                                                    price,
                                                )
                                            }}
                                        </strong>

                                        <span
                                            v-if="
                                                price.starts_at ||
                                                price.ends_at
                                            "
                                        >
                                            <template
                                                v-if="
                                                    price.starts_at
                                                "
                                            >
                                                Desde
                                                {{
                                                    formatDateTime(
                                                        price.starts_at,
                                                    )
                                                }}
                                            </template>

                                            <template
                                                v-if="
                                                    price.starts_at &&
                                                    price.ends_at
                                                "
                                            >
                                                -
                                            </template>

                                            <template
                                                v-if="
                                                    price.ends_at
                                                "
                                            >
                                                Hasta
                                                {{
                                                    formatDateTime(
                                                        price.ends_at,
                                                    )
                                                }}
                                            </template>
                                        </span>
                                    </div>

                                    <strong class="price-value">
                                        {{
                                            formatPrice(
                                                price.price,
                                            )
                                        }}
                                    </strong>
                                </div>
                            </div>

                            <div
                                v-if="
                                    distance.inclusions?.length
                                "
                                class="distance-inclusions"
                            >
                                <h4>Incluye</h4>

                                <ul>
                                    <li
                                        v-for="
                                            inclusion in distance.inclusions
                                        "
                                        :key="inclusion.id"
                                    >
                                        <Check :size="16" />

                                        <span>
                                            {{ inclusion.name }}

                                            <small
                                                v-if="
                                                    inclusion.description
                                                "
                                            >
                                                {{
                                                    inclusion.description
                                                }}
                                            </small>
                                        </span>
                                    </li>
                                </ul>
                            </div>

                            <div
                                v-if="
                                    distance.categories?.length
                                "
                                class="distance-categories"
                            >
                                <h4>Categorías</h4>

                                <div class="category-list">
                                    <span
                                        v-for="
                                            category in distance.categories
                                        "
                                        :key="category.id"
                                        class="category-tag"
                                    >
                                        <Tag :size="14" />

                                        {{ category.name }}

                                        <small
                                            v-if="
                                                category.min_age !==
                                                    null ||
                                                category.max_age !==
                                                    null ||
                                                category.gender
                                            "
                                        >
                                            <template
                                                v-if="
                                                    category.min_age !==
                                                    null
                                                "
                                            >
                                                {{ category.min_age }}
                                            </template>

                                            <template
                                                v-if="
                                                    category.min_age !==
                                                        null &&
                                                    category.max_age !==
                                                        null
                                                "
                                            >
                                                -
                                            </template>

                                            <template
                                                v-if="
                                                    category.max_age !==
                                                    null
                                                "
                                            >
                                                {{ category.max_age }}
                                            </template>

                                            <template
                                                v-if="category.gender"
                                            >
                                                ·
                                                {{
                                                    genderLabel(
                                                        category.gender,
                                                    )
                                                }}
                                            </template>
                                        </small>
                                    </span>
                                </div>
                            </div>

                            <a
                                v-if="
                                    race.status ===
                                    'registration_open'
                                "
                                href="#"
                                class="distance-button"
                            >
                                <Ticket :size="18" />

                                Inscribirme
                            </a>
                        </article>
                    </div>
                </div>
            </section>

            <!-- INSCRIPCIONES -->
            <section
                v-if="
                    race.registration_opens_at ||
                    race.registration_closes_at
                "
                class="race-section"
            >
                <div class="race-container">
                    <div class="registration-card">
                        <div class="registration-icon">
                            <Ticket :size="28" />
                        </div>

                        <div class="registration-content">
                            <span class="race-section-kicker">
                                Inscripciones
                            </span>

                            <h2>
                                Fechas de inscripción
                            </h2>

                            <div class="registration-dates">
                                <div
                                    v-if="
                                        race.registration_opens_at
                                    "
                                >
                                    <span>Apertura</span>

                                    <strong>
                                        {{
                                            formatDate(
                                                formatDateOnly(
                                                    race.registration_opens_at,
                                                ),
                                            )
                                        }}
                                    </strong>
                                </div>

                                <div
                                    v-if="
                                        race.registration_closes_at
                                    "
                                >
                                    <span>Cierre</span>

                                    <strong>
                                        {{
                                            formatDate(
                                                formatDateOnly(
                                                    race.registration_closes_at,
                                                ),
                                            )
                                        }}
                                    </strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- =====================================================
                 GALERÍA
                 ===================================================== -->
            <section
                v-if="race.gallery?.length"
                class="race-section race-section-light"
            >
                <div class="race-container">
                    <div class="race-section-heading">
                        <span class="race-section-kicker">
                            Vive la experiencia
                        </span>

                        <h2>Galería</h2>

                        <p>
                            Revive algunos momentos de nuestras
                            carreras.
                        </p>
                    </div>

                    <div class="race-gallery-grid">
                        <button
                            v-for="(image, index) in race.gallery"
                            :key="image.id ?? index"
                            type="button"
                            class="race-gallery-item race-gallery-button"
                            :aria-label="`Abrir imagen ${index + 1}`"
                            @click="
                                openGalleryImage(index)
                            "
                        >
                            <video
                                v-if="
                                    isVideo(
                                        image.image ??
                                        image.url,
                                    )
                                "
                                :src="
                                    mediaUrl(
                                        image.image ??
                                        image.url,
                                    )
                                "
                                muted
                                playsinline
                                preload="metadata"
                            />

                            <img
                                v-else
                                :src="
                                    imageUrl(
                                        image.image ??
                                        image.url,
                                    )
                                "
                                :alt="`${race.name} - Foto ${index + 1}`"
                                loading="lazy"
                            />

                            <span class="race-gallery-overlay">
                                <Camera :size="21" />
                            </span>
                        </button>
                    </div>
                </div>
            </section>

            <!-- RESULTADOS -->
            <section
                v-if="race.results_url"
                class="race-section"
            >
                <div class="race-container">
                    <div class="results-card">
                        <div class="results-icon">
                            <Trophy :size="30" />
                        </div>

                        <div class="results-content">
                            <span class="race-section-kicker">
                                Resultados
                            </span>

                            <h2>
                                Consulta los resultados
                            </h2>

                            <p>
                                Los resultados de esta carrera están
                                disponibles en un sitio externo.
                            </p>
                        </div>

                        <a
                            :href="race.results_url"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="results-button"
                        >
                            Ver resultados

                            <ExternalLink :size="18" />
                        </a>
                    </div>
                </div>
            </section>

            <!-- TÉRMINOS Y NOTAS -->
            <section
                v-if="
                    race.terms_and_conditions ||
                    race.notes
                "
                class="race-section race-section-light"
            >
                <div class="race-container">
                    <div class="race-extra-grid">
                        <div
                            v-if="race.terms_and_conditions"
                            class="race-extra-card"
                        >
                            <h3>
                                Términos y condiciones
                            </h3>

                            <div>
                                {{
                                    race.terms_and_conditions
                                }}
                            </div>
                        </div>

                        <div
                            v-if="race.notes"
                            class="race-extra-card"
                        >
                            <h3>
                                Información adicional
                            </h3>

                            <div>
                                {{ race.notes }}
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- CTA -->
            <section class="race-cta">
                <div class="race-container">
                    <div class="race-cta-content">
                        <div>
                            <span>
                                {{ race.name }}
                            </span>

                            <h2>
                                ¿Listo para correr?
                            </h2>

                            <p>
                                Forma parte de esta experiencia
                                Sonríe Corriendo.
                            </p>
                        </div>

                        <a
                            v-if="
                                race.status ===
                                'registration_open'
                            "
                            href="#"
                            class="race-cta-button"
                        >
                            <Ticket :size="19" />

                            Inscribirme ahora
                        </a>

                        <span
                            v-else
                            class="race-cta-status"
                        >
                            {{ statusLabel(race.status) }}
                        </span>
                    </div>
                </div>
            </section>
        </main>

        <LandingFooter />

        <!-- =====================================================
             LIGHTBOX GALERÍA
             ===================================================== -->

        <Teleport to="body">
            <div
                v-if="selectedGalleryImage"
                class="race-gallery-lightbox"
                @click.self="closeGalleryImage"
            >
                <!-- CERRAR -->
                <button
                    type="button"
                    class="race-lightbox-close"
                    aria-label="Cerrar galería"
                    @click="closeGalleryImage"
                >
                    <X :size="24" />
                </button>

                <!-- ANTERIOR -->
                <button
                    v-if="galleryImages.length > 1"
                    type="button"
                    class="race-lightbox-arrow race-lightbox-arrow-left"
                    aria-label="Imagen anterior"
                    @click="previousGalleryImage"
                >
                    <ChevronLeft :size="30" />
                </button>

                <!-- CONTENIDO -->
                <div class="race-lightbox-content">
                    <img
                        v-if="
                            !isVideo(
                                selectedGalleryImage.image ??
                                selectedGalleryImage.url,
                            )
                        "
                        :src="
                            imageUrl(
                                selectedGalleryImage.image ??
                                selectedGalleryImage.url,
                            )
                        "
                        :alt="`${race.name} - Fotografía`"
                    />

                    <video
                        v-else
                        :src="
                            mediaUrl(
                                selectedGalleryImage.image ??
                                selectedGalleryImage.url,
                            )
                        "
                        controls
                        autoplay
                        playsinline
                        preload="metadata"
                    />

                    <div class="race-lightbox-counter">
                        {{
                            (selectedGalleryIndex ?? 0) + 1
                        }}
                        /
                        {{ galleryImages.length }}
                    </div>
                </div>

                <!-- SIGUIENTE -->
                <button
                    v-if="galleryImages.length > 1"
                    type="button"
                    class="race-lightbox-arrow race-lightbox-arrow-right"
                    aria-label="Siguiente imagen"
                    @click="nextGalleryImage"
                >
                    <ChevronRight :size="30" />
                </button>
            </div>
        </Teleport>

        <!-- =====================================================
             LIGHTBOX KIT
             ===================================================== -->

        <Teleport to="body">
            <div
                v-if="selectedKitImage"
                class="race-gallery-lightbox"
                @click.self="closeKitImage"
            >
                <!-- CERRAR -->
                <button
                    type="button"
                    class="race-lightbox-close"
                    aria-label="Cerrar kit"
                    @click="closeKitImage"
                >
                    <X :size="24" />
                </button>

                <!-- ANTERIOR -->
                <button
                    v-if="kitImages.length > 1"
                    type="button"
                    class="race-lightbox-arrow race-lightbox-arrow-left"
                    aria-label="Kit anterior"
                    @click="previousKitImage"
                >
                    <ChevronLeft :size="30" />
                </button>

                <!-- CONTENIDO -->
                <div class="race-lightbox-content">
                    <img
                        v-if="
                            !isVideo(
                                getKitMedia(
                                    selectedKitImage,
                                ),
                            )
                        "
                        :src="
                            imageUrl(
                                getKitMedia(
                                    selectedKitImage,
                                ),
                            )
                        "
                        :alt="`${race.name} - Kit`"
                    />

                    <video
                        v-else
                        :src="
                            mediaUrl(
                                getKitMedia(
                                    selectedKitImage,
                                ),
                            )
                        "
                        controls
                        autoplay
                        playsinline
                        preload="metadata"
                    />

                    <div class="race-lightbox-counter">
                        {{
                            (selectedKitIndex ?? 0) + 1
                        }}
                        /
                        {{ kitImages.length }}
                    </div>
                </div>

                <!-- SIGUIENTE -->
                <button
                    v-if="kitImages.length > 1"
                    type="button"
                    class="race-lightbox-arrow race-lightbox-arrow-right"
                    aria-label="Siguiente elemento del kit"
                    @click="nextKitImage"
                >
                    <ChevronRight :size="30" />
                </button>
            </div>
        </Teleport>
    </div>
</template>

<style scoped>
/*
|--------------------------------------------------------------------------
| BOTONES DE GALERÍA
|--------------------------------------------------------------------------
*/

.race-gallery-button {
    position: relative;

    display: block;

    width: 100%;

    padding: 0;

    overflow: hidden;

    border: 0;

    cursor: pointer;

    font: inherit;
}

.race-gallery-button img,
.race-gallery-button video {
    width: 100%;
    height: 100%;

    display: block;

    object-fit: cover;

    transition:
        transform 0.45s ease,
        filter 0.45s ease;
}

.race-gallery-button:hover img,
.race-gallery-button:hover video {
    transform: scale(1.06);

    filter: saturate(1.07);
}

.race-gallery-overlay {
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

    pointer-events: none;
}

.race-gallery-button:hover .race-gallery-overlay,
.race-gallery-button:focus-visible .race-gallery-overlay {
    opacity: 1;
}

.race-gallery-overlay svg {
    width: 45px;
    height: 45px;

    padding: 10px;

    border-radius: 50%;

    background: rgba(255, 255, 255, 0.18);

    backdrop-filter: blur(8px);
}


/*
|--------------------------------------------------------------------------
| LIGHTBOX
|--------------------------------------------------------------------------
*/

.race-gallery-lightbox {
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

.race-lightbox-content {
    position: relative;

    max-width: min(1100px, 90vw);
    max-height: 88vh;

    display: flex;
    align-items: center;
    justify-content: center;
}

.race-lightbox-content img,
.race-lightbox-content video {
    display: block;

    max-width: 100%;
    max-height: 82vh;

    object-fit: contain;

    border-radius: 8px;

    box-shadow:
        0 25px 80px rgba(0, 0, 0, 0.35);
}

.race-lightbox-counter {
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

    white-space: nowrap;
}

.race-lightbox-close,
.race-lightbox-arrow {
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

.race-lightbox-close:hover,
.race-lightbox-arrow:hover {
    background: rgba(255, 255, 255, 0.2);

    transform: scale(1.05);
}

.race-lightbox-close {
    top: 22px;
    right: 22px;
}

.race-lightbox-arrow-left {
    left: 24px;
    top: 50%;

    transform: translateY(-50%);
}

.race-lightbox-arrow-right {
    right: 24px;
    top: 50%;

    transform: translateY(-50%);
}

.race-lightbox-arrow-left:hover,
.race-lightbox-arrow-right:hover {
    transform: translateY(-50%) scale(1.05);
}


/*
|--------------------------------------------------------------------------
| RESPONSIVE LIGHTBOX
|--------------------------------------------------------------------------
*/

@media (max-width: 520px) {
    .race-gallery-lightbox {
        padding: 15px;
    }

    .race-lightbox-close {
        top: 15px;
        right: 15px;
    }

    .race-lightbox-arrow {
        width: 40px;
        height: 40px;
    }

    .race-lightbox-arrow-left {
        left: 10px;
    }

    .race-lightbox-arrow-right {
        right: 10px;
    }

    .race-lightbox-content img,
    .race-lightbox-content video {
        max-width: 94vw;
        max-height: 78vh;
    }
}

/* =========================================================
   BASE
   ========================================================= */

.landing-page {
    width: 100%;
    min-height: 100vh;
    overflow-x: hidden;

    background: #f8fafc;
    color: #172b4d;
}

.race-container {
    width: min(1180px, calc(100% - 40px));
    margin: 0 auto;
}


/* =========================================================
   HERO
   ========================================================= */

.race-hero {
    position: relative;

    display: flex;
    align-items: stretch;

    min-height: 500px;

    overflow: hidden;

    background:
        linear-gradient(
            90deg,
            #12558cfa 0%,
            #1769a8e8 30%,
            #249edba3 54%,
            #d94c9a7a 78%,
            #f48bb052 100%
        );

    background-size: cover;
    background-position: center;
}

.race-hero::before {
    content: '';

    position: absolute;
    inset: 0;

    pointer-events: none;

    background:
        radial-gradient(
            circle at 78% 20%,
            rgba(255, 255, 255, 0.16),
            transparent 28%
        ),
        radial-gradient(
            circle at 92% 80%,
            rgba(244, 139, 176, 0.18),
            transparent 30%
        );
}

.race-hero::after {
    content: '';

    position: absolute;

    width: 480px;
    height: 480px;

    right: -180px;
    bottom: -270px;

    border-radius: 50%;

    background: rgba(244, 139, 176, 0.13);

    filter: blur(10px);

    pointer-events: none;
}

.race-hero-overlay {
    position: relative;
    z-index: 1;

    width: 100%;

    display: flex;
    align-items: center;

    background:
        linear-gradient(
            90deg,
            rgba(18, 85, 140, 0.88) 0%,
            rgba(23, 105, 168, 0.66) 42%,
            rgba(36, 158, 219, 0.28) 68%,
            rgba(217, 76, 154, 0.17) 100%
        );
}

.race-hero-content {
    max-width: 880px;

    padding: 78px 0 88px;
}

.race-status {
    display: inline-flex;
    align-items: center;
    gap: 7px;

    padding: 8px 14px;
    margin-bottom: 20px;

    border: 1px solid rgba(255, 255, 255, 0.28);
    border-radius: 999px;

    background: rgba(255, 255, 255, 0.12);

    box-shadow:
        0 8px 25px rgba(7, 35, 65, 0.1),
        inset 0 1px 0 rgba(255, 255, 255, 0.1);

    backdrop-filter: blur(12px);

    color: #fff;

    font-size: 11px;
    font-weight: 800;
    letter-spacing: 0.45px;
    text-transform: uppercase;
}

.race-hero h1 {
    max-width: 850px;

    margin: 0;

    color: #fff;

    font-size: clamp(42px, 6vw, 72px);
    line-height: 0.98;
    letter-spacing: -3px;
    font-weight: 800;

    text-wrap: balance;

    text-shadow: 0 7px 30px rgba(9, 45, 76, 0.2);
}


/* =========================================================
   SUMMARY
   ========================================================= */

.race-summary {
    position: relative;
    z-index: 5;

    margin-top: -42px;
}

.race-summary-grid {
    display: grid;

    grid-template-columns: repeat(4, 1fr);

    gap: 12px;
}

.race-summary-card {
    min-height: 94px;

    display: flex;
    align-items: center;

    gap: 13px;

    padding: 17px;

    border: 1px solid #e1ebf3;
    border-radius: 16px;

    background: rgba(255, 255, 255, 0.98);

    box-shadow:
        0 15px 38px rgba(23, 43, 77, 0.08),
        0 3px 9px rgba(23, 43, 77, 0.025);

    transition:
        transform 0.2s ease,
        box-shadow 0.2s ease;
}

.race-summary-card:hover {
    transform: translateY(-3px);

    box-shadow:
        0 20px 45px rgba(23, 43, 77, 0.11);
}

.race-summary-card svg {
    flex-shrink: 0;

    color: #249edb;
}

.race-summary-card:nth-child(4) svg {
    color: #d94c9a;
}

.race-summary-card div {
    display: flex;
    flex-direction: column;

    gap: 4px;

    min-width: 0;
}

.race-summary-card span {
    color: #8492a6;

    font-size: 10px;
    font-weight: 800;

    letter-spacing: 0.45px;
    text-transform: uppercase;
}

.race-summary-card strong {
    color: #172b4d;

    font-size: 13px;
    line-height: 1.35;
}


/* =========================================================
   SECTIONS
   ========================================================= */

.race-section {
    padding: 62px 0;
}

.race-section-light {
    background:
        linear-gradient(
            180deg,
            #f5f9fc 0%,
            #eef7fc 100%
        );
}

.race-section-heading {
    max-width: 760px;

    margin-bottom: 30px;
}

.race-section-kicker {
    display: block;

    margin-bottom: 7px;

    color: #249edb;

    font-size: 11px;
    font-weight: 800;

    letter-spacing: 1px;
    text-transform: uppercase;
}

.race-section-heading h2 {
    margin: 0;

    color: #172b4d;

    font-size: clamp(30px, 3.5vw, 44px);
    line-height: 1.05;
    letter-spacing: -1.7px;
    font-weight: 800;

    text-wrap: balance;
}

.race-section-heading p {
    max-width: 650px;

    margin: 11px 0 0;

    color: #718096;

    font-size: 15px;
    line-height: 1.6;
}


/* =========================================================
   DETAILS
   ========================================================= */

.race-details-grid {
    display: grid;

    grid-template-columns:
        minmax(0, 1.45fr)
        minmax(300px, 0.8fr);

    gap: 16px;
}

.race-description-card,
.race-location-card {
    position: relative;

    padding: 25px;

    border: 1px solid #e2ebf2;
    border-radius: 18px;

    background: #fff;

    box-shadow:
        0 10px 30px rgba(23, 43, 77, 0.045);

    transition:
        border-color 0.2s ease,
        box-shadow 0.2s ease;
}

.race-description-card:hover,
.race-location-card:hover {
    border-color: #d3e3ee;

    box-shadow:
        0 15px 35px rgba(23, 43, 77, 0.07);
}

.race-description-card h3,
.race-location-card h3 {
    margin: 0 0 12px;

    color: #172b4d;

    font-size: 19px;
    font-weight: 800;
}

.race-description {
    color: #52647a;

    font-size: 14px;
    line-height: 1.75;

    white-space: pre-line;
}

.race-empty {
    margin: 0;

    color: #94a3b8;

    line-height: 1.65;
}

.race-location-card {
    display: flex;
    align-items: flex-start;

    gap: 14px;
}

.race-location-icon {
    width: 46px;
    height: 46px;

    display: flex;
    align-items: center;
    justify-content: center;

    flex-shrink: 0;

    border: 1px solid #d5eaf6;
    border-radius: 13px;

    background:
        linear-gradient(
            135deg,
            #eaf6fc,
            #f8eaf3
        );

    color: #249edb;
}

.race-location-card p {
    margin: 3px 0 0;

    color: #64748b;

    font-size: 14px;
    line-height: 1.6;
}


/* =========================================================
   DISTANCES
   ========================================================= */

.distance-grid {
    display: grid;

    grid-template-columns:
        repeat(3, minmax(0, 1fr));

    gap: 15px;
}

.distance-card {
    display: flex;
    flex-direction: column;

    padding: 21px;

    border: 1px solid #e0eaf2;
    border-radius: 18px;

    background: #fff;

    box-shadow:
        0 10px 30px rgba(23, 43, 77, 0.045);

    transition:
        transform 0.22s ease,
        box-shadow 0.22s ease,
        border-color 0.22s ease;
}

.distance-card:hover {
    transform: translateY(-4px);

    border-color: #cfe2ee;

    box-shadow:
        0 18px 42px rgba(23, 43, 77, 0.08);
}

.distance-card-top {
    display: flex;
    align-items: center;
    justify-content: space-between;

    gap: 10px;
}

.distance-icon {
    width: 45px;
    height: 45px;

    display: flex;
    align-items: center;
    justify-content: center;

    border: 1px solid #d3ebf8;
    border-radius: 13px;

    background:
        linear-gradient(
            135deg,
            #eaf6fc,
            #f7edf5
        );

    color: #249edb;
}

.distance-capacity {
    display: inline-flex;
    align-items: center;

    gap: 4px;

    padding: 5px 8px;

    border-radius: 999px;

    background: #f5f8fb;

    color: #718096;

    font-size: 10px;
    font-weight: 700;
}

.distance-card h3 {
    margin: 18px 0 4px;

    color: #172b4d;

    font-size: 24px;
    font-weight: 800;
}

.distance-start {
    display: flex;
    align-items: center;

    gap: 6px;

    margin-top: 8px;

    color: #718096;

    font-size: 12px;
}

.distance-start svg {
    color: #d94c9a;
}


/* =========================================================
   DISTANCE DETAILS
   ========================================================= */

.distance-prices,
.distance-inclusions,
.distance-categories {
    margin-top: 18px;
    padding-top: 16px;

    border-top: 1px solid #edf2f7;
}

.distance-prices h4,
.distance-inclusions h4,
.distance-categories h4 {
    margin: 0 0 9px;

    color: #172b4d;

    font-size: 12px;
    font-weight: 800;
}

.price-row {
    display: flex;
    align-items: center;
    justify-content: space-between;

    gap: 10px;

    padding: 8px 0;
}

.price-row > div {
    display: flex;
    flex-direction: column;

    gap: 2px;

    min-width: 0;
}

.price-name {
    color: #172b4d;

    font-size: 12px;
    font-weight: 800;
}

.price-row span {
    color: #94a3b8;

    font-size: 9px;
    line-height: 1.4;
}

.price-row .price-value {
    color: #d94c9a;

    font-size: 16px;
    font-weight: 800;

    white-space: nowrap;
}

.distance-inclusions ul {
    display: flex;
    flex-direction: column;

    gap: 7px;

    padding: 0;
    margin: 0;

    list-style: none;
}

.distance-inclusions li {
    display: flex;
    align-items: flex-start;

    gap: 7px;

    color: #52647a;

    font-size: 12px;
    line-height: 1.45;
}

.distance-inclusions li > svg {
    flex-shrink: 0;

    margin-top: 2px;

    color: #18b89a;
}

.distance-inclusions li span {
    display: flex;
    flex-direction: column;

    gap: 1px;
}

.distance-inclusions li small {
    color: #94a3b8;

    font-size: 10px;
}

.category-list {
    display: flex;
    flex-wrap: wrap;

    gap: 6px;
}

.category-tag {
    display: inline-flex;
    align-items: center;

    gap: 4px;

    padding: 6px 8px;

    border: 1px solid #e4ebf1;
    border-radius: 8px;

    background: #f7f9fb;

    color: #52647a;

    font-size: 10px;
    font-weight: 700;
}

.category-tag svg {
    color: #d94c9a;
}

.category-tag small {
    color: #94a3b8;

    font-weight: 500;
}


/* =========================================================
   BUTTON
   ========================================================= */

.distance-button {
    display: flex;
    align-items: center;
    justify-content: center;

    gap: 7px;

    margin-top: 18px;

    padding: 12px 16px;

    border-radius: 11px;

    background:
        linear-gradient(
            135deg,
            #249edb,
            #1769a8
        );

    box-shadow:
        0 8px 18px rgba(36, 158, 219, 0.15);

    color: #fff;

    font-size: 13px;
    font-weight: 800;

    text-decoration: none;

    transition:
        transform 0.2s ease,
        box-shadow 0.2s ease,
        background 0.2s ease;
}

.distance-button:hover {
    transform: translateY(-2px);

    background:
        linear-gradient(
            135deg,
            #1769a8,
            #d94c9a
        );

    box-shadow:
        0 11px 23px rgba(217, 76, 154, 0.17);
}


/* =========================================================
   REGISTRATION
   ========================================================= */

.registration-card {
    position: relative;

    display: flex;
    align-items: center;

    gap: 20px;

    overflow: hidden;

    padding: 28px;

    border: 1px solid #d8eaf4;
    border-radius: 20px;

    background:
        linear-gradient(
            135deg,
            #eaf6fc 0%,
            #fff 55%,
            #fdf2f8 100%
        );

    box-shadow:
        0 14px 38px rgba(23, 43, 77, 0.055);
}

.registration-card::after {
    content: '';

    position: absolute;

    width: 180px;
    height: 180px;

    right: -70px;
    top: -90px;

    border-radius: 50%;

    background: rgba(217, 76, 154, 0.07);
}

.registration-icon {
    position: relative;
    z-index: 1;

    width: 55px;
    height: 55px;

    display: flex;
    align-items: center;
    justify-content: center;

    flex-shrink: 0;

    border-radius: 15px;

    background:
        linear-gradient(
            135deg,
            #249edb,
            #d94c9a
        );

    box-shadow:
        0 9px 22px rgba(217, 76, 154, 0.15);

    color: #fff;
}

.registration-content {
    position: relative;
    z-index: 1;
}

.registration-content h2 {
    margin: 0;

    color: #172b4d;

    font-size: 24px;
    font-weight: 800;
}

.registration-dates {
    display: flex;
    flex-wrap: wrap;

    gap: 32px;

    margin-top: 14px;
}

.registration-dates div {
    display: flex;
    flex-direction: column;

    gap: 3px;
}

.registration-dates span {
    color: #718096;

    font-size: 10px;
    font-weight: 700;

    text-transform: uppercase;
}

.registration-dates strong {
    color: #172b4d;

    font-size: 13px;
}


/* =========================================================
   GALLERY
   ========================================================= */

.race-gallery-grid {
    display: grid;

    grid-template-columns: repeat(4, 1fr);

    gap: 10px;
}

.race-gallery-item {
    position: relative;

    height: 205px;

    overflow: hidden;

    border: 1px solid #dce7ef;
    border-radius: 14px;

    background: #e5edf4;

    box-shadow:
        0 9px 24px rgba(23, 43, 77, 0.05);
}

.race-gallery-button {
    display: block;

    width: 100%;

    padding: 0;

    appearance: none;

    cursor: pointer;

    text-align: left;

    font: inherit;

    transition:
        transform 0.25s ease,
        box-shadow 0.25s ease;
}

.race-gallery-button:hover {
    transform: translateY(-3px);

    box-shadow:
        0 15px 35px rgba(23, 43, 77, 0.11);
}

.race-gallery-button:focus-visible {
    outline: 3px solid rgba(36, 158, 219, 0.35);
    outline-offset: 3px;
}

.race-gallery-item::after {
    content: '';

    position: absolute;
    inset: 0;

    pointer-events: none;

    background:
        linear-gradient(
            180deg,
            transparent 55%,
            rgba(23, 43, 77, 0.16)
        );
}

.race-gallery-item img,
.race-gallery-item video {
    width: 100%;
    height: 100%;

    display: block;

    object-fit: cover;
}

.race-gallery-item img {
    transition:
        transform 0.4s ease,
        filter 0.4s ease;
}

.race-gallery-button:hover img {
    transform: scale(1.06);

    filter: saturate(1.06);
}

.race-gallery-item video {
    background: #0f172a;
}

.race-gallery-overlay {
    position: absolute;
    inset: 0;

    z-index: 2;

    display: flex;
    align-items: center;
    justify-content: center;

    color: #fff;

    opacity: 0;

    transition: opacity 0.25s ease;

    pointer-events: none;
}

.race-gallery-overlay svg {
    width: 46px;
    height: 46px;

    padding: 11px;

    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 50%;

    background: rgba(18, 85, 140, 0.35);

    backdrop-filter: blur(8px);

    box-shadow:
        0 8px 25px rgba(0, 0, 0, 0.15);
}

.race-gallery-button:hover .race-gallery-overlay {
    opacity: 1;
}


/* =========================================================
   LIGHTBOX
   ========================================================= */

.race-gallery-lightbox {
    position: fixed;
    inset: 0;

    z-index: 99999;

    display: flex;
    align-items: center;
    justify-content: center;

    padding: 35px;

    background: rgba(8, 25, 42, 0.92);

    backdrop-filter: blur(8px);
}

.race-lightbox-content {
    position: relative;

    max-width: min(1100px, 90vw);
    max-height: 88vh;

    display: flex;
    align-items: center;
    justify-content: center;
}

.race-lightbox-content img,
.race-lightbox-content video {
    display: block;

    max-width: 100%;
    max-height: 82vh;

    object-fit: contain;

    border-radius: 8px;

    box-shadow:
        0 25px 80px rgba(0, 0, 0, 0.35);
}

.race-lightbox-content video {
    background: #000;
}

.race-lightbox-counter {
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

    white-space: nowrap;
}

.race-lightbox-close,
.race-lightbox-arrow {
    position: absolute;

    z-index: 3;

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

.race-lightbox-close:hover,
.race-lightbox-arrow:hover {
    background: rgba(255, 255, 255, 0.2);

    transform: scale(1.05);
}

.race-lightbox-close {
    top: 22px;
    right: 22px;
}

.race-lightbox-arrow-left {
    left: 24px;
    top: 50%;

    transform: translateY(-50%);
}

.race-lightbox-arrow-right {
    right: 24px;
    top: 50%;

    transform: translateY(-50%);
}

.race-lightbox-arrow-left:hover,
.race-lightbox-arrow-right:hover {
    transform: translateY(-50%) scale(1.05);
}

.race-lightbox-close:focus-visible,
.race-lightbox-arrow:focus-visible {
    outline: 3px solid rgba(255, 255, 255, 0.35);
    outline-offset: 3px;
}


/* =========================================================
   RESULTS
   ========================================================= */

.results-card {
    position: relative;

    display: flex;
    align-items: center;

    gap: 20px;

    overflow: hidden;

    padding: 29px;

    border-radius: 20px;

    background:
        linear-gradient(
            110deg,
            #12558c 0%,
            #1769a8 45%,
            #249edb 72%,
            #d94c9a 100%
        );

    box-shadow:
        0 16px 42px rgba(23, 105, 168, 0.15);

    color: #fff;
}

.results-card::after {
    content: '';

    position: absolute;

    width: 240px;
    height: 240px;

    right: -100px;
    bottom: -150px;

    border-radius: 50%;

    background: rgba(255, 255, 255, 0.09);
}

.results-icon {
    position: relative;
    z-index: 1;

    width: 54px;
    height: 54px;

    display: flex;
    align-items: center;
    justify-content: center;

    flex-shrink: 0;

    border: 1px solid rgba(255, 255, 255, 0.18);
    border-radius: 15px;

    background: rgba(255, 255, 255, 0.11);

    color: #fff;
}

.results-content {
    position: relative;
    z-index: 1;

    flex: 1;
}

.results-content .race-section-kicker {
    margin-bottom: 4px;

    color: rgba(255, 255, 255, 0.65);
}

.results-content h2 {
    margin: 0;

    color: #fff;

    font-size: 24px;
    font-weight: 800;
}

.results-content p {
    margin: 5px 0 0;

    color: rgba(255, 255, 255, 0.74);

    font-size: 13px;
}

.results-button {
    position: relative;
    z-index: 1;

    display: inline-flex;
    align-items: center;

    gap: 7px;

    padding: 12px 18px;

    border-radius: 11px;

    background: #fff;

    color: #1769a8;

    font-size: 13px;
    font-weight: 800;

    text-decoration: none;

    white-space: nowrap;

    transition:
        transform 0.2s ease,
        box-shadow 0.2s ease;
}

.results-button:hover {
    transform: translateY(-2px);

    box-shadow:
        0 10px 22px rgba(0, 0, 0, 0.14);
}


/* =========================================================
   EXTRA
   ========================================================= */

.race-extra-grid {
    display: grid;

    grid-template-columns:
        repeat(2, minmax(0, 1fr));

    gap: 16px;
}

.race-extra-card {
    padding: 24px;

    border: 1px solid #e1eaf1;
    border-radius: 18px;

    background: #fff;

    box-shadow:
        0 10px 30px rgba(23, 43, 77, 0.04);
}

.race-extra-card h3 {
    margin: 0 0 11px;

    color: #172b4d;

    font-size: 18px;
    font-weight: 800;
}

.race-extra-card div {
    color: #64748b;

    font-size: 14px;
    line-height: 1.7;

    white-space: pre-line;
}


/* =========================================================
   CTA
   ========================================================= */

.race-cta {
    position: relative;

    overflow: hidden;

    padding: 62px 0;

    background:
        linear-gradient(
            90deg,
            #12558c 0%,
            #1769a8 30%,
            #249edb 54%,
            #d94c9a 78%,
            #f48bb0 100%
        );
}

.race-cta::before {
    content: '';

    position: absolute;

    width: 360px;
    height: 360px;

    right: -120px;
    top: -220px;

    border-radius: 50%;

    background: rgba(255, 255, 255, 0.09);
}

.race-cta::after {
    content: '';

    position: absolute;

    width: 250px;
    height: 250px;

    left: -150px;
    bottom: -180px;

    border-radius: 50%;

    background: rgba(255, 255, 255, 0.07);
}

.race-cta-content {
    position: relative;
    z-index: 1;

    display: flex;
    align-items: center;
    justify-content: space-between;

    gap: 25px;
}

.race-cta-content > div > span {
    color: rgba(255, 255, 255, 0.68);

    font-size: 11px;
    font-weight: 800;
    letter-spacing: 0.8px;

    text-transform: uppercase;
}

.race-cta-content h2 {
    margin: 5px 0 7px;

    color: #fff;

    font-size: clamp(30px, 3.5vw, 45px);
    line-height: 1;

    letter-spacing: -1.7px;

    font-weight: 800;
}

.race-cta-content p {
    margin: 0;

    color: rgba(255, 255, 255, 0.8);

    font-size: 14px;
}

.race-cta-button {
    display: inline-flex;
    align-items: center;
    justify-content: center;

    gap: 8px;

    padding: 13px 21px;

    border: 1px solid rgba(255, 255, 255, 0.45);
    border-radius: 12px;

    background: #fff;

    box-shadow:
        0 11px 25px rgba(23, 43, 77, 0.13);

    color: #1769a8;

    font-size: 14px;
    font-weight: 800;

    text-decoration: none;

    white-space: nowrap;

    transition:
        transform 0.2s ease,
        box-shadow 0.2s ease,
        color 0.2s ease;
}

.race-cta-button:hover {
    transform: translateY(-2px);

    box-shadow:
        0 15px 30px rgba(23, 43, 77, 0.18);

    color: #d94c9a;
}

.race-cta-status {
    display: inline-flex;
    align-items: center;
    justify-content: center;

    padding: 12px 17px;

    border: 1px solid rgba(255, 255, 255, 0.22);
    border-radius: 11px;

    background: rgba(255, 255, 255, 0.1);

    backdrop-filter: blur(8px);

    color: #fff !important;

    font-size: 13px;
    font-weight: 800;
}


/* =========================================================
   RESPONSIVE
   ========================================================= */

@media (max-width: 1100px) {
    .race-summary-grid {
        grid-template-columns: repeat(2, 1fr);
    }

    .distance-grid {
        grid-template-columns: repeat(2, 1fr);
    }

    .race-gallery-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 850px) {
    .race-hero {
        min-height: 480px;
    }

    .race-hero-content {
        padding: 70px 0 85px;
    }

    .race-details-grid {
        grid-template-columns: 1fr;
    }

    .race-extra-grid {
        grid-template-columns: 1fr;
    }

    .results-card {
        align-items: flex-start;

        flex-direction: column;
    }

    .results-button {
        width: 100%;

        justify-content: center;
    }

    .race-cta-content {
        align-items: flex-start;

        flex-direction: column;
    }

    .race-lightbox-arrow-left {
        left: 12px;
    }

    .race-lightbox-arrow-right {
        right: 12px;
    }
}

@media (max-width: 620px) {
    .race-container {
        width: min(100% - 28px, 1180px);
    }

    .race-hero {
        min-height: 580px;
    }

    .race-hero-content {
        padding: 62px 0 85px;
    }

    .race-hero h1 {
        font-size: 42px;
        letter-spacing: -1.8px;
    }

    .race-summary {
        margin-top: -28px;
    }

    .race-summary-grid {
        grid-template-columns: 1fr;

        gap: 8px;
    }

    .race-summary-card {
        min-height: 80px;

        padding: 15px;
    }

    .race-section {
        padding: 48px 0;
    }

    .race-section-heading {
        margin-bottom: 24px;
    }

    .race-section-heading h2 {
        font-size: 32px;
        letter-spacing: -1.1px;
    }

    .race-section-heading p {
        font-size: 14px;
    }

    .race-description-card,
    .race-location-card {
        padding: 21px;

        border-radius: 16px;
    }

    .distance-grid {
        grid-template-columns: 1fr;
    }

    .distance-card {
        padding: 20px;
    }

    .distance-card h3 {
        font-size: 22px;
    }

    .race-gallery-grid {
        grid-template-columns: 1fr 1fr;

        gap: 7px;
    }

    .race-gallery-item {
        height: 155px;

        border-radius: 11px;
    }

    .registration-card {
        align-items: flex-start;

        flex-direction: column;

        padding: 23px;
    }

    .registration-dates {
        flex-direction: column;

        gap: 14px;
    }

    .results-card {
        padding: 23px;

        border-radius: 17px;
    }

    .results-content h2 {
        font-size: 22px;
    }

    .race-extra-card {
        padding: 21px;
    }

    .race-cta {
        padding: 48px 0;
    }

    .race-cta-content h2 {
        font-size: 34px;
        letter-spacing: -1.2px;
    }

    .race-cta-button {
        width: 100%;
    }

    .race-gallery-lightbox {
        padding: 15px;
    }

    .race-lightbox-close {
        top: 15px;
        right: 15px;
    }

    .race-lightbox-arrow {
        width: 40px;
        height: 40px;
    }

    .race-lightbox-arrow-left {
        left: 8px;
    }

    .race-lightbox-arrow-right {
        right: 8px;
    }

    .race-lightbox-content {
        max-width: 94vw;
    }

    .race-lightbox-content img,
    .race-lightbox-content video {
        max-width: 94vw;
        max-height: 78vh;
    }
}
</style>
