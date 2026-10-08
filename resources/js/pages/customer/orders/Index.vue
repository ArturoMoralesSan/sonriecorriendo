<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import {
    CalendarDays,
    ChevronRight,
    Package,
    Search,
    ShoppingBag,
} from 'lucide-vue-next';
import { ref } from 'vue';

interface Product {
    id: number;
    name: string;
    image: string | null;
}

interface SaleItem {
    id: number;
    quantity: number;
    unit_price: number;
    subtotal: number;
    product: Product | null;
}

interface PaymentMethod {
    id: number;
    name: string;
}

interface SalePayment {
    id: number;
    amount: number;
    payment_method: PaymentMethod | null;
}

interface Order {
    id: number;
    folio: string;
    subtotal: number;
    discount: number;
    total: number;
    sales_channel: string;
    status: string;
    sold_at: string | null;
    items: SaleItem[];
    payments: SalePayment[];
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface OrdersPagination {
    data: Order[];
    links: PaginationLink[];
    current_page: number;
    last_page: number;
    from: number | null;
    to: number | null;
    total: number;
}

const props = defineProps<{
    orders: OrdersPagination;
    filters?: {
        search?: string;
    };
}>();

/* =========================================================
   SEARCH
========================================================= */

const search = ref(props.filters?.search ?? '');

const submitSearch = (): void => {
    router.get(
        '/pedidos',
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
        '/pedidos',
        {},
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    );
};

/* =========================================================
   FORMATOS
========================================================= */

const formatCurrency = (value: number): string => {
    return new Intl.NumberFormat('es-MX', {
        style: 'currency',
        currency: 'MXN',
        minimumFractionDigits: 2,
    }).format(Number(value));
};

const formatDate = (date: string | null): string => {
    if (!date) {
        return 'Fecha no disponible';
    }

    return new Intl.DateTimeFormat('es-MX', {
        day: '2-digit',
        month: 'long',
        year: 'numeric',
    }).format(new Date(date));
};

/* =========================================================
   ESTADOS
========================================================= */

const getStatusLabel = (status: string): string => {
    const labels: Record<string, string> = {
        pending: 'Pendiente',
        pending_payment: 'Pendiente de pago',
        paid: 'Pagado',
        completed: 'Completado',
        cancelled: 'Cancelado',
        canceled: 'Cancelado',
        failed: 'Fallido',
    };

    return labels[status] ?? status;
};

const getStatusClass = (status: string): string => {
    switch (status) {
        case 'completed':
        case 'paid':
            return 'status-completed';

        case 'pending':
        case 'pending_payment':
            return 'status-pending';

        case 'cancelled':
        case 'canceled':
        case 'failed':
            return 'status-cancelled';

        default:
            return 'status-default';
    }
};

/* =========================================================
   PRODUCTOS
========================================================= */

const getProductCount = (order: Order): number => {
    return order.items.reduce(
        (total, item) => total + Number(item.quantity),
        0,
    );
};

const getProductsText = (order: Order): string => {
    const names = order.items
        .map((item) => item.product?.name)
        .filter(Boolean);

    if (!names.length) {
        return 'Sin productos';
    }

    if (names.length === 1) {
        return names[0] as string;
    }

    return `${names[0]} y ${names.length - 1} más`;
};

/* =========================================================
   MÉTODO DE PAGO
========================================================= */

const getPaymentMethod = (order: Order): string => {
    if (!order.payments.length) {
        return 'Pago no registrado';
    }

    const methods = order.payments
        .map((payment) => payment.payment_method?.name)
        .filter(Boolean);

    if (!methods.length) {
        return 'Pago no registrado';
    }

    return [...new Set(methods)].join(', ');
};

/* =========================================================
   IMÁGENES
========================================================= */

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
</script>

<template>
    <Head title="Mis pedidos" />

    <div class="admin-page">
        <!-- =================================================
             HEADER
        ================================================== -->

        <div class="customer-page-header">
            <div>
                <p class="customer-eyebrow">
                    Mi cuenta
                </p>

                <h1 class="customer-title">
                    Mis pedidos
                </h1>

                <p class="customer-subtitle">
                    Consulta el historial de tus compras en
                    Sonríe Corriendo.
                </p>
            </div>

            <div class="customer-order-counter">
                <ShoppingBag :size="19" />

                <span>
                    {{ orders.total }}
                    {{
                        orders.total === 1
                            ? 'pedido'
                            : 'pedidos'
                    }}
                </span>
            </div>
        </div>

        <!-- =================================================
             SEARCH
        ================================================== -->

        <div class="orders-search">
            <div class="orders-search-box">
                <Search :size="17" />

                <input
                    v-model="search"
                    type="text"
                    placeholder="Buscar por folio, producto o estado..."
                    @keyup.enter="submitSearch"
                />

                <button
                    v-if="search"
                    type="button"
                    class="orders-search-clear"
                    @click="clearSearch"
                >
                    Limpiar
                </button>
            </div>

            <button
                type="button"
                class="orders-search-button"
                @click="submitSearch"
            >
                Buscar
            </button>
        </div>

        <!-- =================================================
             EMPTY
        ================================================== -->

        <div
            v-if="!orders.data.length"
            class="orders-empty"
        >
            <div class="orders-empty-icon">
                <Package :size="34" />
            </div>

            <h2>
                {{
                    search
                        ? 'No encontramos pedidos'
                        : 'Todavía no tienes pedidos'
                }}
            </h2>

            <p>
                {{
                    search
                        ? 'Intenta buscar con otro folio, producto o estado.'
                        : 'Cuando realices una compra, podrás consultar aquí todos los detalles de tus pedidos.'
                }}
            </p>

            <button
                v-if="search"
                type="button"
                class="orders-empty-button"
                @click="clearSearch"
            >
                Limpiar búsqueda
            </button>
        </div>

        <!-- =================================================
             ORDERS
        ================================================== -->

        <div
            v-else
            class="orders-list"
        >
            <article
                v-for="order in orders.data"
                :key="order.id"
                class="customer-order-card"
            >
                <!-- HEADER -->

                <div class="customer-order-header">
                    <div>
                        <div class="customer-order-folio">
                            {{ order.folio }}
                        </div>

                        <div class="customer-order-date">
                            <CalendarDays :size="14" />

                            <span>
                                {{ formatDate(order.sold_at) }}
                            </span>
                        </div>
                    </div>

                    <span
                        class="status-badge"
                        :class="getStatusClass(order.status)"
                    >
                        {{ getStatusLabel(order.status) }}
                    </span>
                </div>

                <!-- CONTENT -->

                <div class="customer-order-content">
                    <div class="order-product-preview">
                        <div
                            v-for="item in order.items.slice(0, 3)"
                            :key="item.id"
                            class="order-product-image"
                        >
                            <img
                                v-if="
                                    getImageUrl(
                                        item.product?.image ??
                                            null,
                                    )
                                "
                                :src="
                                    getImageUrl(
                                        item.product?.image ??
                                            null,
                                    )!
                                "
                                :alt="
                                    item.product?.name ??
                                    'Producto'
                                "
                            />

                            <Package
                                v-else
                                :size="18"
                            />
                        </div>
                    </div>

                    <div class="customer-order-info">
                        <strong>
                            {{ getProductsText(order) }}
                        </strong>

                        <span>
                            {{ getProductCount(order) }}
                            {{
                                getProductCount(order) === 1
                                    ? 'producto'
                                    : 'productos'
                            }}
                        </span>
                    </div>

                    <div class="customer-order-payment">
                        <span>
                            Método de pago
                        </span>

                        <strong>
                            {{ getPaymentMethod(order) }}
                        </strong>
                    </div>

                    <div class="customer-order-total">
                        <span>
                            Total
                        </span>

                        <strong>
                            {{ formatCurrency(order.total) }}
                        </strong>
                    </div>
                </div>

                <!-- FOOTER -->

                <div class="customer-order-footer">
                    <span class="customer-order-channel">
                        {{
                            order.sales_channel === 'web'
                                ? 'Compra en línea'
                                : 'Compra en tienda'
                        }}
                    </span>

                    <Link
                        :href="`/pedidos/${order.id}`"
                        class="customer-order-link"
                    >
                        Ver pedido

                        <ChevronRight :size="16" />
                    </Link>
                </div>
            </article>
        </div>

        <!-- =================================================
             PAGINATION
        ================================================== -->

        <div
            v-if="orders.last_page > 1"
            class="orders-pagination"
        >
            <Link
                v-for="link in orders.links"
                :key="link.label"
                :href="link.url ?? '#'"
                class="pagination-link"
                :class="{
                    active: link.active,
                    disabled: !link.url,
                }"
                v-html="link.label"
            />
        </div>
    </div>
</template>

<style scoped>
.customer-page-header {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 24px;
    margin-bottom: 18px;
}

.customer-eyebrow {
    margin: 0 0 5px;
    color: #249edb;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
}

.customer-title {
    margin: 0;
    color: var(--sc-page-text);
    font-size: 27px;
    font-weight: 750;
    line-height: 1.15;
}

.customer-subtitle {
    margin: 7px 0 0;
    color: var(--sc-page-text-secondary);
    font-size: 13px;
}

.customer-order-counter {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 9px 13px;
    border: 1px solid #dce9f1;
    border-radius: 10px;
    background: #f3f9fc;
    color: #1769a8;
    font-size: 12px;
    font-weight: 700;
    white-space: nowrap;
}

/* =========================================================
   SEARCH
========================================================= */

.orders-search {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 18px;
}

.orders-search-box {
    display: flex;
    align-items: center;
    flex: 1;
    gap: 9px;
    min-height: 42px;
    padding: 0 12px;
    border: 1px solid #e1e8ed;
    border-radius: 10px;
    background: #ffffff;
    color: #94a3b8;
}

.orders-search-box:focus-within {
    border-color: #a9d9f2;
    box-shadow: 0 0 0 3px rgba(36, 158, 219, 0.08);
}

.orders-search-box input {
    width: 100%;
    min-width: 0;
    border: 0;
    outline: 0;
    background: transparent;
    color: var(--sc-page-text);
    font-family: inherit;
    font-size: 12px;
}

.orders-search-box input::placeholder {
    color: #9aa8b2;
}

.orders-search-clear {
    border: 0;
    background: transparent;
    color: #64748b;
    cursor: pointer;
    font-family: inherit;
    font-size: 11px;
    font-weight: 600;
    white-space: nowrap;
}

.orders-search-clear:hover {
    color: #1769a8;
}

.orders-search-button {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 42px;
    padding: 0 17px;
    border: 1px solid #249edb;
    border-radius: 10px;
    background: #249edb;
    color: #ffffff;
    cursor: pointer;
    font-family: inherit;
    font-size: 12px;
    font-weight: 700;
    transition:
        background 0.2s ease,
        border-color 0.2s ease;
}

.orders-search-button:hover {
    border-color: #1769a8;
    background: #1769a8;
}

/* =========================================================
   EMPTY
========================================================= */

.orders-empty {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    min-height: 330px;
    padding: 40px;
    border: 1px solid #e7edf1;
    border-radius: 16px;
    background: #ffffff;
    text-align: center;
}

.orders-empty-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 68px;
    height: 68px;
    margin-bottom: 18px;
    border-radius: 50%;
    background: #eaf6fc;
    color: #249edb;
}

.orders-empty h2 {
    margin: 0;
    color: var(--sc-page-text);
    font-size: 18px;
}

.orders-empty p {
    max-width: 430px;
    margin: 8px 0 0;
    color: var(--sc-page-text-secondary);
    font-size: 13px;
    line-height: 1.6;
}

.orders-empty-button {
    margin-top: 18px;
    padding: 9px 15px;
    border: 1px solid #249edb;
    border-radius: 9px;
    background: #249edb;
    color: #ffffff;
    cursor: pointer;
    font-family: inherit;
    font-size: 12px;
    font-weight: 700;
}

/* =========================================================
   ORDERS
========================================================= */

.orders-list {
    display: flex;
    flex-direction: column;
    gap: 14px;
}

.customer-order-card {
    overflow: hidden;
    border: 1px solid #e5eaee;
    border-radius: 16px;
    background: #ffffff;
    box-shadow: 0 4px 16px rgba(23, 43, 77, 0.04);
}

.customer-order-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
    padding: 17px 20px;
    border-bottom: 1px solid #edf1f4;
}

.customer-order-folio {
    color: var(--sc-page-text);
    font-size: 14px;
    font-weight: 750;
}

.customer-order-date {
    display: flex;
    align-items: center;
    gap: 5px;
    margin-top: 5px;
    color: var(--sc-page-text-secondary);
    font-size: 11px;
}

.status-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 6px 10px;
    border-radius: 8px;
    font-size: 11px;
    font-weight: 700;
}

.status-completed {
    border: 1px solid #cde9dc;
    background: #effaf4;
    color: #25845b;
}

.status-pending {
    border: 1px solid #f3dfb5;
    background: #fff9ec;
    color: #a66b00;
}

.status-cancelled {
    border: 1px solid #f0d8dc;
    background: #fff4f5;
    color: #c94d59;
}

.status-default {
    border: 1px solid #dce9f1;
    background: #f3f9fc;
    color: #1769a8;
}

/* =========================================================
   CONTENT
========================================================= */

.customer-order-content {
    display: grid;
    grid-template-columns: auto minmax(180px, 1fr) auto auto;
    align-items: center;
    gap: 22px;
    padding: 20px;
}

.order-product-preview {
    display: flex;
    align-items: center;
}

.order-product-image {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 52px;
    height: 52px;
    overflow: hidden;
    margin-right: -10px;
    border: 3px solid #ffffff;
    border-radius: 12px;
    background: #f3f7f9;
    color: #91a0aa;
}

.order-product-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.customer-order-info {
    display: flex;
    flex-direction: column;
    gap: 5px;
    min-width: 0;
}

.customer-order-info strong {
    overflow: hidden;
    color: var(--sc-page-text);
    font-size: 13px;
    font-weight: 650;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.customer-order-info span,
.customer-order-payment span,
.customer-order-total span {
    color: var(--sc-page-text-secondary);
    font-size: 11px;
}

.customer-order-payment {
    display: flex;
    flex-direction: column;
    gap: 5px;
    min-width: 130px;
}

.customer-order-payment strong {
    color: var(--sc-page-text);
    font-size: 12px;
    font-weight: 600;
}

.customer-order-total {
    display: flex;
    flex-direction: column;
    gap: 5px;
    min-width: 100px;
    text-align: right;
}

.customer-order-total strong {
    color: #1769a8;
    font-size: 15px;
    font-weight: 750;
}

/* =========================================================
   FOOTER
========================================================= */

.customer-order-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 15px;
    padding: 12px 20px;
    border-top: 1px solid #edf1f4;
    background: #fbfcfd;
}

.customer-order-channel {
    color: var(--sc-page-text-secondary);
    font-size: 11px;
}

.customer-order-link {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    color: #249edb;
    font-size: 12px;
    font-weight: 700;
    text-decoration: none;
    transition: color 0.2s ease;
}

.customer-order-link:hover {
    color: #1769a8;
}

/* =========================================================
   PAGINATION
========================================================= */

.orders-pagination {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 5px;
    margin-top: 20px;
}

.pagination-link {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 34px;
    height: 34px;
    padding: 0 10px;
    border: 1px solid #e1e8ed;
    border-radius: 8px;
    background: #ffffff;
    color: #64748b;
    font-size: 12px;
    font-weight: 600;
    text-decoration: none;
}

.pagination-link.active {
    border-color: #249edb;
    background: #249edb;
    color: #ffffff;
}

.pagination-link.disabled {
    pointer-events: none;
    opacity: 0.45;
}

/* =========================================================
   RESPONSIVE
========================================================= */

@media (max-width: 900px) {
    .customer-order-content {
        grid-template-columns: auto 1fr;
    }

    .customer-order-payment,
    .customer-order-total {
        text-align: left;
    }
}

@media (max-width: 640px) {
    .customer-page-header {
        align-items: flex-start;
        flex-direction: column;
    }

    .orders-search {
        align-items: stretch;
        flex-direction: column;
    }

    .orders-search-button {
        width: 100%;
    }

    .customer-order-header {
        align-items: flex-start;
        flex-direction: column;
    }

    .customer-order-content {
        grid-template-columns: 1fr;
    }

    .customer-order-total {
        text-align: left;
    }

    .customer-order-footer {
        align-items: flex-start;
        flex-direction: column;
    }
}
</style>