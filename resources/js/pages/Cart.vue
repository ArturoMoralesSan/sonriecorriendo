<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import {
    ArrowLeft,
    Minus,
    Plus,
    ShoppingBag,
    Trash2,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';
import Swal from 'sweetalert2';

import LandingFooter from '@/components/landing/LandingFooter.vue';
import LandingHeader from '@/components/landing/LandingHeader.vue';

interface CartItem {
    id: number;
    name: string;
    slug: string;
    image: string | null;
    price: number;
    quantity: number;
    stock: number;
    subtotal: number;
}

interface CheckoutResponse {
    checkout_url?: string;
    message?: string;
    errors?: {
        sale?: string[];
    };
}

const props = defineProps<{
    items: CartItem[];
    subtotal: number;
    totalItems: number;
}>();

const processingCheckout = ref(false);

const formatPrice = (value: number): string => {
    return `$${Number(value).toLocaleString('es-MX', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 2,
    })}`;
};

const imageUrl = (image: string | null): string => {
    if (!image) {
        return 'https://images.pexels.com/photos/6311387/pexels-photo-6311387.jpeg?auto=compress&cs=tinysrgb&w=1200';
    }

    if (
        image.startsWith('http://') ||
        image.startsWith('https://')
    ) {
        return image;
    }

    return `/storage/${image}`;
};

const updateQuantity = (
    item: CartItem,
    quantity: number,
): void => {
    if (quantity < 1) {
        return;
    }

    if (quantity > item.stock) {
        quantity = item.stock;
    }

    router.patch(
        '/carrito/actualizar',
        {
            product_id: item.id,
            quantity,
        },
        {
            preserveScroll: true,
        },
    );
};

const increaseQuantity = (item: CartItem): void => {
    if (item.quantity >= item.stock) {
        return;
    }

    updateQuantity(
        item,
        item.quantity + 1,
    );
};

const decreaseQuantity = (item: CartItem): void => {
    if (item.quantity <= 1) {
        return;
    }

    updateQuantity(
        item,
        item.quantity - 1,
    );
};

const removeItem = (item: CartItem): void => {
    router.delete(
        '/carrito/eliminar',
        {
            data: {
                product_id: item.id,
            },
            preserveScroll: true,
        },
    );
};

const clearCart = (): void => {
    router.delete(
        '/carrito/vaciar',
        {
            preserveScroll: true,
        },
    );
};

const checkout = async (): Promise<void> => {
    if (
        !props.items.length ||
        processingCheckout.value
    ) {
        return;
    }

    processingCheckout.value = true;

    try {
        const csrfToken = document
            .querySelector('meta[name="csrf-token"]')
            ?.getAttribute('content');

        const response = await fetch(
            '/carrito/finalizar',
            {
                method: 'POST',

                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    ...(csrfToken
                        ? {
                              'X-CSRF-TOKEN': csrfToken,
                          }
                        : {}),
                },

                credentials: 'same-origin',
            },
        );

        const data: CheckoutResponse =
            await response.json();

        if (!response.ok) {
            const saleError =
                data?.errors?.sale?.[0];

            throw new Error(
                saleError ||
                data?.message ||
                'No fue posible iniciar el pago.',
            );
        }

        if (!data.checkout_url) {
            throw new Error(
                'Mercado Pago no devolvió la URL de Checkout.',
            );
        }

        window.location.href =
            data.checkout_url;
    } catch (error) {
        console.error(
            'Error al iniciar checkout:',
            error,
        );

        Swal.fire({
            icon: 'error',
            title: 'No se pudo iniciar el pago',
            text:
                error instanceof Error
                    ? error.message
                    : 'Ocurrió un error inesperado.',
            confirmButtonText: 'Aceptar',
        });

        processingCheckout.value = false;
    }
};

const hasItems = computed(() => {
    return props.items.length > 0;
});
</script>

<template>
    <Head title="Carrito">
        <meta
            name="description"
            content="Revisa los productos que agregaste al carrito de Sonríe Corriendo."
        />
    </Head>

    <div class="cart-page">
        <LandingHeader />

        <main>
            <section class="cart-hero">
                <div class="cart-container">
                    <span class="cart-eyebrow">
                        SONRÍE CORRIENDO
                    </span>

                    <h1>
                        Tu carrito
                    </h1>

                    <p>
                        Revisa tus productos antes de continuar con tu compra.
                    </p>
                </div>
            </section>

            <section class="cart-content">
                <div class="cart-container">
                    <!-- CARRITO VACÍO -->
                    <div
                        v-if="!hasItems"
                        class="empty-cart"
                    >
                        <div class="empty-cart-icon">
                            <ShoppingBag :size="34" />
                        </div>

                        <h2>
                            Tu carrito está vacío
                        </h2>

                        <p>
                            Todavía no has agregado productos.
                        </p>

                        <Link
                            href="/tienda"
                            class="continue-shopping-button"
                        >
                            <ArrowLeft :size="17" />

                            Ir a la tienda
                        </Link>
                    </div>

                    <!-- CARRITO -->
                    <div
                        v-else
                        class="cart-layout"
                    >
                        <div class="cart-items">
                            <div class="cart-items-header">
                                <div>
                                    <span>
                                        {{ totalItems }}
                                        {{ totalItems === 1 ? 'producto' : 'productos' }}
                                    </span>
                                </div>

                                <button
                                    type="button"
                                    class="clear-cart-button"
                                    @click="clearCart"
                                >
                                    <Trash2 :size="15" />

                                    Vaciar carrito
                                </button>
                            </div>

                            <article
                                v-for="item in items"
                                :key="item.id"
                                class="cart-item"
                            >
                                <Link
                                    :href="`/productos/${item.slug}`"
                                    class="cart-item-image"
                                >
                                    <img
                                        :src="imageUrl(item.image)"
                                        :alt="item.name"
                                    />
                                </Link>

                                <div class="cart-item-info">
                                    <Link
                                        :href="`/productos/${item.slug}`"
                                        class="cart-item-name"
                                    >
                                        {{ item.name }}
                                    </Link>

                                    <span class="cart-item-price">
                                        {{ formatPrice(item.price) }}
                                        MXN
                                    </span>

                                    <div class="cart-item-actions">
                                        <div class="quantity-control">
                                            <button
                                                type="button"
                                                aria-label="Disminuir cantidad"
                                                :disabled="item.quantity <= 1"
                                                @click="decreaseQuantity(item)"
                                            >
                                                <Minus :size="15" />
                                            </button>

                                            <span>
                                                {{ item.quantity }}
                                            </span>

                                            <button
                                                type="button"
                                                aria-label="Aumentar cantidad"
                                                :disabled="item.quantity >= item.stock"
                                                @click="increaseQuantity(item)"
                                            >
                                                <Plus :size="15" />
                                            </button>
                                        </div>

                                        <button
                                            type="button"
                                            class="remove-item-button"
                                            @click="removeItem(item)"
                                        >
                                            <Trash2 :size="15" />

                                            Eliminar
                                        </button>
                                    </div>
                                </div>

                                <div class="cart-item-subtotal">
                                    <span>
                                        Subtotal
                                    </span>

                                    <strong>
                                        {{ formatPrice(item.subtotal) }}
                                    </strong>
                                </div>
                            </article>
                        </div>

                        <!-- RESUMEN -->
                        <aside class="cart-summary">
                            <span class="summary-eyebrow">
                                RESUMEN
                            </span>

                            <h2>
                                Tu compra
                            </h2>

                            <div class="summary-row">
                                <span>
                                    Productos
                                </span>

                                <strong>
                                    {{ totalItems }}
                                </strong>
                            </div>

                            <div class="summary-row">
                                <span>
                                    Subtotal
                                </span>

                                <strong>
                                    {{ formatPrice(subtotal) }}
                                    MXN
                                </strong>
                            </div>

                            <div class="summary-divider"></div>

                            <div class="summary-total">
                                <span>
                                    Total
                                </span>

                                <strong>
                                    {{ formatPrice(subtotal) }}
                                    MXN
                                </strong>
                            </div>

                            <button
                                type="button"
                                class="checkout-button"
                                :disabled="processingCheckout"
                                @click="checkout"
                            >
                                {{
                                    processingCheckout
                                        ? 'Creando pedido...'
                                        : 'Finalizar compra'
                                }}
                            </button>

                            <p class="checkout-note">
                                Tu pedido quedará registrado y podrás continuar con el pago posteriormente.
                            </p>

                            <Link
                                href="/tienda"
                                class="continue-shopping-link"
                            >
                                <ArrowLeft :size="15" />

                                Seguir comprando
                            </Link>
                        </aside>
                    </div>
                </div>
            </section>
        </main>

        <LandingFooter />
    </div>
</template>

<style scoped>
/* =========================================================
   BASE
   ========================================================= */

.cart-page {
    --blue-deep: #12558c;
    --blue: #1769a8;
    --blue-light: #249edb;
    --pink: #d94c9a;
    --pink-light: #f48bb0;

    --text: #172b4d;
    --muted: #64748b;
    --border: #e1ebf3;
    --bg: #f8fafc;

    width: 100%;
    min-height: 100vh;

    overflow-x: hidden;

    background: var(--bg);
    color: var(--text);
}

.cart-container {
    width: min(1180px, calc(100% - 40px));
    margin: 0 auto;
}


/* =========================================================
   HERO
   ========================================================= */

.cart-hero {
    position: relative;

    overflow: hidden;

    padding: 65px 0 70px;

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

.cart-hero::before {
    content: '';

    position: absolute;

    width: 380px;
    height: 380px;

    right: -130px;
    top: -230px;

    border-radius: 50%;

    background: rgba(255, 255, 255, 0.08);

    pointer-events: none;
}

.cart-hero::after {
    content: '';

    position: absolute;

    width: 280px;
    height: 280px;

    left: -170px;
    bottom: -220px;

    border-radius: 50%;

    background: rgba(244, 139, 176, 0.1);

    pointer-events: none;
}

.cart-eyebrow {
    position: relative;
    z-index: 1;

    display: inline-block;

    margin-bottom: 8px;

    color: rgba(255, 255, 255, 0.72);

    font-size: 11px;
    font-weight: 800;

    letter-spacing: 1px;
    text-transform: uppercase;
}

.cart-hero h1 {
    position: relative;
    z-index: 1;

    margin: 0;

    color: #fff;

    font-size: clamp(42px, 6vw, 68px);
    line-height: 1;
    letter-spacing: -2.8px;

    font-weight: 800;
}

.cart-hero p {
    position: relative;
    z-index: 1;

    max-width: 620px;

    margin: 14px 0 0;

    color: rgba(255, 255, 255, 0.84);

    font-size: 16px;
    line-height: 1.6;
}


/* =========================================================
   CONTENT
   ========================================================= */

.cart-content {
    padding: 55px 0 75px;
}


/* =========================================================
   LAYOUT
   ========================================================= */

.cart-layout {
    display: grid;

    grid-template-columns:
        minmax(0, 1fr)
        350px;

    gap: 28px;

    align-items: start;
}


/* =========================================================
   ITEMS
   ========================================================= */

.cart-items {
    min-width: 0;
}

.cart-items-header {
    display: flex;
    align-items: center;
    justify-content: space-between;

    margin-bottom: 13px;
}

.cart-items-header span {
    color: var(--muted);

    font-size: 12px;
    font-weight: 700;
}

.clear-cart-button {
    display: inline-flex;
    align-items: center;

    gap: 6px;

    padding: 0;

    border: 0;

    background: transparent;

    color: #c23c4c;

    font-size: 11px;
    font-weight: 800;

    cursor: pointer;
}

.clear-cart-button:hover {
    color: #a52f3d;
}


/* =========================================================
   ITEM
   ========================================================= */

.cart-item {
    display: grid;

    grid-template-columns: 105px minmax(0, 1fr) auto;

    gap: 18px;

    align-items: center;

    margin-bottom: 12px;
    padding: 15px;

    border: 1px solid var(--border);
    border-radius: 16px;

    background: #fff;

    box-shadow:
        0 8px 25px rgba(23, 43, 77, 0.04);
}

.cart-item-image {
    width: 105px;
    height: 105px;

    display: flex;
    align-items: center;
    justify-content: center;

    overflow: hidden;

    border-radius: 12px;

    background: #f7fafc;
}

.cart-item-image img {
    width: 100%;
    height: 100%;

    object-fit: contain;

    padding: 10px;
}

.cart-item-info {
    min-width: 0;
}

.cart-item-name {
    display: block;

    color: var(--text);

    font-size: 16px;
    font-weight: 800;

    line-height: 1.25;

    text-decoration: none;
}

.cart-item-name:hover {
    color: var(--blue);
}

.cart-item-price {
    display: block;

    margin-top: 5px;

    color: var(--muted);

    font-size: 11px;
    font-weight: 700;
}

.cart-item-actions {
    display: flex;
    align-items: center;

    gap: 12px;

    margin-top: 14px;
}

.quantity-control {
    display: flex;
    align-items: center;

    height: 36px;

    border: 1px solid #d6e2eb;
    border-radius: 9px;

    background: #fff;
}

.quantity-control button {
    width: 34px;
    height: 100%;

    display: flex;
    align-items: center;
    justify-content: center;

    border: 0;

    background: transparent;

    color: var(--blue);

    cursor: pointer;
}

.quantity-control button:hover:not(:disabled) {
    color: var(--pink);
}

.quantity-control button:disabled {
    opacity: 0.3;

    cursor: not-allowed;
}

.quantity-control span {
    min-width: 28px;

    text-align: center;

    color: var(--text);

    font-size: 12px;
    font-weight: 800;
}

.remove-item-button {
    display: inline-flex;
    align-items: center;

    gap: 5px;

    padding: 0;

    border: 0;

    background: transparent;

    color: #c23c4c;

    font-size: 10px;
    font-weight: 800;

    cursor: pointer;
}

.remove-item-button:hover {
    color: #a52f3d;
}

.cart-item-subtotal {
    min-width: 100px;

    text-align: right;
}

.cart-item-subtotal span {
    display: block;

    margin-bottom: 4px;

    color: var(--muted);

    font-size: 9px;
    font-weight: 700;

    text-transform: uppercase;
}

.cart-item-subtotal strong {
    color: var(--text);

    font-size: 16px;
    font-weight: 800;
}


/* =========================================================
   SUMMARY
   ========================================================= */

.cart-summary {
    position: sticky;
    top: 20px;

    padding: 23px;

    border: 1px solid var(--border);
    border-radius: 17px;

    background: #fff;

    box-shadow:
        0 12px 35px rgba(23, 43, 77, 0.06);
}

.summary-eyebrow {
    display: block;

    margin-bottom: 5px;

    color: var(--blue-light);

    font-size: 9px;
    font-weight: 800;

    letter-spacing: 1px;
}

.cart-summary h2 {
    margin: 0 0 20px;

    color: var(--text);

    font-size: 24px;
    line-height: 1.1;

    letter-spacing: -0.7px;
}

.summary-row {
    display: flex;
    align-items: center;
    justify-content: space-between;

    margin-bottom: 11px;

    color: var(--muted);

    font-size: 12px;
}

.summary-row strong {
    color: var(--text);

    font-weight: 800;
}

.summary-divider {
    height: 1px;

    margin: 17px 0;

    background: var(--border);
}

.summary-total {
    display: flex;
    align-items: center;
    justify-content: space-between;

    color: var(--text);

    font-size: 13px;
    font-weight: 800;
}

.summary-total strong {
    color: var(--pink);

    font-size: 21px;
}

.checkout-button {
    width: 100%;

    min-height: 48px;

    margin-top: 20px;

    border: 0;
    border-radius: 11px;

    background:
        linear-gradient(
            90deg,
            var(--blue-deep),
            var(--blue),
            var(--blue-light),
            var(--pink)
        );

    color: #fff;

    font-size: 12px;
    font-weight: 800;

    cursor: pointer;

    opacity: 1;

    transition:
        opacity 0.2s ease,
        transform 0.2s ease;
}

.checkout-button:hover:not(:disabled) {
    transform: translateY(-1px);
}

.checkout-button:disabled {
    cursor: not-allowed;

    opacity: 0.55;
}

.checkout-note {
    margin: 10px 0 0;

    color: var(--muted);

    font-size: 10px;
    line-height: 1.5;

    text-align: center;
}

.continue-shopping-link {
    display: flex;
    align-items: center;
    justify-content: center;

    gap: 6px;

    margin-top: 17px;

    color: var(--blue);

    font-size: 11px;
    font-weight: 800;

    text-decoration: none;
}

.continue-shopping-link:hover {
    color: var(--pink);
}


/* =========================================================
   EMPTY
   ========================================================= */

.empty-cart {
    max-width: 600px;

    margin: 0 auto;

    padding: 55px 30px;

    border: 1px solid var(--border);
    border-radius: 18px;

    background: #fff;

    text-align: center;

    box-shadow:
        0 12px 35px rgba(23, 43, 77, 0.05);
}

.empty-cart-icon {
    width: 72px;
    height: 72px;

    display: flex;
    align-items: center;
    justify-content: center;

    margin: 0 auto 18px;

    border-radius: 18px;

    background: #eaf6fc;

    color: var(--blue);
}

.empty-cart h2 {
    margin: 0;

    color: var(--text);

    font-size: 26px;
    font-weight: 800;
}

.empty-cart p {
    margin: 9px 0 22px;

    color: var(--muted);

    font-size: 13px;
}

.continue-shopping-button {
    display: inline-flex;
    align-items: center;

    gap: 7px;

    padding: 11px 17px;

    border-radius: 10px;

    background: var(--blue);

    color: #fff;

    font-size: 12px;
    font-weight: 800;

    text-decoration: none;

    transition:
        background 0.2s ease,
        transform 0.2s ease;
}

.continue-shopping-button:hover {
    background: var(--pink);
    transform: translateY(-2px);
}


/* =========================================================
   RESPONSIVE
   ========================================================= */

@media (max-width: 900px) {
    .cart-layout {
        grid-template-columns: 1fr;
    }

    .cart-summary {
        position: static;
    }
}

@media (max-width: 650px) {
    .cart-container {
        width: calc(100% - 28px);
    }

    .cart-hero {
        padding: 60px 0 65px;
    }

    .cart-hero h1 {
        font-size: 42px;
        letter-spacing: -1.6px;
    }

    .cart-hero p {
        font-size: 15px;
    }

    .cart-content {
        padding: 35px 0 50px;
    }

    .cart-item {
        grid-template-columns: 80px minmax(0, 1fr);

        gap: 13px;

        padding: 12px;
    }

    .cart-item-image {
        width: 80px;
        height: 80px;
    }

    .cart-item-subtotal {
        grid-column: 2;

        min-width: 0;

        margin-top: -5px;

        text-align: left;
    }

    .cart-item-actions {
        flex-wrap: wrap;
    }

    .cart-items-header {
        align-items: flex-start;
        flex-direction: column;

        gap: 8px;
    }

    .empty-cart {
        padding: 45px 20px;
    }
}
</style>