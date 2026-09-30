<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Html5Qrcode } from 'html5-qrcode';
import {
    ArrowLeft,
    Camera,
    CheckCircle2,
    CreditCard,
    Minus,
    Package,
    Plus,
    QrCode,
    Receipt,
    Search,
    ShoppingCart,
    Trash2,
    UserRound,
    Wallet,
    X,
} from 'lucide-vue-next';
import {
    computed,
    nextTick,
    onBeforeUnmount,
    ref,
} from 'vue';

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

interface PaymentMethod {
    id: number;
    name: string;
    code: string;
}

interface SaleItem {
    product_id: number;
    quantity: number;
}

interface SalePayment {
    payment_method_id: number | null;
    amount: number;
    reference: string;
}

interface Customer {
    id: number;
    name: string;
    email: string;
    qr_token: string;
}

const props = defineProps<{
    products: Product[];
    paymentMethods: PaymentMethod[];
}>();

const form = useForm({
    customer_id: null as number | null,
    items: [] as SaleItem[],
    discount: 0,
    channel: 'counter',
    status: 'paid',
    notes: '',
    payments: [] as SalePayment[],
});

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
                title: 'Nueva venta',
                href: admin.sales.create(),
            },
        ],
    },
});

/*
|--------------------------------------------------------------------------
| Cliente / QR
|--------------------------------------------------------------------------
*/

const identifiedCustomer = ref<Customer | null>(null);
const qrScanner = ref<Html5Qrcode | null>(null);
const scanning = ref(false);
const qrError = ref<string | null>(null);
const searchingCustomer = ref(false);
const manualQrToken = ref('');

/*
|--------------------------------------------------------------------------
| Productos
|--------------------------------------------------------------------------
*/

const productSearch = ref('');

const formatCurrency = (value: number | string): string => {
    return new Intl.NumberFormat('es-MX', {
        style: 'currency',
        currency: 'MXN',
    }).format(Number(value) || 0);
};

const getProduct = (productId: number): Product | undefined => {
    return props.products.find((product) => product.id === productId);
};

const getItem = (productId: number): SaleItem | undefined => {
    return form.items.find((item) => item.product_id === productId);
};

const getQuantity = (productId: number): number => {
    return getItem(productId)?.quantity ?? 0;
};

const filteredProducts = computed(() => {
    const search = productSearch.value.trim().toLowerCase();

    if (!search) {
        return props.products;
    }

    return props.products.filter((product) => {
        return [
            product.name,
            product.type,
            product.year?.toString(),
            product.slug,
        ]
            .filter(Boolean)
            .some((value) =>
                String(value)
                    .toLowerCase()
                    .includes(search),
            );
    });
});

const addProduct = (product: Product): void => {
    if (!product.is_active || product.stock <= 0) {
        return;
    }

    const item = getItem(product.id);

    if (!item) {
        form.items.push({
            product_id: product.id,
            quantity: 1,
        });

        return;
    }

    if (item.quantity < product.stock) {
        item.quantity++;
    }
};

const removeProduct = (product: Product): void => {
    const item = getItem(product.id);

    if (!item) {
        return;
    }

    if (item.quantity <= 1) {
        removeItem(product.id);
        return;
    }

    item.quantity--;
};

const removeItem = (productId: number): void => {
    const index = form.items.findIndex(
        (item) => item.product_id === productId,
    );

    if (index !== -1) {
        form.items.splice(index, 1);
    }
};

const getItemSubtotal = (item: SaleItem): number => {
    const product = getProduct(item.product_id);

    if (!product) {
        return 0;
    }

    return Number(product.price) * item.quantity;
};

const subtotal = computed(() => {
    return form.items.reduce(
        (total, item) => total + getItemSubtotal(item),
        0,
    );
});

const discountAmount = computed(() => {
    const discount = Number(form.discount) || 0;

    return Math.min(Math.max(discount, 0), subtotal.value);
});

const total = computed(() => {
    return Math.max(subtotal.value - discountAmount.value, 0);
});

const hasStockError = computed(() => {
    return form.items.some((item) => {
        const product = getProduct(item.product_id);

        return product && item.quantity > product.stock;
    });
});

/*
|--------------------------------------------------------------------------
| Pagos
|--------------------------------------------------------------------------
*/

const getPaymentMethod = (
    paymentMethodId: number | null,
): PaymentMethod | undefined => {
    return props.paymentMethods.find(
        (method) => method.id === paymentMethodId,
    );
};

const isCashPayment = (
    paymentMethodId: number | null,
): boolean => {
    const method = getPaymentMethod(paymentMethodId);

    if (!method) {
        return false;
    }

    return method.code.toLowerCase() === 'cash'
        || method.code.toLowerCase() === 'efectivo';
};

const requiresReference = (
    paymentMethodId: number | null,
): boolean => {
    return !isCashPayment(paymentMethodId);
};

const addPayment = (): void => {
    form.payments.push({
        payment_method_id:
            props.paymentMethods[0]?.id ?? null,
        amount: 0,
        reference: '',
    });
};

const removePayment = (index: number): void => {
    form.payments.splice(index, 1);
};

const nonCashPaymentsTotal = computed(() => {
    return form.payments.reduce((total, payment) => {
        if (isCashPayment(payment.payment_method_id)) {
            return total;
        }

        return total + (Number(payment.amount) || 0);
    }, 0);
});

const cashPaymentsTotal = computed(() => {
    return form.payments.reduce((total, payment) => {
        if (!isCashPayment(payment.payment_method_id)) {
            return total;
        }

        return total + (Number(payment.amount) || 0);
    }, 0);
});

const remainingBeforeCash = computed(() => {
    return Math.max(
        total.value - nonCashPaymentsTotal.value,
        0,
    );
});

const remaining = computed(() => {
    return Math.max(
        total.value
            - nonCashPaymentsTotal.value
            - cashPaymentsTotal.value,
        0,
    );
});

const change = computed(() => {
    return Math.max(
        cashPaymentsTotal.value - remainingBeforeCash.value,
        0,
    );
});

const isPaymentComplete = computed(() => {
    if (total.value <= 0) {
        return true;
    }

    return (
        nonCashPaymentsTotal.value
        + cashPaymentsTotal.value
    ) >= total.value;
});

const hasPaymentError = computed(() => {
    return form.payments.some((payment) => {
        if (!payment.payment_method_id) {
            return true;
        }

        if ((Number(payment.amount) || 0) <= 0) {
            return true;
        }

        if (
            requiresReference(payment.payment_method_id)
            && !payment.reference.trim()
        ) {
            return true;
        }

        return false;
    });
});

/*
|--------------------------------------------------------------------------
| Cliente por QR
|--------------------------------------------------------------------------
*/

const findCustomerByQr = async (
    token: string,
): Promise<void> => {
    const cleanToken = token.trim();

    if (!cleanToken) {
        qrError.value = 'Ingresa o escanea un código QR válido.';
        return;
    }

    searchingCustomer.value = true;
    qrError.value = null;

    try {
        const csrfToken = document
            .querySelector('meta[name="csrf-token"]')
            ?.getAttribute('content');

        const response = await fetch(
            '/admin/sales/customer-by-qr',
            {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    ...(csrfToken
                        ? {
                            'X-CSRF-TOKEN': csrfToken,
                        }
                        : {}),
                },
                body: JSON.stringify({
                    qr_token: cleanToken,
                }),
            },
        );

        const data = await response.json();

        if (!response.ok) {
            throw new Error(
                data.message
                ?? 'No fue posible encontrar al cliente.',
            );
        }

        if (!data.customer) {
            throw new Error(
                'No se encontró ningún cliente con ese código QR.',
            );
        }

        identifiedCustomer.value = data.customer;
        form.customer_id = data.customer.id;
        manualQrToken.value = '';

        await stopQrScanner();
    } catch (error) {
        identifiedCustomer.value = null;
        form.customer_id = null;

        qrError.value =
            error instanceof Error
                ? error.message
                : 'No fue posible consultar el código QR.';
    } finally {
        searchingCustomer.value = false;
    }
};

const startQrScanner = async (): Promise<void> => {
    qrError.value = null;
    scanning.value = true;

    await nextTick();

    const reader = document.getElementById(
        'customer-qr-reader',
    );

    if (!reader) {
        scanning.value = false;
        qrError.value =
            'No fue posible iniciar el lector QR.';
        return;
    }

    try {
        if (qrScanner.value) {
            await stopQrScanner();
        }

        reader.innerHTML = '';

        const scanner = new Html5Qrcode(
            'customer-qr-reader',
        );

        qrScanner.value = scanner;

        await scanner.start(
            {
                facingMode: 'environment',
            },
            {
                fps: 10,
                qrbox: {
                    width: 230,
                    height: 230,
                },
                aspectRatio: 1,
            },
            async (decodedText) => {
                await findCustomerByQr(decodedText);
            },
            () => {
                // Los errores de lectura individuales se ignoran.
            },
        );
    } catch (error) {
        scanning.value = false;
        qrScanner.value = null;

        qrError.value =
            error instanceof Error
                ? error.message
                : 'No fue posible acceder a la cámara.';
    }
};

const stopQrScanner = async (): Promise<void> => {
    const scanner = qrScanner.value;

    if (!scanner) {
        scanning.value = false;
        return;
    }

    try {
        if (scanner.isScanning) {
            await scanner.stop();
        }

        await scanner.clear();
    } catch {
        // El lector puede ya estar detenido.
    } finally {
        qrScanner.value = null;
        scanning.value = false;
    }
};

const clearIdentifiedCustomer = async (): Promise<void> => {
    await stopQrScanner();

    identifiedCustomer.value = null;
    form.customer_id = null;
    manualQrToken.value = '';
    qrError.value = null;
};

const searchManualQr = async (): Promise<void> => {
    await findCustomerByQr(manualQrToken.value);
};

onBeforeUnmount(() => {
    void stopQrScanner();
});

/*
|--------------------------------------------------------------------------
| Envío
|--------------------------------------------------------------------------
*/

const submit = (): void => {
    if (!form.items.length) {
        return;
    }

    if (hasStockError.value) {
        return;
    }

    if (hasPaymentError.value) {
        return;
    }

    if (!isPaymentComplete.value) {
        return;
    }

    form.transform((data) => ({
        ...data,
        subtotal: subtotal.value,
        total: total.value,
        discount: discountAmount.value,
        sales_channel: 'counter',
        status: 'paid',
    })).post(admin.sales.store().url);
};
</script>

<template>
    <Head title="Nueva venta" />

    <div class="admin-page">
        <!-- =====================================================
             HEADER
             ===================================================== -->

        <div class="page-header">
            <div class="page-header-content">
                <div>
                    <div class="page-header-eyebrow">
                        <Receipt :size="16" />
                        Ventas
                    </div>

                    <h1>Nueva venta</h1>

                    <p>
                        Registra una nueva venta desde el punto de venta.
                    </p>
                </div>

                <Link
                    :href="admin.sales.index()"
                    class="btn btn-secondary"
                >
                    <ArrowLeft :size="17" />
                    Regresar
                </Link>
            </div>
        </div>

        <form
            class="sale-form"
            @submit.prevent="submit"
        >
            <!-- =====================================================
                 INFORMACIÓN
                 ===================================================== -->

            <div class="info-card">
                <div class="info-icon">
                    <ShoppingCart :size="20" />
                </div>

                <div>
                    <strong>Venta en mostrador</strong>

                    <span>
                        Selecciona los productos, identifica al cliente
                        mediante su código QR y registra el pago.
                    </span>
                </div>

                <div class="channel-badge">
                    Mostrador
                </div>
            </div>

            <!-- =====================================================
                 CLIENTE
                 ===================================================== -->

            <section class="form-card">
                <div class="card-header">
                    <div class="card-header-icon customer">
                        <UserRound :size="20" />
                    </div>

                    <div>
                        <h2>Cliente</h2>
                        <p>
                            Escanea el código QR del cliente para
                            asociarlo a la venta.
                        </p>
                    </div>
                </div>

                <div class="customer-content">
                    <div class="qr-column">
                        <div
                            id="customer-qr-reader"
                            class="qr-reader"
                            :class="{
                                'is-scanning': scanning,
                            }"
                        >
                            <div
                                v-if="!scanning"
                                class="qr-placeholder"
                            >
                                <QrCode :size="46" />

                                <strong>
                                    Lector QR
                                </strong>

                                <span>
                                    Activa la cámara para escanear
                                    el código del cliente.
                                </span>

                                <button
                                    type="button"
                                    class="btn btn-primary"
                                    :disabled="searchingCustomer"
                                    @click="startQrScanner"
                                >
                                    <Camera :size="17" />
                                    Abrir cámara
                                </button>
                            </div>
                        </div>

                        <button
                            v-if="scanning"
                            type="button"
                            class="btn btn-secondary stop-camera"
                            @click="stopQrScanner"
                        >
                            Detener cámara
                        </button>
                    </div>

                    <div class="customer-divider">
                        <span>o</span>
                    </div>

                    <div class="manual-column">
                        <label class="form-label">
                            Código QR manual
                        </label>

                        <div class="manual-search">
                            <input
                                v-model="manualQrToken"
                                type="text"
                                class="form-input"
                                placeholder="Ingresa el código QR"
                                :disabled="searchingCustomer"
                                @keyup.enter="searchManualQr"
                            />

                            <button
                                type="button"
                                class="btn btn-primary"
                                :disabled="
                                    searchingCustomer
                                    || !manualQrToken.trim()
                                "
                                @click="searchManualQr"
                            >
                                <Search :size="17" />

                                {{
                                    searchingCustomer
                                        ? 'Buscando...'
                                        : 'Buscar'
                                }}
                            </button>
                        </div>

                        <p class="field-help">
                            También puedes introducir directamente
                            el token asociado al cliente.
                        </p>

                        <div
                            v-if="identifiedCustomer"
                            class="identified-customer"
                        >
                            <div class="identified-icon">
                                <CheckCircle2 :size="21" />
                            </div>

                            <div class="identified-info">
                                <strong>
                                    {{ identifiedCustomer.name }}
                                </strong>

                                <span>
                                    {{ identifiedCustomer.email }}
                                </span>
                            </div>

                            <button
                                type="button"
                                class="clear-customer"
                                title="Quitar cliente"
                                @click="clearIdentifiedCustomer"
                            >
                                <Trash2 :size="17" />
                            </button>
                        </div>

                        <div
                            v-if="qrError"
                            class="qr-error"
                        >
                            {{ qrError }}
                        </div>
                    </div>
                </div>
            </section>

            <!-- =====================================================
                 PRODUCTOS
                 ===================================================== -->

            <section class="form-card">
                <div class="card-header">
                    <div class="card-header-icon products">
                        <Package :size="20" />
                    </div>

                    <div>
                        <h2>Productos</h2>
                        <p>
                            Selecciona los productos que formarán
                            parte de la venta.
                        </p>
                    </div>

                    <div class="products-count">
                        {{ form.items.length }}
                        {{
                            form.items.length === 1
                                ? 'producto'
                                : 'productos'
                        }}
                    </div>
                </div>

                <div
                    v-if="props.products.length"
                    class="products-content"
                >
                    <!-- =================================================
                         BUSCADOR
                         ================================================= -->

                    <div class="products-toolbar">
                        <div class="product-search">
                            <Search :size="17" />

                            <input
                                v-model="productSearch"
                                type="text"
                                class="form-input"
                                placeholder="Buscar producto por nombre, tipo o año..."
                            />

                            <button
                                v-if="productSearch"
                                type="button"
                                class="clear-product-search"
                                title="Limpiar búsqueda"
                                @click="productSearch = ''"
                            >
                                <X :size="15" />
                            </button>
                        </div>

                        <span class="products-results">
                            Mostrando
                            {{ filteredProducts.length }}
                            de
                            {{ props.products.length }}
                            productos
                        </span>
                    </div>

                    <!-- =================================================
                         TABLA
                         ================================================= -->

                    <div
                        v-if="filteredProducts.length"
                        class="products-table-wrapper"
                    >
                        <table class="products-table">
                            <thead>
                                <tr>
                                    <th class="product-column">
                                        Producto
                                    </th>

                                    <th>
                                        Tipo
                                    </th>

                                    <th>
                                        Precio
                                    </th>

                                    <th>
                                        Stock
                                    </th>

                                    <th class="quantity-column">
                                        Cantidad
                                    </th>

                                    <th class="subtotal-column">
                                        Importe
                                    </th>
                                </tr>
                            </thead>

                            <tbody>
                                <tr
                                    v-for="product in filteredProducts"
                                    :key="product.id"
                                    :class="{
                                        'is-selected':
                                            getQuantity(product.id) > 0,
                                        'is-disabled':
                                            !product.is_active
                                            || product.stock <= 0,
                                    }"
                                >
                                    <td>
                                        <div class="product-cell">
                                            <div class="product-image">
                                                <img
                                                    v-if="product.image"
                                                    :src="`/storage/${product.image}`"
                                                    :alt="product.name"
                                                />

                                                <Package
                                                    v-else
                                                    :size="21"
                                                />
                                            </div>

                                            <div class="product-info">
                                                <strong>
                                                    {{ product.name }}
                                                </strong>

                                                <span v-if="product.year">
                                                    {{ product.year }}
                                                </span>
                                            </div>
                                        </div>
                                    </td>

                                    <td>
                                        <span
                                            v-if="product.type"
                                            class="type-badge"
                                        >
                                            {{ product.type }}
                                        </span>

                                        <span
                                            v-else
                                            class="muted-text"
                                        >
                                            —
                                        </span>
                                    </td>

                                    <td>
                                        <strong class="price-value">
                                            {{
                                                formatCurrency(
                                                    product.price,
                                                )
                                            }}
                                        </strong>
                                    </td>

                                    <td>
                                        <span
                                            class="stock-value"
                                            :class="{
                                                'stock-low':
                                                    product.stock > 0
                                                    && product.stock <= 5,
                                                'stock-empty':
                                                    product.stock <= 0,
                                            }"
                                        >
                                            {{ product.stock }}
                                        </span>

                                        <span class="stock-label">
                                            disponibles
                                        </span>
                                    </td>

                                    <td>
                                        <div class="quantity-control">
                                            <button
                                                type="button"
                                                class="quantity-button"
                                                :disabled="
                                                    !getQuantity(product.id)
                                                    || !product.is_active
                                                "
                                                @click="
                                                    removeProduct(product)
                                                "
                                            >
                                                <Minus :size="15" />
                                            </button>

                                            <span class="quantity-number">
                                                {{ getQuantity(product.id) }}
                                            </span>

                                            <button
                                                type="button"
                                                class="quantity-button add"
                                                :disabled="
                                                    !product.is_active
                                                    || product.stock <= 0
                                                    || getQuantity(
                                                        product.id,
                                                    ) >= product.stock
                                                "
                                                @click="
                                                    addProduct(product)
                                                "
                                            >
                                                <Plus :size="15" />
                                            </button>
                                        </div>
                                    </td>

                                    <td class="subtotal-cell">
                                        <strong
                                            v-if="getQuantity(product.id)"
                                        >
                                            {{
                                                formatCurrency(
                                                    Number(product.price)
                                                    * getQuantity(
                                                        product.id,
                                                    ),
                                                )
                                            }}
                                        </strong>

                                        <span
                                            v-else
                                            class="muted-text"
                                        >
                                            —
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- =================================================
                         SIN RESULTADOS DE BÚSQUEDA
                         ================================================= -->

                    <div
                        v-else
                        class="product-search-empty"
                    >
                        <Search :size="32" />

                        <strong>
                            No encontramos productos
                        </strong>

                        <span>
                            No hay productos que coincidan con
                            "{{ productSearch }}".
                        </span>

                        <button
                            type="button"
                            class="btn btn-secondary"
                            @click="productSearch = ''"
                        >
                            Limpiar búsqueda
                        </button>
                    </div>
                </div>

                <div
                    v-else
                    class="empty-state"
                >
                    <Package :size="42" />

                    <strong>
                        No hay productos disponibles
                    </strong>

                    <span>
                        No existen productos activos para registrar
                        en esta venta.
                    </span>
                </div>

                <div
                    v-if="hasStockError"
                    class="form-error stock-error"
                >
                    Uno o más productos superan el stock disponible.
                </div>
            </section>

            <!-- =====================================================
                 RESUMEN
                 ===================================================== -->

            <div class="summary-grid">
                <section class="form-card">
                    <div class="card-header">
                        <div class="card-header-icon discount">
                            <Receipt :size="20" />
                        </div>

                        <div>
                            <h2>Descuento</h2>
                            <p>
                                Aplica un descuento a la venta si es necesario.
                            </p>
                        </div>
                    </div>

                    <div class="discount-field">
                        <label class="form-label">
                            Descuento
                        </label>

                        <div class="money-input">
                            <span>$</span>

                            <input
                                v-model.number="form.discount"
                                type="number"
                                min="0"
                                :max="subtotal"
                                step="0.01"
                                class="form-input"
                                placeholder="0.00"
                            />
                        </div>

                        <span class="field-help">
                            Máximo:
                            {{ formatCurrency(subtotal) }}
                        </span>
                    </div>
                </section>

                <section class="form-card sale-summary">
                    <div class="card-header">
                        <div class="card-header-icon total">
                            <Wallet :size="20" />
                        </div>

                        <div>
                            <h2>Resumen</h2>
                            <p>
                                Total de la venta.
                            </p>
                        </div>
                    </div>

                    <div class="summary-lines">
                        <div>
                            <span>Subtotal</span>

                            <strong>
                                {{ formatCurrency(subtotal) }}
                            </strong>
                        </div>

                        <div>
                            <span>Descuento</span>

                            <strong class="discount-value">
                                -
                                {{ formatCurrency(discountAmount) }}
                            </strong>
                        </div>

                        <div class="summary-total">
                            <span>Total</span>

                            <strong>
                                {{ formatCurrency(total) }}
                            </strong>
                        </div>
                    </div>
                </section>
            </div>

            <!-- =====================================================
                 PAGOS
                 ===================================================== -->

            <section class="form-card">
                <div class="card-header">
                    <div class="card-header-icon payment">
                        <CreditCard :size="20" />
                    </div>

                    <div>
                        <h2>Pago</h2>

                        <p>
                            Registra uno o varios métodos de pago.
                        </p>
                    </div>

                    <button
                        type="button"
                        class="btn btn-secondary add-payment"
                        @click="addPayment"
                    >
                        <Plus :size="17" />
                        Agregar pago
                    </button>
                </div>

                <div
                    v-if="form.payments.length"
                    class="payments-list"
                >
                    <div
                        v-for="(payment, index) in form.payments"
                        :key="index"
                        class="payment-row"
                    >
                        <div class="payment-number">
                            {{ index + 1 }}
                        </div>

                        <div class="payment-field method-field">
                            <label class="form-label">
                                Método
                            </label>

                            <select
                                v-model="payment.payment_method_id"
                                class="form-input"
                            >
                                <option :value="null">
                                    Selecciona un método
                                </option>

                                <option
                                    v-for="method in paymentMethods"
                                    :key="method.id"
                                    :value="method.id"
                                >
                                    {{ method.name }}
                                </option>
                            </select>
                        </div>

                        <div class="payment-field amount-field">
                            <label class="form-label">
                                Importe
                            </label>

                            <div class="money-input">
                                <span>$</span>

                                <input
                                    v-model.number="payment.amount"
                                    type="number"
                                    min="0"
                                    step="0.01"
                                    class="form-input"
                                    placeholder="0.00"
                                />
                            </div>
                        </div>

                        <div
                            v-if="requiresReference(
                                payment.payment_method_id,
                            )"
                            class="payment-field reference-field"
                        >
                            <label class="form-label">
                                Referencia
                            </label>

                            <input
                                v-model="payment.reference"
                                type="text"
                                class="form-input"
                                placeholder="Referencia / folio"
                            />
                        </div>

                        <button
                            type="button"
                            class="remove-payment"
                            title="Eliminar pago"
                            @click="removePayment(index)"
                        >
                            <Trash2 :size="17" />
                        </button>
                    </div>
                </div>

                <div
                    v-else
                    class="payment-empty"
                >
                    <CreditCard :size="30" />

                    <span>
                        Agrega un método de pago para completar
                        la venta.
                    </span>

                    <button
                        type="button"
                        class="btn btn-secondary"
                        @click="addPayment"
                    >
                        <Plus :size="16" />
                        Agregar pago
                    </button>
                </div>

                <div class="payment-summary">
                    <div>
                        <span>Total de venta</span>

                        <strong>
                            {{ formatCurrency(total) }}
                        </strong>
                    </div>

                    <div>
                        <span>Pagos no efectivo</span>

                        <strong>
                            {{ formatCurrency(nonCashPaymentsTotal) }}
                        </strong>
                    </div>

                    <div>
                        <span>Efectivo recibido</span>

                        <strong>
                            {{ formatCurrency(cashPaymentsTotal) }}
                        </strong>
                    </div>

                    <div
                        v-if="change > 0"
                        class="payment-change"
                    >
                        <span>Cambio</span>

                        <strong>
                            {{ formatCurrency(change) }}
                        </strong>
                    </div>

                    <div
                        v-else
                        class="payment-remaining"
                    >
                        <span>Restante</span>

                        <strong>
                            {{ formatCurrency(remaining) }}
                        </strong>
                    </div>
                </div>

                <div
                    v-if="hasPaymentError"
                    class="form-error"
                >
                    Revisa los métodos de pago, importes y referencias.
                </div>
            </section>

            <!-- =====================================================
                 NOTAS
                 ===================================================== -->

            <section class="form-card">
                <div class="card-header">
                    <div class="card-header-icon notes">
                        <Receipt :size="20" />
                    </div>

                    <div>
                        <h2>Notas</h2>

                        <p>
                            Agrega información adicional de la venta.
                        </p>
                    </div>
                </div>

                <textarea
                    v-model="form.notes"
                    class="form-input notes-input"
                    rows="4"
                    placeholder="Notas de la venta..."
                />
            </section>

            <!-- =====================================================
                 ACTIONS
                 ===================================================== -->

            <div class="form-actions">
                <Link
                    :href="admin.sales.index()"
                    class="btn btn-secondary"
                >
                    Cancelar
                </Link>

                <button
                    type="submit"
                    class="btn btn-primary submit-button"
                    :disabled="
                        form.processing
                        || !form.items.length
                        || hasStockError
                        || hasPaymentError
                        || !isPaymentComplete
                    "
                >
                    <CheckCircle2 :size="18" />

                    {{
                        form.processing
                            ? 'Registrando...'
                            : 'Registrar venta'
                    }}
                </button>
            </div>
        </form>
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
   HEADER
   ========================================================= */

.page-header {
    width: 100%;
    padding: 28px 28px 22px;
    border-bottom: 1px solid var(--sc-page-border);
    background: #ffffff;
}

.page-header-content {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 24px;
}

.page-header-eyebrow {
    display: flex;
    align-items: center;
    gap: 7px;
    margin-bottom: 7px;
    color: var(--sc-primary, #249edb);
    font-size: 13px;
    font-weight: 700;
}

.page-header h1 {
    margin: 0;
    color: var(--sc-text, #172b4d);
    font-size: 27px;
    font-weight: 750;
    line-height: 1.2;
}

.page-header p {
    margin: 7px 0 0;
    color: #718096;
    font-size: 14px;
}

/* =========================================================
   FORM
   ========================================================= */

.sale-form {
    display: flex;
    flex-direction: column;
    gap: 20px;
    padding: 24px 28px 34px;
}

/* =========================================================
   BUTTONS
   ========================================================= */

.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    min-height: 40px;
    padding: 0 15px;
    border: 1px solid transparent;
    border-radius: 10px;
    font-size: 13px;
    font-weight: 700;
    text-decoration: none;
    cursor: pointer;
    transition:
        background .18s ease,
        border-color .18s ease,
        color .18s ease,
        transform .18s ease;
}

.btn:disabled {
    opacity: .55;
    cursor: not-allowed;
}

.btn-primary {
    background: #249edb;
    color: #ffffff;
}

.btn-primary:hover:not(:disabled) {
    background: #1769a8;
}

.btn-secondary {
    background: #ffffff;
    border-color: #dbe4ec;
    color: #344054;
}

.btn-secondary:hover:not(:disabled) {
    background: #f8fafc;
    border-color: #cbd5e1;
}

/* =========================================================
   INFO CARD
   ========================================================= */

.info-card {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 15px 17px;
    border: 1px solid #d9edf8;
    border-radius: 14px;
    background: #f3faff;
}

.info-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    flex: 0 0 40px;
    width: 40px;
    height: 40px;
    border-radius: 11px;
    background: #e2f4fd;
    color: #249edb;
}

.info-card > div:nth-child(2) {
    display: flex;
    flex-direction: column;
    gap: 3px;
    min-width: 0;
    flex: 1;
}

.info-card strong {
    color: #172b4d;
    font-size: 14px;
}

.info-card span {
    color: #718096;
    font-size: 13px;
}

.channel-badge {
    flex: 0 0 auto;
    padding: 6px 10px;
    border-radius: 8px;
    background: #eaf6fc;
    color: #1769a8 !important;
    font-size: 12px !important;
    font-weight: 700;
}

/* =========================================================
   CARDS
   ========================================================= */

.form-card {
    overflow: hidden;
    border: 1px solid var(--sc-page-border, #e5e7eb);
    border-radius: 16px;
    background: #ffffff;
    box-shadow: 0 2px 7px rgba(23, 43, 77, .035);
}

.card-header {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 18px 20px;
    border-bottom: 1px solid #edf1f5;
}

.card-header > div:nth-child(2) {
    min-width: 0;
    flex: 1;
}

.card-header h2 {
    margin: 0;
    color: #172b4d;
    font-size: 16px;
    font-weight: 750;
}

.card-header p {
    margin: 4px 0 0;
    color: #7b8798;
    font-size: 12px;
}

.card-header-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    flex: 0 0 40px;
    width: 40px;
    height: 40px;
    border-radius: 11px;
}

.card-header-icon.customer {
    background: #eaf6fc;
    color: #249edb;
}

.card-header-icon.products {
    background: #f0ecff;
    color: #6753b7;
}

.card-header-icon.discount {
    background: #fff1f8;
    color: #d94c9a;
}

.card-header-icon.total {
    background: #eafaf6;
    color: #18b89a;
}

.card-header-icon.payment {
    background: #eaf6fc;
    color: #249edb;
}

.card-header-icon.notes {
    background: #fff7e8;
    color: #d18b19;
}

/* =========================================================
   CUSTOMER
   ========================================================= */

.customer-content {
    display: grid;
    grid-template-columns: minmax(280px, 390px) 45px minmax(0, 1fr);
    gap: 18px;
    padding: 20px;
}

.qr-column {
    min-width: 0;
}

.qr-reader {
    position: relative;
    width: 100%;
    min-height: 280px;
    overflow: hidden;
    border: 1px dashed #cbd9e5;
    border-radius: 14px;
    background: #f8fafc;
}

.qr-reader.is-scanning {
    min-height: 280px;
    border-style: solid;
    border-color: #b8dff1;
    background: #111827;
}

.qr-reader video {
    width: 100% !important;
    height: 280px !important;
    object-fit: cover !important;
    border-radius: 13px;
}

.qr-reader #qr-shaded-region {
    border-color: #ffffff !important;
}

.qr-placeholder {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    min-height: 280px;
    padding: 25px;
    text-align: center;
    color: #8b98a9;
}

.qr-placeholder svg {
    color: #249edb;
}

.qr-placeholder strong {
    color: #344054;
    font-size: 14px;
}

.qr-placeholder span {
    max-width: 260px;
    margin: 7px 0 15px;
    color: #8b98a9;
    font-size: 12px;
    line-height: 1.5;
}

.stop-camera {
    width: 100%;
    margin-top: 10px;
}

.customer-divider {
    display: flex;
    align-items: center;
    justify-content: center;
    color: #98a2b3;
}

.customer-divider span {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 30px;
    height: 30px;
    border: 1px solid #e4e9ef;
    border-radius: 50%;
    background: #ffffff;
    font-size: 11px;
    font-weight: 700;
}

.manual-column {
    display: flex;
    flex-direction: column;
    justify-content: center;
    min-width: 0;
}

.form-label {
    display: block;
    margin-bottom: 7px;
    color: #344054;
    font-size: 12px;
    font-weight: 700;
}

.form-input {
    width: 100%;
    min-height: 40px;
    padding: 9px 11px;
    border: 1px solid #d9e1e8;
    border-radius: 9px;
    outline: none;
    background: #ffffff;
    color: #172b4d;
    font-family: inherit;
    font-size: 13px;
    transition:
        border-color .18s ease,
        box-shadow .18s ease;
}

.form-input::placeholder {
    color: #a0a9b5;
}

.form-input:focus {
    border-color: #a9d9f2;
    box-shadow: 0 0 0 3px rgba(36, 158, 219, .08);
}

textarea.form-input {
    resize: vertical;
}

select.form-input {
    cursor: pointer;
}

.manual-search {
    display: flex;
    gap: 8px;
}

.manual-search .form-input {
    flex: 1;
}

.field-help {
    margin: 7px 0 0;
    color: #8b98a9;
    font-size: 11px;
    line-height: 1.5;
}

.identified-customer {
    display: flex;
    align-items: center;
    gap: 11px;
    margin-top: 18px;
    padding: 12px;
    border: 1px solid #ccefe5;
    border-radius: 11px;
    background: #f1fcf9;
}

.identified-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    flex: 0 0 34px;
    width: 34px;
    height: 34px;
    border-radius: 9px;
    background: #dff7f0;
    color: #18a987;
}

.identified-info {
    display: flex;
    flex-direction: column;
    min-width: 0;
    flex: 1;
}

.identified-info strong {
    overflow: hidden;
    color: #172b4d;
    font-size: 13px;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.identified-info span {
    overflow: hidden;
    margin-top: 2px;
    color: #7b8798;
    font-size: 11px;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.clear-customer {
    display: flex;
    align-items: center;
    justify-content: center;
    flex: 0 0 32px;
    width: 32px;
    height: 32px;
    border: 0;
    border-radius: 8px;
    background: transparent;
    color: #e83e4d;
    cursor: pointer;
}

.clear-customer:hover {
    background: #fff0f2;
}

.qr-error,
.form-error {
    margin-top: 12px;
    padding: 10px 12px;
    border: 1px solid #f5c8ce;
    border-radius: 9px;
    background: #fff5f6;
    color: #c93443;
    font-size: 12px;
    line-height: 1.5;
}

/* =========================================================
   PRODUCTS
   ========================================================= */

.products-content {
    width: 100%;
}

.products-count {
    flex: 0 0 auto;
    padding: 6px 10px;
    border-radius: 8px;
    background: #f0ecff;
    color: #6753b7;
    font-size: 11px;
    font-weight: 750;
}

.products-toolbar {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 15px 20px;
    border-bottom: 1px solid #edf1f5;
    background: #ffffff;
}

.product-search {
    position: relative;
    display: flex;
    align-items: center;
    flex: 1;
    max-width: 520px;
}

.product-search > svg {
    position: absolute;
    left: 12px;
    color: #98a2b3;
    pointer-events: none;
}

.product-search .form-input {
    min-height: 38px;
    padding-left: 37px;
    padding-right: 38px;
}

.clear-product-search {
    position: absolute;
    right: 7px;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 27px;
    height: 27px;
    border: 0;
    border-radius: 7px;
    background: transparent;
    color: #98a2b3;
    cursor: pointer;
}

.clear-product-search:hover {
    background: #f1f5f9;
    color: #475467;
}

.products-results {
    flex: 0 0 auto;
    color: #8b98a9;
    font-size: 11px;
    font-weight: 650;
    white-space: nowrap;
}

.products-table-wrapper {
    width: 100%;
    overflow-x: auto;
}

.products-table {
    width: 100%;
    min-width: 850px;
    border-collapse: collapse;
}

.products-table thead th {
    padding: 11px 14px;
    border-bottom: 1px solid #e9edf2;
    background: #fbfcfd;
    color: #667085;
    font-size: 11px;
    font-weight: 750;
    letter-spacing: .02em;
    text-align: left;
    text-transform: uppercase;
    white-space: nowrap;
}

.products-table tbody tr {
    border-bottom: 1px solid #eef1f4;
    transition: background .15s ease;
}

.products-table tbody tr:last-child {
    border-bottom: 0;
}

.products-table tbody tr:hover {
    background: #fbfdff;
}

.products-table tbody tr.is-selected {
    background: #f7fcff;
}

.products-table tbody tr.is-disabled {
    opacity: .58;
}

.products-table td {
    padding: 13px 14px;
    color: #344054;
    font-size: 13px;
    vertical-align: middle;
}

.product-column {
    min-width: 300px;
}

.quantity-column {
    width: 150px;
    text-align: center !important;
}

.subtotal-column {
    width: 125px;
    text-align: right !important;
}

.product-cell {
    display: flex;
    align-items: center;
    gap: 11px;
    min-width: 260px;
}

.product-image {
    display: flex;
    align-items: center;
    justify-content: center;
    flex: 0 0 44px;
    width: 44px;
    height: 44px;
    overflow: hidden;
    border: 1px solid #e4e9ef;
    border-radius: 9px;
    background: #f8fafc;
    color: #94a3b8;
}

.product-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.product-info {
    display: flex;
    flex-direction: column;
    min-width: 0;
}

.product-info strong {
    overflow: hidden;
    color: #172b4d;
    font-size: 13px;
    font-weight: 700;
    line-height: 1.35;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.product-info span {
    margin-top: 3px;
    color: #8b98a9;
    font-size: 11px;
}

.type-badge {
    display: inline-flex;
    padding: 4px 8px;
    border-radius: 7px;
    background: #f3f1ff;
    color: #6753b7;
    font-size: 10px;
    font-weight: 700;
}

.muted-text {
    color: #a0a9b5;
}

.price-value {
    color: #172b4d;
    font-size: 13px;
    white-space: nowrap;
}

.stock-value {
    color: #18a987;
    font-size: 13px;
    font-weight: 750;
}

.stock-value.stock-low {
    color: #d18b19;
}

.stock-value.stock-empty {
    color: #e83e4d;
}

.stock-label {
    display: block;
    margin-top: 2px;
    color: #98a2b3;
    font-size: 10px;
}

.quantity-control {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.quantity-button {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 29px;
    height: 29px;
    border: 1px solid #dce4eb;
    border-radius: 8px;
    background: #ffffff;
    color: #526173;
    cursor: pointer;
    transition:
        background .15s ease,
        border-color .15s ease,
        color .15s ease;
}

.quantity-button:hover:not(:disabled) {
    border-color: #a9d9f2;
    background: #f3faff;
    color: #249edb;
}

.quantity-button.add:hover:not(:disabled) {
    background: #eaf6fc;
}

.quantity-button:disabled {
    opacity: .4;
    cursor: not-allowed;
}

.quantity-number {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 26px;
    color: #172b4d;
    font-size: 13px;
    font-weight: 750;
}

.subtotal-cell {
    text-align: right;
}

.subtotal-cell strong {
    color: #1769a8;
    font-size: 13px;
    white-space: nowrap;
}

.product-search-empty {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 7px;
    min-height: 190px;
    padding: 30px;
    color: #98a2b3;
    text-align: center;
}

.product-search-empty svg {
    margin-bottom: 4px;
    color: #94a3b8;
}

.product-search-empty strong {
    color: #475467;
    font-size: 14px;
}

.product-search-empty span {
    color: #98a2b3;
    font-size: 12px;
}

.product-search-empty .btn {
    margin-top: 7px;
}

.stock-error {
    margin: 15px 20px 20px;
}

.empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    min-height: 220px;
    padding: 30px;
    color: #98a2b3;
    text-align: center;
}

.empty-state svg {
    margin-bottom: 12px;
}

.empty-state strong {
    color: #475467;
    font-size: 14px;
}

.empty-state span {
    margin-top: 6px;
    color: #98a2b3;
    font-size: 12px;
}

/* =========================================================
   SUMMARY
   ========================================================= */

.summary-grid {
    display: grid;
    grid-template-columns: minmax(0, 1fr) minmax(320px, 430px);
    gap: 20px;
}

.discount-field {
    padding: 20px;
}

.money-input {
    position: relative;
    display: flex;
    align-items: center;
}

.money-input > span {
    position: absolute;
    left: 11px;
    z-index: 1;
    color: #667085;
    font-size: 13px;
    font-weight: 700;
    pointer-events: none;
}

.money-input .form-input {
    padding-left: 26px;
}

.summary-lines {
    padding: 18px 20px 20px;
}

.summary-lines > div {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 15px;
    padding: 8px 0;
    color: #667085;
    font-size: 13px;
}

.summary-lines > div strong {
    color: #344054;
}

.discount-value {
    color: #d94c9a !important;
}

.summary-total {
    margin-top: 8px;
    padding-top: 15px !important;
    border-top: 1px solid #e9edf2;
}

.summary-total span {
    color: #172b4d;
    font-size: 15px;
    font-weight: 750;
}

.summary-total strong {
    color: #1769a8 !important;
    font-size: 21px;
}

/* =========================================================
   PAYMENTS
   ========================================================= */

.add-payment {
    flex: 0 0 auto;
}

.payments-list {
    display: flex;
    flex-direction: column;
}

.payment-row {
    display: grid;
    grid-template-columns:
        34px
        minmax(200px, 1fr)
        minmax(140px, 180px)
        minmax(180px, 1fr)
        34px;
    align-items: end;
    gap: 12px;
    padding: 17px 20px;
    border-bottom: 1px solid #edf1f5;
}

.payment-number {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 29px;
    height: 29px;
    margin-bottom: 5px;
    border-radius: 8px;
    background: #eaf6fc;
    color: #1769a8;
    font-size: 11px;
    font-weight: 750;
}

.payment-field {
    min-width: 0;
}

.remove-payment {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 34px;
    height: 34px;
    margin-bottom: 3px;
    border: 0;
    border-radius: 8px;
    background: #fff5f6;
    color: #e83e4d;
    cursor: pointer;
}

.remove-payment:hover {
    background: #ffe8eb;
}

.payment-empty {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 10px;
    min-height: 180px;
    padding: 25px;
    color: #98a2b3;
    text-align: center;
}

.payment-empty span {
    font-size: 12px;
}

.payment-summary {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1px;
    margin: 0 20px 20px;
    overflow: hidden;
    border: 1px solid #e8edf2;
    border-radius: 11px;
    background: #e8edf2;
}

.payment-summary > div {
    display: flex;
    flex-direction: column;
    gap: 5px;
    padding: 13px 14px;
    background: #fbfcfd;
}

.payment-summary span {
    color: #7b8798;
    font-size: 10px;
    font-weight: 650;
}

.payment-summary strong {
    color: #172b4d;
    font-size: 14px;
}

.payment-change {
    background: #f1fcf9 !important;
}

.payment-change strong {
    color: #159b7f;
}

.payment-remaining {
    background: #fffaf0 !important;
}

.payment-remaining strong {
    color: #c78316;
}

/* =========================================================
   NOTES
   ========================================================= */

.notes-input {
    display: block;
    min-height: 100px;
    margin: 20px;
    width: calc(100% - 40px);
}

/* =========================================================
   ACTIONS
   ========================================================= */

.form-actions {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 10px;
    padding-top: 2px;
}

.submit-button {
    min-width: 160px;
}

/* =========================================================
   RESPONSIVE
   ========================================================= */

@media (max-width: 1100px) {
    .customer-content {
        grid-template-columns:
            minmax(250px, 1fr)
            35px
            minmax(0, 1fr);
    }

    .payment-row {
        grid-template-columns:
            34px
            1fr
            1fr
            34px;
    }

    .reference-field {
        grid-column: 2 / 4;
    }

    .remove-payment {
        grid-column: 4;
        grid-row: 1;
    }

    .payment-summary {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 800px) {
    .page-header {
        padding: 22px 18px 18px;
    }

    .page-header-content {
        align-items: flex-start;
        flex-direction: column;
    }

    .sale-form {
        padding: 18px;
    }

    .customer-content {
        grid-template-columns: 1fr;
    }

    .customer-divider {
        display: none;
    }

    .manual-column {
        padding-top: 4px;
    }

    .summary-grid {
        grid-template-columns: 1fr;
    }

    .payment-row {
        grid-template-columns:
            34px
            1fr
            34px;
    }

    .method-field,
    .amount-field,
    .reference-field {
        grid-column: 2;
    }

    .remove-payment {
        grid-column: 3;
        grid-row: 1;
    }

    .payment-summary {
        grid-template-columns: 1fr 1fr;
        margin: 0 15px 15px;
    }

    .card-header {
        padding: 16px;
    }

    .products-toolbar {
        align-items: stretch;
        flex-direction: column;
    }

    .product-search {
        max-width: none;
    }

    .products-results {
        text-align: right;
    }

    .products-table td,
    .products-table thead th {
        padding-left: 10px;
        padding-right: 10px;
    }
}

@media (max-width: 520px) {
    .sale-form {
        padding: 14px;
        gap: 15px;
    }

    .page-header {
        padding: 18px 14px;
    }

    .info-card {
        align-items: flex-start;
        flex-wrap: wrap;
    }

    .channel-badge {
        margin-left: 54px;
    }

    .manual-search {
        flex-direction: column;
    }

    .payment-summary {
        grid-template-columns: 1fr;
    }

    .form-actions {
        flex-direction: column-reverse;
    }

    .form-actions .btn {
        width: 100%;
    }

    .submit-button {
        width: 100%;
    }
}
</style>