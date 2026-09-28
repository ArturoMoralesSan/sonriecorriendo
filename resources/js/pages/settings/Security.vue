<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';

import SecurityController from '@/actions/App/Http/Controllers/Settings/SecurityController';
import InputError from '@/components/InputError.vue';
import type { Props as ManagePasskeysProps } from '@/components/ManagePasskeys.vue';
import ManagePasskeys from '@/components/ManagePasskeys.vue';
import type { Props as ManageTwoFactorProps } from '@/components/ManageTwoFactor.vue';
import ManageTwoFactor from '@/components/ManageTwoFactor.vue';
import PasswordInput from '@/components/PasswordInput.vue';

import { edit } from '@/routes/security';

type Props = {
    passwordRules: string;
} & ManagePasskeysProps &
    ManageTwoFactorProps;

const props = defineProps<Props>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Configuración de seguridad',
                href: edit(),
            },
        ],
    },
});
</script>

<template>
    <Head title="Configuración de seguridad" />

    <h1 class="sr-only">
        Configuración de seguridad
    </h1>

    <div class="flex flex-col gap-6">
        <header class="admin-page-header">
            <div>
                <p class="admin-page-eyebrow">
                    Configuración
                </p>

                <h2 class="admin-page-title">
                    Seguridad
                </h2>

                <p class="admin-page-subtitle">
                    Administra la contraseña y las opciones de seguridad de tu cuenta.
                </p>
            </div>
        </header>

        <Form
            v-bind="SecurityController.update.form()"
            :options="{
                preserveScroll: true,
            }"
            reset-on-success
            :reset-on-error="[
                'password',
                'password_confirmation',
                'current_password',
            ]"
            class="admin-form-card"
            v-slot="{ errors, processing }"
        >
            <div class="admin-form">
                <div>
                    <p class="admin-page-eyebrow">
                        Contraseña
                    </p>

                    <h3
                        class="mt-1 text-xl font-semibold text-[var(--sc-page-text)]"
                    >
                        Actualizar contraseña
                    </h3>

                    <p
                        class="mt-2 text-sm leading-6 text-[var(--sc-page-text-secondary)]"
                    >
                        Asegúrate de que tu cuenta utilice una contraseña larga
                        y aleatoria para mantenerla segura.
                    </p>
                </div>

                <div class="admin-form-group">
                    <label
                        for="current_password"
                        class="admin-form-label"
                    >
                        Contraseña actual
                    </label>

                    <PasswordInput
                        id="current_password"
                        name="current_password"
                        class="admin-form-input"
                        autocomplete="current-password"
                        placeholder="Contraseña actual"
                    />

                    <InputError
                        class="admin-form-error"
                        :message="errors.current_password"
                    />
                </div>

                <div class="admin-form-group">
                    <label
                        for="password"
                        class="admin-form-label"
                    >
                        Nueva contraseña
                    </label>

                    <PasswordInput
                        id="password"
                        name="password"
                        class="admin-form-input"
                        autocomplete="new-password"
                        placeholder="Nueva contraseña"
                        :passwordrules="props.passwordRules"
                    />

                    <InputError
                        class="admin-form-error"
                        :message="errors.password"
                    />

                    <p class="admin-form-help">
                        Utiliza una contraseña larga y aleatoria para mejorar
                        la seguridad de tu cuenta.
                    </p>
                </div>

                <div class="admin-form-group">
                    <label
                        for="password_confirmation"
                        class="admin-form-label"
                    >
                        Confirmar contraseña
                    </label>

                    <PasswordInput
                        id="password_confirmation"
                        name="password_confirmation"
                        class="admin-form-input"
                        autocomplete="new-password"
                        placeholder="Confirmar contraseña"
                        :passwordrules="props.passwordRules"
                    />

                    <InputError
                        class="admin-form-error"
                        :message="errors.password_confirmation"
                    />
                </div>

                <div class="admin-form-actions">
                    <button
                        type="submit"
                        class="admin-btn admin-btn-primary"
                        :disabled="processing"
                        data-test="update-password-button"
                    >
                        <span
                            v-if="processing"
                            class="admin-btn-loader"
                        ></span>

                        {{
                            processing
                                ? 'Guardando...'
                                : 'Guardar'
                        }}
                    </button>
                </div>
            </div>
        </Form>

        <ManageTwoFactor
            :canManageTwoFactor="canManageTwoFactor"
            :requiresConfirmation="requiresConfirmation"
            :twoFactorEnabled="twoFactorEnabled"
        />

        <ManagePasskeys
            :canManagePasskeys="canManagePasskeys"
            :passkeys="passkeys"
        />
    </div>
</template>