<script setup lang="ts">
interface ResultsForm {
    results_url: string;
}

const props = defineProps<{
    form: ResultsForm;
    getError: (key: string) => string | undefined;
    getFieldClass: (key: string) => string;
}>();
</script>

<template>
    <section class="results-step">
        <div class="results-step__header">
            <h2>
                Resultados
            </h2>

            <p>
                Agrega el enlace externo donde estarán disponibles
                los resultados de la carrera.
            </p>
        </div>

        <div class="results-step__form-grid">
            <div class="results-step__field results-step__field--full">
                <label class="results-step__label">
                    Enlace de resultados
                </label>

                <input
                    v-model="props.form.results_url"
                    type="url"
                    :class="props.getFieldClass('results_url')"
                    placeholder="https://ejemplo.com/resultados"
                />

                <p class="results-step__help">
                    Este enlace corresponde al sitio externo donde
                    se publicarán los resultados de esta carrera.
                </p>

                <p
                    v-if="props.getError('results_url')"
                    class="results-step__error"
                >
                    {{ props.getError('results_url') }}
                </p>
            </div>
        </div>
    </section>
</template>

<style scoped>
.results-step {
    width: 100%;
}

.results-step__header {
    margin-bottom: 26px;
}

.results-step__header h2 {
    margin: 0;
    color: var(--sc-page-text);
    font-size: 16px;
    font-weight: 700;
    letter-spacing: -0.01em;
}

.results-step__header p {
    margin: 6px 0 0;
    color: var(--sc-page-text-secondary);
    font-size: 12px;
    line-height: 1.5;
}

.results-step__form-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    column-gap: 20px;
}

.results-step__field {
    min-width: 0;
    margin-bottom: 22px;
}

.results-step__field--full {
    grid-column: 1 / -1;
}

.results-step__label {
    display: block;
    margin-bottom: 8px;
    color: var(--sc-page-text);
    font-size: 13px;
    font-weight: 650;
}

.results-step__help {
    margin: 7px 0 0;
    color: #8b9ba6;
    font-size: 11px;
    line-height: 1.45;
}

.results-step__error {
    margin: 6px 0 0;
    color: #d95c4f;
    font-size: 11px;
    line-height: 1.4;
}

@media (max-width: 900px) {
    .results-step__form-grid {
        grid-template-columns: 1fr;
    }

    .results-step__field--full {
        grid-column: auto;
    }
}
</style>