<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
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

import DataTable from '@/Components/Admin/DataTable.vue';
import PageHeader from '@/Components/Admin/PageHeader.vue';
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

const formatCurrency = (
    value: number | string,
): string => {
    return Number(value).toLocaleString('es-MX', {
        style: 'currency',
        currency: 'MXN',
    });
};

const formatDate = (
    value: string | null,
): string => {
    if (!value) {
        return '—';
    }

    return new Date(value).toLocaleString('es-MX', {
        dateStyle: 'short',
        timeStyle: 'short',
    });
};

const getStatusLabel = (
    status: string,
): string => {
    const labels: Record<string, string> = {
        pending: 'Pendiente',
        paid: 'Pagada',
        partially_paid: 'Pago parcial',
        cancelled: 'Cancelada',
        refunded: 'Reembolsada',
    };

    return labels[status] ?? status;
};

const getStatusClass = (
    status: string,
): string => {
    const classes: Record<string, string> = {
        paid: 'status-active',
        pending: 'status-pending',
        partially_paid: 'status-pending',
        cancelled: 'status-inactive',
        refunded: 'status-inactive',
    };

    return classes[status] ?? 'status-neutral';
};

const getChannelLabel = (
    channel: string,
): string => {
    const labels: Record<string, string> = {
        counter: 'Ventanilla',
        branch: 'Sucursal',
        web: 'Web',
        app: 'App',
    };

    return labels[channel] ?? channel;
};

const columns = [
    {
        key: 'sale',
        label: 'Venta',
    },
    {
        key: 'customer',
        label: 'Cliente',
    },
    {
        key: 'date',
        label: 'Fecha',
    },
    {
        key: 'channel',
        label: 'Canal',
    },
    {
        key: 'total',
        label: 'Total',
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
        href: (sale: Record<string, any>) =>
            admin.sales.show(sale.id).url,
    },
];
</script>

<template>
    <Head title="Ventas" />

    <div class="admin-page">
        <PageHeader
            eyebrow="Comercio"
            title="Ventas"
            subtitle="Consulta y administra las ventas realizadas."
            :actions="[
                {
                    label: 'Nueva venta',
                    href: admin.sales.create().url,
                    icon: Plus,
                    variant: 'primary',
                },
            ]"
        />

        <DataTable
            :columns="columns"
            :pagination="sales"
            :actions="actions"
            :search="search"
            search-placeholder="Buscar por folio, cliente, estado..."
            counter-label="ventas"
            empty-title="No se encontraron ventas."
            empty-description="Intenta realizar una búsqueda diferente."
            :search-icon="Search"
            :empty-icon="ShoppingCart"
            @update:search="search = $event"
            @search="submitSearch"
        >
            <!-- VENTA -->
            <template #cell-sale="{ row }">
                <div class="sale-cell">
                    <div class="sale-icon">
                        <FileText
                            :size="18"
                            :stroke-width="2"
                        />
                    </div>

                    <div class="sale-info">
                        <div class="font-medium">
                            {{ row.folio }}
                        </div>

                        <div class="payment-description">
                            {{ row.items?.length ?? 0 }}

                            {{
                                (row.items?.length ?? 0) === 1
                                    ? 'producto'
                                    : 'productos'
                            }}
                        </div>
                    </div>
                </div>
            </template>

            <!-- CLIENTE -->
            <template #cell-customer="{ row }">
                <div class="customer-cell">
                    <div class="customer-icon">
                        <UserRound
                            :size="17"
                            :stroke-width="2"
                        />
                    </div>

                    <div class="customer-info">
                        <div
                            v-if="row.customer"
                            class="font-medium"
                        >
                            {{ row.customer.name }}
                        </div>

                        <div
                            v-if="row.customer?.email"
                            class="payment-description"
                        >
                            {{ row.customer.email }}
                        </div>

                        <span
                            v-else-if="!row.customer"
                            class="payment-description"
                        >
                            Venta mostrador
                        </span>
                    </div>
                </div>
            </template>

            <!-- FECHA -->
            <template #cell-date="{ row }">
                <div class="date-cell">
                    <CalendarDays
                        :size="14"
                        :stroke-width="2"
                    />

                    <span>
                        {{ formatDate(row.sold_at) }}
                    </span>
                </div>
            </template>

            <!-- CANAL -->
            <template #cell-channel="{ row }">
                <span class="channel-badge">
                    {{ getChannelLabel(row.sales_channel) }}
                </span>
            </template>

            <!-- TOTAL -->
            <template #cell-total="{ row }">
                <strong class="sale-total">
                    {{ formatCurrency(row.total) }}
                </strong>

                <span
                    v-if="Number(row.discount) > 0"
                    class="discount-text"
                >
                    Descuento:
                    {{ formatCurrency(row.discount) }}
                </span>
            </template>

            <!-- ESTADO -->
            <template #cell-status="{ row }">
                <span
                    class="status-badge"
                    :class="getStatusClass(row.status)"
                >
                    {{ getStatusLabel(row.status) }}
                </span>
            </template>
        </DataTable>
    </div>
</template>

<style scoped>
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

.status-neutral {
    background: #f1f5f9;
    color: #64748b;
}
</style>