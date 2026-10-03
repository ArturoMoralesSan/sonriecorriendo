<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import {
    Building2,
    Edit,
    Eye,
    Globe,
    Mail,
    Plus,
    Search,
    Trash2,
    UserRound,
} from 'lucide-vue-next';
import Swal from 'sweetalert2';
import { ref } from 'vue';

import DataTable from '@/Components/Admin/DataTable.vue';
import PageHeader from '@/Components/Admin/PageHeader.vue';
import admin from '@/routes/admin';

interface Sponsor {
    id: number;
    name: string;
    slug: string;
    logo: string | null;
    description: string | null;
    contact_name: string | null;
    email: string | null;
    phone: string | null;
    website: string | null;
    is_active: boolean;
    race_sponsors_count: number;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface SponsorsPagination {
    data: Sponsor[];
    links: PaginationLink[];
    current_page: number;
    last_page: number;
    from: number | null;
    to: number | null;
    total: number;
}

const props = defineProps<{
    sponsors: SponsorsPagination;
    filters: {
        search?: string;
    };
}>();

const search = ref(props.filters?.search ?? '');

const submitSearch = (): void => {
    router.get(
        admin.sponsors.index().url,
        {
            search: search.value || undefined,
        },
        {
            preserveState: true,
            replace: true,
        },
    );
};

const deleteSponsor = async (
    sponsor: Sponsor,
): Promise<void> => {
    const result = await Swal.fire({
        title: '¿Eliminar patrocinador?',
        text: `Se eliminará el patrocinador "${sponsor.name}". Esta acción no se puede deshacer.`,
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
        admin.sponsors.destroy(sponsor.id).url,
        {
            preserveScroll: true,

            onSuccess: () => {
                Swal.fire({
                    title: 'Eliminado',
                    text: 'El patrocinador se eliminó correctamente.',
                    icon: 'success',
                    timer: 1800,
                    showConfirmButton: false,
                });
            },

            onError: () => {
                Swal.fire({
                    title: 'Error',
                    text: 'No se pudo eliminar el patrocinador.',
                    icon: 'error',
                    confirmButtonText: 'Aceptar',
                });
            },
        },
    );
};

const getLogoUrl = (
    logo: string | null,
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

const formatWebsite = (
    website: string | null,
): string => {
    if (!website) {
        return '—';
    }

    return website
        .replace(/^https?:\/\//, '')
        .replace(/\/$/, '');
};

const defineStatusClass = (
    isActive: boolean,
): string => {
    return isActive
        ? 'status-badge status-active'
        : 'status-badge status-inactive';
};

const defineStatusLabel = (
    isActive: boolean,
): string => {
    return isActive
        ? 'Activo'
        : 'Inactivo';
};

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Panel',
                href: admin.dashboard(),
            },
            {
                title: 'Patrocinadores',
                href: admin.sponsors.index(),
            },
        ],
    },
});

/* =========================================================
   COLUMNS
========================================================= */

const columns = [
    {
        key: 'sponsor',
        label: 'Patrocinador',
    },
    {
        key: 'contact',
        label: 'Contacto',
    },
    {
        key: 'website',
        label: 'Sitio web',
    },
    {
        key: 'races',
        label: 'Carreras',
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
        class: 'action-btn-view',
        href: (
            sponsor: Record<string, any>,
        ): string =>
            admin.sponsors.show(sponsor.id).url,
    },
    {
        key: 'edit',
        label: 'Editar',
        icon: Edit,
        class: 'action-btn-edit',
        href: (
            sponsor: Record<string, any>,
        ): string =>
            admin.sponsors.edit(sponsor.id).url,
    },
    {
        key: 'delete',
        label: 'Eliminar',
        icon: Trash2,
        class: 'action-btn-delete',
        onClick: (
            sponsor: Record<string, any>,
        ): void => {
            deleteSponsor(sponsor as Sponsor);
        },
    },
];
</script>

<template>
    <Head title="Patrocinadores" />

    <div class="admin-page">
        <!-- =================================================
             HEADER
        ================================================== -->

        <PageHeader
            eyebrow="Carreras"
            title="Patrocinadores"
            subtitle="Administra los patrocinadores disponibles para las carreras."
            :actions="[
                {
                    label: 'Nuevo patrocinador',
                    href: admin.sponsors.create().url,
                    icon: Plus,
                    variant: 'primary',
                },
            ]"
        />

        <!-- =================================================
             TABLE
        ================================================== -->

        <DataTable
            :columns="columns"
            :pagination="sponsors"
            :actions="actions"
            :search="search"
            search-placeholder="Buscar patrocinador..."
            counter-label="patrocinadores"
            empty-title="No hay patrocinadores"
            :empty-description="
                search
                    ? 'No se encontraron patrocinadores con ese término de búsqueda.'
                    : 'Todavía no has registrado ningún patrocinador.'
            "
            :search-icon="Search"
            :empty-icon="Building2"
            @update:search="search = $event"
            @search="submitSearch"
        >
            <!-- =================================================
                 PATROCINADOR
            ================================================== -->

            <template #cell-sponsor="{ row }">
                <div class="payment-method-cell">
                    <div
                        class="payment-method-icon sponsor-logo"
                    >
                        <img
                            v-if="getLogoUrl(row.logo)"
                            :src="getLogoUrl(row.logo) ?? undefined"
                            :alt="`Logo de ${row.name}`"
                        />

                        <Building2
                            v-else
                            :size="17"
                            :stroke-width="2"
                        />
                    </div>

                    <div class="payment-method-info">
                        <strong>
                            {{ row.name }}
                        </strong>

                        <span>
                            ID: {{ row.id }}
                        </span>
                    </div>
                </div>
            </template>

            <!-- =================================================
                 CONTACTO
            ================================================== -->

            <template #cell-contact="{ row }">
                <div
                    v-if="
                        row.contact_name ||
                        row.email
                    "
                    class="sponsor-contact"
                >
                    <div
                        v-if="row.contact_name"
                        class="contact-line contact-primary"
                    >
                        <UserRound
                            :size="12"
                            :stroke-width="2"
                        />

                        {{ row.contact_name }}
                    </div>

                    <div
                        v-if="row.email"
                        class="contact-line"
                    >

                    </div>
                </div>

                <div
                    v-else
                    class="payment-description empty"
                >
                    Sin contacto
                </div>
            </template>

            <!-- =================================================
                 SITIO WEB
            ================================================== -->

            <template #cell-website="{ row }">
                <a
                    v-if="row.website"
                    :href="row.website"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="website-link"
                    @click.stop
                >
                    <Globe
                        :size="13"
                        :stroke-width="2"
                    />

                    {{
                        formatWebsite(
                            row.website,
                        )
                    }}
                </a>

                <div
                    v-else
                    class="payment-description empty"
                >
                    Sin sitio web
                </div>
            </template>

            <!-- =================================================
                 CARRERAS
            ================================================== -->

            <template #cell-races="{ row }">
                <span class="order-badge">
                    {{ row.race_sponsors_count }}
                </span>
            </template>

            <!-- =================================================
                 ESTADO
            ================================================== -->

            <template #cell-status="{ row }">
                <span
                    :class="
                        defineStatusClass(
                            row.is_active,
                        )
                    "
                >
                    {{
                        defineStatusLabel(
                            row.is_active,
                        )
                    }}
                </span>
            </template>
        </DataTable>
    </div>
</template>

<style scoped>
.sponsor-logo {
    overflow: hidden;
}

.sponsor-logo img {
    width: 100%;
    height: 100%;
    object-fit: contain;
    border-radius: 8px;
}

.sponsor-contact {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.contact-line {
    display: flex;
    align-items: center;
    gap: 5px;
    color: #64748b;
    font-size: 10px;
    line-height: 1.35;
}

.contact-primary {
    color: #172b4d;
    font-size: 12px;
    font-weight: 600;
}

.website-link {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    max-width: 190px;
    color: #1769a8;
    font-size: 11px;
    font-weight: 600;
    text-decoration: none;
}

.website-link:hover {
    text-decoration: underline;
}

.payment-description.empty {
    color: #94a3b8;
}
</style>