<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import {
    ArrowRight,
    Menu,
    Search,
    ShoppingCart,
    Trash2,
    X,
} from 'lucide-vue-next';
import {
    computed,
    nextTick,
    onBeforeUnmount,
    onMounted,
    ref,
    watch,
} from 'vue';

import { login, register } from '@/routes';
import admin from '@/routes/admin';

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

interface CartProps {
    totalItems: number;
    subtotal: number;
    items: CartItem[];
}

interface SharedProps {
    auth: {
        user: unknown | null;
    };
    cart?: CartProps;
}

const page = usePage<SharedProps>();

const menuOpen = ref(false);
const cartOpen = ref(false);
const cartButtonRef = ref<HTMLElement | null>(null);
const cartDropdownRef = ref<HTMLElement | null>(null);
const cartAnimating = ref(false);

const cartCount = computed(() => {
    return Number(page.props.cart?.totalItems ?? 0);
});

const cartItems = computed(() => {
    return page.props.cart?.items ?? [];
});

const cartSubtotal = computed(() => {
    return Number(page.props.cart?.subtotal ?? 0);
});

let animationTimeout: ReturnType<typeof setTimeout> | null = null;

const formatPrice = (value: number): string => {
    return `$${Number(value).toLocaleString('es-MX', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 2,
    })}`;
};

const getImageUrl = (image: string | null): string => {
    if (!image) {
        return 'https://images.pexels.com/photos/6311387/pexels-photo-6311387.jpeg?auto=compress&cs=tinysrgb&w=600';
    }

    if (
        image.startsWith('http://') ||
        image.startsWith('https://')
    ) {
        return image;
    }

    return `/storage/${image}`;
};

const animateToCart = async (imageUrl: string) => {
    if (!imageUrl) {
        return;
    }

    await nextTick();

    const cartButton = cartButtonRef.value;

    if (!cartButton) {
        return;
    }

    const productImage = document.querySelector(
        '.product-image img',
    ) as HTMLImageElement | null;

    if (!productImage) {
        return;
    }

    const imageRect = productImage.getBoundingClientRect();
    const cartRect = cartButton.getBoundingClientRect();

    const flyingImage = document.createElement('img');

    flyingImage.src = imageUrl;
    flyingImage.alt = '';

    const startSize = Math.min(
        Math.max(imageRect.width * 0.18, 55),
        100,
    );

    const startX =
        imageRect.left +
        imageRect.width / 2 -
        startSize / 2;

    const startY =
        imageRect.top +
        imageRect.height / 2 -
        startSize / 2;

    const targetX =
        cartRect.left +
        cartRect.width / 2 -
        12;

    const targetY =
        cartRect.top +
        cartRect.height / 2 -
        12;

    flyingImage.className = 'cart-flying-image';

    flyingImage.style.width = `${startSize}px`;
    flyingImage.style.height = `${startSize}px`;
    flyingImage.style.left = `${startX}px`;
    flyingImage.style.top = `${startY}px`;

    document.body.appendChild(flyingImage);

    requestAnimationFrame(() => {
        flyingImage.classList.add('cart-flying-image-active');

        flyingImage.style.setProperty(
            '--cart-target-x',
            `${targetX - startX}px`,
        );

        flyingImage.style.setProperty(
            '--cart-target-y',
            `${targetY - startY}px`,
        );
    });

    window.setTimeout(() => {
        flyingImage.remove();

        cartAnimating.value = false;

        if (animationTimeout) {
            clearTimeout(animationTimeout);
        }
    }, 720);
};

const handleCartAnimation = (event: Event) => {
    const customEvent = event as CustomEvent<{
        imageUrl?: string;
    }>;

    const imageUrl = customEvent.detail?.imageUrl;

    if (!imageUrl) {
        return;
    }

    cartAnimating.value = true;

    animateToCart(imageUrl);

    if (animationTimeout) {
        clearTimeout(animationTimeout);
    }

    animationTimeout = setTimeout(() => {
        cartAnimating.value = false;
    }, 800);
};

const toggleCart = () => {
    cartOpen.value = !cartOpen.value;

    if (cartOpen.value) {
        menuOpen.value = false;
    }
};

const closeCart = () => {
    cartOpen.value = false;
};

const handleDocumentClick = (event: MouseEvent) => {
    const target = event.target as Node;

    if (
        cartButtonRef.value?.contains(target) ||
        cartDropdownRef.value?.contains(target)
    ) {
        return;
    }

    closeCart();
};

const handleEscape = (event: KeyboardEvent) => {
    if (event.key === 'Escape') {
        closeCart();
    }
};

watch(cartCount, (newValue, oldValue) => {
    if (newValue !== oldValue) {
        cartAnimating.value = true;

        if (animationTimeout) {
            clearTimeout(animationTimeout);
        }

        animationTimeout = setTimeout(() => {
            cartAnimating.value = false;
        }, 700);
    }
});

onMounted(() => {
    window.addEventListener(
        'sonrie-cart-animation',
        handleCartAnimation,
    );

    document.addEventListener(
        'click',
        handleDocumentClick,
    );

    document.addEventListener(
        'keydown',
        handleEscape,
    );
});

onBeforeUnmount(() => {
    window.removeEventListener(
        'sonrie-cart-animation',
        handleCartAnimation,
    );

    document.removeEventListener(
        'click',
        handleDocumentClick,
    );

    document.removeEventListener(
        'keydown',
        handleEscape,
    );

    if (animationTimeout) {
        clearTimeout(animationTimeout);
    }
});
</script>

<template>
    <header class="landing-header">
        <div class="landing-container landing-header-inner">
            <Link
                href="/"
                class="landing-logo"
                aria-label="Sonríe Corriendo"
            >
                <img
                    src="/images/sonrie-corriendo.png"
                    alt="Sonríe Corriendo"
                    class="landing-logo-image"
                />
            </Link>

            <nav
                class="landing-nav"
                :class="{ 'landing-nav-open': menuOpen }"
            >
                <Link
                    href="/"
                    class="landing-nav-link active"
                    @click="menuOpen = false"
                >
                    Inicio
                </Link>

                <a
                    href="#eventos"
                    class="landing-nav-link"
                    @click="menuOpen = false"
                >
                    Eventos
                </a>

                <a
                    href="#rutas"
                    class="landing-nav-link"
                    @click="menuOpen = false"
                >
                    Rutas
                </a>

                <a
                    href="#galeria"
                    class="landing-nav-link"
                    @click="menuOpen = false"
                >
                    Galería
                </a>

                <a
                    href="#tienda"
                    class="landing-nav-link"
                    @click="menuOpen = false"
                >
                    Tienda
                </a>

                <a
                    href="#patrocinadores"
                    class="landing-nav-link"
                    @click="menuOpen = false"
                >
                    Patrocinadores
                </a>

                <a
                    href="#clubes"
                    class="landing-nav-link"
                    @click="menuOpen = false"
                >
                    Clubes
                </a>

                <div class="landing-mobile-auth">
                    <template v-if="page.props.auth.user">
                        <Link
                            :href="admin.dashboard()"
                            class="landing-nav-mobile-auth landing-nav-mobile-login"
                            @click="menuOpen = false"
                        >
                            Dashboard
                        </Link>
                    </template>

                    <template v-else>
                        <Link
                            :href="login()"
                            class="landing-nav-mobile-auth landing-nav-mobile-login"
                            @click="menuOpen = false"
                        >
                            Iniciar sesión
                        </Link>

                        <Link
                            :href="register()"
                            class="landing-nav-mobile-auth landing-nav-mobile-register"
                            @click="menuOpen = false"
                        >
                            Registrarse
                        </Link>
                    </template>
                </div>
            </nav>

            <div class="landing-header-actions">
                <button
                    type="button"
                    class="landing-icon-button"
                    aria-label="Buscar"
                >
                    <Search
                        :size="21"
                        :stroke-width="2"
                    />
                </button>

                <div class="cart-wrapper">
                    <button
                        ref="cartButtonRef"
                        type="button"
                        class="landing-icon-button cart-button"
                        :class="{
                            'cart-button-animated': cartAnimating,
                            'cart-button-open': cartOpen,
                        }"
                        :aria-label="
                            cartOpen
                                ? 'Cerrar carrito'
                                : 'Abrir carrito'
                        "
                        :aria-expanded="cartOpen"
                        @click.stop="toggleCart"
                    >
                        <ShoppingCart
                            :size="21"
                            :stroke-width="2"
                        />

                        <span
                            v-if="cartCount > 0"
                            class="cart-badge"
                            :key="cartCount"
                        >
                            {{ cartCount > 99 ? '99+' : cartCount }}
                        </span>
                    </button>

                    <Transition name="cart-dropdown">
                        <div
                            v-if="cartOpen"
                            ref="cartDropdownRef"
                            class="cart-dropdown"
                            @click.stop
                        >
                            <div class="cart-dropdown-header">
                                <div>
                                    <strong>
                                        Tu carrito
                                    </strong>

                                    <span>
                                        {{ cartCount }}
                                        {{
                                            cartCount === 1
                                                ? 'producto'
                                                : 'productos'
                                        }}
                                    </span>
                                </div>

                                <button
                                    type="button"
                                    class="cart-close-button"
                                    aria-label="Cerrar carrito"
                                    @click="closeCart"
                                >
                                    <X :size="17" />
                                </button>
                            </div>

                            <div
                                v-if="cartItems.length"
                                class="cart-dropdown-items"
                            >
                                <Link
                                    v-for="item in cartItems"
                                    :key="item.id"
                                    :href="`/productos/${item.slug}`"
                                    class="cart-dropdown-item"
                                    @click="closeCart"
                                >
                                    <div class="cart-item-image">
                                        <img
                                            :src="getImageUrl(item.image)"
                                            :alt="item.name"
                                        />
                                    </div>

                                    <div class="cart-item-info">
                                        <strong>
                                            {{ item.name }}
                                        </strong>

                                        <span class="cart-item-quantity">
                                            {{ item.quantity }}
                                            ×
                                            {{ formatPrice(item.price) }}
                                        </span>
                                    </div>

                                    <strong class="cart-item-subtotal">
                                        {{ formatPrice(item.subtotal) }}
                                    </strong>
                                </Link>
                            </div>

                            <div
                                v-else
                                class="cart-empty"
                            >
                                <div class="cart-empty-icon">
                                    <ShoppingCart
                                        :size="24"
                                        :stroke-width="1.8"
                                    />
                                </div>

                                <strong>
                                    Tu carrito está vacío
                                </strong>

                                <span>
                                    Agrega productos de nuestra tienda.
                                </span>

                                <Link
                                    href="/tienda"
                                    class="cart-empty-link"
                                    @click="closeCart"
                                >
                                    Ver tienda
                                </Link>
                            </div>

                            <div
                                v-if="cartItems.length"
                                class="cart-dropdown-footer"
                            >
                                <div class="cart-subtotal">
                                    <span>
                                        Subtotal
                                    </span>

                                    <strong>
                                        {{ formatPrice(cartSubtotal) }}
                                        <small>MXN</small>
                                    </strong>
                                </div>

                                <Link
                                    href="/carrito"
                                    class="cart-full-button"
                                    @click="closeCart"
                                >
                                    Ver carrito completo

                                    <ArrowRight
                                        :size="17"
                                        :stroke-width="2"
                                    />
                                </Link>
                            </div>
                        </div>
                    </Transition>
                </div>

                <template v-if="page.props.auth.user">
                    <Link
                        :href="admin.dashboard()"
                        class="landing-btn landing-btn-outline"
                    >
                        Dashboard
                    </Link>
                </template>

                <template v-else>
                    <Link
                        :href="login()"
                        class="landing-btn landing-btn-login"
                    >
                        Iniciar sesión
                    </Link>

                    <Link
                        :href="register()"
                        class="landing-btn landing-btn-gradient"
                    >
                        Registrarse
                    </Link>
                </template>
            </div>

            <button
                type="button"
                class="landing-mobile-toggle"
                :aria-label="menuOpen ? 'Cerrar menú' : 'Abrir menú'"
                :aria-expanded="menuOpen"
                @click="menuOpen = !menuOpen"
            >
                <X
                    v-if="menuOpen"
                    :size="24"
                />

                <Menu
                    v-else
                    :size="24"
                />
            </button>
        </div>
    </header>
</template>

<style scoped>
/* =========================================================
   CART FLYING ANIMATION
   ========================================================= */

:global(.cart-flying-image) {
    position: fixed;
    z-index: 99999;

    display: block;

    object-fit: contain;

    padding: 8px;

    border: 1px solid rgba(255, 255, 255, 0.8);
    border-radius: 14px;

    background: #fff;

    box-shadow:
        0 14px 35px rgba(23, 43, 77, 0.2);

    pointer-events: none;

    opacity: 1;

    transform: translate3d(0, 0, 0) scale(1);

    transition:
        transform 0.68s cubic-bezier(0.22, 0.8, 0.32, 1),
        opacity 0.68s ease;
}

:global(.cart-flying-image-active) {
    transform:
        translate3d(
            var(--cart-target-x),
            var(--cart-target-y),
            0
        )
        scale(0.18);

    opacity: 0.35;
}

/* =========================================================
   CART BUTTON
   ========================================================= */

.cart-wrapper {
    position: relative;
}

.cart-button {
    position: relative;
}

.cart-button-open {
    background: #eaf6fc;
    color: #1769a8;
}

.cart-button-animated {
    animation: cart-button-bounce 0.65s ease;
}

.cart-button-animated svg {
    animation: cart-icon-shake 0.65s ease;
}

.cart-badge {
    position: absolute;

    top: -4px;
    right: -5px;

    min-width: 17px;
    height: 17px;

    display: flex;
    align-items: center;
    justify-content: center;

    padding: 0 4px;

    border: 2px solid #fff;
    border-radius: 999px;

    background: #d94c9a;

    color: #fff;

    font-size: 8px;
    line-height: 1;

    font-weight: 800;

    box-shadow:
        0 3px 9px rgba(217, 76, 154, 0.28);

    animation: cart-badge-pop 0.45s ease;
}

/* =========================================================
   CART DROPDOWN
   ========================================================= */

.cart-dropdown {
    position: absolute;

    top: calc(100% + 14px);
    right: -12px;

    z-index: 5000;

    width: 390px;
    max-width: calc(100vw - 30px);

    overflow: hidden;

    border: 1px solid #e1ebf3;
    border-radius: 18px;

    background: #fff;

    box-shadow:
        0 22px 55px rgba(23, 43, 77, 0.16);
}

.cart-dropdown::before {
    content: '';

    position: absolute;

    top: -7px;
    right: 18px;

    width: 14px;
    height: 14px;

    border-top: 1px solid #e1ebf3;
    border-left: 1px solid #e1ebf3;

    background: #fff;

    transform: rotate(45deg);
}

/* =========================================================
   DROPDOWN HEADER
   ========================================================= */

.cart-dropdown-header {
    position: relative;

    display: flex;
    align-items: center;
    justify-content: space-between;

    padding: 17px 18px;

    border-bottom: 1px solid #edf2f7;

    background: #fff;
}

.cart-dropdown-header > div {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.cart-dropdown-header strong {
    color: #172b4d;

    font-size: 16px;
    font-weight: 800;
}

.cart-dropdown-header span {
    color: #718096;

    font-size: 12px;
}

.cart-close-button {
    width: 30px;
    height: 30px;

    display: flex;
    align-items: center;
    justify-content: center;

    border: 0;
    border-radius: 9px;

    background: #f8fafc;

    color: #64748b;

    cursor: pointer;

    transition:
        background 0.2s ease,
        color 0.2s ease;
}

.cart-close-button:hover {
    background: #eaf6fc;
    color: #1769a8;
}

/* =========================================================
   ITEMS
   ========================================================= */

.cart-dropdown-items {
    max-height: 310px;

    overflow-y: auto;
}

.cart-dropdown-items::-webkit-scrollbar {
    width: 5px;
}

.cart-dropdown-items::-webkit-scrollbar-thumb {
    border-radius: 999px;
    background: #dbe7f0;
}

.cart-dropdown-item {
    display: flex;
    align-items: center;

    gap: 11px;

    padding: 12px 16px;

    border-bottom: 1px solid #f0f4f8;

    color: inherit;
    text-decoration: none;

    transition:
        background 0.2s ease;
}

.cart-dropdown-item:hover {
    background: #f8fbfd;
}

.cart-item-image {
    width: 54px;
    height: 54px;

    flex: 0 0 54px;

    overflow: hidden;

    border: 1px solid #e4edf4;
    border-radius: 11px;

    background: #f8fafc;
}

.cart-item-image img {
    width: 100%;
    height: 100%;

    display: block;

    object-fit: cover;
}

.cart-item-info {
    min-width: 0;

    flex: 1;

    display: flex;
    flex-direction: column;

    gap: 4px;
}

.cart-item-info strong {
    overflow: hidden;

    color: #172b4d;

    font-size: 12px;
    font-weight: 700;

    text-overflow: ellipsis;
    white-space: nowrap;
}

.cart-item-quantity {
    color: #718096;

    font-size: 11px;
}

.cart-item-subtotal {
    flex-shrink: 0;

    color: #1769a8;

    font-size: 12px;
    font-weight: 800;
}

/* =========================================================
   EMPTY
   ========================================================= */

.cart-empty {
    display: flex;
    align-items: center;
    flex-direction: column;

    padding: 30px 20px 28px;

    text-align: center;
}

.cart-empty-icon {
    width: 52px;
    height: 52px;

    display: flex;
    align-items: center;
    justify-content: center;

    margin-bottom: 12px;

    border-radius: 15px;

    background: #eaf6fc;

    color: #249edb;
}

.cart-empty strong {
    margin-bottom: 5px;

    color: #172b4d;

    font-size: 14px;
    font-weight: 800;
}

.cart-empty span {
    max-width: 240px;

    color: #718096;

    font-size: 12px;
    line-height: 1.5;
}

.cart-empty-link {
    margin-top: 15px;

    color: #1769a8;

    font-size: 12px;
    font-weight: 800;

    text-decoration: none;
}

.cart-empty-link:hover {
    color: #d94c9a;
}

/* =========================================================
   FOOTER
   ========================================================= */

.cart-dropdown-footer {
    padding: 15px 16px 16px;

    border-top: 1px solid #edf2f7;

    background: #fbfcfd;
}

.cart-subtotal {
    display: flex;
    align-items: center;
    justify-content: space-between;

    margin-bottom: 12px;
}

.cart-subtotal span {
    color: #64748b;

    font-size: 12px;
}

.cart-subtotal strong {
    color: #172b4d;

    font-size: 17px;
    font-weight: 800;
}

.cart-subtotal small {
    margin-left: 3px;

    color: #718096;

    font-size: 9px;
    font-weight: 700;
}

.cart-full-button {
    width: 100%;
    min-height: 42px;

    display: flex;
    align-items: center;
    justify-content: center;

    gap: 8px;

    border-radius: 11px;

    background: linear-gradient(
        90deg,
        #1769a8 0%,
        #249edb 58%,
        #d94c9a 100%
    );

    color: #fff;

    font-size: 12px;
    font-weight: 800;

    text-decoration: none;

    box-shadow:
        0 8px 18px rgba(36, 158, 219, 0.2);

    transition:
        transform 0.2s ease,
        box-shadow 0.2s ease;
}

.cart-full-button:hover {
    transform: translateY(-1px);

    box-shadow:
        0 11px 24px rgba(36, 158, 219, 0.28);
}

/* =========================================================
   DROPDOWN TRANSITION
   ========================================================= */

.cart-dropdown-enter-active,
.cart-dropdown-leave-active {
    transition:
        opacity 0.18s ease,
        transform 0.18s ease;
}

.cart-dropdown-enter-from,
.cart-dropdown-leave-to {
    opacity: 0;

    transform:
        translateY(-7px)
        scale(0.98);
}

/* =========================================================
   ANIMATIONS
   ========================================================= */

@keyframes cart-button-bounce {
    0% {
        transform: scale(1);
    }

    35% {
        transform: scale(1.16);
    }

    55% {
        transform: scale(0.94);
    }

    75% {
        transform: scale(1.06);
    }

    100% {
        transform: scale(1);
    }
}

@keyframes cart-icon-shake {
    0% {
        transform: rotate(0deg);
    }

    20% {
        transform: rotate(-10deg);
    }

    40% {
        transform: rotate(9deg);
    }

    60% {
        transform: rotate(-6deg);
    }

    80% {
        transform: rotate(4deg);
    }

    100% {
        transform: rotate(0deg);
    }
}

@keyframes cart-badge-pop {
    0% {
        transform: scale(0.4);
        opacity: 0;
    }

    55% {
        transform: scale(1.25);
        opacity: 1;
    }

    100% {
        transform: scale(1);
        opacity: 1;
    }
}

/* =========================================================
   RESPONSIVE
   ========================================================= */

@media (max-width: 768px) {
    .cart-dropdown {
        position: fixed;

        top: 76px;
        right: 15px;

        width: min(390px, calc(100vw - 30px));
    }

    .cart-dropdown::before {
        display: none;
    }
}

@media (max-width: 480px) {
    .cart-dropdown {
        top: 70px;
        right: 10px;

        width: calc(100vw - 20px);

        border-radius: 16px;
    }

    .cart-dropdown-items {
        max-height: 280px;
    }

    .cart-item-image {
        width: 48px;
        height: 48px;

        flex-basis: 48px;
    }
}
</style>