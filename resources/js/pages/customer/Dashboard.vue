<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    ArrowRight,
    CalendarDays,
    ChevronRight,
    CircleCheck,
    Clock3,
    Package,
    ShoppingBag,
    Sparkles,
} from 'lucide-vue-next';

interface Customer {
    id: number;
    name: string;
    email: string;
}

interface Order {
    id: number;
    folio: string;
    total: number;
    status: string;
    sold_at: string | null;
    items_count?: number;
}

interface CustomerStats {
    total_orders: number;
    completed_orders: number;
    pending_orders: number;
    total_spent: number;
}

const props = defineProps<{
    customer: Customer;
    stats: CustomerStats;
    recentOrders: Order[];
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
        month: 'short',
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

const getStatusIcon = (status: string) => {
    switch (status) {
        case 'completed':
        case 'paid':
            return CircleCheck;

        case 'pending':
        case 'pending_payment':
            return Clock3;

        default:
            return Package;
    }
};
</script>

<template>
    <Head title="Mi cuenta" />

    <div class="customer-dashboard">
        <!-- =================================================
             WELCOME
        ================================================== -->

        <section class="customer-welcome">
            <div class="customer-welcome-content">
                <div class="customer-welcome-icon">
                    <Sparkles :size="20" />
                </div>

                <div>
                    <p class="customer-eyebrow">
                        Mi cuenta
                    </p>

                    <h1>
                        ¡Hola, {{ customer.name }}!
                    </h1>

                    <p class="customer-welcome-text">
                        Bienvenido a tu espacio de Sonríe Corriendo.
                        Aquí puedes consultar tus pedidos y compras.
                    </p>
                </div>
            </div>

            <Link
                href="/pedidos"
                class="customer-welcome-button"
            >
                <ShoppingBag :size="17" />
                Ver mis pedidos
                <ArrowRight :size="16" />
            </Link>
        </section>

        <!-- =================================================
             STATS
        ================================================== -->

        <section class="customer-stats">
            <div class="customer-stat-card">
                <div class="customer-stat-icon stat-blue">
                    <ShoppingBag :size="19" />
                </div>

                <div class="customer-stat-content">
                    <span>
                        Mis pedidos
                    </span>

                    <strong>
                        {{ stats.total_orders }}
                    </strong>

                    <small>
                        pedidos realizados
                    </small>
                </div>
            </div>

            <div class="customer-stat-card">
                <div class="customer-stat-icon stat-green">
                    <CircleCheck :size="19" />
                </div>

                <div class="customer-stat-content">
                    <span>
                        Completados
                    </span>

                    <strong>
                        {{ stats.completed_orders }}
                    </strong>

                    <small>
                        compras completadas
                    </small>
                </div>
            </div>

            <div class="customer-stat-card">
                <div class="customer-stat-icon stat-orange">
                    <Clock3 :size="19" />
                </div>

                <div class="customer-stat-content">
                    <span>
                        Pendientes
                    </span>

                    <strong>
                        {{ stats.pending_orders }}
                    </strong>

                    <small>
                        pedidos pendientes
                    </small>
                </div>
            </div>

            <div class="customer-stat-card">
                <div class="customer-stat-icon stat-purple">
                    <Package :size="19" />
                </div>

                <div class="customer-stat-content">
                    <span>
                        Total gastado
                    </span>

                    <strong>
                        {{ formatCurrency(stats.total_spent) }}
                    </strong>

                    <small>
                        compras acumuladas
                    </small>
                </div>
            </div>
        </section>

        <!-- =================================================
             RECENT ORDERS
        ================================================== -->

        <section class="customer-section">
            <div class="customer-section-header">
                <div>
                    <p class="customer-section-eyebrow">
                        Actividad reciente
                    </p>

                    <h2>
                        Mis últimos pedidos
                    </h2>
                </div>

                <Link
                    href="/pedidos"
                    class="customer-section-link"
                >
                    Ver todos
                    <ChevronRight :size="16" />
                </Link>
            </div>

            <div
                v-if="recentOrders.length"
                class="customer-orders-card"
            >
                <div
                    v-for="order in recentOrders"
                    :key="order.id"
                    class="customer-recent-order"
                >
                    <div class="recent-order-icon">
                        <Package :size="18" />
                    </div>

                    <div class="recent-order-main">
                        <strong>
                            {{ order.folio }}
                        </strong>

                        <span>
                            <CalendarDays :size="13" />
                            {{ formatDate(order.sold_at) }}
                        </span>
                    </div>

                    <div class="recent-order-items">
                        <span>
                            Productos
                        </span>

                        <strong>
                            {{
                                order.items_count ??
                                0
                            }}
                        </strong>
                    </div>

                    <div class="recent-order-status">
                        <span
                            class="status-badge"
                            :class="
                                getStatusClass(
                                    order.status,
                                )
                            "
                        >
                            <component
                                :is="
                                    getStatusIcon(
                                        order.status,
                                    )
                                "
                                :size="13"
                            />

                            {{ getStatusLabel(order.status) }}
                        </span>
                    </div>

                    <div class="recent-order-total">
                        <span>
                            Total
                        </span>

                        <strong>
                            {{ formatCurrency(order.total) }}
                        </strong>
                    </div>

                    <Link
                        :href="`/pedidos/${order.id}`"
                        class="recent-order-arrow"
                        title="Ver pedido"
                    >
                        <ChevronRight :size="18" />
                    </Link>
                </div>
            </div>

            <!-- EMPTY -->

            <div
                v-else
                class="customer-no-orders"
            >
                <div class="customer-no-orders-icon">
                    <ShoppingBag :size="30" />
                </div>

                <h3>
                    Todavía no tienes pedidos
                </h3>

                <p>
                    Cuando realices una compra,
                    aquí aparecerá tu historial reciente.
                </p>

                <Link
                    href="/pedidos"
                    class="customer-empty-link"
                >
                    Ver mis pedidos
                    <ArrowRight :size="15" />
                </Link>
            </div>
        </section>

        <!-- =================================================
             QUICK ACCESS
        ================================================== -->

        <section class="customer-quick-section">
            <div class="customer-section-header">
                <div>
                    <p class="customer-section-eyebrow">
                        Accesos rápidos
                    </p>

                    <h2>
                        ¿Qué quieres consultar?
                    </h2>
                </div>
            </div>

            <div class="customer-quick-grid">
                <Link
                    href="/pedidos"
                    class="customer-quick-card"
                >
                    <div class="customer-quick-icon blue">
                        <ShoppingBag :size="20" />
                    </div>

                    <div>
                        <strong>
                            Mis pedidos
                        </strong>

                        <span>
                            Consulta todas tus compras
                        </span>
                    </div>

                    <ChevronRight :size="17" />
                </Link>

                <Link
                    href="/settings/profile"
                    class="customer-quick-card"
                >
                    <div class="customer-quick-icon pink">
                        <Sparkles :size="20" />
                    </div>

                    <div>
                        <strong>
                            Mi perfil
                        </strong>

                        <span>
                            Consulta y actualiza tus datos
                        </span>
                    </div>

                    <ChevronRight :size="17" />
                </Link>
            </div>
        </section>
    </div>
</template>

<style scoped>
.customer-dashboard {
    width: 100%;
    margin: 0 auto;
    padding: 15px;
}

/* =========================================================
   WELCOME
========================================================= */

.customer-welcome {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 24px;
    padding: 25px 28px;
    margin-bottom: 20px;
    border: 1px solid #dcebf3;
    border-radius: 18px;
    background:
        linear-gradient(
            135deg,
            #f4fbfe 0%,
            #ffffff 58%,
            #fff7fc 100%
        );
}

.customer-welcome-content {
    display: flex;
    align-items: flex-start;
    gap: 15px;
}

.customer-welcome-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    flex: 0 0 auto;
    width: 43px;
    height: 43px;
    border-radius: 12px;
    background: #eaf6fc;
    color: #249edb;
}

.customer-eyebrow {
    margin: 0 0 4px;
    color: #249edb;
    font-size: 10px;
    font-weight: 750;
    letter-spacing: 0.09em;
    text-transform: uppercase;
}

.customer-welcome h1 {
    margin: 0;
    color: #172b4d;
    font-size: 25px;
    font-weight: 750;
    line-height: 1.2;
}

.customer-welcome-text {
    max-width: 650px;
    margin: 7px 0 0;
    color: #64748b;
    font-size: 12px;
    line-height: 1.55;
}

.customer-welcome-button {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex: 0 0 auto;
    gap: 7px;
    min-height: 40px;
    padding: 0 15px;
    border: 1px solid #249edb;
    border-radius: 10px;
    background: #249edb;
    color: #ffffff;
    font-size: 12px;
    font-weight: 700;
    text-decoration: none;
    transition:
        background 0.2s ease,
        border-color 0.2s ease,
        transform 0.2s ease;
}

.customer-welcome-button:hover {
    border-color: #1769a8;
    background: #1769a8;
    transform: translateY(-1px);
}

/* =========================================================
   STATS
========================================================= */

.customer-stats {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 14px;
    margin-bottom: 25px;
}

.customer-stat-card {
    display: flex;
    align-items: center;
    gap: 13px;
    min-width: 0;
    padding: 18px;
    border: 1px solid #e5ebef;
    border-radius: 15px;
    background: #ffffff;
    box-shadow: 0 3px 14px rgba(23, 43, 77, 0.035);
}

.customer-stat-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    flex: 0 0 auto;
    width: 42px;
    height: 42px;
    border-radius: 11px;
}

.stat-blue {
    background: #eaf6fc;
    color: #249edb;
}

.stat-green {
    background: #effaf4;
    color: #25845b;
}

.stat-orange {
    background: #fff8e9;
    color: #b77908;
}

.stat-purple {
    background: #f2effc;
    color: #6753b7;
}

.customer-stat-content {
    display: flex;
    flex-direction: column;
    min-width: 0;
}

.customer-stat-content span {
    color: #64748b;
    font-size: 10px;
    font-weight: 600;
}

.customer-stat-content strong {
    margin-top: 3px;
    overflow: hidden;
    color: #172b4d;
    font-size: 17px;
    font-weight: 750;
    line-height: 1.25;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.customer-stat-content small {
    margin-top: 3px;
    overflow: hidden;
    color: #94a3b8;
    font-size: 9px;
    text-overflow: ellipsis;
    white-space: nowrap;
}

/* Ajuste específico para Total gastado */

.customer-stat-card:last-child .customer-stat-content {
    min-width: 0;
    flex: 1;
}

.customer-stat-card:last-child .customer-stat-content strong {
    font-size: 18px;
}

/* =========================================================
   SECTION
========================================================= */

.customer-section {
    margin-bottom: 25px;
}

.customer-section-header {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 20px;
    margin-bottom: 12px;
}

.customer-section-eyebrow {
    margin: 0 0 3px;
    color: #249edb;
    font-size: 9px;
    font-weight: 750;
    letter-spacing: 0.08em;
    text-transform: uppercase;
}

.customer-section-header h2 {
    margin: 0;
    color: #172b4d;
    font-size: 18px;
    font-weight: 750;
}

.customer-section-link {
    display: inline-flex;
    align-items: center;
    gap: 3px;
    color: #249edb;
    font-size: 11px;
    font-weight: 700;
    text-decoration: none;
}

.customer-section-link:hover {
    color: #1769a8;
}

/* =========================================================
   RECENT ORDERS
========================================================= */

.customer-orders-card {
    overflow: hidden;
    border: 1px solid #e5ebef;
    border-radius: 15px;
    background: #ffffff;
    box-shadow: 0 3px 14px rgba(23, 43, 77, 0.035);
}

.customer-recent-order {
    display: grid;
    grid-template-columns: auto minmax(180px, 1fr) auto auto auto auto;
    align-items: center;
    gap: 16px;
    padding: 15px 17px;
    border-bottom: 1px solid #edf1f4;
}

.customer-recent-order:last-child {
    border-bottom: 0;
}

.recent-order-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 39px;
    height: 39px;
    border-radius: 10px;
    background: #f1f8fb;
    color: #249edb;
}

.recent-order-main {
    display: flex;
    flex-direction: column;
    min-width: 0;
    gap: 4px;
}

.recent-order-main strong {
    overflow: hidden;
    color: #172b4d;
    font-size: 12px;
    font-weight: 700;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.recent-order-main span {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    color: #94a3b8;
    font-size: 10px;
}

.recent-order-items,
.recent-order-total {
    display: flex;
    flex-direction: column;
    gap: 3px;
    min-width: 70px;
}

.recent-order-items span,
.recent-order-total span {
    color: #94a3b8;
    font-size: 9px;
}

.recent-order-items strong {
    color: #172b4d;
    font-size: 11px;
}

.recent-order-total {
    min-width: 85px;
    text-align: right;
}

.recent-order-total strong {
    color: #1769a8;
    font-size: 12px;
    font-weight: 750;
}

.status-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 5px;
    padding: 6px 9px;
    border-radius: 8px;
    font-size: 9px;
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

.status-default {
    border: 1px solid #dce9f1;
    background: #f3f9fc;
    color: #1769a8;
}

.recent-order-arrow {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 31px;
    height: 31px;
    border-radius: 8px;
    color: #94a3b8;
    text-decoration: none;
    transition:
        background 0.2s ease,
        color 0.2s ease;
}

.recent-order-arrow:hover {
    background: #eaf6fc;
    color: #249edb;
}

/* =========================================================
   EMPTY
========================================================= */

.customer-no-orders {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    min-height: 260px;
    padding: 30px;
    border: 1px solid #e5ebef;
    border-radius: 15px;
    background: #ffffff;
    text-align: center;
}

.customer-no-orders-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 58px;
    height: 58px;
    margin-bottom: 13px;
    border-radius: 50%;
    background: #eaf6fc;
    color: #249edb;
}

.customer-no-orders h3 {
    margin: 0;
    color: #172b4d;
    font-size: 15px;
    font-weight: 750;
}

.customer-no-orders p {
    max-width: 400px;
    margin: 6px 0 0;
    color: #64748b;
    font-size: 11px;
    line-height: 1.55;
}

.customer-empty-link {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    margin-top: 15px;
    color: #249edb;
    font-size: 11px;
    font-weight: 700;
    text-decoration: none;
}

/* =========================================================
   QUICK ACCESS
========================================================= */

.customer-quick-section {
    margin-bottom: 20px;
}

.customer-quick-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 14px;
}

.customer-quick-card {
    display: flex;
    align-items: center;
    gap: 13px;
    padding: 17px;
    border: 1px solid #e5ebef;
    border-radius: 14px;
    background: #ffffff;
    color: inherit;
    text-decoration: none;
    box-shadow: 0 3px 14px rgba(23, 43, 77, 0.035);
    transition:
        border-color 0.2s ease,
        box-shadow 0.2s ease,
        transform 0.2s ease;
}

.customer-quick-card:hover {
    border-color: #cce5f2;
    box-shadow: 0 7px 20px rgba(23, 43, 77, 0.06);
    transform: translateY(-1px);
}

.customer-quick-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    flex: 0 0 auto;
    width: 42px;
    height: 42px;
    border-radius: 11px;
}

.customer-quick-icon.blue {
    background: #eaf6fc;
    color: #249edb;
}

.customer-quick-icon.pink {
    background: #fdf0f8;
    color: #d94c9a;
}

.customer-quick-card > div:nth-child(2) {
    display: flex;
    flex: 1;
    flex-direction: column;
    gap: 3px;
    min-width: 0;
}

.customer-quick-card strong {
    color: #172b4d;
    font-size: 12px;
    font-weight: 700;
}

.customer-quick-card span {
    color: #64748b;
    font-size: 10px;
}

.customer-quick-card > svg {
    flex: 0 0 auto;
    color: #94a3b8;
}

/* =========================================================
   RESPONSIVE
========================================================= */

@media (max-width: 1100px) {
    .customer-stats {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .customer-recent-order {
        grid-template-columns: auto minmax(160px, 1fr) auto auto;
    }

    .recent-order-items {
        display: none;
    }
}

@media (max-width: 800px) {
    .customer-welcome {
        align-items: flex-start;
        flex-direction: column;
    }

    .customer-welcome-button {
        width: 100%;
    }

    .customer-recent-order {
        grid-template-columns: auto minmax(0, 1fr) auto auto;
    }

    .recent-order-total {
        display: none;
    }
}

@media (max-width: 640px) {
    .customer-dashboard {
        padding: 0;
    }

    .customer-stats {
        grid-template-columns: 1fr;
    }

    .customer-welcome {
        padding: 20px;
    }

    .customer-welcome-content {
        gap: 11px;
    }

    .customer-welcome h1 {
        font-size: 21px;
    }

    .customer-recent-order {
        grid-template-columns: auto minmax(0, 1fr) auto;
        gap: 10px;
        padding: 13px;
    }

    .recent-order-status {
        grid-column: 2 / -1;
    }

    .customer-quick-grid {
        grid-template-columns: 1fr;
    }

    .customer-section-header {
        align-items: flex-start;
    }
}
</style>