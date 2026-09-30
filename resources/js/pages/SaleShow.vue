<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { CheckCircle2, ChevronLeft, Package, ShoppingBag } from 'lucide-vue-next';
import LandingFooter from '@/components/landing/LandingFooter.vue';
import LandingHeader from '@/components/landing/LandingHeader.vue';

interface Customer {
    id: number;
    name: string;
    email: string;
}

interface Product {
    id: number;
    name: string;
    slug: string;
    image: string | null;
}

interface SaleItem {
    id: number;
    product_id: number;
    quantity: number;
    unit_price: number | string;
    subtotal: number | string;
    product: Product | null;
}

interface Sale {
    id: number;
    folio: string;
    customer_id: number | null;
    subtotal: number | string;
    discount: number | string;
    total: number | string;
    sales_channel: string;
    status: string;
    notes: string | null;
    sold_at: string | null;
    created_at: string;
    customer: Customer | null;
    items: SaleItem[];
}

const props = defineProps<{
    sale: Sale;
}>();

const getImageUrl = (image: string | null | undefined): string => {
    if (!image) {
        return 'https://images.pexels.com/photos/3769740/pexels-photo-3769740.jpeg?auto=compress&cs=tinysrgb&w=800';
    }

    if (
        image.startsWith('http://') ||
        image.startsWith('https://') ||
        image.startsWith('/storage/')
    ) {
        return image;
    }

    return `/storage/${image.replace(/^\/+/, '')}`;
};

const formatPrice = (value: number | string): string => {
    return new Intl.NumberFormat('es-MX', {
        style: 'currency',
        currency: 'MXN',
    }).format(Number(value));
};

const formatDate = (value: string): string => {
    return new Intl.DateTimeFormat('es-MX', {
        dateStyle: 'long',
        timeStyle: 'short',
    }).format(new Date(value));
};

const statusLabel = (status: string): string => {
    const labels: Record<string, string> = {
        pending: 'Pendiente de pago',
        paid: 'Pagado',
        partially_paid: 'Pago parcial',
        cancelled: 'Cancelado',
        refunded: 'Reembolsado',
    };

    return labels[status] ?? status;
};

const statusClass = (status: string): string => {
    const classes: Record<string, string> = {
        pending: 'status-pending',
        paid: 'status-paid',
        partially_paid: 'status-partial',
        cancelled: 'status-cancelled',
        refunded: 'status-refunded',
    };

    return classes[status] ?? 'status-pending';
};
</script>

<template>
    <Head :title="`Pedido ${sale.folio}`" />

    <div class="sale-page">
        <LandingHeader />

        <main class="sale-main">
            <section class="sale-hero">
                <div class="sale-hero-inner">
                    <div class="success-icon">
                        <CheckCircle2 :size="34" :stroke-width="2" />
                    </div>

                    <span class="eyebrow">SONRÍE CORRIENDO</span>

                    <h1>Pedido creado correctamente</h1>

                    <p>
                        Hemos registrado tu pedido. Guarda tu folio para
                        consultar la información de tu compra.
                    </p>

                    <div class="folio-box">
                        <span>Folio del pedido</span>
                        <strong>{{ sale.folio }}</strong>
                    </div>
                </div>
            </section>

            <section class="sale-content">
                <div class="sale-layout">
                    <div class="sale-card sale-items-card">
                        <div class="card-header">
                            <div>
                                <span class="card-eyebrow">Tu compra</span>
                                <h2>Productos del pedido</h2>
                            </div>

                            <div class="items-count">
                                {{ sale.items.length }}
                                {{ sale.items.length === 1 ? 'producto' : 'productos' }}
                            </div>
                        </div>

                        <div class="sale-items">
                            <article
                                v-for="item in sale.items"
                                :key="item.id"
                                class="sale-item"
                            >
                                <Link
                                    v-if="item.product"
                                    :href="`/productos/${item.product.slug}`"
                                    class="item-image"
                                >
                                    <img
                                        :src="getImageUrl(item.product.image)"
                                        :alt="item.product.name"
                                    />
                                </Link>

                                <div
                                    v-else
                                    class="item-image"
                                >
                                    <img
                                        :src="getImageUrl(null)"
                                        alt="Producto"
                                    />
                                </div>

                                <div class="item-info">
                                    <Link
                                        v-if="item.product"
                                        :href="`/productos/${item.product.slug}`"
                                        class="item-name"
                                    >
                                        {{ item.product.name }}
                                    </Link>

                                    <span
                                        v-else
                                        class="item-name"
                                    >
                                        Producto
                                    </span>

                                    <div class="item-meta">
                                        <span>
                                            Cantidad: {{ item.quantity }}
                                        </span>

                                        <span>
                                            {{ formatPrice(item.unit_price) }} c/u
                                        </span>
                                    </div>
                                </div>

                                <strong class="item-subtotal">
                                    {{ formatPrice(item.subtotal) }}
                                </strong>
                            </article>
                        </div>
                    </div>

                    <aside class="sale-sidebar">
                        <div class="sale-card summary-card">
                            <div class="card-header">
                                <div>
                                    <span class="card-eyebrow">Resumen</span>
                                    <h2>Total del pedido</h2>
                                </div>
                            </div>

                            <div
                                class="status-box"
                                :class="statusClass(sale.status)"
                            >
                                <Package :size="20" />

                                <div>
                                    <span>Estado</span>
                                    <strong>
                                        {{ statusLabel(sale.status) }}
                                    </strong>
                                </div>
                            </div>

                            <div class="summary-lines">
                                <div>
                                    <span>Subtotal</span>
                                    <strong>
                                        {{ formatPrice(sale.subtotal) }}
                                    </strong>
                                </div>

                                <div>
                                    <span>Descuento</span>
                                    <strong>
                                        {{ formatPrice(sale.discount) }}
                                    </strong>
                                </div>

                                <div class="summary-total">
                                    <span>Total</span>
                                    <strong>
                                        {{ formatPrice(sale.total) }}
                                    </strong>
                                </div>
                            </div>

                            <div class="order-date">
                                <span>Pedido realizado</span>
                                <strong>
                                    {{ formatDate(sale.created_at) }}
                                </strong>
                            </div>
                        </div>

                        <div
                            v-if="sale.customer"
                            class="sale-card customer-card"
                        >
                            <span class="card-eyebrow">Cliente</span>

                            <h3>{{ sale.customer.name }}</h3>

                            <p>{{ sale.customer.email }}</p>
                        </div>
                    </aside>
                </div>

                <div class="sale-actions">
                    <Link
                        href="/tienda"
                        class="secondary-button"
                    >
                        <ChevronLeft :size="18" />
                        Seguir comprando
                    </Link>

                    <Link
                        href="/carrito"
                        class="primary-button"
                    >
                        <ShoppingBag :size="18" />
                        Ver carrito
                    </Link>
                </div>
            </section>
        </main>

        <LandingFooter />
    </div>
</template>

<style scoped>
.sale-page {
    min-height: 100vh;
    background: #f8fafc;
    color: #172b4d;
}

.sale-main {
    width: 100%;
}

.sale-hero {
    position: relative;
    overflow: hidden;
    padding: 54px 24px 48px;
    background:
        linear-gradient(
            90deg,
            #12558cfa 0%,
            #1769a8e8 30%,
            #249edba3 54%,
            #d94c9a7a 78%,
            #f48bb052 100%
        );
}

.sale-hero::before,
.sale-hero::after {
    content: '';
    position: absolute;
    border-radius: 50%;
    pointer-events: none;
}

.sale-hero::before {
    width: 260px;
    height: 260px;
    top: -150px;
    right: 8%;
    background: rgba(255, 255, 255, 0.1);
}

.sale-hero::after {
    width: 180px;
    height: 180px;
    bottom: -110px;
    left: 8%;
    background: rgba(255, 255, 255, 0.08);
}

.sale-hero-inner {
    position: relative;
    z-index: 1;
    width: min(760px, 100%);
    margin: 0 auto;
    text-align: center;
    color: #fff;
}

.success-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 68px;
    height: 68px;
    margin: 0 auto 18px;
    border: 1px solid rgba(255, 255, 255, 0.32);
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.14);
}

.eyebrow {
    display: inline-block;
    margin-bottom: 10px;
    font-size: 12px;
    font-weight: 800;
    letter-spacing: 0.16em;
}

.sale-hero h1 {
    margin: 0;
    font-size: clamp(30px, 5vw, 46px);
    line-height: 1.05;
    font-weight: 850;
    letter-spacing: -0.035em;
}

.sale-hero p {
    max-width: 620px;
    margin: 14px auto 0;
    font-size: 16px;
    line-height: 1.65;
    color: rgba(255, 255, 255, 0.9);
}

.folio-box {
    display: inline-flex;
    flex-direction: column;
    gap: 4px;
    margin-top: 24px;
    padding: 12px 22px;
    border: 1px solid rgba(255, 255, 255, 0.25);
    border-radius: 14px;
    background: rgba(255, 255, 255, 0.12);
    backdrop-filter: blur(10px);
}

.folio-box span {
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: rgba(255, 255, 255, 0.75);
}

.folio-box strong {
    font-size: 20px;
    letter-spacing: 0.04em;
}

.sale-content {
    width: min(1120px, calc(100% - 40px));
    margin: 0 auto;
    padding: 38px 0 64px;
}

.sale-layout {
    display: grid;
    grid-template-columns: minmax(0, 1fr) 350px;
    gap: 24px;
    align-items: start;
}

.sale-card {
    border: 1px solid #e1ebf3;
    border-radius: 16px;
    background: #fff;
    box-shadow: 0 10px 28px rgba(23, 43, 77, 0.06);
}

.items-count {
    flex-shrink: 0;
    padding: 7px 11px;
    border-radius: 999px;
    background: #eaf6fc;
    color: #1769a8;
    font-size: 12px;
    font-weight: 800;
}

.card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    padding: 22px 24px;
    border-bottom: 1px solid #edf2f7;
}

.card-eyebrow {
    display: block;
    margin-bottom: 4px;
    color: #249edb;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 0.1em;
    text-transform: uppercase;
}

.card-header h2 {
    margin: 0;
    color: #172b4d;
    font-size: 20px;
    font-weight: 800;
}

.sale-items {
    padding: 4px 24px;
}

.sale-item {
    display: grid;
    grid-template-columns: 72px minmax(0, 1fr) auto;
    align-items: center;
    gap: 16px;
    padding: 18px 0;
    border-bottom: 1px solid #edf2f7;
}

.sale-item:last-child {
    border-bottom: 0;
}

.item-image {
    display: block;
    width: 72px;
    height: 72px;
    overflow: hidden;
    border-radius: 12px;
    background: #eaf6fc;
}

.item-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.item-info {
    min-width: 0;
}

.item-name {
    display: block;
    overflow: hidden;
    color: #172b4d;
    font-size: 15px;
    font-weight: 800;
    line-height: 1.35;
    text-decoration: none;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.item-name:hover {
    color: #1769a8;
}

.item-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 6px 16px;
    margin-top: 6px;
    color: #718096;
    font-size: 13px;
}

.item-subtotal {
    color: #172b4d;
    font-size: 15px;
    white-space: nowrap;
}

.sale-sidebar {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.summary-card {
    overflow: hidden;
}

.status-box {
    display: flex;
    align-items: center;
    gap: 12px;
    margin: 20px 24px;
    padding: 13px 14px;
    border: 1px solid;
    border-radius: 12px;
}

.status-box span {
    display: block;
    margin-bottom: 2px;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.06em;
}

.status-box strong {
    display: block;
    font-size: 14px;
}

.status-pending {
    border-color: #f2d18b;
    background: #fff8e8;
    color: #9a6700;
}

.status-paid {
    border-color: #a8dfce;
    background: #ecfaf5;
    color: #087f5b;
}

.status-partial {
    border-color: #b9d5f2;
    background: #eef7ff;
    color: #1769a8;
}

.status-cancelled {
    border-color: #f2b8bf;
    background: #fff1f3;
    color: #b42332;
}

.status-refunded {
    border-color: #d6c8ed;
    background: #f7f3fc;
    color: #6753b7;
}

.summary-lines {
    padding: 0 24px;
}

.summary-lines > div {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    padding: 10px 0;
    color: #64748b;
    font-size: 14px;
}

.summary-lines strong {
    color: #172b4d;
}

.summary-total {
    margin-top: 6px;
    padding-top: 16px !important;
    border-top: 1px solid #e1ebf3;
    color: #172b4d !important;
    font-size: 17px !important;
    font-weight: 800;
}

.summary-total strong {
    color: #1769a8;
    font-size: 23px;
}

.order-date {
    display: flex;
    flex-direction: column;
    gap: 4px;
    margin-top: 12px;
    padding: 18px 24px 22px;
    border-top: 1px solid #edf2f7;
}

.order-date span {
    color: #718096;
    font-size: 12px;
}

.order-date strong {
    color: #172b4d;
    font-size: 13px;
}

.customer-card {
    padding: 20px 24px;
}

.customer-card h3 {
    margin: 5px 0 3px;
    color: #172b4d;
    font-size: 16px;
    font-weight: 800;
}

.customer-card p {
    margin: 0;
    color: #718096;
    font-size: 13px;
    word-break: break-word;
}

.sale-actions {
    display: flex;
    justify-content: center;
    gap: 12px;
    margin-top: 28px;
}

.primary-button,
.secondary-button {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    min-height: 46px;
    padding: 0 20px;
    border-radius: 12px;
    font-size: 14px;
    font-weight: 800;
    text-decoration: none;
    transition:
        transform 0.2s ease,
        box-shadow 0.2s ease,
        background 0.2s ease;
}

.primary-button {
    border: 1px solid #1769a8;
    background: #1769a8;
    color: #fff;
    box-shadow: 0 8px 18px rgba(23, 105, 168, 0.18);
}

.primary-button:hover {
    transform: translateY(-1px);
    background: #12558c;
}

.secondary-button {
    border: 1px solid #d8e5ef;
    background: #fff;
    color: #1769a8;
}

.secondary-button:hover {
    transform: translateY(-1px);
    background: #eaf6fc;
}

@media (max-width: 900px) {
    .sale-layout {
        grid-template-columns: 1fr;
    }

    .sale-sidebar {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (max-width: 640px) {
    .sale-hero {
        padding: 42px 18px 38px;
    }

    .sale-content {
        width: min(100% - 24px, 1120px);
        padding-top: 24px;
    }

    .sale-layout {
        gap: 16px;
    }

    .sale-sidebar {
        display: flex;
    }

    .card-header {
        padding: 18px;
    }

    .sale-items {
        padding: 0 18px;
    }

    .sale-item {
        grid-template-columns: 58px minmax(0, 1fr);
        gap: 12px;
    }

    .item-image {
        width: 58px;
        height: 58px;
    }

    .item-subtotal {
        grid-column: 2;
        justify-self: start;
        margin-top: -4px;
    }

    .status-box {
        margin: 18px;
    }

    .summary-lines {
        padding: 0 18px;
    }

    .order-date {
        padding: 16px 18px 18px;
    }

    .customer-card {
        padding: 18px;
    }

    .sale-actions {
        flex-direction: column-reverse;
    }

    .primary-button,
    .secondary-button {
        width: 100%;
    }
}
</style>