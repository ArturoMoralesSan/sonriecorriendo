```vue
<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import {
    ArrowLeft,
    Image,
    Save,
    Upload,
    X,
} from 'lucide-vue-next';
import { ref } from 'vue';

import admin from '@/routes/admin';

const form = useForm({
    name: '',
    page: 'home',
    title: '',
    description: '',
    image: null as File | null,
    mobile_image: null as File | null,
    button_text: '',
    button_url: '',
    is_active: true,
    sort_order: 0,
});

const imageInput = ref<HTMLInputElement | null>(null);
const mobileImageInput = ref<HTMLInputElement | null>(null);

const imagePreview = ref<string | null>(null);
const mobileImagePreview = ref<string | null>(null);

const handleImageChange = (
    event: Event,
): void => {
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0] ?? null;

    form.image = file;

    if (imagePreview.value) {
        URL.revokeObjectURL(imagePreview.value);
    }

    imagePreview.value = file
        ? URL.createObjectURL(file)
        : null;
};

const handleMobileImageChange = (
    event: Event,
): void => {
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0] ?? null;

    form.mobile_image = file;

    if (mobileImagePreview.value) {
        URL.revokeObjectURL(mobileImagePreview.value);
    }

    mobileImagePreview.value = file
        ? URL.createObjectURL(file)
        : null;
};

const removeImage = (): void => {
    form.image = null;
    imagePreview.value = null;

    if (imageInput.value) {
        imageInput.value.value = '';
    }
};

const removeMobileImage = (): void => {
    form.mobile_image = null;
    mobileImagePreview.value = null;

    if (mobileImageInput.value) {
        mobileImageInput.value.value = '';
    }
};

const submit = (): void => {
    form.post(
        admin.banners.store().url,
        {
            forceFormData: true,
        },
    );
};

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Panel',
                href: admin.dashboard(),
            },
            {
                title: 'Banners',
                href: admin.banners.index(),
            },
            {
                title: 'Nuevo banner',
                href: admin.banners.create(),
            },
        ],
    },
});
</script>

<template>
    <Head title="Nuevo banner" />

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
                    Nuevo banner
                </h1>

                <p class="admin-page-subtitle">
                    Crea un nuevo banner promocional para mostrarlo
                    en el sitio.
                </p>
            </div>

            <div class="admin-page-header-actions">
                <Link
                    :href="admin.banners.index().url"
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
             FORM
        ================================================== -->

        <div class="admin-form-card">
            <form
                class="admin-form"
                @submit.prevent="submit"
            >
                <!-- =================================================
                     GENERAL
                ================================================== -->

                <div class="admin-form-section">
                    <div class="admin-form-section-header">
                        <div>
                            <h2 class="admin-form-section-title">
                                Información general
                            </h2>

                            <p class="admin-form-section-description">
                                Define la información que tendrá el banner.
                            </p>
                        </div>
                    </div>

                    <div class="admin-form-grid">
                        <!-- NAME -->

                        <div class="admin-form-group">
                            <label
                                for="name"
                                class="admin-form-label"
                            >
                                Nombre
                            </label>

                            <input
                                id="name"
                                v-model="form.name"
                                type="text"
                                class="admin-form-input"
                                :class="{
                                    'has-error': form.errors.name,
                                }"
                                placeholder="Ej. Banner principal"
                            />

                            <p class="admin-form-help">
                                Nombre interno para identificar el banner.
                            </p>

                            <p
                                v-if="form.errors.name"
                                class="admin-form-error"
                            >
                                {{ form.errors.name }}
                            </p>
                        </div>

                        <!-- PAGE -->

                        <div class="admin-form-group">
                            <label
                                for="page"
                                class="admin-form-label"
                            >
                                Página
                            </label>

                            <select
                                id="page"
                                v-model="form.page"
                                class="admin-form-input admin-form-select"
                                :class="{
                                    'has-error': form.errors.page,
                                }"
                            >
                                <option value="home">
                                    Home
                                </option>

                                <option value="galeria">
                                    Galería
                                </option>

                                <option value="clubes">
                                    Clubs
                                </option>

                                <option value="productos">
                                    Productos
                                </option>

                                <option value="resultados">
                                    Resultados
                                </option>

                                <option value="eventos">
                                    Eventos
                                </option>
                            </select>

                            <p class="admin-form-help">
                                Página donde se mostrará el banner.
                            </p>

                            <p
                                v-if="form.errors.page"
                                class="admin-form-error"
                            >
                                {{ form.errors.page }}
                            </p>
                        </div>

                        <!-- TITLE -->

                        <div class="admin-form-group">
                            <label
                                for="title"
                                class="admin-form-label"
                            >
                                Título
                            </label>

                            <input
                                id="title"
                                v-model="form.title"
                                type="text"
                                class="admin-form-input"
                                :class="{
                                    'has-error': form.errors.title,
                                }"
                                placeholder="Ej. Corre con nosotros"
                            />

                            <p
                                v-if="form.errors.title"
                                class="admin-form-error"
                            >
                                {{ form.errors.title }}
                            </p>
                        </div>

                        <!-- SORT -->

                        <div class="admin-form-group">
                            <label
                                for="sort_order"
                                class="admin-form-label"
                            >
                                Orden
                            </label>

                            <input
                                id="sort_order"
                                v-model.number="form.sort_order"
                                type="number"
                                min="0"
                                class="admin-form-input"
                                :class="{
                                    'has-error': form.errors.sort_order,
                                }"
                            />

                            <p class="admin-form-help">
                                Los banners con menor número aparecerán primero.
                            </p>

                            <p
                                v-if="form.errors.sort_order"
                                class="admin-form-error"
                            >
                                {{ form.errors.sort_order }}
                            </p>
                        </div>
                    </div>

                    <!-- DESCRIPTION -->

                    <div class="admin-form-group">
                        <label
                            for="description"
                            class="admin-form-label"
                        >
                            Descripción
                        </label>

                        <textarea
                            id="description"
                            v-model="form.description"
                            rows="4"
                            class="admin-form-input admin-form-textarea"
                            :class="{
                                'has-error': form.errors.description,
                            }"
                            placeholder="Escribe la descripción del banner..."
                        ></textarea>

                        <p
                            v-if="form.errors.description"
                            class="admin-form-error"
                        >
                            {{ form.errors.description }}
                        </p>
                    </div>
                </div>

                <!-- =================================================
                     IMAGES
                ================================================== -->

                <div class="admin-form-section">
                    <div class="admin-form-section-header">
                        <div>
                            <h2 class="admin-form-section-title">
                                Imágenes
                            </h2>

                            <p class="admin-form-section-description">
                                Agrega la imagen principal y una versión
                                opcional para dispositivos móviles.
                            </p>
                        </div>
                    </div>

                    <div class="admin-image-grid">
                        <!-- IMAGE -->

                        <div class="admin-image-upload">
                            <label class="admin-form-label">
                                Imagen principal
                            </label>

                            <input
                                ref="imageInput"
                                id="image"
                                type="file"
                                accept=".jpg,.jpeg,.png,.webp"
                                class="admin-file-input"
                                @change="handleImageChange"
                            />

                            <div
                                v-if="imagePreview"
                                class="admin-image-preview"
                            >
                                <img
                                    :src="imagePreview"
                                    alt="Vista previa de la imagen"
                                />

                                <button
                                    type="button"
                                    class="admin-image-remove"
                                    title="Eliminar imagen"
                                    @click="removeImage"
                                >
                                    <X
                                        :size="15"
                                        :stroke-width="2"
                                    />
                                </button>
                            </div>

                            <label
                                v-else
                                for="image"
                                class="admin-image-dropzone"
                            >
                                <div class="admin-image-upload-icon">
                                    <Upload
                                        :size="20"
                                        :stroke-width="1.8"
                                    />
                                </div>

                                <strong>
                                    Seleccionar imagen
                                </strong>

                                <span>
                                    JPG, PNG o WEBP · Máximo 5 MB
                                </span>
                            </label>

                            <p class="admin-form-help">
                                La imagen principal es obligatoria.
                            </p>

                            <p
                                v-if="form.errors.image"
                                class="admin-form-error"
                            >
                                {{ form.errors.image }}
                            </p>
                        </div>

                        <!-- MOBILE IMAGE -->

                        <div class="admin-image-upload">
                            <label class="admin-form-label">
                                Imagen móvil
                            </label>

                            <input
                                ref="mobileImageInput"
                                id="mobile_image"
                                type="file"
                                accept=".jpg,.jpeg,.png,.webp"
                                class="admin-file-input"
                                @change="handleMobileImageChange"
                            />

                            <div
                                v-if="mobileImagePreview"
                                class="admin-image-preview"
                            >
                                <img
                                    :src="mobileImagePreview"
                                    alt="Vista previa de la imagen móvil"
                                />

                                <button
                                    type="button"
                                    class="admin-image-remove"
                                    title="Eliminar imagen móvil"
                                    @click="removeMobileImage"
                                >
                                    <X
                                        :size="15"
                                        :stroke-width="2"
                                    />
                                </button>
                            </div>

                            <label
                                v-else
                                for="mobile_image"
                                class="admin-image-dropzone"
                            >
                                <div class="admin-image-upload-icon">
                                    <Image
                                        :size="20"
                                        :stroke-width="1.8"
                                    />
                                </div>

                                <strong>
                                    Seleccionar imagen móvil
                                </strong>

                                <span>
                                    JPG, PNG o WEBP · Máximo 5 MB
                                </span>
                            </label>

                            <p class="admin-form-help">
                                Opcional. Se utilizará en dispositivos móviles.
                            </p>

                            <p
                                v-if="form.errors.mobile_image"
                                class="admin-form-error"
                            >
                                {{ form.errors.mobile_image }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- =================================================
                     BUTTON
                ================================================== -->

                <div class="admin-form-section">
                    <div class="admin-form-section-header">
                        <div>
                            <h2 class="admin-form-section-title">
                                Botón
                            </h2>

                            <p class="admin-form-section-description">
                                Puedes agregar un botón para dirigir al usuario
                                a otra sección del sitio.
                            </p>
                        </div>
                    </div>

                    <div class="admin-form-grid">
                        <!-- BUTTON TEXT -->

                        <div class="admin-form-group">
                            <label
                                for="button_text"
                                class="admin-form-label"
                            >
                                Texto del botón
                            </label>

                            <input
                                id="button_text"
                                v-model="form.button_text"
                                type="text"
                                class="admin-form-input"
                                :class="{
                                    'has-error': form.errors.button_text,
                                }"
                                placeholder="Ej. Ver carrera"
                            />

                            <p
                                v-if="form.errors.button_text"
                                class="admin-form-error"
                            >
                                {{ form.errors.button_text }}
                            </p>
                        </div>

                        <!-- BUTTON URL -->

                        <div class="admin-form-group">
                            <label
                                for="button_url"
                                class="admin-form-label"
                            >
                                URL del botón
                            </label>

                            <input
                                id="button_url"
                                v-model="form.button_url"
                                type="text"
                                class="admin-form-input"
                                :class="{
                                    'has-error': form.errors.button_url,
                                }"
                                placeholder="https://..."
                            />

                            <p
                                v-if="form.errors.button_url"
                                class="admin-form-error"
                            >
                                {{ form.errors.button_url }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- =================================================
                     STATUS
                ================================================== -->

                <div class="admin-form-status">
                    <div class="admin-form-status-content">
                        <div class="admin-form-status-icon">
                            <Image
                                :size="16"
                                :stroke-width="2"
                            />
                        </div>

                        <div>
                            <p class="admin-form-status-title">
                                Estado del banner
                            </p>

                            <p class="admin-form-status-description">
                                Los banners activos estarán disponibles
                                para mostrarse en la página configurada.
                            </p>
                        </div>

                        <label class="admin-switch">
                            <input
                                v-model="form.is_active"
                                type="checkbox"
                            />

                            <span class="admin-switch-slider"></span>

                            <span class="admin-switch-label">
                                {{
                                    form.is_active
                                        ? 'Activo'
                                        : 'Inactivo'
                                }}
                            </span>
                        </label>
                    </div>
                </div>

                <!-- =================================================
                     ACTIONS
                ================================================== -->

                <div class="admin-form-actions">
                    <Link
                        :href="admin.banners.index().url"
                        class="admin-btn admin-btn-secondary"
                    >
                        <ArrowLeft
                            :size="14"
                            :stroke-width="2"
                        />

                        Cancelar
                    </Link>

                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="admin-btn admin-btn-primary"
                    >
                        <span
                            v-if="form.processing"
                            class="admin-btn-loader"
                        ></span>

                        <Save
                            v-else
                            :size="14"
                            :stroke-width="2"
                        />

                        {{
                            form.processing
                                ? 'Guardando...'
                                : 'Guardar banner'
                        }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<style scoped>
.admin-form-section {
    padding-bottom: 28px;
    margin-bottom: 28px;
    border-bottom: 1px solid #e8eef3;
}

.admin-form-section:last-of-type {
    margin-bottom: 0;
    padding-bottom: 0;
    border-bottom: 0;
}

.admin-form-section-header {
    margin-bottom: 20px;
}

.admin-form-section-title {
    margin: 0;
    color: var(--sc-page-text);
    font-size: 15px;
    font-weight: 700;
}

.admin-form-section-description {
    margin: 5px 0 0;
    color: var(--sc-page-text-secondary);
    font-size: 11px;
    line-height: 1.5;
}

.admin-form-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 18px;
}

.admin-form-textarea {
    min-height: 110px;
    resize: vertical;
}

.admin-form-select {
    appearance: auto;
    cursor: pointer;
}

.admin-image-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 18px;
}

.admin-image-upload {
    min-width: 0;
}

.admin-file-input {
    display: none;
}

.admin-image-dropzone {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    min-height: 190px;
    padding: 22px;
    border: 1.5px dashed #b9d9ea;
    border-radius: 14px;
    background: #f8fcfe;
    color: var(--sc-page-text-secondary);
    cursor: pointer;
    text-align: center;
    transition:
        border-color 0.2s ease,
        background 0.2s ease;
}

.admin-image-dropzone:hover {
    border-color: var(--sc-page-blue);
    background: var(--sc-page-blue-light);
}

.admin-image-dropzone strong {
    margin-top: 10px;
    color: var(--sc-page-text);
    font-size: 12px;
    font-weight: 700;
}

.admin-image-dropzone span {
    margin-top: 5px;
    color: var(--sc-page-text-secondary);
    font-size: 10px;
}

.admin-image-upload-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 42px;
    height: 42px;
    border-radius: 11px;
    background: var(--sc-page-blue-light);
    color: var(--sc-page-blue);
}

.admin-image-preview {
    position: relative;
    overflow: hidden;
    min-height: 190px;
    border: 1px solid #dce8ef;
    border-radius: 14px;
    background: #f4f8fa;
}

.admin-image-preview img {
    display: block;
    width: 100%;
    height: 190px;
    object-fit: cover;
}

.admin-image-remove {
    position: absolute;
    top: 9px;
    right: 9px;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 29px;
    height: 29px;
    border: 0;
    border-radius: 8px;
    background: rgba(23, 43, 77, 0.8);
    color: #fff;
    cursor: pointer;
}

.admin-switch {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    margin-left: auto;
    cursor: pointer;
}

.admin-switch input {
    position: absolute;
    opacity: 0;
    pointer-events: none;
}

.admin-switch-slider {
    position: relative;
    width: 38px;
    height: 21px;
    border-radius: 999px;
    background: #cbd5df;
    transition: background 0.2s ease;
}

.admin-switch-slider::after {
    content: '';
    position: absolute;
    top: 3px;
    left: 3px;
    width: 15px;
    height: 15px;
    border-radius: 50%;
    background: #fff;
    box-shadow: 0 1px 3px rgba(23, 43, 77, 0.2);
    transition: transform 0.2s ease;
}

.admin-switch input:checked + .admin-switch-slider {
    background: var(--sc-page-blue);
}

.admin-switch input:checked + .admin-switch-slider::after {
    transform: translateX(17px);
}

.admin-switch-label {
    color: var(--sc-page-text);
    font-size: 11px;
    font-weight: 700;
}

@media (max-width: 768px) {
    .admin-form-grid,
    .admin-image-grid {
        grid-template-columns: 1fr;
    }

    .admin-switch {
        margin-left: 0;
    }
}
</style>