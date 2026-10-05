<script setup lang="ts">
import {
    ArrowRight,
    Zap,
} from 'lucide-vue-next';

interface Banner {
    id: number;
    name: string;
    page: string;
    title: string | null;
    description: string | null;
    image: string | null;
    mobile_image: string | null;
    button_text: string | null;
    button_url: string | null;
    is_active: boolean;
    sort_order: number;
}

const props = defineProps<{
    banners: Banner[];
}>();

const activeBanner = props.banners?.find(
    (banner) => banner.is_active
);

const getImageUrl = (
    image: string | null
): string | null => {
    if (!image) {
        return null;
    }

    if (
        image.startsWith('http://') ||
        image.startsWith('https://')
    ) {
        return image;
    }

    return `/storage/${image}`;
};

const desktopImage = activeBanner?.image
    ? getImageUrl(activeBanner.image)
    : null;

const mobileImage = activeBanner?.mobile_image
    ? getImageUrl(activeBanner.mobile_image)
    : desktopImage;

const mobileImageCss = mobileImage
    ? `url('${mobileImage}')`
    : 'none';
</script>

<template>
    <section class="landing-hero">
        <div
            class="landing-hero-image"
            :style="
                desktopImage
                    ? {
                          backgroundImage: `url('${desktopImage}')`,
                      }
                    : {
                          backgroundImage: 'none',
                      }
            "
        ></div>

        <div class="landing-hero-overlay"></div>

        <div class="landing-container landing-hero-content">
            <div class="landing-hero-copy">
                <h1 class="landing-hero-title">
                    {{
                        activeBanner?.title ||
                        'Corre, vive y'
                    }}

                    <span v-if="!activeBanner?.title">
                        Sonríe
                    </span>
                </h1>

                <p class="landing-hero-description">
                    {{
                        activeBanner?.description ||
                        'Tu próxima carrera te espera. Participa en los mejores eventos, vive la experiencia y sé parte de algo más grande.'
                    }}
                </p>

                <div
                    v-if="
                        activeBanner?.button_text &&
                        activeBanner?.button_url
                    "
                    class="landing-hero-actions"
                >
                    <a
                        :href="activeBanner.button_url"
                        class="landing-btn landing-btn-hero"
                    >
                        <Zap
                            :size="19"
                            :stroke-width="2.4"
                        />

                        {{ activeBanner.button_text }}

                        <ArrowRight
                            :size="18"
                            :stroke-width="2.2"
                        />
                    </a>
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

<style scoped>
@media (max-width: 768px) {
    .landing-hero-image {
        background-image: v-bind(mobileImageCss) !important;
    }
}
</style>