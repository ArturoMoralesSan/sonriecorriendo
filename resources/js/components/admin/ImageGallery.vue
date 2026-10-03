<script setup>
import { computed, ref } from 'vue';
import {
    FileVideo,
    GripVertical,
    ImagePlus,
    Trash2,
    Upload,
} from 'lucide-vue-next';

const props = defineProps({
    modelValue: {
        type: Array,
        default: () => [],
    },

    label: {
        type: String,
        default: 'Galería de imágenes',
    },

    hint: {
        type: String,
        default: 'Arrastra las imágenes para cambiar su orden.',
    },

    accept: {
        type: String,
        default: 'image/jpeg,image/png,image/webp,image/jpg',
    },

    multiple: {
        type: Boolean,
        default: true,
    },

    maxSize: {
        type: Number,
        default: 10,
    },

    maxImages: {
        type: Number,
        default: 20,
    },
});

const emit = defineEmits([
    'update:modelValue',
    'add',
    'remove',
    'reorder',
    'error',
]);

const fileInput = ref(null);
const isDraggingFiles = ref(false);
const draggingIndex = ref(null);
const dragOverIndex = ref(null);

const items = computed(() => props.modelValue || []);

const canAddMore = computed(() => {
    return items.value.length < props.maxImages;
});

function openFileDialog() {
    if (!canAddMore.value) {
        return;
    }

    fileInput.value?.click();
}

function handleFileInput(event) {
    const files = Array.from(event.target.files || []);

    if (files.length) {
        addFiles(files);
    }

    event.target.value = '';
}

function handleDropFiles(event) {
    isDraggingFiles.value = false;

    const files = Array.from(event.dataTransfer?.files || []);

    if (!files.length) {
        return;
    }

    addFiles(files);
}

function isAcceptedFile(file) {
    const acceptedTypes = props.accept
        .split(',')
        .map((type) => type.trim().toLowerCase())
        .filter(Boolean);

    if (!acceptedTypes.length) {
        return true;
    }

    return acceptedTypes.some((acceptedType) => {
        if (acceptedType === '*/*') {
            return true;
        }

        if (acceptedType.endsWith('/*')) {
            const category = acceptedType.replace('/*', '');

            return file.type.toLowerCase().startsWith(`${category}/`);
        }

        return file.type.toLowerCase() === acceptedType;
    });
}

function isVideo(fileOrItem) {
    const file = fileOrItem?.file ?? fileOrItem;

    if (file?.type) {
        return file.type.toLowerCase().startsWith('video/');
    }

    const image = fileOrItem?.image ?? '';

    return /\.(mp4|webm|mov|m4v|avi|ogg)$/i.test(image);
}

function isImage(fileOrItem) {
    const file = fileOrItem?.file ?? fileOrItem;

    if (file?.type) {
        return file.type.toLowerCase().startsWith('image/');
    }

    const image = fileOrItem?.image ?? '';

    return /\.(jpg|jpeg|png|webp|gif|svg)$/i.test(image);
}

function addFiles(files) {
    const remainingSlots =
        props.maxImages - items.value.length;

    if (remainingSlots <= 0) {
        showError(
            `Solo puedes agregar un máximo de ${props.maxImages} archivos.`,
        );

        return;
    }

    const filesToAdd = files.slice(0, remainingSlots);

    if (files.length > remainingSlots) {
        showError(
            `Solo se agregaron ${remainingSlots} archivos porque el máximo es de ${props.maxImages}.`,
        );
    }

    const newItems = [];

    filesToAdd.forEach((file) => {
        if (!isAcceptedFile(file)) {
            showError(
                `${file.name} no tiene un formato permitido.`,
            );

            return;
        }

        const maxBytes = props.maxSize * 1024 * 1024;

        if (file.size > maxBytes) {
            showError(
                `${file.name} supera el tamaño máximo de ${props.maxSize} MB.`,
            );

            return;
        }

        const preview = URL.createObjectURL(file);

        newItems.push({
            id: null,
            image: null,
            file,
            preview,
            media_type: file.type,
            sort_order:
                items.value.length + newItems.length,
            is_active: true,
            isNew: true,
        });
    });

    if (!newItems.length) {
        return;
    }

    const updatedItems = [
        ...items.value,
        ...newItems,
    ].map((item, index) => ({
        ...item,
        sort_order: index,
    }));

    emit('update:modelValue', updatedItems);
    emit('add', newItems);
}

function removeItem(index) {
    const item = items.value[index];

    if (!item) {
        return;
    }

    /*
     * Los archivos nuevos usan Object URLs (blob:).
     *
     * Solo liberamos el Object URL cuando el usuario
     * realmente elimina el archivo.
     */
    if (
        item.preview &&
        item.file &&
        item.preview.startsWith('blob:')
    ) {
        URL.revokeObjectURL(item.preview);
    }

    const updatedItems = items.value
        .filter((_, itemIndex) => itemIndex !== index)
        .map((galleryItem, itemIndex) => ({
            ...galleryItem,
            sort_order: itemIndex,
        }));

    emit('update:modelValue', updatedItems);
    emit('remove', item);
}

function startDragging(index) {
    draggingIndex.value = index;
}

function handleDragEnter(index) {
    if (draggingIndex.value === null) {
        return;
    }

    if (draggingIndex.value === index) {
        return;
    }

    dragOverIndex.value = index;
}

function handleDragLeave(index) {
    if (dragOverIndex.value === index) {
        dragOverIndex.value = null;
    }
}

function handleDropItem(index) {
    if (
        draggingIndex.value === null ||
        draggingIndex.value === index
    ) {
        resetDragState();

        return;
    }

    const updatedItems = [...items.value];

    const draggedItem = updatedItems.splice(
        draggingIndex.value,
        1,
    )[0];

    updatedItems.splice(index, 0, draggedItem);

    const normalizedItems = updatedItems.map(
        (item, itemIndex) => ({
            ...item,
            sort_order: itemIndex,
        }),
    );

    emit('update:modelValue', normalizedItems);
    emit('reorder', normalizedItems);

    resetDragState();
}

function resetDragState() {
    draggingIndex.value = null;
    dragOverIndex.value = null;
}

function handleDragEnd() {
    resetDragState();
}

function showError(message) {
    emit('error', message);
}

function getMediaUrl(item) {
    /*
     * Archivo nuevo:
     * usa el preview temporal generado con
     * URL.createObjectURL().
     */
    if (item.preview) {
        return item.preview;
    }

    /*
     * Archivo existente.
     */
    if (item.image) {
        if (
            item.image.startsWith('http://') ||
            item.image.startsWith('https://') ||
            item.image.startsWith('/')
        ) {
            return item.image;
        }

        return `/storage/${item.image}`;
    }

    return '';
}

function getMediaAlt(item, index) {
    if (item.file?.name) {
        return item.file.name;
    }

    return `Archivo ${index + 1}`;
}

function getFileTypeLabel(item) {
    if (isVideo(item)) {
        return 'Video';
    }

    return 'Imagen';
}

function getAcceptedFormats() {
    const acceptedTypes = props.accept
        .split(',')
        .map((type) => type.trim().toLowerCase());

    const hasImages = acceptedTypes.some((type) =>
        type.startsWith('image/'),
    );

    const hasVideos = acceptedTypes.some((type) =>
        type.startsWith('video/'),
    );

    if (hasImages && hasVideos) {
        return `JPG, PNG, WEBP, MP4, WEBM o MOV · Máximo ${props.maxSize} MB por archivo`;
    }

    if (hasVideos) {
        return `MP4, WEBM o MOV · Máximo ${props.maxSize} MB por archivo`;
    }

    return `JPG, PNG o WEBP · Máximo ${props.maxSize} MB por archivo`;
}
</script>

<template>
    <div class="image-gallery">
        <div class="image-gallery-header">
            <div>
                <label class="image-gallery-label">
                    {{ label }}
                </label>

                <p class="image-gallery-hint">
                    {{ hint }}
                </p>
            </div>

            <div
                v-if="items.length"
                class="image-gallery-count"
            >
                {{ items.length }} / {{ maxImages }}
            </div>
        </div>

        <div
            v-if="items.length"
            class="image-gallery-grid"
        >
            <div
                v-for="(item, index) in items"
                :key="
                    item.id ??
                    item.preview ??
                    `image-${index}`
                "
                class="image-gallery-item"
                :class="{
                    'is-dragging':
                        draggingIndex === index,
                    'is-drag-over':
                        dragOverIndex === index,
                }"
                draggable="true"
                @dragstart="startDragging(index)"
                @dragenter.prevent="handleDragEnter(index)"
                @dragover.prevent
                @dragleave="handleDragLeave(index)"
                @drop.prevent="handleDropItem(index)"
                @dragend="handleDragEnd"
            >
                <div class="image-gallery-preview">
                    <img
                        v-if="
                            getMediaUrl(item) &&
                            isImage(item)
                        "
                        :src="getMediaUrl(item)"
                        :alt="getMediaAlt(item, index)"
                    >

                    <video
                        v-else-if="
                            getMediaUrl(item) &&
                            isVideo(item)
                        "
                        :src="getMediaUrl(item)"
                        muted
                        playsinline
                        preload="metadata"
                    ></video>

                    <div
                        v-else
                        class="image-gallery-no-preview"
                    >
                        <ImagePlus :size="28" />
                    </div>

                    <div class="image-gallery-overlay">
                        <div
                            class="image-gallery-drag-handle"
                        >
                            <GripVertical :size="20" />
                        </div>

                        <button
                            type="button"
                            class="image-gallery-delete"
                            title="Eliminar archivo"
                            @click.stop="removeItem(index)"
                        >
                            <Trash2 :size="17" />
                        </button>
                    </div>

                    <div class="image-gallery-position">
                        {{ index + 1 }}
                    </div>

                    <div
                        v-if="isVideo(item)"
                        class="image-gallery-type"
                    >
                        <FileVideo :size="12" />
                        Video
                    </div>

                    <div
                        v-if="item.isNew"
                        class="image-gallery-new"
                    >
                        Nueva
                    </div>
                </div>
            </div>
        </div>

        <div
            v-if="canAddMore"
            class="image-gallery-upload"
            :class="{
                'is-dragging-files':
                    isDraggingFiles,
            }"
            @click="openFileDialog"
            @dragenter.prevent="
                isDraggingFiles = true
            "
            @dragover.prevent="
                isDraggingFiles = true
            "
            @dragleave.prevent="
                isDraggingFiles = false
            "
            @drop.prevent="handleDropFiles"
        >
            <div class="image-gallery-upload-icon">
                <Upload :size="25" />
            </div>

            <div class="image-gallery-upload-content">
                <strong>
                    Arrastra tus archivos aquí
                </strong>

                <span>
                    o haz clic para seleccionar
                </span>

                <small>
                    {{ getAcceptedFormats() }}
                </small>
            </div>

            <button
                type="button"
                class="image-gallery-upload-button"
                @click.stop="openFileDialog"
            >
                Seleccionar archivos
            </button>

            <input
                ref="fileInput"
                type="file"
                :accept="accept"
                :multiple="multiple"
                hidden
                @change="handleFileInput"
            >
        </div>

        <div
            v-else
            class="image-gallery-limit"
        >
            Has alcanzado el máximo de
            {{ maxImages }} archivos.
        </div>

        <div
            v-if="!items.length"
            class="image-gallery-empty"
        >
            <div class="image-gallery-empty-icon">
                <ImagePlus :size="34" />
            </div>

            <strong>
                Aún no hay archivos
            </strong>

            <span>
                Agrega las imágenes o videos de la galería.
            </span>

            <button
                type="button"
                class="btn btn-primary image-gallery-empty-button"
                @click="openFileDialog"
            >
                <ImagePlus :size="17" />
                Agregar archivos
            </button>
        </div>
    </div>
</template>

<style scoped>
.image-gallery {
    width: 100%;
}

.image-gallery-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 20px;
    margin-bottom: 16px;
}

.image-gallery-label {
    display: block;
    margin-bottom: 5px;
    color: var(--sc-page-text, #172b4d);
    font-size: 15px;
    font-weight: 700;
}

.image-gallery-hint {
    margin: 0;
    color: var(--sc-page-muted, #7b8b97);
    font-size: 13px;
    line-height: 1.5;
}

.image-gallery-count {
    flex-shrink: 0;
    padding: 6px 10px;
    border: 1px solid var(--sc-page-border, #e5ecef);
    border-radius: 8px;
    background: #ffffff;
    color: var(--sc-page-muted, #7b8b97);
    font-size: 12px;
    font-weight: 700;
}

.image-gallery-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 14px;
    margin-bottom: 16px;
}

.image-gallery-item {
    position: relative;
    min-width: 0;
    border-radius: 12px;
    cursor: grab;
    transition:
        transform 0.18s ease,
        opacity 0.18s ease,
        box-shadow 0.18s ease;
}

.image-gallery-item:active {
    cursor: grabbing;
}

.image-gallery-item.is-dragging {
    opacity: 0.45;
    transform: scale(0.97);
}

.image-gallery-item.is-drag-over {
    transform: translateY(-3px);
}

.image-gallery-item.is-drag-over
    .image-gallery-preview {
    border-color: var(--sc-primary, #249edb);
    box-shadow:
        0 0 0 3px
        var(--sc-primary-light, #eaf6fc);
}

.image-gallery-preview {
    position: relative;
    aspect-ratio: 1 / 1;
    overflow: hidden;
    border: 1px solid var(--sc-page-border, #e5ecef);
    border-radius: 12px;
    background: #f3f6f8;
    transition:
        border-color 0.18s ease,
        box-shadow 0.18s ease;
}

.image-gallery-preview img,
.image-gallery-preview video {
    display: block;
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.image-gallery-preview video {
    background: #111820;
}

.image-gallery-no-preview {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    height: 100%;
    color: #a8b5bd;
}

.image-gallery-overlay {
    position: absolute;
    inset: 0;
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    padding: 9px;
    background: linear-gradient(
        to bottom,
        rgba(0, 0, 0, 0.38),
        transparent 35%,
        transparent 60%,
        rgba(0, 0, 0, 0.15)
    );
    opacity: 0;
    transition: opacity 0.18s ease;
}

.image-gallery-item:hover
    .image-gallery-overlay {
    opacity: 1;
}

.image-gallery-drag-handle {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 34px;
    height: 34px;
    border-radius: 8px;
    background: rgba(255, 255, 255, 0.92);
    color: #52616b;
    cursor: grab;
    box-shadow:
        0 2px 8px
        rgba(23, 43, 77, 0.12);
}

.image-gallery-delete {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 34px;
    height: 34px;
    padding: 0;
    border: 0;
    border-radius: 8px;
    background: rgba(255, 255, 255, 0.94);
    color: var(--sc-danger, #e83e4d);
    cursor: pointer;
    box-shadow:
        0 2px 8px
        rgba(23, 43, 77, 0.12);
    transition:
        background 0.15s ease,
        transform 0.15s ease;
}

.image-gallery-delete:hover {
    background: #ffffff;
    transform: scale(1.05);
}

.image-gallery-position {
    position: absolute;
    right: 9px;
    bottom: 9px;
    display: flex;
    align-items: center;
    justify-content: center;
    min-width: 27px;
    height: 27px;
    padding: 0 7px;
    border-radius: 7px;
    background: rgba(23, 43, 77, 0.78);
    color: #ffffff;
    font-size: 11px;
    font-weight: 700;
}

.image-gallery-new {
    position: absolute;
    left: 9px;
    bottom: 9px;
    padding: 5px 8px;
    border-radius: 6px;
    background: rgba(36, 158, 219, 0.94);
    color: #ffffff;
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.02em;
}

.image-gallery-type {
    position: absolute;
    left: 9px;
    top: 50%;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 6px 8px;
    border-radius: 7px;
    background: rgba(23, 43, 77, 0.78);
    color: #ffffff;
    font-size: 10px;
    font-weight: 700;
    transform: translateY(-50%);
}

.image-gallery-upload {
    display: flex;
    align-items: center;
    gap: 14px;
    min-height: 92px;
    padding: 18px 20px;
    border: 1.5px dashed #c8d8e0;
    border-radius: 12px;
    background: #fbfdfe;
    cursor: pointer;
    transition:
        border-color 0.18s ease,
        background 0.18s ease;
}

.image-gallery-upload:hover,
.image-gallery-upload.is-dragging-files {
    border-color: var(--sc-primary, #249edb);
    background: var(--sc-primary-light, #eaf6fc);
}

.image-gallery-upload-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    width: 46px;
    height: 46px;
    border-radius: 10px;
    background: var(--sc-primary-light, #eaf6fc);
    color: var(--sc-primary, #249edb);
}

.image-gallery-upload-content {
    display: flex;
    flex-direction: column;
    gap: 2px;
    flex: 1;
    min-width: 0;
}

.image-gallery-upload-content strong {
    color: var(--sc-page-text, #172b4d);
    font-size: 13px;
    font-weight: 700;
}

.image-gallery-upload-content span {
    color: var(--sc-page-muted, #7b8b97);
    font-size: 12px;
}

.image-gallery-upload-content small {
    margin-top: 3px;
    color: #9aabba;
    font-size: 11px;
}

.image-gallery-upload-button {
    flex-shrink: 0;
    min-height: 36px;
    padding: 0 14px;
    border: 1px solid #cfe0e8;
    border-radius: 8px;
    background: #ffffff;
    color: var(--sc-page-text, #172b4d);
    font-size: 12px;
    font-weight: 700;
    cursor: pointer;
    transition:
        border-color 0.15s ease,
        background 0.15s ease;
}

.image-gallery-upload-button:hover {
    border-color: var(--sc-primary, #249edb);
    background: var(--sc-primary-light, #eaf6fc);
}

.image-gallery-limit {
    padding: 12px 14px;
    border: 1px solid var(--sc-page-border, #e5ecef);
    border-radius: 9px;
    background: #fafcfd;
    color: var(--sc-page-muted, #7b8b97);
    font-size: 12px;
    text-align: center;
}

.image-gallery-empty {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    min-height: 230px;
    padding: 32px 20px;
    border: 1.5px dashed #c8d8e0;
    border-radius: 12px;
    background: #fbfdfe;
    text-align: center;
}

.image-gallery-empty-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 62px;
    height: 62px;
    margin-bottom: 13px;
    border-radius: 14px;
    background: var(--sc-primary-light, #eaf6fc);
    color: var(--sc-primary, #249edb);
}

.image-gallery-empty strong {
    margin-bottom: 4px;
    color: var(--sc-page-text, #172b4d);
    font-size: 14px;
}

.image-gallery-empty span {
    margin-bottom: 18px;
    color: var(--sc-page-muted, #7b8b97);
    font-size: 12px;
}

.image-gallery-empty-button {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 7px;
}

@media (max-width: 1100px) {
    .image-gallery-grid {
        grid-template-columns: repeat(
            3,
            minmax(0, 1fr)
        );
    }
}

@media (max-width: 760px) {
    .image-gallery-grid {
        grid-template-columns: repeat(
            2,
            minmax(0, 1fr)
        );
    }

    .image-gallery-upload {
        flex-wrap: wrap;
    }

    .image-gallery-upload-content {
        flex-basis: calc(100% - 62px);
    }

    .image-gallery-upload-button {
        width: 100%;
    }
}

@media (max-width: 480px) {
    .image-gallery-header {
        align-items: flex-start;
    }

    .image-gallery-grid {
        grid-template-columns: repeat(
            2,
            minmax(0, 1fr)
        );
        gap: 9px;
    }

    .image-gallery-overlay {
        opacity: 1;
    }

    .image-gallery-drag-handle,
    .image-gallery-delete {
        width: 30px;
        height: 30px;
    }

    .image-gallery-position {
        right: 7px;
        bottom: 7px;
    }

    .image-gallery-new {
        left: 7px;
        bottom: 7px;
    }
}
</style>