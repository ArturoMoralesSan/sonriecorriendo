<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import Swal from 'sweetalert2';
import { ref } from 'vue';
import {
    Edit,
    Package,
    Plus,
    Search,
    ShoppingBag,
    Trash2,
} from 'lucide-vue-next';

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

const submitSearch = () => {
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

const getImageUrl = (image: string | null) => {
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

const formatPrice = (price: string | number) => {
    return new Intl.NumberFormat('es-MX', {
        style: 'currency',
        currency: 'MXN',
    }).format(Number(price));
};

const deleteProduct = (product: Product) => {
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
</script>

<template>
    <Head title="Productos" />

    <div class="admin-page">
        <!-- =================================================
             HEADER
        ================================================== -->

        <header class="admin-page-header">
            <div>
                <p class="admin-page-eyebrow">
                    Catálogo
                </p>

                <h1 class="admin-page-title">
                    Productos
                </h1>

                <p class="admin-page-subtitle">
                    Administra los productos y artículos de Sonríe Corriendo.
                </p>
            </div>

            <div class="admin-page-header-actions">
                <Link
                    :href="admin.products.create().url"
                    class="admin-btn admin-btn-primary"
                >
                    <Plus
                        :size="14"
                        :stroke-width="2"
                    />

                    Nuevo producto
                </Link>
            </div>
        </header>

        <!-- =================================================
             TABLE CARD
        ================================================== -->

        <div class="admin-table-card">
            <!-- TOOLBAR -->

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
                            placeholder="Buscar producto..."
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
                    {{ products.total }} productos
                </div>
            </div>

            <!-- TABLE -->

            <div class="admin-table-wrapper">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>
                                Producto
                            </th>

                            <th>
                                Tipo
                            </th>

                            <th>
                                Año
                            </th>

                            <th>
                                Precio
                            </th>

                            <th>
                                Stock
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
                            v-for="product in products.data"
                            :key="product.id"
                        >
                            <!-- PRODUCT -->

                            <td>
                                <div class="payment-method-cell">
                                    <div class="payment-method-icon">
                                        <img
                                            v-if="getImageUrl(product.image)"
                                            :src="getImageUrl(product.image)!"
                                            :alt="product.name"
                                            class="product-image"
                                        />

                                        <Package
                                            v-else
                                            :size="18"
                                            :stroke-width="2"
                                        />
                                    </div>

                                    <div class="payment-method-info">
                                        <div class="font-medium">
                                            {{ product.name }}
                                        </div>

                                        <div class="payment-description">
                                            {{ product.slug }}
                                        </div>

                                        <div class="payment-description">
                                            ID: {{ product.id }}
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- TYPE -->

                            <td>
                                <span
                                    v-if="product.type"
                                    class="payment-description"
                                >
                                    {{ product.type }}
                                </span>

                                <span
                                    v-else
                                    class="payment-description"
                                >
                                    Sin tipo
                                </span>
                            </td>

                            <!-- YEAR -->

                            <td>
                                <span
                                    v-if="product.year"
                                    class="payment-description"
                                >
                                    {{ product.year }}
                                </span>

                                <span
                                    v-else
                                    class="payment-description"
                                >
                                    Sin año
                                </span>
                            </td>

                            <!-- PRICE -->

                            <td>
                                <div class="font-medium">
                                    {{ formatPrice(product.price) }}
                                </div>
                            </td>

                            <!-- STOCK -->

                            <td>
                                <span
                                    v-if="product.stock > 0"
                                    class="payment-description"
                                >
                                    {{ product.stock }} unidades
                                </span>

                                <span
                                    v-else
                                    class="payment-description"
                                >
                                    Sin stock
                                </span>
                            </td>

                            <!-- STATUS -->

                            <td>
                                <span
                                    v-if="product.is_active"
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
                            </td>

                            <!-- ACTIONS -->

                            <td>
                                <div class="table-actions">
                                    <Link
                                        :href="admin.products.edit(product.id).url"
                                        class="action-btn action-btn-edit"
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
                                        @click="deleteProduct(product)"
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

                        <!-- =================================================
                             EMPTY
                        ================================================== -->

                        <tr v-if="products.data.length === 0">
                            <td
                                colspan="7"
                                class="admin-table-empty"
                            >
                                <div class="empty-state">
                                    <div class="empty-state-icon">
                                        <ShoppingBag
                                            :size="22"
                                            :stroke-width="1.8"
                                        />
                                    </div>

                                    <div>
                                        <strong>
                                            No se encontraron productos.
                                        </strong>

                                        <p>
                                            Intenta realizar una búsqueda
                                            diferente.
                                        </p>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- =================================================
                 PAGINATION
            ================================================== -->

            <div
                v-if="products.last_page > 1"
                class="admin-pagination-wrapper"
            >
                <div class="admin-pagination-info">
                    Mostrando
                    <strong>{{ products.from ?? 0 }}</strong>
                    a
                    <strong>{{ products.to ?? 0 }}</strong>
                    de
                    <strong>{{ products.total }}</strong>
                    productos
                </div>

                <div class="admin-pagination">
                    <template
                        v-for="(link, index) in products.links"
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
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.product-image {
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