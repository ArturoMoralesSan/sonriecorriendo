<script setup lang="ts">
import {
    ArrowRight,
    Check,
    Star,
} from 'lucide-vue-next';
</script>

<template>
    <section
        id="experiencia"
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
</template>