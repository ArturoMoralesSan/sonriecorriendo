<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import {
    ArrowLeft,
    Building2,
    Save,
} from 'lucide-vue-next';

import admin from '@/routes/admin';

const form = useForm({
    name: '',
    street: '',
    exterior_number: '',
    interior_number: '',
    neighborhood: '',
    postal_code: '',
    city: 'Durango',
    state: 'Durango',
    references: '',
    phone: '',
    opening_time: '',
    closing_time: '',
    opening_hours: '',
    is_active: true,
});

const submit = (): void => {
    form.post(admin.branches.store().url);
};

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Panel',
                href: admin.dashboard(),
            },
            {
                title: 'Sucursales',
                href: admin.branches.index(),
            },
            {
                title: 'Nueva sucursal',
                href: admin.branches.create(),
            },
        ],
    },
});
</script>

<template>
    <Head title="Nueva sucursal" />

    <div class="admin-page">
        <!-- HEADER -->

        <header class="admin-page-header">
            <div>
                <p class="admin-page-eyebrow">
                    Operación
                </p>

                <h1 class="admin-page-title">
                    Nueva sucursal
                </h1>

                <p class="admin-page-subtitle">
                    Registra una ubicación para la entrega o recolección
                    de pedidos.
                </p>
            </div>

            <div class="admin-page-header-actions">
                <Link
                    :href="admin.branches.index().url"
                    class="admin-btn admin-btn-secondary"
                >
                    <ArrowLeft :size="14" :stroke-width="2" />
                    Regresar
                </Link>
            </div>
        </header>

        <!-- FORM -->

        <div class="admin-form-card">
            <form
                class="admin-form"
                @submit.prevent="submit"
            >
                <!-- INFORMACIÓN GENERAL -->

                <div class="admin-form-section">
                    <div class="admin-form-section-header">
                        <div>
                            <h2 class="admin-form-section-title">
                                Información general
                            </h2>

                            <p class="admin-form-section-description">
                                Define el nombre y los datos de contacto
                                de la sucursal.
                            </p>
                        </div>
                    </div>

                    <div class="admin-form-grid">
                        <!-- NAME -->

                        <div class="admin-form-group">
                            <label
                                for="name"
                                class="admin-form-label"
                            >
                                Nombre de la sucursal *
                            </label>

                            <input
                                id="name"
                                v-model="form.name"
                                type="text"
                                maxlength="255"
                                required
                                class="admin-form-input"
                                :class="{
                                    'has-error': form.errors.name,
                                }"
                                placeholder="Ej. Sucursal Centro"
                            />

                            <p class="admin-form-help">
                                Nombre con el que se identificará el punto
                                de entrega.
                            </p>

                            <p
                                v-if="form.errors.name"
                                class="admin-form-error"
                            >
                                {{ form.errors.name }}
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
                                maxlength="30"
                                class="admin-form-input"
                                :class="{
                                    'has-error': form.errors.phone,
                                }"
                                placeholder="Ej. 6181234567"
                            />

                            <p class="admin-form-help">
                                Número de contacto de la sucursal.
                            </p>

                            <p
                                v-if="form.errors.phone"
                                class="admin-form-error"
                            >
                                {{ form.errors.phone }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- DIRECCIÓN -->

                <div class="admin-form-section">
                    <div class="admin-form-section-header">
                        <div>
                            <h2 class="admin-form-section-title">
                                Dirección
                            </h2>

                            <p class="admin-form-section-description">
                                Registra la dirección que se mostrará al
                                cliente al seleccionar esta sucursal.
                            </p>
                        </div>
                    </div>

                    <div class="admin-form-grid">
                        <!-- STREET -->

                        <div class="admin-form-group">
                            <label
                                for="street"
                                class="admin-form-label"
                            >
                                Calle *
                            </label>

                            <input
                                id="street"
                                v-model="form.street"
                                type="text"
                                maxlength="255"
                                required
                                class="admin-form-input"
                                :class="{
                                    'has-error': form.errors.street,
                                }"
                                placeholder="Nombre de la calle"
                            />

                            <p
                                v-if="form.errors.street"
                                class="admin-form-error"
                            >
                                {{ form.errors.street }}
                            </p>
                        </div>

                        <!-- EXTERIOR NUMBER -->

                        <div class="admin-form-group">
                            <label
                                for="exterior_number"
                                class="admin-form-label"
                            >
                                Número exterior *
                            </label>

                            <input
                                id="exterior_number"
                                v-model="form.exterior_number"
                                type="text"
                                maxlength="50"
                                required
                                class="admin-form-input"
                                :class="{
                                    'has-error': form.errors.exterior_number,
                                }"
                                placeholder="Ej. 120"
                            />

                            <p
                                v-if="form.errors.exterior_number"
                                class="admin-form-error"
                            >
                                {{ form.errors.exterior_number }}
                            </p>
                        </div>

                        <!-- INTERIOR NUMBER -->

                        <div class="admin-form-group">
                            <label
                                for="interior_number"
                                class="admin-form-label"
                            >
                                Número interior
                            </label>

                            <input
                                id="interior_number"
                                v-model="form.interior_number"
                                type="text"
                                maxlength="50"
                                class="admin-form-input"
                                :class="{
                                    'has-error': form.errors.interior_number,
                                }"
                                placeholder="Opcional"
                            />

                            <p
                                v-if="form.errors.interior_number"
                                class="admin-form-error"
                            >
                                {{ form.errors.interior_number }}
                            </p>
                        </div>

                        <!-- NEIGHBORHOOD -->

                        <div class="admin-form-group">
                            <label
                                for="neighborhood"
                                class="admin-form-label"
                            >
                                Colonia *
                            </label>

                            <input
                                id="neighborhood"
                                v-model="form.neighborhood"
                                type="text"
                                maxlength="255"
                                required
                                class="admin-form-input"
                                :class="{
                                    'has-error': form.errors.neighborhood,
                                }"
                                placeholder="Nombre de la colonia"
                            />

                            <p
                                v-if="form.errors.neighborhood"
                                class="admin-form-error"
                            >
                                {{ form.errors.neighborhood }}
                            </p>
                        </div>

                        <!-- POSTAL CODE -->

                        <div class="admin-form-group">
                            <label
                                for="postal_code"
                                class="admin-form-label"
                            >
                                Código postal *
                            </label>

                            <input
                                id="postal_code"
                                v-model="form.postal_code"
                                type="text"
                                maxlength="10"
                                required
                                class="admin-form-input"
                                :class="{
                                    'has-error': form.errors.postal_code,
                                }"
                                placeholder="Ej. 34000"
                            />

                            <p
                                v-if="form.errors.postal_code"
                                class="admin-form-error"
                            >
                                {{ form.errors.postal_code }}
                            </p>
                        </div>

                        <!-- CITY -->

                        <div class="admin-form-group">
                            <label
                                for="city"
                                class="admin-form-label"
                            >
                                Ciudad *
                            </label>

                            <input
                                id="city"
                                v-model="form.city"
                                type="text"
                                maxlength="255"
                                required
                                class="admin-form-input"
                                :class="{
                                    'has-error': form.errors.city,
                                }"
                                placeholder="Ciudad"
                            />

                            <p
                                v-if="form.errors.city"
                                class="admin-form-error"
                            >
                                {{ form.errors.city }}
                            </p>
                        </div>

                        <!-- STATE -->

                        <div class="admin-form-group">
                            <label
                                for="state"
                                class="admin-form-label"
                            >
                                Estado *
                            </label>

                            <input
                                id="state"
                                v-model="form.state"
                                type="text"
                                maxlength="255"
                                required
                                class="admin-form-input"
                                :class="{
                                    'has-error': form.errors.state,
                                }"
                                placeholder="Estado"
                            />

                            <p
                                v-if="form.errors.state"
                                class="admin-form-error"
                            >
                                {{ form.errors.state }}
                            </p>
                        </div>
                    </div>

                    <!-- REFERENCES -->

                    <div class="admin-form-group">
                        <label
                            for="references"
                            class="admin-form-label"
                        >
                            Referencias
                        </label>

                        <textarea
                            id="references"
                            v-model="form.references"
                            rows="3"
                            maxlength="5000"
                            class="admin-form-input admin-form-textarea"
                            :class="{
                                'has-error': form.errors.references,
                            }"
                            placeholder="Indicaciones para localizar la sucursal..."
                        ></textarea>

                        <p class="admin-form-help">
                            Referencias adicionales para facilitar la
                            ubicación del establecimiento.
                        </p>

                        <p
                            v-if="form.errors.references"
                            class="admin-form-error"
                        >
                            {{ form.errors.references }}
                        </p>
                    </div>
                </div>

                <!-- HORARIO -->

                <div class="admin-form-section">
                    <div class="admin-form-section-header">
                        <div>
                            <h2 class="admin-form-section-title">
                                Horario de atención
                            </h2>

                            <p class="admin-form-section-description">
                                Configura las horas habituales de apertura
                                y cierre.
                            </p>
                        </div>
                    </div>

                    <div class="admin-form-grid">
                        <!-- OPENING TIME -->

                        <div class="admin-form-group">
                            <label
                                for="opening_time"
                                class="admin-form-label"
                            >
                                Hora de apertura
                            </label>

                            <input
                                id="opening_time"
                                v-model="form.opening_time"
                                type="time"
                                class="admin-form-input"
                                :class="{
                                    'has-error': form.errors.opening_time,
                                }"
                            />

                            <p
                                v-if="form.errors.opening_time"
                                class="admin-form-error"
                            >
                                {{ form.errors.opening_time }}
                            </p>
                        </div>

                        <!-- CLOSING TIME -->

                        <div class="admin-form-group">
                            <label
                                for="closing_time"
                                class="admin-form-label"
                            >
                                Hora de cierre
                            </label>

                            <input
                                id="closing_time"
                                v-model="form.closing_time"
                                type="time"
                                class="admin-form-input"
                                :class="{
                                    'has-error': form.errors.closing_time,
                                }"
                            />

                            <p
                                v-if="form.errors.closing_time"
                                class="admin-form-error"
                            >
                                {{ form.errors.closing_time }}
                            </p>
                        </div>
                    </div>

                    <!-- OPENING HOURS -->

                    <div class="admin-form-group">
                        <label
                            for="opening_hours"
                            class="admin-form-label"
                        >
                            Notas del horario
                        </label>

                        <textarea
                            id="opening_hours"
                            v-model="form.opening_hours"
                            rows="3"
                            maxlength="1000"
                            class="admin-form-input admin-form-textarea"
                            :class="{
                                'has-error': form.errors.opening_hours,
                            }"
                            placeholder="Ej. Lunes a viernes; sábado con horario reducido."
                        ></textarea>

                        <p class="admin-form-help">
                            Opcional. Úsalo para especificar días de atención,
                            descansos u otras condiciones.
                        </p>

                        <p
                            v-if="form.errors.opening_hours"
                            class="admin-form-error"
                        >
                            {{ form.errors.opening_hours }}
                        </p>
                    </div>
                </div>

                <!-- ESTADO DE LA SUCURSAL -->

                <div class="admin-form-status">
                    <div class="admin-form-status-content">
                        <div class="admin-form-status-icon">
                            <Building2
                                :size="18"
                                :stroke-width="2"
                            />
                        </div>

                        <div class="admin-form-status-info">
                            <p class="admin-form-status-title">
                                Estado de la sucursal
                            </p>

                            <p class="admin-form-status-description">
                                {{
                                    form.is_active
                                        ? 'La sucursal está activa y disponible para la recolección de pedidos.'
                                        : 'La sucursal está inactiva y no estará disponible para nuevos pedidos.'
                                }}
                            </p>
                        </div>

                        <label class="admin-switch">
                            <input
                                v-model="form.is_active"
                                type="checkbox"
                            />

                            <span class="admin-switch-slider"></span>

                            <span class="admin-switch-label">
                                {{ form.is_active ? 'Activa' : 'Inactiva' }}
                            </span>
                        </label>
                    </div>

                    <p
                        v-if="form.errors.is_active"
                        class="admin-form-error"
                    >
                        {{ form.errors.is_active }}
                    </p>
                </div>

                <!-- ACTIONS -->

                <div class="admin-form-actions">
                    <Link
                        :href="admin.branches.index().url"
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
                                : 'Guardar sucursal'
                        }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>


<style scoped>
.admin-form-section {
    padding-bottom: 28px;
    margin-bottom: 28px;
    border-bottom: 1px solid #e8eef3;
}

.admin-form-section:last-of-type {
    margin-bottom: 0;
    padding-bottom: 0;
    border-bottom: 0;
}

.admin-form-section-header {
    margin-bottom: 20px;
}

.admin-form-section-title {
    margin: 0;
    color: var(--sc-page-text);
    font-size: 15px;
    font-weight: 700;
}

.admin-form-section-description {
    margin: 5px 0 0;
    color: var(--sc-page-text-secondary);
    font-size: 11px;
    line-height: 1.5;
}

.admin-form-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 18px;
}

.admin-form-textarea {
    min-height: 90px;
    resize: vertical;
}


/* ESTADO DE LA SUCURSAL */

.admin-form-status-content {
    display: grid !important;
    grid-template-columns: 42px minmax(0, 1fr) 110px !important;
    align-items: center !important;
    gap: 16px !important;
    padding: 8px 0;
    width: 100%;
    box-sizing: border-box;
}

.admin-form-status-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 42px;
    height: 42px;
    flex: none;
    border-radius: 12px;
    background: var(--sc-page-blue-light);
    color: var(--sc-page-blue);
}

.admin-form-status-info {
    display: block;
    min-width: 0;
    width: 100%;
    overflow-wrap: anywhere;
}

.admin-form-status-title {
    margin: 0;
    color: var(--sc-page-text);
    font-size: 13px;
    font-weight: 700;
}

.admin-form-status-description {
    margin: 5px 0 0;
    color: var(--sc-page-text-secondary);
    font-size: 11px;
    line-height: 1.6;
}

.admin-form-status-content .admin-switch {
    display: flex !important;
    align-items: center;
    justify-content: flex-end;
    gap: 8px;
    width: 110px;
    min-width: 110px;
    margin: 0 !important;
    padding: 0 !important;
    flex: none;
    white-space: nowrap;
    cursor: pointer;
}

.admin-form-status-content .admin-switch input {
    position: absolute;
    opacity: 0;
    pointer-events: none;
}

.admin-form-status-content .admin-switch-slider {
    position: relative;
    display: block;
    width: 40px;
    min-width: 40px;
    height: 22px;
    flex: 0 0 40px;
    border-radius: 999px;
    background: #cbd5df;
    transition: background 0.2s ease;
}

.admin-form-status-content .admin-switch-slider::after {
    content: '';
    position: absolute;
    top: 3px;
    left: 3px;
    width: 16px;
    height: 16px;
    border-radius: 50%;
    background: #fff;
    box-shadow: 0 1px 3px rgba(23, 43, 77, 0.2);
    transition: transform 0.2s ease;
}

.admin-form-status-content .admin-switch input:checked + .admin-switch-slider {
    background: var(--sc-page-blue);
}

.admin-form-status-content .admin-switch input:checked + .admin-switch-slider::after {
    transform: translateX(18px);
}

.admin-form-status-content .admin-switch input:focus-visible + .admin-switch-slider {
    outline: 2px solid var(--sc-page-blue);
    outline-offset: 3px;
}

.admin-form-status-content .admin-switch-label {
    display: inline-block;
    min-width: 48px;
    color: var(--sc-page-text);
    font-size: 12px;
    font-weight: 700;
}

/* RESPONSIVE */

@media (max-width: 600px) {
    .admin-form-status-content {
        grid-template-columns: 42px minmax(0, 1fr) !important;
        align-items: start !important;
        gap: 12px !important;
    }

    .admin-form-status-info {
        grid-column: 2;
    }

    .admin-form-status-content .admin-switch {
        grid-column: 2;
        justify-self: start;
        justify-content: flex-start;
        margin-top: 6px !important;
    }
}
</style>