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