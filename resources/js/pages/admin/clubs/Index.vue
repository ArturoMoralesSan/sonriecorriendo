<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import {
    Building2,
    Edit,
    Eye,
    Mail,
    MapPin,
    Plus,
    Search,
    Trash2,
    Users,
} from 'lucide-vue-next';
import Swal from 'sweetalert2';
import { ref } from 'vue';

import DataTable from '@/Components/Admin/DataTable.vue';
import PageHeader from '@/Components/Admin/PageHeader.vue';
import admin from '@/routes/admin';

interface Club {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    logo: string | null;
    responsible: string | null;
    phone: string | null;
    email: string | null;
    city: string | null;
    address: string | null;
    is_active: boolean;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface ClubsPagination {
    data: Club[];
    links: PaginationLink[];
    current_page: number;
    last_page: number;
    from: number | null;
    to: number | null;
    total: number;
}

const props = defineProps<{
    clubs: ClubsPagination;
    filters?: {
        search?: string;
    };
}>();

const search = ref(props.filters?.search ?? '');

const submitSearch = (): void => {
    router.get(
        admin.clubs.index().url,
        {
            search: search.value || undefined,
        },
        {
            preserveState: true,
            replace: true,
        }
    );
};

const getLogoUrl = (
    logo: string | null
): string | null => {
    if (!logo) {
        return null;
    }

    if (
        logo.startsWith('http://') ||
        logo.startsWith('https://')
    ) {
        return logo;
    }

    return `/storage/${logo}`;
};

const deleteClub = (club: Club): void => {
    Swal.fire({
        title: '¿Eliminar club?',
        text: `Se eliminará a "${club.name}". Esta acción no se puede deshacer.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        reverseButtons: true,
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(
                admin.clubs.destroy(club.id).url,
                {
                    preserveScroll: true,
                }
            );
        }
    });
};

/* =========================================================
   COLUMNS
   ========================================================= */

const columns = [
    {
        key: 'club',
        label: 'Club',
    },
    {
        key: 'responsible',
        label: 'Responsable',
    },
    {
        key: 'contact',
        label: 'Contacto',
    },
    {
        key: 'city',
        label: 'Ciudad',
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
        label: 'Detalle',
        icon: Eye,
        class: 'action-btn-detail',
        href: (
            club: Record<string, any>
        ): string =>
            admin.clubs.show(club.id).url,
    },
    {
        key: 'edit',
        label: 'Editar',
        icon: Edit,
        class: 'action-btn-edit',
        href: (
            club: Record<string, any>
        ): string =>
            admin.clubs.edit(club.id).url,
    },
    {
        key: 'delete',
        label: 'Eliminar',
        icon: Trash2,
        class: 'action-btn-delete',
        onClick: (
            club: Record<string, any>
        ): void => {
            deleteClub(club as Club);
        },
    },
];
</script>

<template>
    <Head title="Clubes" />

    <div class="admin-page">
        <PageHeader
            eyebrow="Organización"
            title="Clubes"
            subtitle="Administra los clubes registrados en Sonríe Corriendo."
            :actions="[
                {
                    label: 'Nuevo club',
                    href: admin.clubs.create().url,
                    icon: Plus,
                    variant: 'primary',
                },
            ]"
        />

        <DataTable
            :columns="columns"
            :pagination="clubs"
            :actions="actions"
            :search="search"
            search-placeholder="Buscar club..."
            counter-label="clubes"
            empty-title="No se encontraron clubes."
            empty-description="Intenta realizar una búsqueda diferente."
            :search-icon="Search"
            :empty-icon="Users"
            @update:search="search = $event"
            @search="submitSearch"
        >
            <!-- =================================================
                 CLUB
            ================================================== -->

            <template #cell-club="{ row }">
                <div class="payment-method-cell">
                    <div class="payment-method-icon">
                        <img
                            v-if="getLogoUrl(row.logo)"
                            :src="getLogoUrl(row.logo)!"
                            :alt="row.name"
                            class="club-logo"
                        />

                        <Building2
                            v-else
                            :size="18"
                            :stroke-width="2"
                        />
                    </div>

                    <div class="payment-method-info">
                        <div class="font-medium">
                            {{ row.name }}
                        </div>

                        <div class="payment-description">
                            {{ row.slug }}
                        </div>

                        <div class="payment-description">
                            ID: {{ row.id }}
                        </div>
                    </div>
                </div>
            </template>

            <!-- =================================================
                 RESPONSABLE
            ================================================== -->

            <template #cell-responsible="{ row }">
                <div
                    v-if="row.responsible"
                    class="payment-method-info"
                >
                    <div class="font-medium">
                        {{ row.responsible }}
                    </div>

                    <div
                        v-if="row.email"
                        class="payment-description flex items-center gap-1.5"
                    >
                        <Mail
                            :size="13"
                            :stroke-width="2"
                        />

                        {{ row.email }}
                    </div>
                </div>

                <span
                    v-else
                    class="payment-description"
                >
                    Sin responsable
                </span>
            </template>

            <!-- =================================================
                 CONTACTO
            ================================================== -->

            <template #cell-contact="{ row }">
                <div
                    v-if="row.phone || row.email"
                    class="payment-method-info"
                >
                    <div
                        v-if="row.phone"
                        class="payment-description"
                    >
                        {{ row.phone }}
                    </div>

                </div>

                <span
                    v-else
                    class="payment-description"
                >
                    Sin contacto
                </span>
            </template>

            <!-- =================================================
                 CIUDAD
            ================================================== -->

            <template #cell-city="{ row }">
                <div
                    v-if="row.city"
                    class="payment-description flex items-center gap-1.5"
                >
                    <MapPin
                        :size="13"
                        :stroke-width="2"
                    />

                    {{ row.city }}
                </div>

                <span
                    v-else
                    class="payment-description"
                >
                    Sin ciudad
                </span>
            </template>

            <!-- =================================================
                 ESTADO
            ================================================== -->

            <template #cell-status="{ row }">
                <span
                    v-if="row.is_active"
                    class="status-badge status-active"
                >
                    Activo
                </span>

                <span
                    v-else
                    class="status-badge status-inactive"
                >
                    Inactivo
                </span>
            </template>
        </DataTable>
    </div>
</template>

<style scoped>
.club-logo {
    width: 40px;
    height: 40px;
    object-fit: contain;
    border-radius: 10px;
    background: #ffffff;
}

.payment-method-icon {
    overflow: hidden;
}
</style>