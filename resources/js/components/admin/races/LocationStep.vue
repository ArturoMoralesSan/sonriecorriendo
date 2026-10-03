<script setup lang="ts">
interface LocationForm {
    event_date: string;
    start_time: string;
    end_time: string;
    location: string;
    address: string;
    city: string;
    state: string;
    country: string;
}

const props = defineProps<{
    form: LocationForm;
    getError: (key: string) => string | undefined;
    getFieldClass: (key: string) => string;
}>();
</script>

<template>
    <section class="location-step">
        <div class="location-step__header">
            <h2>
                Fecha y lugar
            </h2>

            <p>
                Define cuándo y dónde se llevará a cabo la carrera.
            </p>
        </div>

        <div class="location-step__form-grid">
            <div class="location-step__field">
                <label class="location-step__label">
                    Fecha del evento
                    <span>*</span>
                </label>

                <input
                    v-model="props.form.event_date"
                    type="date"
                    :class="props.getFieldClass('event_date')"
                />

                <p
                    v-if="props.getError('event_date')"
                    class="location-step__error"
                >
                    {{ props.getError('event_date') }}
                </p>
            </div>

            <div class="location-step__field">
                <label class="location-step__label">
                    Lugar
                    <span>*</span>
                </label>

                <input
                    v-model="props.form.location"
                    type="text"
                    :class="props.getFieldClass('location')"
                    placeholder="Ej. Parque Guadiana"
                />

                <p
                    v-if="props.getError('location')"
                    class="location-step__error"
                >
                    {{ props.getError('location') }}
                </p>
            </div>

            <div class="location-step__field">
                <label class="location-step__label">
                    Hora de inicio
                    <span>*</span>
                </label>

                <input
                    v-model="props.form.start_time"
                    type="time"
                    :class="props.getFieldClass('start_time')"
                />

                <p
                    v-if="props.getError('start_time')"
                    class="location-step__error"
                >
                    {{ props.getError('start_time') }}
                </p>
            </div>

            <div class="location-step__field">
                <label class="location-step__label">
                    Hora de finalización
                    <span>*</span>
                </label>

                <input
                    v-model="props.form.end_time"
                    type="time"
                    :class="props.getFieldClass('end_time')"
                />

                <p
                    v-if="props.getError('end_time')"
                    class="location-step__error"
                >
                    {{ props.getError('end_time') }}
                </p>
            </div>

            <div class="location-step__field location-step__field--full">
                <label class="location-step__label">
                    Dirección
                    <span>*</span>
                </label>

                <input
                    v-model="props.form.address"
                    type="text"
                    :class="props.getFieldClass('address')"
                    placeholder="Ej. Av. Universidad 100"
                />

                <p
                    v-if="props.getError('address')"
                    class="location-step__error"
                >
                    {{ props.getError('address') }}
                </p>
            </div>

            <div class="location-step__field">
                <label class="location-step__label">
                    Ciudad
                    <span>*</span>
                </label>

                <input
                    v-model="props.form.city"
                    type="text"
                    :class="props.getFieldClass('city')"
                    placeholder="Ej. Durango"
                />

                <p
                    v-if="props.getError('city')"
                    class="location-step__error"
                >
                    {{ props.getError('city') }}
                </p>
            </div>

            <div class="location-step__field">
                <label class="location-step__label">
                    Estado
                    <span>*</span>
                </label>

                <input
                    v-model="props.form.state"
                    type="text"
                    :class="props.getFieldClass('state')"
                    placeholder="Ej. Durango"
                />

                <p
                    v-if="props.getError('state')"
                    class="location-step__error"
                >
                    {{ props.getError('state') }}
                </p>
            </div>

            <div class="location-step__field">
                <label class="location-step__label">
                    País
                    <span>*</span>
                </label>

                <input
                    v-model="props.form.country"
                    type="text"
                    :class="props.getFieldClass('country')"
                    placeholder="México"
                />

                <p
                    v-if="props.getError('country')"
                    class="location-step__error"
                >
                    {{ props.getError('country') }}
                </p>
            </div>
        </div>
    </section>
</template>

<style scoped>
.location-step {
    width: 100%;
}

.location-step__header {
    margin-bottom: 26px;
}

.location-step__header h2 {
    margin: 0;
    color: var(--sc-page-text);
    font-size: 16px;
    font-weight: 700;
    letter-spacing: -0.01em;
}

.location-step__header p {
    margin: 6px 0 0;
    color: var(--sc-page-text-secondary);
    font-size: 12px;
    line-height: 1.5;
}

.location-step__form-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    column-gap: 20px;
}

.location-step__field {
    min-width: 0;
    margin-bottom: 22px;
}

.location-step__field--full {
    grid-column: 1 / -1;
}

.location-step__label {
    display: block;
    margin-bottom: 8px;
    color: var(--sc-page-text);
    font-size: 13px;
    font-weight: 650;
}

.location-step__label span {
    color: #d95c4f;
}

.location-step__error {
    margin: 6px 0 0;
    color: #d95c4f;
    font-size: 11px;
    line-height: 1.4;
}

@media (max-width: 900px) {
    .location-step__form-grid {
        grid-template-columns: 1fr;
    }

    .location-step__field--full {
        grid-column: auto;
    }
}
</style>