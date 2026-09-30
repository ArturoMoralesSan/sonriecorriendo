<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
    ArrowRight,
    ShoppingCart,
} from 'lucide-vue-next';

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
    products: Product[];
    showAllLink?: boolean;
}>();

const getImageUrl = (image: string | null): string => {
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

const formatPrice = (price: number | string): string => {
    const value = Number(price);

    if (Number.isNaN(value)) {
        return 'Consultar';
    }

    return `$${value.toLocaleString('es-MX', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 2,
    })}`;
};
</script>

<template>
    <section
        v-if="props.products.length"
        id="tienda"
        class="landing-shop-section"
    >
        <div class="landing-container">
            <div class="landing-section-header">
                <div>
                    <span class="landing-section-eyebrow">
                        SONRÍE CORRIENDO
                    </span>

                    <h2 class="landing-section-title">
                        Tienda
                    </h2>

                    <p class="landing-section-subtitle">
                        Lleva contigo la experiencia Sonríe Corriendo.
                    </p>
                </div>

                <Link
                    v-if="props.showAllLink"
                    href="/tienda"
                    class="landing-see-all"
                >
                    Ver productos

                    <ArrowRight
                        :size="17"
                        :stroke-width="2"
                    />
                </Link>
            </div>

            <div class="landing-shop-grid">
                <article
                    v-for="product in props.products"
                    :key="product.id"
                    class="landing-product-card"
                >
                    <Link
                        :href="`/productos/${product.slug}`"
                        class="landing-product-image-wrapper"
                    >
                        <img
                            :src="getImageUrl(product.image)"
                            :alt="product.name"
                            class="landing-product-image"
                        />
                    </Link>

                    <div class="landing-product-content">
                        <Link
                            :href="`/productos/${product.slug}`"
                            class="landing-product-title-link"
                        >
                            <h3 class="landing-product-title">
                                {{ product.name }}
                            </h3>
                        </Link>

                        <div class="landing-product-footer">
                            <div class="landing-product-price">
                                <strong>
                                    {{ formatPrice(product.price) }}
                                </strong>

                                <span>MXN</span>
                            </div>

                            <Link
                                :href="`/productos/${product.slug}`"
                                class="landing-product-button"
                            >
                                <ShoppingCart
                                    :size="15"
                                    :stroke-width="2"
                                />

                                Comprar
                            </Link>
                        </div>
                    </div>
                </article>
            </div>
        </div>
    </section>
</template>