<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import {
    ArrowLeft,
    Building2,
    Check,
    CheckCircle2,
    DollarSign,
    Plus,
    Trash2,
    X,
} from 'lucide-vue-next';
import Swal from 'sweetalert2';
import { computed, ref } from 'vue';

import admin from '@/routes/admin';

interface Sponsor {
    id: number;
    name: string;
}

interface Race {
    id: number;
    name: string;
    event_date: string | null;
    start_time: string | null;
    end_time: string | null;
    location: string | null;
}

interface Payment {
    id: number;
    amount: number;
    paid_at: string | null;
    payment_method: string | null;
    reference: string | null;
    notes: string | null;
}

interface RaceSponsor {
    id: number;
    race: Race;
    type: string;
    amount: number;
    paid: number;
    remaining: number;
    benefits: string | null;
    is_active: boolean;
    payments: Payment[];
}

interface PageProps {
    errors: Record<string, string>;
}

const page = usePage<PageProps>();

const props = defineProps<{
    sponsor: Sponsor;
    raceSponsor: RaceSponsor;
}>();

const showPaymentForm = ref(false);

const paymentForm = ref({
    amount: '',
    paid_at: new Date()
        .toISOString()
        .substring(0, 10),
    payment_method: '',
    reference: '',
    notes: '',
});

const localErrors = ref<Record<string, string>>({});

const errors = computed(() => {
    return {
        ...page.props.errors,
        ...localErrors.value,
    };
});

const today = computed(() => {
    return new Date()
        .toISOString()
        .substring(0, 10);
});

const goBack = (): void => {
    window.history.back();
};

const formatMoney = (
    value: number,
): string => {
    return new Intl.NumberFormat(
        'es-MX',
        {
            style: 'currency',
            currency: 'MXN',
        },
    ).format(value);
};

const formatDate = (
    value: string | null,
): string => {
    if (!value) {
        return 'Sin fecha';
    }

    try {
        const normalizedValue = value.includes('T')
            ? value
            : `${value}T00:00:00`;

        const date = new Date(
            normalizedValue,
        );

        if (Number.isNaN(date.getTime())) {
            return 'Sin fecha';
        }

        return new Intl.DateTimeFormat(
            'es-MX',
            {
                dateStyle: 'medium',
            },
        ).format(date);
    } catch {
        return 'Sin fecha';
    }
};

const formatTime = (
    value: string | null,
): string => {
    if (!value) {
        return '';
    }

    return value.substring(0, 5);
};

const typeLabel = computed(() => {
    const labels: Record<string, string> = {
        sponsor: 'Patrocinador',
        principal: 'Principal',
        official: 'Oficial',
        gold: 'Oro',
        silver: 'Plata',
        bronze: 'Bronce',
    };

    return (
        labels[props.raceSponsor.type] ??
        props.raceSponsor.type
    );
});

const progress = computed(() => {
    if (props.raceSponsor.amount <= 0) {
        return 0;
    }

    return Math.min(
        100,
        (
            props.raceSponsor.paid /
            props.raceSponsor.amount
        ) * 100,
    );
});

const paymentAmount = computed(() => {
    const amount = Number(
        paymentForm.value.amount,
    );

    if (
        !Number.isFinite(amount) ||
        amount <= 0
    ) {
        return 0;
    }

    return amount;
});

const amountExceedsRemaining = computed(() => {
    return (
        paymentAmount.value >
        props.raceSponsor.remaining
    );
});

const paymentDateIsFuture = computed(() => {
    if (!paymentForm.value.paid_at) {
        return false;
    }

    return (
        paymentForm.value.paid_at >
        today.value
    );
});

const hasPaymentFormErrors = computed(() => {
    return Object.keys(errors.value).length > 0;
});

const clearLocalError = (
    field: string,
): void => {
    if (localErrors.value[field]) {
        const nextErrors = {
            ...localErrors.value,
        };

        delete nextErrors[field];

        localErrors.value = nextErrors;
    }
};

const validateAmount = (): boolean => {
    clearLocalError('amount');

    if (!paymentForm.value.amount) {
        localErrors.value.amount =
            'El monto del pago es obligatorio.';

        return false;
    }

    const amount = Number(
        paymentForm.value.amount,
    );

    if (
        !Number.isFinite(amount) ||
        amount <= 0
    ) {
        localErrors.value.amount =
            'El monto del pago debe ser mayor a cero.';

        return false;
    }

    if (
        amount >
        props.raceSponsor.remaining
    ) {
        localErrors.value.amount =
            `El pago no puede ser mayor al saldo pendiente de ${formatMoney(
                props.raceSponsor.remaining,
            )}.`;

        return false;
    }

    return true;
};

const validatePaidAt = (): boolean => {
    clearLocalError('paid_at');

    if (!paymentForm.value.paid_at) {
        localErrors.value.paid_at =
            'La fecha del pago es obligatoria.';

        return false;
    }

    if (
        paymentForm.value.paid_at >
        today.value
    ) {
        localErrors.value.paid_at =
            'La fecha del pago no puede ser futura.';

        return false;
    }

    return true;
};

const handleAmountInput = (): void => {
    clearLocalError('amount');

    if (!paymentForm.value.amount) {
        return;
    }

    const amount = Number(
        paymentForm.value.amount,
    );

    if (
        Number.isFinite(amount) &&
        amount >
            props.raceSponsor.remaining
    ) {
        localErrors.value.amount =
            `El monto máximo permitido es ${formatMoney(
                props.raceSponsor.remaining,
            )}.`;
    }
};

const handlePaidAtInput = (): void => {
    clearLocalError('paid_at');

    if (
        paymentForm.value.paid_at &&
        paymentForm.value.paid_at >
            today.value
    ) {
        localErrors.value.paid_at =
            'La fecha del pago no puede ser futura.';
    }
};

const resetPaymentForm = (): void => {
    paymentForm.value = {
        amount: '',
        paid_at: new Date()
            .toISOString()
            .substring(0, 10),
        payment_method: '',
        reference: '',
        notes: '',
    };

    localErrors.value = {};
};

const openPaymentForm = (): void => {
    resetPaymentForm();
    showPaymentForm.value = true;
};

const closePaymentForm = (): void => {
    showPaymentForm.value = false;
    resetPaymentForm();
};

const submitPayment = (): void => {
    localErrors.value = {};

    const amountValid =
        validateAmount();

    const dateValid =
        validatePaidAt();

    if (
        !amountValid ||
        !dateValid
    ) {
        return;
    }

    const formData = new FormData();

    formData.append(
        'amount',
        paymentForm.value.amount,
    );

    formData.append(
        'paid_at',
        paymentForm.value.paid_at,
    );

    formData.append(
        'payment_method',
        paymentForm.value.payment_method,
    );

    formData.append(
        'reference',
        paymentForm.value.reference,
    );

    formData.append(
        'notes',
        paymentForm.value.notes,
    );

    router.post(
        `/admin/race-sponsors/${props.raceSponsor.id}/payments`,
        formData,
        {
            forceFormData: true,
            preserveScroll: true,

            onSuccess: () => {
                showPaymentForm.value = false;

                resetPaymentForm();
            },
        },
    );
};

const deletePayment = async (
    payment: Payment,
): Promise<void> => {
    const result = await Swal.fire({
        title: '¿Eliminar pago?',
        text: `Se eliminará el pago de ${formatMoney(payment.amount)}.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        reverseButtons: true,
        focusCancel: true,
    });

    if (!result.isConfirmed) {
        return;
    }

    router.delete(
        `/admin/race-sponsors/${props.raceSponsor.id}/payments/${payment.id}`,
        {
            preserveScroll: true,

            onSuccess: () => {
                Swal.fire({
                    title: 'Eliminado',
                    text: 'El pago se eliminó correctamente.',
                    icon: 'success',
                    timer: 1800,
                    showConfirmButton: false,
                });
            },
        },
    );
};

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Panel',
                href: admin.dashboard(),
            },
            {
                title: 'Patrocinadores',
                href: admin.sponsors.index(),
            },
            {
                title: 'Detalle del patrocinio',
            },
        ],
    },
});
</script>

<template>
    <Head title="Detalle del patrocinio" />

    <div class="admin-page">
        <header class="admin-page-header">
            <div>
                <span class="admin-page-eyebrow">
                    Patrocinio
                </span>

                <h1 class="admin-page-title">
                    {{ sponsor.name }}
                </h1>

                <p class="admin-page-subtitle">
                    {{ raceSponsor.race.name }}
                </p>
            </div>

            <div class="admin-page-header-actions">
                <button
                    type="button"
                    class="admin-btn admin-btn-secondary"
                    @click="goBack"
                >
                    <ArrowLeft
                        :size="16"
                        :stroke-width="2"
                    />

                    <span>Volver</span>
                </button>
            </div>
        </header>

        <section class="sponsorship-card">
            <div class="sponsorship-top">
                <div class="sponsorship-identity">
                    <div class="sponsorship-icon">
                        <Building2
                            :size="21"
                            :stroke-width="2"
                        />
                    </div>

                    <div>
                        <h2>
                            {{ raceSponsor.race.name }}
                        </h2>

                        <div class="sponsorship-meta">
                            <span>
                                {{ typeLabel }}
                            </span>

                            <span>
                                {{
                                    formatDate(
                                        raceSponsor.race.event_date,
                                    )
                                }}
                            </span>

                            <span
                                v-if="
                                    raceSponsor.race.start_time
                                "
                            >
                                {{
                                    formatTime(
                                        raceSponsor.race.start_time,
                                    )
                                }}
                            </span>

                            <span
                                v-if="
                                    raceSponsor.race.location
                                "
                            >
                                {{ raceSponsor.race.location }}
                            </span>
                        </div>
                    </div>
                </div>

                <span
                    class="status-badge"
                    :class="{
                        active: raceSponsor.is_active,
                        inactive: !raceSponsor.is_active,
                    }"
                >
                    {{
                        raceSponsor.is_active
                            ? 'Activo'
                            : 'Inactivo'
                    }}
                </span>
            </div>

            <div class="financial-grid">
                <div class="financial-item">
                    <span class="financial-label">
                        Monto acordado
                    </span>

                    <strong>
                        {{
                            formatMoney(
                                raceSponsor.amount,
                            )
                        }}
                    </strong>
                </div>

                <div class="financial-item">
                    <span class="financial-label">
                        Total pagado
                    </span>

                    <strong>
                        {{
                            formatMoney(
                                raceSponsor.paid,
                            )
                        }}
                    </strong>
                </div>

                <div class="financial-item">
                    <span class="financial-label">
                        Saldo pendiente
                    </span>

                    <strong>
                        {{
                            formatMoney(
                                raceSponsor.remaining,
                            )
                        }}
                    </strong>
                </div>
            </div>

            <div class="progress-wrapper">
                <div class="progress-header">
                    <span>
                        Avance del patrocinio
                    </span>

                    <strong>
                        {{ progress.toFixed(0) }}%
                    </strong>
                </div>

                <div class="progress-track">
                    <div
                        class="progress-bar"
                        :style="{
                            width: `${progress}%`,
                        }"
                    ></div>
                </div>
            </div>

            <div
                v-if="raceSponsor.benefits"
                class="benefits-box"
            >
                <span class="benefits-title">
                    Beneficios / acuerdo
                </span>

                <p>
                    {{ raceSponsor.benefits }}
                </p>
            </div>
        </section>

        <section class="payments-card">
            <div class="payments-header">
                <div>
                    <span class="admin-page-eyebrow">
                        Control financiero
                    </span>

                    <h2 class="payments-title">
                        Pagos
                    </h2>

                    <p class="payments-description">
                        Registra y consulta todos los pagos realizados
                        para este patrocinio.
                    </p>
                </div>

                <button
                    type="button"
                    class="admin-btn admin-btn-primary"
                    :disabled="
                        raceSponsor.remaining <= 0
                    "
                    @click="openPaymentForm"
                >
                    <Plus
                        :size="16"
                        :stroke-width="2"
                    />

                    <span>
                        Registrar pago
                    </span>
                </button>
            </div>

            <div
                v-if="raceSponsor.payments.length"
                class="payments-list"
            >
                <div
                    v-for="payment in raceSponsor.payments"
                    :key="payment.id"
                    class="payment-item"
                >
                    <div class="payment-icon">
                        <DollarSign
                            :size="17"
                            :stroke-width="2"
                        />
                    </div>

                    <div class="payment-info">
                        <div class="payment-main">
                            <strong>
                                {{
                                    formatMoney(
                                        payment.amount,
                                    )
                                }}
                            </strong>

                            <span>
                                {{
                                    formatDate(
                                        payment.paid_at,
                                    )
                                }}
                            </span>
                        </div>

                        <div class="payment-details">
                            <span
                                v-if="
                                    payment.payment_method
                                "
                            >
                                {{
                                    payment.payment_method
                                }}
                            </span>

                            <span
                                v-if="
                                    payment.reference
                                "
                            >
                                Ref.
                                {{ payment.reference }}
                            </span>
                        </div>

                        <p
                            v-if="payment.notes"
                            class="payment-notes"
                        >
                            {{ payment.notes }}
                        </p>
                    </div>

                    <div class="payment-actions">
                        <button
                            type="button"
                            class="action-btn action-btn-delete"
                            @click="
                                deletePayment(payment)
                            "
                        >
                            <Trash2
                                :size="14"
                                :stroke-width="2"
                            />

                            <span>Eliminar</span>
                        </button>
                    </div>
                </div>
            </div>

            <div
                v-else
                class="payments-empty"
            >
                <div class="empty-state-icon">
                    <DollarSign
                        :size="25"
                        :stroke-width="1.6"
                    />
                </div>

                <strong>
                    No hay pagos registrados
                </strong>

                <span>
                    Registra el primer pago de este patrocinio.
                </span>
            </div>
        </section>
    </div>

    <Teleport to="body">
        <div
            v-if="showPaymentForm"
            class="payment-modal-overlay"
            @click.self="closePaymentForm"
        >
            <div
                class="payment-modal"
                role="dialog"
                aria-modal="true"
                aria-labelledby="payment-modal-title"
            >
                <div class="payment-modal-header">
                    <div>
                        <span class="admin-page-eyebrow">
                            Control financiero
                        </span>

                        <h2 id="payment-modal-title">
                            Registrar pago
                        </h2>

                        <p>
                            Registra un nuevo pago para este patrocinio.
                        </p>
                    </div>

                    <button
                        type="button"
                        class="payment-modal-close"
                        aria-label="Cerrar"
                        @click="closePaymentForm"
                    >
                        <X
                            :size="19"
                            :stroke-width="2"
                        />
                    </button>
                </div>

                <form
                    class="payment-form"
                    @submit.prevent="submitPayment"
                >
                    <div
                        v-if="hasPaymentFormErrors"
                        class="payment-error-summary"
                    >
                        <strong>
                            Revisa los datos del pago
                        </strong>

                        <span>
                            Hay campos que requieren atención antes de guardar.
                        </span>
                    </div>

                    <div class="payment-form-grid">
                        <div class="admin-form-group">
                            <label
                                for="payment_amount"
                                class="admin-form-label"
                            >
                                Monto
                            </label>

                            <input
                                id="payment_amount"
                                v-model="paymentForm.amount"
                                type="number"
                                min="0.01"
                                :max="raceSponsor.remaining"
                                step="0.01"
                                class="admin-form-input"
                                :class="{
                                    'has-error': errors.amount,
                                }"
                                :placeholder="
                                    raceSponsor.remaining.toFixed(2)
                                "
                                required
                                @input="handleAmountInput"
                            />

                            <span
                                v-if="errors.amount"
                                class="admin-form-error"
                            >
                                {{ errors.amount }}
                            </span>

                            <span
                                v-else
                                class="admin-form-help"
                            >
                                Saldo disponible:
                                {{
                                    formatMoney(
                                        raceSponsor.remaining,
                                    )
                                }}
                            </span>
                        </div>

                        <div class="admin-form-group">
                            <label
                                for="paid_at"
                                class="admin-form-label"
                            >
                                Fecha de pago
                            </label>

                            <input
                                id="paid_at"
                                v-model="paymentForm.paid_at"
                                type="date"
                                :max="today"
                                class="admin-form-input"
                                :class="{
                                    'has-error': errors.paid_at,
                                }"
                                required
                                @change="handlePaidAtInput"
                            />

                            <span
                                v-if="errors.paid_at"
                                class="admin-form-error"
                            >
                                {{ errors.paid_at }}
                            </span>

                            <span
                                v-else
                                class="admin-form-help"
                            >
                                La fecha no puede ser posterior a hoy.
                            </span>
                        </div>

                        <div class="admin-form-group">
                            <label
                                for="payment_method"
                                class="admin-form-label"
                            >
                                Método de pago
                            </label>

                            <select
                                id="payment_method"
                                v-model="paymentForm.payment_method"
                                class="admin-form-input"
                                :class="{
                                    'has-error': errors.payment_method,
                                }"
                                @change="
                                    clearLocalError(
                                        'payment_method',
                                    )
                                "
                            >
                                <option value="">
                                    Selecciona
                                </option>

                                <option value="transferencia">
                                    Transferencia
                                </option>

                                <option value="deposito">
                                    Depósito
                                </option>

                                <option value="efectivo">
                                    Efectivo
                                </option>

                                <option value="cheque">
                                    Cheque
                                </option>

                                <option value="otro">
                                    Otro
                                </option>
                            </select>

                            <span
                                v-if="errors.payment_method"
                                class="admin-form-error"
                            >
                                {{ errors.payment_method }}
                            </span>
                        </div>

                        <div class="admin-form-group">
                            <label
                                for="reference"
                                class="admin-form-label"
                            >
                                Referencia / folio
                            </label>

                            <input
                                id="reference"
                                v-model="paymentForm.reference"
                                type="text"
                                class="admin-form-input"
                                :class="{
                                    'has-error': errors.reference,
                                }"
                                placeholder="Folio o referencia"
                                @input="
                                    clearLocalError(
                                        'reference',
                                    )
                                "
                            />

                            <span
                                v-if="errors.reference"
                                class="admin-form-error"
                            >
                                {{ errors.reference }}
                            </span>
                        </div>

                        <div class="admin-form-group payment-full">
                            <label
                                for="notes"
                                class="admin-form-label"
                            >
                                Notas
                            </label>

                            <textarea
                                id="notes"
                                v-model="paymentForm.notes"
                                class="admin-form-textarea"
                                :class="{
                                    'has-error': errors.notes,
                                }"
                                rows="3"
                                placeholder="Observaciones del pago..."
                                @input="
                                    clearLocalError(
                                        'notes',
                                    )
                                "
                            ></textarea>

                            <span
                                v-if="errors.notes"
                                class="admin-form-error"
                            >
                                {{ errors.notes }}
                            </span>
                        </div>
                    </div>

                    <div
                        v-if="amountExceedsRemaining"
                        class="payment-inline-warning"
                    >
                        El monto ingresado supera el saldo pendiente de
                        {{
                            formatMoney(
                                raceSponsor.remaining,
                            )
                        }}.
                    </div>

                    <div
                        v-if="paymentDateIsFuture"
                        class="payment-inline-warning"
                    >
                        La fecha seleccionada no puede ser futura.
                    </div>

                    <div class="payment-form-actions">
                        <button
                            type="button"
                            class="admin-btn admin-btn-secondary"
                            @click="closePaymentForm"
                        >
                            Cancelar
                        </button>

                        <button
                            type="submit"
                            class="admin-btn admin-btn-primary"
                            :disabled="
                                amountExceedsRemaining ||
                                paymentDateIsFuture
                            "
                        >
                            <Check
                                :size="16"
                                :stroke-width="2"
                            />

                            <span>
                                Guardar pago
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </Teleport>
</template>

<style scoped>
.admin-page {
    width: 100%;
    min-height: 90%;
    padding: 28px 30px 38px;
    background: var(--sc-page-background);
    color: var(--sc-page-text);
}

.admin-page-header {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 20px;
    margin-bottom: 25px;
}

.admin-page-eyebrow {
    display: block;
    margin-bottom: 5px;
    color: var(--sc-page-blue-dark);
    font-size: 12px;
    font-weight: 700;
    letter-spacing: 0.04em;
    text-transform: uppercase;
}

.admin-page-title {
    margin: 0;
    font-size: 27px;
    line-height: 1.15;
    font-weight: 700;
}

.admin-page-subtitle {
    margin: 7px 0 0;
    color: var(--sc-page-text-secondary);
    font-size: 13px;
}

.admin-page-header-actions {
    display: flex;
    gap: 9px;
}

.admin-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 7px;
    min-height: 40px;
    padding: 0 14px;
    border: 1px solid transparent;
    border-radius: 10px;
    font-family: inherit;
    font-size: 12px;
    font-weight: 600;
    text-decoration: none;
    cursor: pointer;
}

.admin-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.admin-btn-primary {
    background: linear-gradient(
        115deg,
        #249edb 0%,
        #1769a8 48%,
        #d95f91 100%
    );
    color: #ffffff;
}

.admin-btn-secondary {
    border-color: var(--sc-page-border);
    background: #ffffff;
    color: var(--sc-page-text-secondary);
}

.sponsorship-card,
.payments-card {
    border: 1px solid var(--sc-page-border);
    border-radius: var(--sc-page-radius);
    background: #ffffff;
    box-shadow:
        0 4px 15px rgba(27, 62, 90, 0.035),
        0 1px 3px rgba(27, 62, 90, 0.025);
}

.sponsorship-card {
    padding: 25px;
    margin-bottom: 18px;
}

.sponsorship-top {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 20px;
    padding-bottom: 21px;
    border-bottom: 1px solid var(--sc-page-border-soft);
}

.sponsorship-identity {
    display: flex;
    align-items: center;
    gap: 12px;
}

.sponsorship-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 42px;
    height: 42px;
    border-radius: 11px;
    background: var(--sc-page-blue-light);
    color: var(--sc-page-blue-dark);
}

.sponsorship-identity h2 {
    margin: 0;
    font-size: 17px;
    font-weight: 700;
}

.sponsorship-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 6px 13px;
    margin-top: 5px;
    color: var(--sc-page-text-secondary);
    font-size: 11px;
}

.status-badge {
    display: inline-flex;
    align-items: center;
    min-height: 25px;
    padding: 0 9px;
    border-radius: 7px;
    font-size: 10px;
    font-weight: 650;
}

.status-badge.active {
    background: var(--sc-page-green-light);
    color: #118c76;
}

.status-badge.inactive {
    background: var(--sc-page-red-light);
    color: var(--sc-page-red);
}

.financial-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 12px;
    padding: 21px 0;
}

.financial-item {
    padding: 16px;
    border: 1px solid var(--sc-page-border);
    border-radius: 10px;
    background: #fbfcfd;
}

.financial-label {
    display: block;
    margin-bottom: 7px;
    color: var(--sc-page-text-secondary);
    font-size: 11px;
}

.financial-item strong {
    font-size: 19px;
    font-weight: 700;
}

.progress-wrapper {
    padding-top: 3px;
}

.progress-header {
    display: flex;
    justify-content: space-between;
    margin-bottom: 7px;
    color: var(--sc-page-text-secondary);
    font-size: 11px;
}

.progress-header strong {
    color: var(--sc-page-text);
}

.progress-track {
    width: 100%;
    height: 8px;
    overflow: hidden;
    border-radius: 10px;
    background: #edf1f4;
}

.progress-bar {
    height: 100%;
    border-radius: inherit;
    background: linear-gradient(
        90deg,
        #249edb,
        #d95f91
    );
}

.benefits-box {
    margin-top: 19px;
    padding: 15px;
    border: 1px solid var(--sc-page-border);
    border-radius: 10px;
    background: #fbfcfd;
}

.benefits-title {
    display: block;
    margin-bottom: 5px;
    color: var(--sc-page-text);
    font-size: 11px;
    font-weight: 650;
}

.benefits-box p {
    margin: 0;
    color: var(--sc-page-text-secondary);
    font-size: 12px;
    line-height: 1.5;
}

.payments-card {
    padding: 25px;
}

.payments-header {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 20px;
    padding-bottom: 20px;
    border-bottom: 1px solid var(--sc-page-border-soft);
}

.payments-title {
    margin: 0;
    font-size: 17px;
    font-weight: 700;
}

.payments-description {
    margin: 5px 0 0;
    color: var(--sc-page-text-secondary);
    font-size: 11px;
}

.payment-form {
    padding: 0;
}

.payment-error-summary {
    display: flex;
    flex-direction: column;
    gap: 3px;
    margin-bottom: 18px;
    padding: 11px 13px;
    border: 1px solid #f0cbd0;
    border-radius: 9px;
    background: #fff6f7;
    color: var(--sc-page-red);
    font-size: 11px;
}

.payment-error-summary strong {
    font-size: 12px;
    font-weight: 650;
}

.payment-error-summary span {
    color: #9a5960;
}

.payment-inline-warning {
    margin-top: -4px;
    margin-bottom: 15px;
    padding: 9px 11px;
    border: 1px solid #f0cbd0;
    border-radius: 8px;
    background: #fff6f7;
    color: var(--sc-page-red);
    font-size: 11px;
}

.payment-form-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 0 18px;
}

.payment-full {
    grid-column: 1 / -1;
}

.admin-form-group {
    margin-bottom: 18px;
}

.admin-form-label {
    display: block;
    margin-bottom: 7px;
    color: var(--sc-page-text);
    font-size: 13px;
    font-weight: 650;
}

.admin-form-input,
.admin-form-textarea {
    display: block;
    width: 100%;
    border: 1px solid var(--sc-page-border);
    border-radius: 10px;
    background: #ffffff;
    color: var(--sc-page-text);
    font-family: inherit;
    font-size: 13px;
    box-sizing: border-box;
    transition:
        border-color 0.2s ease,
        box-shadow 0.2s ease,
        background-color 0.2s ease;
}

.admin-form-input {
    height: 42px;
    padding: 0 13px;
}

.admin-form-textarea {
    min-height: 90px;
    padding: 11px 13px;
    resize: vertical;
}

.admin-form-input:focus,
.admin-form-textarea:focus {
    outline: none;
    border-color: #a9d9f2;
    background: #ffffff;
    box-shadow: 0 0 0 3px rgba(36, 158, 219, 0.08);
}

.admin-form-input.has-error,
.admin-form-textarea.has-error {
    border-color: #efb8bf;
    background: #fff6f7;
}

.admin-form-input.has-error:focus,
.admin-form-textarea.has-error:focus {
    border-color: var(--sc-page-red);
    box-shadow: 0 0 0 3px rgba(232, 62, 77, 0.08);
}

.admin-form-help {
    display: block;
    margin-top: 5px;
    color: var(--sc-page-muted);
    font-size: 11px;
}

.admin-form-error {
    display: block;
    margin-top: 5px;
    color: var(--sc-page-red);
    font-size: 11px;
    font-weight: 500;
}

.payment-form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 9px;
    padding-top: 5px;
}

.payments-list {
    display: flex;
    flex-direction: column;
    gap: 9px;
    padding-top: 18px;
}

.payment-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 13px;
    border: 1px solid var(--sc-page-border);
    border-radius: 10px;
}

.payment-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 35px;
    height: 35px;
    flex: 0 0 35px;
    border-radius: 9px;
    background: var(--sc-page-green-light);
    color: var(--sc-page-green);
}

.payment-info {
    min-width: 0;
    flex: 1;
}

.payment-main {
    display: flex;
    align-items: center;
    gap: 10px;
}

.payment-main strong {
    color: var(--sc-page-text);
    font-size: 13px;
}

.payment-main span {
    color: var(--sc-page-text-secondary);
    font-size: 11px;
}

.payment-details {
    display: flex;
    flex-wrap: wrap;
    gap: 6px 12px;
    margin-top: 3px;
    color: var(--sc-page-text-secondary);
    font-size: 10px;
}

.payment-notes {
    margin: 5px 0 0;
    color: var(--sc-page-text-secondary);
    font-size: 11px;
}

.payment-actions {
    display: flex;
    align-items: center;
    gap: 6px;
}

.action-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 5px;
    min-height: 32px;
    padding: 0 9px;
    border: 1px solid var(--sc-page-border);
    border-radius: 8px;
    background: #ffffff;
    color: var(--sc-page-text-secondary);
    font-size: 12px;
    font-weight: 600;
    text-decoration: none;
    cursor: pointer;
}

.action-btn-delete {
    border-color: #f0cbd0;
    background: #fff6f7;
    color: var(--sc-page-red);
}

.payments-empty {
    display: flex;
    align-items: center;
    justify-content: center;
    flex-direction: column;
    gap: 5px;
    padding: 40px 20px;
    color: var(--sc-page-muted);
    text-align: center;
}

.payments-empty strong {
    color: var(--sc-page-text-secondary);
    font-size: 12px;
}

.payments-empty span {
    font-size: 11px;
}

/* =========================================================
   PAYMENT MODAL
   ========================================================= */

.payment-modal-overlay {
    position: fixed;
    inset: 0;
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 24px;
    background: rgba(23, 43, 77, 0.42);
    backdrop-filter: blur(3px);
}

.payment-modal {
    width: min(720px, 100%);
    max-height: calc(100vh - 48px);
    overflow-y: auto;
    border: 1px solid var(--sc-page-border);
    border-radius: 16px;
    background: #ffffff;
    box-shadow:
        0 20px 55px rgba(23, 43, 77, 0.18),
        0 5px 18px rgba(23, 43, 77, 0.08);
}

.payment-modal-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 20px;
    padding: 22px 24px 18px;
    border-bottom: 1px solid var(--sc-page-border-soft);
}

.payment-modal-header h2 {
    margin: 0;
    color: var(--sc-page-text);
    font-size: 20px;
    line-height: 1.2;
    font-weight: 700;
}

.payment-modal-header p {
    margin: 5px 0 0;
    color: var(--sc-page-text-secondary);
    font-size: 11px;
}

.payment-modal-close {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 34px;
    height: 34px;
    flex: 0 0 34px;
    border: 1px solid var(--sc-page-border);
    border-radius: 9px;
    background: #ffffff;
    color: var(--sc-page-text-secondary);
    cursor: pointer;
    transition:
        background-color 0.2s ease,
        color 0.2s ease,
        border-color 0.2s ease;
}

.payment-modal-close:hover {
    border-color: #d5dde4;
    background: #f8fafc;
    color: var(--sc-page-text);
}

.payment-modal .payment-form {
    padding: 22px 24px 24px;
}

@media (max-width: 760px) {
    .admin-page {
        padding: 22px 18px 30px;
    }

    .admin-page-header,
    .payments-header {
        align-items: flex-start;
        flex-direction: column;
    }

    .sponsorship-card,
    .payments-card {
        padding: 18px;
    }

    .financial-grid {
        grid-template-columns: 1fr;
    }

    .payment-form-grid {
        grid-template-columns: 1fr;
    }

    .payment-full {
        grid-column: auto;
    }

    .payment-item {
        align-items: flex-start;
        flex-wrap: wrap;
    }

    .payment-actions {
        width: 100%;
        padding-left: 47px;
    }

    .payment-modal-overlay {
        align-items: flex-end;
        padding: 0;
    }

    .payment-modal {
        width: 100%;
        max-height: 92vh;
        border-radius: 16px 16px 0 0;
    }

    .payment-modal-header {
        padding: 19px 18px 16px;
    }

    .payment-modal .payment-form {
        padding: 20px 18px 22px;
    }

    .payment-form-actions {
        flex-direction: column-reverse;
    }

    .payment-form-actions .admin-btn {
        width: 100%;
    }
}
</style>