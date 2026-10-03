<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import {
    ArrowLeft,
    Check,
    ImagePlus,
    Trash2,
    Upload,
} from 'lucide-vue-next';
import { ref } from 'vue';
import Swal from 'sweetalert2';

import ImageGallery from '@/components/admin/ImageGallery.vue';
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

interface SelectedGalleryItem {
    id: number | null;
    image: string | null;
    file?: File;
    preview?: string;
    media_type?: string;
    sort_order: number;
    is_active: boolean;
    isNew?: boolean;
}

const props = defineProps<{
    race: Race;
    gallery: GalleryItem[];
}>();

const uploading = ref(false);
const deletingId = ref<number | null>(null);

const selectedGalleryItems = ref<SelectedGalleryItem[]>([]);

const form = useForm<{
    images: File[];
}>({
    images: [],
});

function syncSelectedFiles() {
    form.images = selectedGalleryItems.value
        .filter((item) => item.file instanceof File)
        .map((item) => item.file as File);
}

function handleGalleryUpdate(
    items: SelectedGalleryItem[],
) {
    selectedGalleryItems.value = items;
    syncSelectedFiles();
}

function handleGalleryAdd(
    items: SelectedGalleryItem[],
) {
    selectedGalleryItems.value = [
        ...selectedGalleryItems.value,
    ];

    syncSelectedFiles();
}

function clearSelected() {
    selectedGalleryItems.value = [];
    form.images = [];
}

function uploadImages() {
    syncSelectedFiles();

    if (!form.images.length || uploading.value) {
        return;
    }

    uploading.value = true;

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

async function deleteImage(gallery: GalleryItem) {
    if (deletingId.value !== null) {
        return;
    }

    const result = await Swal.fire({
        icon: 'warning',
        title: '¿Eliminar archivo?',
        text: 'Este archivo se eliminará definitivamente de la galería.',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        reverseButtons: true,
    });

    if (!result.isConfirmed) {
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

function isVideo(path: string): boolean {
    return /\.(mp4|webm|mov|m4v|ogg)$/i.test(path);
}

function mediaUrl(path: string): string {
    if (
        path.startsWith('http://') ||
        path.startsWith('https://') ||
        path.startsWith('/')
    ) {
        return path;
    }

    return `/storage/${path}`;
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
                    Administra las imágenes y videos que se mostrarán
                    en el carrusel de la página pública del evento.
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
                        Galería multimedia asociada a esta carrera.
                        Los archivos activos estarán disponibles para
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
                            Total de archivos
                        </span>

                        <strong>
                            {{ gallery.length }}
                        </strong>
                    </div>

                    <div class="show-info-box">
                        <span>
                            Archivos activos
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
                            Archivos inactivos
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
                        Agregar imágenes y videos
                    </h2>

                    <p class="show-card-description">
                        Selecciona una o varias imágenes o videos para
                        agregarlos a la galería de esta carrera.
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
                <ImageGallery
                    :model-value="selectedGalleryItems"
                    label="Galería del kit"
                    hint="Agrega las imágenes y videos del kit y arrástralos para cambiar su orden."
                    accept="image/jpeg,image/png,image/webp,image/jpg,video/mp4,video/webm,video/quicktime"
                    :max-images="20"
                    :max-size="50"
                    @update:model-value="handleGalleryUpdate"
                    @add="handleGalleryAdd"
                />

                <div
                    v-if="selectedGalleryItems.length"
                    class="gallery-upload-actions"
                >
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
                                : `Subir ${selectedGalleryItems.length} archivos`
                        }}
                    </button>
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
                        Archivos de la galería
                    </h2>

                    <p class="show-card-description">
                        Imágenes y videos disponibles para el carrusel
                        de la página pública del evento.
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
                            <video
                                v-if="isVideo(item.image)"
                                :src="mediaUrl(item.image)"
                                muted
                                controls
                                playsinline
                                preload="metadata"
                            ></video>

                            <img
                                v-else
                                :src="mediaUrl(item.image)"
                                :alt="`Imagen ${item.id}`"
                            />

                            <span
                                class="gallery-item-type"
                            >
                                {{
                                    isVideo(item.image)
                                        ? 'Video'
                                        : 'Imagen'
                                }}
                            </span>

                            <span
                                class="gallery-item-status"
                                :class="{
                                    active: item.is_active,
                                }"
                            >
                                <span
                                    class="gallery-status-dot"
                                ></span>

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
                                :disabled="
                                    deletingId === item.id
                                "
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
                        No hay archivos en la galería
                    </strong>

                    <span>
                        Esta carrera todavía no tiene imágenes o videos
                        configurados para su carrusel.
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
   UPLOAD ACTION
   ========================================================= */

.gallery-upload-actions {
    display: flex;
    justify-content: flex-end;
    margin-top: 18px;
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

.gallery-item-image img,
.gallery-item-image video {
    display: block;
    width: 100%;
    aspect-ratio: 16 / 10;
    object-fit: cover;
}

.gallery-item-image video {
    background: #111820;
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

.gallery-item-type {
    position: absolute;
    right: 10px;
    top: 10px;
    display: inline-flex;
    align-items: center;
    min-height: 25px;
    padding: 0 8px;
    border-radius: 7px;
    background: rgba(27, 43, 52, 0.72);
    color: #ffffff;
    font-size: 10px;
    font-weight: 650;
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

    .gallery-item-footer {
        align-items: flex-start;
        flex-direction: column;
    }
}
</style>