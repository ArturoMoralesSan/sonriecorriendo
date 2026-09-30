<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import {
    ArrowLeft,
    ImagePlus,
    Package,
    Save,
    X,
} from 'lucide-vue-next';
import { ref } from 'vue';

import admin from '@/routes/admin';

const form = useForm({
    name: '',
    slug: '',
    description: '',
    image: null as File | null,
    type: '',
    year: null as number | null,
    price: 0,
    stock: 0,
    is_active: true,
});

const imagePreview = ref<string | null>(null);

const generateSlug = () => {
    if (!form.name.trim()) {
        return;
    }

    form.slug = form.name
        .toLowerCase()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-+|-+$/g, '');
};

const handleImageChange = (event: Event) => {
    const target = event.target as HTMLInputElement;

    if (!target.files || !target.files[0]) {
        return;
    }

    const file = target.files[0];

    form.image = file;

    if (imagePreview.value) {
        URL.revokeObjectURL(imagePreview.value);
    }

    imagePreview.value = URL.createObjectURL(file);
};

const removeImage = () => {
    form.image = null;

    if (imagePreview.value) {
        URL.revokeObjectURL(imagePreview.value);
    }

    imagePreview.value = null;

    const input = document.getElementById(
        'image'
    ) as HTMLInputElement | null;

    if (input) {
        input.value = '';
    }
};

const submit = () => {
    form.post(admin.products.store().url, {
        forceFormData: true,
    });
};

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Panel',
                href: admin.dashboard(),
            },
            {
                title: 'Productos',
                href: admin.products.index(),
            },
            {
                title: 'Nuevo producto',
                href: admin.products.create(),
            },
        ],
    },
});
</script>

<template>
    <Head title="Nuevo producto" />

    <div class="admin-page">
        <!-- HEADER -->

        <div class="admin-page-header">
            <div>
                <p class="admin-page-eyebrow">
                    Catálogo
                </p>

                <h1 class="admin-page-title">
                    Nuevo producto
                </h1>

                <p class="admin-page-subtitle">
                    Registra un nuevo producto dentro de Sonríe Corriendo.
                </p>
            </div>

            <div class="admin-page-header-actions">
                <Link
                    :href="admin.products.index().url"
                    class="admin-btn admin-btn-secondary"
                >
                    <ArrowLeft
                        :size="14"
                        :stroke-width="2"
                    />

                    Regresar
                </Link>
            </div>
        </div>

        <!-- FORM CARD -->

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
                        <div class="admin-form-section-icon">
                            <Package
                                :size="17"
                                :stroke-width="2"
                            />
                        </div>

                        <div>
                            <h2 class="admin-form-section-title">
                                Información del producto
                            </h2>

                            <p class="admin-form-section-description">
                                Datos principales del producto.
                            </p>
                        </div>
                    </div>
                </div>

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
                        :class="{ 'has-error': form.errors.name }"
                        placeholder="Ej. Playera Sonríe Corriendo 2026"
                        @blur="generateSlug"
                    />

                    <p
                        v-if="form.errors.name"
                        class="admin-form-error"
                    >
                        {{ form.errors.name }}
                    </p>
                </div>

                <!-- SLUG -->

                <div class="admin-form-group">
                    <label
                        for="slug"
                        class="admin-form-label"
                    >
                        Slug
                    </label>

                    <input
                        id="slug"
                        v-model="form.slug"
                        type="text"
                        class="admin-form-input"
                        :class="{ 'has-error': form.errors.slug }"
                        placeholder="playera-sonrie-corriendo-2026"
                    />

                    <p class="admin-form-help">
                        Se utiliza para identificar el producto mediante una
                        URL. Se genera automáticamente a partir del nombre.
                    </p>

                    <p
                        v-if="form.errors.slug"
                        class="admin-form-error"
                    >
                        {{ form.errors.slug }}
                    </p>
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
                        :class="{ 'has-error': form.errors.description }"
                        placeholder="Describe brevemente el producto..."
                    />

                    <p
                        v-if="form.errors.description"
                        class="admin-form-error"
                    >
                        {{ form.errors.description }}
                    </p>
                </div>

                <!-- =================================================
                     IMAGE
                ================================================== -->

                <div class="admin-form-group">
                    <label
                        for="image"
                        class="admin-form-label"
                    >
                        Imagen
                    </label>

                    <div class="product-image-upload">
                        <div
                            v-if="imagePreview"
                            class="product-image-preview"
                        >
                            <img
                                :src="imagePreview"
                                alt="Vista previa de la imagen"
                            />

                            <button
                                type="button"
                                class="product-image-remove"
                                title="Quitar imagen"
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
                            class="product-image-upload-box"
                        >
                            <div class="product-image-upload-icon">
                                <ImagePlus
                                    :size="22"
                                    :stroke-width="1.8"
                                />
                            </div>

                            <div>
                                <span class="product-image-upload-title">
                                    Seleccionar imagen
                                </span>

                                <span class="product-image-upload-description">
                                    PNG, JPG o WEBP. Máximo 2 MB.
                                </span>
                            </div>
                        </label>

                        <input
                            id="image"
                            type="file"
                            accept="image/png,image/jpeg,image/webp"
                            class="product-image-file-input"
                            @change="handleImageChange"
                        />
                    </div>

                    <p
                        v-if="form.errors.image"
                        class="admin-form-error"
                    >
                        {{ form.errors.image }}
                    </p>
                </div>

                <!-- =================================================
                     CLASSIFICATION
                ================================================== -->

                <div class="admin-form-section">
                    <div class="admin-form-section-header">
                        <div class="admin-form-section-icon">
                            <Package
                                :size="17"
                                :stroke-width="2"
                            />
                        </div>

                        <div>
                            <h2 class="admin-form-section-title">
                                Clasificación
                            </h2>

                            <p class="admin-form-section-description">
                                Información para identificar y organizar el
                                producto.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- TYPE -->

                <div class="admin-form-group">
                    <label
                        for="type"
                        class="admin-form-label"
                    >
                        Tipo
                    </label>

                    <select
                        id="type"
                        v-model="form.type"
                        class="admin-form-input"
                        :class="{ 'has-error': form.errors.type }"
                    >
                        <option value="">
                            Seleccionar tipo
                        </option>

                        <option value="Playera">
                            Playera
                        </option>

                        <option value="Medalla">
                            Medalla
                        </option>

                        <option value="Kit">
                            Kit
                        </option>

                        <option value="Accesorio">
                            Accesorio
                        </option>

                        <option value="Souvenir">
                            Souvenir
                        </option>

                        <option value="Otro">
                            Otro
                        </option>
                    </select>

                    <p class="admin-form-help">
                        Permite clasificar el producto dentro del catálogo.
                    </p>

                    <p
                        v-if="form.errors.type"
                        class="admin-form-error"
                    >
                        {{ form.errors.type }}
                    </p>
                </div>

                <!-- YEAR -->

                <div class="admin-form-group">
                    <label
                        for="year"
                        class="admin-form-label"
                    >
                        Año
                    </label>

                    <input
                        id="year"
                        v-model.number="form.year"
                        type="number"
                        min="1900"
                        max="2200"
                        class="admin-form-input"
                        :class="{ 'has-error': form.errors.year }"
                        placeholder="Ej. 2026"
                    />

                    <p class="admin-form-help">
                        Año al que corresponde el producto. Es opcional.
                    </p>

                    <p
                        v-if="form.errors.year"
                        class="admin-form-error"
                    >
                        {{ form.errors.year }}
                    </p>
                </div>

                <!-- =================================================
                     INVENTORY
                ================================================== -->

                <div class="admin-form-section">
                    <div class="admin-form-section-header">
                        <div class="admin-form-section-icon">
                            <Package
                                :size="17"
                                :stroke-width="2"
                            />
                        </div>

                        <div>
                            <h2 class="admin-form-section-title">
                                Precio e inventario
                            </h2>

                            <p class="admin-form-section-description">
                                Define el precio actual y la cantidad
                                disponible para venta.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- PRICE -->

                <div class="admin-form-group">
                    <label
                        for="price"
                        class="admin-form-label"
                    >
                        Precio
                    </label>

                    <input
                        id="price"
                        v-model.number="form.price"
                        type="number"
                        min="0"
                        step="0.01"
                        class="admin-form-input"
                        :class="{ 'has-error': form.errors.price }"
                        placeholder="Ej. 359.00"
                    />

                    <p class="admin-form-help">
                        Precio de venta del producto en pesos mexicanos.
                    </p>

                    <p
                        v-if="form.errors.price"
                        class="admin-form-error"
                    >
                        {{ form.errors.price }}
                    </p>
                </div>

                <!-- STOCK -->

                <div class="admin-form-group">
                    <label
                        for="stock"
                        class="admin-form-label"
                    >
                        Stock
                    </label>

                    <input
                        id="stock"
                        v-model.number="form.stock"
                        type="number"
                        min="0"
                        step="1"
                        class="admin-form-input"
                        :class="{ 'has-error': form.errors.stock }"
                        placeholder="Ej. 100"
                    />

                    <p class="admin-form-help">
                        Cantidad de unidades disponibles para venta.
                    </p>

                    <p
                        v-if="form.errors.stock"
                        class="admin-form-error"
                    >
                        {{ form.errors.stock }}
                    </p>
                </div>

                <!-- =================================================
                     STATUS
                ================================================== -->

                <div class="admin-form-status">
                    <div class="admin-form-status-content">
                        <div class="admin-form-status-icon">
                            <span></span>
                        </div>

                        <div>
                            <p class="admin-form-status-title">
                                Producto activo
                            </p>

                            <p class="admin-form-status-description">
                                Los productos inactivos no estarán disponibles
                                para mostrarse ni venderse.
                            </p>
                        </div>
                    </div>

                    <label class="admin-switch">
                        <input
                            v-model="form.is_active"
                            type="checkbox"
                        />

                        <span class="admin-switch-slider"></span>
                    </label>
                </div>

                <p
                    v-if="form.errors.is_active"
                    class="admin-form-error"
                >
                    {{ form.errors.is_active }}
                </p>

                <!-- =================================================
                     ACTIONS
                ================================================== -->

                <div class="admin-form-actions">
                    <Link
                        :href="admin.products.index().url"
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
                                : 'Guardar producto'
                        }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<style scoped>
.admin-form-section {
    padding-bottom: 4px;
}

.admin-form-section-header {
    display: flex;
    align-items: center;
    gap: 12px;
    padding-bottom: 4px;
}

.admin-form-section-icon {
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    border-radius: 10px;
    color: var(--sc-page-blue-dark);
    background: var(--sc-page-blue-light);
}

.admin-form-section-title {
    margin: 0;
    font-size: 14px;
    font-weight: 700;
    color: var(--sc-page-text);
}

.admin-form-section-description {
    margin: 3px 0 0;
    font-size: 12px;
    color: var(--sc-page-text-secondary);
}

.product-image-upload {
    position: relative;
}

.product-image-file-input {
    display: none;
}

.product-image-upload-box {
    min-height: 100px;
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 18px;
    border: 1px dashed var(--sc-page-border);
    border-radius: 12px;
    background: #fafcfe;
    cursor: pointer;
    transition:
        border-color 0.2s ease,
        background 0.2s ease;
}

.product-image-upload-box:hover {
    border-color: #a9d9f2;
    background: var(--sc-page-blue-light);
}

.product-image-upload-icon {
    width: 42px;
    height: 42px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    border-radius: 10px;
    color: var(--sc-page-blue-dark);
    background: #ffffff;
    border: 1px solid var(--sc-page-border);
}

.product-image-upload-title {
    display: block;
    font-size: 13px;
    font-weight: 600;
    color: var(--sc-page-text);
}

.product-image-upload-description {
    display: block;
    margin-top: 3px;
    font-size: 11px;
    color: var(--sc-page-text-secondary);
}

.product-image-preview {
    position: relative;
    width: 150px;
    height: 110px;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 12px;
    border: 1px solid var(--sc-page-border);
    border-radius: 12px;
    background: #ffffff;
}

.product-image-preview img {
    width: 100%;
    height: 100%;
    object-fit: contain;
}

.product-image-remove {
    position: absolute;
    top: -8px;
    right: -8px;
    width: 26px;
    height: 26px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 1px solid var(--sc-page-border);
    border-radius: 50%;
    color: #64748b;
    background: #ffffff;
    cursor: pointer;
    box-shadow: 0 3px 10px rgba(23, 43, 77, 0.12);
    transition:
        color 0.2s ease,
        border-color 0.2s ease;
}

.product-image-remove:hover {
    color: #e83e4d;
    border-color: #e8b4bb;
}
</style>