<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import {
    ArrowLeft,
    BadgePercent,
    FileSpreadsheet,
    Link2,
    Search,
    Trash2,
    Upload,
    Users,
} from 'lucide-vue-next';
import Swal from 'sweetalert2';
import { computed, ref } from 'vue';

import { formatDiscountAmount } from '@/lib/discountAmount';
import {
    extractParticipants,
    readSpreadsheet,
} from '@/lib/readSpreadsheet';
import type { DiscountParticipant } from '@/lib/readSpreadsheet';
import admin from '@/routes/admin';

interface Race {
    id: number;
    name: string;
    slug: string;
    discount_registration_url: string | null;
    discount_amount: number | null;
}

const props = defineProps<{
    race: Race;
    participants: DiscountParticipant[];
}>();

/*
|--------------------------------------------------------------------------
| Enlace de descuento
|--------------------------------------------------------------------------
*/

const urlForm = useForm<{
    discount_registration_url: string;
    discount_amount: number | string | null;
}>({
    discount_registration_url:
        props.race.discount_registration_url ?? '',
    discount_amount: props.race.discount_amount ?? '',
});

const amountLabel = computed(() =>
    formatDiscountAmount(props.race.discount_amount),
);

function saveUrl() {
    urlForm.transform((data) => ({
        ...data,
        discount_amount:
            data.discount_amount === '' ? null : data.discount_amount,
    })).put(
        admin.races.discounts.url({ race: props.race.id }).url,
        { preserveScroll: true },
    );
}

/*
|--------------------------------------------------------------------------
| Importar Excel
|--------------------------------------------------------------------------
*/

const fileInput = ref<HTMLInputElement | null>(null);
const fileName = ref('');
const parsedParticipants = ref<DiscountParticipant[]>([]);
const incompleteRows = ref(0);
const duplicateRows = ref(0);
const parseError = ref('');
const parsing = ref(false);
const replaceList = ref(true);

const importForm = useForm<{
    participants: DiscountParticipant[];
    replace: boolean;
}>({
    participants: [],
    replace: true,
});

async function handleFile(event: Event) {
    const file = (event.target as HTMLInputElement).files?.[0];

    parsedParticipants.value = [];
    incompleteRows.value = 0;
    duplicateRows.value = 0;
    parseError.value = '';
    fileName.value = file?.name ?? '';

    if (!file) {
        return;
    }

    parsing.value = true;

    try {
        const rows = await readSpreadsheet(file);
        const result = extractParticipants(rows);

        parsedParticipants.value = result.participants;
        incompleteRows.value = result.incomplete;
        duplicateRows.value = result.duplicates;

        if (!result.participants.length) {
            parseError.value =
                'No se encontraron participantes con folio, nombres y apellidos.';
        }
    } catch (error) {
        parseError.value =
            error instanceof Error
                ? error.message
                : 'No se pudo leer el archivo.';
    } finally {
        parsing.value = false;
    }
}

function resetFile() {
    parsedParticipants.value = [];
    incompleteRows.value = 0;
    duplicateRows.value = 0;
    fileName.value = '';

    if (fileInput.value) {
        fileInput.value.value = '';
    }
}

function importParticipants() {
    if (!parsedParticipants.value.length) {
        return;
    }

    importForm.participants = parsedParticipants.value;
    importForm.replace = replaceList.value;

    importForm.post(
        admin.races.discounts.import({ race: props.race.id }).url,
        {
            preserveScroll: true,
            onSuccess: () => resetFile(),
        },
    );
}

async function clearList() {
    const result = await Swal.fire({
        icon: 'warning',
        title: '¿Eliminar la lista?',
        text: 'Se eliminarán todos los participantes con descuento de esta carrera.',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        reverseButtons: true,
    });

    if (!result.isConfirmed) {
        return;
    }

    router.delete(
        admin.races.discounts.destroy({ race: props.race.id }).url,
        { preserveScroll: true },
    );
}

/*
|--------------------------------------------------------------------------
| Buscar en la lista
|--------------------------------------------------------------------------
*/

const search = ref('');

function normalizeSearch(value: string): string {
    return value
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .toUpperCase()
        .replace(/\s+/g, ' ')
        .trim();
}

const filteredParticipants = computed(() => {
    const term = normalizeSearch(search.value);

    const list = term
        ? props.participants.filter((p) =>
              normalizeSearch(
                  `${p.folio} ${p.first_name} ${p.last_name}`,
              ).includes(term),
          )
        : props.participants;

    return list.slice(0, 200);
});
</script>

<template>
    <Head :title="`Descuentos - ${race.name}`" />

    <div class="admin-page">
        <!-- =================================================
             HEADER
        ================================================== -->

        <header class="admin-page-header">
            <div>
                <p class="admin-page-eyebrow">
                    Carrera
                </p>

                <h1 class="admin-page-title">
                    Descuentos
                </h1>

                <p class="admin-page-subtitle">
                    Sube el Excel de participantes pasados. Quien busque
                    por su número de folio, su nombre completo o ambos en
                    la página de la carrera y aparezca en la lista verá
                    el enlace de registro con descuento.
                </p>
            </div>

            <div class="admin-page-header-actions">
                <Link
                    :href="admin.races.show(race.id).url"
                    class="admin-btn admin-btn-secondary"
                >
                    <ArrowLeft
                        :size="14"
                        :stroke-width="2"
                    />

                    Regresar
                </Link>
            </div>
        </header>

        <!-- =================================================
             RESUMEN
        ================================================== -->

        <section class="admin-form-card">
            <div class="show-card-header">
                <div>
                    <p class="show-card-eyebrow">
                        Carrera
                    </p>

                    <h2 class="show-card-title">
                        {{ race.name }}
                    </h2>

                    <p class="show-card-description">
                        Resumen de la configuración de descuentos para
                        esta carrera.
                    </p>
                </div>

                <div class="show-card-icon">
                    <BadgePercent
                        :size="17"
                        :stroke-width="2"
                    />
                </div>
            </div>

            <div class="show-card-body">
                <div class="discount-info-grid">
                    <div class="show-info-box">
                        <span>
                            Participantes con descuento
                        </span>

                        <strong>
                            {{ participants.length }}
                        </strong>
                    </div>

                    <div class="show-info-box">
                        <span>
                            Enlace de descuento
                        </span>

                        <strong
                            class="discount-status"
                            :class="{
                                active: !!race.discount_registration_url,
                            }"
                        >
                            <span class="discount-status-dot"></span>

                            {{
                                race.discount_registration_url
                                    ? 'Configurado'
                                    : 'Sin configurar'
                            }}
                        </strong>
                    </div>

                    <div class="show-info-box">
                        <span>
                            Precio con descuento
                        </span>

                        <strong>
                            {{ amountLabel ?? 'Sin precio' }}
                        </strong>
                    </div>
                </div>
            </div>
        </section>

        <!-- =================================================
             ENLACE
        ================================================== -->

        <section class="admin-form-card">
            <div class="show-card-header">
                <div>
                    <p class="show-card-eyebrow">
                        Registro
                    </p>

                    <h2 class="show-card-title">
                        Enlace y precio con descuento
                    </h2>

                    <p class="show-card-description">
                        Este enlace solo se muestra a quienes su folio o
                        nombre completo coincidan con la lista.
                    </p>
                </div>

                <div class="show-card-icon">
                    <Link2
                        :size="17"
                        :stroke-width="2"
                    />
                </div>
            </div>

            <form
                class="show-card-body"
                @submit.prevent="saveUrl"
            >
                <div class="admin-form-group">
                    <label
                        for="discount_url"
                        class="admin-form-label"
                    >
                        Enlace
                    </label>

                    <input
                        id="discount_url"
                        v-model="urlForm.discount_registration_url"
                        type="url"
                        class="admin-form-input"
                        placeholder="https://..."
                    />

                    <p
                        v-if="urlForm.errors.discount_registration_url"
                        class="admin-form-error"
                    >
                        {{ urlForm.errors.discount_registration_url }}
                    </p>
                </div>

                <div class="admin-form-group discount-amount-group">
                    <label
                        for="discount_amount"
                        class="admin-form-label"
                    >
                        Precio final con descuento (MXN)
                    </label>

                    <input
                        id="discount_amount"
                        v-model="urlForm.discount_amount"
                        type="number"
                        min="0"
                        step="0.01"
                        inputmode="decimal"
                        class="admin-form-input"
                        placeholder="Ej. 330"
                    />

                    <p class="discount-hint">
                        Es el costo total que pagará el participante (no la
                        rebaja). Se muestra en la página de la carrera como
                        "¿Ya has participado? Inscríbete por $330".
                        Déjalo vacío para no mostrar precio.
                    </p>

                    <p
                        v-if="urlForm.errors.discount_amount"
                        class="admin-form-error"
                    >
                        {{ urlForm.errors.discount_amount }}
                    </p>
                </div>

                <div class="discount-actions">
                    <button
                        type="submit"
                        class="admin-btn admin-btn-primary"
                        :disabled="urlForm.processing"
                    >
                        <Link2
                            :size="14"
                            :stroke-width="2"
                        />

                        {{
                            urlForm.processing
                                ? 'Guardando...'
                                : 'Guardar'
                        }}
                    </button>
                </div>
            </form>
        </section>

        <!-- =================================================
             IMPORTAR
        ================================================== -->

        <section class="admin-form-card">
            <div class="show-card-header">
                <div>
                    <p class="show-card-eyebrow">
                        Participantes
                    </p>

                    <h2 class="show-card-title">
                        Importar participantes
                    </h2>

                    <p class="show-card-description">
                        Archivo .xlsx o .csv (primera hoja) con las
                        columnas <strong>Número / Folio</strong>,
                        <strong>Nombres</strong> y
                        <strong>Apellidos</strong>. Las demás columnas
                        se ignoran.
                    </p>
                </div>

                <div class="show-card-icon">
                    <Upload
                        :size="17"
                        :stroke-width="2"
                    />
                </div>
            </div>

            <div class="show-card-body">
                <label
                    class="discount-dropzone"
                    :class="{ 'has-file': !!fileName }"
                >
                    <input
                        ref="fileInput"
                        type="file"
                        accept=".xlsx,.csv"
                        @change="handleFile"
                    />

                    <span class="discount-dropzone-icon">
                        <FileSpreadsheet
                            :size="20"
                            :stroke-width="2"
                        />
                    </span>

                    <strong>
                        {{ fileName || 'Seleccionar archivo' }}
                    </strong>

                    <span>
                        {{
                            parsing
                                ? 'Leyendo archivo...'
                                : 'Haz clic para elegir un archivo .xlsx o .csv'
                        }}
                    </span>
                </label>

                <p
                    v-if="parseError"
                    class="admin-form-error"
                >
                    {{ parseError }}
                </p>

                <div
                    v-if="parsedParticipants.length"
                    class="discount-parsed"
                >
                    <FileSpreadsheet
                        :size="15"
                        :stroke-width="2"
                    />

                    <span>
                        <strong>{{ parsedParticipants.length }}</strong>
                        participante(s) listos
                        <template v-if="incompleteRows">
                            · {{ incompleteRows }} fila(s) sin folio,
                            nombres o apellidos se omitirán
                        </template>
                        <template v-if="duplicateRows">
                            · {{ duplicateRows }} folio(s) repetidos
                            (se usa el último)
                        </template>
                    </span>
                </div>

                <p
                    v-if="importForm.errors.participants"
                    class="admin-form-error"
                >
                    {{ importForm.errors.participants }}
                </p>

                <div class="discount-replace">
                    <label class="admin-switch">
                        <input
                            v-model="replaceList"
                            type="checkbox"
                        />

                        <span class="admin-switch-slider"></span>
                    </label>

                    <div>
                        <strong>
                            Reemplazar la lista actual
                        </strong>

                        <span>
                            Si se desactiva, los participantes se agregan
                            a los existentes (un folio repetido se
                            actualiza).
                        </span>
                    </div>
                </div>

                <div class="discount-actions">
                    <button
                        type="button"
                        class="admin-btn admin-btn-primary"
                        :disabled="
                            !parsedParticipants.length ||
                            importForm.processing
                        "
                        @click="importParticipants"
                    >
                        <Upload
                            :size="14"
                            :stroke-width="2"
                        />

                        {{
                            importForm.processing
                                ? 'Importando...'
                                : `Importar ${parsedParticipants.length || ''}`
                        }}
                    </button>
                </div>
            </div>
        </section>

        <!-- =================================================
             LISTA
        ================================================== -->

        <section class="admin-form-card">
            <div class="show-card-header">
                <div>
                    <p class="show-card-eyebrow">
                        Participantes
                    </p>

                    <h2 class="show-card-title">
                        Participantes con descuento
                        ({{ participants.length }})
                    </h2>

                    <p class="show-card-description">
                        Folios que pueden ver el enlace de registro con
                        descuento.
                    </p>
                </div>

                <div class="show-card-icon">
                    <Users
                        :size="17"
                        :stroke-width="2"
                    />
                </div>
            </div>

            <div class="show-card-body">
                <template v-if="participants.length">
                    <div class="discount-toolbar">
                        <div class="search-field">
                            <Search
                                :size="15"
                                :stroke-width="2"
                            />

                            <input
                                v-model="search"
                                type="text"
                                placeholder="Buscar por folio, nombre o apellido..."
                            />
                        </div>

                        <button
                            type="button"
                            class="discount-clear-btn"
                            @click="clearList"
                        >
                            <Trash2
                                :size="13"
                                :stroke-width="2"
                            />

                            Eliminar lista
                        </button>
                    </div>

                    <ul
                        v-if="filteredParticipants.length"
                        class="discount-list"
                    >
                        <li
                            v-for="participant in filteredParticipants"
                            :key="participant.folio"
                        >
                            <span class="discount-folio">
                                {{ participant.folio }}
                            </span>

                            <span class="discount-name">
                                {{ participant.first_name }}
                                {{ participant.last_name }}
                            </span>
                        </li>
                    </ul>

                    <div
                        v-else
                        class="show-empty-block"
                    >
                        <Search
                            :size="20"
                            :stroke-width="2"
                        />

                        <strong>
                            Sin resultados
                        </strong>

                        <span>
                            Ningún participante coincide con la búsqueda.
                        </span>
                    </div>

                    <p
                        v-if="filteredParticipants.length === 200"
                        class="discount-limit"
                    >
                        Mostrando los primeros 200 resultados.
                    </p>
                </template>

                <div
                    v-else
                    class="show-empty-block"
                >
                    <Users
                        :size="20"
                        :stroke-width="2"
                    />

                    <strong>
                        Aún no hay participantes importados
                    </strong>

                    <span>
                        Importa el Excel con folios, nombres y apellidos
                        para habilitar el descuento.
                    </span>
                </div>
            </div>
        </section>
    </div>
</template>

<style scoped>
/* =========================================================
   PAGE SPACING
   ========================================================= */

.admin-page {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

/* =========================================================
   CARDS
   ========================================================= */

.admin-form-card {
    width: 100%;
    overflow: hidden;
}

.show-card-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 20px;
    padding: 21px 24px;
    border-bottom: 1px solid var(--sc-page-border);
}

.show-card-eyebrow {
    margin: 0 0 5px;
    color: #91a1ac;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.06em;
    text-transform: uppercase;
}

.show-card-title {
    margin: 0;
    color: var(--sc-page-text);
    font-size: 16px;
    font-weight: 700;
}

.show-card-description {
    margin: 6px 0 0;
    color: #8999a4;
    font-size: 12px;
    line-height: 1.55;
}

.show-card-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 34px;
    height: 34px;
    flex: 0 0 34px;
    border: 1px solid #dce9ef;
    border-radius: 9px;
    background: #f5f9fb;
    color: #7592a3;
}

.show-card-body {
    padding: 23px 24px;
}

/* =========================================================
   SUMMARY
   ========================================================= */

.discount-info-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 10px;
}

.discount-amount-group {
    margin-top: 16px;
}

.discount-hint {
    margin: 6px 0 0;
    color: #8999a4;
    font-size: 11px;
}

.show-info-box {
    display: flex;
    flex-direction: column;
    gap: 8px;
    min-width: 0;
    padding: 13px;
    border: 1px solid #e5ecef;
    border-radius: 10px;
    background: #fbfcfd;
}

.show-info-box > span {
    color: #91a1ac;
    font-size: 11px;
    font-weight: 650;
}

.show-info-box > strong {
    color: var(--sc-page-text);
    font-size: 16px;
    font-weight: 700;
    line-height: 1.5;
}

.show-info-box > .discount-status {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    color: #9a7d82;
    font-size: 13px;
}

.show-info-box > .discount-status.active {
    color: #628b78;
}

.discount-status-dot {
    width: 7px;
    height: 7px;
    flex: 0 0 7px;
    border-radius: 50%;
    background: currentColor;
}

/* =========================================================
   ACTIONS
   ========================================================= */

.discount-actions {
    display: flex;
    justify-content: flex-end;
    margin-top: 18px;
}

/* =========================================================
   IMPORT
   ========================================================= */

.discount-dropzone {
    display: flex;
    align-items: center;
    flex-direction: column;
    justify-content: center;
    gap: 6px;
    padding: 26px 20px;
    border: 1px dashed #cfdde4;
    border-radius: 10px;
    background: #fbfcfd;
    color: #93a1a9;
    text-align: center;
    cursor: pointer;
    transition:
        border-color 0.2s ease,
        background-color 0.2s ease;
}

.discount-dropzone:hover,
.discount-dropzone.has-file {
    border-color: #249edb;
    background: #f5fafd;
}

.discount-dropzone input {
    display: none;
}

.discount-dropzone-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    margin-bottom: 4px;
    border: 1px solid #dce9ef;
    border-radius: 10px;
    background: #ffffff;
    color: #7592a3;
}

.discount-dropzone strong {
    max-width: 100%;
    overflow: hidden;
    color: var(--sc-page-text);
    font-size: 13px;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.discount-dropzone > span:last-child {
    font-size: 11px;
}

.discount-parsed {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-top: 12px;
    padding: 10px 12px;
    border: 1px solid #d6e8df;
    border-radius: 9px;
    background: #f4faf7;
    color: #628b78;
    font-size: 12px;
}

.discount-parsed strong {
    color: #49755e;
}

.discount-replace {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-top: 16px;
    padding: 13px;
    border: 1px solid #e5ecef;
    border-radius: 10px;
    background: #fbfcfd;
}

.discount-replace > div {
    display: flex;
    flex-direction: column;
    gap: 3px;
}

.discount-replace strong {
    color: var(--sc-page-text);
    font-size: 12px;
}

.discount-replace > div > span {
    color: #8999a4;
    font-size: 11px;
}

/* =========================================================
   LIST
   ========================================================= */

.discount-toolbar {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 16px;
}

.search-field {
    display: flex;
    flex: 1;
    align-items: center;
    gap: 8px;
    min-height: 39px;
    padding: 0 11px;
    border: 1px solid #dfe7eb;
    border-radius: 9px;
    background: #ffffff;
    color: #91a1ac;
}

.search-field:focus-within {
    border-color: #249edb;
    box-shadow: 0 0 0 3px rgba(36, 158, 219, 0.08);
}

.search-field input {
    width: 100%;
    min-width: 0;
    border: 0;
    outline: 0;
    background: transparent;
    color: var(--sc-page-text);
    font-family: inherit;
    font-size: 12px;
}

.search-field input::placeholder {
    color: #a0adb5;
}

.discount-clear-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    min-height: 39px;
    padding: 0 13px;
    border: 1px solid #ecdfe1;
    border-radius: 9px;
    background: #ffffff;
    color: #9a7d82;
    cursor: pointer;
    font-family: inherit;
    font-size: 11px;
    font-weight: 650;
    white-space: nowrap;
}

.discount-clear-btn:hover {
    border-color: #e2c5c9;
    background: #fdf7f8;
    color: #b15c66;
}

.discount-list {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
    gap: 8px;
    max-height: 380px;
    margin: 0;
    padding: 0;
    overflow-y: auto;
    list-style: none;
}

.discount-list li {
    display: flex;
    align-items: center;
    gap: 10px;
    min-width: 0;
    padding: 9px 11px;
    border: 1px solid #e5ecef;
    border-radius: 8px;
    background: #fbfcfd;
    color: #647884;
    font-size: 12px;
    font-weight: 600;
}

.discount-folio {
    flex: 0 0 auto;
    min-width: 34px;
    padding: 2px 7px;
    border-radius: 6px;
    background: #eef5f9;
    color: #1769a8;
    font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
    font-size: 11px;
    text-align: center;
}

.discount-name {
    min-width: 0;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.discount-limit {
    margin: 12px 0 0;
    color: #93a1a9;
    font-size: 11px;
    text-align: center;
}

/* =========================================================
   EMPTY
   ========================================================= */

.show-empty-block {
    display: flex;
    align-items: center;
    flex-direction: column;
    justify-content: center;
    gap: 8px;
    padding: 35px 20px;
    color: #93a1a9;
    text-align: center;
}

.show-empty-block strong {
    color: var(--sc-page-text);
    font-size: 13px;
}

.show-empty-block span {
    font-size: 11px;
}

/* =========================================================
   RESPONSIVE
   ========================================================= */

@media (max-width: 700px) {
    .admin-page {
        gap: 16px;
    }

    .show-card-header,
    .show-card-body {
        padding: 18px;
    }

    .discount-info-grid {
        grid-template-columns: 1fr;
    }

    .discount-toolbar {
        align-items: stretch;
        flex-direction: column;
    }

    .discount-clear-btn {
        justify-content: center;
    }

    .discount-actions .admin-btn {
        justify-content: center;
        width: 100%;
    }
}
</style>
