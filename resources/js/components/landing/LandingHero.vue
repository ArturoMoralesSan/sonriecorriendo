<script setup lang="ts">
import {
    ArrowRight,
    Zap,
} from 'lucide-vue-next';
</script>

<template>
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
</template>