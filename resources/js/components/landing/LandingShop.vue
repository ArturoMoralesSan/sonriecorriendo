<script setup lang="ts">
import {
    ArrowRight,
    ShoppingCart,
} from 'lucide-vue-next';

const products = [
    {
        id: 1,
        name: 'Playera Sonríe Corriendo',
        price: '$450',
        image:
            'https://images.pexels.com/photos/6311387/pexels-photo-6311387.jpeg?auto=compress&cs=tinysrgb&w=1200',
    },
    {
        id: 2,
        name: 'Gorra oficial',
        price: '$280',
        image:
            'https://images.pexels.com/photos/1124465/pexels-photo-1124465.jpeg?auto=compress&cs=tinysrgb&w=1200',
    },
    {
        id: 3,
        name: 'Termo Sonríe Corriendo',
        price: '$320',
        image:
            'https://images.pexels.com/photos/1346155/pexels-photo-1346155.jpeg?auto=compress&cs=tinysrgb&w=1200',
    },
    {
        id: 4,
        name: 'Kit corredor',
        price: '$650',
        image:
            'https://images.pexels.com/photos/3763877/pexels-photo-3763877.jpeg?auto=compress&cs=tinysrgb&w=1200',
    },
];
</script>

<template>
    <section
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

                <a
                    href="#tienda"
                    class="landing-see-all"
                >
                    Ver productos

                    <ArrowRight
                        :size="17"
                        :stroke-width="2"
                    />
                </a>
            </div>

            <div class="landing-shop-grid">
                <article
                    v-for="product in products"
                    :key="product.id"
                    class="landing-product-card"
                >
                    <div class="landing-product-image-wrapper">
                        <img
                            :src="product.image"
                            :alt="product.name"
                            class="landing-product-image"
                        />
                    </div>

                    <div class="landing-product-content">
                        <h3 class="landing-product-title">
                            {{ product.name }}
                        </h3>

                        <div class="landing-product-footer">
                            <div class="landing-product-price">
                                <strong>{{ product.price }}</strong>
                                <span>MXN</span>
                            </div>

                            <a
                                href="#"
                                class="landing-product-button"
                            >
                                <ShoppingCart
                                    :size="15"
                                    :stroke-width="2"
                                />

                                Comprar
                            </a>
                        </div>
                    </div>
                </article>
            </div>
        </div>
    </section>
</template>