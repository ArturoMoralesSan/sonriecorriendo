<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    ArrowLeft,
    CalendarDays,
    ClipboardList,
    Clock3,
    FileText,
    Image,
    MapPin,
    Pencil,
    Tag,
    Trophy,
    Users,
    Wallet,
} from 'lucide-vue-next';

import admin from '@/routes/admin';

interface RacePrice {
    id: number;
    name: string;
    price: string | number;
    starts_at: string | null;
    ends_at: string | null;
    capacity: number | null;
    sort_order: number;
    is_active: boolean;
}

interface RaceInclusion {
    id: number;
    name: string;
    description: string | null;
    type: string;
    included: boolean;
    sort_order: number;
}

interface RaceCategory {
    id: number;
    name: string;
    description: string | null;
    min_age: number | null;
    max_age: number | null;
    gender: string;
    sort_order: number;
    is_active: boolean;
}

interface RaceDistance {
    id: number;
    name: string;
    distance: string | number;
    unit: string;
    start_time: string | null;
    capacity: number | null;
    sort_order: number;
    is_active: boolean;
    prices?: RacePrice[];
    inclusions?: RaceInclusion[];
    categories?: RaceCategory[];
}

interface Race {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    event_date: string | null;
    start_time: string | null;
    end_time: string | null;
    location: string | null;
    address: string | null;
    city: string | null;
    state: string | null;
    country: string | null;
    banner: string | null;
    registration_opens_at: string | null;
    registration_closes_at: string | null;
    status: string;
    terms_and_conditions: string | null;
    notes: string | null;
    distances?: RaceDistance[];
}

const props = defineProps<{
    race: Race;
}>();

const getStatusLabel = (status: string): string => {
    const labels: Record<string, string> = {
        draft: 'Borrador',
        published: 'Publicada',
        registration_open: 'Inscripciones abiertas',
        registration_closed: 'Inscripciones cerradas',
        finished: 'Finalizada',
        cancelled: 'Cancelada',
    };

    return labels[status] ?? status;
};

const getStatusClass = (status: string): string => {
    const classes: Record<string, string> = {
        draft: 'status-draft',
        published: 'status-published',
        registration_open: 'status-active',
        registration_closed: 'status-closed',
        finished: 'status-finished',
        cancelled: 'status-inactive',
    };

    return classes[status] ?? 'status-draft';
};

const formatDate = (
    date: string | null | undefined,
): string => {
    if (!date) {
        return 'Sin fecha';
    }

    const value = String(date).substring(0, 10);
    const parts = value.split('-');

    if (parts.length !== 3) {
        return 'Sin fecha';
    }

    const [year, month, day] = parts;

    if (
        !year ||
        !month ||
        !day ||
        Number.isNaN(Number(year)) ||
        Number.isNaN(Number(month)) ||
        Number.isNaN(Number(day))
    ) {
        return 'Sin fecha';
    }

    return `${day}/${month}/${year}`;
};

const formatDateTime = (
    value: string | null | undefined,
): string => {
    if (!value) {
        return 'No especificado';
    }

    const normalized = String(value).replace(' ', 'T');
    const date = new Date(normalized);

    if (Number.isNaN(date.getTime())) {
        return 'No especificado';
    }

    return new Intl.DateTimeFormat('es-MX', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    }).format(date);
};

const formatTime = (
    value: string | null | undefined,
): string => {
    if (!value) {
        return 'No especificada';
    }

    const parts = String(value).split(':');

    if (parts.length < 2) {
        return String(value);
    }

    const hour = parts[0];
    const minute = parts[1];

    if (!hour || !minute) {
        return 'No especificada';
    }

    return `${hour}:${minute}`;
};

const formatPrice = (
    price: string | number | null | undefined,
): string => {
    if (
        price === null ||
        price === undefined ||
        price === ''
    ) {
        return '$0.00';
    }

    const numericPrice = Number(price);

    if (Number.isNaN(numericPrice)) {
        return '$0.00';
    }

    return new Intl.NumberFormat('es-MX', {
        style: 'currency',
        currency: 'MXN',
    }).format(numericPrice);
};

const formatDistance = (
    distance: string | number,
    unit: string,
): string => {
    const numericDistance = Number(distance);

    if (Number.isNaN(numericDistance)) {
        return `${distance} ${unit}`;
    }

    return `${numericDistance} ${unit}`;
};

const formatLocation = (): string => {
    const parts = [
        props.race.location,
        props.race.address,
        props.race.city,
        props.race.state,
        props.race.country,
    ].filter(
        (value): value is string =>
            Boolean(value && value.trim()),
    );

    return parts.length > 0
        ? parts.join(', ')
        : 'Sin ubicación especificada';
};

const getBannerUrl = (
    banner: string | null,
): string | null => {
    if (!banner) {
        return null;
    }

    if (
        banner.startsWith('http://') ||
        banner.startsWith('https://') ||
        banner.startsWith('/')
    ) {
        return banner;
    }

    return `/storage/${banner}`;
};

const getGenderLabel = (
    gender: string,
): string => {
    const labels: Record<string, string> = {
        mixed: 'Mixta',
        male: 'Varonil',
        female: 'Femenil',
    };

    return labels[gender] ?? gender;
};

const getAgeRange = (
    category: RaceCategory,
): string => {
    if (
        category.min_age !== null &&
        category.max_age !== null
    ) {
        return `${category.min_age} - ${category.max_age} años`;
    }

    if (category.min_age !== null) {
        return `${category.min_age}+ años`;
    }

    if (category.max_age !== null) {
        return `Hasta ${category.max_age} años`;
    }

    return 'Todas las edades';
};

const activeDistances = (): RaceDistance[] => {
    return (props.race.distances ?? []).filter(
        (distance) => distance.is_active,
    );
};

const totalDistances = (): number => {
    return activeDistances().length;
};

const totalPrices = (): number => {
    return activeDistances().reduce(
        (total, distance) =>
            total + (distance.prices?.length ?? 0),
        0,
    );
};

const totalInclusions = (): number => {
    return activeDistances().reduce(
        (total, distance) =>
            total + (distance.inclusions?.length ?? 0),
        0,
    );
};

const totalCategories = (): number => {
    return activeDistances().reduce(
        (total, distance) =>
            total + (distance.categories?.length ?? 0),
        0,
    );
};

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Panel',
                href: admin.dashboard(),
            },
            {
                title: 'Carreras',
                href: admin.races.index(),
            },
            {
                title: 'Detalle de carrera',
                href: admin.races.index(),
            },
        ],
    },
});
</script>

<template>
    <Head :title="race.name" />

    <div class="admin-page">
        <!-- =================================================
             HEADER
        ================================================== -->

        <header class="admin-page-header">
            <div>
                <p class="admin-page-eyebrow">
                    Carrera
                </p>

                <h1 class="admin-page-title">
                    {{ race.name }}
                </h1>

                <p class="admin-page-subtitle">
                    Consulta la información general, configuración,
                    distancias, precios y beneficios de esta carrera.
                </p>
            </div>

            <div class="admin-page-header-actions">
            <Link
                :href="
                    admin.races.gallery.index({
                        race: race.id,
                    }).url
                "
                class="admin-btn admin-btn-secondary"
            >
                <Image :size="14" :stroke-width="2" />
                Galería
            </Link>
                <Link
                    :href="
                        admin.races.checklist.index(
                            race.id,
                        ).url
                    "
                    class="admin-btn admin-btn-secondary"
                    title="Ver checklist de la carrera"
                >
                    <ClipboardList
                        :size="14"
                        :stroke-width="2"
                    />

                    Checklist
                </Link>

                <Link
                    :href="
                        admin.races.expenses.index({
                            race: race.id,
                        }).url
                    "
                    class="admin-btn admin-btn-secondary"
                    title="Controlar gastos de la carrera"
                >
                    <Wallet
                        :size="14"
                        :stroke-width="2"
                    />

                    Gastos
                </Link>

                <Link
                    :href="admin.races.index().url"
                    class="admin-btn admin-btn-secondary"
                >
                    <ArrowLeft
                        :size="14"
                        :stroke-width="2"
                    />

                    Regresar
                </Link>

                <Link
                    :href="admin.races.edit(race.id).url"
                    class="admin-btn admin-btn-primary"
                >
                    <Pencil
                        :size="14"
                        :stroke-width="2"
                    />

                    Editar
                </Link>
            </div>
        </header>

        <!-- =================================================
             BANNER
        ================================================== -->

        <section
            v-if="getBannerUrl(race.banner)"
            class="race-show-banner"
        >
            <img
                :src="getBannerUrl(race.banner)!"
                :alt="race.name"
                class="race-show-banner-image"
            />

            <div class="race-show-banner-overlay">
                <div>
                    <p class="race-show-banner-label">
                        Evento
                    </p>

                    <h2>
                        {{ race.name }}
                    </h2>
                </div>

                <span
                    class="status-badge"
                    :class="getStatusClass(race.status)"
                >
                    <span class="status-dot"></span>

                    {{ getStatusLabel(race.status) }}
                </span>
            </div>
        </section>

        <!-- =================================================
             SUMMARY
        ================================================== -->

        <section class="race-summary-grid">
            <div class="race-summary-card">
                <div class="race-summary-icon">
                    <CalendarDays
                        :size="17"
                        :stroke-width="2"
                    />
                </div>

                <div>
                    <span>
                        Fecha del evento
                    </span>

                    <strong>
                        {{ formatDate(race.event_date) }}
                    </strong>
                </div>
            </div>

            <div class="race-summary-card">
                <div class="race-summary-icon">
                    <MapPin
                        :size="17"
                        :stroke-width="2"
                    />
                </div>

                <div>
                    <span>
                        Ubicación
                    </span>

                    <strong>
                        {{ race.location || 'Sin ubicación' }}
                    </strong>
                </div>
            </div>

            <div class="race-summary-card">
                <div class="race-summary-icon">
                    <Trophy
                        :size="17"
                        :stroke-width="2"
                    />
                </div>

                <div>
                    <span>
                        Distancias
                    </span>

                    <strong>
                        {{ totalDistances() }}
                    </strong>
                </div>
            </div>

            <div class="race-summary-card">
                <div class="race-summary-icon">
                    <Users
                        :size="17"
                        :stroke-width="2"
                    />
                </div>

                <div>
                    <span>
                        Categorías
                    </span>

                    <strong>
                        {{ totalCategories() }}
                    </strong>
                </div>
            </div>
        </section>

        <!-- =================================================
             GENERAL + EVENT
        ================================================== -->

        <div class="race-show-grid">
            <!-- GENERAL -->

            <section class="admin-form-card">
                <div class="show-card-header">
                    <div>
                        <p class="show-card-eyebrow">
                            Información
                        </p>

                        <h2 class="show-card-title">
                            Información general
                        </h2>
                    </div>

                    <div class="show-card-icon">
                        <FileText
                            :size="17"
                            :stroke-width="2"
                        />
                    </div>
                </div>

                <div class="show-card-body">
                    <div class="show-field">
                        <span class="show-field-label">
                            Nombre
                        </span>

                        <strong>
                            {{ race.name }}
                        </strong>
                    </div>

                    <div class="show-field">
                        <span class="show-field-label">
                            Slug
                        </span>

                        <strong class="show-mono">
                            {{ race.slug }}
                        </strong>
                    </div>

                    <div class="show-field show-field-full">
                        <span class="show-field-label">
                            Descripción
                        </span>

                        <p
                            v-if="race.description"
                            class="show-description"
                        >
                            {{ race.description }}
                        </p>

                        <span
                            v-else
                            class="show-empty"
                        >
                            Sin descripción.
                        </span>
                    </div>
                </div>
            </section>

            <!-- EVENT -->

            <section class="admin-form-card">
                <div class="show-card-header">
                    <div>
                        <p class="show-card-eyebrow">
                            Evento
                        </p>

                        <h2 class="show-card-title">
                            Fecha y ubicación
                        </h2>
                    </div>

                    <div class="show-card-icon">
                        <MapPin
                            :size="17"
                            :stroke-width="2"
                        />
                    </div>
                </div>

                <div class="show-card-body">
                    <div class="show-field">
                        <span class="show-field-label">
                            Fecha
                        </span>

                        <strong>
                            {{ formatDate(race.event_date) }}
                        </strong>
                    </div>

                    <div class="show-field">
                        <span class="show-field-label">
                            Horario
                        </span>

                        <strong>
                            <template
                                v-if="
                                    race.start_time ||
                                    race.end_time
                                "
                            >
                                {{ formatTime(race.start_time) }}

                                <template v-if="race.end_time">
                                    —
                                    {{ formatTime(race.end_time) }}
                                </template>
                            </template>

                            <template v-else>
                                No especificado
                            </template>
                        </strong>
                    </div>

                    <div class="show-field show-field-full">
                        <span class="show-field-label">
                            Ubicación
                        </span>

                        <strong>
                            {{ formatLocation() }}
                        </strong>
                    </div>
                </div>
            </section>
        </div>

        <!-- =================================================
             REGISTRATION
        ================================================== -->

        <section class="admin-form-card">
            <div class="show-card-header">
                <div>
                    <p class="show-card-eyebrow">
                        Registro
                    </p>

                    <h2 class="show-card-title">
                        Configuración de inscripciones
                    </h2>
                </div>

                <div class="show-card-icon">
                    <CalendarDays
                        :size="17"
                        :stroke-width="2"
                    />
                </div>
            </div>

            <div class="show-card-body">
                <div class="show-info-grid">
                    <div class="show-info-box">
                        <span>
                            Apertura
                        </span>

                        <strong>
                            {{
                                formatDateTime(
                                    race.registration_opens_at,
                                )
                            }}
                        </strong>
                    </div>

                    <div class="show-info-box">
                        <span>
                            Cierre
                        </span>

                        <strong>
                            {{
                                formatDateTime(
                                    race.registration_closes_at,
                                )
                            }}
                        </strong>
                    </div>

                    <div class="show-info-box">
                        <span>
                            Estado
                        </span>

                        <strong>
                            <span
                                class="status-badge"
                                :class="
                                    getStatusClass(
                                        race.status,
                                    )
                                "
                            >
                                <span class="status-dot"></span>

                                {{
                                    getStatusLabel(
                                        race.status,
                                    )
                                }}
                            </span>
                        </strong>
                    </div>
                </div>
            </div>
        </section>

        <!-- =================================================
             DISTANCES
        ================================================== -->

        <section class="admin-form-card">
            <div class="show-card-header">
                <div>
                    <p class="show-card-eyebrow">
                        Configuración
                    </p>

                    <h2 class="show-card-title">
                        Distancias
                    </h2>

                    <p class="show-card-description">
                        {{ totalDistances() }}
                        distancias activas,
                        {{ totalPrices() }}
                        precios y
                        {{ totalInclusions() }}
                        beneficios configurados.
                    </p>
                </div>

                <div class="show-card-icon">
                    <Trophy
                        :size="17"
                        :stroke-width="2"
                    />
                </div>
            </div>

            <div class="show-card-body">
                <div
                    v-if="activeDistances().length > 0"
                    class="distance-show-list"
                >
                    <article
                        v-for="distance in activeDistances()"
                        :key="distance.id"
                        class="distance-show-card"
                    >
                        <!-- DISTANCE HEADER -->

                        <div class="distance-show-header">
                            <div class="distance-show-title">
                                <div class="distance-number">
                                    <Trophy
                                        :size="16"
                                        :stroke-width="2"
                                    />
                                </div>

                                <div>
                                    <h3>
                                        {{ distance.name }}
                                    </h3>

                                    <span>
                                        {{
                                            formatDistance(
                                                distance.distance,
                                                distance.unit,
                                            )
                                        }}
                                    </span>
                                </div>
                            </div>

                            <div class="distance-show-meta">
                                <span
                                    v-if="distance.start_time"
                                    class="distance-meta"
                                >
                                    <Clock3
                                        :size="13"
                                        :stroke-width="2"
                                    />

                                    {{
                                        formatTime(
                                            distance.start_time,
                                        )
                                    }}
                                </span>

                                <span
                                    v-if="
                                        distance.capacity !== null
                                    "
                                    class="distance-meta"
                                >
                                    <Users
                                        :size="13"
                                        :stroke-width="2"
                                    />

                                    {{ distance.capacity }}
                                    lugares
                                </span>

                                <span
                                    v-else
                                    class="distance-meta"
                                >
                                    <Users
                                        :size="13"
                                        :stroke-width="2"
                                    />

                                    Sin límite
                                </span>
                            </div>
                        </div>

                        <!-- PRICES -->

                        <div
                            v-if="
                                distance.prices &&
                                distance.prices.length > 0
                            "
                            class="distance-section"
                        >
                            <div class="distance-section-title">
                                <Tag
                                    :size="14"
                                    :stroke-width="2"
                                />

                                <span>
                                    Precios
                                </span>
                            </div>

                            <div class="price-list">
                                <div
                                    v-for="price in distance.prices"
                                    :key="price.id"
                                    class="price-item"
                                >
                                    <div class="price-main">
                                        <strong>
                                            {{ price.name }}
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
                                                    price.ends_at
                                                "
                                            >
                                                hasta
                                                {{
                                                    formatDateTime(
                                                        price.ends_at,
                                                    )
                                                }}
                                            </template>
                                        </span>
                                    </div>

                                    <div class="price-side">
                                        <strong>
                                            {{
                                                formatPrice(
                                                    price.price,
                                                )
                                            }}
                                        </strong>

                                        <span
                                            v-if="
                                                price.capacity !==
                                                null
                                            "
                                        >
                                            {{
                                                price.capacity
                                            }}
                                            lugares
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- INCLUSIONS -->

                        <div
                            v-if="
                                distance.inclusions &&
                                distance.inclusions.length > 0
                            "
                            class="distance-section"
                        >
                            <div class="distance-section-title">
                                <Tag
                                    :size="14"
                                    :stroke-width="2"
                                />

                                <span>
                                    Beneficios e inclusiones
                                </span>
                            </div>

                            <div class="inclusion-list">
                                <div
                                    v-for="
                                        inclusion in distance.inclusions
                                    "
                                    :key="inclusion.id"
                                    class="inclusion-item"
                                >
                                    <div
                                        class="inclusion-marker"
                                        :class="{
                                            inactive:
                                                !inclusion.included,
                                        }"
                                    >
                                        <span></span>
                                    </div>

                                    <div>
                                        <strong>
                                            {{ inclusion.name }}
                                        </strong>

                                        <p
                                            v-if="
                                                inclusion.description
                                            "
                                        >
                                            {{
                                                inclusion.description
                                            }}
                                        </p>
                                    </div>

                                    <span
                                        class="inclusion-status"
                                        :class="{
                                            inactive:
                                                !inclusion.included,
                                        }"
                                    >
                                        {{
                                            inclusion.included
                                                ? 'Incluido'
                                                : 'No incluido'
                                        }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- CATEGORIES -->

                        <div
                            v-if="
                                distance.categories &&
                                distance.categories.length > 0
                            "
                            class="distance-section"
                        >
                            <div class="distance-section-title">
                                <Users
                                    :size="14"
                                    :stroke-width="2"
                                />

                                <span>
                                    Categorías
                                </span>
                            </div>

                            <div class="category-list">
                                <div
                                    v-for="
                                        category in distance.categories
                                    "
                                    :key="category.id"
                                    class="category-item"
                                >
                                    <div>
                                        <strong>
                                            {{ category.name }}
                                        </strong>

                                        <span>
                                            {{
                                                getGenderLabel(
                                                    category.gender,
                                                )
                                            }}
                                            ·
                                            {{
                                                getAgeRange(
                                                    category,
                                                )
                                            }}
                                        </span>
                                    </div>

                                    <span
                                        v-if="category.is_active"
                                        class="category-active"
                                    >
                                        Activa
                                    </span>
                                </div>
                            </div>
                        </div>
                    </article>
                </div>

                <div
                    v-else
                    class="show-empty-block"
                >
                    <Trophy
                        :size="20"
                        :stroke-width="2"
                    />

                    <strong>
                        No hay distancias activas
                    </strong>

                    <span>
                        Esta carrera todavía no tiene distancias
                        configuradas.
                    </span>
                </div>
            </div>
        </section>

        <!-- =================================================
             TERMS + NOTES
        ================================================== -->

        <div class="race-show-grid">
            <section class="admin-form-card">
                <div class="show-card-header">
                    <div>
                        <p class="show-card-eyebrow">
                            Legal
                        </p>

                        <h2 class="show-card-title">
                            Términos y condiciones
                        </h2>
                    </div>

                    <div class="show-card-icon">
                        <FileText
                            :size="17"
                            :stroke-width="2"
                        />
                    </div>
                </div>

                <div class="show-card-body">
                    <p
                        v-if="race.terms_and_conditions"
                        class="show-long-text"
                    >
                        {{ race.terms_and_conditions }}
                    </p>

                    <span
                        v-else
                        class="show-empty"
                    >
                        Sin términos y condiciones registrados.
                    </span>
                </div>
            </section>

            <section class="admin-form-card">
                <div class="show-card-header">
                    <div>
                        <p class="show-card-eyebrow">
                            Interno
                        </p>

                        <h2 class="show-card-title">
                            Notas
                        </h2>
                    </div>

                    <div class="show-card-icon">
                        <FileText
                            :size="17"
                            :stroke-width="2"
                        />
                    </div>
                </div>

                <div class="show-card-body">
                    <p
                        v-if="race.notes"
                        class="show-long-text"
                    >
                        {{ race.notes }}
                    </p>

                    <span
                        v-else
                        class="show-empty"
                    >
                        Sin notas registradas.
                    </span>
                </div>
            </section>
        </div>

        <!-- =================================================
             BANNER INFO
        ================================================== -->

        <section
            v-if="race.banner"
            class="admin-form-card"
        >
            <div class="show-card-header">
                <div>
                    <p class="show-card-eyebrow">
                        Multimedia
                    </p>

                    <h2 class="show-card-title">
                        Imagen del evento
                    </h2>
                </div>

                <div class="show-card-icon">
                    <Image
                        :size="17"
                        :stroke-width="2"
                    />
                </div>
            </div>

            <div class="show-card-body">
                <div class="banner-file-info">
                    <Image
                        :size="16"
                        :stroke-width="2"
                    />

                    <span>
                        {{ race.banner }}
                    </span>
                </div>
            </div>
        </section>
    </div>
</template>

<style scoped>
/* =========================================================
   PAGE SPACING
   ========================================================= */

.admin-page {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

/* =========================================================
   BANNER
   ========================================================= */

.race-show-banner {
    position: relative;
    width: 100%;
    height: 250px;
    overflow: hidden;
    border: 1px solid var(--sc-page-border);
    border-radius: var(--sc-page-radius);
    background: #f4f8fa;
    box-shadow:
        0 4px 15px rgba(27, 62, 90, 0.035),
        0 1px 3px rgba(27, 62, 90, 0.025);
}

.race-show-banner-image {
    display: block;
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.race-show-banner::after {
    position: absolute;
    inset: 0;
    background: linear-gradient(
        180deg,
        rgba(16, 30, 41, 0.05) 0%,
        rgba(16, 30, 41, 0.7) 100%
    );
    content: '';
}

.race-show-banner-overlay {
    position: absolute;
    right: 0;
    bottom: 0;
    left: 0;
    z-index: 2;
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 20px;
    padding: 28px;
}

.race-show-banner-label {
    margin: 0 0 5px;
    color: rgba(255, 255, 255, 0.78);
    font-size: 12px;
    font-weight: 650;
    letter-spacing: 0.04em;
    text-transform: uppercase;
}

.race-show-banner h2 {
    margin: 0;
    color: #ffffff;
    font-size: 24px;
    font-weight: 700;
    line-height: 1.2;
}

.race-show-banner .status-badge {
    flex: 0 0 auto;
    border-color: rgba(255, 255, 255, 0.2);
}

/* =========================================================
   SUMMARY
   ========================================================= */

.race-summary-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 16px;
}

.race-summary-card {
    display: flex;
    align-items: center;
    gap: 11px;
    min-height: 76px;
    padding: 14px;
    border: 1px solid var(--sc-page-border);
    border-radius: var(--sc-page-radius);
    background: #ffffff;
    box-shadow:
        0 4px 15px rgba(27, 62, 90, 0.035),
        0 1px 3px rgba(27, 62, 90, 0.025);
}

.race-summary-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 36px;
    height: 36px;
    flex: 0 0 36px;
    border: 1px solid #dce9ef;
    border-radius: 10px;
    background: #f5f9fb;
    color: #6d8a9b;
}

.race-summary-card > div:last-child {
    display: flex;
    flex-direction: column;
    min-width: 0;
    gap: 4px;
}

.race-summary-card span {
    color: #8a9aa6;
    font-size: 11px;
    font-weight: 600;
}

.race-summary-card strong {
    overflow: hidden;
    color: var(--sc-page-text);
    font-size: 14px;
    font-weight: 650;
    text-overflow: ellipsis;
    white-space: nowrap;
}

/* =========================================================
   SHOW GRIDS
   ========================================================= */

.race-show-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 20px;
}

/* =========================================================
   CARDS
   ========================================================= */

.admin-form-card {
    width: 100%;
    overflow: hidden;
}

/* =========================================================
   CARD HEADER
   ========================================================= */

.show-card-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 20px;
    padding: 21px 24px;
    border-bottom: 1px solid var(--sc-page-border);
}

.show-card-eyebrow {
    margin: 0 0 5px;
    color: #91a1ac;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.06em;
    text-transform: uppercase;
}

.show-card-title {
    margin: 0;
    color: var(--sc-page-text);
    font-size: 16px;
    font-weight: 700;
}

.show-card-description {
    margin: 6px 0 0;
    color: #8999a4;
    font-size: 12px;
    line-height: 1.55;
}

.show-card-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 34px;
    height: 34px;
    flex: 0 0 34px;
    border: 1px solid #dce9ef;
    border-radius: 9px;
    background: #f5f9fb;
    color: #7592a3;
}

/* =========================================================
   CARD BODY
   ========================================================= */

.show-card-body {
    padding: 23px 24px;
}

/* =========================================================
   FIELDS
   ========================================================= */

.show-field {
    display: flex;
    flex-direction: column;
    gap: 6px;
    padding: 14px 0;
    border-bottom: 1px solid #edf1f3;
}

.show-field:first-child {
    padding-top: 0;
}

.show-field:last-child {
    border-bottom: 0;
    padding-bottom: 0;
}

.show-field-full {
    grid-column: 1 / -1;
}

.show-field-label {
    color: #91a1ac;
    font-size: 11px;
    font-weight: 650;
}

.show-field strong {
    color: var(--sc-page-text);
    font-size: 14px;
    font-weight: 650;
    line-height: 1.5;
}

.show-mono {
    font-family:
        ui-monospace,
        SFMono-Regular,
        Menlo,
        Monaco,
        Consolas,
        monospace;
    font-size: 12px !important;
    font-weight: 500 !important;
}

.show-description,
.show-long-text {
    margin: 0;
    color: #647884;
    font-size: 13px;
    line-height: 1.75;
    white-space: pre-line;
}

.show-empty {
    color: #9aa7af;
    font-size: 12px;
    font-style: italic;
}

/* =========================================================
   REGISTRATION INFO
   ========================================================= */

.show-info-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 10px;
}

.show-info-box {
    display: flex;
    flex-direction: column;
    gap: 8px;
    min-width: 0;
    padding: 13px;
    border: 1px solid #e5ecef;
    border-radius: 10px;
    background: #fbfcfd;
}

.show-info-box > span {
    color: #91a1ac;
    font-size: 11px;
    font-weight: 650;
}

.show-info-box > strong {
    color: var(--sc-page-text);
    font-size: 13px;
    font-weight: 650;
    line-height: 1.5;
}

/* =========================================================
   DISTANCES
   ========================================================= */

.distance-show-list {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.distance-show-card {
    overflow: hidden;
    border: 1px solid var(--sc-page-border);
    border-radius: 12px;
    background: #fbfcfd;
}

.distance-show-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 15px;
    padding: 17px;
    border-bottom: 1px solid var(--sc-page-border);
    background: #ffffff;
}

.distance-show-title {
    display: flex;
    align-items: center;
    gap: 11px;
    min-width: 0;
}

.distance-number {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 34px;
    height: 34px;
    flex: 0 0 34px;
    border: 1px solid #dce9ef;
    border-radius: 9px;
    background: #f5f9fb;
    color: #718d9e;
}

.distance-show-title > div:last-child {
    min-width: 0;
}

.distance-show-title h3 {
    margin: 0 0 4px;
    overflow: hidden;
    color: var(--sc-page-text);
    font-size: 14px;
    font-weight: 700;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.distance-show-title span {
    color: #8a9aa6;
    font-size: 11px;
}

.distance-show-meta {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    flex-wrap: wrap;
    gap: 7px;
}

.distance-meta {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    min-height: 27px;
    padding: 0 9px;
    border: 1px solid #e0e9ed;
    border-radius: 7px;
    background: #f8fafb;
    color: #718692;
    font-size: 10px;
    font-weight: 600;
}

.distance-section {
    padding: 18px;
    border-bottom: 1px solid #e5ebee;
}

.distance-section:last-child {
    border-bottom: 0;
}

.distance-section-title {
    display: flex;
    align-items: center;
    gap: 6px;
    margin-bottom: 12px;
    color: #6f8491;
    font-size: 12px;
    font-weight: 700;
}

/* =========================================================
   PRICE / INCLUSION / CATEGORY LISTS
   ========================================================= */

.price-list,
.inclusion-list,
.category-list {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.price-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 15px;
    padding: 11px 12px;
    border: 1px solid #e5ebee;
    border-radius: 8px;
    background: #ffffff;
}

.price-main {
    display: flex;
    flex-direction: column;
    gap: 4px;
    min-width: 0;
}

.price-main strong {
    color: var(--sc-page-text);
    font-size: 12px;
    font-weight: 650;
}

.price-main span {
    color: #91a0a9;
    font-size: 10px;
    line-height: 1.5;
}

.price-side {
    display: flex;
    align-items: flex-end;
    flex-direction: column;
    gap: 4px;
    flex: 0 0 auto;
}

.price-side strong {
    color: #557b8e;
    font-size: 14px;
    font-weight: 700;
}

.price-side span {
    color: #96a3aa;
    font-size: 10px;
}

.inclusion-item {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    padding: 10px 11px;
    border: 1px solid #e5ebee;
    border-radius: 8px;
    background: #ffffff;
}

.inclusion-marker {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 18px;
    height: 18px;
    flex: 0 0 18px;
    margin-top: 2px;
    border-radius: 50%;
    background: #e5f4ed;
}

.inclusion-marker span {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: #6caa8d;
}

.inclusion-marker.inactive {
    background: #f2f3f4;
}

.inclusion-marker.inactive span {
    background: #a7afb4;
}

.inclusion-item > div:nth-child(2) {
    min-width: 0;
    flex: 1;
}

.inclusion-item strong {
    display: block;
    color: var(--sc-page-text);
    font-size: 12px;
    font-weight: 650;
}

.inclusion-item p {
    margin: 4px 0 0;
    color: #8d9ba4;
    font-size: 10px;
    line-height: 1.55;
}

.inclusion-status {
    flex: 0 0 auto;
    color: #628b78;
    font-size: 10px;
    font-weight: 650;
}

.inclusion-status.inactive {
    color: #9a9fa2;
}

.category-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    padding: 11px 12px;
    border: 1px solid #e5ebee;
    border-radius: 8px;
    background: #ffffff;
}

.category-item > div {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.category-item strong {
    color: var(--sc-page-text);
    font-size: 12px;
    font-weight: 650;
}

.category-item span {
    color: #8e9ca5;
    font-size: 10px;
}

.category-active {
    padding: 5px 8px;
    border: 1px solid #d7e9e0;
    border-radius: 999px;
    background: #f2faf6;
    color: #638b79 !important;
    font-size: 10px !important;
    font-weight: 650;
}

/* =========================================================
   EMPTY
   ========================================================= */

.show-empty-block {
    display: flex;
    align-items: center;
    flex-direction: column;
    justify-content: center;
    gap: 8px;
    padding: 35px 20px;
    color: #93a1a9;
    text-align: center;
}

.show-empty-block strong {
    color: var(--sc-page-text);
    font-size: 13px;
}

.show-empty-block span {
    font-size: 11px;
}

/* =========================================================
   BANNER INFO
   ========================================================= */

.banner-file-info {
    display: flex;
    align-items: center;
    gap: 9px;
    min-height: 40px;
    padding: 0 12px;
    border: 1px solid #e3eaee;
    border-radius: 9px;
    background: #fbfcfd;
    color: #718793;
    font-size: 11px;
}

.banner-file-info svg {
    flex: 0 0 auto;
}

.banner-file-info span {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

/* =========================================================
   FOOTER
   ========================================================= */

.show-actions-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding-top: 0;
}

/* =========================================================
   STATUS
   ========================================================= */

.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    min-height: 29px;
    padding: 0 10px;
    border: 1px solid transparent;
    border-radius: 999px;
    font-size: 11px;
    font-weight: 650;
    line-height: 1;
    white-space: nowrap;
}

.status-dot {
    width: 6px;
    height: 6px;
    flex: 0 0 6px;
    border-radius: 50%;
    background: currentColor;
}

.status-draft {
    border-color: #dce3e7;
    background: #f5f7f8;
    color: #74828b;
}

.status-published {
    border-color: #cfe4ef;
    background: #f0f8fc;
    color: #4c839f;
}

.status-active {
    border-color: #c7e8d9;
    background: #f0faf5;
    color: #438665;
}

.status-closed {
    border-color: #e7ddd0;
    background: #faf7f2;
    color: #927957;
}

.status-finished {
    border-color: #d8dce9;
    background: #f5f6fa;
    color: #68728d;
}

.status-inactive {
    border-color: #f0d3d7;
    background: #fff5f6;
    color: #b15c66;
}

/* =========================================================
   RESPONSIVE
   ========================================================= */

@media (max-width: 1000px) {
    .race-summary-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .race-show-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 700px) {
    .admin-page {
        gap: 16px;
    }

    .race-show-banner {
        height: 210px;
    }

    .race-show-banner-overlay {
        align-items: flex-start;
        flex-direction: column;
        padding: 20px;
    }

    .race-show-banner h2 {
        font-size: 20px;
    }

    .race-summary-grid {
        grid-template-columns: 1fr;
        gap: 12px;
    }

    .race-show-grid {
        gap: 16px;
    }

    .show-card-header {
        padding: 18px;
    }

    .show-card-body {
        padding: 18px;
    }

    .show-info-grid {
        grid-template-columns: 1fr;
    }

    .distance-show-header {
        align-items: flex-start;
        flex-direction: column;
    }

    .distance-show-meta {
        justify-content: flex-start;
    }

    .price-item {
        align-items: flex-start;
        flex-direction: column;
    }

    .price-side {
        align-items: flex-start;
    }

    .inclusion-item {
        flex-wrap: wrap;
    }

    .inclusion-status {
        margin-left: 28px;
    }

    .show-actions-footer {
        align-items: stretch;
        flex-direction: column-reverse;
    }

    .show-actions-footer .admin-btn {
        justify-content: center;
    }
}
</style>