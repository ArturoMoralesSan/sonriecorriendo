<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import {
    ArrowLeft,
    Building2,
    ImagePlus,
    Save,
    X,
} from 'lucide-vue-next';
import { ref } from 'vue';

import admin from '@/routes/admin';

const form = useForm({
    name: '',
    slug: '',
    description: '',
    logo: null as File | null,
    responsible: '',
    phone: '',
    email: '',
    city: '',
    address: '',
    is_active: true,
});

const logoPreview = ref<string | null>(null);

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

const handleLogoChange = (event: Event) => {
    const target = event.target as HTMLInputElement;

    if (!target.files || !target.files[0]) {
        return;
    }

    const file = target.files[0];

    form.logo = file;

    if (logoPreview.value) {
        URL.revokeObjectURL(logoPreview.value);
    }

    logoPreview.value = URL.createObjectURL(file);
};

const removeLogo = () => {
    form.logo = null;

    if (logoPreview.value) {
        URL.revokeObjectURL(logoPreview.value);
    }

    logoPreview.value = null;

    const input = document.getElementById(
        'logo'
    ) as HTMLInputElement | null;

    if (input) {
        input.value = '';
    }
};

const submit = () => {
    form.post(admin.clubs.store().url, {
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
                title: 'Clubes',
                href: admin.clubs.index(),
            },
            {
                title: 'Nuevo club',
                href: admin.clubs.create(),
            },
        ],
    },
});
</script>

<template>
    <Head title="Nuevo club" />

    <div class="admin-page">
        <!-- HEADER -->

        <div class="admin-page-header">
            <div>
                <p class="admin-page-eyebrow">
                    Clubes
                </p>

                <h1 class="admin-page-title">
                    Nuevo club
                </h1>

                <p class="admin-page-subtitle">
                    Registra un nuevo club dentro de Sonríe Corriendo.
                </p>
            </div>

            <div class="admin-page-header-actions">
                <Link
                    :href="admin.clubs.index().url"
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
                            <Building2
                                :size="17"
                                :stroke-width="2"
                            />
                        </div>

                        <div>
                            <h2 class="admin-form-section-title">
                                Información del club
                            </h2>

                            <p class="admin-form-section-description">
                                Datos principales del club.
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
                        placeholder="Ej. Club Corredores Durango"
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
                        placeholder="club-corredores-durango"
                    />

                    <p class="admin-form-help">
                        Se utiliza para identificar el club mediante una URL.
                        Se genera automáticamente a partir del nombre.
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
                        placeholder="Describe brevemente el club..."
                    />

                    <p
                        v-if="form.errors.description"
                        class="admin-form-error"
                    >
                        {{ form.errors.description }}
                    </p>
                </div>

                <!-- =================================================
                     LOGO
                ================================================== -->

                <div class="admin-form-group">
                    <label
                        for="logo"
                        class="admin-form-label"
                    >
                        Logo
                    </label>

                    <div class="club-logo-upload">
                        <div
                            v-if="logoPreview"
                            class="club-logo-preview"
                        >
                            <img
                                :src="logoPreview"
                                alt="Vista previa del logo"
                            />

                            <button
                                type="button"
                                class="club-logo-remove"
                                title="Quitar logo"
                                @click="removeLogo"
                            >
                                <X
                                    :size="15"
                                    :stroke-width="2"
                                />
                            </button>
                        </div>

                        <label
                            v-else
                            for="logo"
                            class="club-logo-upload-box"
                        >
                            <div class="club-logo-upload-icon">
                                <ImagePlus
                                    :size="22"
                                    :stroke-width="1.8"
                                />
                            </div>

                            <div>
                                <span class="club-logo-upload-title">
                                    Seleccionar logo
                                </span>

                                <span class="club-logo-upload-description">
                                    PNG, JPG o WEBP. Máximo 2 MB.
                                </span>
                            </div>
                        </label>

                        <input
                            id="logo"
                            type="file"
                            accept="image/png,image/jpeg,image/webp"
                            class="club-logo-file-input"
                            @change="handleLogoChange"
                        />
                    </div>

                    <p
                        v-if="form.errors.logo"
                        class="admin-form-error"
                    >
                        {{ form.errors.logo }}
                    </p>
                </div>

                <!-- =================================================
                     CONTACT
                ================================================== -->

                <div class="admin-form-section">
                    <div class="admin-form-section-header">
                        <div class="admin-form-section-icon">
                            <Building2
                                :size="17"
                                :stroke-width="2"
                            />
                        </div>

                        <div>
                            <h2 class="admin-form-section-title">
                                Información de contacto
                            </h2>

                            <p class="admin-form-section-description">
                                Datos de la persona responsable y ubicación
                                del club.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- RESPONSIBLE -->

                <div class="admin-form-group">
                    <label
                        for="responsible"
                        class="admin-form-label"
                    >
                        Responsable
                    </label>

                    <input
                        id="responsible"
                        v-model="form.responsible"
                        type="text"
                        class="admin-form-input"
                        :class="{ 'has-error': form.errors.responsible }"
                        placeholder="Ej. Juan Pérez"
                    />

                    <p
                        v-if="form.errors.responsible"
                        class="admin-form-error"
                    >
                        {{ form.errors.responsible }}
                    </p>
                </div>

                <!-- PHONE -->

                <div class="admin-form-group">
                    <label
                        for="phone"
                        class="admin-form-label"
                    >
                        Teléfono
                    </label>

                    <input
                        id="phone"
                        v-model="form.phone"
                        type="tel"
                        class="admin-form-input"
                        :class="{ 'has-error': form.errors.phone }"
                        placeholder="Ej. 618 123 4567"
                    />

                    <p
                        v-if="form.errors.phone"
                        class="admin-form-error"
                    >
                        {{ form.errors.phone }}
                    </p>
                </div>

                <!-- EMAIL -->

                <div class="admin-form-group">
                    <label
                        for="email"
                        class="admin-form-label"
                    >
                        Correo electrónico
                    </label>

                    <input
                        id="email"
                        v-model="form.email"
                        type="email"
                        class="admin-form-input"
                        :class="{ 'has-error': form.errors.email }"
                        placeholder="Ej. contacto@club.com"
                    />

                    <p
                        v-if="form.errors.email"
                        class="admin-form-error"
                    >
                        {{ form.errors.email }}
                    </p>
                </div>

                <!-- CITY -->

                <div class="admin-form-group">
                    <label
                        for="city"
                        class="admin-form-label"
                    >
                        Ciudad
                    </label>

                    <input
                        id="city"
                        v-model="form.city"
                        type="text"
                        class="admin-form-input"
                        :class="{ 'has-error': form.errors.city }"
                        placeholder="Ej. Durango"
                    />

                    <p
                        v-if="form.errors.city"
                        class="admin-form-error"
                    >
                        {{ form.errors.city }}
                    </p>
                </div>

                <!-- ADDRESS -->

                <div class="admin-form-group">
                    <label
                        for="address"
                        class="admin-form-label"
                    >
                        Dirección
                    </label>

                    <textarea
                        id="address"
                        v-model="form.address"
                        rows="3"
                        class="admin-form-input admin-form-textarea"
                        :class="{ 'has-error': form.errors.address }"
                        placeholder="Dirección del club..."
                    />

                    <p
                        v-if="form.errors.address"
                        class="admin-form-error"
                    >
                        {{ form.errors.address }}
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
                                Club activo
                            </p>

                            <p class="admin-form-status-description">
                                Los clubes inactivos no estarán disponibles
                                para mostrarse públicamente.
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
                        :href="admin.clubs.index().url"
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
                                : 'Guardar club'
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

.club-logo-upload {
    position: relative;
}

.club-logo-file-input {
    display: none;
}

.club-logo-upload-box {
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

.club-logo-upload-box:hover {
    border-color: #a9d9f2;
    background: var(--sc-page-blue-light);
}

.club-logo-upload-icon {
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

.club-logo-upload-title {
    display: block;
    font-size: 13px;
    font-weight: 600;
    color: var(--sc-page-text);
}

.club-logo-upload-description {
    display: block;
    margin-top: 3px;
    font-size: 11px;
    color: var(--sc-page-text-secondary);
}

.club-logo-preview {
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

.club-logo-preview img {
    width: 100%;
    height: 100%;
    object-fit: contain;
}

.club-logo-remove {
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

.club-logo-remove:hover {
    color: #e83e4d;
    border-color: #e8b4bb;
}
</style>