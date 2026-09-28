<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import {
    ArrowLeft,
    Check,
    KeyRound,
    Save,
    ShieldCheck,
} from 'lucide-vue-next';

import admin from '@/routes/admin';

interface Permission {
    id: number;
    name: string;
    guard_name: string;
}

const props = defineProps<{
    permissions: Permission[];
}>();

const form = useForm({
    name: '',
    permissions: [] as string[],
});

const groupedPermissions = () => {
    const groups: Record<string, Permission[]> = {};

    props.permissions.forEach((permission) => {
        const module = permission.name.split('.')[0] ?? 'otros';

        if (!groups[module]) {
            groups[module] = [];
        }

        groups[module].push(permission);
    });

    return groups;
};

const togglePermission = (permission: string) => {
    const index = form.permissions.indexOf(permission);

    if (index === -1) {
        form.permissions.push(permission);
    } else {
        form.permissions.splice(index, 1);
    }
};

const toggleGroup = (permissions: Permission[]) => {
    const names = permissions.map(
        (permission) => permission.name,
    );

    const allSelected = names.every((name) =>
        form.permissions.includes(name),
    );

    if (allSelected) {
        form.permissions = form.permissions.filter(
            (permission) => !names.includes(permission),
        );
    } else {
        names.forEach((name) => {
            if (!form.permissions.includes(name)) {
                form.permissions.push(name);
            }
        });
    }
};

const submit = () => {
    form.post(
        admin.roles.store().url,
    );
};
</script>

<template>
    <Head title="Nuevo rol" />

    <div class="admin-page">
        <!-- =================================================
             HEADER
        ================================================== -->

        <header class="admin-page-header">
            <div>
                <p class="admin-page-eyebrow">
                    Roles
                </p>

                <h1 class="admin-page-title">
                    Nuevo rol
                </h1>

                <p class="admin-page-subtitle">
                    Crea un rol y selecciona los permisos que tendrá.
                </p>
            </div>

            <div class="admin-page-header-actions">
                <Link
                    :href="admin.roles.index().url"
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
                        Nombre del rol
                    </label>

                    <div class="admin-form-input-wrapper">
                        <input
                            id="name"
                            v-model="form.name"
                            type="text"
                            placeholder="Ejemplo: editor"
                            class="admin-form-input"
                            :class="{
                                'has-error': form.errors.name,
                            }"
                        />
                    </div>

                    <p class="admin-form-help">
                        Ejemplos: admin, staff, editor.
                    </p>

                    <p
                        v-if="form.errors.name"
                        class="admin-form-error"
                    >
                        {{ form.errors.name }}
                    </p>
                </div>

                <!-- PERMISSIONS -->

                <div class="admin-form-group">
                    <div class="mb-5">
                        <p class="admin-page-eyebrow">
                            Accesos
                        </p>

                        <h2 class="admin-page-title text-xl">
                            Permisos
                        </h2>

                        <p class="admin-form-help">
                            Selecciona las acciones que podrá realizar este
                            rol.
                        </p>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                        <div
                            v-for="(
                                permissions,
                                module
                            ) in groupedPermissions()"
                            :key="module"
                            class="admin-permission-group"
                        >
                            <!-- MODULE HEADER -->

                            <div class="admin-permission-group-header">
                                <div class="admin-permission-group-title">
                                    <div class="admin-permission-group-icon">
                                        <ShieldCheck
                                            :size="16"
                                            :stroke-width="2"
                                        />
                                    </div>

                                    <h3>
                                        {{ module }}
                                    </h3>
                                </div>

                                <button
                                    type="button"
                                    class="admin-permission-select"
                                    @click="
                                        toggleGroup(
                                            permissions,
                                        )
                                    "
                                >
                                    Seleccionar
                                </button>
                            </div>

                            <!-- PERMISSIONS -->

                            <div class="admin-permission-list">
                                <label
                                    v-for="permission in permissions"
                                    :key="permission.id"
                                    class="admin-permission-item"
                                >
                                    <input
                                        type="checkbox"
                                        :checked="
                                            form.permissions.includes(
                                                permission.name,
                                            )
                                        "
                                        class="admin-permission-checkbox"
                                        @change="
                                            togglePermission(
                                                permission.name,
                                            )
                                        "
                                    />

                                    <span class="admin-permission-check">
                                        <Check
                                            :size="12"
                                            :stroke-width="3"
                                        />
                                    </span>

                                    <span class="admin-permission-name">
                                        {{ permission.name }}
                                    </span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <p
                        v-if="form.errors.permissions"
                        class="admin-form-error mt-3"
                    >
                        {{ form.errors.permissions }}
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
                                Permisos del rol
                            </p>

                            <p class="admin-form-status-description">
                                Los permisos determinan las acciones que podrá
                                realizar este rol dentro del sistema.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- ACTIONS -->

                <div class="admin-form-actions">
                    <Link
                        :href="admin.roles.index().url"
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
                                ? 'Creando...'
                                : 'Crear rol'
                        }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>