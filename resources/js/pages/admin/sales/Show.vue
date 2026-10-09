<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    ArrowLeft,
    CalendarDays,
    CheckCircle2,
    CreditCard,
    FileText,
    Hash,
    MapPin,
    Package,
    Store,
    Printer,
    Receipt,
    ShoppingCart,
    UserRound,
    Wallet,
} from 'lucide-vue-next';
import { computed } from 'vue';

import admin from '@/routes/admin';

interface Customer {
    id: number;
    name: string;
    email?: string | null;
    qr_token?: string | null;
}

interface Product {
    id: number;
    name: string;
    slug?: string | null;
    image?: string | null;
    type?: string | null;
    year?: number | string | null;
}

interface SaleItem {
    id: number;
    product_id: number;
    quantity: number;
    price: number | string;
    subtotal: number | string;
    product?: Product | null;
}

interface PaymentMethod {
    id: number;
    name: string;
    code?: string | null;
}

interface SalePayment {
    id: number;
    payment_method_id: number;
    amount: number | string;
    reference?: string | null;
    payment_method?: PaymentMethod | null;
}

interface DeliveryAddress {
    id?: number;
    delivery_method?: string | null;
    branch_id?: number | null;
    branch_name?: string | null;
    branch_address?: string | null;
    street?: string | null;
    exterior_number?: string | null;
    interior_number?: string | null;
    neighborhood?: string | null;
    postal_code?: string | null;
    city?: string | null;
    state?: string | null;
    references?: string | null;
    branch?: {
        id?: number;
        name?: string | null;
        street?: string | null;
        exterior_number?: string | null;
        interior_number?: string | null;
        neighborhood?: string | null;
        postal_code?: string | null;
        city?: string | null;
        state?: string | null;
        phone?: string | null;
    } | null;
}

interface Sale {
    id: number;
    customer_id?: number | null;
    subtotal: number | string;
    discount: number | string;
    total: number | string;
    channel?: string | null;
    sales_channel?: string | null;
    status?: string | null;
    notes?: string | null;
    created_at: string;
    updated_at?: string | null;
    customer?: Customer | null;
    items?: SaleItem[] | null;
    payments?: SalePayment[] | null;
    delivery_address?: DeliveryAddress | null;
}

const props = defineProps<{
    sale: Sale;
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
            {
                title: 'Detalle de venta',
                href: admin.sales.index(),
            },
        ],
    },
});

const saleItems = computed<SaleItem[]>(() => {
    return Array.isArray(props.sale?.items)
        ? props.sale.items
        : [];
});

const salePayments = computed<SalePayment[]>(() => {
    return Array.isArray(props.sale?.payments)
        ? props.sale.payments
        : [];
});

const formatCurrency = (
    value: number | string | null | undefined,
) => {
    const amount = Number(value ?? 0);

    return new Intl.NumberFormat('es-MX', {
        style: 'currency',
        currency: 'MXN',
    }).format(amount);
};

const formatDate = (value: string | null | undefined) => {
    if (!value) {
        return '—';
    }

    const date = new Date(value);

    if (Number.isNaN(date.getTime())) {
        return value;
    }

    return new Intl.DateTimeFormat('es-MX', {
        dateStyle: 'medium',
        timeStyle: 'short',
    }).format(date);
};

const getSaleChannelLabel = (sale: Sale) => {
    const channel = sale.sales_channel ?? sale.channel ?? '';

    const labels: Record<string, string> = {
        web: 'Web',
        online: 'Web',
        store: 'Tienda',
        in_store: 'Tienda',
        pos: 'Punto de venta',
        admin: 'Administración',
        manual: 'Manual',
    };

    return labels[channel] ?? (channel || '—');
};

const getStatusLabel = (
    status: string | null | undefined,
) => {
    const labels: Record<string, string> = {
        pending: 'Pendiente',
        completed: 'Completada',
        paid: 'Pagada',
        cancelled: 'Cancelada',
        refunded: 'Reembolsada',
    };

    return labels[status ?? ''] ?? (status || '—');
};

const getStatusClass = (
    status: string | null | undefined,
) => {
    switch (status) {
        case 'completed':
        case 'paid':
            return 'status-success';

        case 'pending':
            return 'status-warning';

        case 'cancelled':
        case 'refunded':
            return 'status-danger';

        default:
            return 'status-neutral';
    }
};

const getPaymentMethodName = (
    payment: SalePayment,
) => {
    return payment.payment_method?.name
        ?? `Método #${payment.payment_method_id}`;
};

const getProductImage = (
    product?: Product | null,
) => {
    if (!product?.image) {
        return null;
    }

    if (
        product.image.startsWith('http://') ||
        product.image.startsWith('https://') ||
        product.image.startsWith('/')
    ) {
        return product.image;
    }

    return `/storage/${product.image}`;
};

const deliveryAddress = computed<DeliveryAddress | null>(() => {
    return props.sale?.delivery_address ?? null;
});

const getDeliveryMethodLabel = (address?: DeliveryAddress | null) => {
    if (!address?.delivery_method) return 'No especificado';

    const labels: Record<string, string> = {
        home_delivery: 'Entrega a domicilio',
        branch_pickup: 'Recoger en sucursal',
    };

    return labels[address.delivery_method] ?? address.delivery_method;
};

const getDeliveryAddressText = (address?: DeliveryAddress | null) => {
    if (!address) return '';

    if (address.delivery_method === 'branch_pickup') {
        const branch = address.branch;
        const branchAddress = address.branch_address
            || (branch ? [
                branch.street,
                branch.exterior_number ? `No. ${branch.exterior_number}` : null,
                branch.interior_number ? `Int. ${branch.interior_number}` : null,
                branch.neighborhood,
                branch.postal_code ? `C.P. ${branch.postal_code}` : null,
                branch.city,
                branch.state,
            ].filter(Boolean).join(', ') : '');

        return [address.branch_name || branch?.name, branchAddress]
            .filter(Boolean)
            .join(' — ');
    }

    return [
        address.street,
        address.exterior_number ? `No. ${address.exterior_number}` : null,
        address.interior_number ? `Int. ${address.interior_number}` : null,
        address.neighborhood,
        address.postal_code ? `C.P. ${address.postal_code}` : null,
        address.city,
        address.state,
    ].filter(Boolean).join(', ');
};

/*
|--------------------------------------------------------------------------
| IMPRESIÓN TÉRMICA 80 MM
|--------------------------------------------------------------------------
*/

const printSale = () => {
    const printView = document.querySelector(
        '.print-view',
    ) as HTMLElement | null;

    if (!printView) {
        return;
    }

    const printWindow = window.open(
        '',
        '_blank',
        'width=420,height=750',
    );

    if (!printWindow) {
        window.alert(
            'Permite las ventanas emergentes para poder imprimir la venta.',
        );

        return;
    }

    const printStyles = `
        @page {
            size: 80mm auto;
            margin: 0;
        }

        * {
            box-sizing: border-box;
        }

        html {
            width: 80mm;
            min-width: 80mm;
            max-width: 80mm;
            margin: 0;
            padding: 0;
            background: #fff;
        }

        body {
            width: 80mm;
            min-width: 80mm;
            max-width: 80mm;

            margin: 0;
            padding: 0;

            background: #fff;
            color: #000;

            font-family: Arial, Helvetica, sans-serif;
            font-size: 10px;
            line-height: 1.3;
        }

        .print-document {
            width: 80mm;
            max-width: 80mm;

            margin: 0;
            padding: 4mm 4mm 5mm;

            background: #fff;
            color: #000;
        }

        /* =================================================
           HEADER
           ================================================= */

        .print-header {
            width: 100%;
            margin: 0;
            padding: 0 0 3mm;

            text-align: center;
        }

        .print-header h1 {
            margin: 0;
            padding: 0;

            color: #000;

            font-size: 17px;
            font-weight: 800;
            line-height: 1.2;
        }

        .print-header p {
            margin: 1mm 0 0;
            padding: 0;

            color: #000;

            font-size: 9px;
        }

        .print-folio {
            display: flex;
            flex-direction: column;
            align-items: center;

            margin-top: 2mm;
        }

        .print-folio span {
            color: #000;

            font-size: 8px;
            font-weight: 700;
        }

        .print-folio strong {
            color: #000;

            font-size: 14px;
            font-weight: 800;
        }

        .print-line {
            display: none;
        }

        /* =================================================
           INFORMACIÓN
           ================================================= */

        .print-info {
            width: 100%;

            margin: 0;
            padding: 2mm 0;

            border-top: 1px dashed #000;
            border-bottom: 1px dashed #000;
        }

        .print-info > div {
            display: flex;
            align-items: baseline;
            justify-content: space-between;

            gap: 3mm;

            margin: 0;
            padding: 0.8mm 0;
        }

        .print-info span {
            color: #000;

            font-size: 8px;
            font-weight: 700;
        }

        .print-info strong {
            max-width: 60%;

            color: #000;

            font-size: 8px;
            font-weight: 400;

            text-align: right;

            overflow-wrap: anywhere;
        }

        /* =================================================
           SECCIONES
           ================================================= */

        .print-section {
            width: 100%;

            margin: 3mm 0 0;
            padding: 0;
        }

        .print-section h2 {
            margin: 0 0 1.5mm;
            padding: 0 0 1.5mm;

            border-bottom: 1px dashed #000;

            color: #000;

            font-size: 9px;
            font-weight: 800;

            text-transform: uppercase;
        }

        /* =================================================
           TABLAS
           ================================================= */

        .print-table {
            width: 100%;
            max-width: 100%;

            margin: 0;
            padding: 0;

            border-collapse: collapse;

            table-layout: fixed;
        }

        .print-table th {
            padding: 1mm 0;

            border-bottom: 1px solid #000;

            color: #000;

            font-size: 8px;
            font-weight: 800;

            text-align: left;
        }

        .print-table td {
            padding: 1.5mm 0;

            border-bottom: 1px dotted #999;

            color: #000;

            font-size: 8px;

            vertical-align: top;

            word-break: break-word;
        }

        .print-table th:nth-child(1),
        .print-table td:nth-child(1) {
            width: 43%;
            padding-right: 2mm;
        }

        .print-table th:nth-child(2),
        .print-table td:nth-child(2) {
            width: 12%;
        }

        .print-table th:nth-child(3),
        .print-table td:nth-child(3) {
            width: 22%;
        }

        .print-table th:nth-child(4),
        .print-table td:nth-child(4) {
            width: 23%;
        }

        .print-table td strong {
            display: block;

            color: #000;

            font-size: 8px;
            font-weight: 700;
        }

        .print-table td small {
            display: block;

            margin-top: 0.5mm;

            color: #000;

            font-size: 7px;
        }

        .print-center {
            text-align: center !important;
        }

        .print-right {
            text-align: right !important;
        }

        /* =================================================
           TOTALES
           ================================================= */

        .print-totals {
            width: 100%;

            margin: 3mm 0 0;
            padding: 0;
        }

        .print-totals > div {
            display: flex;
            align-items: center;
            justify-content: space-between;

            gap: 3mm;

            padding: 0.8mm 0;

            color: #000;

            font-size: 9px;
        }

        .print-totals span,
        .print-totals strong {
            color: #000;
        }

        .print-total {
            margin-top: 1mm;

            padding-top: 1.5mm !important;

            border-top: 1px solid #000;

            font-size: 12px !important;
            font-weight: 800;
        }

        .print-total strong {
            font-size: 13px;
            font-weight: 800;
        }

        /* =================================================
           PAGOS
           ================================================= */

        .print-paid {
            display: flex;
            align-items: center;
            justify-content: space-between;

            gap: 3mm;

            margin: 1.5mm 0 0;
            padding: 1.5mm 0 0;

            border-top: 1px dashed #000;
        }

        .print-paid span,
        .print-paid strong {
            color: #000;

            font-size: 9px;
            font-weight: 700;
        }

        /* =================================================
           NOTAS
           ================================================= */

        .print-notes {
            width: 100%;

            margin: 3mm 0 0;
            padding: 2mm 0;

            border-top: 1px dashed #000;
            border-bottom: 1px dashed #000;
        }

        .print-notes strong {
            display: block;

            margin-bottom: 1mm;

            color: #000;

            font-size: 8px;
            font-weight: 800;

            text-transform: uppercase;
        }

        .print-notes p {
            margin: 0;
            padding: 0;

            color: #000;

            font-size: 8px;
            line-height: 1.4;

            white-space: pre-wrap;
            overflow-wrap: anywhere;
        }

        /* =================================================
           FOOTER
           ================================================= */

        .print-footer {
            display: block;

            width: 100%;

            margin: 4mm 0 0;
            padding: 2mm 0 0;

            border-top: 1px dashed #000;

            color: #000;

            text-align: center;
        }

        .print-footer span {
            display: block;

            margin: 0.5mm 0;

            color: #000;

            font-size: 8px;
        }

        .print-table tr {
            break-inside: avoid;
            page-break-inside: avoid;
        }

        .print-section,
        .print-totals,
        .print-notes {
            break-inside: avoid;
            page-break-inside: avoid;
        }
    `;

    printWindow.document.open();

    printWindow.document.write(`
        <!DOCTYPE html>
        <html lang="es">
            <head>
                <meta charset="UTF-8">

                <meta
                    name="viewport"
                    content="width=80mm, initial-scale=1.0"
                >

                <title>
                    Venta #${props.sale.id}
                </title>

                <style>
                    ${printStyles}
                </style>
            </head>

            <body>
                ${printView.innerHTML}
            </body>
        </html>
    `);

    printWindow.document.close();

    printWindow.focus();

    setTimeout(() => {
        printWindow.print();
    }, 500);

    printWindow.onafterprint = () => {
        printWindow.close();
    };
};

const totalPayments = computed(() => {
    return salePayments.value.reduce(
        (total, payment) => {
            return total + Number(payment.amount ?? 0);
        },
        0,
    );
});

const paymentDifference = computed(() => {
    return Number(props.sale?.total ?? 0)
        - totalPayments.value;
});
</script>

<template>
    <Head :title="`Venta #${props.sale.id}`" />

    <!-- =====================================================
         VISTA NORMAL
         ===================================================== -->
    <div class="admin-page screen-view">
        <!-- HEADER -->
        <div class="page-header">
            <div class="page-header-content">
                <div class="page-eyebrow">
                    Ventas
                </div>

                <div class="page-title-row">
                    <div>
                        <h1>
                            Venta #{{ props.sale.id }}
                        </h1>

                        <p>
                            Consulta el detalle de productos, cliente y pagos
                            de esta venta.
                        </p>
                    </div>

                    <div class="page-header-actions">
                        <span
                            class="status-badge"
                            :class="getStatusClass(props.sale.status)"
                        >
                            <CheckCircle2 :size="15" />
                            {{ getStatusLabel(props.sale.status) }}
                        </span>

                        <button
                            type="button"
                            class="btn btn-secondary"
                            @click="printSale"
                        >
                            <Printer :size="17" />
                            Imprimir
                        </button>

                        <Link
                            :href="admin.sales.index()"
                            class="admin-btn admin-btn-secondary"
                        >
                            <ArrowLeft
                                :size="16"
                                :stroke-width="2"
                                class="admin-btn-icon"
                            />

                            <span>Volver</span>
                        </Link>
                    </div>
                </div>
            </div>
        </div>

        <!-- SUMMARY -->
        <div class="summary-grid">
            <div class="summary-card">
                <div class="summary-icon">
                    <Hash :size="20" />
                </div>

                <div>
                    <span>Folio</span>

                    <strong>
                        #{{ props.sale.id }}
                    </strong>
                </div>
            </div>

            <div class="summary-card">
                <div class="summary-icon">
                    <CalendarDays :size="20" />
                </div>

                <div>
                    <span>Fecha</span>

                    <strong>
                        {{ formatDate(props.sale.created_at) }}
                    </strong>
                </div>
            </div>

            <div class="summary-card">
                <div class="summary-icon">
                    <Receipt :size="20" />
                </div>

                <div>
                    <span>Canal</span>

                    <strong>
                        {{ getSaleChannelLabel(props.sale) }}
                    </strong>
                </div>
            </div>

            <div class="summary-card summary-total">
                <div class="summary-icon">
                    <Wallet :size="20" />
                </div>

                <div>
                    <span>Total</span>

                    <strong>
                        {{ formatCurrency(props.sale.total) }}
                    </strong>
                </div>
            </div>
        </div>

        <!-- CONTENT -->
        <div class="detail-grid">
            <!-- LEFT -->
            <div class="detail-main">
                <!-- PRODUCTS -->
                <section class="content-card">
                    <div class="card-header">
                        <div class="card-header-title">
                            <div class="card-icon">
                                <ShoppingCart :size="19" />
                            </div>

                            <div>
                                <h2>
                                    Productos
                                </h2>

                                <p>
                                    {{ saleItems.length }}
                                    {{
                                        saleItems.length === 1
                                            ? 'producto'
                                            : 'productos'
                                    }}
                                    en esta venta
                                </p>
                            </div>
                        </div>
                    </div>

                    <div
                        v-if="saleItems.length"
                        class="products-table-wrapper"
                    >
                        <table class="products-table">
                            <thead>
                                <tr>
                                    <th>
                                        Producto
                                    </th>

                                    <th class="text-center">
                                        Cantidad
                                    </th>

                                    <th class="text-right">
                                        Precio
                                    </th>

                                    <th class="text-right">
                                        Subtotal
                                    </th>
                                </tr>
                            </thead>

                            <tbody>
                                <tr
                                    v-for="item in saleItems"
                                    :key="item.id"
                                >
                                    <td>
                                        <div class="product-cell">
                                            <div class="product-image">
                                                <img
                                                    v-if="getProductImage(item.product)"
                                                    :src="getProductImage(item.product)!"
                                                    :alt="
                                                        item.product?.name
                                                            ?? 'Producto'
                                                    "
                                                />

                                                <Package
                                                    v-else
                                                    :size="19"
                                                />
                                            </div>

                                            <div class="product-info">
                                                <strong>
                                                    {{
                                                        item.product?.name
                                                            ?? `Producto #${item.product_id}`
                                                    }}
                                                </strong>

                                                <span
                                                    v-if="item.product?.slug"
                                                >
                                                    {{ item.product.slug }}
                                                </span>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="text-center">
                                        <span class="quantity-badge">
                                            {{ item.quantity }}
                                        </span>
                                    </td>

                                    <td class="text-right">
                                        {{ formatCurrency(item.price) }}
                                    </td>

                                    <td class="text-right price-strong">
                                        {{ formatCurrency(item.subtotal) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div
                        v-else
                        class="empty-state"
                    >
                        <Package :size="30" />

                        <strong>
                            No hay productos registrados
                        </strong>

                        <span>
                            Esta venta no tiene productos asociados.
                        </span>
                    </div>

                    <div class="totals-box">
                        <div class="total-line">
                            <span>
                                Subtotal
                            </span>

                            <strong>
                                {{ formatCurrency(props.sale.subtotal) }}
                            </strong>
                        </div>

                        <div class="total-line">
                            <span>
                                Descuento
                            </span>

                            <strong class="discount">
                                -
                                {{ formatCurrency(props.sale.discount) }}
                            </strong>
                        </div>

                        <div class="total-line total-final">
                            <span>
                                Total
                            </span>

                            <strong>
                                {{ formatCurrency(props.sale.total) }}
                            </strong>
                        </div>
                    </div>
                </section>

                <!-- PAYMENTS -->
                <section class="content-card">
                    <div class="card-header">
                        <div class="card-header-title">
                            <div class="card-icon">
                                <CreditCard :size="19" />
                            </div>

                            <div>
                                <h2>
                                    Pagos
                                </h2>

                                <p>
                                    Métodos de pago registrados para la venta.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div
                        v-if="salePayments.length"
                        class="payments-list"
                    >
                        <div
                            v-for="payment in salePayments"
                            :key="payment.id"
                            class="payment-row"
                        >
                            <div class="payment-method">
                                <div class="payment-icon">
                                    <CreditCard :size="17" />
                                </div>

                                <div>
                                    <strong>
                                        {{ getPaymentMethodName(payment) }}
                                    </strong>

                                    <span
                                        v-if="payment.reference"
                                    >
                                        Referencia:
                                        {{ payment.reference }}
                                    </span>
                                </div>
                            </div>

                            <strong class="payment-amount">
                                {{ formatCurrency(payment.amount) }}
                            </strong>
                        </div>

                        <div class="payments-total">
                            <span>
                                Total pagado
                            </span>

                            <strong>
                                {{ formatCurrency(totalPayments) }}
                            </strong>
                        </div>

                        <div
                            v-if="Math.abs(paymentDifference) > 0.01"
                            class="payment-warning"
                        >
                            <span>
                                Diferencia
                            </span>

                            <strong>
                                {{ formatCurrency(paymentDifference) }}
                            </strong>
                        </div>
                    </div>

                    <div
                        v-else
                        class="empty-state"
                    >
                        <CreditCard :size="30" />

                        <strong>
                            No hay pagos registrados
                        </strong>

                        <span>
                            Esta venta no tiene pagos asociados.
                        </span>
                    </div>
                </section>

                <!-- NOTES -->
                <section
                    v-if="props.sale.notes"
                    class="content-card"
                >
                    <div class="card-header">
                        <div class="card-header-title">
                            <div class="card-icon">
                                <FileText :size="19" />
                            </div>

                            <div>
                                <h2>
                                    Notas
                                </h2>

                                <p>
                                    Observaciones de la venta.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="notes-content">
                        {{ props.sale.notes }}
                    </div>
                </section>
            </div>

            <!-- RIGHT -->
            <aside class="detail-sidebar">
                <!-- CUSTOMER -->
                <section class="content-card">
                    <div class="card-header">
                        <div class="card-header-title">
                            <div class="card-icon">
                                <UserRound :size="19" />
                            </div>

                            <div>
                                <h2>
                                    Cliente
                                </h2>

                                <p>
                                    Información del comprador.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div
                        v-if="props.sale.customer"
                        class="customer-card"
                    >
                        <div class="customer-avatar">
                            <UserRound :size="25" />
                        </div>

                        <div class="customer-info">
                            <strong>
                                {{ props.sale.customer.name }}
                            </strong>

                            <span
                                v-if="props.sale.customer.email"
                            >
                                {{ props.sale.customer.email }}
                            </span>

                            <span
                                v-if="props.sale.customer.qr_token"
                                class="customer-qr"
                            >
                                Cliente #{{ props.sale.customer.id }}
                            </span>
                        </div>
                    </div>

                    <div
                        v-else
                        class="empty-customer"
                    >
                        <UserRound :size="25" />

                        <span>
                            Venta sin cliente asignado
                        </span>
                    </div>
                </section>

                <!-- DELIVERY ADDRESS / BRANCH -->
                <section
                    v-if="deliveryAddress"
                    class="content-card"
                >
                    <div class="card-header">
                        <div class="card-header-title">
                            <div class="card-icon">
                                <Store
                                    v-if="deliveryAddress.delivery_method === 'branch_pickup'"
                                    :size="19"
                                />
                                <MapPin v-else :size="19" />
                            </div>
                            <div>
                                <h2>Entrega</h2>
                                <p>Dirección o sucursal asociada a la venta.</p>
                            </div>
                        </div>
                    </div>

                    <div class="info-list">
                        <div class="info-row">
                            <span>Modalidad</span>
                            <strong>{{ getDeliveryMethodLabel(deliveryAddress) }}</strong>
                        </div>
                        <div
                            v-if="deliveryAddress.branch_name || deliveryAddress.branch?.name"
                            class="info-row"
                        >
                            <span>Sucursal</span>
                            <strong>{{ deliveryAddress.branch_name || deliveryAddress.branch?.name }}</strong>
                        </div>
                        <div class="delivery-address-content">
                            <MapPin :size="17" />
                            <span>{{ getDeliveryAddressText(deliveryAddress) || 'No hay dirección registrada.' }}</span>
                        </div>
                        <div
                            v-if="deliveryAddress.references"
                            class="delivery-references"
                        >
                            <strong>Referencias</strong>
                            <span>{{ deliveryAddress.references }}</span>
                        </div>
                        <div
                            v-if="deliveryAddress.branch?.phone"
                            class="info-row"
                        >
                            <span>Teléfono</span>
                            <strong>{{ deliveryAddress.branch.phone }}</strong>
                        </div>
                    </div>
                </section>

                <!-- INFORMATION -->
                <section class="content-card">
                    <div class="card-header">
                        <div class="card-header-title">
                            <div class="card-icon">
                                <FileText :size="19" />
                            </div>

                            <div>
                                <h2>
                                    Información
                                </h2>

                                <p>
                                    Datos generales de la venta.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="info-list">
                        <div class="info-row">
                            <span>
                                Folio
                            </span>

                            <strong>
                                #{{ props.sale.id }}
                            </strong>
                        </div>

                        <div class="info-row">
                            <span>
                                Cliente
                            </span>

                            <strong>
                                {{ props.sale.customer?.name ?? '—' }}
                            </strong>
                        </div>

                        <div class="info-row">
                            <span>
                                Canal
                            </span>

                            <strong>
                                {{ getSaleChannelLabel(props.sale) }}
                            </strong>
                        </div>

                        <div class="info-row">
                            <span>
                                Estado
                            </span>

                            <strong>
                                {{ getStatusLabel(props.sale.status) }}
                            </strong>
                        </div>

                        <div class="info-row">
                            <span>
                                Creada
                            </span>

                            <strong>
                                {{ formatDate(props.sale.created_at) }}
                            </strong>
                        </div>

                        <div
                            v-if="props.sale.updated_at"
                            class="info-row"
                        >
                            <span>
                                Actualizada
                            </span>

                            <strong>
                                {{ formatDate(props.sale.updated_at) }}
                            </strong>
                        </div>
                    </div>
                </section>

                <!-- TOTAL -->
                <section class="total-card">
                    <div class="total-card-icon">
                        <Wallet :size="23" />
                    </div>

                    <span>
                        Total de la venta
                    </span>

                    <strong>
                        {{ formatCurrency(props.sale.total) }}
                    </strong>

                    <small>
                        {{ saleItems.length }}
                        {{
                            saleItems.length === 1
                                ? 'producto'
                                : 'productos'
                        }}
                        ·
                        {{ salePayments.length }}
                        {{
                            salePayments.length === 1
                                ? 'pago'
                                : 'pagos'
                        }}
                    </small>
                </section>
            </aside>
        </div>
    </div>

    <!-- =====================================================
         VISTA EXCLUSIVA PARA IMPRESIÓN TÉRMICA
         ===================================================== -->
    <div class="print-view">
        <div class="print-document">
            <!-- HEADER -->
            <div class="print-header">
                <h1>
                    Sonríe Corriendo
                </h1>

                <p>
                    Comprobante de venta
                </p>

                <div class="print-folio">
                    <span>
                        VENTA
                    </span>

                    <strong>
                        #{{ props.sale.id }}
                    </strong>
                </div>
            </div>

            <!-- INFORMATION -->
            <div class="print-info">
                <div>
                    <span>
                        Fecha
                    </span>

                    <strong>
                        {{ formatDate(props.sale.created_at) }}
                    </strong>
                </div>

                <div>
                    <span>
                        Cliente
                    </span>

                    <strong>
                        {{
                            props.sale.customer?.name
                                ?? 'Público general'
                        }}
                    </strong>
                </div>

                <div>
                    <span>
                        Canal
                    </span>

                    <strong>
                        {{ getSaleChannelLabel(props.sale) }}
                    </strong>
                </div>

                <div>
                    <span>
                        Estado
                    </span>

                    <strong>
                        {{ getStatusLabel(props.sale.status) }}
                    </strong>
                </div>

                <div
                    v-if="props.sale.customer?.email"
                >
                    <span>
                        Correo
                    </span>

                    <strong>
                        {{ props.sale.customer.email }}
                    </strong>
                </div>
            </div>

            <!-- DELIVERY -->
            <div v-if="deliveryAddress" class="print-section">
                <h2>Entrega</h2>
                <div class="print-info print-delivery-info">
                    <div>
                        <span>Modalidad</span>
                        <strong>{{ getDeliveryMethodLabel(deliveryAddress) }}</strong>
                    </div>
                    <div v-if="deliveryAddress.branch_name || deliveryAddress.branch?.name">
                        <span>Sucursal</span>
                        <strong>{{ deliveryAddress.branch_name || deliveryAddress.branch?.name }}</strong>
                    </div>
                    <div>
                        <span>Dirección</span>
                        <strong>{{ getDeliveryAddressText(deliveryAddress) || 'No registrada' }}</strong>
                    </div>
                    <div v-if="deliveryAddress.references">
                        <span>Referencias</span>
                        <strong>{{ deliveryAddress.references }}</strong>
                    </div>
                </div>
            </div>

            <!-- PRODUCTS -->
            <div class="print-section">
                <h2>
                    Productos
                </h2>

                <table class="print-table">
                    <thead>
                        <tr>
                            <th>
                                Producto
                            </th>

                            <th class="print-center">
                                Cant.
                            </th>

                            <th class="print-right">
                                Precio
                            </th>

                            <th class="print-right">
                                Subtotal
                            </th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr
                            v-for="item in saleItems"
                            :key="item.id"
                        >
                            <td>
                                <strong>
                                    {{
                                        item.product?.name
                                            ?? `Producto #${item.product_id}`
                                    }}
                                </strong>

                                <small
                                    v-if="item.product?.slug"
                                >
                                    {{ item.product.slug }}
                                </small>
                            </td>

                            <td class="print-center">
                                {{ item.quantity }}
                            </td>

                            <td class="print-right">
                                {{ formatCurrency(item.price) }}
                            </td>

                            <td class="print-right">
                                {{ formatCurrency(item.subtotal) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- TOTALS -->
            <div class="print-totals">
                <div>
                    <span>
                        Subtotal
                    </span>

                    <strong>
                        {{ formatCurrency(props.sale.subtotal) }}
                    </strong>
                </div>

                <div>
                    <span>
                        Descuento
                    </span>

                    <strong>
                        -
                        {{ formatCurrency(props.sale.discount) }}
                    </strong>
                </div>

                <div class="print-total">
                    <span>
                        TOTAL
                    </span>

                    <strong>
                        {{ formatCurrency(props.sale.total) }}
                    </strong>
                </div>
            </div>

            <!-- PAYMENTS -->
            <div
                v-if="salePayments.length"
                class="print-section"
            >
                <h2>
                    Pago
                </h2>

                <table class="print-table">
                    <thead>
                        <tr>
                            <th>
                                Método
                            </th>

                            <th>
                                Referencia
                            </th>

                            <th
                                colspan="2"
                                class="print-right"
                            >
                                Importe
                            </th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr
                            v-for="payment in salePayments"
                            :key="payment.id"
                        >
                            <td>
                                {{ getPaymentMethodName(payment) }}
                            </td>

                            <td>
                                {{ payment.reference ?? '—' }}
                            </td>

                            <td
                                colspan="2"
                                class="print-right"
                            >
                                {{ formatCurrency(payment.amount) }}
                            </td>
                        </tr>
                    </tbody>
                </table>

                <div class="print-paid">
                    <span>
                        TOTAL PAGADO
                    </span>

                    <strong>
                        {{ formatCurrency(totalPayments) }}
                    </strong>
                </div>
            </div>

            <!-- NOTES -->
            <div
                v-if="props.sale.notes"
                class="print-notes"
            >
                <strong>
                    Notas
                </strong>

                <p>
                    {{ props.sale.notes }}
                </p>
            </div>

            <!-- FOOTER -->
            <div class="print-footer">
                <span>
                    Gracias por tu compra.
                </span>

                <span>
                    Sonríe Corriendo
                </span>

                <span>
                    Venta #{{ props.sale.id }}
                </span>
            </div>
        </div>
    </div>
</template>

<style scoped>
.admin-page {
    width: 100%;
}

/* =========================================================
   HEADER
   ========================================================= */

.page-header {
    margin-bottom: 24px;
}

.page-header-content {
    width: 100%;
}

.page-eyebrow {
    color: var(--sc-primary, #249edb);
    font-size: 12px;
    font-weight: 800;
    letter-spacing: 0.08em;
    margin-bottom: 6px;
    text-transform: uppercase;
}

.page-title-row {
    align-items: flex-start;
    display: flex;
    gap: 24px;
    justify-content: space-between;
}

.page-title-row h1 {
    color: var(--sc-text, #172b4d);
    font-size: 30px;
    font-weight: 800;
    letter-spacing: -0.03em;
    margin: 0;
}

.page-title-row p {
    color: #718096;
    font-size: 14px;
    margin: 7px 0 0;
}

.page-header-actions {
    align-items: center;
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}

/* =========================================================
   BUTTONS
   ========================================================= */

.btn {
    align-items: center;
    border: 0;
    border-radius: 10px;
    cursor: pointer;
    display: inline-flex;
    font-size: 13px;
    font-weight: 700;
    gap: 8px;
    justify-content: center;
    min-height: 42px;
    padding: 0 16px;
    text-decoration: none;
    transition:
        background-color 0.18s ease,
        border-color 0.18s ease,
        box-shadow 0.18s ease,
        transform 0.18s ease;
}

.btn:hover {
    transform: translateY(-1px);
}

.btn-primary {
    background: var(--sc-primary, #249edb);
    color: #fff;
}

.btn-primary:hover {
    background: var(--sc-primary-dark, #1769a8);
}

.btn-secondary {
    background: #fff;
    border: 1px solid var(--sc-page-border, #e2e8f0);
    color: var(--sc-text, #172b4d);
}

.btn-secondary:hover {
    background: #f8fafc;
    border-color: #cbd5e1;
}

/* =========================================================
   STATUS
   ========================================================= */

.status-badge {
    align-items: center;
    border-radius: 999px;
    display: inline-flex;
    font-size: 12px;
    font-weight: 800;
    gap: 6px;
    min-height: 34px;
    padding: 0 11px;
}

.status-success {
    background: #eafaf5;
    color: #11936e;
}

.status-warning {
    background: #fff7e8;
    color: #b7791f;
}

.status-danger {
    background: #fff0f1;
    color: #d63b49;
}

.status-neutral {
    background: #eef2f7;
    color: #64748b;
}

/* =========================================================
   SUMMARY
   ========================================================= */

.summary-grid {
    display: grid;
    gap: 14px;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    margin-bottom: 18px;
}

.summary-card {
    align-items: center;
    background: #fff;
    border: 1px solid var(--sc-page-border, #e2e8f0);
    border-radius: 14px;
    display: flex;
    gap: 12px;
    min-height: 86px;
    padding: 16px;
}

.summary-icon {
    align-items: center;
    background: var(--sc-page-light, #eaf6fc);
    border-radius: 11px;
    color: var(--sc-primary, #249edb);
    display: flex;
    flex: 0 0 42px;
    height: 42px;
    justify-content: center;
}

.summary-card span {
    color: #718096;
    display: block;
    font-size: 11px;
    font-weight: 700;
    margin-bottom: 3px;
    text-transform: uppercase;
}

.summary-card strong {
    color: var(--sc-text, #172b4d);
    display: block;
    font-size: 15px;
    font-weight: 800;
}

.summary-total .summary-icon {
    background: #f1effc;
    color: #6753b7;
}

.summary-total strong {
    color: #6753b7;
    font-size: 18px;
}

/* =========================================================
   DETAIL GRID
   ========================================================= */

.detail-grid {
    align-items: start;
    display: grid;
    gap: 18px;
    grid-template-columns: minmax(0, 1fr) 340px;
}

.detail-main,
.detail-sidebar {
    display: flex;
    flex-direction: column;
    gap: 18px;
}

.content-card {
    background: #fff;
    border: 1px solid var(--sc-page-border, #e2e8f0);
    border-radius: 16px;
    overflow: hidden;
}

.card-header {
    border-bottom: 1px solid #edf1f5;
    padding: 18px 20px;
}

.card-header-title {
    align-items: center;
    display: flex;
    gap: 11px;
}

.card-icon {
    align-items: center;
    background: var(--sc-page-light, #eaf6fc);
    border-radius: 10px;
    color: var(--sc-primary, #249edb);
    display: flex;
    flex: 0 0 38px;
    height: 38px;
    justify-content: center;
}

.card-header h2 {
    color: var(--sc-text, #172b4d);
    font-size: 16px;
    font-weight: 800;
    margin: 0;
}

.card-header p {
    color: #8a94a6;
    font-size: 12px;
    margin: 3px 0 0;
}

/* =========================================================
   PRODUCTS
   ========================================================= */

.products-table-wrapper {
    overflow-x: auto;
}

.products-table {
    border-collapse: collapse;
    min-width: 650px;
    width: 100%;
}

.products-table th {
    background: #fbfcfd;
    border-bottom: 1px solid #edf1f5;
    color: #7b8798;
    font-size: 11px;
    font-weight: 800;
    padding: 12px 18px;
    text-transform: uppercase;
}

.products-table td {
    border-bottom: 1px solid #edf1f5;
    color: #526174;
    font-size: 13px;
    padding: 13px 18px;
}

.products-table tbody tr:last-child td {
    border-bottom: 0;
}

.text-center {
    text-align: center;
}

.text-right {
    text-align: right;
}

.product-cell {
    align-items: center;
    display: flex;
    gap: 11px;
    min-width: 240px;
}

.product-image {
    align-items: center;
    background: #f1f5f9;
    border-radius: 9px;
    color: #94a3b8;
    display: flex;
    flex: 0 0 42px;
    height: 42px;
    justify-content: center;
    overflow: hidden;
}

.product-image img {
    height: 100%;
    object-fit: cover;
    width: 100%;
}

.product-info {
    display: flex;
    flex-direction: column;
    min-width: 0;
}

.product-info strong {
    color: var(--sc-text, #172b4d);
    font-size: 13px;
    font-weight: 700;
}

.product-info span {
    color: #8a94a6;
    font-size: 11px;
    margin-top: 2px;
}

.quantity-badge {
    align-items: center;
    background: #f1f5f9;
    border-radius: 8px;
    color: #475569;
    display: inline-flex;
    font-size: 12px;
    font-weight: 800;
    justify-content: center;
    min-width: 34px;
    padding: 6px 9px;
}

.price-strong {
    color: var(--sc-text, #172b4d) !important;
    font-weight: 800;
}

/* =========================================================
   TOTALS
   ========================================================= */

.totals-box {
    background: #fbfcfd;
    border-top: 1px solid #edf1f5;
    padding: 16px 20px;
}

.total-line {
    align-items: center;
    color: #718096;
    display: flex;
    font-size: 13px;
    justify-content: space-between;
    padding: 5px 0;
}

.total-line strong {
    color: var(--sc-text, #172b4d);
    font-weight: 700;
}

.total-line .discount {
    color: #d94c9a;
}

.total-final {
    border-top: 1px solid #e5eaf0;
    color: var(--sc-text, #172b4d);
    font-size: 15px;
    font-weight: 800;
    margin-top: 7px;
    padding-top: 12px;
}

.total-final strong {
    color: var(--sc-primary, #249edb);
    font-size: 18px;
}

/* =========================================================
   EMPTY
   ========================================================= */

.empty-state {
    align-items: center;
    color: #94a3b8;
    display: flex;
    flex-direction: column;
    gap: 7px;
    justify-content: center;
    padding: 38px 20px;
    text-align: center;
}

.empty-state strong {
    color: var(--sc-text, #172b4d);
    font-size: 14px;
}

.empty-state span {
    font-size: 12px;
}

/* =========================================================
   PAYMENTS
   ========================================================= */

.payments-list {
    padding: 4px 20px 18px;
}

.payment-row {
    align-items: center;
    border-bottom: 1px solid #edf1f5;
    display: flex;
    justify-content: space-between;
    padding: 14px 0;
}

.payment-method {
    align-items: center;
    display: flex;
    gap: 10px;
}

.payment-icon {
    align-items: center;
    background: #f1effc;
    border-radius: 9px;
    color: #6753b7;
    display: flex;
    flex: 0 0 36px;
    height: 36px;
    justify-content: center;
}

.payment-method div:last-child {
    display: flex;
    flex-direction: column;
}

.payment-method strong {
    color: var(--sc-text, #172b4d);
    font-size: 13px;
    font-weight: 700;
}

.payment-method span {
    color: #8a94a6;
    font-size: 11px;
    margin-top: 2px;
}

.payment-amount {
    color: var(--sc-text, #172b4d);
    font-size: 14px;
    font-weight: 800;
}

.payments-total,
.payment-warning {
    align-items: center;
    display: flex;
    font-size: 13px;
    justify-content: space-between;
    padding-top: 14px;
}

.payments-total span,
.payment-warning span {
    color: #718096;
}

.payments-total strong {
    color: var(--sc-primary, #249edb);
    font-size: 16px;
}

.payment-warning {
    color: #d63b49;
    font-size: 12px;
    padding-top: 7px;
}

.payment-warning span,
.payment-warning strong {
    color: #d63b49;
}

/* =========================================================
   NOTES
   ========================================================= */

.notes-content {
    color: #526174;
    font-size: 13px;
    line-height: 1.7;
    padding: 18px 20px;
    white-space: pre-line;
}

/* =========================================================
   CUSTOMER
   ========================================================= */

.customer-card {
    align-items: center;
    display: flex;
    gap: 12px;
    padding: 18px 20px;
}

.customer-avatar {
    align-items: center;
    background: var(--sc-page-light, #eaf6fc);
    border-radius: 50%;
    color: var(--sc-primary, #249edb);
    display: flex;
    flex: 0 0 48px;
    height: 48px;
    justify-content: center;
}

.customer-info {
    display: flex;
    flex-direction: column;
    min-width: 0;
}

.customer-info strong {
    color: var(--sc-text, #172b4d);
    font-size: 14px;
    font-weight: 800;
}

.customer-info span {
    color: #718096;
    font-size: 12px;
    margin-top: 3px;
    overflow: hidden;
    text-overflow: ellipsis;
}

.customer-info .customer-qr {
    color: var(--sc-primary, #249edb);
    font-size: 11px;
    font-weight: 700;
}

.empty-customer {
    align-items: center;
    color: #94a3b8;
    display: flex;
    gap: 9px;
    padding: 24px 20px;
}

.empty-customer span {
    font-size: 12px;
}

/* =========================================================
   INFORMATION
   ========================================================= */

.info-list {
    padding: 8px 20px 16px;
}

.info-row {
    align-items: center;
    border-bottom: 1px solid #edf1f5;
    display: flex;
    gap: 15px;
    justify-content: space-between;
    padding: 11px 0;
}

.info-row:last-child {
    border-bottom: 0;
}

.info-row span {
    color: #8a94a6;
    font-size: 12px;
}

.info-row strong {
    color: var(--sc-text, #172b4d);
    font-size: 12px;
    font-weight: 700;
    text-align: right;
}

/* =========================================================
   TOTAL CARD
   ========================================================= */

.total-card {
    align-items: center;
    background: linear-gradient(
        145deg,
        #249edb 0%,
        #1769a8 100%
    );
    border-radius: 16px;
    color: #fff;
    display: flex;
    flex-direction: column;
    padding: 25px 20px;
    text-align: center;
}

.total-card-icon {
    align-items: center;
    background: rgba(255, 255, 255, 0.15);
    border-radius: 12px;
    display: flex;
    height: 45px;
    justify-content: center;
    margin-bottom: 11px;
    width: 45px;
}

.total-card > span {
    font-size: 11px;
    font-weight: 700;
    opacity: 0.85;
    text-transform: uppercase;
}

.total-card > strong {
    font-size: 28px;
    font-weight: 800;
    letter-spacing: -0.03em;
    margin-top: 5px;
}

.total-card > small {
    font-size: 11px;
    margin-top: 5px;
    opacity: 0.8;
}

/* =========================================================
   BOTTOM ACTIONS
   ========================================================= */

.bottom-actions {
    align-items: center;
    display: flex;
    justify-content: space-between;
    margin-top: 20px;
}

/* =========================================================
   DELIVERY ADDRESS
   ========================================================= */

.delivery-address-content {
    align-items: flex-start;
    color: #526174;
    display: flex;
    font-size: 12px;
    gap: 8px;
    line-height: 1.65;
    padding: 12px 0;
}

.delivery-address-content :deep(svg) {
    color: var(--sc-primary, #249edb);
    flex: 0 0 auto;
    margin-top: 2px;
}

.delivery-references {
    border-top: 1px solid #edf1f5;
    display: flex;
    flex-direction: column;
    gap: 4px;
    padding: 12px 0;
}

.delivery-references strong {
    color: var(--sc-text, #172b4d);
    font-size: 12px;
}

.delivery-references span {
    color: #718096;
    font-size: 12px;
    line-height: 1.6;
    white-space: pre-line;
}

.print-delivery-info {
    border-top: 0;
    border-bottom: 0;
    padding: 0;
}

.print-delivery-info > div {
    align-items: flex-start;
}

/* =========================================================
   RESPONSIVE
   ========================================================= */

@media (max-width: 1100px) {
    .summary-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .detail-grid {
        grid-template-columns: minmax(0, 1fr);
    }

    .detail-sidebar {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .total-card {
        grid-column: 1 / -1;
    }
}

@media (max-width: 760px) {
    .page-title-row {
        flex-direction: column;
    }

    .page-header-actions {
        width: 100%;
    }

    .page-header-actions .btn {
        flex: 1;
    }

    .summary-grid {
        grid-template-columns: 1fr;
    }

    .detail-sidebar {
        display: flex;
    }

    .bottom-actions {
        align-items: stretch;
        flex-direction: column;
        gap: 10px;
    }

    .bottom-actions .btn {
        width: 100%;
    }
}

@media (max-width: 520px) {
    .page-title-row h1 {
        font-size: 25px;
    }

    .page-header-actions {
        align-items: stretch;
        flex-direction: column;
    }

    .page-header-actions .btn {
        width: 100%;
    }

    .summary-card {
        min-height: 76px;
    }
}

/* =========================================================
   PRINT VIEW
   ========================================================= */

.print-view {
    display: none;
}
</style>
