<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import {
    Edit,
    Image,
    Plus,
    Search,
    Trash2,
} from 'lucide-vue-next';
import Swal from 'sweetalert2';
import { ref } from 'vue';

import DataTable from '@/Components/Admin/DataTable.vue';
import PageHeader from '@/Components/Admin/PageHeader.vue';
import admin from '@/routes/admin';

interface Banner {
    id: number;
    name: string;
    page: string;
    title: string | null;
    description: string | null;
    image: string;
    mobile_image: string | null;
    button_text: string | null;
    button_url: string | null;
    is_active: boolean;
    sort_order: number;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface BannersPagination {
    data: Banner[];
    links: PaginationLink[];
    current_page: number;
    last_page: number;
    from: number | null;
    to: number | null;
    total: number;
}

const props = defineProps<{
    banners: BannersPagination;
    filters?: {
        search?: string;
    };
}>();

const search = ref(props.filters?.search ?? '');

const submitSearch = (): void => {
    router.get(
        admin.banners.index().url,
        {
            search: search.value || undefined,
        },
        {
            preserveState: true,
            replace: true,
        }
    );
};

const getImageUrl = (
    image: string | null
): string | null => {
    if (!image) {
        return null;
    }

    if (
        image.startsWith('http://') ||
        image.startsWith('https://')
    ) {
        return image;
    }

    return `/storage/${image}`;
};

const deleteBanner = (banner: Banner): void => {
    Swal.fire({
        title: '¿Eliminar banner?',
        text: `Se eliminará "${banner.name}". Esta acción no se puede deshacer.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        reverseButtons: true,
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(
                admin.banners.destroy(banner.id).url,
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
        key: 'banner',
        label: 'Banner',
    },
    {
        key: 'page',
        label: 'Página',
    },
    {
        key: 'title',
        label: 'Título',
    },
    {
        key: 'sort_order',
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
        key: 'edit',
        label: 'Editar',
        icon: Edit,
        class: 'action-btn-edit',
        href: (
            banner: Record<string, any>
        ): string =>
            admin.banners.edit(banner.id).url,
    },
    {
        key: 'delete',
        label: 'Eliminar',
        icon: Trash2,
        class: 'action-btn-delete',
        onClick: (
            banner: Record<string, any>
        ): void => {
            deleteBanner(banner as Banner);
        },
    },
];
</script>

<template>
    <Head title="Banners" />

    <div class="admin-page">
        <PageHeader
            eyebrow="Contenido"
            title="Banners"
            subtitle="Administra los banners promocionales disponibles en el sitio."
            :actions="[
                {
                    label: 'Nuevo banner',
                    href: admin.banners.create().url,
                    icon: Plus,
                    variant: 'primary',
                },
            ]"
        />

        <DataTable
            :columns="columns"
            :pagination="banners"
            :actions="actions"
            :search="search"
            search-placeholder="Buscar banner..."
            counter-label="banners"
            empty-title="No se encontraron banners."
            empty-description="Intenta realizar una búsqueda diferente."
            :search-icon="Search"
            :empty-icon="Image"
            @update:search="search = $event"
            @search="submitSearch"
        >
            <!-- =================================================
                 BANNER
            ================================================== -->

            <template #cell-banner="{ row }">
                <div class="banner-cell">
                    <div class="banner-image-wrapper">
                        <img
                            v-if="getImageUrl(row.image)"
                            :src="getImageUrl(row.image)!"
                            :alt="row.name"
                            class="banner-image"
                        />

                        <Image
                            v-else
                            :size="18"
                            :stroke-width="2"
                        />
                    </div>

                    <div class="banner-info">
                        <div class="font-medium">
                            {{ row.name }}
                        </div>

                        <div
                            v-if="row.description"
                            class="banner-description"
                        >
                            {{ row.description }}
                        </div>

                        <div class="banner-description">
                            ID: {{ row.id }}
                        </div>
                    </div>
                </div>
            </template>

            <!-- =================================================
                 PÁGINA
            ================================================== -->

            <template #cell-page="{ row }">
                <span class="page-badge">
                    {{ row.page }}
                </span>
            </template>

            <!-- =================================================
                 TÍTULO
            ================================================== -->

            <template #cell-title="{ row }">
                <span
                    v-if="row.title"
                    class="banner-title"
                >
                    {{ row.title }}
                </span>

                <span
                    v-else
                    class="banner-description"
                >
                    Sin título
                </span>
            </template>

            <!-- =================================================
                 ORDEN
            ================================================== -->

            <template #cell-sort_order="{ row }">
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
.banner-cell {
    display: flex;
    align-items: center;
    gap: 12px;
    min-width: 260px;
}

.banner-image-wrapper {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 58px;
    height: 40px;
    overflow: hidden;
    flex: 0 0 auto;
    border: 1px solid #e5eaee;
    border-radius: 8px;
    background: #f8fafb;
    color: #91a0aa;
}

.banner-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.banner-info {
    min-width: 0;
}

.banner-description {
    margin-top: 3px;
    color: var(--sc-page-text-secondary);
    font-size: 11px;
}

.banner-title {
    color: var(--sc-page-text);
    font-size: 12px;
    font-weight: 600;
}

.page-badge {
    display: inline-flex;
    align-items: center;
    padding: 5px 9px;
    border: 1px solid #dce9f1;
    border-radius: 7px;
    background: #f3f9fc;
    color: #1769a8;
    font-size: 11px;
    font-weight: 600;
    text-transform: capitalize;
}

.order-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 28px;
    height: 26px;
    padding: 0 7px;
    border: 1px solid #e5eaee;
    border-radius: 7px;
    background: #f8fafb;
    color: #667782;
    font-size: 11px;
    font-weight: 700;
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
