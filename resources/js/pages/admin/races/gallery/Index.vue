<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import {
    ArrowLeft,
    Check,
    ImagePlus,
    Trash2,
    Upload,
    X,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';

import admin from '@/routes/admin';

interface Race {
    id: number;
    name: string;
    slug: string;
}

interface GalleryItem {
    id: number;
    image: string;
    sort_order: number;
    is_active: boolean;
    created_at: string | null;
}

const props = defineProps<{
    race: Race;
    gallery: GalleryItem[];
}>();

const fileInput = ref<HTMLInputElement | null>(null);
const selectedFiles = ref<File[]>([]);
const isDragging = ref(false);
const uploading = ref(false);
const deletingId = ref<number | null>(null);

const form = useForm<{
    images: File[];
}>({
    images: [],
});

const previews = computed(() =>
    selectedFiles.value.map((file) => ({
        file,
        url: URL.createObjectURL(file),
    })),
);

function openFilePicker() {
    fileInput.value?.click();
}

function handleFiles(files: FileList | File[]) {
    const incomingFiles = Array.from(files).filter((file) =>
        file.type.startsWith('image/'),
    );

    if (!incomingFiles.length) {
        return;
    }

    selectedFiles.value = [
        ...selectedFiles.value,
        ...incomingFiles,
    ];

    form.images = selectedFiles.value;
}

function handleFileInput(event: Event) {
    const target = event.target as HTMLInputElement;

    if (!target.files) {
        return;
    }

    handleFiles(target.files);

    target.value = '';
}

function handleDrop(event: DragEvent) {
    event.preventDefault();
    isDragging.value = false;

    if (!event.dataTransfer?.files) {
        return;
    }

    handleFiles(event.dataTransfer.files);
}

function removeSelected(index: number) {
    selectedFiles.value.splice(index, 1);
    form.images = selectedFiles.value;
}

function clearSelected() {
    selectedFiles.value = [];
    form.images = [];
}

function uploadImages() {
    if (!selectedFiles.value.length || uploading.value) {
        return;
    }

    uploading.value = true;

    form.images = selectedFiles.value;

    form.post(
        admin.races.gallery.store({
            race: props.race.id,
        }).url,
        {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: () => {
                clearSelected();
            },
            onFinish: () => {
                uploading.value = false;
            },
        },
    );
}

function deleteImage(gallery: GalleryItem) {
    if (deletingId.value !== null) {
        return;
    }

    if (!confirm('¿Deseas eliminar esta imagen de la galería?')) {
        return;
    }

    deletingId.value = gallery.id;

    router.delete(
        admin.races.gallery.destroy({
            race: props.race.id,
            gallery: gallery.id,
        }).url,
        {
            preserveScroll: true,
            onFinish: () => {
                deletingId.value = null;
            },
        },
    );
}

function toggleActive(gallery: GalleryItem) {
    router.put(
        admin.races.gallery.update({
            race: props.race.id,
            gallery: gallery.id,
        }).url,
        {
            is_active: !gallery.is_active,
        },
        {
            preserveScroll: true,
        },
    );
}

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Panel',
                href: admin.dashboard(),
            },
            {
                title: 'Carreras',
                href: admin.races.index(),
            },
            {
                title: 'Galería',
                href: admin.races.index(),
            },
        ],
    },
});
</script>

<template>
    <Head :title="`Galería - ${race.name}`" />

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
                    Galería
                </h1>

                <p class="admin-page-subtitle">
                    Administra las imágenes que se mostrarán en el
                    carrusel de la página pública del evento.
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
             EVENT INFO
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
                        Galería de imágenes asociada a esta carrera.
                        Las imágenes activas estarán disponibles para
                        el carrusel de la página pública.
                    </p>
                </div>

                <div class="show-card-icon">
                    <ImagePlus
                        :size="17"
                        :stroke-width="2"
                    />
                </div>
            </div>

            <div class="show-card-body">
                <div class="gallery-info-grid">
                    <div class="show-info-box">
                        <span>
                            Total de imágenes
                        </span>

                        <strong>
                            {{ gallery.length }}
                        </strong>
                    </div>

                    <div class="show-info-box">
                        <span>
                            Imágenes activas
                        </span>

                        <strong>
                            {{
                                gallery.filter(
                                    (item) => item.is_active,
                                ).length
                            }}
                        </strong>
                    </div>

                    <div class="show-info-box">
                        <span>
                            Imágenes inactivas
                        </span>

                        <strong>
                            {{
                                gallery.filter(
                                    (item) => !item.is_active,
                                ).length
                            }}
                        </strong>
                    </div>
                </div>
            </div>
        </section>

        <!-- =================================================
             UPLOAD
        ================================================== -->

        <section class="admin-form-card">
            <div class="show-card-header">
                <div>
                    <p class="show-card-eyebrow">
                        Multimedia
                    </p>

                    <h2 class="show-card-title">
                        Agregar imágenes
                    </h2>

                    <p class="show-card-description">
                        Selecciona una o varias imágenes para agregarlas
                        a la galería de esta carrera.
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
                <div
                    class="gallery-dropzone"
                    :class="{
                        'gallery-dropzone-active': isDragging,
                    }"
                    @dragenter.prevent="isDragging = true"
                    @dragover.prevent="isDragging = true"
                    @dragleave.prevent="isDragging = false"
                    @drop="handleDrop"
                    @click="openFilePicker"
                >
                    <input
                        ref="fileInput"
                        type="file"
                        accept="image/jpeg,image/png,image/webp"
                        multiple
                        hidden
                        @change="handleFileInput"
                    />

                    <div class="gallery-dropzone-icon">
                        <Upload
                            :size="21"
                            :stroke-width="2"
                        />
                    </div>

                    <strong>
                        Arrastra tus imágenes aquí
                    </strong>

                    <span>
                        o haz clic para seleccionar archivos
                    </span>

                    <small>
                        JPG, PNG o WEBP
                    </small>
                </div>

                <!-- SELECTED FILES -->

                <div
                    v-if="previews.length"
                    class="gallery-selected"
                >
                    <div class="gallery-section-header">
                        <div>
                            <p class="show-card-eyebrow">
                                Preparadas
                            </p>

                            <h3>
                                Imágenes seleccionadas
                            </h3>
                        </div>

                        <button
                            type="button"
                            class="gallery-clear-btn"
                            @click="clearSelected"
                        >
                            <X
                                :size="14"
                                :stroke-width="2"
                            />

                            Limpiar
                        </button>
                    </div>

                    <div class="gallery-preview-grid">
                        <div
                            v-for="(preview, index) in previews"
                            :key="`${preview.file.name}-${index}`"
                            class="gallery-preview"
                        >
                            <div class="gallery-preview-image">
                                <img
                                    :src="preview.url"
                                    :alt="preview.file.name"
                                />

                                <button
                                    type="button"
                                    class="gallery-preview-remove"
                                    @click.stop="removeSelected(index)"
                                >
                                    <X
                                        :size="13"
                                        :stroke-width="2"
                                    />
                                </button>
                            </div>

                            <div class="gallery-preview-name">
                                {{ preview.file.name }}
                            </div>
                        </div>
                    </div>

                    <div class="gallery-upload-actions">
                        <button
                            type="button"
                            class="admin-btn admin-btn-primary"
                            :disabled="uploading"
                            @click="uploadImages"
                        >
                            <Upload
                                :size="14"
                                :stroke-width="2"
                            />

                            {{
                                uploading
                                    ? 'Subiendo...'
                                    : 'Subir imágenes'
                            }}
                        </button>
                    </div>
                </div>
            </div>
        </section>

        <!-- =================================================
             CURRENT GALLERY
        ================================================== -->

        <section class="admin-form-card">
            <div class="show-card-header">
                <div>
                    <p class="show-card-eyebrow">
                        Multimedia
                    </p>

                    <h2 class="show-card-title">
                        Imágenes de la galería
                    </h2>

                    <p class="show-card-description">
                        Imágenes disponibles para el carrusel de la
                        página pública del evento.
                    </p>
                </div>

                <div class="show-card-icon">
                    <ImagePlus
                        :size="17"
                        :stroke-width="2"
                    />
                </div>
            </div>

            <div class="show-card-body">
                <div
                    v-if="gallery.length > 0"
                    class="gallery-grid"
                >
                    <article
                        v-for="item in gallery"
                        :key="item.id"
                        class="gallery-item"
                        :class="{
                            'gallery-item-inactive':
                                !item.is_active,
                        }"
                    >
                        <div class="gallery-item-image">
                            <img
                                :src="`/storage/${item.image}`"
                                :alt="`Imagen ${item.id}`"
                            />

                            <span
                                class="gallery-item-status"
                                :class="{
                                    active: item.is_active,
                                }"
                            >
                                <span class="gallery-status-dot"></span>

                                {{
                                    item.is_active
                                        ? 'Activa'
                                        : 'Inactiva'
                                }}
                            </span>
                        </div>

                        <div class="gallery-item-footer">
                            <button
                                type="button"
                                class="gallery-item-toggle"
                                :class="{
                                    active: item.is_active,
                                }"
                                @click="toggleActive(item)"
                            >
                                <Check
                                    :size="13"
                                    :stroke-width="2"
                                />

                                {{
                                    item.is_active
                                        ? 'Desactivar'
                                        : 'Activar'
                                }}
                            </button>

                            <button
                                type="button"
                                class="gallery-item-delete"
                                :disabled="deletingId === item.id"
                                @click="deleteImage(item)"
                            >
                                <Trash2
                                    :size="13"
                                    :stroke-width="2"
                                />

                                {{
                                    deletingId === item.id
                                        ? 'Eliminando...'
                                        : 'Eliminar'
                                }}
                            </button>
                        </div>
                    </article>
                </div>

                <div
                    v-else
                    class="show-empty-block"
                >
                    <ImagePlus
                        :size="20"
                        :stroke-width="2"
                    />

                    <strong>
                        No hay imágenes en la galería
                    </strong>

                    <span>
                        Esta carrera todavía no tiene imágenes
                        configuradas para su carrusel.
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

/* =========================================================
   CARD HEADER
   ========================================================= */

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

/* =========================================================
   CARD BODY
   ========================================================= */

.show-card-body {
    padding: 23px 24px;
}

/* =========================================================
   SUMMARY
   ========================================================= */

.gallery-info-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 10px;
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

/* =========================================================
   UPLOAD
   ========================================================= */

.gallery-dropzone {
    display: flex;
    align-items: center;
    flex-direction: column;
    justify-content: center;
    min-height: 190px;
    padding: 25px;
    border: 1px dashed #d6e3e9;
    border-radius: 10px;
    background: #fbfcfd;
    cursor: pointer;
    text-align: center;
    transition:
        border-color 0.2s ease,
        background 0.2s ease;
}

.gallery-dropzone:hover,
.gallery-dropzone-active {
    border-color: #a9c7d5;
    background: #f6fafc;
}

.gallery-dropzone-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 44px;
    height: 44px;
    margin-bottom: 12px;
    border: 1px solid #dce9ef;
    border-radius: 9px;
    background: #f5f9fb;
    color: #718d9e;
}

.gallery-dropzone strong {
    margin-bottom: 5px;
    color: var(--sc-page-text);
    font-size: 13px;
    font-weight: 700;
}

.gallery-dropzone span {
    margin-bottom: 6px;
    color: #8999a4;
    font-size: 12px;
}

.gallery-dropzone small {
    color: #a0adb4;
    font-size: 10px;
}

/* =========================================================
   SELECTED
   ========================================================= */

.gallery-selected {
    margin-top: 22px;
    padding-top: 22px;
    border-top: 1px solid var(--sc-page-border);
}

.gallery-section-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 15px;
    margin-bottom: 14px;
}

.gallery-section-header h3 {
    margin: 0;
    color: var(--sc-page-text);
    font-size: 14px;
    font-weight: 700;
}

.gallery-clear-btn {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 0;
    border: 0;
    background: transparent;
    color: #8c777b;
    cursor: pointer;
    font-size: 11px;
    font-weight: 650;
}

.gallery-clear-btn:hover {
    color: #b15c66;
}

/* =========================================================
   PREVIEWS
   ========================================================= */

.gallery-preview-grid {
    display: grid;
    grid-template-columns: repeat(5, minmax(0, 1fr));
    gap: 12px;
}

.gallery-preview {
    overflow: hidden;
    border: 1px solid #e2eaee;
    border-radius: 9px;
    background: #ffffff;
}

.gallery-preview-image {
    position: relative;
    overflow: hidden;
    background: #f5f8fa;
}

.gallery-preview-image img {
    display: block;
    width: 100%;
    aspect-ratio: 1 / 1;
    object-fit: cover;
}

.gallery-preview-remove {
    position: absolute;
    top: 7px;
    right: 7px;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 25px;
    height: 25px;
    padding: 0;
    border: 1px solid rgba(255, 255, 255, 0.35);
    border-radius: 7px;
    background: rgba(28, 43, 52, 0.72);
    color: #ffffff;
    cursor: pointer;
}

.gallery-preview-name {
    overflow: hidden;
    padding: 8px 9px;
    color: #81919b;
    font-size: 10px;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.gallery-upload-actions {
    display: flex;
    justify-content: flex-end;
    margin-top: 16px;
}

/* =========================================================
   CURRENT GALLERY
   ========================================================= */

.gallery-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 16px;
}

.gallery-item {
    overflow: hidden;
    border: 1px solid var(--sc-page-border);
    border-radius: 10px;
    background: #ffffff;
}

.gallery-item-inactive {
    opacity: 0.62;
}

.gallery-item-image {
    position: relative;
    overflow: hidden;
    background: #f5f8fa;
}

.gallery-item-image img {
    display: block;
    width: 100%;
    aspect-ratio: 16 / 10;
    object-fit: cover;
}

.gallery-item-status {
    position: absolute;
    top: 10px;
    left: 10px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    min-height: 25px;
    padding: 0 8px;
    border: 1px solid rgba(255, 255, 255, 0.3);
    border-radius: 999px;
    background: rgba(27, 43, 52, 0.72);
    color: #ffffff;
    font-size: 10px;
    font-weight: 650;
}

.gallery-item-status.active {
    background: rgba(73, 117, 94, 0.86);
}

.gallery-status-dot {
    width: 5px;
    height: 5px;
    flex: 0 0 5px;
    border-radius: 50%;
    background: currentColor;
}

.gallery-item-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    padding: 11px 12px;
    border-top: 1px solid #edf1f3;
}

.gallery-item-toggle,
.gallery-item-delete {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 0;
    border: 0;
    background: transparent;
    cursor: pointer;
    font-size: 10px;
    font-weight: 650;
}

.gallery-item-toggle {
    color: #78909d;
}

.gallery-item-toggle.active {
    color: #628b78;
}

.gallery-item-toggle:hover {
    color: #557b8e;
}

.gallery-item-delete {
    color: #9a7d82;
}

.gallery-item-delete:hover {
    color: #b15c66;
}

.gallery-item-delete:disabled {
    opacity: 0.5;
    cursor: not-allowed;
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

@media (max-width: 1000px) {
    .gallery-info-grid {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }

    .gallery-grid {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }

    .gallery-preview-grid {
        grid-template-columns: repeat(4, minmax(0, 1fr));
    }
}

@media (max-width: 700px) {
    .admin-page {
        gap: 16px;
    }

    .show-card-header {
        padding: 18px;
    }

    .show-card-body {
        padding: 18px;
    }

    .gallery-info-grid {
        grid-template-columns: 1fr;
    }

    .gallery-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .gallery-preview-grid {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }

    .gallery-section-header {
        align-items: flex-start;
        flex-direction: column;
    }

    .gallery-upload-actions {
        justify-content: stretch;
    }

    .gallery-upload-actions .admin-btn {
        justify-content: center;
        width: 100%;
    }
}

@media (max-width: 500px) {
    .gallery-grid {
        grid-template-columns: 1fr;
    }

    .gallery-preview-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .gallery-item-footer {
        align-items: flex-start;
        flex-direction: column;
    }
}
</style>