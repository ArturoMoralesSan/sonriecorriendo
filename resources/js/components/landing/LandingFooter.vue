<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
    Instagram,
    Sparkles,
    Users,
    Youtube,
} from 'lucide-vue-next';
</script>

<template>
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
                    <a href="#rutas">Rutas</a>
                    <a href="#galeria">Galería</a>
                    <a href="#tienda">Tienda</a>
                    <a href="#patrocinadores">Patrocinadores</a>
                    <a href="#clubes">Clubes</a>
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
</template>