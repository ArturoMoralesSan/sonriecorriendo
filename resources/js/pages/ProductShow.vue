<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import {
    ArrowLeft,
    Check,
    Minus,
    Plus,
    ShoppingBag,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';

import LandingFooter from '@/components/landing/LandingFooter.vue';
import LandingHeader from '@/components/landing/LandingHeader.vue';

interface Product {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    image: string | null;
    type: string | null;
    year: number | null;
    price: number | string;
    stock: number;
    is_active: boolean;
}

const props = defineProps<{
    product: Product;
}>();

const quantity = ref(1);
const addingToCart = ref(false);

const price = computed(() => {
    const value = Number(props.product.price);

    return Number.isFinite(value) ? value : 0;
});

const total = computed(() => {
    return price.value * quantity.value;
});

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

const formatPrice = (value: number): string => {
    return `$${value.toLocaleString('es-MX', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 2,
    })}`;
};

const hasStock = computed(() => {
    return props.product.stock > 0;
});

const stockLabel = computed(() => {
    if (props.product.stock <= 0) {
        return 'Agotado';
    }

    if (props.product.stock <= 5) {
        return `Últimas ${props.product.stock} piezas`;
    }

    return 'Disponible';
});

const increaseQuantity = () => {
    if (quantity.value >= props.product.stock) {
        return;
    }

    quantity.value++;
};

const decreaseQuantity = () => {
    if (quantity.value > 1) {
        quantity.value--;
    }
};

const addToCart = () => {
    if (
        !hasStock.value ||
        addingToCart.value ||
        quantity.value < 1
    ) {
        return;
    }

    addingToCart.value = true;

    const currentImageUrl = imageUrl(props.product.image);

    window.dispatchEvent(
        new CustomEvent('sonrie-cart-animation', {
            detail: {
                imageUrl: currentImageUrl,
            },
        }),
    );

    router.post(
        '/carrito/agregar',
        {
            product_id: props.product.id,
            quantity: quantity.value,
        },
        {
            preserveScroll: true,

            onFinish: () => {
                addingToCart.value = false;
            },
        },
    );
};
</script>

<template>
    <Head :title="product.name">
        <meta
            name="description"
            :content="
                product.description ||
                `Compra ${product.name} en Sonríe Corriendo.`
            "
        />
    </Head>

    <div class="product-page">
        <LandingHeader />

        <main>
            <section class="product-hero">
                <div class="product-container">
                    <div class="product-layout">
                        <!-- IMAGEN -->
                        <div class="product-image-wrapper">
                            <div class="product-image">
                                <img
                                    :src="imageUrl(product.image)"
                                    :alt="product.name"
                                />
                            </div>
                        </div>

                        <!-- INFORMACIÓN -->
                        <div class="product-info">
                            <span
                                v-if="product.type"
                                class="product-type"
                            >
                                {{ product.type }}
                            </span>

                            <h1>
                                {{ product.name }}
                            </h1>

                            <div
                                v-if="product.year"
                                class="product-year"
                            >
                                {{ product.year }}
                            </div>

                            <div class="product-price">
                                {{ formatPrice(price) }}

                                <span>MXN</span>
                            </div>

                            <div
                                v-if="product.description"
                                class="product-description"
                            >
                                <p>
                                    {{ product.description }}
                                </p>
                            </div>

                            <div class="product-status">
                                <span
                                    class="status-dot"
                                    :class="{
                                        'is-empty': !hasStock,
                                    }"
                                ></span>

                                {{ stockLabel }}
                            </div>

                            <!-- COMPRA -->
                            <div
                                v-if="hasStock"
                                class="product-purchase"
                            >
                                <div class="quantity-label">
                                    Cantidad
                                </div>

                                <div class="purchase-row">
                                    <div class="quantity-control">
                                        <button
                                            type="button"
                                            aria-label="Disminuir cantidad"
                                            :disabled="
                                                quantity <= 1 ||
                                                addingToCart
                                            "
                                            @click="decreaseQuantity"
                                        >
                                            <Minus :size="16" />
                                        </button>

                                        <span>
                                            {{ quantity }}
                                        </span>

                                        <button
                                            type="button"
                                            aria-label="Aumentar cantidad"
                                            :disabled="
                                                quantity >= product.stock ||
                                                addingToCart
                                            "
                                            @click="increaseQuantity"
                                        >
                                            <Plus :size="16" />
                                        </button>
                                    </div>

                                    <button
                                        type="button"
                                        class="add-cart-button"
                                        :disabled="addingToCart"
                                        @click="addToCart"
                                    >
                                        <ShoppingBag
                                            :size="19"
                                        />

                                        <span v-if="!addingToCart">
                                            Agregar al carrito
                                        </span>

                                        <span v-else>
                                            Agregando...
                                        </span>
                                    </button>
                                </div>

                                <div class="purchase-total">
                                    <span>Total</span>

                                    <strong>
                                        {{ formatPrice(total) }}
                                        MXN
                                    </strong>
                                </div>
                            </div>

                            <!-- AGOTADO -->
                            <div
                                v-else
                                class="sold-out"
                            >
                                <ShoppingBag :size="18" />

                                Producto agotado
                            </div>

                            <!-- BENEFICIOS -->
                            <div class="product-features">
                                <div class="feature">
                                    <span class="feature-icon">
                                        <Check :size="16" />
                                    </span>

                                    <div>
                                        <strong>
                                            Compra segura
                                        </strong>

                                        <span>
                                            Proceso protegido y confiable
                                        </span>
                                    </div>
                                </div>

                                <div class="feature">
                                    <span class="feature-icon">
                                        <Check :size="16" />
                                    </span>

                                    <div>
                                        <strong>
                                            Producto oficial
                                        </strong>

                                        <span>
                                            Artículos de Sonríe Corriendo
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- INFORMACIÓN -->
            <section class="product-details">
                <div class="product-container">
                    <div class="details-card">
                        <div>
                            <span class="details-kicker">
                                SONRÍE CORRIENDO
                            </span>

                            <h2>
                                Lleva contigo la experiencia
                            </h2>
                        </div>

                        <p>
                            Encuentra productos oficiales para acompañarte
                            dentro y fuera de nuestras carreras.
                        </p>
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

.product-page {
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

.product-container {
    width: min(1180px, calc(100% - 40px));
    margin: 0 auto;
}


/* =========================================================
   HERO
   ========================================================= */

.product-hero {
    padding: 34px 0 65px;

    background: #f8fafc;
}

.product-back {
    display: inline-flex;
    align-items: center;

    gap: 7px;

    margin-bottom: 30px;

    color: var(--blue);

    font-size: 13px;
    font-weight: 700;

    text-decoration: none;

    transition:
        color 0.2s ease,
        transform 0.2s ease;
}

.product-back:hover {
    color: var(--pink);
    transform: translateX(-3px);
}


/* =========================================================
   LAYOUT
   ========================================================= */

.product-layout {
    display: grid;

    grid-template-columns:
        minmax(0, 1.08fr)
        minmax(400px, 0.92fr);

    gap: 55px;

    align-items: center;
}


/* =========================================================
   IMAGE
   ========================================================= */

.product-image-wrapper {
    position: relative;

    min-width: 0;
}

.product-image {
    position: relative;

    width: 100%;
    aspect-ratio: 1 / 0.92;

    display: flex;
    align-items: center;
    justify-content: center;

    overflow: hidden;

    border: 1px solid #dbe8f0;
    border-radius: 22px;

    background: #fff;

    box-shadow:
        0 20px 55px rgba(23, 43, 77, 0.09);
}

.product-image::before {
    content: '';

    position: absolute;

    width: 300px;
    height: 300px;

    right: -130px;
    top: -140px;

    border-radius: 50%;

    background:
        linear-gradient(
            135deg,
            rgba(36, 158, 219, 0.14),
            rgba(217, 76, 154, 0.14)
        );
}

.product-image img {
    position: relative;
    z-index: 1;

    width: 100%;
    height: 100%;

    object-fit: contain;

    padding: 35px;

    transition: transform 0.4s ease;
}

.product-image:hover img {
    transform: scale(1.025);
}


/* =========================================================
   INFO
   ========================================================= */

.product-info {
    min-width: 0;
}

.product-type {
    display: inline-flex;
    align-items: center;

    margin-bottom: 13px;
    padding: 6px 10px;

    border-radius: 999px;

    background: #e9f5fb;

    color: var(--blue);

    font-size: 10px;
    font-weight: 800;

    letter-spacing: 0.7px;
    text-transform: uppercase;
}

.product-info h1 {
    margin: 0;

    color: var(--text);

    font-size: clamp(36px, 4vw, 54px);
    line-height: 1.03;
    letter-spacing: -2px;

    font-weight: 800;
}

.product-price {
    margin-top: 18px;

    color: var(--pink);

    font-size: 31px;
    line-height: 1;

    font-weight: 800;
}

.product-price span {
    margin-left: 4px;

    color: var(--muted);

    font-size: 11px;
    font-weight: 700;
}


/* =========================================================
   YEAR BADGE
   ========================================================= */

.product-year {
    display: inline-flex;
    align-items: center;

    margin-top: 10px;
    padding: 5px 10px;

    border: 1px solid #dce5ef;
    border-radius: 999px;

    background: #fff;

    color: var(--muted);

    font-size: 10px;
    line-height: 1;

    font-weight: 800;

    letter-spacing: 0.5px;
}

.product-description {
    max-width: 570px;

    margin-top: 20px;
}

.product-description p {
    margin: 0;

    color: var(--muted);

    font-size: 14px;
    line-height: 1.7;
}

.product-status {
    display: flex;
    align-items: center;

    gap: 7px;

    margin-top: 17px;

    color: #3f6d5d;

    font-size: 12px;
    font-weight: 700;
}

.status-dot {
    width: 8px;
    height: 8px;

    border-radius: 50%;

    background: #18b89a;
}

.status-dot.is-empty {
    background: #e83e4d;
}


/* =========================================================
   PURCHASE
   ========================================================= */

.product-purchase {
    margin-top: 27px;
    padding-top: 23px;

    border-top: 1px solid var(--border);
}

.quantity-label {
    margin-bottom: 9px;

    color: var(--text);

    font-size: 11px;
    font-weight: 800;

    letter-spacing: 0.5px;
    text-transform: uppercase;
}

.purchase-row {
    display: flex;
    align-items: stretch;

    gap: 10px;
}

.quantity-control {
    display: flex;
    align-items: center;

    height: 48px;

    border: 1px solid #d6e2eb;
    border-radius: 11px;

    background: #fff;
}

.quantity-control button {
    width: 43px;
    height: 100%;

    display: flex;
    align-items: center;
    justify-content: center;

    border: 0;

    background: transparent;

    color: var(--blue);

    cursor: pointer;

    transition:
        color 0.2s ease,
        opacity 0.2s ease;
}

.quantity-control button:hover:not(:disabled) {
    color: var(--pink);
}

.quantity-control button:disabled {
    cursor: not-allowed;

    opacity: 0.35;
}

.quantity-control span {
    min-width: 32px;

    text-align: center;

    color: var(--text);

    font-size: 14px;
    font-weight: 800;
}


/* =========================================================
   ADD TO CART
   ========================================================= */

.add-cart-button {
    flex: 1;

    min-height: 48px;

    display: flex;
    align-items: center;
    justify-content: center;

    gap: 8px;

    padding: 0 20px;

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

    font-size: 13px;
    font-weight: 800;

    cursor: pointer;

    box-shadow:
        0 8px 20px rgba(23, 105, 168, 0.18);

    transition:
        transform 0.2s ease,
        box-shadow 0.2s ease,
        opacity 0.2s ease;
}

.add-cart-button:hover:not(:disabled) {
    transform: translateY(-2px);

    box-shadow:
        0 12px 27px rgba(23, 105, 168, 0.25);
}

.add-cart-button:disabled {
    cursor: wait;

    opacity: 0.72;

    transform: none;
}


/* =========================================================
   TOTAL
   ========================================================= */

.purchase-total {
    display: flex;
    align-items: center;
    justify-content: space-between;

    margin-top: 12px;
    padding: 11px 14px;

    border: 1px solid #dce8f1;
    border-radius: 11px;

    background:
        linear-gradient(
            90deg,
            #f4faff,
            #fff
        );

    color: var(--muted);

    font-size: 11px;
    font-weight: 700;
}

.purchase-total span {
    display: flex;
    align-items: center;

    gap: 6px;
}

.purchase-total span::before {
    content: '';

    width: 6px;
    height: 6px;

    border-radius: 50%;

    background: var(--blue-light);
}

.purchase-total strong {
    color: var(--text);

    font-size: 17px;
    font-weight: 800;
}

.sold-out {
    display: flex;
    align-items: center;
    justify-content: center;

    gap: 8px;

    margin-top: 27px;
    padding: 14px;

    border: 1px solid #f0d5da;
    border-radius: 11px;

    background: #fff7f8;

    color: #c23c4c;

    font-size: 13px;
    font-weight: 800;
}


/* =========================================================
   FEATURES
   ========================================================= */

.product-features {
    display: grid;

    grid-template-columns: repeat(2, minmax(0, 1fr));

    gap: 12px;

    margin-top: 28px;
}

.feature {
    display: flex;
    align-items: flex-start;

    gap: 9px;

    padding: 13px;

    border: 1px solid var(--border);
    border-radius: 12px;

    background: rgba(255, 255, 255, 0.7);
}

.feature-icon {
    flex: 0 0 auto;

    width: 28px;
    height: 28px;

    display: flex;
    align-items: center;
    justify-content: center;

    border-radius: 8px;

    background: #e8f7f3;

    color: #159d83;
}

.feature div {
    display: flex;
    flex-direction: column;

    gap: 2px;
}

.feature strong {
    color: var(--text);

    font-size: 11px;
    font-weight: 800;
}

.feature div span {
    color: var(--muted);

    font-size: 10px;
    line-height: 1.4;
}


/* =========================================================
   DETAILS
   ========================================================= */

.product-details {
    padding: 0 0 70px;
}

.details-card {
    display: flex;
    align-items: center;
    justify-content: space-between;

    gap: 40px;

    padding: 28px 32px;

    border-radius: 18px;

    background:
        linear-gradient(
            90deg,
            #12558c,
            #1769a8 42%,
            #249edb 68%,
            #d94c9a
        );

    color: #fff;
}

.details-kicker {
    display: block;

    margin-bottom: 5px;

    color: rgba(255, 255, 255, 0.65);

    font-size: 9px;
    font-weight: 800;

    letter-spacing: 1px;
    text-transform: uppercase;
}

.details-card h2 {
    margin: 0;

    font-size: 25px;
    line-height: 1.1;
    letter-spacing: -0.6px;
}

.details-card p {
    max-width: 440px;

    margin: 0;

    color: rgba(255, 255, 255, 0.83);

    font-size: 12px;
    line-height: 1.6;
}


/* =========================================================
   RESPONSIVE
   ========================================================= */

@media (max-width: 900px) {
    .product-layout {
        grid-template-columns: 1fr;

        gap: 35px;
    }

    .product-image {
        max-width: 650px;

        margin: 0 auto;
    }

    .product-info {
        max-width: 700px;

        margin: 0 auto;
    }
}

@media (max-width: 600px) {
    .product-container {
        width: calc(100% - 28px);
    }

    .product-hero {
        padding: 25px 0 45px;
    }

    .product-back {
        margin-bottom: 22px;
    }

    .product-layout {
        gap: 27px;
    }

    .product-image {
        border-radius: 17px;
    }

    .product-image img {
        padding: 22px;
    }

    .product-info h1 {
        font-size: 36px;
        letter-spacing: -1.4px;
    }

    .product-price {
        font-size: 28px;
    }

    .product-year {
        margin-top: 9px;
    }

    .purchase-row {
        flex-direction: column;
    }

    .quantity-control {
        width: fit-content;
    }

    .add-cart-button {
        width: 100%;
    }

    .product-features {
        grid-template-columns: 1fr;
    }

    .details-card {
        align-items: flex-start;
        flex-direction: column;

        gap: 14px;

        padding: 25px 22px;
    }

    .details-card h2 {
        font-size: 22px;
    }

    .product-details {
        padding-bottom: 45px;
    }
}
</style>