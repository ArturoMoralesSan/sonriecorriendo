<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
    Menu,
    Search,
    ShoppingCart,
    X,
} from 'lucide-vue-next';
import { ref } from 'vue';

import { login, register } from '@/routes';
import admin from '@/routes/admin';

const menuOpen = ref(false);
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
                    <template v-if="$page.props.auth.user">
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

                <button
                    type="button"
                    class="landing-icon-button cart-button"
                    aria-label="Carrito"
                >
                    <ShoppingCart
                        :size="21"
                        :stroke-width="2"
                    />

                    <span class="cart-badge">2</span>
                </button>

                <template v-if="$page.props.auth.user">
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