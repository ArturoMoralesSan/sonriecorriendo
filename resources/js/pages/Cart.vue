<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import {
    ArrowLeft,
    Minus,
    Plus,
    ShoppingBag,
    Trash2,
    MapPin,
    Store,
    Truck,
    UserRound,
    Mail,
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

interface Branch {
    id: number;
    name: string;
    address: string;
    phone?: string | null;
    opening_time?: string | null;
    closing_time?: string | null;
}

interface AuthUser {
    name?: string | null;
    email?: string | null;
}

interface CheckoutResponse {
    checkout_url?: string;
    message?: string;
    errors?: Record<string, string[]>;
}

type DeliveryMethod = 'home_delivery' | 'branch_pickup';

const props = defineProps<{
    items: CartItem[];
    subtotal: number;
    totalItems: number;
    branches?: Branch[];
}>();

const page = usePage();

const authUser = computed<AuthUser | null>(() => {
    const pageProps = page.props as unknown as {
        auth?: {
            user?: AuthUser | null;
        };
    };

    return pageProps.auth?.user ?? null;
});

const isGuest = computed(() => !authUser.value);

const guestName = ref('');
const guestEmail = ref('');

const processingCheckout = ref(false);

const deliveryMethod = ref<DeliveryMethod>('home_delivery');
const branchId = ref<number | null>(null);

const address = ref({
    street: '',
    exterior_number: '',
    interior_number: '',
    neighborhood: '',
    postal_code: '',
    city: '',
    state: '',
    references: '',
});

const branches = computed(() => props.branches ?? []);

const selectedBranch = computed(() => {
    return branches.value.find(
        (branch) => branch.id === branchId.value,
    ) ?? null;
});

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

    updateQuantity(item, item.quantity + 1);
};

const decreaseQuantity = (item: CartItem): void => {
    if (item.quantity <= 1) {
        return;
    }

    updateQuantity(item, item.quantity - 1);
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

const validateCustomer = (): string | null => {
    if (!isGuest.value) {
        return null;
    }

    if (!guestName.value.trim()) {
        return 'Escribe tu nombre completo.';
    }

    if (guestName.value.trim().length < 2) {
        return 'El nombre debe tener al menos 2 caracteres.';
    }

    if (!guestEmail.value.trim()) {
        return 'Escribe tu correo electrónico.';
    }

    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    if (!emailPattern.test(guestEmail.value.trim())) {
        return 'Escribe un correo electrónico válido.';
    }

    return null;
};

const validateDelivery = (): string | null => {
    if (deliveryMethod.value === 'branch_pickup') {
        if (!branchId.value) {
            return 'Selecciona la sucursal donde recogerás tu pedido.';
        }

        if (!selectedBranch.value) {
            return 'La sucursal seleccionada ya no está disponible. Selecciona otra.';
        }

        return null;
    }

    if (!address.value.street.trim()) {
        return 'Escribe la calle de tu domicilio.';
    }

    if (!address.value.exterior_number.trim()) {
        return 'Escribe el número exterior de tu domicilio.';
    }

    if (!address.value.neighborhood.trim()) {
        return 'Escribe la colonia de tu domicilio.';
    }

    if (!address.value.postal_code.trim()) {
        return 'Escribe el código postal.';
    }

    if (!address.value.city.trim()) {
        return 'Escribe la ciudad o municipio.';
    }

    if (!address.value.state.trim()) {
        return 'Escribe el estado.';
    }

    return null;
};

const checkout = async (): Promise<void> => {
    if (!props.items.length || processingCheckout.value) {
        return;
    }

    const customerError = validateCustomer();

    if (customerError) {
        await Swal.fire({
            icon: 'warning',
            title: 'Revisa tus datos de contacto',
            text: customerError,
            confirmButtonText: 'Aceptar',
        });

        return;
    }

    const deliveryError = validateDelivery();

    if (deliveryError) {
        await Swal.fire({
            icon: 'warning',
            title: 'Revisa los datos de entrega',
            text: deliveryError,
            confirmButtonText: 'Aceptar',
        });

        return;
    }

    processingCheckout.value = true;

    try {
        const csrfToken = document
            .querySelector('meta[name="csrf-token"]')
            ?.getAttribute('content');

        /*
         * Los clientes registrados se identifican en el backend
         * mediante su sesión autenticada.
         *
         * Los invitados envían su nombre y correo para que Laravel
         * pueda validarlos y asociarlos con el pedido.
         */
        const customerData = isGuest.value
            ? {
                  customer_name: guestName.value.trim(),
                  customer_email: guestEmail.value.trim(),
              }
            : {};

        const deliveryData = deliveryMethod.value === 'branch_pickup'
            ? {
                  ...customerData,
                  delivery_method: deliveryMethod.value,
                  branch_id: branchId.value,
              }
            : {
                  ...customerData,
                  delivery_method: deliveryMethod.value,
                  street: address.value.street.trim(),
                  exterior_number: address.value.exterior_number.trim(),
                  interior_number:
                      address.value.interior_number.trim() || null,
                  neighborhood: address.value.neighborhood.trim(),
                  postal_code: address.value.postal_code.trim(),
                  city: address.value.city.trim(),
                  state: address.value.state.trim(),
                  references: address.value.references.trim() || null,
              };

        const response = await fetch('/carrito/finalizar', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                ...(csrfToken
                    ? { 'X-CSRF-TOKEN': csrfToken }
                    : {}),
            },
            credentials: 'same-origin',
            body: JSON.stringify(deliveryData),
        });

        const data: CheckoutResponse = await response.json();

        if (!response.ok) {
            const validationErrors = data.errors
                ? Object.values(data.errors).flat()
                : [];

            const errorMessage = validationErrors.length
                ? validationErrors.join('\n')
                : data.message || 'No fue posible iniciar el pago.';

            throw new Error(errorMessage);
        }

        if (!data.checkout_url) {
            throw new Error(
                'Mercado Pago no devolvió la URL de Checkout.',
            );
        }

        window.location.href = data.checkout_url;
    } catch (error) {
        console.error('Error al iniciar checkout:', error);

        await Swal.fire({
            icon: 'error',
            title: 'No se pudo iniciar el pago',
            text: error instanceof Error
                ? error.message
                : 'Ocurrió un error inesperado.',
            confirmButtonText: 'Aceptar',
        });

        processingCheckout.value = false;
    }
};

const hasItems = computed(() => props.items.length > 0);
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

                    <h1>Tu carrito</h1>

                    <p>
                        Revisa tus productos antes de continuar con tu compra.
                    </p>
                </div>
            </section>

            <section class="cart-content">
                <div class="cart-container">
                    <div v-if="!hasItems" class="empty-cart">
                        <div class="empty-cart-icon">
                            <ShoppingBag :size="34" />
                        </div>

                        <h2>Tu carrito está vacío</h2>

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

                    <div v-else class="cart-layout">
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
                                        {{ formatPrice(item.price) }} MXN
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

                                            <span>{{ item.quantity }}</span>

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
                                    <span>Subtotal</span>
                                    <strong>
                                        {{ formatPrice(item.subtotal) }}
                                    </strong>
                                </div>
                            </article>

                            <!-- DATOS DEL COMPRADOR -->
                            <section class="customer-section">
                                <div class="customer-heading">
                                    <span class="customer-icon">
                                        <UserRound :size="21" />
                                    </span>

                                    <div>
                                        <h2>Datos del comprador</h2>
                                        <p>
                                            Utilizaremos estos datos para enviarte
                                            la confirmación de tu compra.
                                        </p>
                                    </div>
                                </div>

                                <!-- CLIENTE REGISTRADO -->
                                <div
                                    v-if="authUser"
                                    class="customer-registered"
                                >
                                    <div class="customer-detail">
                                        <UserRound :size="17" />

                                        <div>
                                            <small>Nombre</small>
                                            <strong>
                                                {{ authUser.name || 'Usuario registrado' }}
                                            </strong>
                                        </div>
                                    </div>

                                    <div class="customer-detail">
                                        <Mail :size="17" />

                                        <div>
                                            <small>Correo electrónico</small>
                                            <strong>
                                                {{ authUser.email || 'Sin correo disponible' }}
                                            </strong>
                                        </div>
                                    </div>

                                    <p class="customer-note">
                                        La compra se asociará con tu cuenta.
                                    </p>
                                </div>

                                <!-- CLIENTE INVITADO -->
                                <div v-else class="customer-form">
                                    <p class="guest-info">
                                        Puedes comprar sin crear una cuenta.
                                        Solo necesitamos estos datos para enviarte
                                        la confirmación del pedido.
                                    </p>

                                    <div class="customer-form-grid">
                                        <label class="customer-field">
                                            <span>Nombre completo *</span>

                                            <input
                                                v-model="guestName"
                                                type="text"
                                                name="customer_name"
                                                autocomplete="name"
                                                maxlength="255"
                                                placeholder="Escribe tu nombre"
                                                required
                                            />
                                        </label>

                                        <label class="customer-field">
                                            <span>Correo electrónico *</span>

                                            <input
                                                v-model="guestEmail"
                                                type="email"
                                                name="customer_email"
                                                autocomplete="email"
                                                maxlength="255"
                                                placeholder="correo@ejemplo.com"
                                                required
                                            />

                                            <small>
                                                Aquí recibirás la confirmación de tu compra.
                                            </small>
                                        </label>
                                    </div>

                                    <p class="customer-note">
                                        No necesitas registrarte para comprar.
                                    </p>
                                </div>
                            </section>

                            <!-- DATOS DE ENTREGA -->
                            <section class="delivery-section">
                                <div class="delivery-heading">
                                    <span class="delivery-eyebrow">
                                        ENTREGA DE TU PEDIDO
                                    </span>

                                    <h2>¿Cómo quieres recibir tu compra?</h2>

                                    <p>
                                        Elige si prefieres recibir tu pedido en casa
                                        o recogerlo en una de nuestras sucursales.
                                    </p>
                                </div>

                                <div class="delivery-options">
                                    <button
                                        type="button"
                                        class="delivery-option"
                                        :class="{
                                            'delivery-option-active':
                                                deliveryMethod === 'home_delivery',
                                        }"
                                        :aria-pressed="
                                            deliveryMethod === 'home_delivery'
                                        "
                                        @click="deliveryMethod = 'home_delivery'"
                                    >
                                        <span class="delivery-option-icon">
                                            <Truck :size="22" />
                                        </span>

                                        <span class="delivery-option-text">
                                            <strong>Entrega a domicilio</strong>
                                            <small>
                                                Indica la dirección de esta compra.
                                            </small>
                                        </span>

                                        <span class="delivery-radio">
                                            <span
                                                v-if="deliveryMethod === 'home_delivery'"
                                            ></span>
                                        </span>
                                    </button>

                                    <button
                                        type="button"
                                        class="delivery-option"
                                        :class="{
                                            'delivery-option-active':
                                                deliveryMethod === 'branch_pickup',
                                        }"
                                        :aria-pressed="
                                            deliveryMethod === 'branch_pickup'
                                        "
                                        @click="deliveryMethod = 'branch_pickup'"
                                    >
                                        <span class="delivery-option-icon">
                                            <Store :size="22" />
                                        </span>

                                        <span class="delivery-option-text">
                                            <strong>Recoger en sucursal</strong>
                                            <small>
                                                Selecciona dónde recoger tu pedido.
                                            </small>
                                        </span>

                                        <span class="delivery-radio">
                                            <span
                                                v-if="deliveryMethod === 'branch_pickup'"
                                            ></span>
                                        </span>
                                    </button>
                                </div>

                                <div
                                    v-if="deliveryMethod === 'home_delivery'"
                                    class="delivery-fields"
                                >
                                    <div class="delivery-fields-heading">
                                        <span class="delivery-fields-icon">
                                            <MapPin :size="19" />
                                        </span>

                                        <div>
                                            <h3>Dirección de entrega</h3>
                                            <p>
                                                Estos datos se guardarán para este pedido.
                                                No se utilizará una dirección anterior.
                                            </p>
                                        </div>
                                    </div>

                                    <div class="delivery-form-grid">
                                        <label class="delivery-field field-full">
                                            <span>Calle *</span>
                                            <input
                                                v-model="address.street"
                                                type="text"
                                                maxlength="255"
                                                autocomplete="address-line1"
                                                placeholder="Nombre de la calle"
                                                required
                                            />
                                        </label>

                                        <label class="delivery-field">
                                            <span>Número exterior *</span>
                                            <input
                                                v-model="address.exterior_number"
                                                type="text"
                                                maxlength="50"
                                                placeholder="Ej. 123"
                                                required
                                            />
                                        </label>

                                        <label class="delivery-field">
                                            <span>Número interior</span>
                                            <input
                                                v-model="address.interior_number"
                                                type="text"
                                                maxlength="50"
                                                placeholder="Ej. 4B"
                                            />
                                        </label>

                                        <label class="delivery-field field-full">
                                            <span>Colonia *</span>
                                            <input
                                                v-model="address.neighborhood"
                                                type="text"
                                                maxlength="255"
                                                autocomplete="address-level3"
                                                placeholder="Nombre de la colonia"
                                                required
                                            />
                                        </label>

                                        <label class="delivery-field">
                                            <span>Código postal *</span>
                                            <input
                                                v-model="address.postal_code"
                                                type="text"
                                                maxlength="10"
                                                inputmode="numeric"
                                                autocomplete="postal-code"
                                                placeholder="Ej. 34000"
                                                required
                                            />
                                        </label>

                                        <label class="delivery-field">
                                            <span>Ciudad o municipio *</span>
                                            <input
                                                v-model="address.city"
                                                type="text"
                                                maxlength="255"
                                                autocomplete="address-level2"
                                                placeholder="Ciudad o municipio"
                                                required
                                            />
                                        </label>

                                        <label class="delivery-field field-full">
                                            <span>Estado *</span>
                                            <input
                                                v-model="address.state"
                                                type="text"
                                                maxlength="255"
                                                autocomplete="address-level1"
                                                placeholder="Estado"
                                                required
                                            />
                                        </label>

                                        <label class="delivery-field field-full">
                                            <span>Referencias adicionales</span>
                                            <textarea
                                                v-model="address.references"
                                                rows="3"
                                                maxlength="2000"
                                                placeholder="Entre calles, color de fachada, indicaciones para localizar el domicilio..."
                                            ></textarea>

                                            <small>
                                                Opcional. Ayúdanos a localizar tu domicilio.
                                            </small>
                                        </label>
                                    </div>
                                </div>

                                <div v-else class="delivery-fields">
                                    <div class="delivery-fields-heading">
                                        <span class="delivery-fields-icon">
                                            <Store :size="19" />
                                        </span>

                                        <div>
                                            <h3>Selecciona una sucursal</h3>
                                            <p>
                                                Elige la sucursal donde deseas recoger
                                                tu pedido.
                                            </p>
                                        </div>
                                    </div>

                                    <div v-if="branches.length" class="branch-list">
                                        <label
                                            v-for="branch in branches"
                                            :key="branch.id"
                                            class="branch-option"
                                            :class="{
                                                'branch-option-active':
                                                    branchId === branch.id,
                                            }"
                                        >
                                            <input
                                                v-model.number="branchId"
                                                type="radio"
                                                name="branch_id"
                                                :value="branch.id"
                                            />

                                            <span class="branch-option-content">
                                                <strong>{{ branch.name }}</strong>

                                                <span class="branch-address">
                                                    <MapPin :size="15" />
                                                    {{ branch.address }}
                                                </span>

                                                <span
                                                    v-if="branch.phone"
                                                    class="branch-detail"
                                                >
                                                    Tel. {{ branch.phone }}
                                                </span>

                                                <span
                                                    v-if="
                                                        branch.opening_time &&
                                                        branch.closing_time
                                                    "
                                                    class="branch-detail"
                                                >
                                                    Horario:
                                                    {{ branch.opening_time }}
                                                    a
                                                    {{ branch.closing_time }}
                                                </span>
                                            </span>

                                            <span class="delivery-radio">
                                                <span
                                                    v-if="branchId === branch.id"
                                                ></span>
                                            </span>
                                        </label>
                                    </div>

                                    <div v-else class="no-branches">
                                        <Store :size="25" />

                                        <strong>
                                            No hay sucursales disponibles
                                        </strong>

                                        <p>
                                            Por el momento no hay sucursales activas
                                            para recoger pedidos. Selecciona entrega
                                            a domicilio o inténtalo más tarde.
                                        </p>

                                        <button
                                            type="button"
                                            @click="deliveryMethod = 'home_delivery'"
                                        >
                                            Elegir entrega a domicilio
                                        </button>
                                    </div>
                                </div>
                            </section>
                        </div>

                        <!-- RESUMEN -->
                        <aside class="cart-summary">
                            <span class="summary-eyebrow">RESUMEN</span>

                            <h2>Tu compra</h2>

                            <div class="summary-row">
                                <span>Productos</span>
                                <strong>{{ totalItems }}</strong>
                            </div>

                            <div class="summary-row">
                                <span>Subtotal</span>
                                <strong>
                                    {{ formatPrice(subtotal) }} MXN
                                </strong>
                            </div>

                            <div class="summary-divider"></div>

                            <div class="summary-total">
                                <span>Total</span>
                                <strong>
                                    {{ formatPrice(subtotal) }} MXN
                                </strong>
                            </div>

                            <div class="summary-delivery">
                                <span class="summary-delivery-icon">
                                    <Truck
                                        v-if="deliveryMethod === 'home_delivery'"
                                        :size="17"
                                    />
                                    <Store v-else :size="17" />
                                </span>

                                <div>
                                    <strong>
                                        {{
                                            deliveryMethod === 'home_delivery'
                                                ? 'Entrega a domicilio'
                                                : 'Recoger en sucursal'
                                        }}
                                    </strong>

                                    <small>
                                        {{
                                            deliveryMethod === 'home_delivery'
                                                ? 'Dirección capturada para este pedido'
                                                : selectedBranch?.name ||
                                                  'Selecciona una sucursal'
                                        }}
                                    </small>
                                </div>
                            </div>

                            <button
                                type="button"
                                class="checkout-button"
                                :disabled="
                                    processingCheckout ||
                                    (
                                        deliveryMethod === 'branch_pickup' &&
                                        !branches.length
                                    )
                                "
                                @click="checkout"
                            >
                                {{
                                    processingCheckout
                                        ? 'Creando pedido...'
                                        : 'Finalizar compra'
                                }}
                            </button>

                            <p class="checkout-note">
                                Al continuar, se registrará tu pedido con los
                                datos de entrega seleccionados y podrás continuar
                                con el pago en Mercado Pago.
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

.cart-hero {
    position: relative;
    overflow: hidden;
    padding: 65px 0 70px;
    background: linear-gradient(
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

.cart-content {
    padding: 55px 0 75px;
}

.cart-layout {
    display: grid;
    grid-template-columns: minmax(0, 1fr) 350px;
    gap: 28px;
    align-items: start;
}

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

.clear-cart-button:hover,
.remove-item-button:hover {
    color: #a52f3d;
}

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
    box-shadow: 0 8px 25px rgba(23, 43, 77, 0.04);
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

/* DATOS DEL COMPRADOR */

.customer-section {
    margin-top: 24px;
    padding: 24px;
    border: 1px solid var(--border);
    border-radius: 17px;
    background: #fff;
    box-shadow: 0 8px 25px rgba(23, 43, 77, 0.04);
}

.customer-heading {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    margin-bottom: 20px;
}

.customer-icon {
    flex: 0 0 auto;
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 11px;
    background: #eaf6fc;
    color: var(--blue);
}

.customer-heading h2 {
    margin: 0;
    color: var(--text);
    font-size: 19px;
    font-weight: 800;
}

.customer-heading p {
    margin: 6px 0 0;
    color: var(--muted);
    font-size: 12px;
    line-height: 1.6;
}

.customer-registered {
    display: grid;
    gap: 14px;
}

.customer-detail {
    display: flex;
    align-items: center;
    gap: 11px;
    padding: 13px;
    border: 1px solid var(--border);
    border-radius: 10px;
    background: #f8fbfe;
    color: var(--blue);
}

.customer-detail > div {
    min-width: 0;
}

.customer-detail small {
    display: block;
    margin-bottom: 4px;
    color: var(--muted);
    font-size: 10px;
}

.customer-detail strong {
    display: block;
    overflow-wrap: anywhere;
    color: var(--text);
    font-size: 12px;
}

.guest-info {
    margin: 0 0 16px;
    color: var(--muted);
    font-size: 12px;
    line-height: 1.6;
}

.customer-form-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 14px;
}

.customer-field {
    display: flex;
    flex-direction: column;
    gap: 7px;
    min-width: 0;
}

.customer-field > span {
    color: var(--text);
    font-size: 11px;
    font-weight: 800;
}

.customer-field input {
    width: 100%;
    min-width: 0;
    min-height: 43px;
    padding: 11px 12px;
    border: 1px solid #d6e2eb;
    border-radius: 9px;
    outline: none;
    background: #fff;
    color: var(--text);
    font: inherit;
    font-size: 12px;
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

.customer-field input::placeholder {
    color: #94a3b8;
}

.customer-field input:focus {
    border-color: #a9d9f2;
    box-shadow: 0 0 0 3px rgba(36, 158, 219, 0.1);
}

.customer-field small {
    color: var(--muted);
    font-size: 10px;
    line-height: 1.5;
}

.customer-note {
    margin: 14px 0 0;
    color: var(--muted);
    font-size: 11px;
    line-height: 1.6;
}

/* ENTREGA */

.delivery-section {
    margin-top: 28px;
    padding: 24px;
    border: 1px solid var(--border);
    border-radius: 17px;
    background: #fff;
    box-shadow: 0 8px 25px rgba(23, 43, 77, 0.04);
}

.delivery-heading {
    margin-bottom: 20px;
}

.delivery-eyebrow {
    display: block;
    margin-bottom: 7px;
    color: var(--blue-light);
    font-size: 9px;
    font-weight: 800;
    letter-spacing: 1px;
}

.delivery-heading h2 {
    margin: 0;
    color: var(--text);
    font-size: 23px;
    font-weight: 800;
    line-height: 1.25;
    letter-spacing: -0.5px;
}

.delivery-heading > p {
    margin: 8px 0 0;
    color: var(--muted);
    font-size: 12px;
    line-height: 1.6;
}

.delivery-options {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 12px;
}

.delivery-option {
    min-width: 0;
    display: flex;
    align-items: flex-start;
    gap: 11px;
    padding: 15px 12px;
    border: 1px solid var(--border);
    border-radius: 12px;
    background: #fff;
    text-align: left;
    cursor: pointer;
    transition: border-color 0.2s ease, background 0.2s ease;
}

.delivery-option:hover {
    border-color: #a9d9f2;
}

.delivery-option-active {
    border-color: var(--blue-light);
    background: #f2faff;
}

.delivery-option-icon,
.delivery-fields-icon {
    flex: 0 0 auto;
    width: 38px;
    height: 38px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 10px;
    background: #eaf6fc;
    color: var(--blue);
}

.delivery-option-text {
    min-width: 0;
    flex: 1;
}

.delivery-option-text strong {
    display: block;
    color: var(--text);
    font-size: 11px;
    font-weight: 800;
    line-height: 1.4;
}

.delivery-option-text small {
    display: block;
    margin-top: 5px;
    color: var(--muted);
    font-size: 10px;
    line-height: 1.5;
}

.delivery-radio {
    flex: 0 0 auto;
    width: 17px;
    height: 17px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 1px solid #cbd5e1;
    border-radius: 50%;
    background: #fff;
}

.delivery-option-active .delivery-radio,
.branch-option-active .delivery-radio {
    border-color: var(--blue);
}

.delivery-radio > span {
    width: 9px;
    height: 9px;
    border-radius: 50%;
    background: var(--blue);
}

.delivery-fields {
    margin-top: 23px;
    padding-top: 21px;
    border-top: 1px solid var(--border);
}

.delivery-fields-heading {
    display: flex;
    align-items: flex-start;
    gap: 11px;
    margin-bottom: 19px;
}

.delivery-fields-heading h3 {
    margin: 0;
    color: var(--text);
    font-size: 15px;
    font-weight: 800;
}

.delivery-fields-heading p {
    margin: 5px 0 0;
    color: var(--muted);
    font-size: 11px;
    line-height: 1.6;
}

.delivery-form-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 15px 12px;
}

.delivery-field {
    min-width: 0;
    display: flex;
    flex-direction: column;
    gap: 7px;
}

.field-full {
    grid-column: 1 / -1;
}

.delivery-field > span {
    color: var(--text);
    font-size: 11px;
    font-weight: 800;
}

.delivery-field input,
.delivery-field textarea {
    width: 100%;
    min-width: 0;
    padding: 11px 12px;
    border: 1px solid #d6e2eb;
    border-radius: 9px;
    outline: none;
    background: #fff;
    color: var(--text);
    font: inherit;
    font-size: 12px;
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

.delivery-field input {
    min-height: 42px;
}

.delivery-field textarea {
    resize: vertical;
    line-height: 1.5;
}

.delivery-field input::placeholder,
.delivery-field textarea::placeholder {
    color: #94a3b8;
}

.delivery-field input:focus,
.delivery-field textarea:focus {
    border-color: #a9d9f2;
    box-shadow: 0 0 0 3px rgba(36, 158, 219, 0.1);
}

.delivery-field > small {
    color: var(--muted);
    font-size: 10px;
    line-height: 1.5;
}

/* SUCURSALES */

.branch-list {
    display: grid;
    gap: 10px;
}

.branch-option {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 15px;
    border: 1px solid var(--border);
    border-radius: 12px;
    background: #fff;
    cursor: pointer;
    transition: border-color 0.2s ease, background 0.2s ease;
}

.branch-option:hover {
    border-color: #a9d9f2;
}

.branch-option-active {
    border-color: var(--blue-light);
    background: #f2faff;
}

.branch-option > input {
    position: absolute;
    width: 1px;
    height: 1px;
    opacity: 0;
    pointer-events: none;
}

.branch-option-content {
    min-width: 0;
    flex: 1;
}

.branch-option-content > strong {
    display: block;
    margin-bottom: 7px;
    color: var(--text);
    font-size: 13px;
    font-weight: 800;
}

.branch-address {
    display: flex;
    align-items: flex-start;
    gap: 6px;
    color: var(--muted);
    font-size: 11px;
    line-height: 1.6;
}

.branch-address :deep(svg) {
    flex: 0 0 auto;
    margin-top: 1px;
    color: var(--blue);
}

.branch-detail {
    display: block;
    margin-top: 5px;
    color: var(--muted);
    font-size: 10px;
}

.no-branches {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 25px 18px;
    border: 1px dashed #cbd5e1;
    border-radius: 12px;
    color: var(--muted);
    text-align: center;
}

.no-branches > svg {
    margin-bottom: 10px;
    color: var(--blue);
}

.no-branches > strong {
    color: var(--text);
    font-size: 13px;
}

.no-branches p {
    max-width: 350px;
    margin: 8px 0 15px;
    font-size: 11px;
    line-height: 1.6;
}

.no-branches button {
    padding: 9px 13px;
    border: 0;
    border-radius: 8px;
    background: var(--blue);
    color: #fff;
    font-size: 11px;
    font-weight: 800;
    cursor: pointer;
}

.no-branches button:hover {
    background: var(--pink);
}

/* RESUMEN */

.cart-summary {
    position: sticky;
    top: 20px;
    padding: 23px;
    border: 1px solid var(--border);
    border-radius: 17px;
    background: #fff;
    box-shadow: 0 12px 35px rgba(23, 43, 77, 0.06);
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

.summary-delivery {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    margin-top: 19px;
    padding: 12px;
    border-radius: 10px;
    background: #f6faff;
}

.summary-delivery-icon {
    flex: 0 0 auto;
    color: var(--blue);
}

.summary-delivery > div {
    min-width: 0;
}

.summary-delivery strong {
    display: block;
    color: var(--text);
    font-size: 11px;
    font-weight: 800;
}

.summary-delivery small {
    display: block;
    margin-top: 4px;
    color: var(--muted);
    font-size: 10px;
    line-height: 1.5;
    overflow-wrap: anywhere;
}

.checkout-button {
    width: 100%;
    min-height: 48px;
    margin-top: 20px;
    border: 0;
    border-radius: 11px;
    background: linear-gradient(
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
    transition: opacity 0.2s ease, transform 0.2s ease;
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

/* CARRITO VACÍO */

.empty-cart {
    max-width: 600px;
    margin: 0 auto;
    padding: 55px 30px;
    border: 1px solid var(--border);
    border-radius: 18px;
    background: #fff;
    text-align: center;
    box-shadow: 0 12px 35px rgba(23, 43, 77, 0.05);
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
    transition: background 0.2s ease, transform 0.2s ease;
}

.continue-shopping-button:hover {
    background: var(--pink);
    transform: translateY(-2px);
}

/* RESPONSIVE */

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

    .customer-section,
    .delivery-section {
        padding: 17px;
    }

    .customer-form-grid,
    .delivery-form-grid {
        grid-template-columns: 1fr;
    }

    .field-full {
        grid-column: auto;
    }

    .delivery-options {
        grid-template-columns: 1fr;
    }

    .delivery-heading h2 {
        font-size: 20px;
    }
}
</style>