<script setup lang="ts">
import {
    Head,
    Link,
    useForm,
} from '@inertiajs/vue3';
import {
    ArrowLeft,
    Building2,
    Save,
} from 'lucide-vue-next';

import admin from '@/routes/admin';
import sponsors from '@/routes/admin/sponsors';

const form = useForm({
    name: '',
    slug: '',
    logo: null as File | null,
    description: '',
    contact_name: '',
    email: '',
    phone: '',
    website: '',
    is_active: true,
});

const submit = (): void => {
    form.post(
        sponsors.store().url,
        {
            forceFormData: true,
        },
    );
};

const handleLogoChange = (
    event: Event,
): void => {
    const target =
        event.target as HTMLInputElement;

    form.logo =
        target.files?.[0] ?? null;
};

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Panel',
                href: admin.dashboard(),
            },
            {
                title: 'Patrocinadores',
                href: sponsors.index(),
            },
            {
                title: 'Nuevo patrocinador',
                href: sponsors.create(),
            },
        ],
    },
});
</script>

<template>
    <Head title="Nuevo patrocinador" />

    <div class="admin-page">
        <!-- =================================================
             HEADER
        ================================================== -->

        <header class="admin-page-header">
            <div>
                <p class="admin-page-eyebrow">
                    Patrocinadores
                </p>

                <h1 class="admin-page-title">
                    Nuevo patrocinador
                </h1>

                <p class="admin-page-subtitle">
                    Registra un nuevo patrocinador para utilizarlo
                    posteriormente en las carreras.
                </p>
            </div>

            <div class="admin-page-header-actions">
                <Link
                    :href="sponsors.index().url"
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
             FORM CARD
        ================================================== -->

        <div class="admin-form-card">
            <form
                class="admin-form"
                @submit.prevent="submit"
            >
                <!-- NAME -->

                <div class="admin-form-group">
                    <label
                        for="name"
                        class="admin-form-label"
                    >
                        Nombre del patrocinador
                    </label>

                    <div class="admin-form-input-wrapper">
                        <input
                            id="name"
                            v-model="form.name"
                            type="text"
                            class="admin-form-input"
                            :class="{
                                'has-error': form.errors.name,
                            }"
                            placeholder="Ej. Coca-Cola"
                        />
                    </div>

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
                        Identificador
                    </label>

                    <div class="admin-form-input-wrapper">
                        <input
                            id="slug"
                            v-model="form.slug"
                            type="text"
                            class="admin-form-input"
                            :class="{
                                'has-error': form.errors.slug,
                            }"
                            placeholder="Ej. coca-cola"
                        />
                    </div>

                    <p class="admin-form-help">
                        Si lo dejas vacío, se generará automáticamente
                        a partir del nombre.
                    </p>

                    <p
                        v-if="form.errors.slug"
                        class="admin-form-error"
                    >
                        {{ form.errors.slug }}
                    </p>
                </div>

                <!-- LOGO -->

                <div class="admin-form-group">
                    <label
                        for="logo"
                        class="admin-form-label"
                    >
                        Logo
                    </label>

                    <div class="admin-form-input-wrapper">
                        <input
                            id="logo"
                            type="file"
                            class="admin-form-input admin-form-file"
                            :class="{
                                'has-error': form.errors.logo,
                            }"
                            accept=".jpg,.jpeg,.png,.webp"
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
                        class="admin-form-input admin-form-textarea"
                        :class="{
                            'has-error':
                                form.errors.description,
                        }"
                        placeholder="Describe brevemente al patrocinador..."
                    ></textarea>

                    <p
                        v-if="form.errors.description"
                        class="admin-form-error"
                    >
                        {{ form.errors.description }}
                    </p>
                </div>

                <!-- CONTACT -->

                <div class="admin-form-section">
                    <div class="admin-form-section-header">
                        <div>
                            <p class="admin-form-section-title">
                                Información de contacto
                            </p>

                            <p class="admin-form-section-description">
                                Datos de la persona o medio de contacto
                                del patrocinador.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="admin-form-grid">
                    <!-- CONTACT NAME -->

                    <div class="admin-form-group">
                        <label
                            for="contact_name"
                            class="admin-form-label"
                        >
                            Nombre de contacto
                        </label>

                        <div
                            class="admin-form-input-wrapper"
                        >
                            <input
                                id="contact_name"
                                v-model="form.contact_name"
                                type="text"
                                class="admin-form-input"
                                :class="{
                                    'has-error':
                                        form.errors
                                            .contact_name,
                                }"
                                placeholder="Ej. Juan Pérez"
                            />
                        </div>

                        <p
                            v-if="
                                form.errors.contact_name
                            "
                            class="admin-form-error"
                        >
                            {{
                                form.errors.contact_name
                            }}
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

                        <div
                            class="admin-form-input-wrapper"
                        >
                            <input
                                id="email"
                                v-model="form.email"
                                type="email"
                                class="admin-form-input"
                                :class="{
                                    'has-error':
                                        form.errors.email,
                                }"
                                placeholder="contacto@empresa.com"
                            />
                        </div>

                        <p
                            v-if="form.errors.email"
                            class="admin-form-error"
                        >
                            {{ form.errors.email }}
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

                        <div
                            class="admin-form-input-wrapper"
                        >
                            <input
                                id="phone"
                                v-model="form.phone"
                                type="tel"
                                class="admin-form-input"
                                :class="{
                                    'has-error':
                                        form.errors.phone,
                                }"
                                placeholder="Ej. 618 123 4567"
                            />
                        </div>

                        <p
                            v-if="form.errors.phone"
                            class="admin-form-error"
                        >
                            {{ form.errors.phone }}
                        </p>
                    </div>

                    <!-- WEBSITE -->

                    <div class="admin-form-group">
                        <label
                            for="website"
                            class="admin-form-label"
                        >
                            Sitio web
                        </label>

                        <div
                            class="admin-form-input-wrapper"
                        >
                            <input
                                id="website"
                                v-model="form.website"
                                type="url"
                                class="admin-form-input"
                                :class="{
                                    'has-error':
                                        form.errors.website,
                                }"
                                placeholder="https://www.empresa.com"
                            />
                        </div>

                        <p
                            v-if="form.errors.website"
                            class="admin-form-error"
                        >
                            {{ form.errors.website }}
                        </p>
                    </div>
                </div>

                <!-- STATUS -->

                <div class="admin-form-status">
                    <div class="admin-form-status-content">
                        <div class="admin-form-status-icon">
                            <Building2
                                :size="16"
                                :stroke-width="2"
                            />
                        </div>

                        <div>
                            <p class="admin-form-status-title">
                                Estado del patrocinador
                            </p>

                            <p class="admin-form-status-description">
                                Define si el patrocinador estará disponible
                                para ser asignado a las carreras.
                            </p>
                        </div>
                    </div>

                    <label class="admin-form-switch">
                        <input
                            v-model="form.is_active"
                            type="checkbox"
                        />

                        <span class="admin-form-switch-track">
                            <span
                                class="admin-form-switch-thumb"
                            ></span>
                        </span>

                        <span class="admin-form-switch-label">
                            {{
                                form.is_active
                                    ? 'Activo'
                                    : 'Inactivo'
                            }}
                        </span>
                    </label>
                </div>

                <!-- ACTIONS -->

                <div class="admin-form-actions">
                    <Link
                        :href="sponsors.index().url"
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
                                : 'Guardar patrocinador'
                        }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<style scoped>
.admin-form-section {
    margin-top: 26px;
    margin-bottom: 20px;
    padding-top: 20px;
    border-top: 1px solid #f0f3f6;
}

.admin-form-section-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.admin-form-section-title {
    margin: 0;
    color: #172b4d;
    font-size: 13px;
    font-weight: 700;
}

.admin-form-section-description {
    margin: 4px 0 0;
    color: #64748b;
    font-size: 11px;
    line-height: 1.45;
}

.admin-form-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    column-gap: 18px;
}

.admin-form-textarea {
    height: 100px;
    padding-top: 11px;
    padding-bottom: 11px;
    resize: vertical;
    line-height: 1.45;
}

.admin-form-file {
    padding-top: 9px;
    padding-bottom: 9px;
    cursor: pointer;
}

.admin-form-switch {
    display: inline-flex;
    align-items: center;
    gap: 9px;
    margin-top: 15px;
    cursor: pointer;
}

.admin-form-switch input {
    position: absolute;
    width: 1px;
    height: 1px;
    opacity: 0;
    pointer-events: none;
}

.admin-form-switch-track {
    position: relative;
    display: flex;
    align-items: center;
    width: 38px;
    height: 22px;
    padding: 2px;
    border-radius: 999px;
    background: #dce3e9;
    transition:
        background-color 0.2s ease;
}

.admin-form-switch-thumb {
    display: block;
    width: 18px;
    height: 18px;
    border-radius: 50%;
    background: #ffffff;
    box-shadow:
        0 1px 3px rgba(
            27,
            62,
            90,
            0.18
        );
    transition:
        transform 0.2s ease;
}

.admin-form-switch input:checked
    + .admin-form-switch-track {
    background: #249edb;
}

.admin-form-switch input:checked
    + .admin-form-switch-track
    .admin-form-switch-thumb {
    transform: translateX(16px);
}

.admin-form-switch-label {
    color: #172b4d;
    font-size: 12px;
    font-weight: 600;
}

@media (max-width: 700px) {
    .admin-form-grid {
        grid-template-columns: 1fr;
    }
}
</style>