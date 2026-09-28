<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import {
    ArrowLeft,
    Save,
} from 'lucide-vue-next';

import admin from '@/routes/admin';

const form = useForm({
    name: '',
    code: '',
    description: '',
    is_active: true,
    sort_order: 0,
});

const submit = () => {
    form.post(admin.paymentMethods.store().url);
};

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Panel',
                href: admin.dashboard(),
            },
            {
                title: 'Métodos de pago',
                href: admin.paymentMethods.index(),
            },
            {
                title: 'Nuevo método de pago',
                href: admin.paymentMethods.create(),
            },
        ],
    },
});
</script>

<template>
    <Head title="Nuevo método de pago" />

    <div class="admin-page">
        <!-- HEADER -->
        <div class="admin-page-header">
            <div>
                <p class="admin-page-eyebrow">
                    Métodos de pago
                </p>

                <h1 class="admin-page-title">
                    Nuevo método de pago
                </h1>

                <p class="admin-page-subtitle">
                    Registra un nuevo método de pago para las órdenes de
                    boletos.
                </p>
            </div>

            <div class="admin-page-header-actions">
                <Link
                    :href="admin.paymentMethods.index().url"
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
                        placeholder="Ej. Efectivo"
                    />

                    <p
                        v-if="form.errors.name"
                        class="admin-form-error"
                    >
                        {{ form.errors.name }}
                    </p>
                </div>

                <!-- CODE -->
                <div class="admin-form-group">
                    <label
                        for="code"
                        class="admin-form-label"
                    >
                        Código
                    </label>

                    <input
                        id="code"
                        v-model="form.code"
                        type="text"
                        class="admin-form-input"
                        :class="{ 'has-error': form.errors.code }"
                        placeholder="Ej. cash"
                    />

                    <p class="admin-form-help">
                        Usa un código único, por ejemplo:
                        cash, card, transfer o mercadopago.
                    </p>

                    <p
                        v-if="form.errors.code"
                        class="admin-form-error"
                    >
                        {{ form.errors.code }}
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
                        placeholder="Describe el método de pago..."
                    />

                    <p
                        v-if="form.errors.description"
                        class="admin-form-error"
                    >
                        {{ form.errors.description }}
                    </p>
                </div>

                <!-- ORDER -->
                <div class="admin-form-group admin-form-group-small">
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
                        :class="{ 'has-error': form.errors.sort_order }"
                    />

                    <p class="admin-form-help">
                        Define el orden en que aparecerá el método de pago.
                    </p>

                    <p
                        v-if="form.errors.sort_order"
                        class="admin-form-error"
                    >
                        {{ form.errors.sort_order }}
                    </p>
                </div>

                <!-- STATUS -->
                <div class="admin-form-status">
                    <div class="admin-form-status-content">
                        <div class="admin-form-status-icon">
                            <span></span>
                        </div>

                        <div>
                            <p class="admin-form-status-title">
                                Método activo
                            </p>

                            <p class="admin-form-status-description">
                                Los métodos inactivos no estarán disponibles
                                para nuevas órdenes.
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

                <!-- ACTIONS -->
                <div class="admin-form-actions">
                    <Link
                        :href="admin.paymentMethods.index().url"
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
                                : 'Guardar método de pago'
                        }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>