<script setup lang="ts">
interface RegistrationForm {
    registration_opens_at: string;
    registration_closes_at: string;
    terms_and_conditions: string;
    notes: string;
}

const props = defineProps<{
    form: RegistrationForm;
    getError: (key: string) => string | undefined;
    getFieldClass: (key: string) => string;
}>();
</script>

<template>
    <section class="registration-step">
        <div class="registration-step__header">
            <h2>
                Inscripciones
            </h2>

            <p>
                Configura el periodo de inscripción y las condiciones
                para participar en la carrera.
            </p>
        </div>

        <div class="registration-step__form-grid">
            <div class="registration-step__field">
                <label class="registration-step__label">
                    Apertura de inscripciones
                    <span>*</span>
                </label>

                <input
                    v-model="props.form.registration_opens_at"
                    type="datetime-local"
                    :class="
                        props.getFieldClass(
                            'registration_opens_at',
                        )
                    "
                />

                <p
                    v-if="
                        props.getError(
                            'registration_opens_at',
                        )
                    "
                    class="registration-step__error"
                >
                    {{
                        props.getError(
                            'registration_opens_at',
                        )
                    }}
                </p>
            </div>

            <div class="registration-step__field">
                <label class="registration-step__label">
                    Cierre de inscripciones
                    <span>*</span>
                </label>

                <input
                    v-model="props.form.registration_closes_at"
                    type="datetime-local"
                    :class="
                        props.getFieldClass(
                            'registration_closes_at',
                        )
                    "
                />

                <p
                    v-if="
                        props.getError(
                            'registration_closes_at',
                        )
                    "
                    class="registration-step__error"
                >
                    {{
                        props.getError(
                            'registration_closes_at',
                        )
                    }}
                </p>
            </div>

            <div
                class="registration-step__field registration-step__field--full"
            >
                <label class="registration-step__label">
                    Términos y condiciones
                </label>

                <textarea
                    v-model="props.form.terms_and_conditions"
                    :class="[
                        props.getFieldClass(
                            'terms_and_conditions',
                        ),
                        'registration-step__textarea',
                    ]"
                    placeholder="Escribe los términos y condiciones para los participantes..."
                ></textarea>

                <p
                    v-if="
                        props.getError(
                            'terms_and_conditions',
                        )
                    "
                    class="registration-step__error"
                >
                    {{
                        props.getError(
                            'terms_and_conditions',
                        )
                    }}
                </p>
            </div>

            <div
                class="registration-step__field registration-step__field--full"
            >
                <label class="registration-step__label">
                    Notas
                </label>

                <textarea
                    v-model="props.form.notes"
                    :class="[
                        props.getFieldClass('notes'),
                        'registration-step__textarea',
                    ]"
                    placeholder="Agrega información adicional para las inscripciones..."
                ></textarea>

                <p
                    v-if="props.getError('notes')"
                    class="registration-step__error"
                >
                    {{ props.getError('notes') }}
                </p>
            </div>
        </div>
    </section>
</template>

<style scoped>
.registration-step {
    width: 100%;
}

.registration-step__header {
    margin-bottom: 26px;
}

.registration-step__header h2 {
    margin: 0;
    color: var(--sc-page-text);
    font-size: 16px;
    font-weight: 700;
    letter-spacing: -0.01em;
}

.registration-step__header p {
    margin: 6px 0 0;
    color: var(--sc-page-text-secondary);
    font-size: 12px;
    line-height: 1.5;
}

.registration-step__form-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    column-gap: 20px;
}

.registration-step__field {
    min-width: 0;
    margin-bottom: 22px;
}

.registration-step__field--full {
    grid-column: 1 / -1;
}

.registration-step__label {
    display: block;
    margin-bottom: 8px;
    color: var(--sc-page-text);
    font-size: 13px;
    font-weight: 650;
}

.registration-step__label span {
    color: #d95c4f;
}

.registration-step__textarea {
    height: auto;
    min-height: 130px;
    padding-top: 12px;
    padding-bottom: 12px;
    resize: vertical;
}

.registration-step__error {
    margin: 6px 0 0;
    color: #d95c4f;
    font-size: 11px;
    line-height: 1.4;
}

@media (max-width: 900px) {
    .registration-step__form-grid {
        grid-template-columns: 1fr;
    }

    .registration-step__field--full {
        grid-column: auto;
    }
}
</style>