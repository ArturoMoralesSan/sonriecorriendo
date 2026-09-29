<script setup lang="ts">
import {
    Ticket,
    ShieldCheck,
    Sparkles,
    Trophy,
} from 'lucide-vue-next';

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
</script>

<template>
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
</template>