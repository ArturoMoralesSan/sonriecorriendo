<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import {
    Edit,
    Package,
    Plus,
    Search,
    ShoppingBag,
    Trash2,
} from 'lucide-vue-next';
import Swal from 'sweetalert2';
import { ref } from 'vue';

import DataTable from '@/Components/Admin/DataTable.vue';
import PageHeader from '@/Components/Admin/PageHeader.vue';
import admin from '@/routes/admin';

interface Product {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    image: string | null;
    type: string | null;
    year: number | null;
    price: string | number;
    stock: number;
    is_active: boolean;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface ProductsPagination {
    data: Product[];
    links: PaginationLink[];
    current_page: number;
    last_page: number;
    from: number | null;
    to: number | null;
    total: number;
}

const props = defineProps<{
    products: ProductsPagination;
    filters?: {
        search?: string;
    };
}>();

const search = ref(props.filters?.search ?? '');

const submitSearch = (): void => {
    router.get(
        admin.products.index().url,
        {
            search: search.value || undefined,
        },
        {
            preserveState: true,
            replace: true,
        },
    );
};

const getImageUrl = (
    image: string | null,
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

const formatPrice = (
    price: string | number,
): string => {
    return new Intl.NumberFormat('es-MX', {
        style: 'currency',
        currency: 'MXN',
    }).format(Number(price));
};

const deleteProduct = (
    product: Product,
): void => {
    Swal.fire({
        title: '¿Eliminar producto?',
        text: `Se eliminará "${product.name}". Esta acción no se puede deshacer.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        reverseButtons: true,
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(
                admin.products.destroy(product.id).url,
                {
                    preserveScroll: true,
                },
            );
        }
    });
};

const columns = [
    {
        key: 'product',
        label: 'Producto',
    },
    {
        key: 'type',
        label: 'Tipo',
    },
    {
        key: 'year',
        label: 'Año',
    },
    {
        key: 'price',
        label: 'Precio',
    },
    {
        key: 'stock',
        label: 'Stock',
    },
    {
        key: 'status',
        label: 'Estado',
    },
];

const actions = [
    {
        key: 'edit',
        label: 'Editar',
        icon: Edit,
        class: 'action-btn-edit',
        href: (product: Record<string, any>) =>
            admin.products.edit(product.id).url,
    },
    {
        key: 'delete',
        label: 'Eliminar',
        icon: Trash2,
        class: 'action-btn-delete',
        onClick: (product: Record<string, any>) =>
            deleteProduct(product as Product),
    },
];
</script>

<template>
    <Head title="Productos" />

    <div class="admin-page">
        <PageHeader
            eyebrow="Catálogo"
            title="Productos"
            subtitle="Administra los productos y artículos de Sonríe Corriendo."
            :actions="[
                {
                    label: 'Nuevo producto',
                    href: admin.products.create().url,
                    icon: Plus,
                    variant: 'primary',
                },
            ]"
        />

        <DataTable
            :columns="columns"
            :pagination="products"
            :actions="actions"
            :search="search"
            search-placeholder="Buscar producto..."
            counter-label="productos"
            empty-title="No se encontraron productos."
            empty-description="Intenta realizar una búsqueda diferente."
            :search-icon="Search"
            :empty-icon="ShoppingBag"
            @update:search="search = $event"
            @search="submitSearch"
        >
            <!-- PRODUCTO -->
            <template #cell-product="{ row }">
                <div class="product-cell">
                    <div class="product-icon">
                        <img
                            v-if="getImageUrl(row.image)"
                            :src="getImageUrl(row.image)!"
                            :alt="row.name"
                            class="product-image"
                        />

                        <Package
                            v-else
                            :size="18"
                            :stroke-width="2"
                        />
                    </div>

                    <div class="product-info">
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

            <!-- TIPO -->
            <template #cell-type="{ row }">
                <span class="payment-description">
                    {{ row.type || 'Sin tipo' }}
                </span>
            </template>

            <!-- AÑO -->
            <template #cell-year="{ row }">
                <span class="payment-description">
                    {{ row.year || 'Sin año' }}
                </span>
            </template>

            <!-- PRECIO -->
            <template #cell-price="{ row }">
                <div class="font-medium">
                    {{ formatPrice(row.price) }}
                </div>
            </template>

            <!-- STOCK -->
            <template #cell-stock="{ row }">
                <span class="payment-description">
                    {{
                        row.stock > 0
                            ? `${row.stock} unidades`
                            : 'Sin stock'
                    }}
                </span>
            </template>

            <!-- ESTADO -->
            <template #cell-status="{ row }">
                <span
                    class="status-badge"
                    :class="
                        row.is_active
                            ? 'status-active'
                            : 'status-inactive'
                    "
                >
                    {{
                        row.is_active
                            ? 'Activo'
                            : 'Inactivo'
                    }}
                </span>
            </template>
        </DataTable>
    </div>
</template>

<style scoped>
.product-cell {
    display: flex;
    align-items: center;
    gap: 11px;
}

.product-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    flex: 0 0 40px;
    width: 40px;
    height: 40px;
    overflow: hidden;
    border-radius: 10px;
    background: var(--sc-page-light);
    color: var(--sc-page-blue);
}

.product-image {
    width: 40px;
    height: 40px;
    object-fit: contain;
    border-radius: 10px;
    background: #ffffff;
}

.product-info {
    display: flex;
    flex-direction: column;
    min-width: 0;
    gap: 3px;
}

.font-medium {
    overflow: hidden;
    color: var(--sc-page-text);
    font-size: 13px;
    font-weight: 700;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.payment-description {
    overflow: hidden;
    color: #718096;
    font-size: 11px;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 5px 9px;
    border-radius: 7px;
    font-size: 10px;
    font-weight: 700;
    white-space: nowrap;
}

.status-active {
    background: var(--sc-page-green-light);
    color: #159c83;
}

.status-inactive {
    background: #feecee;
    color: #c93645;
}
</style>