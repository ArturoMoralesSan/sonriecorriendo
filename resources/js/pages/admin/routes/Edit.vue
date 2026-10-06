<script setup lang="ts">
import {
    Head,
    Link,
    useForm,
} from '@inertiajs/vue3';
import {
    ArrowLeft,
    Check,
    ImagePlus,
    Save,
    Video,
} from 'lucide-vue-next';
import { ref } from 'vue';

import ImageGallery from '@/components/admin/ImageGallery.vue';
import admin from '@/routes/admin';

interface RouteMedia {
    id: number;
    route_id: number;
    type: 'image' | 'video';
    file: string;
    title: string | null;
    sort_order: number;
}

interface RouteItem {
    id: number;
    title: string;
    description: string | null;
    is_active: boolean;
    sort_order: number;
    media?: RouteMedia[];
}

interface SelectedMediaItem {
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
    route: RouteItem;
}>();

function getMediaType(file: string): 'image' | 'video' {
    const extension = file
        .split('?')[0]
        .split('.')
        .pop()
        ?.toLowerCase();

    const videoExtensions = [
        'mp4',
        'webm',
        'mov',
        'm4v',
        'ogg',
    ];

    return videoExtensions.includes(extension ?? '')
        ? 'video'
        : 'image';
}

const selectedMedia = ref<SelectedMediaItem[]>(
    (props.route.media ?? []).map((media) => ({
        id: media.id,
        image: media.file,
        preview: `/storage/${media.file}`,
        media_type: getMediaType(media.file),
        sort_order: media.sort_order,
        is_active: true,
        isNew: false,
    })),
);

const originalMediaIds = ref<number[]>(
    (props.route.media ?? []).map(
        (media) => media.id,
    ),
);

const form = useForm<{
    title: string;
    description: string;
    is_active: boolean;
    sort_order: number;
    media: Array<{
        file: File;
        title: string | null;
        sort_order: number;
    }>;
    remove_media: number[];
    media_order: Array<{
        id: number;
        sort_order: number;
    }>;
}>({
    title: props.route.title ?? '',
    description: props.route.description ?? '',
    is_active: props.route.is_active ?? true,
    sort_order: props.route.sort_order ?? 0,
    media: [],
    remove_media: [],
    media_order: [],
});

function handleGalleryUpdate(
    items: SelectedMediaItem[],
): void {
    selectedMedia.value = items;
}

function handleGalleryAdd(
    items: SelectedMediaItem[],
): void {
    selectedMedia.value = [
        ...items,
    ];
}

function syncMedia(): void {
    form.media = selectedMedia.value
        .filter(
            (item) => item.file instanceof File,
        )
        .map((item, index) => ({
            file: item.file as File,
            title: null,
            sort_order:
                typeof item.sort_order === 'number'
                    ? item.sort_order
                    : index,
        }));
}

function syncRemovedMedia(): void {
    form.remove_media = originalMediaIds.value.filter(
        (id) =>
            !selectedMedia.value.some(
                (item) => item.id === id,
            ),
    );
}

function syncMediaOrder(): void {
    form.media_order = selectedMedia.value
        .filter(
            (item): item is SelectedMediaItem & {
                id: number;
            } =>
                item.id !== null &&
                typeof item.id === 'number',
        )
        .map((item, index) => ({
            id: item.id,
            sort_order:
                typeof item.sort_order === 'number'
                    ? item.sort_order
                    : index,
        }));
}

function submit(): void {
    syncMedia();
    syncRemovedMedia();
    syncMediaOrder();

    form
        .transform((data) => ({
            ...data,
            _method: 'PUT',
        }))
        .post(
            admin.routes.update(props.route.id).url,
            {
                forceFormData: true,
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
                title: 'Rutas',
                href: admin.routes.index(),
            },
            {
                title: 'Editar ruta',
                href: admin.routes.index(),
            },
        ],
    },
});
</script>

<template>
    <Head title="Editar ruta" />

    <div class="admin-page">
        <!-- =================================================
             HEADER
        ================================================== -->

        <header class="admin-page-header">
            <div>
                <p class="admin-page-eyebrow">
                    Contenido
                </p>

                <h1 class="admin-page-title">
                    Editar ruta
                </h1>

                <p class="admin-page-subtitle">
                    Actualiza la información y el contenido
                    multimedia de esta ruta.
                </p>
            </div>

            <div class="admin-page-header-actions">
                <Link
                    :href="admin.routes.index().url"
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
             GENERAL
        ================================================== -->

        <section class="admin-form-card">
            <div class="show-card-header">
                <div>
                    <p class="show-card-eyebrow">
                        Información
                    </p>

                    <h2 class="show-card-title">
                        Datos de la ruta
                    </h2>

                    <p class="show-card-description">
                        Actualiza el nombre, descripción y estado
                        de publicación de esta ruta.
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
                <div class="form-grid">
                    <div class="form-field form-field-full">
                        <label
                            for="title"
                            class="form-label"
                        >
                            Título
                            <span class="required">
                                *
                            </span>
                        </label>

                        <input
                            id="title"
                            v-model="form.title"
                            type="text"
                            class="form-input"
                            placeholder="Ej. Ruta 5K Feria Nacional"
                            maxlength="255"
                        />

                        <p
                            v-if="form.errors.title"
                            class="form-error"
                        >
                            {{ form.errors.title }}
                        </p>
                    </div>

                    <div class="form-field form-field-full">
                        <label
                            for="description"
                            class="form-label"
                        >
                            Descripción
                        </label>

                        <textarea
                            id="description"
                            v-model="form.description"
                            class="form-textarea"
                            rows="5"
                            placeholder="Describe brevemente esta ruta..."
                        ></textarea>

                        <p
                            v-if="form.errors.description"
                            class="form-error"
                        >
                            {{ form.errors.description }}
                        </p>
                    </div>

                    <div class="form-field">
                        <label
                            for="sort_order"
                            class="form-label"
                        >
                            Orden
                        </label>

                        <input
                            id="sort_order"
                            v-model.number="form.sort_order"
                            type="number"
                            min="0"
                            class="form-input"
                        />

                        <p class="form-hint">
                            Define la posición en la que aparecerá
                            esta ruta.
                        </p>

                        <p
                            v-if="form.errors.sort_order"
                            class="form-error"
                        >
                            {{ form.errors.sort_order }}
                        </p>
                    </div>

                    <div class="form-field">
                        <label class="form-label">
                            Estado
                        </label>

                        <label class="status-switch">
                            <input
                                v-model="form.is_active"
                                type="checkbox"
                            />

                            <span class="status-switch-control">
                                <span
                                    class="status-switch-check"
                                >
                                    <Check
                                        :size="11"
                                        :stroke-width="2.5"
                                    />
                                </span>
                            </span>

                            <span class="status-switch-text">
                                <strong>
                                    Ruta activa
                                </strong>

                                <small>
                                    Disponible para mostrarse
                                </small>
                            </span>
                        </label>

                        <p
                            v-if="form.errors.is_active"
                            class="form-error"
                        >
                            {{ form.errors.is_active }}
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- =================================================
             MULTIMEDIA
        ================================================== -->

        <section class="admin-form-card">
            <div class="show-card-header">
                <div>
                    <p class="show-card-eyebrow">
                        Multimedia
                    </p>

                    <h2 class="show-card-title">
                        Fotos y videos
                    </h2>

                    <p class="show-card-description">
                        Consulta el contenido multimedia relacionado
                        con esta ruta.
                    </p>
                </div>

                <div class="show-card-icon">
                    <Video
                        :size="17"
                        :stroke-width="2"
                    />
                </div>
            </div>

            <div class="show-card-body">
                <ImageGallery
                    :model-value="selectedMedia"
                    label="Multimedia de la ruta"
                    hint="Aquí se muestran las fotografías y videos asociados a esta ruta."
                    accept="image/jpeg,image/png,image/webp,image/jpg,video/mp4,video/webm,video/quicktime"
                    :max-images="20"
                    :max-size="50"
                    @update:model-value="handleGalleryUpdate"
                    @add="handleGalleryAdd"
                />

                <div
                    v-if="form.errors['media']"
                    class="form-error media-error"
                >
                    {{ form.errors['media'] }}
                </div>
            </div>
        </section>

        <!-- =================================================
             ACTIONS
        ================================================== -->

        <div class="form-actions">
            <Link
                :href="admin.routes.index().url"
                class="admin-btn admin-btn-secondary"
            >
                <ArrowLeft
                    :size="14"
                    :stroke-width="2"
                />

                Cancelar
            </Link>

            <button
                type="button"
                class="admin-btn admin-btn-primary"
                :disabled="form.processing"
                @click="submit"
            >
                <Save
                    :size="14"
                    :stroke-width="2"
                />

                {{
                    form.processing
                        ? 'Guardando...'
                        : 'Guardar cambios'
                }}
            </button>
        </div>
    </div>
</template>

<style scoped>
/* =========================================================
   PAGE
   ========================================================= */

.admin-page {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

/* =========================================================
   CARD
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
   FORM
   ========================================================= */

.form-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 20px;
}

.form-field {
    min-width: 0;
}

.form-field-full {
    grid-column: 1 / -1;
}

.form-label {
    display: block;
    margin-bottom: 7px;
    color: var(--sc-page-text);
    font-size: 12px;
    font-weight: 700;
}

.required {
    color: #d94c9a;
}

.form-input,
.form-textarea {
    display: block;
    width: 100%;
    border: 1px solid #dce7ec;
    border-radius: 9px;
    background: #ffffff;
    color: var(--sc-page-text);
    font-family: inherit;
    font-size: 13px;
    outline: none;
    transition:
        border-color 0.15s ease,
        box-shadow 0.15s ease;
}

.form-input {
    min-height: 42px;
    padding: 0 12px;
}

.form-textarea {
    min-height: 120px;
    padding: 11px 12px;
    resize: vertical;
    line-height: 1.5;
}

.form-input:focus,
.form-textarea:focus {
    border-color: #a9d9f2;
    box-shadow: 0 0 0 3px rgba(36, 158, 219, 0.08);
}

.form-input::placeholder,
.form-textarea::placeholder {
    color: #a3b0b8;
}

.form-hint {
    margin: 6px 0 0;
    color: #91a1ac;
    font-size: 11px;
    line-height: 1.4;
}

.form-error {
    margin: 6px 0 0;
    color: #c55b66;
    font-size: 11px;
    line-height: 1.4;
}

.media-error {
    margin-top: 12px;
}

/* =========================================================
   STATUS
   ========================================================= */

.status-switch {
    display: flex;
    align-items: center;
    gap: 11px;
    min-height: 42px;
    padding: 7px 10px;
    border: 1px solid #dce7ec;
    border-radius: 9px;
    background: #ffffff;
    cursor: pointer;
}

.status-switch input {
    position: absolute;
    width: 1px;
    height: 1px;
    opacity: 0;
    pointer-events: none;
}

.status-switch-control {
    display: flex;
    align-items: center;
    width: 34px;
    height: 20px;
    flex: 0 0 34px;
    padding: 2px;
    border-radius: 999px;
    background: #cbd5db;
    transition: background 0.15s ease;
}

.status-switch-check {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 16px;
    height: 16px;
    border-radius: 50%;
    background: #ffffff;
    color: transparent;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.14);
    transition:
        transform 0.15s ease,
        color 0.15s ease;
}

.status-switch input:checked
    + .status-switch-control {
    background: #249edb;
}

.status-switch input:checked
    + .status-switch-control
    .status-switch-check {
    transform: translateX(14px);
    color: #249edb;
}

.status-switch-text {
    display: flex;
    flex-direction: column;
    gap: 1px;
}

.status-switch-text strong {
    color: var(--sc-page-text);
    font-size: 12px;
    font-weight: 700;
}

.status-switch-text small {
    color: #91a1ac;
    font-size: 10px;
}

/* =========================================================
   ACTIONS
   ========================================================= */

.form-actions {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 10px;
    padding-bottom: 4px;
}

/* =========================================================
   RESPONSIVE
   ========================================================= */

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

    .form-grid {
        grid-template-columns: 1fr;
        gap: 17px;
    }

    .form-field-full {
        grid-column: auto;
    }

    .form-actions {
        flex-direction: column-reverse;
        align-items: stretch;
    }

    .form-actions .admin-btn {
        justify-content: center;
        width: 100%;
    }
}
</style>
