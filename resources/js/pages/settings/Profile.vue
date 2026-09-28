<script setup lang="ts">
import {
    Form,
    Head,
    Link,
    usePage,
} from '@inertiajs/vue3';

import QRCode from 'qrcode';
import {
    computed,
    onMounted,
    ref,
} from 'vue';

import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController';
import DeleteUser from '@/components/DeleteUser.vue';
import InputError from '@/components/InputError.vue';

import { edit } from '@/routes/profile';
import { send } from '@/routes/verification';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Configuración del perfil',
                href: edit(),
            },
        ],
    },
});

/*
|--------------------------------------------------------------------------
| Interfaces
|--------------------------------------------------------------------------
*/

interface User {
    id: number;
    name: string;
    username: string;
    email: string;
    email_verified_at: string | null;
    qr_token: string | null;
}

interface Profile {
    phone: string | null;
    birth_date: string | null;
    avatar: string | null;
    city: string | null;
    country: string | null;
}

/*
|--------------------------------------------------------------------------
| Página
|--------------------------------------------------------------------------
*/

const page = usePage();

const user = computed(
    () => page.props.auth.user as User,
);

const profile = computed(
    () =>
        page.props.profile as
            | Profile
            | null,
);

/*
|--------------------------------------------------------------------------
| QR del usuario
|--------------------------------------------------------------------------
*/

const userQrCode = ref<string | null>(null);

const generateUserQrCode = async (): Promise<void> => {
    if (!user.value.qr_token) {
        console.warn(
            'El usuario no tiene qr_token.',
        );

        return;
    }

    try {
        userQrCode.value =
            await QRCode.toDataURL(
                user.value.qr_token,
                {
                    width: 400,
                    margin: 2,
                    errorCorrectionLevel: 'H',
                },
            );
    } catch (error) {
        console.error(
            'Error generando QR del usuario:',
            error,
        );
    }
};

onMounted(() => {
    generateUserQrCode();
});

/*
|--------------------------------------------------------------------------
| Avatar
|--------------------------------------------------------------------------
*/

const avatarPreview = ref<string | null>(
    null,
);

const handleAvatarChange = (
    event: Event,
) => {
    const target =
        event.target as HTMLInputElement;

    if (
        !target.files ||
        !target.files[0]
    ) {
        avatarPreview.value = null;

        return;
    }

    if (avatarPreview.value) {
        URL.revokeObjectURL(
            avatarPreview.value,
        );
    }

    avatarPreview.value =
        URL.createObjectURL(
            target.files[0],
        );
};
</script>

<template>
    <Head title="Configuración del perfil" />

    <h1 class="sr-only">
        Configuración del perfil
    </h1>

    <div class="flex flex-col gap-6">

        <!-- Código QR -->

        <div class="admin-form-card">
            <div class="admin-form">

                <div>
                    <p class="admin-page-eyebrow">
                        Identificación
                    </p>

                    <h3
                        class="mt-1 text-xl font-semibold text-[var(--sc-page-text)]"
                    >
                        Mi código QR
                    </h3>

                    <p
                        class="mt-2 max-w-xl text-sm leading-6 text-[var(--sc-page-text-secondary)]"
                    >
                        Presenta este código en venta para
                        identificar tu usuario y asociar tus
                        compras.
                    </p>
                </div>

                <div
                    class="flex flex-col gap-6 md:flex-row md:items-center md:justify-between"
                >
                    <!-- Información -->

                    <div class="flex-1">
                        <div
                            class="rounded-xl border border-[var(--sc-page-border-soft)] bg-[var(--sc-page-background)] p-4"
                        >
                            <p
                                class="text-sm font-semibold text-[var(--sc-page-text)]"
                            >
                                Código personal
                            </p>

                            <p
                                class="mt-1 text-xs leading-5 text-[var(--sc-page-text-secondary)]"
                            >
                                Este código QR es único y
                                permanente para tu cuenta.
                            </p>
                        </div>
                    </div>

                    <!-- QR -->

                    <div
                        class="flex shrink-0 justify-center md:justify-end"
                    >
                        <div
                            v-if="userQrCode"
                            class="rounded-2xl border border-[var(--sc-page-border)] bg-white p-4 shadow-sm"
                        >
                            <img
                                :src="userQrCode"
                                alt="Código QR personal"
                                class="h-56 w-56"
                            />
                        </div>

                        <div
                            v-else-if="user.qr_token"
                            class="flex h-56 w-56 items-center justify-center rounded-2xl border border-dashed border-[var(--sc-page-border)] bg-[var(--sc-page-background)]"
                        >
                            <span
                                class="text-sm text-[var(--sc-page-text-secondary)]"
                            >
                                Generando QR...
                            </span>
                        </div>

                        <div
                            v-else
                            class="flex h-56 w-56 items-center justify-center rounded-2xl border border-dashed border-[var(--sc-page-border)] bg-[var(--sc-page-background)] p-6 text-center"
                        >
                            <span
                                class="text-sm leading-5 text-[var(--sc-page-text-secondary)]"
                            >
                                No hay un código QR
                                disponible para esta
                                cuenta.
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Formulario -->

        <Form
            v-bind="ProfileController.update.form()"
            :options="{
                forceFormData: true,
            }"
            enctype="multipart/form-data"
            class="admin-form-card"
            v-slot="{
                errors,
                processing,
            }"
        >
            <div class="admin-form">

                <!-- Información personal -->

                <div>
                    <p class="admin-page-eyebrow">
                        Información personal
                    </p>

                    <h3
                        class="mt-1 text-xl font-semibold text-[var(--sc-page-text)]"
                    >
                        Datos del perfil
                    </h3>

                    <p
                        class="mt-2 text-sm text-[var(--sc-page-text-secondary)]"
                    >
                        Mantén actualizada la información de
                        tu cuenta.
                    </p>
                </div>

                <!-- Avatar -->

                <div class="admin-form-group">
                    <label
                        for="avatar"
                        class="admin-form-label"
                    >
                        Foto de perfil
                    </label>

                    <div
                        class="flex flex-col gap-5 sm:flex-row sm:items-center"
                    >
                        <!-- Vista previa -->

                        <div
                            class="flex h-24 w-24 shrink-0 items-center justify-center overflow-hidden rounded-full border border-[var(--sc-page-border)] bg-[var(--sc-page-background)]"
                        >
                            <img
                                v-if="avatarPreview"
                                :src="avatarPreview"
                                alt="Vista previa del avatar"
                                class="h-full w-full object-cover"
                            />

                            <img
                                v-else-if="profile?.avatar"
                                :src="`/storage/${profile.avatar}`"
                                alt="Avatar actual"
                                class="h-full w-full object-cover"
                            />

                            <span
                                v-else
                                class="text-xs text-[var(--sc-page-muted)]"
                            >
                                Sin foto
                            </span>
                        </div>

                        <!-- Selector -->

                        <div class="flex-1">
                            <input
                                id="avatar"
                                type="file"
                                name="avatar"
                                accept="image/jpeg,image/png,image/webp"
                                class="admin-form-input"
                                @change="
                                    handleAvatarChange
                                "
                            />

                            <p class="admin-form-help">
                                JPG, PNG o WebP. Máximo 2 MB.
                            </p>

                            <InputError
                                class="admin-form-error"
                                :message="errors.avatar"
                            />
                        </div>
                    </div>
                </div>

                <!-- Nombre -->

                <div class="admin-form-group">
                    <label
                        for="name"
                        class="admin-form-label"
                    >
                        Nombre
                    </label>

                    <input
                        id="name"
                        class="admin-form-input"
                        name="name"
                        :value="user.name"
                        required
                        autocomplete="name"
                        placeholder="Nombre completo"
                    />

                    <InputError
                        class="admin-form-error"
                        :message="errors.name"
                    />
                </div>

                <!-- Nombre de usuario -->

                <div class="admin-form-group">
                    <label
                        for="username"
                        class="admin-form-label"
                    >
                        Nombre de usuario
                    </label>

                    <input
                        id="username"
                        type="text"
                        class="admin-form-input"
                        :value="user.username"
                        readonly
                        disabled
                        autocomplete="username"
                    />

                    <p class="admin-form-help">
                        El nombre de usuario se utiliza para iniciar sesión
                        y no puede modificarse desde el perfil.
                    </p>
                </div>

                <!-- Correo -->

                <div class="admin-form-group">
                    <label
                        for="email"
                        class="admin-form-label"
                    >
                        Correo electrónico
                    </label>

                    <input
                        id="email"
                        type="email"
                        class="admin-form-input"
                        name="email"
                        :value="user.email"
                        required
                        autocomplete="email"
                        placeholder="Correo electrónico"
                    />

                    <InputError
                        class="admin-form-error"
                        :message="errors.email"
                    />
                </div>

                <!-- Teléfono -->

                <div class="admin-form-group">
                    <label
                        for="phone"
                        class="admin-form-label"
                    >
                        Teléfono
                    </label>

                    <input
                        id="phone"
                        type="tel"
                        class="admin-form-input"
                        name="phone"
                        :value="
                            profile?.phone ?? ''
                        "
                        autocomplete="tel"
                        placeholder="Número de teléfono"
                    />

                    <InputError
                        class="admin-form-error"
                        :message="errors.phone"
                    />
                </div>

                <!-- Fecha de nacimiento -->

                <div class="admin-form-group">
                    <label
                        for="birth_date"
                        class="admin-form-label"
                    >
                        Fecha de nacimiento
                    </label>

                    <input
                        id="birth_date"
                        type="date"
                        class="admin-form-input"
                        name="birth_date"
                        :value="
                            profile?.birth_date ?? ''
                        "
                        autocomplete="bday"
                    />

                    <InputError
                        class="admin-form-error"
                        :message="errors.birth_date"
                    />
                </div>

                <!-- Ciudad y país -->

                <div
                    class="grid gap-6 md:grid-cols-2"
                >
                    <!-- Ciudad -->

                    <div class="admin-form-group">
                        <label
                            for="city"
                            class="admin-form-label"
                        >
                            Ciudad
                        </label>

                        <input
                            id="city"
                            type="text"
                            class="admin-form-input"
                            name="city"
                            :value="
                                profile?.city ?? ''
                            "
                            autocomplete="address-level2"
                            placeholder="Ciudad"
                        />

                        <InputError
                            class="admin-form-error"
                            :message="errors.city"
                        />
                    </div>

                    <!-- País -->

                    <div class="admin-form-group">
                        <label
                            for="country"
                            class="admin-form-label"
                        >
                            País
                        </label>

                        <input
                            id="country"
                            type="text"
                            class="admin-form-input"
                            name="country"
                            :value="
                                profile?.country ?? ''
                            "
                            autocomplete="country-name"
                            placeholder="País"
                        />

                        <InputError
                            class="admin-form-error"
                            :message="errors.country"
                        />
                    </div>
                </div>

                <!-- Verificación de correo -->

                <div
                    v-if="
                        page.props.mustVerifyEmail &&
                        !user.email_verified_at
                    "
                    class="admin-form-status"
                >
                    <div class="admin-form-status-content">
                        <div>
                            <p
                                class="admin-form-status-title"
                            >
                                Correo electrónico no verificado
                            </p>

                            <p
                                class="admin-form-status-description"
                            >
                                Tu dirección de correo electrónico
                                no está verificada.
                            </p>

                            <Link
                                :href="send()"
                                as="button"
                                class="mt-2 text-sm font-medium text-[var(--sc-page-blue)] underline underline-offset-4 transition-colors hover:text-[var(--sc-page-blue-dark)]"
                            >
                                Haz clic aquí para volver a
                                enviar el correo de verificación.
                            </Link>
                        </div>
                    </div>

                    <div
                        v-if="
                            page.props.status ===
                            'verification-link-sent'
                        "
                        class="mt-3 text-sm font-medium text-[var(--sc-page-green)]"
                    >
                        Se ha enviado un nuevo enlace de
                        verificación a tu dirección de correo
                        electrónico.
                    </div>
                </div>

                <!-- Guardar -->

                <div class="admin-form-actions">
                    <button
                        type="submit"
                        class="admin-btn admin-btn-primary"
                        :disabled="processing"
                        data-test="update-profile-button"
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

        <!-- Eliminar cuenta -->

        <DeleteUser />
    </div>
</template>
