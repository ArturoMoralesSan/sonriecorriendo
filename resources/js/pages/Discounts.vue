<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { BadgePercent, Search, Ticket } from 'lucide-vue-next';
import { ref } from 'vue';

import LandingFooter from '@/components/landing/LandingFooter.vue';
import LandingHeader from '@/components/landing/LandingHeader.vue';

import { formatDiscountAmount } from '@/lib/discountAmount';

interface DiscountRace {
    name: string;
    slug: string;
    url: string;
    amount: number | null;
}

type Status = 'idle' | 'loading' | 'found' | 'not_found' | 'error';

const folio = ref('');
const fullName = ref('');
const status = ref<Status>('idle');
const message = ref('');
const races = ref<DiscountRace[]>([]);

function getXsrfToken(): string {
    const match = document.cookie.match(/(?:^|;\s*)XSRF-TOKEN=([^;]+)/);

    return match ? decodeURIComponent(match[1]) : '';
}

async function check() {
    const payload = {
        folio: folio.value.trim(),
        full_name: fullName.value.trim(),
    };

    if (!payload.folio && !payload.full_name) {
        status.value = 'error';
        message.value = 'Ingresa tu número de folio o tu nombre completo.';

        return;
    }

    status.value = 'loading';
    races.value = [];

    try {
        const response = await fetch('/descuentos', {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-XSRF-TOKEN': getXsrfToken(),
            },
            body: JSON.stringify(payload),
        });

        if (response.status === 429) {
            status.value = 'error';
            message.value =
                'Demasiados intentos. Espera un minuto e inténtalo de nuevo.';

            return;
        }

        const data = await response.json();

        if (response.status === 422) {
            status.value = 'error';
            const errors = data?.errors ?? {};

            message.value =
                errors.folio?.[0] ??
                errors.full_name?.[0] ??
                'Revisa los datos ingresados.';

            return;
        }

        if (!response.ok) {
            throw new Error();
        }

        races.value = data.races ?? [];
        status.value = races.value.length ? 'found' : 'not_found';
    } catch {
        status.value = 'error';
        message.value = 'No se pudo verificar. Inténtalo más tarde.';
    }
}
</script>

<template>
    <Head title="Verifica tu descuento">
        <meta
            name="description"
            content="Ingresa tu número de folio o tu nombre completo y descubre en qué carreras tienes descuento en tu inscripción."
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

        <main class="discounts-main">
            <div class="discounts-card">
                <div class="discounts-icon">
                    <BadgePercent :size="28" />
                </div>

                <h1>Verifica tu descuento</h1>

                <p class="discounts-lead">
                    Si participaste en carreras anteriores, busca con tu
                    número de folio, tu nombre completo (nombres y
                    apellidos) o ambos para ver en qué eventos tienes un
                    descuento.
                </p>

                <form
                    class="discounts-form"
                    @submit.prevent="check"
                >
                    <input
                        v-model="folio"
                        type="text"
                        inputmode="numeric"
                        maxlength="50"
                        autocomplete="off"
                        placeholder="Número de folio"
                        aria-label="Número de folio"
                        class="discounts-input discounts-input--folio"
                    />

                    <input
                        v-model="fullName"
                        type="text"
                        maxlength="255"
                        autocomplete="name"
                        placeholder="Nombre completo"
                        aria-label="Nombre completo"
                        class="discounts-input"
                    />

                    <button
                        type="submit"
                        class="discounts-button"
                        :disabled="status === 'loading'"
                    >
                        <Search :size="16" />
                        {{
                            status === 'loading'
                                ? 'Buscando...'
                                : 'Verificar'
                        }}
                    </button>
                </form>

                <div
                    v-if="status === 'found'"
                    class="discounts-result discounts-result--ok"
                >
                    <strong>
                        ¡Felicidades! Tienes descuento en
                        {{
                            races.length === 1
                                ? 'esta carrera'
                                : 'estas carreras'
                        }}:
                    </strong>

                    <ul class="discounts-list">
                        <li
                            v-for="race in races"
                            :key="race.slug"
                        >
                            <div class="discounts-race-info">
                                <Link
                                    :href="`/carreras/${race.slug}`"
                                    class="discounts-race-name"
                                >
                                    {{ race.name }}
                                </Link>

                                <span
                                    v-if="formatDiscountAmount(race.amount)"
                                    class="discounts-race-amount"
                                >
                                    Precio especial:
                                    {{ formatDiscountAmount(race.amount) }}
                                </span>
                            </div>

                            <a
                                :href="race.url"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="discounts-button"
                            >
                                <Ticket :size="16" />
                                Registrarme con descuento
                            </a>
                        </li>
                    </ul>
                </div>

                <div
                    v-else-if="status === 'not_found'"
                    class="discounts-result"
                >
                    No encontramos descuentos con esos datos en las
                    carreras con inscripción abierta. Verifica que el
                    folio o tu nombre completo sean los de tu ficha, o
                    inscríbete con el precio normal.
                </div>

                <div
                    v-else-if="status === 'error'"
                    class="discounts-result discounts-result--error"
                >
                    {{ message }}
                </div>
            </div>
        </main>

        <LandingFooter />
    </div>
</template>

<style scoped>
.landing-page {
    width: 100%;
    min-height: 100vh;
    overflow-x: hidden;
    background: #f8fafc;
    font-family: Inter, sans-serif;
}

.discounts-main {
    padding: 140px 16px 80px;
}

.discounts-card {
    max-width: 640px;
    margin: 0 auto;
    padding: 36px 32px;
    border: 1px solid rgba(36, 158, 219, 0.25);
    border-radius: 20px;
    background: #ffffff;
    box-shadow: 0 10px 30px rgba(15, 40, 70, 0.06);
    text-align: center;
}

.discounts-icon {
    display: grid;
    place-items: center;
    width: 60px;
    height: 60px;
    margin: 0 auto 16px;
    border-radius: 16px;
    background: rgba(36, 158, 219, 0.12);
    color: #1769a8;
}

.discounts-card h1 {
    margin: 0 0 8px;
    color: #0f2846;
    font-size: 26px;
    font-weight: 800;
}

.discounts-lead {
    margin: 0 0 22px;
    color: #5b6573;
    font-size: 15px;
    line-height: 1.55;
}

.discounts-form {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}

.discounts-input {
    flex: 1 1 160px;
    min-width: 0;
    padding: 13px 14px;
    border: 1px solid #d6dde6;
    border-radius: 11px;
    font-size: 15px;
}

.discounts-input--folio {
    flex: 0 1 150px;
    font-family: ui-monospace, monospace;
}

.discounts-input:focus {
    border-color: #249edb;
    outline: 0;
    box-shadow: 0 0 0 3px rgba(36, 158, 219, 0.12);
}

.discounts-button {
    display: inline-flex;
    gap: 7px;
    align-items: center;
    justify-content: center;
    padding: 13px 18px;
    border: 0;
    border-radius: 11px;
    background: linear-gradient(135deg, #249edb, #1769a8);
    color: #ffffff;
    font-size: 13px;
    font-weight: 700;
    text-decoration: none;
    white-space: nowrap;
    cursor: pointer;
}

.discounts-button:disabled {
    opacity: 0.6;
    cursor: wait;
}

.discounts-result {
    margin-top: 18px;
    padding: 16px;
    border-radius: 12px;
    background: #f3f6f9;
    color: #3d4754;
    font-size: 14px;
    text-align: left;
}

.discounts-result--ok {
    background: #eaf8ef;
    color: #1d6b3a;
}

.discounts-result--error {
    background: #fdeeee;
    color: #a63a2f;
}

.discounts-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
    margin: 12px 0 0;
    padding: 0;
    list-style: none;
}

.discounts-list li {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    align-items: center;
    justify-content: space-between;
    padding: 12px;
    border-radius: 10px;
    background: #ffffff;
}

.discounts-race-name {
    color: #0f2846;
    font-weight: 700;
    text-decoration: none;
}

.discounts-race-name:hover {
    color: #1769a8;
}

.discounts-race-info {
    display: flex;
    flex-direction: column;
    gap: 3px;
    min-width: 0;
}

.discounts-race-amount {
    color: #1d6b3a;
    font-size: 13px;
    font-weight: 700;
}

@media (max-width: 560px) {
    .discounts-main {
        padding-top: 110px;
    }

    .discounts-card {
        padding: 24px 18px;
    }

    .discounts-form > *,
    .discounts-list .discounts-button {
        width: 100%;
    }
}
</style>
