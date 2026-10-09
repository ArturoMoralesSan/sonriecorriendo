
<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    ArrowLeft,
    CalendarDays,
    CheckCircle2,
    Clock3,
    CreditCard,
    FileText,
    MapPin,
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

interface Branch {
    id: number;
    name: string;
    street: string | null;
    exterior_number: string | null;
    interior_number: string | null;
    neighborhood: string | null;
    postal_code: string | null;
    city: string | null;
    state: string | null;
    phone: string | null;
    full_address?: string | null;
}

interface DeliveryAddress {
    delivery_method: string | null;
    branch_id: number | null;
    branch_name: string | null;
    branch_address: string | null;
    street: string | null;
    exterior_number: string | null;
    interior_number: string | null;
    neighborhood: string | null;
    postal_code: string | null;
    city: string | null;
    state: string | null;
    references: string | null;
    branch: Branch | null;
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
    delivery_address: DeliveryAddress | null;
}

defineProps<{
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
    }).format(Number(value ?? 0));
};

const formatDate = (date: string | null): string => {
    if (!date) {
        return 'Fecha no disponible';
    }

    const parsedDate = new Date(date);

    if (Number.isNaN(parsedDate.getTime())) {
        return 'Fecha no disponible';
    }

    return new Intl.DateTimeFormat('es-MX', {
        day: '2-digit',
        month: 'long',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    }).format(parsedDate);
};

/* =========================================================
   ESTADO DEL PEDIDO
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

/* =========================================================
   MÉTODO DE ENTREGA
========================================================= */

/**
 * Los valores corresponden a DeliveryAddress.php:
 * home_delivery = entrega a domicilio
 * branch_pickup = recoger en sucursal
 *
 * branch_id sirve como respaldo si delivery_method está vacío.
 */
const isBranchDelivery = (address: DeliveryAddress): boolean => {
    if (address.delivery_method === 'branch_pickup') {
        return true;
    }

    if (address.delivery_method === 'home_delivery') {
        return false;
    }

    return address.branch_id !== null
        && address.branch_id !== undefined;
};

const getDeliveryMethodLabel = (
    address: DeliveryAddress,
): string => {
    return isBranchDelivery(address)
        ? 'Recoger en sucursal'
        : 'Entrega a domicilio';
};

/* =========================================================
   DIRECCIÓN DE SUCURSAL
========================================================= */

const getBranchName = (address: DeliveryAddress): string => {
    return (
        address.branch_name
        || address.branch?.name
        || 'Sucursal'
    );
};

const getBranchStreet = (address: DeliveryAddress): string => {
    const branch = address.branch;

    if (!branch) {
        return address.branch_address || '';
    }

    return [
        branch.street,
        branch.exterior_number
            ? `No. ${branch.exterior_number}`
            : null,
        branch.interior_number
            ? `Interior ${branch.interior_number}`
            : null,
    ]
        .filter(Boolean)
        .join(', ');
};

const getBranchLocation = (address: DeliveryAddress): string => {
    const branch = address.branch;

    if (!branch) {
        return '';
    }

    return [
        branch.neighborhood
            ? `Colonia ${branch.neighborhood}`
            : null,
        branch.postal_code
            ? `C.P. ${branch.postal_code}`
            : null,
        branch.city,
        branch.state,
    ]
        .filter(Boolean)
        .join(', ');
};

const getBranchFullAddress = (
    address: DeliveryAddress,
): string => {
    if (address.branch?.full_address) {
        return address.branch.full_address;
    }

    return [
        getBranchStreet(address),
        getBranchLocation(address),
    ]
        .filter(Boolean)
        .join(', ');
};

/* =========================================================
   DIRECCIÓN A DOMICILIO
========================================================= */

const getHomeStreet = (address: DeliveryAddress): string => {
    return [
        address.street,
        address.exterior_number
            ? `No. ${address.exterior_number}`
            : null,
        address.interior_number
            ? `Interior ${address.interior_number}`
            : null,
    ]
        .filter(Boolean)
        .join(', ');
};

const getHomeLocation = (address: DeliveryAddress): string => {
    return [
        address.neighborhood
            ? `Colonia ${address.neighborhood}`
            : null,
        address.postal_code
            ? `C.P. ${address.postal_code}`
            : null,
        address.city,
        address.state,
    ]
        .filter(Boolean)
        .join(', ');
};

const getDeliverySummary = (address: DeliveryAddress): string => {
    if (isBranchDelivery(address)) {
        return getBranchName(address);
    }

    return (
        address.city
        || address.neighborhood
        || address.street
        || 'Dirección registrada'
    );
};
</script>

<template>
    <Head :title="`Pedido ${order.folio}`" />

    <div class="order-show">
        <!-- ENCABEZADO -->

        <div class="order-header">
            <Link href="/pedidos" class="back-link">
                <ArrowLeft :size="16" />
                Volver a mis pedidos
            </Link>

            <div class="order-title-row">
                <div>
                    <p class="order-eyebrow">
                        Detalle de pedido
                    </p>

                    <h1>{{ order.folio }}</h1>

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

        <div class="order-layout">
            <div class="order-main">
                <!-- PRODUCTOS -->

                <section class="order-card">
                    <div class="card-header">
                        <div>
                            <p class="card-eyebrow">Productos</p>
                            <h2>Artículos de tu pedido</h2>
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
                                    :src="
                                        item.image.startsWith('http')
                                            || item.image.startsWith('/storage/')
                                            ? item.image
                                            : `/storage/${item.image}`
                                    "
                                    :alt="item.name"
                                />

                                <Package v-else :size="24" />
                            </div>

                            <div class="product-info">
                                <strong>{{ item.name }}</strong>
                                <span>Cantidad: {{ item.quantity }}</span>
                            </div>

                            <div class="product-price">
                                <span>
                                    {{ formatCurrency(item.unit_price) }} c/u
                                </span>

                                <strong>
                                    {{ formatCurrency(item.subtotal) }}
                                </strong>
                            </div>
                        </div>

                        <div
                            v-if="!order.items.length"
                            class="empty-state"
                        >
                            No hay productos registrados en este pedido.
                        </div>
                    </div>
                </section>

                <!-- ENTREGA -->

                <section
                    v-if="order.delivery_address"
                    class="order-card"
                >
                    <div class="card-header">
                        <div>
                            <p class="card-eyebrow">Entrega</p>
                            <h2>Información de entrega</h2>
                        </div>

                        <component
                            :is="
                                isBranchDelivery(order.delivery_address)
                                    ? Store
                                    : MapPin
                            "
                            :size="19"
                        />
                    </div>

                    <div class="delivery-address">
                        <div class="delivery-address-icon">
                            <component
                                :is="
                                    isBranchDelivery(order.delivery_address)
                                        ? Store
                                        : MapPin
                                "
                                :size="21"
                            />
                        </div>

                        <div class="delivery-address-content">
                            <span class="delivery-method">
                                {{
                                    getDeliveryMethodLabel(
                                        order.delivery_address,
                                    )
                                }}
                            </span>

                            <!-- RECOGER EN SUCURSAL -->

                            <template
                                v-if="
                                    isBranchDelivery(order.delivery_address)
                                "
                            >
                                <strong class="delivery-recipient">
                                    {{ getBranchName(order.delivery_address) }}
                                </strong>

                                <p
                                    v-if="
                                        getBranchFullAddress(
                                            order.delivery_address,
                                        )
                                    "
                                >
                                    {{
                                        getBranchFullAddress(
                                            order.delivery_address,
                                        )
                                    }}
                                </p>

                                <p
                                    v-if="
                                        order.delivery_address.branch?.phone
                                    "
                                    class="delivery-phone"
                                >
                                    <strong>Teléfono:</strong>
                                    {{ order.delivery_address.branch.phone }}
                                </p>

                                <p
                                    v-if="
                                        !order.delivery_address.branch
                                        && !order.delivery_address.branch_address
                                    "
                                    class="delivery-unavailable"
                                >
                                    No hay información de dirección de la
                                    sucursal disponible para este pedido.
                                </p>
                            </template>

                            <!-- ENTREGA A DOMICILIO -->

                            <template v-else>
                                <p
                                    v-if="
                                        getHomeStreet(order.delivery_address)
                                    "
                                >
                                    {{
                                        getHomeStreet(
                                            order.delivery_address,
                                        )
                                    }}
                                </p>

                                <p
                                    v-if="
                                        getHomeLocation(order.delivery_address)
                                    "
                                >
                                    {{
                                        getHomeLocation(
                                            order.delivery_address,
                                        )
                                    }}
                                </p>

                                <p
                                    v-if="
                                        !getHomeStreet(order.delivery_address)
                                        && !getHomeLocation(
                                            order.delivery_address,
                                        )
                                    "
                                    class="delivery-unavailable"
                                >
                                    No hay información de domicilio registrada.
                                </p>

                                <div
                                    v-if="
                                        order.delivery_address.references
                                    "
                                    class="delivery-references"
                                >
                                    <strong>Referencias de entrega</strong>
                                    <p>
                                        {{ order.delivery_address.references }}
                                    </p>
                                </div>
                            </template>
                        </div>
                    </div>
                </section>

                <!-- PAGOS -->

                <section
                    v-if="order.payments.length"
                    class="order-card"
                >
                    <div class="card-header">
                        <div>
                            <p class="card-eyebrow">Pagos</p>
                            <h2>Métodos de pago</h2>
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
                                <strong>{{ payment.method }}</strong>

                                <span v-if="payment.reference">
                                    Referencia: {{ payment.reference }}
                                </span>

                                <span v-if="payment.notes">
                                    {{ payment.notes }}
                                </span>
                            </div>

                            <strong class="payment-amount">
                                {{ formatCurrency(payment.amount) }}
                            </strong>
                        </div>
                    </div>
                </section>

                <!-- NOTAS -->

                <section
                    v-if="order.notes"
                    class="order-card"
                >
                    <div class="card-header">
                        <div>
                            <p class="card-eyebrow">Información adicional</p>
                            <h2>Notas del pedido</h2>
                        </div>

                        <FileText :size="19" />
                    </div>

                    <div class="order-notes">
                        {{ order.notes }}
                    </div>
                </section>
            </div>

            <!-- COLUMNA LATERAL -->

            <aside class="order-sidebar">
                <!-- RESUMEN -->

                <section class="summary-card">
                    <div class="summary-header">
                        <div class="summary-icon">
                            <ShoppingBag :size="19" />
                        </div>

                        <div>
                            <p>Resumen del pedido</p>
                            <strong>{{ order.folio }}</strong>
                        </div>
                    </div>

                    <div class="summary-lines">
                        <div class="summary-line">
                            <span>Subtotal</span>
                            <strong>
                                {{ formatCurrency(order.subtotal) }}
                            </strong>
                        </div>

                        <div
                            v-if="Number(order.discount) > 0"
                            class="summary-line discount"
                        >
                            <span>Descuento</span>
                            <strong>
                                -{{ formatCurrency(order.discount) }}
                            </strong>
                        </div>

                        <div class="summary-divider"></div>

                        <div class="summary-total">
                            <span>Total</span>
                            <strong>
                                {{ formatCurrency(order.total) }}
                            </strong>
                        </div>
                    </div>
                </section>

                <!-- INFORMACIÓN DEL PEDIDO -->

                <section class="info-card">
                    <div class="info-card-header">
                        <h3>Información del pedido</h3>
                    </div>

                    <div class="info-list">
                        <div class="info-row">
                            <div class="info-row-icon">
                                <CalendarDays :size="15" />
                            </div>

                            <div>
                                <span>Fecha</span>
                                <strong>
                                    {{
                                        formatDate(
                                            order.sold_at ?? order.created_at,
                                        )
                                    }}
                                </strong>
                            </div>
                        </div>

                        <div class="info-row">
                            <div class="info-row-icon">
                                <Store :size="15" />
                            </div>

                            <div>
                                <span>Canal de venta</span>
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
                                <span>Estado</span>
                                <strong>
                                    {{ getStatusLabel(order.status) }}
                                </strong>
                            </div>
                        </div>

                        <div
                            v-if="order.delivery_address"
                            class="info-row"
                        >
                            <div class="info-row-icon">
                                <component
                                    :is="
                                        isBranchDelivery(order.delivery_address)
                                            ? Store
                                            : MapPin
                                    "
                                    :size="15"
                                />
                            </div>

                            <div>
                                <span>Método de entrega</span>
                                <strong>
                                    {{
                                        getDeliveryMethodLabel(
                                            order.delivery_address,
                                        )
                                    }}
                                </strong>
                            </div>
                        </div>

                        <div
                            v-if="order.delivery_address"
                            class="info-row"
                        >
                            <div class="info-row-icon">
                                <MapPin :size="15" />
                            </div>

                            <div>
                                <span>
                                    {{
                                        isBranchDelivery(order.delivery_address)
                                            ? 'Sucursal'
                                            : 'Ubicación'
                                    }}
                                </span>

                                <strong>
                                    {{
                                        getDeliverySummary(
                                            order.delivery_address,
                                        )
                                    }}
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

/* ENCABEZADO */

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

/* ESTADO */

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

/* DISTRIBUCIÓN */

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
    min-width: 0;
}

.order-sidebar {
    display: flex;
    flex-direction: column;
    gap: 16px;
    min-width: 0;
}

/* TARJETAS */

.order-card,
.info-card {
    overflow: hidden;
    border: 1px solid #e5ebef;
    border-radius: 15px;
    background: #fff;
    box-shadow: 0 3px 14px rgb(23 43 77 / 3.5%);
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
    flex-shrink: 0;
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

/* PRODUCTOS */

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

.empty-state {
    padding: 22px 18px;
    color: #94a3b8;
    font-size: 12px;
    text-align: center;
}

/* DIRECCIÓN DE ENTREGA */

.delivery-address {
    display: flex;
    align-items: flex-start;
    gap: 14px;
    padding: 18px;
}

.delivery-address-icon {
    display: flex;
    flex: 0 0 auto;
    align-items: center;
    justify-content: center;
    width: 42px;
    height: 42px;
    border-radius: 11px;
    background: #eaf6fc;
    color: #249edb;
}

.delivery-address-content {
    display: flex;
    flex: 1;
    flex-direction: column;
    align-items: flex-start;
    gap: 7px;
    min-width: 0;
}

.delivery-method {
    display: inline-flex;
    align-items: center;
    padding: 5px 8px;
    border: 1px solid #dcebf3;
    border-radius: 7px;
    background: #f2f9fd;
    color: #1769a8;
    font-size: 10px;
    font-weight: 750;
}

.delivery-recipient {
    color: #172b4d;
    font-size: 13px;
    font-weight: 750;
}

.delivery-address-content > p {
    margin: 0;
    color: #64748b;
    font-size: 11px;
    line-height: 1.7;
    overflow-wrap: anywhere;
}

.delivery-address-content .delivery-phone {
    margin-top: 2px;
}

.delivery-phone strong {
    color: #334155;
}

.delivery-references {
    width: 100%;
    margin-top: 5px;
    padding: 11px 12px;
    border: 1px solid #e5edf2;
    border-radius: 9px;
    background: #f8fafc;
}

.delivery-references strong {
    display: block;
    margin-bottom: 4px;
    color: #334155;
    font-size: 10px;
}

.delivery-references p {
    margin: 0;
    color: #64748b;
    font-size: 11px;
    line-height: 1.6;
    overflow-wrap: anywhere;
}

.delivery-address-content .delivery-unavailable {
    color: #94a3b8;
    font-style: italic;
}

/* PAGOS */

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
    flex: 0 0 auto;
    align-items: center;
    justify-content: center;
    width: 38px;
    height: 38px;
    border-radius: 10px;
    background: #f1f8fb;
    color: #249edb;
}

.payment-info {
    display: flex;
    flex: 1;
    flex-direction: column;
    gap: 4px;
    min-width: 0;
}

.payment-info strong {
    color: #172b4d;
    font-size: 12px;
    font-weight: 700;
}

.payment-info span {
    color: #94a3b8;
    font-size: 10px;
    overflow-wrap: anywhere;
}

.payment-amount {
    color: #1769a8;
    font-size: 12px;
    font-weight: 750;
    white-space: nowrap;
}

/* NOTAS */

.order-notes {
    padding: 17px 18px;
    color: #64748b;
    font-size: 12px;
    line-height: 1.8;
    overflow-wrap: anywhere;
}

/* RESUMEN */

.summary-card {
    overflow: hidden;
    border: 1px solid #dcebf3;
    border-radius: 15px;
    background: #fff;
    box-shadow: 0 3px 14px rgb(23 43 77 / 3.5%);
}

.summary-header {
    display: flex;
    align-items: center;
    gap: 11px;
    padding: 17px 18px;
    border-bottom: 1px solid #edf1f4;
}

.summary-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    border-radius: 11px;
    background: #eaf6fc;
    color: #249edb;
}

.summary-header p {
    margin: 0 0 4px;
    color: #94a3b8;
    font-size: 10px;
}

.summary-header strong {
    color: #172b4d;
    font-size: 13px;
    font-weight: 750;
}

.summary-lines {
    padding: 17px 18px;
}

.summary-line {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 13px;
}

.summary-line span {
    color: #64748b;
    font-size: 11px;
}

.summary-line strong {
    color: #334155;
    font-size: 11px;
    font-weight: 700;
}

.summary-line.discount strong {
    color: #25845b;
}

.summary-divider {
    height: 1px;
    margin: 5px 0 15px;
    background: #edf1f4;
}

.summary-total {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
}

.summary-total span {
    color: #172b4d;
    font-size: 12px;
    font-weight: 750;
}

.summary-total strong {
    color: #1769a8;
    font-size: 22px;
    font-weight: 800;
    letter-spacing: -0.04em;
}

/* INFORMACIÓN DEL PEDIDO */

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
    gap: 0;
    padding: 5px 17px;
}

.info-row {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    padding: 12px 0;
    border-bottom: 1px solid #f0f3f6;
}

.info-row:last-child {
    border-bottom: 0;
}

.info-row-icon {
    display: flex;
    flex: 0 0 auto;
    align-items: center;
    justify-content: center;
    width: 29px;
    height: 29px;
    border-radius: 8px;
    background: #f1f8fb;
    color: #249edb;
}

.info-row > div:last-child {
    display: flex;
    flex: 1;
    flex-direction: column;
    gap: 4px;
    min-width: 0;
}

.info-row span {
    color: #94a3b8;
    font-size: 10px;
}

.info-row strong {
    color: #334155;
    font-size: 11px;
    font-weight: 700;
    line-height: 1.6;
    overflow-wrap: anywhere;
}

/* REGRESAR */

.back-orders-button {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    min-height: 43px;
    padding: 11px 15px;
    border: 1px solid #dcebf3;
    border-radius: 10px;
    background: #fff;
    color: #1769a8;
    font-size: 11px;
    font-weight: 750;
    text-decoration: none;
    transition:
        background 0.2s ease,
        border-color 0.2s ease;
}

.back-orders-button:hover {
    border-color: #a9d9f2;
    background: #f2f9fd;
}

/* RESPONSIVE */

@media (max-width: 1024px) {
    .order-layout {
        grid-template-columns: minmax(0, 1fr) 290px;
        gap: 15px;
    }

    .order-main {
        gap: 15px;
    }

    .order-sidebar {
        gap: 13px;
    }
}

@media (max-width: 800px) {
    .order-layout {
        grid-template-columns: minmax(0, 1fr);
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

@media (max-width: 560px) {
    .order-title-row {
        align-items: flex-start;
        flex-direction: column;
        gap: 12px;
    }

    .order-title-row h1 {
        font-size: 22px;
        overflow-wrap: anywhere;
    }

    .order-sidebar {
        display: flex;
        flex-direction: column;
    }

    .order-item {
        grid-template-columns: 46px minmax(0, 1fr);
        gap: 11px;
        padding: 13px;
    }

    .product-image {
        width: 46px;
        height: 46px;
    }

    .product-price {
        grid-column: 2;
        flex-direction: row;
        align-items: center;
        justify-content: space-between;
        width: 100%;
        margin-top: -2px;
    }

    .product-info strong {
        white-space: normal;
    }

    .card-header {
        padding: 15px;
    }

    .delivery-address {
        gap: 11px;
        padding: 15px;
    }

    .delivery-address-icon {
        width: 36px;
        height: 36px;
    }

    .payment-row {
        flex-wrap: wrap;
        padding: 13px 15px;
    }

    .payment-amount {
        margin-left: 50px;
    }

    .summary-total strong {
        font-size: 20px;
    }
}
</style>