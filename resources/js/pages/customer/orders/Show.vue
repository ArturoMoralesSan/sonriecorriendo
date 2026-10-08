<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    ArrowLeft,
    CalendarDays,
    CheckCircle2,
    ChevronRight,
    Clock3,
    CreditCard,
    FileText,
    Package,
    ShoppingBag,
    Store,
    Tag,
    XCircle,
} from 'lucide-vue-next';

interface OrderItem {
    id: number;
    product_id: number;
    name: string;
    image: string | null;
    quantity: number;
    unit_price: number;
    subtotal: number;
}

interface Payment {
    id: number;
    method: string;
    amount: number;
    reference: string | null;
    notes: string | null;
}

interface Order {
    id: number;
    folio: string;
    status: string;
    subtotal: number;
    discount: number;
    total: number;
    sales_channel: string;
    notes: string | null;
    sold_at: string | null;
    created_at: string | null;
    items: OrderItem[];
    payments: Payment[];
}

const props = defineProps<{
    order: Order;
}>();

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
        hour: '2-digit',
        minute: '2-digit',
    }).format(new Date(date));
};

/* =========================================================
   ESTADO
========================================================= */

const getStatusLabel = (status: string): string => {
    const labels: Record<string, string> = {
        pending: 'Pendiente',
        pending_payment: 'Pendiente de pago',
        paid: 'Pagado',
        completed: 'Completado',
        cancelled: 'Cancelado',
        canceled: 'Cancelado',
        refunded: 'Reembolsado',
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

        case 'refunded':
            return 'status-refunded';

        default:
            return 'status-default';
    }
};

const getStatusIcon = (status: string) => {
    switch (status) {
        case 'completed':
        case 'paid':
            return CheckCircle2;

        case 'pending':
        case 'pending_payment':
            return Clock3;

        case 'cancelled':
        case 'canceled':
        case 'failed':
            return XCircle;

        default:
            return Package;
    }
};

const getChannelLabel = (channel: string): string => {
    const channels: Record<string, string> = {
        counter: 'Ventanilla',
        online: 'Compra en línea',
        web: 'Compra en línea',
        sucursal: 'Sucursal',
    };

    return channels[channel] ?? channel;
};
</script>

<template>
    <Head :title="`Pedido ${order.folio}`" />

    <div class="order-show">
        <!-- =================================================
             HEADER
        ================================================== -->

        <div class="order-header">
            <div>
                <Link
                    href="/pedidos"
                    class="back-link"
                >
                    <ArrowLeft :size="16" />
                    Volver a mis pedidos
                </Link>

                <div class="order-title-row">
                    <div>
                        <p class="order-eyebrow">
                            Detalle de pedido
                        </p>

                        <h1>
                            {{ order.folio }}
                        </h1>

                        <p class="order-date">
                            <CalendarDays :size="14" />
                            {{ formatDate(order.sold_at ?? order.created_at) }}
                        </p>
                    </div>

                    <span
                        class="status-badge"
                        :class="getStatusClass(order.status)"
                    >
                        <component
                            :is="getStatusIcon(order.status)"
                            :size="15"
                        />

                        {{ getStatusLabel(order.status) }}
                    </span>
                </div>
            </div>
        </div>

        <!-- =================================================
             CONTENT
        ================================================== -->

        <div class="order-layout">
            <!-- =================================================
                 LEFT
            ================================================== -->

            <div class="order-main">
                <!-- ITEMS -->

                <section class="order-card">
                    <div class="card-header">
                        <div>
                            <p class="card-eyebrow">
                                Productos
                            </p>

                            <h2>
                                Artículos de tu pedido
                            </h2>
                        </div>

                        <div class="card-header-count">
                            <ShoppingBag :size="16" />
                            {{ order.items.length }}
                        </div>
                    </div>

                    <div class="order-items">
                        <div
                            v-for="item in order.items"
                            :key="item.id"
                            class="order-item"
                        >
                            <div class="product-image">
                                <img
                                    v-if="item.image"
                                    :src="`/storage/${item.image}`"
                                    :alt="item.name"
                                />

                                <Package
                                    v-else
                                    :size="24"
                                />
                            </div>

                            <div class="product-info">
                                <strong>
                                    {{ item.name }}
                                </strong>

                                <span>
                                    Cantidad: {{ item.quantity }}
                                </span>
                            </div>

                            <div class="product-price">
                                <span>
                                    {{ formatCurrency(item.unit_price) }}
                                    c/u
                                </span>

                                <strong>
                                    {{ formatCurrency(item.subtotal) }}
                                </strong>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- PAYMENT -->

                <section
                    v-if="order.payments.length"
                    class="order-card"
                >
                    <div class="card-header">
                        <div>
                            <p class="card-eyebrow">
                                Pagos
                            </p>

                            <h2>
                                Método de pago
                            </h2>
                        </div>

                        <CreditCard :size="19" />
                    </div>

                    <div class="payment-list">
                        <div
                            v-for="payment in order.payments"
                            :key="payment.id"
                            class="payment-row"
                        >
                            <div class="payment-icon">
                                <CreditCard :size="17" />
                            </div>

                            <div class="payment-info">
                                <strong>
                                    {{ payment.method }}
                                </strong>

                                <span
                                    v-if="payment.reference"
                                >
                                    Referencia:
                                    {{ payment.reference }}
                                </span>

                                <span
                                    v-if="payment.notes"
                                >
                                    {{ payment.notes }}
                                </span>
                            </div>

                            <strong class="payment-amount">
                                {{ formatCurrency(payment.amount) }}
                            </strong>
                        </div>
                    </div>
                </section>

                <!-- NOTES -->

                <section
                    v-if="order.notes"
                    class="order-card"
                >
                    <div class="card-header">
                        <div>
                            <p class="card-eyebrow">
                                Información adicional
                            </p>

                            <h2>
                                Notas del pedido
                            </h2>
                        </div>

                        <FileText :size="19" />
                    </div>

                    <div class="order-notes">
                        {{ order.notes }}
                    </div>
                </section>
            </div>

            <!-- =================================================
                 RIGHT / SUMMARY
            ================================================== -->

            <aside class="order-sidebar">
                <section class="summary-card">
                    <div class="summary-header">
                        <div class="summary-icon">
                            <ShoppingBag :size="19" />
                        </div>

                        <div>
                            <p>
                                Resumen
                            </p>

                            <strong>
                                {{ order.folio }}
                            </strong>
                        </div>
                    </div>

                    <div class="summary-lines">
                        <div class="summary-line">
                            <span>
                                Subtotal
                            </span>

                            <strong>
                                {{ formatCurrency(order.subtotal) }}
                            </strong>
                        </div>

                        <div
                            v-if="Number(order.discount) > 0"
                            class="summary-line discount"
                        >
                            <span>
                                Descuento
                            </span>

                            <strong>
                                -{{ formatCurrency(order.discount) }}
                            </strong>
                        </div>

                        <div class="summary-divider"></div>

                        <div class="summary-total">
                            <span>
                                Total
                            </span>

                            <strong>
                                {{ formatCurrency(order.total) }}
                            </strong>
                        </div>
                    </div>
                </section>

                <!-- ORDER INFO -->

                <section class="info-card">
                    <div class="info-card-header">
                        <h3>
                            Información del pedido
                        </h3>
                    </div>

                    <div class="info-list">
                        <div class="info-row">
                            <div class="info-row-icon">
                                <CalendarDays :size="15" />
                            </div>

                            <div>
                                <span>
                                    Fecha
                                </span>

                                <strong>
                                    {{ formatDate(order.sold_at ?? order.created_at) }}
                                </strong>
                            </div>
                        </div>

                        <div class="info-row">
                            <div class="info-row-icon">
                                <Store :size="15" />
                            </div>

                            <div>
                                <span>
                                    Canal de venta
                                </span>

                                <strong>
                                    {{ getChannelLabel(order.sales_channel) }}
                                </strong>
                            </div>
                        </div>

                        <div class="info-row">
                            <div class="info-row-icon">
                                <Tag :size="15" />
                            </div>

                            <div>
                                <span>
                                    Estado
                                </span>

                                <strong>
                                    {{ getStatusLabel(order.status) }}
                                </strong>
                            </div>
                        </div>
                    </div>
                </section>

                <Link
                    href="/pedidos"
                    class="back-orders-button"
                >
                    <ArrowLeft :size="16" />
                    Regresar a mis pedidos
                </Link>
            </aside>
        </div>
    </div>
</template>

<style scoped>
.order-show {
    width: 100%;
    max-width: 1400px;
    margin: 0 auto;
}

/* =========================================================
   HEADER
========================================================= */

.order-header {
    margin-bottom: 22px;
}

.back-link {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    margin-bottom: 13px;
    color: #249edb;
    font-size: 11px;
    font-weight: 700;
    text-decoration: none;
}

.back-link:hover {
    color: #1769a8;
}

.order-title-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
}

.order-eyebrow {
    margin: 0 0 4px;
    color: #249edb;
    font-size: 9px;
    font-weight: 750;
    letter-spacing: 0.08em;
    text-transform: uppercase;
}

.order-title-row h1 {
    margin: 0;
    color: #172b4d;
    font-size: 25px;
    font-weight: 750;
    line-height: 1.2;
}

.order-date {
    display: flex;
    align-items: center;
    gap: 5px;
    margin: 7px 0 0;
    color: #94a3b8;
    font-size: 10px;
}

/* =========================================================
   STATUS
========================================================= */

.status-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    padding: 8px 11px;
    border-radius: 9px;
    font-size: 10px;
    font-weight: 700;
    white-space: nowrap;
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

.status-refunded {
    border: 1px solid #ddd6f4;
    background: #f4f1fc;
    color: #6753b7;
}

.status-default {
    border: 1px solid #dce9f1;
    background: #f3f9fc;
    color: #1769a8;
}

/* =========================================================
   LAYOUT
========================================================= */

.order-layout {
    display: grid;
    grid-template-columns: minmax(0, 1fr) 330px;
    align-items: start;
    gap: 20px;
}

.order-main {
    display: flex;
    flex-direction: column;
    gap: 18px;
}

.order-sidebar {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

/* =========================================================
   CARD
========================================================= */

.order-card,
.info-card {
    overflow: hidden;
    border: 1px solid #e5ebef;
    border-radius: 15px;
    background: #ffffff;
    box-shadow: 0 3px 14px rgba(23, 43, 77, 0.035);
}

.card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 15px;
    padding: 17px 18px;
    border-bottom: 1px solid #edf1f4;
}

.card-eyebrow {
    margin: 0 0 3px;
    color: #249edb;
    font-size: 9px;
    font-weight: 750;
    letter-spacing: 0.08em;
    text-transform: uppercase;
}

.card-header h2 {
    margin: 0;
    color: #172b4d;
    font-size: 16px;
    font-weight: 750;
}

.card-header > svg {
    color: #249edb;
}

.card-header-count {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 6px 9px;
    border-radius: 8px;
    background: #eaf6fc;
    color: #249edb;
    font-size: 10px;
    font-weight: 700;
}

/* =========================================================
   PRODUCTS
========================================================= */

.order-items {
    display: flex;
    flex-direction: column;
}

.order-item {
    display: grid;
    grid-template-columns: 54px minmax(0, 1fr) auto;
    align-items: center;
    gap: 13px;
    padding: 14px 18px;
    border-bottom: 1px solid #edf1f4;
}

.order-item:last-child {
    border-bottom: 0;
}

.product-image {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 54px;
    height: 54px;
    overflow: hidden;
    border-radius: 10px;
    background: #f1f8fb;
    color: #249edb;
}

.product-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.product-info {
    display: flex;
    flex-direction: column;
    gap: 4px;
    min-width: 0;
}

.product-info strong {
    overflow: hidden;
    color: #172b4d;
    font-size: 12px;
    font-weight: 700;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.product-info span {
    color: #94a3b8;
    font-size: 10px;
}

.product-price {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 3px;
}

.product-price span {
    color: #94a3b8;
    font-size: 9px;
}

.product-price strong {
    color: #1769a8;
    font-size: 12px;
    font-weight: 750;
}

/* =========================================================
   PAYMENTS
========================================================= */

.payment-list {
    display: flex;
    flex-direction: column;
}

.payment-row {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px 18px;
    border-bottom: 1px solid #edf1f4;
}

.payment-row:last-child {
    border-bottom: 0;
}

.payment-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    flex: 0 0 auto;
    width: 38px;
    height: 38px;
    border-radius: 10px;
    background: #eaf6fc;
    color: #249edb;
}

.payment-info {
    display: flex;
    flex: 1;
    flex-direction: column;
    gap: 3px;
    min-width: 0;
}

.payment-info strong {
    color: #172b4d;
    font-size: 11px;
    font-weight: 700;
}

.payment-info span {
    overflow: hidden;
    color: #94a3b8;
    font-size: 9px;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.payment-amount {
    color: #1769a8;
    font-size: 12px;
    font-weight: 750;
    white-space: nowrap;
}

/* =========================================================
   NOTES
========================================================= */

.order-notes {
    padding: 17px 18px;
    color: #64748b;
    font-size: 11px;
    line-height: 1.6;
}

/* =========================================================
   SUMMARY
========================================================= */

.summary-card {
    padding: 18px;
    border: 1px solid #dcebf3;
    border-radius: 15px;
    background:
        linear-gradient(
            135deg,
            #f4fbfe 0%,
            #ffffff 62%,
            #fff7fc 100%
        );
    box-shadow: 0 3px 14px rgba(23, 43, 77, 0.035);
}

.summary-header {
    display: flex;
    align-items: center;
    gap: 11px;
    padding-bottom: 16px;
    border-bottom: 1px solid #e5edf2;
}

.summary-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    border-radius: 10px;
    background: #eaf6fc;
    color: #249edb;
}

.summary-header p {
    margin: 0 0 2px;
    color: #94a3b8;
    font-size: 9px;
    font-weight: 600;
}

.summary-header strong {
    color: #172b4d;
    font-size: 12px;
    font-weight: 750;
}

.summary-lines {
    padding-top: 15px;
}

.summary-line {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 15px;
    padding: 5px 0;
}

.summary-line span {
    color: #64748b;
    font-size: 10px;
}

.summary-line strong {
    color: #172b4d;
    font-size: 11px;
}

.summary-line.discount strong {
    color: #25845b;
}

.summary-divider {
    height: 1px;
    margin: 11px 0;
    background: #e5edf2;
}

.summary-total {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 15px;
}

.summary-total span {
    color: #172b4d;
    font-size: 12px;
    font-weight: 700;
}

.summary-total strong {
    color: #1769a8;
    font-size: 19px;
    font-weight: 750;
}

/* =========================================================
   INFO
========================================================= */

.info-card-header {
    padding: 16px 17px;
    border-bottom: 1px solid #edf1f4;
}

.info-card-header h3 {
    margin: 0;
    color: #172b4d;
    font-size: 13px;
    font-weight: 750;
}

.info-list {
    display: flex;
    flex-direction: column;
}

.info-row {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 17px;
    border-bottom: 1px solid #edf1f4;
}

.info-row:last-child {
    border-bottom: 0;
}

.info-row-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    flex: 0 0 auto;
    width: 32px;
    height: 32px;
    border-radius: 8px;
    background: #f1f8fb;
    color: #249edb;
}

.info-row > div:last-child {
    display: flex;
    flex-direction: column;
    gap: 2px;
    min-width: 0;
}

.info-row span {
    color: #94a3b8;
    font-size: 9px;
}

.info-row strong {
    overflow: hidden;
    color: #172b4d;
    font-size: 10px;
    font-weight: 700;
    text-overflow: ellipsis;
    white-space: nowrap;
}

/* =========================================================
   BACK BUTTON
========================================================= */

.back-orders-button {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 7px;
    min-height: 40px;
    padding: 0 15px;
    border: 1px solid #249edb;
    border-radius: 10px;
    background: #249edb;
    color: #ffffff;
    font-size: 11px;
    font-weight: 700;
    text-decoration: none;
    transition:
        background 0.2s ease,
        border-color 0.2s ease,
        transform 0.2s ease;
}

.back-orders-button:hover {
    border-color: #1769a8;
    background: #1769a8;
    transform: translateY(-1px);
}

/* =========================================================
   RESPONSIVE
========================================================= */

@media (max-width: 1000px) {
    .order-layout {
        grid-template-columns: 1fr;
    }

    .order-sidebar {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        align-items: start;
    }

    .back-orders-button {
        grid-column: 1 / -1;
    }
}

@media (max-width: 700px) {
    .order-title-row {
        align-items: flex-start;
        flex-direction: column;
    }

    .order-sidebar {
        display: flex;
    }

    .order-item {
        grid-template-columns: 48px minmax(0, 1fr);
    }

    .product-image {
        width: 48px;
        height: 48px;
    }

    .product-price {
        grid-column: 2;
        align-items: flex-start;
    }
}

@media (max-width: 500px) {
    .order-title-row h1 {
        font-size: 21px;
    }

    .card-header {
        padding: 15px;
    }

    .order-item {
        padding: 13px 15px;
    }

    .payment-row {
        padding: 13px 15px;
    }

    .summary-card {
        padding: 15px;
    }
}
</style>