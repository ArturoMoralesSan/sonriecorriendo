<script setup lang="ts">
import { BadgePercent, Search, Ticket } from 'lucide-vue-next';
import { computed, ref } from 'vue';

import { formatDiscountAmount } from '@/lib/discountAmount';

const props = defineProps<{
    slug: string;
    amount?: number | null;
}>();

const amountLabel = computed(() => formatDiscountAmount(props.amount));

type Status = 'idle' | 'loading' | 'eligible' | 'not_eligible' | 'error';

const folio = ref('');
const fullName = ref('');
const status = ref<Status>('idle');
const message = ref('');
const url = ref<string | null>(null);

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
    url.value = null;

    try {
        const response = await fetch(
            `/carreras/${encodeURIComponent(props.slug)}/descuento`,
            {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-XSRF-TOKEN': getXsrfToken(),
                },
                body: JSON.stringify(payload),
            },
        );

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

        if (data.eligible && data.url) {
            status.value = 'eligible';
            url.value = data.url;
        } else {
            status.value = 'not_eligible';
        }
    } catch {
        status.value = 'error';
        message.value = 'No se pudo verificar. Inténtalo más tarde.';
    }
}
</script>

<template>
    <div class="discount-check">
        <div class="discount-check__icon">
            <BadgePercent :size="26" />
        </div>

        <div class="discount-check__body">
            <h3>¿Ya has participado?</h3>

            <p>
                Busca con tu número de folio, tu nombre completo
                (nombres y apellidos) o ambos, tal como aparecen en tu
                ficha de inscripción.
            </p>

            <form class="discount-check__form" @submit.prevent="check">
                <input
                    v-model="folio"
                    type="text"
                    inputmode="numeric"
                    maxlength="50"
                    autocomplete="off"
                    placeholder="Número de folio"
                    aria-label="Número de folio"
                    class="discount-check__input discount-check__input--folio"
                />

                <input
                    v-model="fullName"
                    type="text"
                    maxlength="255"
                    autocomplete="name"
                    placeholder="Nombre completo"
                    aria-label="Nombre completo"
                    class="discount-check__input"
                />

                <button
                    type="submit"
                    class="discount-check__button"
                    :disabled="status === 'loading'"
                >
                    <Search :size="16" />
                    {{ status === 'loading' ? 'Buscando...' : 'Verificar' }}
                </button>
            </form>

            <div
                v-if="status === 'eligible' && url"
                class="discount-check__result discount-check__result--ok"
            >
                <strong>
                    {{
                        amountLabel
                            ? `¡Felicidades! Tu inscripción queda en ${amountLabel}.`
                            : '¡Felicidades! Tienes descuento aprobado.'
                    }}
                </strong>

                <a
                    :href="url"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="discount-check__button"
                >
                    <Ticket :size="16" />
                    Registrarme con descuento
                </a>
            </div>

            <div
                v-else-if="status === 'not_eligible'"
                class="discount-check__result"
            >
                No encontramos tus datos en la lista de participantes
                anteriores. Verifica que el folio o tu nombre completo
                sean los de tu ficha, o inscríbete con el precio normal.
            </div>

            <div
                v-else-if="status === 'error'"
                class="discount-check__result discount-check__result--error"
            >
                {{ message }}
            </div>
        </div>
    </div>
</template>

<style scoped>
.discount-check {
    display: flex;
    gap: 18px;
    align-items: flex-start;
    max-width: 720px;
    margin: 0 auto;
    padding: 24px;
    border: 1px solid rgba(36, 158, 219, 0.25);
    border-radius: 18px;
    background: #fff;
    box-shadow: 0 10px 30px rgba(15, 40, 70, 0.06);
}

.discount-check__icon {
    display: grid;
    flex-shrink: 0;
    place-items: center;
    width: 52px;
    height: 52px;
    border-radius: 14px;
    background: rgba(36, 158, 219, 0.12);
    color: #1769a8;
}

.discount-check__body {
    flex: 1;
    min-width: 0;
}

.discount-check__body h3 {
    margin: 0 0 4px;
    font-size: 18px;
}

.discount-check__body p {
    margin: 0 0 14px;
    color: #5b6573;
    font-size: 14px;
}

.discount-check__body p.discount-check__amount {
    margin-bottom: 6px;
    color: #1769a8;
    font-size: 15px;
}

.discount-check__form {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}

.discount-check__input {
    flex: 1 1 150px;
    min-width: 0;
    padding: 12px 14px;
    border: 1px solid #d6dde6;
    border-radius: 11px;
    font-size: 14px;
}

.discount-check__input--folio {
    flex: 0 1 140px;
    font-family: ui-monospace, monospace;
}

.discount-check__button {
    display: inline-flex;
    gap: 7px;
    align-items: center;
    justify-content: center;
    padding: 12px 18px;
    border: 0;
    border-radius: 11px;
    background: linear-gradient(135deg, #249edb, #1769a8);
    color: #fff;
    font-size: 13px;
    font-weight: 700;
    text-decoration: none;
    cursor: pointer;
}

.discount-check__button:disabled {
    opacity: 0.6;
    cursor: wait;
}

.discount-check__result {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    align-items: center;
    justify-content: space-between;
    margin-top: 14px;
    padding: 14px;
    border-radius: 12px;
    background: #f3f6f9;
    color: #3d4754;
    font-size: 14px;
}

.discount-check__result--ok {
    background: #eaf8ef;
    color: #1d6b3a;
}

.discount-check__result--error {
    background: #fdeeee;
    color: #a63a2f;
}

@media (max-width: 560px) {
    .discount-check {
        flex-direction: column;
        padding: 18px;
    }

    .discount-check__form > * {
        width: 100%;
    }
}
</style>
