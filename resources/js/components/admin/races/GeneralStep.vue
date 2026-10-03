<script setup lang="ts">
import { Image } from 'lucide-vue-next';

import ImageGallery from '@/Components/Admin/ImageGallery.vue';

interface KitImageForm {
    id: number | null;
    image: string | null;
    file: File | null;
    preview: string | null;
    sort_order: number;
    is_active: boolean;
    isNew: boolean;
}

interface GeneralForm {
    name: string;
    slug: string;
    description: string;
    status: string;
    banner: File | null;
    kit_images: KitImageForm[];
}

const props = defineProps<{
    form: GeneralForm;
    getError: (key: string) => string | undefined;
    getFieldClass: (key: string) => string;
    bannerPreview: string | null;
}>();

const emit = defineEmits<{
    (event: 'select-banner', event: Event): void;
    (event: 'remove-banner'): void;
    (event: 'change-banner'): void;
    (event: 'kit-gallery-error', message: string): void;
}>();
</script>

<template>
    <section class="general-step">
        <div class="general-step__header">
            <h2>
                Información general
            </h2>

            <p>
                Configura la información principal de la carrera.
            </p>
        </div>

        <div class="general-step__form-grid">
            <div class="general-step__field">
                <label class="general-step__label">
                    Nombre de la carrera
                    <span>*</span>
                </label>

                <input
                    v-model="props.form.name"
                    type="text"
                    :class="props.getFieldClass('name')"
                    placeholder="Ej. Carrera Sonríe Corriendo 2026"
                />

                <p
                    v-if="props.getError('name')"
                    class="general-step__error"
                >
                    {{ props.getError('name') }}
                </p>
            </div>

            <div class="general-step__field">
                <label class="general-step__label">
                    Slug
                    <span>*</span>
                </label>

                <input
                    v-model="props.form.slug"
                    type="text"
                    :class="props.getFieldClass('slug')"
                    readonly
                />

                <p class="general-step__help">
                    Se genera automáticamente a partir del nombre.
                </p>

                <p
                    v-if="props.getError('slug')"
                    class="general-step__error"
                >
                    {{ props.getError('slug') }}
                </p>
            </div>

            <div class="general-step__field general-step__field--full">
                <label class="general-step__label">
                    Descripción
                </label>

                <textarea
                    v-model="props.form.description"
                    :class="[
                        props.getFieldClass('description'),
                        'admin-form-textarea',
                    ]"
                    placeholder="Describe brevemente la carrera..."
                ></textarea>

                <p
                    v-if="props.getError('description')"
                    class="general-step__error"
                >
                    {{ props.getError('description') }}
                </p>
            </div>

            <div class="general-step__field general-step__field--full">
                <label class="general-step__label">
                    Banner
                </label>

                <input
                    id="race-banner-input"
                    type="file"
                    accept="image/*"
                    class="general-step__hidden-file"
                    @change="emit('select-banner', $event)"
                />

                <div
                    v-if="!props.bannerPreview"
                    class="general-step__banner-upload"
                    @click="emit('change-banner')"
                >
                    <div class="general-step__banner-icon">
                        <Image :size="20" />
                    </div>

                    <div class="general-step__banner-content">
                        <strong>
                            Agrega el banner de la carrera
                        </strong>

                        <p>
                            Selecciona una imagen para utilizarla
                            como portada.
                        </p>

                        <span>
                            JPG, PNG o WEBP
                        </span>
                    </div>

                    <button
                        type="button"
                        class="admin-btn admin-btn-secondary general-step__banner-button"
                        @click.stop="emit('change-banner')"
                    >
                        Seleccionar
                    </button>
                </div>

                <div
                    v-else
                    class="general-step__banner-preview"
                >
                    <img
                        :src="props.bannerPreview"
                        alt="Vista previa del banner"
                    />

                    <div class="general-step__banner-footer">
                        <div>
                            <strong>
                                Banner seleccionado
                            </strong>

                            <span>
                                Imagen lista para subir.
                            </span>
                        </div>

                        <div class="general-step__banner-actions">
                            <button
                                type="button"
                                class="admin-btn admin-btn-secondary"
                                @click="emit('change-banner')"
                            >
                                Cambiar
                            </button>

                            <button
                                type="button"
                                class="admin-btn admin-btn-secondary general-step__danger-button"
                                @click="emit('remove-banner')"
                            >
                                Eliminar
                            </button>
                        </div>
                    </div>
                </div>

                <p
                    v-if="props.getError('banner')"
                    class="general-step__error"
                >
                    {{ props.getError('banner') }}
                </p>
            </div>

            <div class="general-step__field general-step__field--full">
                <ImageGallery
                    v-model="props.form.kit_images"
                    label="Galería del kit"
                    hint="Agrega las imágenes del kit y arrástralas para cambiar su orden."
                    :max-images="20"
                    :max-size="10"
                    @error="emit('kit-gallery-error', $event)"
                />
            </div>

            <div class="general-step__field general-step__field--full">
                <label class="general-step__label">
                    Estado
                    <span>*</span>
                </label>

                <div class="general-step__status">
                    <div class="general-step__status-content">
                        <div class="general-step__status-icon">
                            <span></span>
                        </div>

                        <div>
                            <p class="general-step__status-title">
                                Estado de la carrera
                            </p>

                            <p class="general-step__status-description">
                                Define la etapa actual en la que se
                                encuentra la carrera.
                            </p>
                        </div>
                    </div>

                    <select
                        v-model="props.form.status"
                        :class="props.getFieldClass('status')"
                    >
                        <option value="draft">
                            Borrador
                        </option>

                        <option value="published">
                            Publicada
                        </option>

                        <option value="registration_open">
                            Inscripciones abiertas
                        </option>

                        <option value="registration_closed">
                            Inscripciones cerradas
                        </option>

                        <option value="finished">
                            Finalizada
                        </option>

                        <option value="cancelled">
                            Cancelada
                        </option>
                    </select>
                </div>

                <p
                    v-if="props.getError('status')"
                    class="general-step__error"
                >
                    {{ props.getError('status') }}
                </p>
            </div>
        </div>
    </section>
</template>

<style scoped>
.general-step {
    width: 100%;
}

.general-step__header {
    margin-bottom: 26px;
}

.general-step__header h2 {
    margin: 0;
    color: var(--sc-page-text);
    font-size: 16px;
    font-weight: 700;
    letter-spacing: -0.01em;
}

.general-step__header p {
    margin: 6px 0 0;
    color: var(--sc-page-text-secondary);
    font-size: 12px;
    line-height: 1.5;
}

.general-step__form-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    column-gap: 20px;
}

.general-step__field {
    min-width: 0;
    margin-bottom: 22px;
}

.general-step__field--full {
    grid-column: 1 / -1;
}

.general-step__label {
    display: block;
    margin-bottom: 8px;
    color: var(--sc-page-text);
    font-size: 13px;
    font-weight: 650;
}

.general-step__label span {
    color: #d95c4f;
}

.general-step__error {
    margin: 6px 0 0;
    color: #d95c4f;
    font-size: 11px;
    line-height: 1.4;
}

.general-step__help {
    margin: 7px 0 0;
    color: #8b9ba6;
    font-size: 11px;
    line-height: 1.45;
}

.general-step__hidden-file {
    display: none;
}

.general-step__banner-upload {
    display: flex;
    align-items: center;
    gap: 16px;
    min-height: 116px;
    padding: 19px;
    border: 1px dashed #cadbe4;
    border-radius: 10px;
    background: #fbfcfd;
    cursor: pointer;
    transition:
        border-color 0.2s ease,
        background-color 0.2s ease;
}

.general-step__banner-upload:hover {
    border-color: #a9d9f2;
    background: #f8fcff;
}

.general-step__banner-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 46px;
    height: 46px;
    flex: 0 0 46px;
    border-radius: 9px;
    background: #eef7fb;
    color: #70a7c1;
}

.general-step__banner-content {
    flex: 1;
    min-width: 0;
}

.general-step__banner-content strong {
    display: block;
    color: var(--sc-page-text);
    font-size: 12px;
    font-weight: 700;
}

.general-step__banner-content p {
    margin: 5px 0;
    color: var(--sc-page-text-secondary);
    font-size: 11px;
}

.general-step__banner-content span {
    color: #9aaab5;
    font-size: 10px;
}

.general-step__banner-button {
    flex: 0 0 auto;
}

.general-step__banner-preview {
    overflow: hidden;
    border: 1px solid var(--sc-page-border);
    border-radius: 10px;
    background: #f4f7f9;
}

.general-step__banner-preview img {
    display: block;
    width: 100%;
    height: 220px;
    object-fit: cover;
}

.general-step__banner-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 15px;
    padding: 12px 14px;
    border-top: 1px solid var(--sc-page-border);
    background: #ffffff;
}

.general-step__banner-footer > div:first-child {
    min-width: 0;
}

.general-step__banner-footer strong,
.general-step__banner-footer span {
    display: block;
}

.general-step__banner-footer strong {
    color: var(--sc-page-text);
    font-size: 11px;
}

.general-step__banner-footer span {
    margin-top: 3px;
    color: var(--sc-page-text-secondary);
    font-size: 10px;
}

.general-step__banner-actions {
    display: flex;
    gap: 7px;
    flex: 0 0 auto;
}

.general-step__danger-button {
    color: #c85454;
}

.general-step__status {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
    min-height: 74px;
    padding: 15px 17px;
    border: 1px solid #e5ecef;
    border-radius: 10px;
    background: #fbfcfd;
}

.general-step__status-content {
    display: flex;
    align-items: center;
    gap: 12px;
    min-width: 0;
}

.general-step__status-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    flex: 0 0 32px;
    border: 1px solid #dbeaf1;
    border-radius: 8px;
    background: #f1f8fb;
}

.general-step__status-icon span {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #76aac5;
}

.general-step__status-title {
    margin: 0;
    color: var(--sc-page-text);
    font-size: 12px;
    font-weight: 700;
}

.general-step__status-description {
    max-width: 550px;
    margin: 4px 0 0;
    color: var(--sc-page-text-secondary);
    font-size: 11px;
    line-height: 1.45;
}

.general-step__status .admin-form-input {
    width: 220px;
    flex: 0 0 220px;
}

@media (max-width: 900px) {
    .general-step__form-grid {
        grid-template-columns: 1fr;
    }

    .general-step__field--full {
        grid-column: auto;
    }

    .general-step__status {
        align-items: flex-start;
        flex-direction: column;
    }

    .general-step__status .admin-form-input {
        width: 100%;
        flex: none;
    }
}

@media (max-width: 640px) {
    .general-step__banner-upload {
        align-items: flex-start;
        flex-wrap: wrap;
    }

    .general-step__banner-button {
        width: 100%;
    }

    .general-step__banner-preview img {
        height: 180px;
    }

    .general-step__banner-footer {
        align-items: flex-start;
        flex-direction: column;
    }

    .general-step__banner-actions {
        width: 100%;
    }

    .general-step__banner-actions .admin-btn {
        flex: 1;
    }
}
</style>