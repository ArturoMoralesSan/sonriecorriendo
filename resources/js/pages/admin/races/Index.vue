<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
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
    Wallet,
} from 'lucide-vue-next';
import Swal from 'sweetalert2';
import { ref } from 'vue';

import DataTable from '@/Components/Admin/DataTable.vue';
import PageHeader from '@/Components/Admin/PageHeader.vue';
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

const columns = [
    {
        key: 'race',
        label: 'Carrera',
    },
    {
        key: 'date',
        label: 'Fecha',
    },
    {
        key: 'location',
        label: 'Ubicación',
    },
    {
        key: 'distances',
        label: 'Distancias',
        headerClass: 'text-center',
        class: 'text-center',
    },
    {
        key: 'status',
        label: 'Estado',
    },
];

const actions = [
    {
        key: 'show',
        label: 'Detalle',
        icon: Eye,
        class: 'action-btn-view',
        href: (race: Record<string, any>) =>
            admin.races.show(race.id).url,
    },
    {
        key: 'checklist',
        label: 'Checklist',
        icon: ClipboardList,
        class: 'action-btn-checklist',
        href: (race: Record<string, any>) =>
            admin.races.checklist.index(race.id).url,
    },
    {
        key: 'expenses',
        label: 'Gastos',
        icon: Wallet,
        class: 'action-btn-expenses',
        href: (race: Record<string, any>) =>
            admin.races.expenses.index({
                race: race.id,
            }).url,
    },
    {
        key: 'edit',
        label: 'Editar',
        icon: Edit,
        class: 'action-btn-edit',
        href: (race: Record<string, any>) =>
            admin.races.edit(race.id).url,
    },
    {
        key: 'delete',
        label: 'Eliminar',
        icon: Trash2,
        class: 'action-btn-delete',
        onClick: (race: Record<string, any>) =>
            deleteRace(race as Race),
    },
];

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
        <PageHeader
            eyebrow="Eventos"
            title="Carreras"
            subtitle="Administra las carreras, sus fechas, ubicaciones y distancias disponibles."
            :actions="[
                {
                    label: 'Nueva carrera',
                    href: admin.races.create().url,
                    icon: Plus,
                    variant: 'primary',
                },
            ]"
        />

        <DataTable
            :columns="columns"
            :pagination="races"
            :actions="actions"
            :search="search"
            search-placeholder="Buscar carrera..."
            counter-label="carreras"
            empty-title="No se encontraron carreras."
            empty-description="Intenta cambiar el término de búsqueda."
            :search-icon="Search"
            :empty-icon="Trophy"
            @update:search="search = $event"
            @search="submitSearch"
        >
            <template #cell-race="{ row }">
                <div class="race-cell">
                    <div class="race-icon">
                        <img
                            v-if="getBannerUrl(row.banner)"
                            :src="getBannerUrl(row.banner)!"
                            :alt="row.name"
                        />

                        <Trophy
                            v-else
                            :size="17"
                            :stroke-width="2"
                        />
                    </div>

                    <div class="race-info">
                        <strong>
                            {{ row.name }}
                        </strong>

                        <span>
                            ID: {{ row.id }}
                        </span>
                    </div>
                </div>
            </template>

            <template #cell-date="{ row }">
                <div class="race-date-cell">
                    <CalendarDays
                        :size="15"
                        :stroke-width="2"
                    />

                    <span>
                        {{ formatDate(row.event_date) }}
                    </span>
                </div>
            </template>

            <template #cell-location="{ row }">
                <div class="race-location-cell">
                    <MapPin
                        :size="15"
                        :stroke-width="2"
                    />

                    <span>
                        {{ getLocation(row as Race) }}
                    </span>
                </div>
            </template>

            <template #cell-distances="{ row }">
                <span class="distance-badge">
                    {{ row.distances_count }}
                </span>
            </template>

            <template #cell-status="{ row }">
                <span
                    class="status-badge"
                    :class="getStatusClass(row.status)"
                >
                    <span class="status-dot"></span>

                    {{ getStatusLabel(row.status) }}
                </span>
            </template>
        </DataTable>
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

/*
 * Acciones propias de Carreras.
 * Detalle / Editar / Eliminar ya las controla DataTable.
 */

:global(.data-table-action.action-btn-checklist) {
    background: #f7f5fc;
    color: #6753a8;
}

:global(.data-table-action.action-btn-checklist:hover) {
    background: #eeeaf8;
    color: #594698;
}

:global(.data-table-action.action-btn-expenses) {
    background: #f4f9f5;
    color: #557d60;
}

:global(.data-table-action.action-btn-expenses:hover) {
    background: #eaf5ec;
    color: #83ac8e;
}

@media (max-width: 900px) {
    .race-location-cell {
        max-width: 180px;
    }
}
</style>