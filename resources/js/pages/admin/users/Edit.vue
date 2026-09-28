<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import {
    ArrowLeft,
    Save,
} from 'lucide-vue-next';

import admin from '@/routes/admin';

interface User {
    id: number;
    name: string;
    username: string;
    email: string;
    roles: Role[];
}

interface Role {
    id: number;
    name: string;
}

const props = defineProps<{
    user: User;
    roles: Role[];
}>();

const form = useForm({
    name: props.user.name,
    username: props.user.username,
    email: props.user.email,
    password: '',
    password_confirmation: '',
    role: props.user.roles[0]?.name ?? '',
});

const submit = () => {
    form.put(
        admin.users.update(
            props.user.id,
        ).url,
    );
};
</script>

<template>
    <Head title="Editar usuario" />

    <div class="admin-page">
        <!-- Encabezado -->
        <div class="admin-page-header">
            <div>
                <div class="admin-page-eyebrow">
                    Usuarios
                </div>

                <h1 class="admin-page-title">
                    Editar usuario
                </h1>

                <p class="admin-page-subtitle">
                    Modifica la información y el rol del usuario.
                </p>
            </div>

            <div class="admin-page-header-actions">
                <Link
                    :href="admin.users.index().url"
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

        <!-- Formulario -->
        <div class="admin-form-card">
            <form
                class="admin-form"
                @submit.prevent="submit"
            >
                <!-- Nombre -->
                <div class="admin-form-group">
                    <label
                        for="name"
                        class="admin-form-label"
                    >
                        Nombre
                    </label>

                    <div class="admin-form-input-wrapper">
                        <input
                            id="name"
                            v-model="form.name"
                            type="text"
                            placeholder="Nombre completo"
                            class="admin-form-input admin-form-input-with-icon"
                            :class="{
                                'has-error': form.errors.name,
                            }"
                        />
                    </div>

                    <p
                        v-if="form.errors.name"
                        class="admin-form-error"
                    >
                        {{ form.errors.name }}
                    </p>
                </div>

                <!-- Nombre de usuario -->
                <div class="admin-form-group">
                    <label
                        for="username"
                        class="admin-form-label"
                    >
                        Nombre de usuario
                    </label>

                    <div class="admin-form-input-wrapper">
                        <input
                            id="username"
                            v-model="form.username"
                            type="text"
                            placeholder="Nombre de usuario"
                            autocomplete="username"
                            class="admin-form-input admin-form-input-with-icon"
                            :class="{
                                'has-error': form.errors.username,
                            }"
                        />
                    </div>

                    <p class="admin-form-help">
                        Este nombre se utilizará para iniciar sesión.
                    </p>

                    <p
                        v-if="form.errors.username"
                        class="admin-form-error"
                    >
                        {{ form.errors.username }}
                    </p>
                </div>

                <!-- Correo electrónico -->
                <div class="admin-form-group">
                    <label
                        for="email"
                        class="admin-form-label"
                    >
                        Correo electrónico
                    </label>

                    <div class="admin-form-input-wrapper">
                        <input
                            id="email"
                            v-model="form.email"
                            type="email"
                            placeholder="usuario@ejemplo.com"
                            autocomplete="email"
                            class="admin-form-input admin-form-input-with-icon"
                            :class="{
                                'has-error': form.errors.email,
                            }"
                        />
                    </div>

                    <p class="admin-form-help">
                        Se utilizará para recuperación de contraseña y
                        notificaciones.
                    </p>

                    <p
                        v-if="form.errors.email"
                        class="admin-form-error"
                    >
                        {{ form.errors.email }}
                    </p>
                </div>

                <!-- Nueva contraseña -->
                <div class="admin-form-group">
                    <label
                        for="password"
                        class="admin-form-label"
                    >
                        Nueva contraseña
                    </label>

                    <div class="admin-form-input-wrapper">
                        <input
                            id="password"
                            v-model="form.password"
                            type="password"
                            placeholder="Dejar vacío para conservar la actual"
                            autocomplete="new-password"
                            class="admin-form-input admin-form-input-with-icon"
                            :class="{
                                'has-error': form.errors.password,
                            }"
                        />
                    </div>

                    <p class="admin-form-help">
                        Deja este campo vacío si no deseas cambiar la
                        contraseña.
                    </p>

                    <p
                        v-if="form.errors.password"
                        class="admin-form-error"
                    >
                        {{ form.errors.password }}
                    </p>
                </div>

                <!-- Confirmar contraseña -->
                <div class="admin-form-group">
                    <label
                        for="password_confirmation"
                        class="admin-form-label"
                    >
                        Confirmar nueva contraseña
                    </label>

                    <div class="admin-form-input-wrapper">
                        <input
                            id="password_confirmation"
                            v-model="form.password_confirmation"
                            type="password"
                            placeholder="Repite la nueva contraseña"
                            autocomplete="new-password"
                            class="admin-form-input admin-form-input-with-icon"
                            :class="{
                                'has-error':
                                    form.errors.password_confirmation,
                            }"
                        />
                    </div>

                    <p
                        v-if="form.errors.password_confirmation"
                        class="admin-form-error"
                    >
                        {{ form.errors.password_confirmation }}
                    </p>
                </div>

                <!-- Rol -->
                <div class="admin-form-group">
                    <label
                        for="role"
                        class="admin-form-label"
                    >
                        Rol
                    </label>

                    <div class="admin-form-input-wrapper">
                        <select
                            id="role"
                            v-model="form.role"
                            class="admin-form-input admin-form-input-with-icon"
                            :class="{
                                'has-error': form.errors.role,
                            }"
                        >
                            <option
                                v-for="role in roles"
                                :key="role.id"
                                :value="role.name"
                            >
                                {{ role.name }}
                            </option>
                        </select>
                    </div>

                    <p class="admin-form-help">
                        Selecciona el rol que tendrá este usuario dentro del
                        sistema.
                    </p>

                    <p
                        v-if="form.errors.role"
                        class="admin-form-error"
                    >
                        {{ form.errors.role }}
                    </p>
                </div>

                <!-- Acciones -->
                <div class="admin-form-actions">
                    <Link
                        :href="admin.users.index().url"
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
