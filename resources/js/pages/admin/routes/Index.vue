<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import {
    Edit,
    Eye,
    Files,
    Plus,
    Search,
    Trash2,
    Route as RouteIcon,
} from 'lucide-vue-next';
import Swal from 'sweetalert2';
import { ref } from 'vue';

import DataTable from '@/Components/Admin/DataTable.vue';
import PageHeader from '@/Components/Admin/PageHeader.vue';
import admin from '@/routes/admin';

interface RouteMedia {
    id: number;
    route_id: number;
    type: 'image' | 'video';
    file: string;
    title: string | null;
    sort_order: number;
}

interface RouteItem {
    id: number;
    title: string;
    description: string | null;
    is_active: boolean;
    sort_order: number;
    media?: RouteMedia[];
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface RoutesPagination {
    data: RouteItem[];
    links: PaginationLink[];
    current_page: number;
    last_page: number;
    from: number | null;
    to: number | null;
    total: number;
}

const props = defineProps<{
    routes: RoutesPagination;
    filters?: {
        search?: string;
    };
}>();

const search = ref(props.filters?.search ?? '');

const submitSearch = (): void => {
    router.get(
        admin.routes.index().url,
        {
            search: search.value || undefined,
        },
        {
            preserveState: true,
            replace: true,
        }
    );
};

const getMediaCount = (
    route: RouteItem
): number => {
    return route.media?.length ?? 0;
};

const getImageCount = (
    route: RouteItem
): number => {
    return (
        route.media?.filter(
            (media) => media.type === 'image'
        ).length ?? 0
    );
};

const getVideoCount = (
    route: RouteItem
): number => {
    return (
        route.media?.filter(
            (media) => media.type === 'video'
        ).length ?? 0
    );
};

const deleteRoute = (
    route: RouteItem
): void => {
    Swal.fire({
        title: '¿Eliminar ruta?',
        text: `Se eliminará "${route.title}" junto con todas sus fotos y videos. Esta acción no se puede deshacer.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        reverseButtons: true,
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(
                admin.routes.destroy(route.id).url,
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
        key: 'route',
        label: 'Ruta',
    },
    {
        key: 'media',
        label: 'Archivos',
    },
    {
        key: 'order',
        label: 'Orden',
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
            route: Record<string, any>
        ): string =>
            admin.routes.show(route.id).url,
    },
    {
        key: 'edit',
        label: 'Editar',
        icon: Edit,
        class: 'action-btn-edit',
        href: (
            route: Record<string, any>
        ): string =>
            admin.routes.edit(route.id).url,
    },
    {
        key: 'delete',
        label: 'Eliminar',
        icon: Trash2,
        class: 'action-btn-delete',
        onClick: (
            route: Record<string, any>
        ): void => {
            deleteRoute(route as RouteItem);
        },
    },
];
</script>

<template>
    <Head title="Rutas" />

    <div class="admin-page">
        <PageHeader
            eyebrow="Contenido"
            title="Rutas"
            subtitle="Administra las rutas y su contenido multimedia."
            :actions="[
                {
                    label: 'Nueva ruta',
                    href: admin.routes.create().url,
                    icon: Plus,
                    variant: 'primary',
                },
            ]"
        />

        <DataTable
            :columns="columns"
            :pagination="routes"
            :actions="actions"
            :search="search"
            search-placeholder="Buscar ruta..."
            counter-label="rutas"
            empty-title="No se encontraron rutas."
            empty-description="Intenta realizar una búsqueda diferente."
            :search-icon="Search"
            :empty-icon="RouteIcon"
            @update:search="search = $event"
            @search="submitSearch"
        >
            <!-- =================================================
                 RUTA
            ================================================== -->

            <template #cell-route="{ row }">
                <div class="route-info">
                    <div class="font-medium">
                        {{ row.title }}
                    </div>

                    <div
                        v-if="row.description"
                        class="route-description"
                    >
                        {{ row.description }}
                    </div>

                    <div class="route-id">
                        ID: {{ row.id }}
                    </div>
                </div>
            </template>

            <!-- =================================================
                 ARCHIVOS
            ================================================== -->

            <template #cell-media="{ row }">
                <div class="media-count">
                    <div class="media-count-main">
                        <Files
                            :size="15"
                            :stroke-width="2"
                        />

                        <strong>
                            {{ getMediaCount(row) }}
                        </strong>

                        <span>
                            {{
                                getMediaCount(row) === 1
                                    ? 'archivo'
                                    : 'archivos'
                            }}
                        </span>
                    </div>
                </div>
            </template>

            <!-- =================================================
                 ORDEN
            ================================================== -->

            <template #cell-order="{ row }">
                <span class="order-badge">
                    {{ row.sort_order }}
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
.route-info {
    min-width: 0;
}

.route-description {
    max-width: 420px;
    margin-top: 2px;
    overflow: hidden;
    color: #64748b;
    font-size: 12px;
    line-height: 1.4;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.route-id {
    margin-top: 2px;
    color: #94a3b8;
    font-size: 11px;
}

.media-count {
    display: flex;
    flex-direction: column;
    gap: 3px;
}

.media-count-main {
    display: flex;
    align-items: center;
    gap: 5px;
    color: #1769a8;
    font-size: 12px;
}

.media-count-main strong {
    font-size: 13px;
    font-weight: 700;
}

.media-count-main span {
    color: #475569;
    font-weight: 500;
}

.media-count-detail {
    color: #94a3b8;
    font-size: 11px;
}

.order-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 30px;
    height: 28px;
    padding: 0 8px;
    border-radius: 8px;
    background: #eaf6fc;
    color: #1769a8;
    font-size: 12px;
    font-weight: 700;
}
</style>