<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import {
    ArrowLeft,
    CalendarDays,
    ArrowRight,
    ChevronDown,
    ChevronUp,
    ExternalLink,
    FileText,
    Image,
    MapPin,
    Plus,
    Save,
    Settings2,
    Trash2,
    Trophy,
} from 'lucide-vue-next';
import { ref, watch } from 'vue';

import admin from '@/routes/admin';

type TabKey =
    | 'general'
    | 'location'
    | 'registration'
    | 'distances'
    | 'results';

interface PriceForm {
    name: string;
    price: string;
    starts_at: string;
    ends_at: string;
    capacity: string;
    sort_order: number;
    is_active: boolean;
}

interface InclusionForm {
    name: string;
    description: string;
    type: string;
    included: boolean;
    sort_order: number;
}

interface CategoryForm {
    name: string;
    description: string;
    min_age: string;
    max_age: string;
    gender: string;
    sort_order: number;
    is_active: boolean;
}

interface DistanceForm {
    name: string;
    distance: string;
    unit: string;
    start_time: string;
    capacity: string;
    sort_order: number;
    is_active: boolean;
    prices: PriceForm[];
    inclusions: InclusionForm[];
    categories: CategoryForm[];
}

const activeTab = ref<TabKey>('general');

const expandedDistances = ref<number[]>([0]);

const bannerPreview = ref<string | null>(null);

const createPrice = (): PriceForm => ({
    name: '',
    price: '',
    starts_at: '',
    ends_at: '',
    capacity: '',
    sort_order: 0,
    is_active: true,
});

const createInclusion = (): InclusionForm => ({
    name: '',
    description: '',
    type: 'benefit',
    included: true,
    sort_order: 0,
});

const createCategory = (): CategoryForm => ({
    name: '',
    description: '',
    min_age: '',
    max_age: '',
    gender: 'mixed',
    sort_order: 0,
    is_active: true,
});

const createDistance = (): DistanceForm => ({
    name: '',
    distance: '',
    unit: 'km',
    start_time: '',
    capacity: '',
    sort_order: 0,
    is_active: true,
    prices: [createPrice()],
    inclusions: [createInclusion()],
    categories: [createCategory()],
});

const slugify = (value: string): string => {
    return value
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .toLowerCase()
        .trim()
        .replace(/[^a-z0-9\s-]/g, '')
        .replace(/\s+/g, '-')
        .replace(/-+/g, '-')
        .replace(/^-|-$/g, '');
};

const form = useForm({
    name: '',
    slug: '',
    results_url: '',
    description: '',
    event_date: '',
    start_time: '',
    end_time: '',
    location: '',
    address: '',
    city: '',
    state: '',
    country: 'México',
    banner: null as File | null,
    registration_opens_at: '',
    registration_closes_at: '',
    status: 'draft',
    terms_and_conditions: '',
    notes: '',
    distances: [createDistance()] as DistanceForm[],
});

watch(
    () => form.name,
    (name) => {
        form.slug = slugify(name);
    },
);

/*
|--------------------------------------------------------------------------
| Errors
|--------------------------------------------------------------------------
*/

const getError = (key: string): string | undefined => {
    return (form.errors as Record<string, string>)[key];
};

const hasError = (key: string): boolean => {
    return Boolean(getError(key));
};

const getFieldClass = (key: string): string => {
    return hasError(key)
        ? 'admin-form-input has-error'
        : 'admin-form-input';
};

/*
|--------------------------------------------------------------------------
| Tab errors
|--------------------------------------------------------------------------
*/

const hasGeneralErrors = (): boolean => {
    return [
        'name',
        'slug',
        'description',
        'banner',
        'status',
    ].some(hasError);
};

const hasLocationErrors = (): boolean => {
    return [
        'event_date',
        'start_time',
        'end_time',
        'location',
        'address',
        'city',
        'state',
        'country',
    ].some(hasError);
};

const hasRegistrationErrors = (): boolean => {
    return [
        'registration_opens_at',
        'registration_closes_at',
        'terms_and_conditions',
        'notes',
    ].some(hasError);
};

const hasDistanceErrors = (): boolean => {
    return Object.keys(form.errors).some((key) =>
        key.startsWith('distances.'),
    );
};

const hasResultsErrors = (): boolean => {
    return hasError('results_url');
};

/*
|--------------------------------------------------------------------------
| Validation navigation
|--------------------------------------------------------------------------
*/

const focusFirstError = (
    errors: Record<string, string>,
): void => {
    const keys = Object.keys(errors);

    if (!keys.length) {
        return;
    }

    const firstError = keys[0];

    if (firstError.startsWith('distances.')) {
        activeTab.value = 'distances';

        const match = firstError.match(
            /^distances\.(\d+)\./,
        );

        if (match) {
            const distanceIndex = Number(match[1]);

            if (
                !expandedDistances.value.includes(
                    distanceIndex,
                )
            ) {
                expandedDistances.value.push(
                    distanceIndex,
                );
            }
        }

        return;
    }

    if (firstError === 'results_url') {
        activeTab.value = 'results';
        return;
    }

    const locationFields = [
        'event_date',
        'start_time',
        'end_time',
        'location',
        'address',
        'city',
        'state',
        'country',
    ];

    if (locationFields.includes(firstError)) {
        activeTab.value = 'location';
        return;
    }

    const registrationFields = [
        'registration_opens_at',
        'registration_closes_at',
        'terms_and_conditions',
        'notes',
    ];

    if (registrationFields.includes(firstError)) {
        activeTab.value = 'registration';
        return;
    }

    activeTab.value = 'general';
};

/*
|--------------------------------------------------------------------------
| Banner
|--------------------------------------------------------------------------
*/

const selectBanner = (event: Event): void => {
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0];

    if (!file) {
        return;
    }

    if (!file.type.startsWith('image/')) {
        target.value = '';
        return;
    }

    form.banner = file;

    if (bannerPreview.value) {
        URL.revokeObjectURL(bannerPreview.value);
    }

    bannerPreview.value = URL.createObjectURL(file);

    target.value = '';
};

const removeBanner = (): void => {
    if (bannerPreview.value) {
        URL.revokeObjectURL(bannerPreview.value);
    }

    bannerPreview.value = null;
    form.banner = null;
};

const changeBanner = (): void => {
    document
        .getElementById('race-banner-input')
        ?.click();
};

/*
|--------------------------------------------------------------------------
| Distances
|--------------------------------------------------------------------------
*/

const addDistance = (): void => {
    form.distances.push(createDistance());

    expandedDistances.value.push(
        form.distances.length - 1,
    );
};

const removeDistance = (index: number): void => {
    if (form.distances.length <= 1) {
        return;
    }

    form.distances.splice(index, 1);

    expandedDistances.value =
        expandedDistances.value
            .filter((item) => item !== index)
            .map((item) =>
                item > index ? item - 1 : item,
            );
};

const toggleDistance = (index: number): void => {
    if (
        expandedDistances.value.includes(index)
    ) {
        expandedDistances.value =
            expandedDistances.value.filter(
                (item) => item !== index,
            );

        return;
    }

    expandedDistances.value.push(index);
};

/*
|--------------------------------------------------------------------------
| Prices
|--------------------------------------------------------------------------
*/

const addPrice = (distanceIndex: number): void => {
    form.distances[distanceIndex].prices.push(
        createPrice(),
    );
};

const removePrice = (
    distanceIndex: number,
    priceIndex: number,
): void => {
    const prices =
        form.distances[distanceIndex].prices;

    if (prices.length <= 1) {
        return;
    }

    prices.splice(priceIndex, 1);
};

/*
|--------------------------------------------------------------------------
| Inclusions
|--------------------------------------------------------------------------
*/

const addInclusion = (
    distanceIndex: number,
): void => {
    form.distances[distanceIndex].inclusions.push(
        createInclusion(),
    );
};

const removeInclusion = (
    distanceIndex: number,
    inclusionIndex: number,
): void => {
    const inclusions =
        form.distances[distanceIndex].inclusions;

    if (inclusions.length <= 1) {
        return;
    }

    inclusions.splice(inclusionIndex, 1);
};

/*
|--------------------------------------------------------------------------
| Categories
|--------------------------------------------------------------------------
*/

const addCategory = (
    distanceIndex: number,
): void => {
    form.distances[distanceIndex].categories.push(
        createCategory(),
    );
};

const removeCategory = (
    distanceIndex: number,
    categoryIndex: number,
): void => {
    const categories =
        form.distances[distanceIndex].categories;

    if (categories.length <= 1) {
        return;
    }

    categories.splice(categoryIndex, 1);
};

/*
|--------------------------------------------------------------------------
| Tabs
|--------------------------------------------------------------------------
*/

const tabs: TabKey[] = [
    'general',
    'location',
    'registration',
    'distances',
    'results',
];

const nextTab = (): void => {
    const currentIndex =
        tabs.indexOf(activeTab.value);

    if (currentIndex < tabs.length - 1) {
        activeTab.value =
            tabs[currentIndex + 1];
    }
};

const previousTab = (): void => {
    const currentIndex =
        tabs.indexOf(activeTab.value);

    if (currentIndex > 0) {
        activeTab.value =
            tabs[currentIndex - 1];
    }
};

/*
|--------------------------------------------------------------------------
| Submit
|--------------------------------------------------------------------------
*/

const submit = (): void => {
    form.post(admin.races.store().url, {
        preserveScroll: true,
        forceFormData: true,

        onError: (errors) => {
            focusFirstError(
                errors as Record<string, string>,
            );
        },
    });
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
                title: 'Nueva carrera',
                href: admin.races.create(),
            },
        ],
    },
});
</script>

<template>
    <Head title="Nueva carrera" />

    <div class="admin-page">
        <!-- =====================================================
             HEADER
             ===================================================== -->

        <div class="admin-page-header">
            <div>
                <p class="admin-page-eyebrow">
                    Carreras
                </p>

                <h1 class="admin-page-title">
                    Nueva carrera
                </h1>

                <p class="admin-page-subtitle">
                    Configura la información, ubicación,
                    inscripciones y distancias de la carrera.
                </p>
            </div>

            <div class="admin-page-header-actions">
                <Link
                    :href="admin.races.index().url"
                    class="admin-btn admin-btn-secondary"
                >
                    <ArrowLeft :size="15" />
                    Regresar
                </Link>
            </div>
        </div>

        <!-- =====================================================
             FORM CARD
             ===================================================== -->

        <div class="admin-form-card">
            <!-- =================================================
                 TABS
                 ================================================= -->

            <div class="race-tabs-wrapper">
                <nav class="race-tabs">
                    <button
                        type="button"
                        class="race-tab"
                        :class="{
                            'race-tab-active':
                                activeTab === 'general',
                        }"
                        @click="activeTab = 'general'"
                    >
                        <FileText :size="16" />

                        <span>
                            General
                        </span>

                        <span
                            v-if="hasGeneralErrors()"
                            class="race-tab-error"
                        ></span>
                    </button>

                    <button
                        type="button"
                        class="race-tab"
                        :class="{
                            'race-tab-active':
                                activeTab === 'location',
                        }"
                        @click="activeTab = 'location'"
                    >
                        <MapPin :size="16" />

                        <span>
                            Fecha y lugar
                        </span>

                        <span
                            v-if="hasLocationErrors()"
                            class="race-tab-error"
                        ></span>
                    </button>

                    <button
                        type="button"
                        class="race-tab"
                        :class="{
                            'race-tab-active':
                                activeTab === 'registration',
                        }"
                        @click="activeTab = 'registration'"
                    >
                        <Settings2 :size="16" />

                        <span>
                            Inscripciones
                        </span>

                        <span
                            v-if="hasRegistrationErrors()"
                            class="race-tab-error"
                        ></span>
                    </button>

                    <button
                        type="button"
                        class="race-tab"
                        :class="{
                            'race-tab-active':
                                activeTab === 'distances',
                        }"
                        @click="activeTab = 'distances'"
                    >
                        <Trophy :size="16" />

                        <span>
                            Distancias
                        </span>

                        <span
                            v-if="hasDistanceErrors()"
                            class="race-tab-error"
                        ></span>
                    </button>

                    <button
                        type="button"
                        class="race-tab"
                        :class="{
                            'race-tab-active':
                                activeTab === 'results',
                        }"
                        @click="activeTab = 'results'"
                    >
                        <ExternalLink :size="16" />

                        <span>
                            Resultados
                        </span>

                        <span
                            v-if="hasResultsErrors()"
                            class="race-tab-error"
                        ></span>
                    </button>
                </nav>
            </div>

            <!-- =================================================
                 FORM
                 ================================================= -->

            <form
                class="admin-form"
                @submit.prevent="submit"
            >
                <!-- =================================================
                     GENERAL
                     ================================================= -->

                <div
                    v-if="activeTab === 'general'"
                    class="race-tab-content"
                >
                    <div class="race-section-header">
                        <div>
                            <h2>
                                Información general
                            </h2>

                            <p>
                                Información principal de la carrera.
                            </p>
                        </div>
                    </div>

                    <div class="admin-form-grid">
                        <div class="admin-form-group">
                            <label
                                for="name"
                                class="admin-form-label"
                            >
                                Nombre
                                <span>*</span>
                            </label>

                            <input
                                id="name"
                                v-model="form.name"
                                type="text"
                                :class="getFieldClass('name')"
                                placeholder="Ej. Sonríe Corriendo 2027"
                            />

                            <p
                                v-if="hasError('name')"
                                class="admin-form-error"
                            >
                                {{ getError('name') }}
                            </p>
                        </div>

                        <div class="admin-form-group">
                            <label
                                for="slug"
                                class="admin-form-label"
                            >
                                Slug
                                <span>*</span>
                            </label>

                            <input
                                id="slug"
                                :value="form.slug"
                                type="text"
                                :class="getFieldClass('slug')"
                                placeholder="sonrie-corriendo-2027"
                                readonly
                            />

                            <p class="admin-form-help">
                                Se genera automáticamente a partir del nombre
                                de la carrera.
                            </p>

                            <p
                                v-if="hasError('slug')"
                                class="admin-form-error"
                            >
                                {{ getError('slug') }}
                            </p>
                        </div>

                        <div
                            class="admin-form-group admin-form-group-full"
                        >
                            <label
                                for="description"
                                class="admin-form-label"
                            >
                                Descripción
                            </label>

                            <textarea
                                id="description"
                                v-model="form.description"
                                rows="4"
                                class="admin-form-input admin-form-textarea"
                                :class="{
                                    'has-error':
                                        hasError('description'),
                                }"
                                placeholder="Describe brevemente la carrera..."
                            ></textarea>

                            <p
                                v-if="hasError('description')"
                                class="admin-form-error"
                            >
                                {{ getError('description') }}
                            </p>
                        </div>

                        <!-- BANNER -->

                        <div
                            class="admin-form-group admin-form-group-full"
                        >
                            <label class="admin-form-label">
                                Banner
                            </label>

                            <input
                                id="race-banner-input"
                                type="file"
                                accept="image/jpeg,image/png,image/webp"
                                class="race-hidden-file-input"
                                @change="selectBanner"
                            />

                            <div
                                v-if="!bannerPreview"
                                class="race-banner-upload"
                                @click="changeBanner"
                            >
                                <div class="race-banner-upload-icon">
                                    <Image :size="22" />
                                </div>

                                <div class="race-banner-upload-content">
                                    <strong>
                                        Selecciona el banner de la carrera
                                    </strong>

                                    <p>
                                        Puedes cargar una imagen desde tu
                                        computadora.
                                    </p>

                                    <span>
                                        JPG, PNG o WEBP · Recomendado
                                        1600 × 600 px · Máximo 5 MB
                                    </span>
                                </div>

                                <button
                                    type="button"
                                    class="admin-btn admin-btn-secondary race-banner-button"
                                    @click.stop="changeBanner"
                                >
                                    Seleccionar
                                </button>
                            </div>

                            <div
                                v-else
                                class="race-banner-preview"
                            >
                                <img
                                    :src="bannerPreview"
                                    alt="Vista previa del banner"
                                />

                                <div class="race-banner-preview-footer">
                                    <div>
                                        <strong>
                                            Banner seleccionado
                                        </strong>

                                        <span>
                                            La imagen se cargará al guardar
                                            la carrera.
                                        </span>
                                    </div>

                                    <div class="race-banner-actions">
                                        <button
                                            type="button"
                                            class="admin-btn admin-btn-secondary"
                                            @click="changeBanner"
                                        >
                                            <Image :size="14" />
                                            Cambiar
                                        </button>

                                        <button
                                            type="button"
                                            class="admin-btn admin-btn-secondary race-danger-button"
                                            @click="removeBanner"
                                        >
                                            <Trash2 :size="14" />
                                            Quitar
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <p
                                v-if="hasError('banner')"
                                class="admin-form-error"
                            >
                                {{ getError('banner') }}
                            </p>
                        </div>

                        <!-- STATUS -->

                        <div
                            class="admin-form-group admin-form-group-full"
                        >
                            <div class="admin-form-status">
                                <div class="admin-form-status-content">
                                    <div class="admin-form-status-icon">
                                        <span></span>
                                    </div>

                                    <div>
                                        <p class="admin-form-status-title">
                                            Estado de la carrera
                                        </p>

                                        <p
                                            class="admin-form-status-description"
                                        >
                                            Define cómo aparecerá y operará
                                            actualmente la carrera.
                                        </p>
                                    </div>
                                </div>

                                <select
                                    v-model="form.status"
                                    :class="getFieldClass('status')"
                                >
                                    <option value="draft">
                                        Borrador
                                    </option>

                                    <option value="published">
                                        Publicada
                                    </option>

                                    <option value="registration_open">
                                        Inscripciones abiertas
                                    </option>

                                    <option value="registration_closed">
                                        Inscripciones cerradas
                                    </option>

                                    <option value="finished">
                                        Finalizada
                                    </option>

                                    <option value="cancelled">
                                        Cancelada
                                    </option>
                                </select>
                            </div>

                            <p
                                v-if="hasError('status')"
                                class="admin-form-error"
                            >
                                {{ getError('status') }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- =================================================
                     LOCATION
                     ================================================= -->

                <div
                    v-if="activeTab === 'location'"
                    class="race-tab-content"
                >
                    <div class="race-section-header">
                        <div>
                            <h2>
                                Fecha y lugar
                            </h2>

                            <p>
                                Define cuándo y dónde se realizará la carrera.
                            </p>
                        </div>
                    </div>

                    <div class="admin-form-grid">
                        <div class="admin-form-group">
                            <label
                                for="event_date"
                                class="admin-form-label"
                            >
                                Fecha del evento
                                <span>*</span>
                            </label>

                            <div class="admin-input-icon-wrapper">
                                <CalendarDays
                                    :size="15"
                                    class="admin-input-icon"
                                />

                                <input
                                    id="event_date"
                                    v-model="form.event_date"
                                    type="date"
                                    :class="getFieldClass('event_date')"
                                />
                            </div>

                            <p
                                v-if="hasError('event_date')"
                                class="admin-form-error"
                            >
                                {{ getError('event_date') }}
                            </p>
                        </div>

                        <div class="admin-form-group">
                            <label
                                for="start_time"
                                class="admin-form-label"
                            >
                                Hora de inicio
                            </label>

                            <input
                                id="start_time"
                                v-model="form.start_time"
                                type="time"
                                :class="getFieldClass('start_time')"
                            />

                            <p
                                v-if="hasError('start_time')"
                                class="admin-form-error"
                            >
                                {{ getError('start_time') }}
                            </p>
                        </div>

                        <div class="admin-form-group">
                            <label
                                for="end_time"
                                class="admin-form-label"
                            >
                                Hora de finalización
                            </label>

                            <input
                                id="end_time"
                                v-model="form.end_time"
                                type="time"
                                :class="getFieldClass('end_time')"
                            />

                            <p
                                v-if="hasError('end_time')"
                                class="admin-form-error"
                            >
                                {{ getError('end_time') }}
                            </p>
                        </div>

                        <div class="admin-form-group">
                            <label
                                for="location"
                                class="admin-form-label"
                            >
                                Lugar
                            </label>

                            <input
                                id="location"
                                v-model="form.location"
                                type="text"
                                :class="getFieldClass('location')"
                                placeholder="Ej. Parque Guadiana"
                            />

                            <p
                                v-if="hasError('location')"
                                class="admin-form-error"
                            >
                                {{ getError('location') }}
                            </p>
                        </div>

                        <div
                            class="admin-form-group admin-form-group-full"
                        >
                            <label
                                for="address"
                                class="admin-form-label"
                            >
                                Dirección
                            </label>

                            <input
                                id="address"
                                v-model="form.address"
                                type="text"
                                :class="getFieldClass('address')"
                                placeholder="Dirección del evento"
                            />

                            <p
                                v-if="hasError('address')"
                                class="admin-form-error"
                            >
                                {{ getError('address') }}
                            </p>
                        </div>

                        <div class="admin-form-group">
                            <label
                                for="city"
                                class="admin-form-label"
                            >
                                Ciudad
                            </label>

                            <input
                                id="city"
                                v-model="form.city"
                                type="text"
                                :class="getFieldClass('city')"
                                placeholder="Durango"
                            />

                            <p
                                v-if="hasError('city')"
                                class="admin-form-error"
                            >
                                {{ getError('city') }}
                            </p>
                        </div>

                        <div class="admin-form-group">
                            <label
                                for="state"
                                class="admin-form-label"
                            >
                                Estado
                            </label>

                            <input
                                id="state"
                                v-model="form.state"
                                type="text"
                                :class="getFieldClass('state')"
                                placeholder="Durango"
                            />

                            <p
                                v-if="hasError('state')"
                                class="admin-form-error"
                            >
                                {{ getError('state') }}
                            </p>
                        </div>

                        <div class="admin-form-group">
                            <label
                                for="country"
                                class="admin-form-label"
                            >
                                País
                            </label>

                            <input
                                id="country"
                                v-model="form.country"
                                type="text"
                                :class="getFieldClass('country')"
                                placeholder="México"
                            />

                            <p
                                v-if="hasError('country')"
                                class="admin-form-error"
                            >
                                {{ getError('country') }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- =================================================
                     REGISTRATION
                     ================================================= -->

                <div
                    v-if="activeTab === 'registration'"
                    class="race-tab-content"
                >
                    <div class="race-section-header">
                        <div>
                            <h2>
                                Inscripciones
                            </h2>

                            <p>
                                Configura el periodo de inscripción y las
                                condiciones de participación.
                            </p>
                        </div>
                    </div>

                    <div class="admin-form-grid">
                        <div class="admin-form-group">
                            <label
                                for="registration_opens_at"
                                class="admin-form-label"
                            >
                                Apertura de inscripciones
                            </label>

                            <input
                                id="registration_opens_at"
                                v-model="form.registration_opens_at"
                                type="datetime-local"
                                :class="
                                    getFieldClass(
                                        'registration_opens_at',
                                    )
                                "
                            />

                            <p
                                v-if="
                                    hasError(
                                        'registration_opens_at',
                                    )
                                "
                                class="admin-form-error"
                            >
                                {{
                                    getError(
                                        'registration_opens_at',
                                    )
                                }}
                            </p>
                        </div>

                        <div class="admin-form-group">
                            <label
                                for="registration_closes_at"
                                class="admin-form-label"
                            >
                                Cierre de inscripciones
                            </label>

                            <input
                                id="registration_closes_at"
                                v-model="form.registration_closes_at"
                                type="datetime-local"
                                :class="
                                    getFieldClass(
                                        'registration_closes_at',
                                    )
                                "
                            />

                            <p
                                v-if="
                                    hasError(
                                        'registration_closes_at',
                                    )
                                "
                                class="admin-form-error"
                            >
                                {{
                                    getError(
                                        'registration_closes_at',
                                    )
                                }}
                            </p>
                        </div>

                        <div
                            class="admin-form-group admin-form-group-full"
                        >
                            <label
                                for="terms_and_conditions"
                                class="admin-form-label"
                            >
                                Términos y condiciones
                            </label>

                            <textarea
                                id="terms_and_conditions"
                                v-model="form.terms_and_conditions"
                                rows="7"
                                class="admin-form-input admin-form-textarea"
                                :class="{
                                    'has-error':
                                        hasError(
                                            'terms_and_conditions',
                                        ),
                                }"
                                placeholder="Escribe los términos y condiciones de participación..."
                            ></textarea>

                            <p
                                v-if="
                                    hasError(
                                        'terms_and_conditions',
                                    )
                                "
                                class="admin-form-error"
                            >
                                {{
                                    getError(
                                        'terms_and_conditions',
                                    )
                                }}
                            </p>
                        </div>

                        <div
                            class="admin-form-group admin-form-group-full"
                        >
                            <label
                                for="notes"
                                class="admin-form-label"
                            >
                                Notas internas
                            </label>

                            <textarea
                                id="notes"
                                v-model="form.notes"
                                rows="5"
                                class="admin-form-input admin-form-textarea"
                                :class="{
                                    'has-error':
                                        hasError('notes'),
                                }"
                                placeholder="Notas para administración..."
                            ></textarea>

                            <p
                                v-if="hasError('notes')"
                                class="admin-form-error"
                            >
                                {{ getError('notes') }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- =================================================
                     DISTANCES
                     ================================================= -->

                <div
                    v-if="activeTab === 'distances'"
                    class="race-tab-content"
                >
                    <div class="race-section-header">
                        <div>
                            <h2>
                                Distancias
                            </h2>

                            <p>
                                Configura las distancias, precios,
                                beneficios y categorías.
                            </p>
                        </div>
                    </div>

                    <div class="race-distances-list">
                        <div
                            v-for="(
                                distance,
                                distanceIndex
                            ) in form.distances"
                            :key="distanceIndex"
                            class="race-distance-card"
                        >
                            <div class="race-distance-header">
                                <button
                                    type="button"
                                    class="race-distance-toggle"
                                    @click="
                                        toggleDistance(
                                            distanceIndex,
                                        )
                                    "
                                >
                                    <div class="race-distance-main">
                                        <span
                                            class="race-distance-number"
                                        >
                                            {{
                                                String(
                                                    distanceIndex + 1,
                                                ).padStart(2, '0')
                                            }}
                                        </span>

                                        <div
                                            class="race-distance-heading"
                                        >
                                            <strong>
                                                {{
                                                    distance.name ||
                                                    `Distancia ${
                                                        distanceIndex + 1
                                                    }`
                                                }}
                                            </strong>

                                            <span>
                                                {{
                                                    distance.distance ||
                                                    '0'
                                                }}
                                                {{
                                                    distance.unit ||
                                                    'km'
                                                }}
                                            </span>
                                        </div>
                                    </div>

                                    <div class="race-distance-toggle-icon">
                                        <ChevronUp
                                            v-if="
                                                expandedDistances.includes(
                                                    distanceIndex,
                                                )
                                            "
                                            :size="17"
                                        />

                                        <ChevronDown
                                            v-else
                                            :size="17"
                                        />
                                    </div>
                                </button>

                                <button
                                    v-if="
                                        form.distances.length > 1
                                    "
                                    type="button"
                                    class="race-distance-delete"
                                    title="Eliminar distancia"
                                    @click="
                                        removeDistance(
                                            distanceIndex,
                                        )
                                    "
                                >
                                    <Trash2 :size="15" />
                                </button>
                            </div>

                            <div
                                v-if="
                                    expandedDistances.includes(
                                        distanceIndex,
                                    )
                                "
                                class="race-distance-body"
                            >
                                <!-- BASIC -->

                                <div class="race-subsection">
                                    <div
                                        class="race-subsection-heading"
                                    >
                                        <h3>
                                            Información de la distancia
                                        </h3>

                                        <p>
                                            Define los datos básicos de esta
                                            modalidad.
                                        </p>
                                    </div>

                                    <div class="admin-form-grid">
                                        <div class="admin-form-group">
                                            <label
                                                class="admin-form-label"
                                            >
                                                Nombre
                                                <span>*</span>
                                            </label>

                                            <input
                                                v-model="distance.name"
                                                type="text"
                                                :class="
                                                    getFieldClass(
                                                        `distances.${distanceIndex}.name`,
                                                    )
                                                "
                                                placeholder="Ej. 5K"
                                            />

                                            <p
                                                v-if="
                                                    hasError(
                                                        `distances.${distanceIndex}.name`,
                                                    )
                                                "
                                                class="admin-form-error"
                                            >
                                                {{
                                                    getError(
                                                        `distances.${distanceIndex}.name`,
                                                    )
                                                }}
                                            </p>
                                        </div>

                                        <div class="admin-form-group">
                                            <label
                                                class="admin-form-label"
                                            >
                                                Distancia
                                                <span>*</span>
                                            </label>

                                            <input
                                                v-model="distance.distance"
                                                type="number"
                                                min="0"
                                                step="0.01"
                                                :class="
                                                    getFieldClass(
                                                        `distances.${distanceIndex}.distance`,
                                                    )
                                                "
                                                placeholder="5"
                                            />

                                            <p
                                                v-if="
                                                    hasError(
                                                        `distances.${distanceIndex}.distance`,
                                                    )
                                                "
                                                class="admin-form-error"
                                            >
                                                {{
                                                    getError(
                                                        `distances.${distanceIndex}.distance`,
                                                    )
                                                }}
                                            </p>
                                        </div>

                                        <div class="admin-form-group">
                                            <label
                                                class="admin-form-label"
                                            >
                                                Unidad
                                                <span>*</span>
                                            </label>

                                            <select
                                                v-model="distance.unit"
                                                :class="
                                                    getFieldClass(
                                                        `distances.${distanceIndex}.unit`,
                                                    )
                                                "
                                            >
                                                <option value="km">
                                                    Kilómetros
                                                </option>

                                                <option value="mi">
                                                    Millas
                                                </option>
                                            </select>

                                            <p
                                                v-if="
                                                    hasError(
                                                        `distances.${distanceIndex}.unit`,
                                                    )
                                                "
                                                class="admin-form-error"
                                            >
                                                {{
                                                    getError(
                                                        `distances.${distanceIndex}.unit`,
                                                    )
                                                }}
                                            </p>
                                        </div>

                                        <div class="admin-form-group">
                                            <label
                                                class="admin-form-label"
                                            >
                                                Hora de salida
                                            </label>

                                            <input
                                                v-model="
                                                    distance.start_time
                                                "
                                                type="time"
                                                :class="
                                                    getFieldClass(
                                                        `distances.${distanceIndex}.start_time`,
                                                    )
                                                "
                                            />

                                            <p
                                                v-if="
                                                    hasError(
                                                        `distances.${distanceIndex}.start_time`,
                                                    )
                                                "
                                                class="admin-form-error"
                                            >
                                                {{
                                                    getError(
                                                        `distances.${distanceIndex}.start_time`,
                                                    )
                                                }}
                                            </p>
                                        </div>

                                        <div class="admin-form-group">
                                            <label
                                                class="admin-form-label"
                                            >
                                                Capacidad
                                            </label>

                                            <input
                                                v-model="distance.capacity"
                                                type="number"
                                                min="0"
                                                :class="
                                                    getFieldClass(
                                                        `distances.${distanceIndex}.capacity`,
                                                    )
                                                "
                                                placeholder="Sin límite"
                                            />

                                            <p
                                                v-if="
                                                    hasError(
                                                        `distances.${distanceIndex}.capacity`,
                                                    )
                                                "
                                                class="admin-form-error"
                                            >
                                                {{
                                                    getError(
                                                        `distances.${distanceIndex}.capacity`,
                                                    )
                                                }}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- PRICES -->

                                <div class="race-subsection">
                                    <div
                                        class="race-dynamic-section-header"
                                    >
                                        <div
                                            class="race-subsection-heading"
                                        >
                                            <h3>
                                                Precios
                                            </h3>

                                            <p>
                                                Define las etapas de precio
                                                para esta distancia.
                                            </p>
                                        </div>

                                        <button
                                            type="button"
                                            class="admin-btn admin-btn-secondary race-add-button"
                                            @click="
                                                addPrice(
                                                    distanceIndex,
                                                )
                                            "
                                        >
                                            <Plus :size="14" />
                                            Agregar precio
                                        </button>
                                    </div>

                                    <div class="race-dynamic-list">
                                        <div
                                            v-for="(
                                                price,
                                                priceIndex
                                            ) in distance.prices"
                                            :key="priceIndex"
                                            class="race-dynamic-item"
                                        >
                                            <div
                                                class="race-dynamic-item-header"
                                            >
                                                <span>
                                                    Etapa
                                                    {{
                                                        priceIndex + 1
                                                    }}
                                                </span>

                                                <button
                                                    v-if="
                                                        distance.prices
                                                            .length > 1
                                                    "
                                                    type="button"
                                                    class="race-item-delete"
                                                    title="Eliminar precio"
                                                    @click="
                                                        removePrice(
                                                            distanceIndex,
                                                            priceIndex,
                                                        )
                                                    "
                                                >
                                                    <Trash2
                                                        :size="14"
                                                    />
                                                </button>
                                            </div>

                                            <div
                                                class="admin-form-grid"
                                            >
                                                <div
                                                    class="admin-form-group"
                                                >
                                                    <label
                                                        class="admin-form-label"
                                                    >
                                                        Nombre
                                                        <span>*</span>
                                                    </label>

                                                    <input
                                                        v-model="
                                                            price.name
                                                        "
                                                        type="text"
                                                        :class="
                                                            getFieldClass(
                                                                `distances.${distanceIndex}.prices.${priceIndex}.name`,
                                                            )
                                                        "
                                                        placeholder="Ej. Preventa"
                                                    />

                                                    <p
                                                        v-if="
                                                            hasError(
                                                                `distances.${distanceIndex}.prices.${priceIndex}.name`,
                                                            )
                                                        "
                                                        class="admin-form-error"
                                                    >
                                                        {{
                                                            getError(
                                                                `distances.${distanceIndex}.prices.${priceIndex}.name`,
                                                            )
                                                        }}
                                                    </p>
                                                </div>

                                                <div
                                                    class="admin-form-group"
                                                >
                                                    <label
                                                        class="admin-form-label"
                                                    >
                                                        Precio
                                                        <span>*</span>
                                                    </label>

                                                    <input
                                                        v-model="
                                                            price.price
                                                        "
                                                        type="number"
                                                        min="0"
                                                        step="0.01"
                                                        :class="
                                                            getFieldClass(
                                                                `distances.${distanceIndex}.prices.${priceIndex}.price`,
                                                            )
                                                        "
                                                        placeholder="359.00"
                                                    />

                                                    <p
                                                        v-if="
                                                            hasError(
                                                                `distances.${distanceIndex}.prices.${priceIndex}.price`,
                                                            )
                                                        "
                                                        class="admin-form-error"
                                                    >
                                                        {{
                                                            getError(
                                                                `distances.${distanceIndex}.prices.${priceIndex}.price`,
                                                            )
                                                        }}
                                                    </p>
                                                </div>

                                                <div
                                                    class="admin-form-group"
                                                >
                                                    <label
                                                        class="admin-form-label"
                                                    >
                                                        Inicia
                                                    </label>

                                                    <input
                                                        v-model="
                                                            price.starts_at
                                                        "
                                                        type="datetime-local"
                                                        :class="
                                                            getFieldClass(
                                                                `distances.${distanceIndex}.prices.${priceIndex}.starts_at`,
                                                            )
                                                        "
                                                    />

                                                    <p
                                                        v-if="
                                                            hasError(
                                                                `distances.${distanceIndex}.prices.${priceIndex}.starts_at`,
                                                            )
                                                        "
                                                        class="admin-form-error"
                                                    >
                                                        {{
                                                            getError(
                                                                `distances.${distanceIndex}.prices.${priceIndex}.starts_at`,
                                                            )
                                                        }}
                                                    </p>
                                                </div>

                                                <div
                                                    class="admin-form-group"
                                                >
                                                    <label
                                                        class="admin-form-label"
                                                    >
                                                        Termina
                                                    </label>

                                                    <input
                                                        v-model="
                                                            price.ends_at
                                                        "
                                                        type="datetime-local"
                                                        :class="
                                                            getFieldClass(
                                                                `distances.${distanceIndex}.prices.${priceIndex}.ends_at`,
                                                            )
                                                        "
                                                    />

                                                    <p
                                                        v-if="
                                                            hasError(
                                                                `distances.${distanceIndex}.prices.${priceIndex}.ends_at`,
                                                            )
                                                        "
                                                        class="admin-form-error"
                                                    >
                                                        {{
                                                            getError(
                                                                `distances.${distanceIndex}.prices.${priceIndex}.ends_at`,
                                                            )
                                                        }}
                                                    </p>
                                                </div>

                                                <div
                                                    class="admin-form-group"
                                                >
                                                    <label
                                                        class="admin-form-label"
                                                    >
                                                        Capacidad
                                                    </label>

                                                    <input
                                                        v-model="
                                                            price.capacity
                                                        "
                                                        type="number"
                                                        min="0"
                                                        :class="
                                                            getFieldClass(
                                                                `distances.${distanceIndex}.prices.${priceIndex}.capacity`,
                                                            )
                                                        "
                                                        placeholder="Sin límite"
                                                    />

                                                    <p
                                                        v-if="
                                                            hasError(
                                                                `distances.${distanceIndex}.prices.${priceIndex}.capacity`,
                                                            )
                                                        "
                                                        class="admin-form-error"
                                                    >
                                                        {{
                                                            getError(
                                                                `distances.${distanceIndex}.prices.${priceIndex}.capacity`,
                                                            )
                                                        }}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- INCLUSIONS -->

                                <div class="race-subsection">
                                    <div
                                        class="race-dynamic-section-header"
                                    >
                                        <div
                                            class="race-subsection-heading"
                                        >
                                            <h3>
                                                Incluye
                                            </h3>

                                            <p>
                                                Beneficios y artículos incluidos
                                                en la inscripción.
                                            </p>
                                        </div>

                                        <button
                                            type="button"
                                            class="admin-btn admin-btn-secondary race-add-button"
                                            @click="
                                                addInclusion(
                                                    distanceIndex,
                                                )
                                            "
                                        >
                                            <Plus :size="14" />
                                            Agregar incluido
                                        </button>
                                    </div>

                                    <div class="race-dynamic-list">
                                        <div
                                            v-for="(
                                                inclusion,
                                                inclusionIndex
                                            ) in distance.inclusions"
                                            :key="inclusionIndex"
                                            class="race-dynamic-item"
                                        >
                                            <div
                                                class="race-dynamic-item-header"
                                            >
                                                <span>
                                                    Incluido
                                                    {{
                                                        inclusionIndex + 1
                                                    }}
                                                </span>

                                                <button
                                                    v-if="
                                                        distance.inclusions
                                                            .length > 1
                                                    "
                                                    type="button"
                                                    class="race-item-delete"
                                                    title="Eliminar incluido"
                                                    @click="
                                                        removeInclusion(
                                                            distanceIndex,
                                                            inclusionIndex,
                                                        )
                                                    "
                                                >
                                                    <Trash2
                                                        :size="14"
                                                    />
                                                </button>
                                            </div>

                                            <div
                                                class="admin-form-grid"
                                            >
                                                <div
                                                    class="admin-form-group"
                                                >
                                                    <label
                                                        class="admin-form-label"
                                                    >
                                                        Nombre
                                                        <span>*</span>
                                                    </label>

                                                    <input
                                                        v-model="
                                                            inclusion.name
                                                        "
                                                        type="text"
                                                        :class="
                                                            getFieldClass(
                                                                `distances.${distanceIndex}.inclusions.${inclusionIndex}.name`,
                                                            )
                                                        "
                                                        placeholder="Ej. Medalla finisher"
                                                    />

                                                    <p
                                                        v-if="
                                                            hasError(
                                                                `distances.${distanceIndex}.inclusions.${inclusionIndex}.name`,
                                                            )
                                                        "
                                                        class="admin-form-error"
                                                    >
                                                        {{
                                                            getError(
                                                                `distances.${distanceIndex}.inclusions.${inclusionIndex}.name`,
                                                            )
                                                        }}
                                                    </p>
                                                </div>

                                                <div
                                                    class="admin-form-group"
                                                >
                                                    <label
                                                        class="admin-form-label"
                                                    >
                                                        Tipo
                                                    </label>

                                                    <select
                                                        v-model="
                                                            inclusion.type
                                                        "
                                                        :class="
                                                            getFieldClass(
                                                                `distances.${distanceIndex}.inclusions.${inclusionIndex}.type`,
                                                            )
                                                        "
                                                    >
                                                        <option
                                                            value="benefit"
                                                        >
                                                            Beneficio
                                                        </option>

                                                        <option
                                                            value="product"
                                                        >
                                                            Producto
                                                        </option>

                                                        <option
                                                            value="service"
                                                        >
                                                            Servicio
                                                        </option>
                                                    </select>

                                                    <p
                                                        v-if="
                                                            hasError(
                                                                `distances.${distanceIndex}.inclusions.${inclusionIndex}.type`,
                                                            )
                                                        "
                                                        class="admin-form-error"
                                                    >
                                                        {{
                                                            getError(
                                                                `distances.${distanceIndex}.inclusions.${inclusionIndex}.type`,
                                                            )
                                                        }}
                                                    </p>
                                                </div>

                                                <div
                                                    class="admin-form-group admin-form-group-full"
                                                >
                                                    <label
                                                        class="admin-form-label"
                                                    >
                                                        Descripción
                                                    </label>

                                                    <textarea
                                                        v-model="
                                                            inclusion.description
                                                        "
                                                        rows="3"
                                                        class="admin-form-input admin-form-textarea"
                                                        :class="{
                                                            'has-error':
                                                                hasError(
                                                                    `distances.${distanceIndex}.inclusions.${inclusionIndex}.description`,
                                                                ),
                                                        }"
                                                        placeholder="Descripción del beneficio o artículo..."
                                                    ></textarea>

                                                    <p
                                                        v-if="
                                                            hasError(
                                                                `distances.${distanceIndex}.inclusions.${inclusionIndex}.description`,
                                                            )
                                                        "
                                                        class="admin-form-error"
                                                    >
                                                        {{
                                                            getError(
                                                                `distances.${distanceIndex}.inclusions.${inclusionIndex}.description`,
                                                            )
                                                        }}
                                                    </p>
                                                </div>

                                                <div
                                                    class="admin-form-group"
                                                >
                                                    <label
                                                        class="race-checkbox-label"
                                                    >
                                                        <input
                                                            v-model="
                                                                inclusion.included
                                                            "
                                                            type="checkbox"
                                                        />

                                                        <span>
                                                            Incluido
                                                        </span>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- CATEGORIES -->

                                <div class="race-subsection">
                                    <div
                                        class="race-dynamic-section-header"
                                    >
                                        <div
                                            class="race-subsection-heading"
                                        >
                                            <h3>
                                                Categorías
                                            </h3>

                                            <p>
                                                Define las categorías
                                                disponibles para esta distancia.
                                            </p>
                                        </div>

                                        <button
                                            type="button"
                                            class="admin-btn admin-btn-secondary race-add-button"
                                            @click="
                                                addCategory(
                                                    distanceIndex,
                                                )
                                            "
                                        >
                                            <Plus :size="14" />
                                            Agregar categoría
                                        </button>
                                    </div>

                                    <div class="race-dynamic-list">
                                        <div
                                            v-for="(
                                                category,
                                                categoryIndex
                                            ) in distance.categories"
                                            :key="categoryIndex"
                                            class="race-dynamic-item"
                                        >
                                            <div
                                                class="race-dynamic-item-header"
                                            >
                                                <span>
                                                    Categoría
                                                    {{
                                                        categoryIndex + 1
                                                    }}
                                                </span>

                                                <button
                                                    v-if="
                                                        distance.categories
                                                            .length > 1
                                                    "
                                                    type="button"
                                                    class="race-item-delete"
                                                    title="Eliminar categoría"
                                                    @click="
                                                        removeCategory(
                                                            distanceIndex,
                                                            categoryIndex,
                                                        )
                                                    "
                                                >
                                                    <Trash2
                                                        :size="14"
                                                    />
                                                </button>
                                            </div>

                                            <div
                                                class="admin-form-grid"
                                            >
                                                <div
                                                    class="admin-form-group"
                                                >
                                                    <label
                                                        class="admin-form-label"
                                                    >
                                                        Nombre
                                                        <span>*</span>
                                                    </label>

                                                    <input
                                                        v-model="
                                                            category.name
                                                        "
                                                        type="text"
                                                        :class="
                                                            getFieldClass(
                                                                `distances.${distanceIndex}.categories.${categoryIndex}.name`,
                                                            )
                                                        "
                                                        placeholder="Ej. Libre"
                                                    />

                                                    <p
                                                        v-if="
                                                            hasError(
                                                                `distances.${distanceIndex}.categories.${categoryIndex}.name`,
                                                            )
                                                        "
                                                        class="admin-form-error"
                                                    >
                                                        {{
                                                            getError(
                                                                `distances.${distanceIndex}.categories.${categoryIndex}.name`,
                                                            )
                                                        }}
                                                    </p>
                                                </div>

                                                <div
                                                    class="admin-form-group"
                                                >
                                                    <label
                                                        class="admin-form-label"
                                                    >
                                                        Género
                                                    </label>

                                                    <select
                                                        v-model="
                                                            category.gender
                                                        "
                                                        :class="
                                                            getFieldClass(
                                                                `distances.${distanceIndex}.categories.${categoryIndex}.gender`,
                                                            )
                                                        "
                                                    >
                                                        <option
                                                            value="mixed"
                                                        >
                                                            Mixto
                                                        </option>

                                                        <option
                                                            value="male"
                                                        >
                                                            Masculino
                                                        </option>

                                                        <option
                                                            value="female"
                                                        >
                                                            Femenino
                                                        </option>
                                                    </select>

                                                    <p
                                                        v-if="
                                                            hasError(
                                                                `distances.${distanceIndex}.categories.${categoryIndex}.gender`,
                                                            )
                                                        "
                                                        class="admin-form-error"
                                                    >
                                                        {{
                                                            getError(
                                                                `distances.${distanceIndex}.categories.${categoryIndex}.gender`,
                                                            )
                                                        }}
                                                    </p>
                                                </div>

                                                <div
                                                    class="admin-form-group"
                                                >
                                                    <label
                                                        class="admin-form-label"
                                                    >
                                                        Edad mínima
                                                    </label>

                                                    <input
                                                        v-model="
                                                            category.min_age
                                                        "
                                                        type="number"
                                                        min="0"
                                                        max="120"
                                                        :class="
                                                            getFieldClass(
                                                                `distances.${distanceIndex}.categories.${categoryIndex}.min_age`,
                                                            )
                                                        "
                                                        placeholder="0"
                                                    />

                                                    <p
                                                        v-if="
                                                            hasError(
                                                                `distances.${distanceIndex}.categories.${categoryIndex}.min_age`,
                                                            )
                                                        "
                                                        class="admin-form-error"
                                                    >
                                                        {{
                                                            getError(
                                                                `distances.${distanceIndex}.categories.${categoryIndex}.min_age`,
                                                            )
                                                        }}
                                                    </p>
                                                </div>

                                                <div
                                                    class="admin-form-group"
                                                >
                                                    <label
                                                        class="admin-form-label"
                                                    >
                                                        Edad máxima
                                                    </label>

                                                    <input
                                                        v-model="
                                                            category.max_age
                                                        "
                                                        type="number"
                                                        min="0"
                                                        max="120"
                                                        :class="
                                                            getFieldClass(
                                                                `distances.${distanceIndex}.categories.${categoryIndex}.max_age`,
                                                            )
                                                        "
                                                        placeholder="Sin límite"
                                                    />

                                                    <p
                                                        v-if="
                                                            hasError(
                                                                `distances.${distanceIndex}.categories.${categoryIndex}.max_age`,
                                                            )
                                                        "
                                                        class="admin-form-error"
                                                    >
                                                        {{
                                                            getError(
                                                                `distances.${distanceIndex}.categories.${categoryIndex}.max_age`,
                                                            )
                                                        }}
                                                    </p>
                                                </div>

                                                <div
                                                    class="admin-form-group admin-form-group-full"
                                                >
                                                    <label
                                                        class="admin-form-label"
                                                    >
                                                        Descripción
                                                    </label>

                                                    <textarea
                                                        v-model="
                                                            category.description
                                                        "
                                                        rows="3"
                                                        class="admin-form-input admin-form-textarea"
                                                        :class="{
                                                            'has-error':
                                                                hasError(
                                                                    `distances.${distanceIndex}.categories.${categoryIndex}.description`,
                                                                ),
                                                        }"
                                                        placeholder="Descripción de la categoría..."
                                                    ></textarea>

                                                    <p
                                                        v-if="
                                                            hasError(
                                                                `distances.${distanceIndex}.categories.${categoryIndex}.description`,
                                                            )
                                                        "
                                                        class="admin-form-error"
                                                    >
                                                        {{
                                                            getError(
                                                                `distances.${distanceIndex}.categories.${categoryIndex}.description`,
                                                            )
                                                        }}
                                                    </p>
                                                </div>

                                                <div
                                                    class="admin-form-group"
                                                >
                                                    <label
                                                        class="race-checkbox-label"
                                                    >
                                                        <input
                                                            v-model="
                                                                category.is_active
                                                            "
                                                            type="checkbox"
                                                        />

                                                        <span>
                                                            Categoría activa
                                                        </span>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <button
                        type="button"
                        class="race-add-distance-button"
                        @click="addDistance"
                    >
                        <Plus :size="15" />
                        Agregar distancia
                    </button>
                </div>

                <!-- =================================================
                     RESULTS
                     ================================================= -->

                <div
                    v-if="activeTab === 'results'"
                    class="race-tab-content"
                >
                    <div class="race-section-header">
                        <div>
                            <h2>
                                Resultados
                            </h2>

                            <p>
                                Agrega el enlace externo donde estarán
                                disponibles los resultados de la carrera.
                            </p>
                        </div>
                    </div>

                    <div class="admin-form-grid">
                        <div
                            class="admin-form-group admin-form-group-full"
                        >
                            <label
                                for="results_url"
                                class="admin-form-label"
                            >
                                Enlace de resultados
                            </label>

                            <input
                                id="results_url"
                                v-model="form.results_url"
                                type="url"
                                :class="getFieldClass('results_url')"
                                placeholder="https://ejemplo.com/resultados"
                            />

                            <p class="admin-form-help">
                                Este enlace corresponde al sitio externo
                                donde se publicarán los resultados de esta
                                carrera.
                            </p>

                            <p
                                v-if="hasError('results_url')"
                                class="admin-form-error"
                            >
                                {{ getError('results_url') }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- =================================================
                     ACTIONS
                     ================================================= -->

                <div class="admin-form-actions race-form-actions">
                    <div class="race-form-cancel">
                        <Link
                            :href="admin.races.index().url"
                            class="admin-btn admin-btn-secondary"
                        >
                            Cancelar
                        </Link>
                    </div>

                    <div class="race-form-navigation">
                        <button
                            v-if="activeTab !== 'general'"
                            type="button"
                            class="admin-btn admin-btn-secondary"
                            @click="previousTab"
                        >
                            <ArrowLeft :size="14" />
                            Anterior
                        </button>

                        <button
                            v-if="activeTab !== 'results'"
                            type="button"
                            class="admin-btn admin-btn-secondary"
                            @click="nextTab"
                        >
                            Siguiente
                            <ArrowRight :size="14" />
                        </button>

                        <span
                            v-if="form.processing"
                            class="race-saving-text"
                        >
                            Guardando...
                        </span>

                        <button
                            v-if="activeTab === 'results'"
                            type="submit"
                            :disabled="form.processing"
                            class="admin-btn admin-btn-primary"
                        >
                            <span
                                v-if="form.processing"
                                class="admin-btn-loader"
                            ></span>

                            <Save
                                v-else
                                :size="15"
                            />

                            {{
                                form.processing
                                    ? 'Guardando...'
                                    : 'Guardar carrera'
                            }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</template>

<style scoped>
/* =========================================================
   HEADER
   ========================================================= */

.admin-page {
    width: 100%;
}

.race-tabs-wrapper {
    padding: 8px 28px 0;
    border-bottom: 1px solid var(--sc-page-border);
    background: #fbfcfd;
}

.race-tabs {
    display: flex;
    align-items: stretch;
    gap: 4px;
    overflow-x: auto;
}

.race-tab {
    position: relative;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 7px;
    min-height: 48px;
    padding: 0 16px;
    border: 0;
    border-bottom: 2px solid transparent;
    border-radius: 7px 7px 0 0;
    background: transparent;
    color: #7b8b97;
    font-family: inherit;
    font-size: 12px;
    font-weight: 650;
    white-space: nowrap;
    cursor: pointer;
    transition:
        color 0.2s ease,
        background-color 0.2s ease,
        border-color 0.2s ease;
}

.race-tab:hover {
    background: #f4f8fa;
    color: var(--sc-page-text);
}

.race-tab-active {
    border-bottom-color: #a9d9f2;
    background: #ffffff;
    color: var(--sc-page-text);
}

.race-tab-error {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: #d95c4f;
}

/* =========================================================
   CONTENT
   ========================================================= */

.race-tab-content {
    padding: 30px;
}

.race-section-header {
    margin-bottom: 26px;
}

.race-section-header h2 {
    margin: 0;
    color: var(--sc-page-text);
    font-size: 16px;
    font-weight: 700;
    letter-spacing: -0.01em;
}

.race-section-header p {
    margin: 6px 0 0;
    color: var(--sc-page-text-secondary);
    font-size: 12px;
    line-height: 1.5;
}

/* =========================================================
   FORM
   ========================================================= */

.admin-form-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    column-gap: 20px;
}

.admin-form-group {
    min-width: 0;
    margin-bottom: 22px;
}

.admin-form-group-full {
    grid-column: 1 / -1;
}

.admin-form-label {
    display: block;
    margin-bottom: 8px;
    color: var(--sc-page-text);
    font-size: 13px;
    font-weight: 650;
}

.admin-form-label span {
    color: #d95c4f;
}

.admin-form-input {
    display: block;
    width: 100%;
    height: 44px;
    padding: 0 14px;
    border: 1px solid var(--sc-page-border);
    border-radius: 10px;
    outline: none;
    background: #fbfcfd;
    color: var(--sc-page-text);
    font-family: inherit;
    font-size: 13px;
    box-sizing: border-box;
    transition:
        border-color 0.2s ease,
        background-color 0.2s ease,
        box-shadow 0.2s ease;
}

.admin-form-input::placeholder {
    color: #9aabba;
}

.admin-form-input:hover {
    background: #ffffff;
}

.admin-form-input:focus {
    border-color: #a9d9f2;
    background: #ffffff;
    box-shadow:
        0 0 0 3px rgba(36, 158, 219, 0.08);
}

.admin-form-input.has-error {
    border-color: #efb8bf;
    background: var(--sc-page-red-light);
}

.admin-form-input.has-error:focus {
    border-color: var(--sc-page-red);
    box-shadow:
        0 0 0 3px rgba(232, 62, 77, 0.08);
}

.admin-form-textarea {
    height: auto;
    min-height: 105px;
    padding-top: 12px;
    padding-bottom: 12px;
    resize: vertical;
}

.admin-form-error {
    margin: 6px 0 0;
    color: #d95c4f;
    font-size: 11px;
    line-height: 1.4;
}

.admin-form-help {
    margin: 7px 0 0;
    color: #8b9ba6;
    font-size: 11px;
    line-height: 1.45;
}

.admin-input-icon-wrapper {
    position: relative;
}

.admin-input-icon-wrapper .admin-form-input {
    padding-left: 40px;
}

.admin-input-icon {
    position: absolute;
    top: 50%;
    left: 14px;
    z-index: 1;
    color: #8ea1ae;
    pointer-events: none;
    transform: translateY(-50%);
}

/* =========================================================
   STATUS
   ========================================================= */

.admin-form-status {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
    min-height: 74px;
    padding: 15px 17px;
    border: 1px solid #e5ecef;
    border-radius: 10px;
    background: #fbfcfd;
}

.admin-form-status-content {
    display: flex;
    align-items: center;
    gap: 12px;
    min-width: 0;
}

.admin-form-status-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    flex: 0 0 32px;
    border: 1px solid #dbeaf1;
    border-radius: 8px;
    background: #f1f8fb;
}

.admin-form-status-icon span {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #76aac5;
}

.admin-form-status-title {
    margin: 0;
    color: var(--sc-page-text);
    font-size: 12px;
    font-weight: 700;
}

.admin-form-status-description {
    max-width: 550px;
    margin: 4px 0 0;
    color: var(--sc-page-text-secondary);
    font-size: 11px;
    line-height: 1.45;
}

.admin-form-status .admin-form-input {
    width: 220px;
    flex: 0 0 220px;
}

/* =========================================================
   BANNER
   ========================================================= */

.race-hidden-file-input {
    display: none;
}

.race-banner-upload {
    display: flex;
    align-items: center;
    gap: 16px;
    min-height: 116px;
    padding: 19px;
    border: 1px dashed #cadbe4;
    border-radius: 10px;
    background: #fbfcfd;
    cursor: pointer;
    transition:
        border-color 0.2s ease,
        background-color 0.2s ease;
}

.race-banner-upload:hover {
    border-color: #a9d9f2;
    background: #f8fcff;
}

.race-banner-upload-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 46px;
    height: 46px;
    flex: 0 0 46px;
    border-radius: 9px;
    background: #eef7fb;
    color: #70a7c1;
}

.race-banner-upload-content {
    flex: 1;
    min-width: 0;
}

.race-banner-upload-content strong {
    display: block;
    color: var(--sc-page-text);
    font-size: 12px;
    font-weight: 700;
}

.race-banner-upload-content p {
    margin: 5px 0;
    color: var(--sc-page-text-secondary);
    font-size: 11px;
}

.race-banner-upload-content span {
    color: #9aaab5;
    font-size: 10px;
}

.race-banner-button {
    flex: 0 0 auto;
}

.race-banner-preview {
    overflow: hidden;
    border: 1px solid var(--sc-page-border);
    border-radius: 10px;
    background: #f4f7f9;
}

.race-banner-preview img {
    display: block;
    width: 100%;
    height: 220px;
    object-fit: cover;
}

.race-banner-preview-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 15px;
    padding: 12px 14px;
    border-top: 1px solid var(--sc-page-border);
    background: #ffffff;
}

.race-banner-preview-footer > div:first-child {
    min-width: 0;
}

.race-banner-preview-footer strong,
.race-banner-preview-footer span {
    display: block;
}

.race-banner-preview-footer strong {
    color: var(--sc-page-text);
    font-size: 11px;
}

.race-banner-preview-footer span {
    margin-top: 3px;
    color: var(--sc-page-text-secondary);
    font-size: 10px;
}

.race-banner-actions {
    display: flex;
    gap: 7px;
    flex: 0 0 auto;
}

.race-danger-button {
    color: #c85454;
}

/* =========================================================
   DISTANCES
   ========================================================= */

.race-distances-list {
    display: flex;
    flex-direction: column;
    gap: 13px;
}

.race-distance-card {
    overflow: hidden;
    border: 1px solid var(--sc-page-border);
    border-radius: 10px;
    background: #ffffff;
}

.race-distance-header {
    display: flex;
    align-items: center;
    min-height: 66px;
    padding: 0 14px;
    background: #fbfcfd;
}

.race-distance-toggle {
    display: flex;
    align-items: center;
    justify-content: space-between;
    width: 100%;
    min-width: 0;
    padding: 0;
    border: 0;
    background: transparent;
    color: var(--sc-page-text);
    font-family: inherit;
    cursor: pointer;
}

.race-distance-main {
    display: flex;
    align-items: center;
    gap: 11px;
    min-width: 0;
}

.race-distance-number {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 34px;
    height: 34px;
    flex: 0 0 34px;
    border: 1px solid #dbeaf1;
    border-radius: 8px;
    background: #f1f8fb;
    color: #5c95b1;
    font-size: 11px;
    font-weight: 700;
}

.race-distance-heading {
    display: flex;
    align-items: baseline;
    gap: 9px;
    min-width: 0;
}

.race-distance-heading strong {
    overflow: hidden;
    color: var(--sc-page-text);
    font-size: 13px;
    font-weight: 700;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.race-distance-heading span {
    color: #8497a3;
    font-size: 11px;
    white-space: nowrap;
}

.race-distance-toggle-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    color: #8799a5;
}

.race-distance-delete {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    flex: 0 0 32px;
    margin-left: 5px;
    border: 0;
    border-radius: 7px;
    background: transparent;
    color: #a2adb5;
    cursor: pointer;
    transition:
        color 0.2s ease,
        background-color 0.2s ease;
}

.race-distance-delete:hover {
    background: #fff2f1;
    color: #d95c4f;
}

.race-distance-body {
    padding: 25px 21px 21px;
    border-top: 1px solid var(--sc-page-border);
}

.race-subsection {
    padding-top: 23px;
    margin-top: 23px;
    border-top: 1px solid #edf1f3;
}

.race-subsection:first-child {
    padding-top: 0;
    margin-top: 0;
    border-top: 0;
}

.race-subsection-heading {
    margin-bottom: 17px;
}

.race-subsection-heading h3 {
    margin: 0;
    color: var(--sc-page-text);
    font-size: 13px;
    font-weight: 700;
}

.race-subsection-heading p {
    margin: 5px 0 0;
    color: var(--sc-page-text-secondary);
    font-size: 11px;
}

/* =========================================================
   DYNAMIC ITEMS
   ========================================================= */

.race-dynamic-section-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 15px;
    margin-bottom: 16px;
}

.race-dynamic-section-header .race-subsection-heading {
    margin-bottom: 0;
}

.race-add-button {
    white-space: nowrap;
}

.race-dynamic-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.race-dynamic-item {
    position: relative;
    padding: 16px 16px 3px;
    border: 1px solid #e5ecef;
    border-radius: 9px;
    background: #fcfdfe;
}

.race-dynamic-item-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 13px;
}

.race-dynamic-item-header > span {
    color: #82939e;
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.04em;
}

.race-item-delete {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 28px;
    height: 28px;
    border: 0;
    border-radius: 7px;
    background: transparent;
    color: #a5b0b7;
    cursor: pointer;
}

.race-item-delete:hover {
    background: #fff2f1;
    color: #d95c4f;
}

.race-dynamic-item .admin-form-group {
    margin-bottom: 17px;
}

/* =========================================================
   CHECKBOX
   ========================================================= */

.race-checkbox-label {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    min-height: 44px;
    color: var(--sc-page-text);
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
}

.race-checkbox-label input {
    width: 16px;
    height: 16px;
    margin: 0;
    accent-color: #70a7c1;
}

/* =========================================================
   ADD DISTANCE
   ========================================================= */

.race-add-distance-button {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    width: 100%;
    min-height: 46px;
    margin-top: 15px;
    border: 1px dashed #c9dce6;
    border-radius: 9px;
    background: #fbfdfe;
    color: #568eac;
    font-family: inherit;
    font-size: 11px;
    font-weight: 650;
    cursor: pointer;
    transition:
        border-color 0.2s ease,
        background-color 0.2s ease;
}

.race-add-distance-button:hover {
    border-color: #a9d9f2;
    background: #f7fcff;
}

/* =========================================================
   ACTIONS
   ========================================================= */

.race-form-actions {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    margin-top: 0;
    padding-top: 20px;
    border-top: 1px solid var(--sc-page-border);
}

.race-form-cancel,
.race-form-navigation {
    display: flex;
    align-items: center;
    gap: 8px;
}

.race-saving-text {
    margin-right: 4px;
    color: #82939e;
    font-size: 11px;
}


/* =========================================================
   RESPONSIVE
   ========================================================= */

@media (max-width: 900px) {
    .race-tabs-wrapper {
        padding-left: 15px;
        padding-right: 15px;
    }

    .race-tab-content {
        padding: 24px 20px;
    }

    .admin-form-grid {
        grid-template-columns: 1fr;
    }

    .admin-form-group-full {
        grid-column: auto;
    }

    .admin-form-status {
        align-items: flex-start;
        flex-direction: column;
    }

    .admin-form-status .admin-form-input {
        width: 100%;
        flex: none;
    }
}

@media (max-width: 640px) {
    .race-tab {
        padding: 0 12px;
    }

    .race-tab span:not(.race-tab-error) {
        display: none;
    }

    .race-tab-content {
        padding: 21px 15px;
    }

    .race-banner-upload {
        align-items: flex-start;
        flex-wrap: wrap;
    }

    .race-banner-button {
        width: 100%;
    }

    .race-banner-preview img {
        height: 180px;
    }

    .race-banner-preview-footer {
        align-items: flex-start;
        flex-direction: column;
    }

    .race-banner-actions {
        width: 100%;
    }

    .race-banner-actions .admin-btn {
        flex: 1;
    }

    .race-distance-body {
        padding: 19px 13px;
    }

    .race-dynamic-section-header {
        align-items: stretch;
        flex-direction: column;
    }

    .race-add-button {
        align-self: flex-start;
    }

    .race-form-actions {
        align-items: stretch;
        flex-direction: column;
    }

    .race-form-cancel,
    .race-form-navigation {
        width: 100%;
    }

    .race-form-cancel .admin-btn,
    .race-form-navigation .admin-btn {
        flex: 1;
    }

    .race-saving-text {
        display: none;
    }
}
</style>
