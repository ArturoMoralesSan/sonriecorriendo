<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import {
    ArrowLeft,
    KeyRound,
    Save,
} from 'lucide-vue-next';

import admin from '@/routes/admin';

interface Permission {
    id: number;
    name: string;
    guard_name: string;
}

const props = defineProps<{
    permission: Permission;
}>();

const form = useForm({
    name: props.permission.name,
});

const submit = () => {
    form.put(admin.permissions.update(props.permission.id).url);
};
</script>

<template>
    <Head title="Editar permiso" />

    <div class="admin-page">
        <!-- =================================================
             HEADER
        ================================================== -->

        <header class="admin-page-header">
            <div>
                <p class="admin-page-eyebrow">
                    Permisos
                </p>

                <h1 class="admin-page-title">
                    Editar permiso
                </h1>

                <p class="admin-page-subtitle">
                    Modifica la información del permiso.
                </p>
            </div>

            <div class="admin-page-header-actions">
                <Link
                    :href="admin.permissions.index().url"
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
                        Nombre del permiso
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
                            placeholder="Ej. users.view"
                        />
                    </div>

                    <p class="admin-form-help">
                        Utiliza el formato
                        <strong>modulo.accion</strong>.
                        Por ejemplo: users.view, users.create o
                        users.delete.
                    </p>

                    <p
                        v-if="form.errors.name"
                        class="admin-form-error"
                    >
                        {{ form.errors.name }}
                    </p>
                </div>

                <!-- GUARD -->

                <div class="admin-form-group">
                    <label
                        for="guard_name"
                        class="admin-form-label"
                    >
                        Guard
                    </label>

                    <div class="admin-form-input-wrapper">
                        <input
                            id="guard_name"
                            :value="permission.guard_name"
                            type="text"
                            disabled
                            class="admin-form-input"
                        />
                    </div>

                    <p class="admin-form-help">
                        El guard determina el sistema de autenticación
                        asociado al permiso.
                    </p>
                </div>

                <!-- INFO -->

                <div class="admin-form-status">
                    <div class="admin-form-status-content">
                        <div class="admin-form-status-icon">
                            <KeyRound
                                :size="16"
                                :stroke-width="2"
                            />
                        </div>

                        <div>
                            <p class="admin-form-status-title">
                                Identificador del permiso
                            </p>

                            <p class="admin-form-status-description">
                                El nombre debe ser único y seguirá el formato
                                módulo.acción para facilitar su organización.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- ACTIONS -->

                <div class="admin-form-actions">
                    <Link
                        :href="admin.permissions.index().url"
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
                                : 'Guardar cambios'
                        }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>