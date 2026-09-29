<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
    ArrowRight,
    Zap,
} from 'lucide-vue-next';

import { register } from '@/routes';
</script>

<template>
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
</template>