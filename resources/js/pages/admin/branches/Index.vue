
<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import {
    Building2,
    Clock,
    Edit,
    Eye,
    MapPin,
    Plus,
    Search,
} from 'lucide-vue-next';
import { ref } from 'vue';

import DataTable from '@/Components/Admin/DataTable.vue';
import PageHeader from '@/Components/Admin/PageHeader.vue';
import admin from '@/routes/admin';

interface Branch {
    id: number;
    name: string;
    slug: string;
    street: string;
    exterior_number: string;
    interior_number: string | null;
    neighborhood: string;
    postal_code: string;
    city: string;
    state: string;
    phone: string | null;
    opening_time: string | null;
    closing_time: string | null;
    opening_hours: string | null;
    is_active: boolean;
    delivery_addresses_count?: number;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface BranchesPagination {
    data: Branch[];
    links: PaginationLink[];
    current_page: number;
    last_page: number;
    from: number | null;
    to: number | null;
    total: number;
}

const props = defineProps<{
    branches: BranchesPagination;
    filters?: {
        search?: string;
    };
}>();

const search = ref(props.filters?.search ?? '');

const submitSearch = (): void => {
    router.get(
        admin.branches.index().url,
        {
            search: search.value || undefined,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    );
};

const formatAddress = (branch: Branch): string => {
    const interior = branch.interior_number
        ? `, Int. ${branch.interior_number}`
        : '';

    return `${branch.street} ${branch.exterior_number}${interior}, ${branch.neighborhood}, C.P. ${branch.postal_code}, ${branch.city}, ${branch.state}`;
};

const formatSchedule = (branch: Branch): string => {
    if (branch.opening_time && branch.closing_time) {
        return `${branch.opening_time.slice(0, 5)} - ${branch.closing_time.slice(0, 5)}`;
    }

    return branch.opening_hours || 'Sin horario registrado';
};

/* =========================================================
   COLUMNS
   ========================================================= */

const columns = [
    {
        key: 'branch',
        label: 'Sucursal',
    },
    {
        key: 'address',
        label: 'Dirección',
    },
    {
        key: 'phone',
        label: 'Teléfono',
    },
    {
        key: 'schedule',
        label: 'Horario',
    },
    {
        key: 'status',
        label: 'Estado',
    },
];

/* =========================================================
   ACTIONS
   ========================================================= */

const actions = [
    {
        key: 'show',
        label: 'Ver',
        icon: Eye,
        class: 'action-btn-view',
        href: (branch: Record<string, any>): string =>
            admin.branches.show(branch.id).url,
    },
    {
        key: 'edit',
        label: 'Editar',
        icon: Edit,
        class: 'action-btn-edit',
        href: (branch: Record<string, any>): string =>
            admin.branches.edit(branch.id).url,
    },
];
</script>

<template>
    <Head title="Sucursales" />

    <div class="admin-page">
        <PageHeader
            eyebrow="Operación"
            title="Sucursales"
            subtitle="Administra los puntos de entrega y recolección de pedidos."
            :actions="[
                {
                    label: 'Nueva sucursal',
                    href: admin.branches.create().url,
                    icon: Plus,
                    variant: 'primary',
                },
            ]"
        />

        <DataTable
            :columns="columns"
            :pagination="branches"
            :actions="actions"
            :search="search"
            search-placeholder="Buscar sucursal..."
            counter-label="sucursales"
            empty-title="No se encontraron sucursales."
            empty-description="Intenta realizar una búsqueda diferente."
            :search-icon="Search"
            :empty-icon="Building2"
            @update:search="search = $event"
            @search="submitSearch"
        >
            <!-- =================================================
                 SUCURSAL
            ================================================== -->

            <template #cell-branch="{ row }">
                <div class="branch-cell">
                    <div class="branch-icon">
                        <Building2 :size="19" :stroke-width="2" />
                    </div>

                    <div class="branch-info">
                        <div class="font-medium">
                            {{ row.name }}
                        </div>

                        <div class="branch-description">
                            ID: {{ row.id }}
                        </div>
                    </div>
                </div>
            </template>

            <!-- =================================================
                 DIRECCIÓN
            ================================================== -->

            <template #cell-address="{ row }">
                <div class="address-cell">
                    <MapPin :size="16" />

                    <span>{{ formatAddress(row as Branch) }}</span>
                </div>
            </template>

            <!-- =================================================
                 TELÉFONO
            ================================================== -->

            <template #cell-phone="{ row }">
                <div v-if="row.phone" class="phone-cell">
                    {{ row.phone }}
                </div>

                <span v-else class="branch-description">
                    Sin teléfono
                </span>
            </template>

            <!-- =================================================
                 HORARIO
            ================================================== -->

            <template #cell-schedule="{ row }">
                <div class="schedule-cell">
                    <Clock :size="15" />
                    <span>{{ formatSchedule(row as Branch) }}</span>
                </div>
            </template>

            <!-- =================================================
                 ESTADO
            ================================================== -->

            <template #cell-status="{ row }">
                <span
                    v-if="row.is_active"
                    class="status-badge status-active"
                >
                    Activa
                </span>

                <span
                    v-else
                    class="status-badge status-inactive"
                >
                    Inactiva
                </span>
            </template>
        </DataTable>
    </div>
</template>

<style scoped>
.branch-cell {
    display: flex;
    align-items: center;
    gap: 12px;
    min-width: 175px;
}

.branch-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 42px;
    height: 42px;
    flex: 0 0 auto;
    border: 1px solid #dce9f1;
    border-radius: 9px;
    background: #f3f9fc;
    color: #1769a8;
}

.branch-info {
    min-width: 0;
}

.branch-description {
    margin-top: 3px;
    color: var(--sc-page-text-secondary);
    font-size: 11px;
}

.address-cell {
    display: flex;
    align-items: flex-start;
    gap: 7px;
    min-width: 210px;
    max-width: 330px;
    color: var(--sc-page-text-secondary);
    font-size: 12px;
    line-height: 1.6;
}

.address-cell svg {
    flex: 0 0 auto;
    margin-top: 2px;
    color: #249edb;
}

.phone-cell {
    color: var(--sc-page-text);
    font-size: 12px;
}

.schedule-cell {
    display: flex;
    align-items: flex-start;
    gap: 7px;
    min-width: 130px;
    color: var(--sc-page-text-secondary);
    font-size: 11px;
    line-height: 1.5;
}

.schedule-cell svg {
    flex: 0 0 auto;
    color: #1769a8;
}

.status-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 5px 9px;
    border-radius: 7px;
    font-size: 11px;
    font-weight: 700;
}

.status-active {
    border: 1px solid #cde9dc;
    background: #effaf4;
    color: #25845b;
}

.status-inactive {
    border: 1px solid #f0d8dc;
    background: #fff4f5;
    color: #c94d59;
}
</style>