<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import {
    Building2,
    Mail,
    MapPin,
    Phone,
    UserRound,
} from 'lucide-vue-next';

import LandingFooter from '@/components/landing/LandingFooter.vue';
import LandingHeader from '@/components/landing/LandingHeader.vue';

defineProps<{
    club: any;
}>();

const imageUrl = (
    image: string | null | undefined,
) => {
    if (!image) {
        return '';
    }

    if (
        image.startsWith('http://') ||
        image.startsWith('https://') ||
        image.startsWith('/')
    ) {
        return image;
    }

    return `/storage/${image}`;
};
</script>

<template>
    <Head :title="club.name">
        <meta
            name="description"
            :content="
                club.description ||
                `Conoce ${club.name}, parte de la comunidad Sonríe Corriendo.`
            "
        />

        <link
            rel="preconnect"
            href="https://fonts.googleapis.com"
        />

        <link
            rel="preconnect"
            href="https://fonts.gstatic.com"
        />

        <link
            href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap"
            rel="stylesheet"
        />
    </Head>

    <div class="landing-page">
        <LandingHeader />

        <main>
            <!-- =====================================================
                 HERO
                 ===================================================== -->

            <section
                class="club-hero"
                :style="
                    club.logo
                        ? {
                              backgroundImage: `linear-gradient(90deg, rgba(18, 85, 140, 0.96), rgba(23, 105, 168, 0.78) 38%, rgba(36, 158, 219, 0.48) 65%, rgba(217, 76, 154, 0.28)), url('${imageUrl(club.logo)}')`,
                          }
                        : {}
                "
            >
                <div class="club-hero-overlay">
                    <div class="club-container">
                        <div class="club-hero-content">
                            <span class="club-eyebrow">
                                CLUB SONRÍE CORRIENDO
                            </span>

                            <h1>
                                {{ club.name }}
                            </h1>

                            <p
                                v-if="club.description"
                                class="club-hero-description"
                            >
                                {{ club.description }}
                            </p>

                            <div class="club-hero-info">
                                <div
                                    v-if="club.city"
                                    class="club-hero-info-item"
                                >
                                    <MapPin :size="20" />

                                    <div>
                                        <small>Ciudad</small>

                                        <strong>
                                            {{ club.city }}
                                        </strong>
                                    </div>
                                </div>

                                <div
                                    v-if="club.responsible"
                                    class="club-hero-info-item"
                                >
                                    <UserRound :size="20" />

                                    <div>
                                        <small>Responsable</small>

                                        <strong>
                                            {{ club.responsible }}
                                        </strong>
                                    </div>
                                </div>

                                <div
                                    v-if="club.phone"
                                    class="club-hero-info-item"
                                >
                                    <Phone :size="20" />

                                    <div>
                                        <small>Contacto</small>

                                        <strong>
                                            {{ club.phone }}
                                        </strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div
                    v-if="club.logo"
                    class="club-hero-logo"
                >
                    <img
                        :src="imageUrl(club.logo)"
                        :alt="club.name"
                    />
                </div>
            </section>

            <!-- =====================================================
                 INFORMACIÓN DEL CLUB
                 ===================================================== -->

            <section class="club-section">
                <div class="club-container">
                    <div class="club-section-heading">
                        <span class="club-section-kicker">
                            Conoce el club
                        </span>

                        <h2>
                            Una comunidad que corre contigo
                        </h2>

                        <p>
                            Conoce más sobre este club y encuentra sus
                            datos de contacto.
                        </p>
                    </div>

                    <div class="club-details-grid">
                        <!-- DESCRIPCIÓN -->

                        <div class="club-description-card">
                            <div class="club-card-icon">
                                <Building2 :size="25" />
                            </div>

                            <h3>
                                Sobre el club
                            </h3>

                            <div
                                v-if="club.description"
                                class="club-description"
                            >
                                {{ club.description }}
                            </div>

                            <p
                                v-else
                                class="club-empty"
                            >
                                Próximamente encontrarás aquí más
                                información sobre este club.
                            </p>
                        </div>
                    </div>
                </div>
            </section>


            <!-- =====================================================
                 CTA
                 ===================================================== -->

            <section class="club-cta">
                <div class="club-container">
                    <div class="club-cta-content">
                        <div>
                            <span>
                                {{ club.name }}
                            </span>

                            <h2>
                                Corre junto a la comunidad.
                            </h2>

                            <p>
                                Forma parte de la experiencia Sonríe
                                Corriendo.
                            </p>
                        </div>

                        <a
                            href="/#clubes"
                            class="club-cta-button"
                        >
                            Ver clubes
                        </a>
                    </div>
                </div>
            </section>
        </main>

        <LandingFooter />
    </div>
</template>

<style scoped>
/* =========================================================
   BASE
   ========================================================= */

.landing-page {
    width: 100%;
    min-height: 100vh;
    overflow-x: hidden;

    background: #f8fafc;
    color: #172b4d;
}

.club-container {
    width: min(1180px, calc(100% - 40px));
    margin: 0 auto;
}


/* =========================================================
   HERO
   ========================================================= */

.club-hero {
    position: relative;

    display: flex;
    align-items: stretch;

    min-height: 500px;

    overflow: hidden;

    background:
        linear-gradient(
            90deg,
            #12558cfa 0%,
            #1769a8e8 30%,
            #249edba3 54%,
            #d94c9a7a 78%,
            #f48bb052 100%
        );

    background-size: cover;
    background-position: center;
}

.club-hero::before {
    content: '';

    position: absolute;
    inset: 0;

    pointer-events: none;

    background:
        radial-gradient(
            circle at 78% 20%,
            rgba(255, 255, 255, 0.16),
            transparent 28%
        ),
        radial-gradient(
            circle at 92% 80%,
            rgba(244, 139, 176, 0.18),
            transparent 30%
        );
}

.club-hero::after {
    content: '';

    position: absolute;

    width: 480px;
    height: 480px;

    right: -180px;
    bottom: -270px;

    border-radius: 50%;

    background: rgba(244, 139, 176, 0.13);

    filter: blur(10px);

    pointer-events: none;
}

.club-hero-overlay {
    position: relative;
    z-index: 1;

    width: 100%;

    display: flex;
    align-items: center;

    background:
        linear-gradient(
            90deg,
            rgba(18, 85, 140, 0.88) 0%,
            rgba(23, 105, 168, 0.66) 42%,
            rgba(36, 158, 219, 0.28) 68%,
            rgba(217, 76, 154, 0.17) 100%
        );
}

.club-hero-content {
    max-width: 880px;

    padding: 78px 0 88px;
}

.club-eyebrow {
    display: inline-flex;
    align-items: center;
    gap: 7px;

    padding: 8px 14px;
    margin-bottom: 20px;

    border: 1px solid rgba(255, 255, 255, 0.28);
    border-radius: 999px;

    background: rgba(255, 255, 255, 0.12);

    box-shadow:
        0 8px 25px rgba(7, 35, 65, 0.1),
        inset 0 1px 0 rgba(255, 255, 255, 0.1);

    backdrop-filter: blur(12px);

    color: #fff;

    font-size: 11px;
    font-weight: 800;
    letter-spacing: 0.45px;
    text-transform: uppercase;
}

.club-hero h1 {
    max-width: 850px;

    margin: 0;

    color: #fff;

    font-size: clamp(42px, 6vw, 72px);
    line-height: 0.98;
    letter-spacing: -3px;
    font-weight: 800;

    text-wrap: balance;

    text-shadow: 0 7px 30px rgba(9, 45, 76, 0.2);
}

.club-hero-description {
    max-width: 700px;

    margin: 22px 0 0;

    color: rgba(255, 255, 255, 0.9);

    font-size: 16px;
    line-height: 1.65;
}

.club-hero-info {
    display: flex;
    flex-wrap: wrap;
    gap: 9px;

    margin-top: 28px;
}

.club-hero-info-item {
    display: flex;
    align-items: center;
    gap: 10px;

    min-width: 180px;

    padding: 12px 15px;

    border: 1px solid rgba(255, 255, 255, 0.18);
    border-radius: 13px;

    background: rgba(255, 255, 255, 0.1);

    box-shadow:
        0 9px 28px rgba(5, 39, 69, 0.08),
        inset 0 1px 0 rgba(255, 255, 255, 0.08);

    backdrop-filter: blur(12px);

    color: #fff;
}

.club-hero-info-item svg {
    flex-shrink: 0;

    color: #fff;
}

.club-hero-info-item div {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.club-hero-info-item small {
    color: rgba(255, 255, 255, 0.65);

    font-size: 9px;
    font-weight: 800;
    letter-spacing: 0.7px;
    text-transform: uppercase;
}

.club-hero-info-item strong {
    color: #fff;

    font-size: 13px;
    font-weight: 700;
}

.club-hero-logo {
    position: absolute;
    z-index: 2;

    right: 8%;
    top: 50%;

    display: flex;
    align-items: center;
    justify-content: center;

    width: 190px;
    height: 190px;

    transform: translateY(-50%);

    overflow: hidden;

    border: 8px solid rgba(255, 255, 255, 0.9);
    border-radius: 50%;

    background: #fff;

    box-shadow:
        0 18px 45px rgba(7, 44, 75, 0.25);
}

.club-hero-logo img {
    width: 100%;
    height: 100%;

    display: block;

    object-fit: contain;
}


/* =========================================================
   SUMMARY
   ========================================================= */

.club-summary {
    position: relative;
    z-index: 5;

    margin-top: -42px;
}

.club-summary-grid {
    display: grid;

    grid-template-columns: repeat(4, 1fr);

    gap: 12px;
}

.club-summary-card {
    min-height: 94px;

    display: flex;
    align-items: center;

    gap: 13px;

    padding: 17px;

    border: 1px solid #e1ebf3;
    border-radius: 16px;

    background: rgba(255, 255, 255, 0.98);

    box-shadow:
        0 15px 38px rgba(23, 43, 77, 0.08),
        0 3px 9px rgba(23, 43, 77, 0.025);

    transition:
        transform 0.2s ease,
        box-shadow 0.2s ease;
}

.club-summary-card:hover {
    transform: translateY(-3px);

    box-shadow:
        0 20px 45px rgba(23, 43, 77, 0.11);
}

.club-summary-card svg {
    flex-shrink: 0;

    color: #249edb;
}

.club-summary-card:nth-child(3) svg {
    color: #d94c9a;
}

.club-summary-card:nth-child(4) svg {
    color: #18b89a;
}

.club-summary-card div {
    display: flex;
    flex-direction: column;

    gap: 4px;

    min-width: 0;
}

.club-summary-card span {
    color: #8492a6;

    font-size: 10px;
    font-weight: 800;
    letter-spacing: 0.45px;
    text-transform: uppercase;
}

.club-summary-card strong {
    overflow: hidden;

    color: #172b4d;

    font-size: 13px;
    line-height: 1.35;

    text-overflow: ellipsis;
}


/* =========================================================
   SECTIONS
   ========================================================= */

.club-section {
    padding: 62px 0;
}

.club-section-light {
    background:
        linear-gradient(
            180deg,
            #f5f9fc 0%,
            #eef7fc 100%
        );
}

.club-section-heading {
    max-width: 760px;

    margin-bottom: 30px;
}

.club-section-kicker {
    display: block;

    margin-bottom: 7px;

    color: #249edb;

    font-size: 11px;
    font-weight: 800;
    letter-spacing: 1px;
    text-transform: uppercase;
}

.club-section-heading h2 {
    margin: 0;

    color: #172b4d;

    font-size: clamp(30px, 3.5vw, 44px);
    line-height: 1.05;
    letter-spacing: -1.7px;
    font-weight: 800;

    text-wrap: balance;
}

.club-section-heading p {
    max-width: 650px;

    margin: 11px 0 0;

    color: #718096;

    font-size: 15px;
    line-height: 1.6;
}


/* =========================================================
   DETAILS
   ========================================================= */

.club-details-grid {
    display: grid;

    grid-template-columns:
        minmax(0, 1.45fr)
        minmax(300px, 0.8fr);

    gap: 16px;
}

.club-description-card,
.club-contact-card {
    position: relative;

    padding: 25px;

    border: 1px solid #e2ebf2;
    border-radius: 18px;

    background: #fff;

    box-shadow:
        0 10px 30px rgba(23, 43, 77, 0.045);

    transition:
        border-color 0.2s ease,
        box-shadow 0.2s ease;
}

.club-description-card:hover,
.club-contact-card:hover {
    border-color: #d3e3ee;

    box-shadow:
        0 15px 35px rgba(23, 43, 77, 0.07);
}

.club-card-icon {
    width: 46px;
    height: 46px;

    display: flex;
    align-items: center;
    justify-content: center;

    margin-bottom: 17px;

    border: 1px solid #d5eaf6;
    border-radius: 13px;

    background:
        linear-gradient(
            135deg,
            #eaf6fc,
            #f8eaf3
        );

    color: #249edb;
}

.club-description-card h3,
.club-contact-card h3 {
    margin: 0 0 12px;

    color: #172b4d;

    font-size: 19px;
    font-weight: 800;
}

.club-description {
    color: #52647a;

    font-size: 14px;
    line-height: 1.75;

    white-space: pre-line;
}

.club-empty {
    margin: 0;

    color: #94a3b8;

    line-height: 1.65;
}

.club-contact-list {
    display: flex;
    flex-direction: column;

    gap: 13px;
}

.club-contact-item {
    display: flex;
    align-items: center;

    gap: 12px;

    min-width: 0;

    color: inherit;
    text-decoration: none;
}

.club-contact-icon {
    width: 36px;
    height: 36px;

    display: flex;
    align-items: center;
    justify-content: center;

    flex-shrink: 0;

    border-radius: 10px;

    background: #f5faff;

    color: #1769a8;
}

.club-contact-item > div:last-child {
    display: flex;
    flex-direction: column;

    gap: 2px;

    min-width: 0;
}

.club-contact-item span {
    color: #94a3b8;

    font-size: 10px;
    font-weight: 700;
}

.club-contact-item strong {
    overflow-wrap: anywhere;

    color: #172b4d;

    font-size: 13px;
    font-weight: 800;
}

.club-contact-link {
    transition:
        transform 0.2s ease,
        color 0.2s ease;
}

.club-contact-link:hover {
    transform: translateX(3px);
}

.club-contact-link:hover strong {
    color: #d94c9a;
}


/* =========================================================
   LOCATION
   ========================================================= */

.club-location-card {
    display: flex;
    align-items: center;

    gap: 20px;

    padding: 29px;

    border: 1px solid #d8eaf4;
    border-radius: 20px;

    background:
        linear-gradient(
            135deg,
            #eaf6fc 0%,
            #fff 55%,
            #fdf2f8 100%
        );

    box-shadow:
        0 14px 38px rgba(23, 43, 77, 0.055);
}

.club-location-icon {
    width: 58px;
    height: 58px;

    display: flex;
    align-items: center;
    justify-content: center;

    flex-shrink: 0;

    border-radius: 15px;

    background:
        linear-gradient(
            135deg,
            #249edb,
            #d94c9a
        );

    color: #fff;

    box-shadow:
        0 9px 22px rgba(217, 76, 154, 0.15);
}

.club-location-content {
    min-width: 0;
}

.club-location-content > span {
    color: #249edb;

    font-size: 10px;
    font-weight: 800;
    letter-spacing: 0.7px;

    text-transform: uppercase;
}

.club-location-content h3 {
    margin: 3px 0 4px;

    color: #172b4d;

    font-size: 22px;
    font-weight: 800;
}

.club-location-content p {
    margin: 0;

    color: #64748b;

    font-size: 14px;
    line-height: 1.6;
}


/* =========================================================
   CONTACT BANNER
   ========================================================= */

.club-contact-banner {
    position: relative;

    display: flex;
    align-items: center;

    gap: 20px;

    overflow: hidden;

    padding: 29px;

    border-radius: 20px;

    background:
        linear-gradient(
            110deg,
            #12558c 0%,
            #1769a8 45%,
            #249edb 72%,
            #d94c9a 100%
        );

    box-shadow:
        0 16px 42px rgba(23, 105, 168, 0.15);

    color: #fff;
}

.club-contact-banner::after {
    content: '';

    position: absolute;

    width: 240px;
    height: 240px;

    right: -100px;
    bottom: -150px;

    border-radius: 50%;

    background: rgba(255, 255, 255, 0.09);
}

.club-contact-banner-icon {
    position: relative;
    z-index: 1;

    width: 54px;
    height: 54px;

    display: flex;
    align-items: center;
    justify-content: center;

    flex-shrink: 0;

    border: 1px solid rgba(255, 255, 255, 0.18);
    border-radius: 15px;

    background: rgba(255, 255, 255, 0.11);

    color: #fff;
}

.club-contact-banner-content {
    position: relative;
    z-index: 1;

    flex: 1;
}

.club-contact-banner-content > span {
    color: rgba(255, 255, 255, 0.65);

    font-size: 10px;
    font-weight: 800;
    letter-spacing: 0.7px;

    text-transform: uppercase;
}

.club-contact-banner-content h2 {
    margin: 3px 0 4px;

    color: #fff;

    font-size: 24px;
    font-weight: 800;
}

.club-contact-banner-content p {
    margin: 0;

    color: rgba(255, 255, 255, 0.74);

    font-size: 13px;
}

.club-contact-actions {
    position: relative;
    z-index: 1;

    display: flex;

    gap: 8px;
}

.club-contact-button {
    display: inline-flex;
    align-items: center;
    justify-content: center;

    gap: 7px;

    padding: 12px 16px;

    border-radius: 11px;

    background: #fff;

    color: #1769a8;

    font-size: 13px;
    font-weight: 800;

    text-decoration: none;

    white-space: nowrap;

    transition:
        transform 0.2s ease,
        box-shadow 0.2s ease,
        color 0.2s ease;
}

.club-contact-button:hover {
    transform: translateY(-2px);

    box-shadow:
        0 10px 22px rgba(0, 0, 0, 0.14);

    color: #d94c9a;
}

.club-contact-button-light {
    background: rgba(255, 255, 255, 0.12);

    border: 1px solid rgba(255, 255, 255, 0.3);

    color: #fff;
}

.club-contact-button-light:hover {
    background: #fff;

    color: #d94c9a;
}


/* =========================================================
   CTA
   ========================================================= */

.club-cta {
    position: relative;

    overflow: hidden;

    padding: 62px 0;

    background:
        linear-gradient(
            90deg,
            #12558c 0%,
            #1769a8 30%,
            #249edb 54%,
            #d94c9a 78%,
            #f48bb0 100%
        );
}

.club-cta::before {
    content: '';

    position: absolute;

    width: 360px;
    height: 360px;

    right: -120px;
    top: -220px;

    border-radius: 50%;

    background: rgba(255, 255, 255, 0.09);
}

.club-cta::after {
    content: '';

    position: absolute;

    width: 250px;
    height: 250px;

    left: -150px;
    bottom: -180px;

    border-radius: 50%;

    background: rgba(255, 255, 255, 0.07);
}

.club-cta-content {
    position: relative;
    z-index: 1;

    display: flex;
    align-items: center;
    justify-content: space-between;

    gap: 25px;
}

.club-cta-content > div > span {
    color: rgba(255, 255, 255, 0.68);

    font-size: 11px;
    font-weight: 800;
    letter-spacing: 0.8px;

    text-transform: uppercase;
}

.club-cta-content h2 {
    margin: 5px 0 7px;

    color: #fff;

    font-size: clamp(30px, 3.5vw, 45px);
    line-height: 1;

    letter-spacing: -1.7px;

    font-weight: 800;
}

.club-cta-content p {
    margin: 0;

    color: rgba(255, 255, 255, 0.8);

    font-size: 14px;
}

.club-cta-button {
    display: inline-flex;
    align-items: center;
    justify-content: center;

    padding: 13px 21px;

    border: 1px solid rgba(255, 255, 255, 0.45);
    border-radius: 12px;

    background: #fff;

    box-shadow:
        0 11px 25px rgba(23, 43, 77, 0.13);

    color: #1769a8;

    font-size: 14px;
    font-weight: 800;

    text-decoration: none;

    white-space: nowrap;

    transition:
        transform 0.2s ease,
        box-shadow 0.2s ease,
        color 0.2s ease;
}

.club-cta-button:hover {
    transform: translateY(-2px);

    box-shadow:
        0 15px 30px rgba(23, 43, 77, 0.18);

    color: #d94c9a;
}


/* =========================================================
   RESPONSIVE
   ========================================================= */

@media (max-width: 1100px) {
    .club-summary-grid {
        grid-template-columns: repeat(2, 1fr);
    }

    .club-hero-logo {
        right: 5%;
    }
}

@media (max-width: 900px) {
    .club-hero {
        min-height: 480px;
    }

    .club-hero-logo {
        width: 145px;
        height: 145px;

        right: 6%;
    }

    .club-hero-content {
        max-width: 680px;
    }

    .club-details-grid {
        grid-template-columns: 1fr;
    }

    .club-contact-banner {
        align-items: flex-start;

        flex-direction: column;
    }

    .club-contact-actions {
        width: 100%;
    }
}

@media (max-width: 700px) {
    .club-hero {
        min-height: 580px;
    }

    .club-hero-logo {
        width: 120px;
        height: 120px;

        right: 50%;
        top: auto;
        bottom: 35px;

        transform: translateX(50%);

        border-width: 6px;
    }

    .club-hero-content {
        padding: 62px 0 190px;
    }

    .club-hero h1 {
        font-size: 42px;
        letter-spacing: -1.8px;
    }

    .club-hero-description {
        margin-top: 18px;

        font-size: 15px;
        line-height: 1.6;
    }

    .club-hero-info {
        flex-direction: column;

        gap: 8px;

        margin-top: 23px;
    }

    .club-hero-info-item {
        width: 100%;
        min-width: 0;

        padding: 11px 14px;
    }

    .club-summary {
        margin-top: -28px;
    }

    .club-summary-grid {
        grid-template-columns: 1fr;

        gap: 8px;
    }

    .club-summary-card {
        min-height: 80px;

        padding: 15px;
    }

    .club-section {
        padding: 48px 0;
    }

    .club-section-heading {
        margin-bottom: 24px;
    }

    .club-section-heading h2 {
        font-size: 32px;
        letter-spacing: -1.1px;
    }

    .club-section-heading p {
        font-size: 14px;
    }

    .club-description-card,
    .club-contact-card {
        padding: 21px;

        border-radius: 16px;
    }

    .club-location-card {
        align-items: flex-start;

        flex-direction: column;

        padding: 23px;
    }

    .club-contact-banner {
        padding: 23px;

        border-radius: 17px;
    }

    .club-contact-actions {
        flex-direction: column;
    }

    .club-contact-button {
        width: 100%;
    }

    .club-cta {
        padding: 48px 0;
    }

    .club-cta-content {
        align-items: flex-start;

        flex-direction: column;
    }

    .club-cta-content h2 {
        font-size: 34px;
        letter-spacing: -1.2px;
    }

    .club-cta-button {
        width: 100%;
    }
}

@media (max-width: 620px) {
    .club-container {
        width: min(100% - 28px, 1180px);
    }
}
</style>