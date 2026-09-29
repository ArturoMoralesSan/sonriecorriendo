<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    ArrowRight,
    CalendarDays,
    Check,
    Instagram,
    MapPin,
    Menu,
    Search,
    ShieldCheck,
    ShoppingCart,
    Sparkles,
    Star,
    Ticket,
    Trophy,
    Users,
    X,
    Youtube,
    Zap,
} from 'lucide-vue-next';
import { ref } from 'vue';

import { login, register } from '@/routes';
import admin from '@/routes/admin';

const menuOpen = ref(false);

const events = [
    {
        id: 1,
        type: '5K',
        typeClass: 'blue',
        name: 'Carrera Sonríe Corriendo',
        date: '25 Jul 2026',
        location: 'Parque Metropolitano',
        price: '$350',
        image:
            'https://images.pexels.com/photos/2402777/pexels-photo-2402777.jpeg?auto=compress&cs=tinysrgb&w=1200',
    },
    {
        id: 2,
        type: '10K',
        typeClass: 'purple',
        name: 'Maratón Kids',
        date: '22 Ago 2026',
        location: 'Parque Metropolitano',
        price: '$250',
        image:
            'https://images.pexels.com/photos/11757805/pexels-photo-11757805.jpeg?auto=compress&cs=tinysrgb&w=1200',
    },
    {
        id: 3,
        type: '10K',
        typeClass: 'pink',
        name: 'Carrera 10K',
        date: '14 Sep 2026',
        location: 'Parque Metropolitano',
        price: '$450',
        image:
            'https://images.pexels.com/photos/3768916/pexels-photo-3768916.jpeg?auto=compress&cs=tinysrgb&w=1200',
    },
    {
        id: 4,
        type: 'Kids',
        typeClass: 'green',
        name: 'Carrera Familiar',
        date: '20 Oct 2026',
        location: 'Parque Guadiana',
        price: '$350',
        image:
            'https://images.pexels.com/photos/1462399/pexels-photo-1462399.jpeg?auto=compress&cs=tinysrgb&w=1200',
    },
];

const benefits = [
    {
        title: 'Eventos para todos',
        description:
            'Carreras, maratones y más, para todas las edades y niveles.',
        icon: Trophy,
        className: 'benefit-blue',
    },
    {
        title: 'Seguro y confiable',
        description:
            'Tu inscripción y tus datos siempre estarán protegidos.',
        icon: ShieldCheck,
        className: 'benefit-purple',
    },
    {
        title: 'Compra fácil y segura',
        description:
            'Inscríbete en minutos con múltiples métodos de pago.',
        icon: Ticket,
        className: 'benefit-pink',
    },
    {
        title: 'Vive la experiencia',
        description:
            'Medallas, kits y una comunidad que te impulsa.',
        icon: Sparkles,
        className: 'benefit-green',
    },
];

const clubs = [
    {
        id: 1,
        name: 'Club de Corredores',
        location: 'Durango, Dgo.',
        description: 'Corre, comparte y disfruta cada kilómetro.',
        image:
            'https://images.pexels.com/photos/2402777/pexels-photo-2402777.jpeg?auto=compress&cs=tinysrgb&w=1200',
    },
    {
        id: 2,
        name: 'Running Team',
        location: 'Durango, Dgo.',
        description: 'Una comunidad para disfrutar el running.',
        image:
            'https://images.pexels.com/photos/3768916/pexels-photo-3768916.jpeg?auto=compress&cs=tinysrgb&w=1200',
    },
    {
        id: 3,
        name: 'Comunidad Runner',
        location: 'Durango, Dgo.',
        description: 'Entrena, participa y vive nuevas experiencias.',
        image:
            'https://images.pexels.com/photos/1462399/pexels-photo-1462399.jpeg?auto=compress&cs=tinysrgb&w=1200',
    },
    {
        id: 4,
        name: 'Club de Atletismo',
        location: 'Durango, Dgo.',
        description: 'Metas, kilómetros y experiencias compartidas.',
        image:
            'https://images.pexels.com/photos/11757805/pexels-photo-11757805.jpeg?auto=compress&cs=tinysrgb&w=1200',
    },
];

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
    <Head title="Sonríe Corriendo">
        <meta
            name="description"
            content="Encuentra carreras, inscríbete a eventos y vive la experiencia Sonríe Corriendo."
        />

        <link
            rel="preconnect"
            href="https://fonts.googleapis.com"
        />

        <link
            rel="preconnect"
            href="https://fonts.gstatic.com"
            crossorigin
        />

        <link
            href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap"
            rel="stylesheet"
        />
    </Head>

    <div class="landing-page">
        <!-- =========================================================
             HEADER
             ========================================================= -->
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
                        href="#clubes"
                        class="landing-nav-link"
                        @click="menuOpen = false"
                    >
                        Clubes
                    </a>

                    <a
                        href="#tienda"
                        class="landing-nav-link"
                        @click="menuOpen = false"
                    >
                        Tienda
                    </a>

                    <a
                        href="#como-funciona"
                        class="landing-nav-link"
                        @click="menuOpen = false"
                    >
                        Cómo funciona
                    </a>

                    <a
                        href="#nosotros"
                        class="landing-nav-link"
                        @click="menuOpen = false"
                    >
                        Nosotros
                    </a>

                    <a
                        href="#contacto"
                        class="landing-nav-link"
                        @click="menuOpen = false"
                    >
                        Contacto
                    </a>

                    <!-- =================================================
                         AUTENTICACIÓN MÓVIL
                         ================================================= -->
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

                <!-- =====================================================
                     ACCIONES DESKTOP
                     ===================================================== -->
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

                <!-- =====================================================
                     BOTÓN MENÚ MÓVIL
                     ===================================================== -->
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

        <!-- =========================================================
             HERO
             ========================================================= -->
        <main>
            <section class="landing-hero">
                <div class="landing-hero-image"></div>

                <div class="landing-hero-overlay"></div>

                <div class="landing-container landing-hero-content">
                    <div class="landing-hero-copy">
                        <h1 class="landing-hero-title">
                            Corre, vive y
                            <span>Sonríe</span>
                        </h1>

                        <p class="landing-hero-description">
                            Tu próxima carrera te espera. Participa en los
                            mejores eventos, vive la experiencia y sé parte de
                            algo más grande.
                        </p>

                        <div class="landing-hero-actions">
                            <a
                                href="#eventos"
                                class="landing-btn landing-btn-hero"
                            >
                                <Zap
                                    :size="19"
                                    :stroke-width="2.4"
                                />

                                Explorar eventos

                                <ArrowRight
                                    :size="18"
                                    :stroke-width="2.2"
                                />
                            </a>
                        </div>

                        <div class="landing-hero-meta">
                            <div class="hero-meta-item">
                                <strong>+20</strong>
                                <span>eventos</span>
                            </div>

                            <div class="hero-meta-divider"></div>

                            <div class="hero-meta-item">
                                <strong>+5K</strong>
                                <span>corredores</span>
                            </div>

                            <div class="hero-meta-divider"></div>

                            <div class="hero-meta-item">
                                <strong>100%</strong>
                                <span>experiencia</span>
                            </div>
                        </div>
                    </div>

                    <div class="landing-hero-message">
                        <p>
                            Sonríe Corriendo <strong>IMAX</strong>
                        </p>
                    </div>
                </div>
            </section>

            <!-- =====================================================
                 BENEFICIOS
                 ===================================================== -->
            <section
                id="como-funciona"
                class="landing-benefits-section"
            >
                <div class="landing-container">
                    <div class="landing-benefits-card">
                        <div
                            v-for="benefit in benefits"
                            :key="benefit.title"
                            class="landing-benefit"
                        >
                            <div
                                class="landing-benefit-icon"
                                :class="benefit.className"
                            >
                                <component
                                    :is="benefit.icon"
                                    :size="29"
                                    :stroke-width="1.8"
                                />
                            </div>

                            <div class="landing-benefit-content">
                                <h3>{{ benefit.title }}</h3>

                                <p>{{ benefit.description }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- =====================================================
                 EVENTOS
                 ===================================================== -->
            <section
                id="eventos"
                class="landing-events-section"
            >
                <div class="landing-container">
                    <div class="landing-section-header">
                        <div>
                            <span class="landing-section-eyebrow">
                                SONRÍE CORRIENDO
                            </span>

                            <h2 class="landing-section-title">
                                Eventos
                            </h2>

                            <p class="landing-section-subtitle">
                                Elige tu reto y sé parte de la experiencia.
                            </p>
                        </div>

                        <a
                            href="#eventos"
                            class="landing-see-all"
                        >
                            Ver todos los eventos

                            <ArrowRight
                                :size="17"
                                :stroke-width="2"
                            />
                        </a>
                    </div>

                    <div class="landing-events-grid">
                        <article
                            v-for="event in events"
                            :key="event.id"
                            class="landing-event-card"
                        >
                            <div class="landing-event-image-wrapper">
                                <img
                                    :src="event.image"
                                    :alt="event.name"
                                    class="landing-event-image"
                                />

                                <span
                                    class="landing-event-badge"
                                    :class="`badge-${event.typeClass}`"
                                >
                                    {{ event.type }}
                                </span>
                            </div>

                            <div class="landing-event-body">
                                <h3 class="landing-event-title">
                                    {{ event.name }}
                                </h3>

                                <div class="landing-event-info">
                                    <div class="landing-event-info-row">
                                        <CalendarDays
                                            :size="16"
                                            :stroke-width="1.9"
                                        />

                                        <span>{{ event.date }}</span>
                                    </div>

                                    <div class="landing-event-info-row">
                                        <MapPin
                                            :size="16"
                                            :stroke-width="1.9"
                                        />

                                        <span>{{ event.location }}</span>
                                    </div>
                                </div>

                                <div class="landing-event-footer">
                                    <div class="landing-event-price">
                                        <strong>{{ event.price }}</strong>
                                        <span>MXN</span>
                                    </div>

                                    <a
                                        href="#"
                                        class="landing-event-button"
                                    >
                                        Inscribirme

                                        <ArrowRight
                                            :size="16"
                                            :stroke-width="2.2"
                                        />
                                    </a>
                                </div>
                            </div>
                        </article>
                    </div>
                </div>
            </section>

            <!-- =====================================================
                 CLUBES
                 ===================================================== -->
            <section
                id="clubes"
                class="landing-clubs-section"
            >
                <div class="landing-container">
                    <div class="landing-section-header">
                        <div>
                            <span class="landing-section-eyebrow">
                                SONRÍE CORRIENDO
                            </span>

                            <h2 class="landing-section-title">
                                Clubes
                            </h2>

                            <p class="landing-section-subtitle">
                                Conoce los clubes y comunidades que forman
                                parte de la experiencia.
                            </p>
                        </div>

                        <a
                            href="#clubes"
                            class="landing-see-all"
                        >
                            Ver todos los clubes

                            <ArrowRight
                                :size="17"
                                :stroke-width="2"
                            />
                        </a>
                    </div>

                    <div class="landing-clubs-grid">
                        <article
                            v-for="club in clubs"
                            :key="club.id"
                            class="landing-club-card"
                        >
                            <div class="landing-club-image-wrapper">
                                <img
                                    :src="club.image"
                                    :alt="club.name"
                                    class="landing-club-image"
                                />
                            </div>

                            <div class="landing-club-content">
                                <h3 class="landing-club-title">
                                    {{ club.name }}
                                </h3>

                                <div class="landing-club-location">
                                    <MapPin
                                        :size="15"
                                        :stroke-width="1.9"
                                    />

                                    <span>{{ club.location }}</span>
                                </div>

                                <p class="landing-club-description">
                                    {{ club.description }}
                                </p>

                                <a
                                    href="#"
                                    class="landing-club-button"
                                >
                                    Ver club

                                    <ArrowRight
                                        :size="15"
                                        :stroke-width="2.2"
                                    />
                                </a>
                            </div>
                        </article>
                    </div>
                </div>
            </section>

            <!-- =====================================================
                 TIENDA
                 ===================================================== -->
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

            <!-- =====================================================
                 NOSOTROS / EXPERIENCIA
                 ===================================================== -->
            <section
                id="nosotros"
                class="landing-experience-section"
            >
                <div class="landing-container landing-experience-grid">
                    <div class="landing-experience-content">
                        <span class="landing-section-eyebrow">
                            MÁS QUE UNA CARRERA
                        </span>

                        <h2>
                            Corre por el reto.
                            <span>Quédate por la experiencia.</span>
                        </h2>

                        <p>
                            En Sonríe Corriendo creemos que cada kilómetro
                            cuenta. Por eso creamos experiencias deportivas
                            donde correr, compartir y disfrutar forman parte
                            del mismo recorrido.
                        </p>

                        <div class="landing-experience-points">
                            <div class="experience-point">
                                <div class="experience-point-icon">
                                    <Check :size="17" />
                                </div>

                                <span>
                                    Eventos para diferentes niveles
                                </span>
                            </div>

                            <div class="experience-point">
                                <div class="experience-point-icon">
                                    <Check :size="17" />
                                </div>

                                <span>
                                    Inscripción rápida y segura
                                </span>
                            </div>

                            <div class="experience-point">
                                <div class="experience-point-icon">
                                    <Check :size="17" />
                                </div>

                                <span>
                                    Comunidad de corredores
                                </span>
                            </div>
                        </div>

                        <a
                            href="#contacto"
                            class="landing-btn landing-btn-gradient landing-experience-button"
                        >
                            Conoce más

                            <ArrowRight :size="17" />
                        </a>
                    </div>

                    <div class="landing-experience-visual">
                        <div class="experience-image-main">
                            <img
                                src="https://images.pexels.com/photos/3768916/pexels-photo-3768916.jpeg?auto=compress&cs=tinysrgb&w=1400"
                                alt="Corredores durante una carrera"
                            />
                        </div>

                        <div class="experience-floating-card">
                            <div class="experience-floating-icon">
                                <Star
                                    :size="22"
                                    fill="currentColor"
                                />
                            </div>

                            <div>
                                <strong>Vive la experiencia</strong>

                                <span>
                                    Cada carrera tiene una historia.
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- =====================================================
                 CTA
                 ===================================================== -->
            <section
                id="contacto"
                class="landing-cta-section"
            >
                <div class="landing-container landing-cta-inner">
                    <div class="landing-cta-icon">
                        <Zap
                            :size="31"
                            :stroke-width="2"
                        />
                    </div>

                    <div class="landing-cta-content">
                        <h2>¿Listo para correr?</h2>

                        <p>
                            Únete a nuestra comunidad y no te pierdas
                            ningún evento.
                        </p>
                    </div>

                    <Link
                        v-if="!$page.props.auth.user"
                        :href="register()"
                        class="landing-btn landing-btn-gradient landing-cta-button"
                    >
                        Crear cuenta gratuita

                        <ArrowRight :size="17" />
                    </Link>

                    <a
                        v-else
                        href="#eventos"
                        class="landing-btn landing-btn-gradient landing-cta-button"
                    >
                        Explorar eventos

                        <ArrowRight :size="17" />
                    </a>

                    <div class="landing-cta-message">
                        <span>Sonrie corriendo,</span>
                        <strong>IMAX.</strong>
                    </div>
                </div>
            </section>
        </main>

        <!-- =========================================================
             FOOTER
             ========================================================= -->
        <footer class="landing-footer">
            <div class="landing-container">
                <div class="landing-footer-main">
                    <Link
                        href="/"
                        class="landing-logo landing-logo-footer"
                    >
                        <img
                            src="/images/sonrie-corriendo.png"
                            alt="Sonríe Corriendo"
                            class="landing-logo-image landing-logo-image-footer"
                        />
                    </Link>

                    <nav class="landing-footer-nav">
                        <a href="/">Inicio</a>
                        <a href="#eventos">Eventos</a>
                        <a href="#clubes">Clubes</a>
                        <a href="#tienda">Tienda</a>
                        <a href="#como-funciona">Cómo funciona</a>
                        <a href="#nosotros">Nosotros</a>
                        <a href="#contacto">Contacto</a>
                    </nav>

                    <div class="landing-socials">
                        <a
                            href="#"
                            aria-label="Facebook"
                            class="landing-social"
                        >
                            <Users :size="17" />
                        </a>

                        <a
                            href="#"
                            aria-label="Instagram"
                            class="landing-social"
                        >
                            <Instagram :size="17" />
                        </a>

                        <a
                            href="#"
                            aria-label="YouTube"
                            class="landing-social"
                        >
                            <Youtube :size="17" />
                        </a>

                        <a
                            href="#"
                            aria-label="TikTok"
                            class="landing-social"
                        >
                            <Sparkles :size="17" />
                        </a>
                    </div>
                </div>

                <div class="landing-footer-bottom">
                    <p>
                        © 2026 Sonríe Corriendo. Todos los derechos
                        reservados.
                    </p>

                    <div class="landing-footer-links">
                        <a href="#">Aviso de privacidad</a>
                        <a href="#">Términos y condiciones</a>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</template>