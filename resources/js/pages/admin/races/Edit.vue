<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import {
    ArrowLeft,
    ArrowRight,
    ExternalLink,
    FileText,
    MapPin,
    Save,
    Settings2,
    Trophy,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';

import admin from '@/routes/admin';

import TabSteps from '@/Components/Admin/TabSteps.vue';
import GeneralStep from '@/Components/Admin/Races/GeneralStep.vue';
import LocationStep from '@/Components/Admin/Races/LocationStep.vue';
import RegistrationStep from '@/Components/Admin/Races/RegistrationStep.vue';
import DistancesStep from '@/Components/Admin/Races/DistancesStep.vue';
import ResultsStep from '@/Components/Admin/Races/ResultsStep.vue';

type TabKey =
    | 'general'
    | 'location'
    | 'registration'
    | 'distances'
    | 'results';

interface PriceForm {
    id?: number;
    name: string;
    price: string;
    starts_at: string;
    ends_at: string;
    capacity: string;
    sort_order: number;
    is_active: boolean;
}

interface InclusionForm {
    id?: number;
    name: string;
    description: string;
    type: string;
    included: boolean;
    sort_order: number;
}

interface CategoryForm {
    id?: number;
    name: string;
    description: string;
    min_age: string;
    max_age: string;
    gender: string;
    sort_order: number;
    is_active: boolean;
}

interface DistanceForm {
    id?: number;
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

interface KitImageForm {
    id: number | null;
    image: string | null;
    file: File | null;
    preview: string | null;
    sort_order: number;
    is_active: boolean;
    isNew: boolean;
}

interface RaceKitImage {
    id: number;
    image: string;
    sort_order: number;
    is_active: boolean;
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
    results_url: string | null;
    status: string;
    terms_and_conditions: string | null;
    notes: string | null;
    distances: DistanceForm[];
    kit_images?: RaceKitImage[];
    kitImages?: RaceKitImage[];
}

const props = defineProps<{
    race: Race;
}>();

/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

const normalizeDateTimeLocal = (
    value: string | null | undefined,
): string => {
    if (!value) {
        return '';
    }

    return value
        .replace(' ', 'T')
        .slice(0, 16);
};

const normalizeTime = (
    value: string | null | undefined,
): string => {
    if (!value) {
        return '';
    }

    return value.slice(0, 5);
};

/*
|--------------------------------------------------------------------------
| Tabs
|--------------------------------------------------------------------------
*/

const activeTab = ref<TabKey>('general');

const tabs: TabKey[] = [
    'general',
    'location',
    'registration',
    'distances',
    'results',
];

const expandedDistances = ref<number[]>(
    props.race.distances?.length
        ? [0]
        : [],
);

/*
|--------------------------------------------------------------------------
| Banner
|--------------------------------------------------------------------------
*/

const getBannerUrl = (
    banner: string | null,
): string | null => {
    if (!banner) {
        return null;
    }

    if (
        banner.startsWith('http://') ||
        banner.startsWith('https://')
    ) {
        return banner;
    }

    return `/storage/${banner}`;
};

const bannerPreview = ref<string | null>(
    getBannerUrl(props.race.banner),
);

const existingBanner = ref<string | null>(
    props.race.banner,
);

/*
|--------------------------------------------------------------------------
| Kit gallery
|--------------------------------------------------------------------------
*/

const getKitImageUrl = (
    image: string | null | undefined,
): string | null => {
    if (!image) {
        return null;
    }

    if (
        image.startsWith('http://') ||
        image.startsWith('https://') ||
        image.startsWith('blob:') ||
        image.startsWith('data:')
    ) {
        return image;
    }

    if (image.startsWith('/')) {
        return image;
    }

    return `/storage/${image}`;
};

const normalizeKitImages = (
    images: RaceKitImage[] | undefined,
): KitImageForm[] => {
    if (!images?.length) {
        return [];
    }

    return images.map((image, index) => ({
        id: image.id,
        image: image.image,
        file: null,
        preview: getKitImageUrl(image.image),
        sort_order:
            image.sort_order ?? index,
        is_active:
            image.is_active ?? true,
        isNew: false,
    }));
};

/*
|--------------------------------------------------------------------------
| Factories
|--------------------------------------------------------------------------
*/

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

/*
|--------------------------------------------------------------------------
| Normalize existing distances
|--------------------------------------------------------------------------
*/

const normalizeDistances = (
    distances: DistanceForm[] | undefined,
): DistanceForm[] => {
    if (!distances?.length) {
        return [createDistance()];
    }

    return distances.map(
        (distance) => ({
            id: distance.id,
            name: distance.name ?? '',
            distance:
                distance.distance ?? '',
            unit:
                distance.unit ?? 'km',
            start_time:
                normalizeTime(
                    distance.start_time,
                ),
            capacity:
                distance.capacity ?? '',
            sort_order:
                distance.sort_order ?? 0,
            is_active:
                distance.is_active ?? true,

            prices:
                distance.prices?.length
                    ? distance.prices.map(
                          (price) => ({
                              id: price.id,
                              name:
                                  price.name ??
                                  '',
                              price:
                                  price.price ??
                                  '',
                              starts_at:
                                  normalizeDateTimeLocal(
                                      price.starts_at,
                                  ),
                              ends_at:
                                  normalizeDateTimeLocal(
                                      price.ends_at,
                                  ),
                              capacity:
                                  price.capacity ??
                                  '',
                              sort_order:
                                  price.sort_order ??
                                  0,
                              is_active:
                                  price.is_active ??
                                  true,
                          }),
                      )
                    : [
                          createPrice(),
                      ],

            inclusions:
                distance.inclusions?.length
                    ? distance.inclusions.map(
                          (
                              inclusion,
                          ) => ({
                              id: inclusion.id,
                              name:
                                  inclusion.name ??
                                  '',
                              description:
                                  inclusion.description ??
                                  '',
                              type:
                                  inclusion.type ??
                                  'benefit',
                              included:
                                  inclusion.included ??
                                  true,
                              sort_order:
                                  inclusion.sort_order ??
                                  0,
                          }),
                      )
                    : [
                          createInclusion(),
                      ],

            categories:
                distance.categories?.length
                    ? distance.categories.map(
                          (
                              category,
                          ) => ({
                              id: category.id,
                              name:
                                  category.name ??
                                  '',
                              description:
                                  category.description ??
                                  '',
                              min_age:
                                  category.min_age ??
                                  '',
                              max_age:
                                  category.max_age ??
                                  '',
                              gender:
                                  category.gender ??
                                  'mixed',
                              sort_order:
                                  category.sort_order ??
                                  0,
                              is_active:
                                  category.is_active ??
                                  true,
                          }),
                      )
                    : [
                          createCategory(),
                      ],
        }),
    );
};

/*
|--------------------------------------------------------------------------
| Form
|--------------------------------------------------------------------------
*/

const form = useForm({
    name:
        props.race.name ?? '',

    slug:
        props.race.slug ?? '',

    results_url:
        props.race.results_url ?? '',

    description:
        props.race.description ?? '',

    event_date:
        props.race.event_date
            ? props.race.event_date.slice(
                  0,
                  10,
              )
            : '',

    start_time:
        normalizeTime(
            props.race.start_time,
        ),

    end_time:
        normalizeTime(
            props.race.end_time,
        ),

    location:
        props.race.location ?? '',

    address:
        props.race.address ?? '',

    city:
        props.race.city ?? '',

    state:
        props.race.state ?? '',

    country:
        props.race.country ?? 'México',

    banner:
        null as File | null,

    remove_banner: false,

    kit_images:
        normalizeKitImages(
            props.race.kit_images ??
            props.race.kitImages,
        ),

    registration_opens_at:
        normalizeDateTimeLocal(
            props.race.registration_opens_at,
        ),

    registration_closes_at:
        normalizeDateTimeLocal(
            props.race.registration_closes_at,
        ),

    status:
        props.race.status ?? 'draft',

    terms_and_conditions:
        props.race
            .terms_and_conditions ?? '',

    notes:
        props.race.notes ?? '',

    distances:
        normalizeDistances(
            props.race.distances,
        ),
});

/*
|--------------------------------------------------------------------------
| Errors
|--------------------------------------------------------------------------
*/

const getError = (
    key: string,
): string | undefined => {
    return (
        form.errors as Record<
            string,
            string
        >
    )[key];
};

const hasError = (
    key: string,
): boolean => {
    return Boolean(
        getError(key),
    );
};

const getFieldClass = (
    key: string,
): string => {
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
    return Object.keys(form.errors).some(
        (key) =>
            key.startsWith(
                'distances.',
            ),
    );
};

const hasResultsErrors = (): boolean => {
    return hasError(
        'results_url',
    );
};

/*
|--------------------------------------------------------------------------
| Tab steps
|--------------------------------------------------------------------------
*/

const tabSteps = computed(() => [
    {
        key: 'general',
        label: 'General',
        icon: FileText,
        hasError:
            hasGeneralErrors(),
    },
    {
        key: 'location',
        label: 'Fecha y lugar',
        icon: MapPin,
        hasError:
            hasLocationErrors(),
    },
    {
        key: 'registration',
        label: 'Inscripciones',
        icon: Settings2,
        hasError:
            hasRegistrationErrors(),
    },
    {
        key: 'distances',
        label: 'Distancias',
        icon: Trophy,
        hasError:
            hasDistanceErrors(),
    },
    {
        key: 'results',
        label: 'Resultados',
        icon: ExternalLink,
        hasError:
            hasResultsErrors(),
    },
]);

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

    if (
        firstError.startsWith(
            'distances.',
        )
    ) {
        activeTab.value =
            'distances';

        const match =
            firstError.match(
                /^distances\.(\d+)\./,
            );

        if (match) {
            const distanceIndex =
                Number(match[1]);

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

    if (
        firstError.startsWith(
            'kit_images.',
        ) ||
        firstError === 'kit_images'
    ) {
        activeTab.value =
            'general';

        return;
    }

    if (
        firstError ===
        'results_url'
    ) {
        activeTab.value =
            'results';

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

    if (
        locationFields.includes(
            firstError,
        )
    ) {
        activeTab.value =
            'location';

        return;
    }

    const registrationFields = [
        'registration_opens_at',
        'registration_closes_at',
        'terms_and_conditions',
        'notes',
    ];

    if (
        registrationFields.includes(
            firstError,
        )
    ) {
        activeTab.value =
            'registration';

        return;
    }

    activeTab.value =
        'general';
};

/*
|--------------------------------------------------------------------------
| Banner
|--------------------------------------------------------------------------
*/

const selectBanner = (
    event: Event,
): void => {
    const target =
        event.target as HTMLInputElement;

    const file =
        target.files?.[0];

    if (!file) {
        return;
    }

    if (
        !file.type.startsWith(
            'image/',
        )
    ) {
        target.value = '';

        return;
    }

    form.banner = file;
    form.remove_banner = false;

    if (
        bannerPreview.value
    ) {
        URL.revokeObjectURL(
            bannerPreview.value,
        );
    }

    bannerPreview.value =
        URL.createObjectURL(file);

    target.value = '';
};

const removeBanner = (): void => {
    if (
        bannerPreview.value
    ) {
        if (
            bannerPreview.value.startsWith(
                'blob:',
            )
        ) {
            URL.revokeObjectURL(
                bannerPreview.value,
            );
        }
    }

    bannerPreview.value = null;
    form.banner = null;
    form.remove_banner =
        Boolean(
            existingBanner.value,
        );
};

const changeBanner = (): void => {
    document
        .getElementById(
            'race-banner-input',
        )
        ?.click();
};

/*
|--------------------------------------------------------------------------
| Kit gallery
|--------------------------------------------------------------------------
*/

const handleKitGalleryError = (
    message: string,
): void => {
    console.warn(message);
};

/*
|--------------------------------------------------------------------------
| Distances
|--------------------------------------------------------------------------
*/

const addDistance = (): void => {
    form.distances.push(
        createDistance(),
    );

    expandedDistances.value.push(
        form.distances.length - 1,
    );
};

const removeDistance = (
    index: number,
): void => {
    if (
        form.distances.length <= 1
    ) {
        return;
    }

    form.distances.splice(
        index,
        1,
    );

    expandedDistances.value =
        expandedDistances.value
            .filter(
                (item) =>
                    item !== index,
            )
            .map((item) =>
                item > index
                    ? item - 1
                    : item,
            );
};

const toggleDistance = (
    index: number,
): void => {
    if (
        expandedDistances.value.includes(
            index,
        )
    ) {
        expandedDistances.value =
            expandedDistances.value.filter(
                (item) =>
                    item !== index,
            );

        return;
    }

    expandedDistances.value.push(
        index,
    );
};

/*
|--------------------------------------------------------------------------
| Prices
|--------------------------------------------------------------------------
*/

const addPrice = (
    distanceIndex: number,
): void => {
    form.distances[
        distanceIndex
    ].prices.push(
        createPrice(),
    );
};

const removePrice = (
    distanceIndex: number,
    priceIndex: number,
): void => {
    const prices =
        form.distances[
            distanceIndex
        ].prices;

    if (prices.length <= 1) {
        return;
    }

    prices.splice(
        priceIndex,
        1,
    );
};

/*
|--------------------------------------------------------------------------
| Inclusions
|--------------------------------------------------------------------------
*/

const addInclusion = (
    distanceIndex: number,
): void => {
    form.distances[
        distanceIndex
    ].inclusions.push(
        createInclusion(),
    );
};

const removeInclusion = (
    distanceIndex: number,
    inclusionIndex: number,
): void => {
    const inclusions =
        form.distances[
            distanceIndex
        ].inclusions;

    if (
        inclusions.length <= 1
    ) {
        return;
    }

    inclusions.splice(
        inclusionIndex,
        1,
    );
};

/*
|--------------------------------------------------------------------------
| Categories
|--------------------------------------------------------------------------
*/

const addCategory = (
    distanceIndex: number,
): void => {
    form.distances[
        distanceIndex
    ].categories.push(
        createCategory(),
    );
};

const removeCategory = (
    distanceIndex: number,
    categoryIndex: number,
): void => {
    const categories =
        form.distances[
            distanceIndex
        ].categories;

    if (
        categories.length <= 1
    ) {
        return;
    }

    categories.splice(
        categoryIndex,
        1,
    );
};

/*
|--------------------------------------------------------------------------
| Tabs
|--------------------------------------------------------------------------
*/

const nextTab = (): void => {
    const currentIndex =
        tabs.indexOf(
            activeTab.value,
        );

    if (
        currentIndex <
        tabs.length - 1
    ) {
        activeTab.value =
            tabs[
                currentIndex + 1
            ];
    }
};

const previousTab = (): void => {
    const currentIndex =
        tabs.indexOf(
            activeTab.value,
        );

    if (currentIndex > 0) {
        activeTab.value =
            tabs[
                currentIndex - 1
            ];
    }
};

/*
|--------------------------------------------------------------------------
| Submit
|--------------------------------------------------------------------------
*/

const submit = (): void => {
    form.put(
        admin.races.update(
            props.race.id,
        ).url,
        {
            preserveScroll: true,
            forceFormData: true,

            onError: (
                errors,
            ) => {
                focusFirstError(
                    errors as Record<
                        string,
                        string
                    >,
                );
            },
        },
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
                title: 'Editar carrera',
                href: admin.races.index(),
            },
        ],
    },
});
</script>

<template>
    <Head title="Editar carrera" />

    <div class="admin-page">
        <div class="race-page-header">
            <div class="race-page-header-content">
                <span class="race-page-eyebrow">
                    Carreras
                </span>

                <h1>
                    Editar carrera
                </h1>

                <p>
                    Modifica la información, fechas,
                    inscripciones, distancias y resultados
                    de la carrera.
                </p>
            </div>

            <Link
                :href="admin.races.index().url"
                class="admin-btn admin-btn-secondary"
            >
                <ArrowLeft :size="14" />
                Volver
            </Link>
        </div>

        <form
            class="race-form"
            @submit.prevent="submit"
        >
            <div class="race-form-card">
                <TabSteps
                    v-model:active-step="activeTab"
                    :steps="tabSteps"
                />

                <div class="race-tab-content">
                    <GeneralStep
                        v-if="activeTab === 'general'"
                        :form="form"
                        :get-error="getError"
                        :get-field-class="getFieldClass"
                        :banner-preview="bannerPreview"
                        @select-banner="selectBanner"
                        @remove-banner="removeBanner"
                        @change-banner="changeBanner"
                        @kit-gallery-error="handleKitGalleryError"
                    />

                    <LocationStep
                        v-if="activeTab === 'location'"
                        :form="form"
                        :get-error="getError"
                        :get-field-class="getFieldClass"
                    />

                    <RegistrationStep
                        v-if="activeTab === 'registration'"
                        :form="form"
                        :get-error="getError"
                        :get-field-class="getFieldClass"
                    />

                    <DistancesStep
                        v-if="activeTab === 'distances'"
                        :form="form"
                        :get-error="getError"
                        :get-field-class="getFieldClass"
                        :expanded-distances="expandedDistances"
                        @add-distance="addDistance"
                        @remove-distance="removeDistance"
                        @toggle-distance="toggleDistance"
                        @add-price="addPrice"
                        @remove-price="removePrice"
                        @add-inclusion="addInclusion"
                        @remove-inclusion="removeInclusion"
                        @add-category="addCategory"
                        @remove-category="removeCategory"
                    />

                    <ResultsStep
                        v-if="activeTab === 'results'"
                        :form="form"
                        :get-error="getError"
                        :get-field-class="getFieldClass"
                    />
                </div>

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
                                    : 'Guardar cambios'
                            }}
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</template>

<style scoped>
.admin-page {
    width: 100%;
}

.race-page-header {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 20px;
    margin-bottom: 24px;
}

.race-page-header-content {
    min-width: 0;
}

.race-page-eyebrow {
    display: block;
    margin-bottom: 7px;
    color: #7e929f;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.04em;
    text-transform: uppercase;
}

.race-page-header h1 {
    margin: 0;
    color: var(--sc-page-text);
    font-size: 24px;
    font-weight: 750;
    letter-spacing: -0.025em;
}

.race-page-header p {
    max-width: 650px;
    margin: 7px 0 0;
    color: var(--sc-page-text-secondary);
    font-size: 12px;
    line-height: 1.5;
}

.race-form {
    width: 100%;
}

.race-form-card {
    overflow: hidden;
    border: 1px solid var(--sc-page-border);
    border-radius: 16px;
    background: #ffffff;
}

/*
|--------------------------------------------------------------------------
| TAB CONTENT
|--------------------------------------------------------------------------
*/

.race-tab-content {
    padding: 30px;
}

/*
|--------------------------------------------------------------------------
| GENERIC FORM
|--------------------------------------------------------------------------
*/

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
    box-shadow: 0 0 0 3px rgba(36, 158, 219, 0.08);
}

.admin-form-input.has-error {
    border-color: #efb8bf;
    background: var(--sc-page-red-light);
}

.admin-form-input.has-error:focus {
    border-color: var(--sc-page-red);
    box-shadow: 0 0 0 3px rgba(232, 62, 77, 0.08);
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

/*
|--------------------------------------------------------------------------
| ACTIONS
|--------------------------------------------------------------------------
*/

.race-form-actions {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    margin: 0 30px;
    padding: 20px 0 30px;
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

/*
|--------------------------------------------------------------------------
| RESPONSIVE
|--------------------------------------------------------------------------
*/

@media (max-width: 900px) {
    .race-page-header {
        align-items: flex-start;
        flex-direction: column;
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

    .race-form-actions {
        margin-left: 20px;
        margin-right: 20px;
    }
}

@media (max-width: 640px) {
    .race-page-header {
        margin-bottom: 18px;
    }

    .race-page-header h1 {
        font-size: 21px;
    }

    .race-tab-content {
        padding: 21px 15px;
    }

    .race-form-actions {
        align-items: stretch;
        flex-direction: column;
        margin-left: 15px;
        margin-right: 15px;
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