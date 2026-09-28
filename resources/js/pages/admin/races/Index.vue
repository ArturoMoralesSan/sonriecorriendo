<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import Swal from 'sweetalert2';
import { ref } from 'vue';
import {
    CalendarDays,
    ClipboardList,
    Edit,
    Eye,
    MapPin,
    Plus,
    Search,
    Trash2,
    Trophy,
} from 'lucide-vue-next';

import admin from '@/routes/admin';

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
    distances_count: number;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface RacesPagination {
    data: Race[];
    links: PaginationLink[];
    current_page: number;
    last_page: number;
    from: number | null;
    to: number | null;
    total: number;
}

const props = defineProps<{
    races: RacesPagination;
    filters: {
        search?: string;
    };
}>();

const search = ref(props.filters?.search ?? '');

const submitSearch = (): void => {
    router.get(
        admin.races.index().url,
        {
            search: search.value || undefined,
        },
        {
            preserveState: true,
            replace: true,
        },
    );
};

const deleteRace = async (race: Race): Promise<void> => {
    const result = await Swal.fire({
        title: '¿Eliminar carrera?',
        text: `Se eliminará la carrera "${race.name}". Esta acción no se puede deshacer.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        reverseButtons: true,
        focusCancel: true,
    });

    if (!result.isConfirmed) {
        return;
    }

    router.delete(
        admin.races.destroy(race.id).url,
        {
            preserveScroll: true,

            onSuccess: () => {
                Swal.fire({
                    title: 'Eliminada',
                    text: 'La carrera se eliminó correctamente.',
                    icon: 'success',
                    timer: 1800,
                    showConfirmButton: false,
                });
            },

            onError: () => {
                Swal.fire({
                    title: 'Error',
                    text: 'No se pudo eliminar la carrera.',
                    icon: 'error',
                    confirmButtonText: 'Aceptar',
                });
            },
        },
    );
};

const formatDate = (date: string | null): string => {
    if (!date) {
        return 'Sin fecha';
    }

    const value = String(date).substring(0, 10);
    const parts = value.split('-');

    if (parts.length !== 3) {
        return 'Sin fecha';
    }

    const [year, month, day] = parts;

    if (!year || !month || !day) {
        return 'Sin fecha';
    }

    return `${day}/${month}/${year}`;
};

const getLocation = (race: Race): string => {
    const location = [
        race.location,
        race.city,
        race.state,
    ]
        .filter(Boolean)
        .join(', ');

    return location || 'Sin ubicación';
};

const getBannerUrl = (banner: string | null): string | null => {
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
        ],
    },
});
</script>

<template>
    <Head title="Carreras" />

    <div class="admin-page">
        <header class="admin-page-header">
            <div>
                <p class="admin-page-eyebrow">
                    Eventos
                </p>

                <h1 class="admin-page-title">
                    Carreras
                </h1>

                <p class="admin-page-subtitle">
                    Administra las carreras, sus fechas, ubicaciones y
                    distancias disponibles.
                </p>
            </div>

            <div class="admin-page-header-actions">
                <Link
                    :href="admin.races.create().url"
                    class="admin-btn admin-btn-primary"
                >
                    <span class="admin-btn-icon">
                        <Plus
                            :size="14"
                            :stroke-width="2.2"
                        />
                    </span>

                    Nueva carrera
                </Link>
            </div>
        </header>

        <section class="admin-table-card">
            <div class="admin-table-toolbar">
                <form
                    class="admin-search-form"
                    @submit.prevent="submitSearch"
                >
                    <div class="admin-search-wrapper">
                        <Search
                            class="admin-search-icon"
                            :size="16"
                            :stroke-width="2"
                        />

                        <input
                            v-model="search"
                            type="search"
                            placeholder="Buscar carrera..."
                            class="admin-search-input"
                        />
                    </div>

                    <button
                        type="submit"
                        class="admin-btn admin-btn-search"
                    >
                        <Search
                            :size="14"
                            :stroke-width="2"
                        />

                        Buscar
                    </button>
                </form>

                <div class="admin-table-counter">
                    <strong>
                        {{ races.total }}
                    </strong>

                    <span>
                        carreras
                    </span>
                </div>
            </div>

            <div class="admin-table-wrapper">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>
                                Carrera
                            </th>

                            <th>
                                Fecha
                            </th>

                            <th>
                                Ubicación
                            </th>

                            <th class="text-center">
                                Distancias
                            </th>

                            <th>
                                Estado
                            </th>

                            <th class="text-right">
                                Acciones
                            </th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr
                            v-for="race in races.data"
                            :key="race.id"
                        >
                            <td>
                                <div class="race-cell">
                                    <div class="race-icon">
                                        <img
                                            v-if="getBannerUrl(race.banner)"
                                            :src="getBannerUrl(race.banner)!"
                                            :alt="race.name"
                                        />

                                        <Trophy
                                            v-else
                                            :size="17"
                                            :stroke-width="2"
                                        />
                                    </div>

                                    <div class="race-info">
                                        <strong>
                                            {{ race.name }}
                                        </strong>

                                        <span>
                                            ID: {{ race.id }}
                                        </span>
                                    </div>
                                </div>
                            </td>

                            <td>
                                <div class="race-date-cell">
                                    <CalendarDays
                                        :size="15"
                                        :stroke-width="2"
                                    />

                                    <span>
                                        {{ formatDate(race.event_date) }}
                                    </span>
                                </div>
                            </td>

                            <td>
                                <div class="race-location-cell">
                                    <MapPin
                                        :size="15"
                                        :stroke-width="2"
                                    />

                                    <span>
                                        {{ getLocation(race) }}
                                    </span>
                                </div>
                            </td>

                            <td class="text-center">
                                <span class="distance-badge">
                                    {{ race.distances_count }}
                                </span>
                            </td>

                            <td>
                                <span
                                    class="status-badge"
                                    :class="getStatusClass(race.status)"
                                >
                                    <span class="status-dot"></span>

                                    {{ getStatusLabel(race.status) }}
                                </span>
                            </td>

                            <td>
                                <div class="table-actions">
                                    <Link
                                        :href="
                                            admin.races.show(
                                                race.id,
                                            ).url
                                        "
                                        class="action-btn action-btn-view"
                                        title="Ver detalles de la carrera"
                                        aria-label="Ver detalles de la carrera"
                                    >
                                        <Eye
                                            :size="14"
                                            :stroke-width="2"
                                        />

                                        Detalle
                                    </Link>

                                    <Link
                                        :href="
                                            admin.races.checklist.index(
                                                race.id,
                                            ).url
                                        "
                                        class="action-btn action-btn-checklist"
                                        title="Ver checklist de la carrera"
                                        aria-label="Ver checklist de la carrera"
                                    >
                                        <ClipboardList
                                            :size="14"
                                            :stroke-width="2"
                                        />

                                        Checklist
                                    </Link>
                                    
                                    <Link
                                        :href="admin.races.expenses.index({
                                            race: race.id,
                                        }).url"
                                        class="action-btn action-btn-expenses"
                                        title="Controlar gastos de la carrera"
                                        aria-label="Controlar gastos de la carrera"
                                    >
                                        <Wallet
                                            :size="14"
                                            :stroke-width="2"
                                        />

                                        Gastos
                                    </Link>

                                    <Link
                                        :href="
                                            admin.races.edit(
                                                race.id,
                                            ).url
                                        "
                                        class="action-btn action-btn-edit"
                                        title="Editar carrera"
                                        aria-label="Editar carrera"
                                    >
                                        <Edit
                                            :size="14"
                                            :stroke-width="2"
                                        />

                                        Editar
                                    </Link>

                                    <button
                                        type="button"
                                        class="action-btn action-btn-delete"
                                        title="Eliminar carrera"
                                        aria-label="Eliminar carrera"
                                        @click="
                                            deleteRace(
                                                race,
                                            )
                                        "
                                    >
                                        <Trash2
                                            :size="14"
                                            :stroke-width="2"
                                        />

                                        Eliminar
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <tr
                            v-if="races.data.length === 0"
                        >
                            <td
                                colspan="6"
                                class="admin-table-empty"
                            >
                                <div class="empty-state">
                                    <div class="empty-state-icon">
                                        <Trophy
                                            :size="20"
                                            :stroke-width="2"
                                        />
                                    </div>

                                    <strong>
                                        No se encontraron carreras
                                    </strong>

                                    <span>
                                        Intenta cambiar el término
                                        de búsqueda.
                                    </span>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div
                v-if="races.last_page > 1"
                class="admin-pagination-wrapper"
            >
                <div class="admin-pagination-info">
                    Mostrando

                    <strong>
                        {{ races.from ?? 0 }}
                    </strong>

                    a

                    <strong>
                        {{ races.to ?? 0 }}
                    </strong>

                    de

                    <strong>
                        {{ races.total }}
                    </strong>
                </div>

                <nav class="admin-pagination">
                    <template
                        v-for="(link, index) in races.links"
                        :key="index"
                    >
                        <Link
                            v-if="link.url"
                            :href="link.url"
                            class="pagination-btn"
                            :class="{
                                active: link.active,
                            }"
                            v-html="link.label"
                        />

                        <span
                            v-else
                            class="pagination-btn disabled"
                            v-html="link.label"
                        />
                    </template>
                </nav>
            </div>
        </section>
    </div>
</template>

<style scoped>
.race-cell {
    display: flex;
    align-items: center;
    gap: 11px;
}

.race-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 38px;
    height: 38px;
    flex: 0 0 38px;
    overflow: hidden;
    border: 1px solid #dceaf2;
    border-radius: 9px;
    background: #f5f9fc;
    color: #6e9bb5;
}

.race-icon img {
    display: block;
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.race-info {
    display: flex;
    flex-direction: column;
    gap: 4px;
    min-width: 0;
}

.race-info strong {
    overflow: hidden;
    color: var(--sc-page-text);
    font-size: 13px;
    font-weight: 650;
    line-height: 1.35;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.race-info span {
    color: #8c9ba7;
    font-size: 11px;
    line-height: 1.35;
}

.race-date-cell,
.race-location-cell {
    display: flex;
    align-items: center;
    gap: 8px;
    color: var(--sc-page-text);
    font-size: 12px;
    line-height: 1.35;
}

.race-date-cell svg,
.race-location-cell svg {
    flex: 0 0 auto;
    color: #8aa7b9;
}

.race-location-cell {
    max-width: 240px;
}

.race-location-cell span {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.distance-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 30px;
    height: 26px;
    padding: 0 9px;
    border: 1px solid #dceaf2;
    border-radius: 7px;
    background: #f7fafc;
    color: #5e7f94;
    font-size: 12px;
    font-weight: 650;
}

.status-draft {
    border-color: #e2e7eb;
    background: #f6f8f9;
    color: #788894;
}

.status-published {
    border-color: #cfe4ef;
    background: #f0f8fc;
    color: #527f98;
}

.status-active {
    border-color: #cce8da;
    background: #f1faf5;
    color: #4e8868;
}

.status-closed {
    border-color: #e5dcca;
    background: #fbf8ef;
    color: #8d7950;
}

.status-finished {
    border-color: #d9ddea;
    background: #f4f5fa;
    color: #68718b;
}

.status-inactive {
    border-color: #f0d5d8;
    background: #fff5f5;
    color: #a6656d;
}

@media (max-width: 900px) {
    .race-location-cell {
        max-width: 180px;
    }
}

@media (max-width: 760px) {
    .admin-table-wrapper {
        overflow-x: auto;
    }

    .admin-table {
        min-width: 950px;
    }
}
</style>