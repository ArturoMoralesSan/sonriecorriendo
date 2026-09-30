<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import {
    CalendarDays,
    Eye,
    FileText,
    Plus,
    Search,
    ShoppingCart,
    UserRound,
} from 'lucide-vue-next';
import { ref } from 'vue';

import admin from '@/routes/admin';

interface Customer {
    id: number;
    name: string;
    email: string;
}

interface Product {
    id: number;
    name: string;
    image: string | null;
}

interface SaleItem {
    id: number;
    product_id: number;
    quantity: number;
    unit_price: string | number;
    subtotal: string | number;
    product: Product | null;
}

interface PaymentMethod {
    id: number;
    name: string;
    code: string;
}

interface SalePayment {
    id: number;
    payment_method_id: number;
    amount: string | number;
    reference: string | null;
    paymentMethod: PaymentMethod | null;
}

interface Sale {
    id: number;
    folio: string;
    customer_id: number | null;
    customer: Customer | null;
    subtotal: string | number;
    discount: string | number;
    total: string | number;
    sales_channel: string;
    status: string;
    notes: string | null;
    sold_at: string | null;
    items: SaleItem[];
    payments: SalePayment[];
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface SalesPagination {
    data: Sale[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number | null;
    to: number | null;
    links: PaginationLink[];
}

const props = defineProps<{
    sales: SalesPagination;
    filters: {
        search?: string;
    };
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Panel',
                href: admin.dashboard(),
            },
            {
                title: 'Ventas',
                href: admin.sales.index(),
            },
        ],
    },
});

const search = ref(props.filters.search ?? '');

const submitSearch = (): void => {
    router.get(
        admin.sales.index().url,
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

const clearSearch = (): void => {
    search.value = '';

    router.get(
        admin.sales.index().url,
        {},
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    );
};

const formatCurrency = (value: number | string): string => {
    return Number(value).toLocaleString('es-MX', {
        style: 'currency',
        currency: 'MXN',
    });
};

const formatDate = (value: string | null): string => {
    if (!value) {
        return '—';
    }

    return new Date(value).toLocaleString('es-MX', {
        dateStyle: 'short',
        timeStyle: 'short',
    });
};

const getStatusLabel = (status: string): string => {
    const labels: Record<string, string> = {
        pending: 'Pendiente',
        paid: 'Pagada',
        partially_paid: 'Pago parcial',
        cancelled: 'Cancelada',
        refunded: 'Reembolsada',
    };

    return labels[status] ?? status;
};

const getChannelLabel = (channel: string): string => {
    const labels: Record<string, string> = {
        counter: 'Ventanilla',
        branch: 'Sucursal',
        web: 'Web',
        app: 'App',
    };

    return labels[channel] ?? channel;
};
</script>

<template>
    <Head title="Ventas" />

    <div class="admin-page">
        <!-- =================================================
             HEADER
        ================================================== -->

        <header class="admin-page-header">
            <div>
                <p class="admin-page-eyebrow">
                    Comercio
                </p>

                <h1 class="admin-page-title">
                    Ventas
                </h1>

                <p class="admin-page-subtitle">
                    Consulta y administra las ventas realizadas.
                </p>
            </div>

            <div class="admin-page-header-actions">
                <Link
                    :href="admin.sales.create().url"
                    class="admin-btn admin-btn-primary"
                >
                    <Plus
                        :size="14"
                        :stroke-width="2"
                    />

                    Nueva venta
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
                            placeholder="Buscar por folio, cliente, estado..."
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

                    <button
                        v-if="search"
                        type="button"
                        class="admin-btn admin-btn-secondary"
                        @click="clearSearch"
                    >
                        Limpiar
                    </button>
                </form>

                <div class="admin-table-counter">
                    {{ sales.total }}
                    {{ sales.total === 1 ? 'venta' : 'ventas' }}
                </div>
            </div>

            <!-- TABLE -->

            <div class="admin-table-wrapper">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>
                                Venta
                            </th>

                            <th>
                                Cliente
                            </th>

                            <th>
                                Fecha
                            </th>

                            <th>
                                Canal
                            </th>

                            <th>
                                Total
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
                            v-for="sale in sales.data"
                            :key="sale.id"
                        >
                            <!-- VENTA -->

                            <td>
                                <div class="sale-cell">
                                    <div class="sale-icon">
                                        <FileText
                                            :size="18"
                                            :stroke-width="2"
                                        />
                                    </div>

                                    <div class="sale-info">
                                        <div class="font-medium">
                                            {{ sale.folio }}
                                        </div>

                                        <div class="payment-description">
                                            {{ sale.items.length }}

                                            {{
                                                sale.items.length === 1
                                                    ? 'producto'
                                                    : 'productos'
                                            }}
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- CLIENTE -->

                            <td>
                                <div class="customer-cell">
                                    <div class="customer-icon">
                                        <UserRound
                                            :size="17"
                                            :stroke-width="2"
                                        />
                                    </div>

                                    <div class="customer-info">
                                        <div
                                            v-if="sale.customer"
                                            class="font-medium"
                                        >
                                            {{ sale.customer.name }}
                                        </div>

                                        <div
                                            v-if="sale.customer?.email"
                                            class="payment-description"
                                        >
                                            {{ sale.customer.email }}
                                        </div>

                                        <span
                                            v-else-if="!sale.customer"
                                            class="payment-description"
                                        >
                                            Venta mostrador
                                        </span>
                                    </div>
                                </div>
                            </td>

                            <!-- FECHA -->

                            <td>
                                <div class="date-cell">
                                    <CalendarDays
                                        :size="14"
                                        :stroke-width="2"
                                    />

                                    <span>
                                        {{ formatDate(sale.sold_at) }}
                                    </span>
                                </div>
                            </td>

                            <!-- CANAL -->

                            <td>
                                <span class="channel-badge">
                                    {{ getChannelLabel(sale.sales_channel) }}
                                </span>
                            </td>

                            <!-- TOTAL -->

                            <td>
                                <strong class="sale-total">
                                    {{ formatCurrency(sale.total) }}
                                </strong>

                                <span
                                    v-if="Number(sale.discount) > 0"
                                    class="discount-text"
                                >
                                    Descuento:
                                    {{ formatCurrency(sale.discount) }}
                                </span>
                            </td>

                            <!-- ESTADO -->

                            <td>
                                <span
                                    class="status-badge"
                                    :class="{
                                        'status-active':
                                            sale.status === 'paid',

                                        'status-pending':
                                            sale.status === 'pending'
                                            || sale.status === 'partially_paid',

                                        'status-inactive':
                                            sale.status === 'cancelled'
                                            || sale.status === 'refunded',
                                    }"
                                >
                                    {{ getStatusLabel(sale.status) }}
                                </span>
                            </td>

                            <!-- ACCIONES -->

                            <td>
                                <div class="table-actions">
                                    <Link
                                        :href="admin.sales.show(sale.id).url"
                                        class="action-btn action-btn-view"
                                        title="Ver venta"
                                    >
                                        <Eye
                                            :size="14"
                                            :stroke-width="2"
                                        />
                                    </Link>
                                </div>
                            </td>
                        </tr>

                        <!-- =================================================
                             EMPTY
                        ================================================== -->

                        <tr v-if="sales.data.length === 0">
                            <td
                                colspan="7"
                                class="admin-table-empty"
                            >
                                <div class="empty-state">
                                    <div class="empty-state-icon">
                                        <ShoppingCart
                                            :size="22"
                                            :stroke-width="1.8"
                                        />
                                    </div>

                                    <div>
                                        <strong>
                                            No se encontraron ventas.
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
                v-if="sales.last_page > 1"
                class="admin-pagination-wrapper"
            >
                <div class="admin-pagination-info">
                    Mostrando
                    <strong>{{ sales.from ?? 0 }}</strong>
                    a
                    <strong>{{ sales.to ?? 0 }}</strong>
                    de
                    <strong>{{ sales.total }}</strong>
                    ventas
                </div>

                <div class="admin-pagination">
                    <template
                        v-for="(link, index) in sales.links"
                        :key="index"
                    >
                        <Link
                            v-if="link.url"
                            :href="link.url"
                            class="pagination-btn"
                            :class="{
                                active: link.active,
                            }"
                            preserve-scroll
                            preserve-state
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
/* =========================================================
   PAGE
   ========================================================= */

.admin-page {
    width: 100%;
}

/* =========================================================
   SALE
   ========================================================= */

.sale-cell {
    display: flex;
    align-items: center;
    gap: 11px;
}

.sale-icon,
.customer-icon {
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

.sale-info,
.customer-info {
    display: flex;
    flex-direction: column;
    min-width: 0;
}

.sale-info {
    gap: 3px;
}

.customer-info {
    gap: 2px;
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

/* =========================================================
   CUSTOMER
   ========================================================= */

.customer-cell {
    display: flex;
    align-items: center;
    gap: 10px;
}

.customer-cell .customer-icon {
    flex-basis: 34px;
    width: 34px;
    height: 34px;
    border-radius: 9px;
}

/* =========================================================
   DATE
   ========================================================= */

.date-cell {
    display: flex;
    align-items: center;
    gap: 7px;
    color: #64748b;
    font-size: 12px;
    white-space: nowrap;
}

.date-cell svg {
    color: #94a3b8;
}

/* =========================================================
   CHANNEL
   ========================================================= */

.channel-badge {
    display: inline-flex;
    align-items: center;
    padding: 5px 9px;
    border-radius: 8px;
    background: #f1f5f9;
    color: #475569;
    font-size: 11px;
    font-weight: 650;
    white-space: nowrap;
}

/* =========================================================
   TOTAL
   ========================================================= */

.sale-total {
    display: block;
    color: var(--sc-page-text);
    font-size: 13px;
    font-weight: 750;
    white-space: nowrap;
}

.discount-text {
    display: block;
    margin-top: 3px;
    color: #94a3b8;
    font-size: 10px;
    white-space: nowrap;
}

/* =========================================================
   STATUS
   ========================================================= */

.status-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 88px;
    padding: 5px 9px;
    border-radius: 999px;
    font-size: 11px;
    font-weight: 650;
    white-space: nowrap;
}

.status-active {
    background: #e8f8f2;
    color: #168a68;
}

.status-pending {
    background: #fff6df;
    color: #a87500;
}

.status-inactive {
    background: #feecee;
    color: #c93645;
}

/* =========================================================
   ACTIONS
   ========================================================= */

.text-right {
    text-align: right;
}

.action-btn-view {
    color: var(--sc-page-blue);
    background: var(--sc-page-light);
}

.action-btn-view:hover {
    background: #dff1fb;
}

/* =========================================================
   RESPONSIVE
   ========================================================= */

@media (max-width: 1100px) {
    .admin-table-wrapper {
        overflow-x: auto;
    }

    .admin-table {
        min-width: 1050px;
    }
}

@media (max-width: 768px) {
    .admin-page-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 18px;
    }

    .admin-page-header-actions {
        width: 100%;
    }

    .admin-page-header-actions .admin-btn {
        width: 100%;
        justify-content: center;
    }

    .admin-table-toolbar {
        flex-direction: column;
        align-items: stretch;
        gap: 12px;
    }

    .admin-search-form {
        flex-wrap: wrap;
    }

    .admin-search-wrapper {
        width: 100%;
    }

    .admin-btn-search {
        flex: 1;
    }

    .admin-table-counter {
        align-self: flex-start;
    }

    .admin-pagination-wrapper {
        flex-direction: column;
        align-items: flex-start;
        gap: 12px;
    }

    .admin-pagination {
        width: 100%;
        overflow-x: auto;
    }
}
</style>