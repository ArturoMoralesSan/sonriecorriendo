<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import {
    ArrowLeft,
    Building2,
    Save,
    Upload,
    X,
} from 'lucide-vue-next';
import { computed, onBeforeUnmount, ref } from 'vue';

import admin from '@/routes/admin';
import sponsors from '@/routes/admin/sponsors';

interface Sponsor {
    id: number;
    name: string;
    slug: string;
    logo: string | null;
    description: string | null;
    contact_name: string | null;
    email: string | null;
    phone: string | null;
    website: string | null;
    is_active: boolean;
}

const props = defineProps<{
    sponsor: Sponsor;
}>();

const form = useForm({
    _method: 'PUT',
    name: props.sponsor.name ?? '',
    slug: props.sponsor.slug ?? '',
    logo: null as File | null,
    remove_logo: false,
    description: props.sponsor.description ?? '',
    contact_name: props.sponsor.contact_name ?? '',
    email: props.sponsor.email ?? '',
    phone: props.sponsor.phone ?? '',
    website: props.sponsor.website ?? '',
    is_active: props.sponsor.is_active,
});

const logoInput = ref<HTMLInputElement | null>(null);

const currentLogoUrl = computed((): string | null => {
    if (!props.sponsor.logo) {
        return null;
    }

    if (
        props.sponsor.logo.startsWith('http://') ||
        props.sponsor.logo.startsWith('https://')
    ) {
        return props.sponsor.logo;
    }

    return `/storage/${props.sponsor.logo}`;
});

const hasCurrentLogo = computed((): boolean => {
    return Boolean(currentLogoUrl.value) && !form.remove_logo;
});

const selectedLogoUrl = ref<string | null>(null);

const updateSelectedLogoPreview = (): void => {
    if (selectedLogoUrl.value) {
        URL.revokeObjectURL(selectedLogoUrl.value);
        selectedLogoUrl.value = null;
    }

    if (form.logo) {
        selectedLogoUrl.value = URL.createObjectURL(form.logo);
    }
};

const handleLogoChange = (
    event: Event,
): void => {
    const target = event.target as HTMLInputElement;

    form.logo = target.files?.[0] ?? null;

    if (form.logo) {
        form.remove_logo = false;
    }

    updateSelectedLogoPreview();
};

const clearSelectedLogo = (): void => {
    form.logo = null;

    if (logoInput.value) {
        logoInput.value.value = '';
    }

    updateSelectedLogoPreview();
};

const removeCurrentLogo = (): void => {
    form.remove_logo = true;
};

const cancelRemoveLogo = (): void => {
    form.remove_logo = false;
};

const submit = (): void => {
    form.post(
        sponsors.update(props.sponsor.id).url,
        {
            forceFormData: true,
            preserveScroll: true,
        },
    );
};

onBeforeUnmount(() => {
    if (selectedLogoUrl.value) {
        URL.revokeObjectURL(selectedLogoUrl.value);
    }
});

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Panel',
                href: admin.dashboard(),
            },
            {
                title: 'Patrocinadores',
                href: admin.sponsors.index(),
            },
            {
                title: 'Editar patrocinador',
                href: '#',
            },
        ],
    },
});
</script>

<template>
    <Head :title="`Editar ${sponsor.name}`" />

    <div class="admin-page">
        <header class="admin-page-header">
            <div>
                <div class="admin-page-eyebrow">
                    Patrocinadores
                </div>

                <h1 class="admin-page-title">
                    Editar patrocinador
                </h1>

                <p class="admin-page-subtitle">
                    Actualiza la información del patrocinador
                    {{ sponsor.name }}.
                </p>
            </div>

            <div class="admin-page-header-actions">
                <Link
                    :href="admin.sponsors.index().url"
                    class="admin-btn admin-btn-secondary"
                >
                    <ArrowLeft
                        :size="16"
                        :stroke-width="2"
                        class="admin-btn-icon"
                    />

                    <span>Volver</span>
                </Link>
            </div>
        </header>

        <section class="admin-form-card">
            <form
                class="admin-form"
                @submit.prevent="submit"
            >
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
                                Información del patrocinador
                            </h2>

                            <p class="admin-form-section-description">
                                Datos generales y de identificación.
                            </p>
                        </div>
                    </div>

                    <div class="admin-form-grid">
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
                                placeholder="Nombre del patrocinador"
                                autocomplete="organization"
                            />

                            <p
                                v-if="form.errors.name"
                                class="admin-form-error"
                            >
                                {{ form.errors.name }}
                            </p>
                        </div>

                        <div class="admin-form-group">
                            <label
                                for="slug"
                                class="admin-form-label"
                            >
                                Identificador
                            </label>

                            <input
                                id="slug"
                                v-model="form.slug"
                                type="text"
                                class="admin-form-input"
                                :class="{
                                    'has-error': form.errors.slug,
                                }"
                                placeholder="identificador-del-patrocinador"
                            />

                            <p class="admin-form-help">
                                Se utiliza como identificador único del
                                patrocinador.
                            </p>

                            <p
                                v-if="form.errors.slug"
                                class="admin-form-error"
                            >
                                {{ form.errors.slug }}
                            </p>
                        </div>

                        <div class="admin-form-group admin-form-group-full">
                            <label
                                for="description"
                                class="admin-form-label"
                            >
                                Descripción
                            </label>

                            <textarea
                                id="description"
                                v-model="form.description"
                                class="admin-form-textarea"
                                :class="{
                                    'has-error': form.errors.description,
                                }"
                                placeholder="Describe al patrocinador..."
                                rows="5"
                            />

                            <p
                                v-if="form.errors.description"
                                class="admin-form-error"
                            >
                                {{ form.errors.description }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="admin-form-divider" />

                <div class="admin-form-section">
                    <div class="admin-form-section-header">
                        <div class="admin-form-section-icon">
                            <Upload
                                :size="17"
                                :stroke-width="2"
                            />
                        </div>

                        <div>
                            <h2 class="admin-form-section-title">
                                Logo
                            </h2>

                            <p class="admin-form-section-description">
                                Actualiza la imagen del patrocinador.
                            </p>
                        </div>
                    </div>

                    <div class="admin-logo-area">
                        <div
                            v-if="selectedLogoUrl"
                            class="admin-logo-preview-wrapper"
                        >
                            <div class="admin-logo-preview">
                                <img
                                    :src="selectedLogoUrl"
                                    alt="Vista previa del nuevo logo"
                                />
                            </div>

                            <div class="admin-logo-preview-info">
                                <span class="admin-logo-preview-title">
                                    Nuevo logo
                                </span>

                                <span class="admin-logo-preview-text">
                                    {{ form.logo?.name }}
                                </span>

                                <button
                                    type="button"
                                    class="admin-logo-remove-btn"
                                    @click="clearSelectedLogo"
                                >
                                    <X
                                        :size="14"
                                        :stroke-width="2"
                                    />

                                    Quitar selección
                                </button>
                            </div>
                        </div>

                        <div
                            v-else-if="hasCurrentLogo"
                            class="admin-logo-preview-wrapper"
                        >
                            <div class="admin-logo-preview">
                                <img
                                    :src="currentLogoUrl!"
                                    alt="Logo actual del patrocinador"
                                />
                            </div>

                            <div class="admin-logo-preview-info">
                                <span class="admin-logo-preview-title">
                                    Logo actual
                                </span>

                                <span class="admin-logo-preview-text">
                                    Puedes reemplazarlo seleccionando una
                                    nueva imagen.
                                </span>

                                <button
                                    type="button"
                                    class="admin-logo-remove-btn"
                                    @click="removeCurrentLogo"
                                >
                                    <X
                                        :size="14"
                                        :stroke-width="2"
                                    />

                                    Eliminar logo
                                </button>
                            </div>
                        </div>

                        <div
                            v-else-if="form.remove_logo"
                            class="admin-logo-removed"
                        >
                            <div class="admin-logo-removed-icon">
                                <X
                                    :size="17"
                                    :stroke-width="2"
                                />
                            </div>

                            <div>
                                <span class="admin-logo-preview-title">
                                    Logo marcado para eliminar
                                </span>

                                <span class="admin-logo-preview-text">
                                    El cambio se aplicará al guardar.
                                </span>
                            </div>

                            <button
                                type="button"
                                class="admin-logo-cancel-remove"
                                @click="cancelRemoveLogo"
                            >
                                Cancelar
                            </button>
                        </div>

                        <div class="admin-file-wrapper">
                            <label
                                for="logo"
                                class="admin-file-label"
                            >
                                <Upload
                                    :size="16"
                                    :stroke-width="2"
                                />

                                <span>
                                    {{
                                        form.logo
                                            ? 'Cambiar imagen'
                                            : 'Seleccionar imagen'
                                    }}
                                </span>
                            </label>

                            <input
                                id="logo"
                                ref="logoInput"
                                type="file"
                                class="admin-file-input"
                                accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                                @change="handleLogoChange"
                            />
                        </div>

                        <p class="admin-form-help">
                            Formatos permitidos: JPG, JPEG, PNG o WEBP.
                            Tamaño máximo: 5 MB.
                        </p>

                        <p
                            v-if="form.errors.logo"
                            class="admin-form-error"
                        >
                            {{ form.errors.logo }}
                        </p>

                        <p
                            v-if="form.errors.remove_logo"
                            class="admin-form-error"
                        >
                            {{ form.errors.remove_logo }}
                        </p>
                    </div>
                </div>

                <div class="admin-form-divider" />

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
                                Datos de contacto y presencia digital.
                            </p>
                        </div>
                    </div>

                    <div class="admin-form-grid">
                        <div class="admin-form-group">
                            <label
                                for="contact_name"
                                class="admin-form-label"
                            >
                                Nombre de contacto
                            </label>

                            <input
                                id="contact_name"
                                v-model="form.contact_name"
                                type="text"
                                class="admin-form-input"
                                :class="{
                                    'has-error': form.errors.contact_name,
                                }"
                                placeholder="Nombre de la persona de contacto"
                                autocomplete="name"
                            />

                            <p
                                v-if="form.errors.contact_name"
                                class="admin-form-error"
                            >
                                {{ form.errors.contact_name }}
                            </p>
                        </div>

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
                                :class="{
                                    'has-error': form.errors.email,
                                }"
                                placeholder="contacto@empresa.com"
                                autocomplete="email"
                            />

                            <p
                                v-if="form.errors.email"
                                class="admin-form-error"
                            >
                                {{ form.errors.email }}
                            </p>
                        </div>

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
                                :class="{
                                    'has-error': form.errors.phone,
                                }"
                                placeholder="618 123 4567"
                                autocomplete="tel"
                            />

                            <p
                                v-if="form.errors.phone"
                                class="admin-form-error"
                            >
                                {{ form.errors.phone }}
                            </p>
                        </div>

                        <div class="admin-form-group">
                            <label
                                for="website"
                                class="admin-form-label"
                            >
                                Sitio web
                            </label>

                            <input
                                id="website"
                                v-model="form.website"
                                type="url"
                                class="admin-form-input"
                                :class="{
                                    'has-error': form.errors.website,
                                }"
                                placeholder="https://www.empresa.com"
                                autocomplete="url"
                            />

                            <p
                                v-if="form.errors.website"
                                class="admin-form-error"
                            >
                                {{ form.errors.website }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="admin-form-divider" />

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
                                Estado
                            </h2>

                            <p class="admin-form-section-description">
                                Controla si el patrocinador está disponible
                                en el sistema.
                            </p>
                        </div>
                    </div>

                    <label class="admin-switch-wrapper">
                        <input
                            v-model="form.is_active"
                            type="checkbox"
                            class="admin-switch-input"
                        />

                        <span class="admin-switch">
                            <span class="admin-switch-thumb" />
                        </span>

                        <span class="admin-switch-content">
                            <span class="admin-switch-title">
                                Patrocinador activo
                            </span>

                            <span class="admin-switch-description">
                                El patrocinador estará disponible para
                                asociarlo a carreras.
                            </span>
                        </span>
                    </label>

                    <p
                        v-if="form.errors.is_active"
                        class="admin-form-error"
                    >
                        {{ form.errors.is_active }}
                    </p>
                </div>

                <div class="admin-form-actions">
                    <Link
                        :href="admin.sponsors.index().url"
                        class="admin-btn admin-btn-secondary"
                    >
                        Cancelar
                    </Link>

                    <button
                        type="submit"
                        class="admin-btn admin-btn-primary"
                        :disabled="form.processing"
                    >
                        <span
                            v-if="form.processing"
                            class="admin-btn-loader"
                        />

                        <Save
                            v-else
                            :size="16"
                            :stroke-width="2"
                            class="admin-btn-icon"
                        />

                        <span>
                            {{
                                form.processing
                                    ? 'Guardando...'
                                    : 'Guardar cambios'
                            }}
                        </span>
                    </button>
                </div>
            </form>
        </section>
    </div>
</template>

<style scoped>
.admin-page {
    width: 100%;
    min-height: 90%;
    padding: 28px 30px 38px;
    background: var(--sc-page-background);
    color: var(--sc-page-text);
}

.admin-page-header {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 24px;
    margin-bottom: 24px;
}

.admin-page-eyebrow {
    margin-bottom: 5px;
    color: var(--sc-page-blue);
    font-size: 12px;
    font-weight: 700;
    letter-spacing: 0.04em;
    text-transform: uppercase;
}

.admin-page-title {
    margin: 0;
    color: var(--sc-page-text);
    font-size: 27px;
    font-weight: 750;
    line-height: 1.15;
    letter-spacing: -0.02em;
}

.admin-page-subtitle {
    margin: 7px 0 0;
    color: var(--sc-page-text-secondary);
    font-size: 13px;
    line-height: 1.5;
}

.admin-page-header-actions {
    display: flex;
    align-items: center;
    gap: 9px;
    flex-shrink: 0;
}

.admin-form-card {
    width: 100%;
    border: 1px solid var(--sc-page-border);
    border-radius: var(--sc-page-radius);
    background: #ffffff;
    box-shadow:
        0 4px 15px rgba(27, 62, 90, 0.035),
        0 1px 3px rgba(27, 62, 90, 0.025);
}

.admin-form {
    padding: 28px;
}

.admin-form-section {
    width: 100%;
}

.admin-form-section-header {
    display: flex;
    align-items: flex-start;
    gap: 11px;
    margin-bottom: 21px;
}

.admin-form-section-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 34px;
    height: 34px;
    flex: 0 0 34px;
    border-radius: 9px;
    background: var(--sc-page-blue-light);
    color: var(--sc-page-blue-dark);
}

.admin-form-section-title {
    margin: 0;
    color: var(--sc-page-text);
    font-size: 14px;
    font-weight: 700;
    line-height: 1.3;
}

.admin-form-section-description {
    margin: 3px 0 0;
    color: var(--sc-page-text-secondary);
    font-size: 11px;
    line-height: 1.5;
}

.admin-form-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    column-gap: 20px;
    row-gap: 0;
}

.admin-form-group {
    margin-bottom: 21px;
}

.admin-form-group-full {
    grid-column: 1 / -1;
}

.admin-form-label {
    display: block;
    margin-bottom: 7px;
    color: var(--sc-page-text);
    font-size: 13px;
    font-weight: 650;
}

.admin-form-input {
    display: block;
    width: 100%;
    height: 42px;
    padding: 0 13px;
    border: 1px solid var(--sc-page-border);
    border-radius: 10px;
    outline: none;
    background: #fbfcfd;
    color: var(--sc-page-text);
    font-family: inherit;
    font-size: 13px;
    box-sizing: border-box;
    transition:
        border-color 0.2s ease,
        background-color 0.2s ease,
        box-shadow 0.2s ease;
}

.admin-form-input::placeholder,
.admin-form-textarea::placeholder {
    color: #a8b2be;
}

.admin-form-input:focus,
.admin-form-textarea:focus {
    border-color: #a9d9f2;
    background: #ffffff;
    box-shadow: 0 0 0 3px rgba(36, 158, 219, 0.08);
}

.admin-form-input.has-error,
.admin-form-textarea.has-error {
    border-color: #efb8bf;
    background: var(--sc-page-red-light);
}

.admin-form-input.has-error:focus,
.admin-form-textarea.has-error:focus {
    border-color: var(--sc-page-red);
    box-shadow: 0 0 0 3px rgba(232, 62, 77, 0.08);
}

.admin-form-textarea {
    display: block;
    width: 100%;
    min-height: 120px;
    padding: 11px 13px;
    border: 1px solid var(--sc-page-border);
    border-radius: 10px;
    outline: none;
    resize: vertical;
    background: #fbfcfd;
    color: var(--sc-page-text);
    font-family: inherit;
    font-size: 13px;
    line-height: 1.5;
    box-sizing: border-box;
    transition:
        border-color 0.2s ease,
        background-color 0.2s ease,
        box-shadow 0.2s ease;
}

.admin-form-help {
    margin: 6px 0 0;
    color: var(--sc-page-muted);
    font-size: 11px;
    line-height: 1.45;
}

.admin-form-error {
    margin: 6px 0 0;
    color: var(--sc-page-red);
    font-size: 11px;
    line-height: 1.45;
}

.admin-form-divider {
    height: 1px;
    margin: 5px 0 27px;
    background: var(--sc-page-border-soft);
}

.admin-logo-area {
    width: 100%;
}

.admin-logo-preview-wrapper {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-bottom: 16px;
    padding: 13px;
    border: 1px solid var(--sc-page-border);
    border-radius: 10px;
    background: #fbfcfd;
}

.admin-logo-preview {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 82px;
    height: 82px;
    flex: 0 0 82px;
    overflow: hidden;
    border: 1px solid var(--sc-page-border);
    border-radius: 9px;
    background: #ffffff;
}

.admin-logo-preview img {
    display: block;
    width: 100%;
    height: 100%;
    object-fit: contain;
}

.admin-logo-preview-info {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    min-width: 0;
}

.admin-logo-preview-title {
    display: block;
    color: var(--sc-page-text);
    font-size: 12px;
    font-weight: 650;
    line-height: 1.4;
}

.admin-logo-preview-text {
    display: block;
    margin-top: 3px;
    color: var(--sc-page-text-secondary);
    font-size: 11px;
    line-height: 1.45;
}

.admin-logo-remove-btn {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    margin-top: 8px;
    padding: 0;
    border: 0;
    background: transparent;
    color: var(--sc-page-red);
    font-family: inherit;
    font-size: 11px;
    font-weight: 600;
    cursor: pointer;
}

.admin-logo-remove-btn:hover {
    text-decoration: underline;
}

.admin-logo-removed {
    display: flex;
    align-items: center;
    gap: 11px;
    margin-bottom: 16px;
    padding: 13px;
    border: 1px solid #f3d5d8;
    border-radius: 10px;
    background: var(--sc-page-red-light);
}

.admin-logo-removed-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 34px;
    height: 34px;
    flex: 0 0 34px;
    border-radius: 8px;
    background: #ffffff;
    color: var(--sc-page-red);
}

.admin-logo-cancel-remove {
    margin-left: auto;
    padding: 6px 10px;
    border: 1px solid #efb8bf;
    border-radius: 7px;
    background: #ffffff;
    color: var(--sc-page-red);
    font-family: inherit;
    font-size: 11px;
    font-weight: 600;
    cursor: pointer;
}

.admin-file-wrapper {
    position: relative;
    display: inline-flex;
    align-items: center;
}

.admin-file-label {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 7px;
    min-height: 38px;
    padding: 0 13px;
    border: 1px solid var(--sc-page-border);
    border-radius: 9px;
    background: #ffffff;
    color: var(--sc-page-text);
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    transition:
        border-color 0.2s ease,
        background-color 0.2s ease;
}

.admin-file-label:hover {
    border-color: #c9d4df;
    background: #fbfcfd;
}

.admin-file-input {
    position: absolute;
    width: 1px;
    height: 1px;
    overflow: hidden;
    opacity: 0;
    pointer-events: none;
}

.admin-switch-wrapper {
    display: flex;
    align-items: center;
    gap: 11px;
    width: 100%;
    padding: 13px;
    border: 1px solid var(--sc-page-border);
    border-radius: 10px;
    background: #fbfcfd;
    cursor: pointer;
}

.admin-switch-input {
    position: absolute;
    width: 1px;
    height: 1px;
    opacity: 0;
    pointer-events: none;
}

.admin-switch {
    position: relative;
    display: flex;
    align-items: center;
    width: 38px;
    height: 22px;
    flex: 0 0 38px;
    padding: 2px;
    border-radius: 999px;
    background: #cbd5df;
    transition: background-color 0.2s ease;
}

.admin-switch-thumb {
    display: block;
    width: 18px;
    height: 18px;
    border-radius: 50%;
    background: #ffffff;
    box-shadow: 0 1px 3px rgba(27, 62, 90, 0.18);
    transition: transform 0.2s ease;
}

.admin-switch-input:checked + .admin-switch {
    background: var(--sc-page-blue);
}

.admin-switch-input:checked + .admin-switch .admin-switch-thumb {
    transform: translateX(16px);
}

.admin-switch-content {
    display: flex;
    flex-direction: column;
    min-width: 0;
}

.admin-switch-title {
    color: var(--sc-page-text);
    font-size: 12px;
    font-weight: 650;
    line-height: 1.4;
}

.admin-switch-description {
    margin-top: 2px;
    color: var(--sc-page-text-secondary);
    font-size: 11px;
    line-height: 1.45;
}

.admin-form-actions {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 9px;
    margin-top: 5px;
    padding-top: 24px;
    border-top: 1px solid var(--sc-page-border-soft);
}

.admin-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 7px;
    min-height: 40px;
    padding: 0 14px;
    border: 1px solid transparent;
    border-radius: 10px;
    font-family: inherit;
    font-size: 12px;
    font-weight: 600;
    line-height: 1;
    text-decoration: none;
    white-space: nowrap;
    box-sizing: border-box;
    cursor: pointer;
    transition:
        transform 0.15s ease,
        box-shadow 0.2s ease,
        background-color 0.2s ease,
        border-color 0.2s ease;
}

.admin-btn:hover:not(:disabled) {
    transform: translateY(-1px);
}

.admin-btn:disabled {
    cursor: not-allowed;
    opacity: 0.65;
}

.admin-btn-icon {
    flex: 0 0 auto;
}

.admin-btn-primary {
    border-color: transparent;
    background: var(--button-primary);
    color: #ffffff;
    box-shadow: 0 4px 10px rgba(36, 158, 219, 0.14);
}

.admin-btn-primary:hover:not(:disabled) {
    box-shadow: 0 6px 14px rgba(36, 158, 219, 0.18);
}

.admin-btn-secondary {
    border-color: var(--sc-page-border);
    background: #ffffff;
    color: var(--sc-page-text);
}

.admin-btn-secondary:hover:not(:disabled) {
    background: #fbfcfd;
    border-color: #d3dbe4;
}

.admin-btn-loader {
    width: 14px;
    height: 14px;
    border: 2px solid rgba(255, 255, 255, 0.4);
    border-top-color: #ffffff;
    border-radius: 50%;
    animation: admin-spin 0.7s linear infinite;
}

@keyframes admin-spin {
    to {
        transform: rotate(360deg);
    }
}

@media (max-width: 800px) {
    .admin-page {
        padding: 22px 20px 30px;
    }

    .admin-page-header {
        align-items: flex-start;
        flex-direction: column;
    }

    .admin-page-header-actions {
        width: 100%;
    }

    .admin-page-header-actions .admin-btn {
        width: 100%;
    }

    .admin-form {
        padding: 22px;
    }

    .admin-form-grid {
        grid-template-columns: 1fr;
    }

    .admin-form-group-full {
        grid-column: auto;
    }
}

@media (max-width: 520px) {
    .admin-page {
        padding: 18px 14px 24px;
    }

    .admin-page-title {
        font-size: 24px;
    }

    .admin-form {
        padding: 18px;
    }

    .admin-logo-preview-wrapper {
        align-items: flex-start;
        flex-direction: column;
    }

    .admin-logo-removed {
        align-items: flex-start;
        flex-wrap: wrap;
    }

    .admin-logo-cancel-remove {
        margin-left: 45px;
    }

    .admin-form-actions {
        align-items: stretch;
        flex-direction: column-reverse;
    }

    .admin-form-actions .admin-btn {
        width: 100%;
    }
}
</style>