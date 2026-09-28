<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Building2, Save } from 'lucide-vue-next';

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
    location: string | null;
}

const props = defineProps<{
    sponsor: Sponsor;
    races: Race[];
}>();

const form = useForm({
    race_id: '',
    type: 'sponsor',
    amount: '',
    benefits: '',
    is_active: true,
});

const submit = (): void => {
    form.post(
        `/admin/sponsors/${props.sponsor.id}/races`,
        {
            preserveScroll: true,
        },
    );
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

const formatRaceOption = (
    race: Race,
): string => {
    const date = formatDate(
        race.event_date,
    );

    const time = formatTime(
        race.start_time,
    );

    if (time) {
        return `${race.name} — ${date} · ${time}`;
    }

    return `${race.name} — ${date}`;
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
                title: 'Asociar carrera',
            },
        ],
    },
});
</script>

<template>
    <Head title="Asociar carrera" />

    <div class="admin-page">
        <header class="admin-page-header">
            <div>
                <span class="admin-page-eyebrow">
                    Patrocinadores
                </span>

                <h1 class="admin-page-title">
                    Asociar carrera
                </h1>

                <p class="admin-page-subtitle">
                    Asocia {{ sponsor.name }} a una carrera y define
                    las condiciones del patrocinio.
                </p>
            </div>

            <div class="admin-page-header-actions">
                <Link
                    :href="admin.sponsors.show(sponsor.id)"
                    class="admin-btn admin-btn-secondary"
                >
                    <ArrowLeft
                        :size="16"
                        :stroke-width="2"
                    />

                    <span>Volver</span>
                </Link>
            </div>
        </header>

        <section class="admin-form-card">
            <form
                class="admin-form"
                @submit.prevent="submit"
            >
                <div class="admin-form-section">
                    <div class="admin-form-section-header">
                        <div class="admin-form-section-icon">
                            <Building2
                                :size="17"
                                :stroke-width="2"
                            />
                        </div>

                        <div>
                            <h2 class="admin-form-section-title">
                                Datos del patrocinio
                            </h2>

                            <p class="admin-form-section-description">
                                Define la carrera y el monto acordado.
                            </p>
                        </div>
                    </div>

                    <div class="admin-form-grid">
                        <div class="admin-form-group admin-form-group-full">
                            <label
                                for="race_id"
                                class="admin-form-label"
                            >
                                Carrera
                            </label>

                            <select
                                id="race_id"
                                v-model="form.race_id"
                                class="admin-form-input"
                                :class="{
                                    'has-error': form.errors.race_id,
                                }"
                            >
                                <option value="">
                                    Selecciona una carrera
                                </option>

                                <option
                                    v-for="race in races"
                                    :key="race.id"
                                    :value="race.id"
                                >
                                    {{ formatRaceOption(race) }}
                                </option>
                            </select>

                            <p
                                v-if="form.errors.race_id"
                                class="admin-form-error"
                            >
                                {{ form.errors.race_id }}
                            </p>
                        </div>

                        <div class="admin-form-group">
                            <label
                                for="type"
                                class="admin-form-label"
                            >
                                Tipo de patrocinio
                            </label>

                            <select
                                id="type"
                                v-model="form.type"
                                class="admin-form-input"
                            >
                                <option value="sponsor">
                                    Patrocinador
                                </option>

                                <option value="principal">
                                    Principal
                                </option>

                                <option value="official">
                                    Oficial
                                </option>

                                <option value="gold">
                                    Oro
                                </option>

                                <option value="silver">
                                    Plata
                                </option>

                                <option value="bronze">
                                    Bronce
                                </option>
                            </select>
                        </div>

                        <div class="admin-form-group">
                            <label
                                for="amount"
                                class="admin-form-label"
                            >
                                Monto acordado
                            </label>

                            <input
                                id="amount"
                                v-model="form.amount"
                                type="number"
                                min="0"
                                step="0.01"
                                class="admin-form-input"
                                :class="{
                                    'has-error': form.errors.amount,
                                }"
                                placeholder="25000.00"
                            />

                            <p
                                v-if="form.errors.amount"
                                class="admin-form-error"
                            >
                                {{ form.errors.amount }}
                            </p>
                        </div>

                        <div class="admin-form-group admin-form-group-full">
                            <label
                                for="benefits"
                                class="admin-form-label"
                            >
                                Beneficios / acuerdo
                            </label>

                            <textarea
                                id="benefits"
                                v-model="form.benefits"
                                class="admin-form-textarea"
                                rows="5"
                                placeholder="Describe los beneficios, presencia de marca o condiciones acordadas..."
                            ></textarea>
                        </div>

                        <div class="admin-form-group admin-form-group-full">
                            <label class="admin-form-label">
                                Estado
                            </label>

                            <label class="admin-switch">
                                <input
                                    v-model="form.is_active"
                                    type="checkbox"
                                />

                                <span class="admin-switch-slider"></span>

                                <span class="admin-switch-text">
                                    Patrocinio activo
                                </span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="admin-form-actions">
                    <Link
                        :href="admin.sponsors.show(sponsor.id)"
                        class="admin-btn admin-btn-secondary"
                    >
                        Cancelar
                    </Link>

                    <button
                        type="submit"
                        class="admin-btn admin-btn-primary"
                        :disabled="form.processing"
                    >
                        <Save
                            v-if="!form.processing"
                            :size="16"
                            :stroke-width="2"
                            class="admin-btn-icon"
                        />

                        <span>
                            {{
                                form.processing
                                    ? 'Guardando...'
                                    : 'Asociar carrera'
                            }}
                        </span>
                    </button>
                </div>
            </form>
        </section>
    </div>
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

.admin-form-card {
    width: 100%;
    border: 1px solid var(--sc-page-border);
    border-radius: var(--sc-page-radius);
    background: #ffffff;
    box-shadow:
        0 4px 15px rgba(27, 62, 90, 0.035),
        0 1px 3px rgba(27, 62, 90, 0.025);
}

.admin-form {
    padding: 28px;
}

.admin-form-section-header {
    display: flex;
    align-items: flex-start;
    gap: 11px;
    margin-bottom: 22px;
}

.admin-form-section-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 34px;
    height: 34px;
    flex: 0 0 34px;
    border-radius: 9px;
    background: var(--sc-page-blue-light);
    color: var(--sc-page-blue-dark);
}

.admin-form-section-title {
    margin: 0;
    font-size: 15px;
    font-weight: 700;
}

.admin-form-section-description {
    margin: 4px 0 0;
    color: var(--sc-page-text-secondary);
    font-size: 11px;
}

.admin-form-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 0 18px;
}

.admin-form-group {
    margin-bottom: 21px;
}

.admin-form-group-full {
    grid-column: 1 / -1;
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
    outline: none;
    background: #fbfcfd;
    color: var(--sc-page-text);
    font-family: inherit;
    font-size: 13px;
    box-sizing: border-box;
    transition:
        border-color 0.2s ease,
        background-color 0.2s ease,
        box-shadow 0.2s ease;
}

.admin-form-input {
    height: 42px;
    padding: 0 13px;
}

.admin-form-textarea {
    min-height: 110px;
    padding: 11px 13px;
    resize: vertical;
    line-height: 1.5;
}

.admin-form-input:focus,
.admin-form-textarea:focus {
    border-color: #a9d9f2;
    background: #ffffff;
    box-shadow: 0 0 0 3px rgba(36, 158, 219, 0.08);
}

.admin-form-input.has-error {
    border-color: #efb8bf;
    background: var(--sc-page-red-light);
}

.admin-form-error {
    margin: 5px 0 0;
    color: var(--sc-page-red);
    font-size: 11px;
}

/* SWITCH */

.admin-switch {
    display: inline-flex;
    align-items: center;
    gap: 9px;
    cursor: pointer;
    user-select: none;
}

.admin-switch input {
    position: absolute;
    width: 1px;
    height: 1px;
    opacity: 0;
    pointer-events: none;
}

.admin-switch-slider {
    position: relative;
    display: block;
    width: 42px;
    height: 24px;
    flex: 0 0 42px;
    border: 1px solid #b8c4d1;
    border-radius: 999px;
    background: #cbd5e1;
    box-sizing: border-box;
    transition:
        background-color 0.2s ease,
        border-color 0.2s ease;
}

.admin-switch-slider::after {
    content: '';
    position: absolute;
    top: 3px;
    left: 3px;
    width: 16px;
    height: 16px;
    border-radius: 50%;
    background: #ffffff;
    box-shadow:
        0 1px 3px rgba(15, 23, 42, 0.22),
        0 1px 2px rgba(15, 23, 42, 0.12);
    transition: transform 0.2s ease;
}

.admin-switch input:checked + .admin-switch-slider {
    background: var(--sc-page-blue);
    border-color: var(--sc-page-blue);
}

.admin-switch input:checked + .admin-switch-slider::after {
    transform: translateX(18px);
}

.admin-switch-text {
    color: var(--sc-page-text-secondary);
    font-size: 12px;
    font-weight: 500;
}

.admin-switch input:checked ~ .admin-switch-text {
    color: var(--sc-page-blue-dark);
    font-weight: 600;
}

/* ACTIONS */

.admin-form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 9px;
    padding-top: 7px;
    border-top: 1px solid var(--sc-page-border-soft);
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
    transition:
        opacity 0.2s ease,
        transform 0.2s ease;
}

.admin-btn:disabled {
    opacity: 0.55;
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

.admin-btn-icon {
    flex: 0 0 auto;
}

@media (max-width: 760px) {
    .admin-page {
        padding: 22px 18px 30px;
    }

    .admin-page-header {
        align-items: flex-start;
        flex-direction: column;
    }

    .admin-form {
        padding: 20px;
    }

    .admin-form-grid {
        grid-template-columns: 1fr;
    }

    .admin-form-group-full {
        grid-column: auto;
    }

    .admin-form-actions {
        flex-direction: column-reverse;
    }

    .admin-form-actions .admin-btn {
        width: 100%;
    }
}
</style>