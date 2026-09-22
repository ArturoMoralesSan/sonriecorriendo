<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import Swal from 'sweetalert2';
import { ref } from 'vue';

import admin from '@/routes/admin';

interface PaymentMethod {
    id: number;
    name: string;
    code: string;
    description: string | null;
    is_active: boolean;
    sort_order: number;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface PaymentMethodsPagination {
    data: PaymentMethod[];
    links: PaginationLink[];
    current_page: number;
    last_page: number;
    from: number | null;
    to: number | null;
    total: number;
}

const props = defineProps<{
    paymentMethods: PaymentMethodsPagination;
    filters: {
        search?: string;
    };
}>();

const search = ref(props.filters?.search ?? '');

const submitSearch = (): void => {
    router.get(
        admin.paymentMethods.index().url,
        {
            search: search.value || undefined,
        },
        {
            preserveState: true,
            replace: true,
        },
    );
};

const deletePaymentMethod = async (
    paymentMethod: PaymentMethod,
): Promise<void> => {
    const result = await Swal.fire({
        title: '¿Eliminar método de pago?',
        text: `Se eliminará el método de pago "${paymentMethod.name}". Esta acción no se puede deshacer.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        reverseButtons: true,
        focusCancel: true,
    });

    if (!result.isConfirmed) {
        return;
    }

    router.delete(
        admin.paymentMethods.destroy(paymentMethod.id).url,
        {
            preserveScroll: true,

            onSuccess: () => {
                Swal.fire({
                    title: 'Eliminado',
                    text: 'El método de pago se eliminó correctamente.',
                    icon: 'success',
                    timer: 1800,
                    showConfirmButton: false,
                });
            },

            onError: () => {
                Swal.fire({
                    title: 'Error',
                    text: 'No se pudo eliminar el método de pago.',
                    icon: 'error',
                    confirmButtonText: 'Aceptar',
                });
            },
        },
    );
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
        ],
    },
});
</script>

<template>
    <Head title="Métodos de pago" />

    <div class="admin-page">
        <!-- =================================================
             HEADER
        ================================================== -->

        <header class="admin-page-header">
            <div>
                <p class="admin-page-eyebrow">
                    Configuración
                </p>

                <h1 class="admin-page-title">
                    Métodos de pago
                </h1>

                <p class="admin-page-subtitle">
                    Administra los métodos de pago disponibles para
                    las órdenes de boletos.
                </p>
            </div>

            <div class="admin-page-header-actions">
                <Link
                    :href="admin.paymentMethods.create().url"
                    class="admin-btn admin-btn-primary"
                >
                    <span class="admin-btn-icon">+</span>

                    Nuevo método de pago
                </Link>
            </div>
        </header>

        <!-- =================================================
             MAIN CARD
        ================================================== -->

        <section class="admin-table-card">
            <!-- TOOLBAR -->

            <div class="admin-table-toolbar">
                <form
                    class="admin-search-form"
                    @submit.prevent="submitSearch"
                >
                    <div class="admin-search-wrapper">
                        <svg
                            class="admin-search-icon"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                        >
                            <circle
                                cx="11"
                                cy="11"
                                r="7"
                            />

                            <path
                                d="m20 20-4-4"
                            />
                        </svg>

                        <input
                            v-model="search"
                            type="search"
                            placeholder="Buscar método de pago..."
                            class="admin-search-input"
                        />
                    </div>

                    <button
                        type="submit"
                        class="admin-btn admin-btn-search"
                    >
                        Buscar
                    </button>
                </form>

                <div class="admin-table-counter">
                    <strong>
                        {{ paymentMethods.total }}
                    </strong>

                    <span>
                        métodos
                    </span>
                </div>
            </div>

            <!-- TABLE -->

            <div class="admin-table-wrapper">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>
                                Método de pago
                            </th>

                            <th>
                                Código
                            </th>

                            <th>
                                Descripción
                            </th>

                            <th class="text-center">
                                Orden
                            </th>

                            <th>
                                Estado
                            </th>

                            <th class="text-right">
                                Acciones
                            </th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr
                            v-for="paymentMethod in paymentMethods.data"
                            :key="paymentMethod.id"
                        >
                            <!-- MÉTODO -->

                            <td>
                                <div class="payment-method-cell">
                                    <div class="payment-method-icon">
                                        $
                                    </div>

                                    <div class="payment-method-info">
                                        <strong>
                                            {{ paymentMethod.name }}
                                        </strong>

                                        <span>
                                            ID: {{ paymentMethod.id }}
                                        </span>
                                    </div>
                                </div>
                            </td>

                            <!-- CÓDIGO -->

                            <td>
                                <code class="payment-code">
                                    {{ paymentMethod.code }}
                                </code>
                            </td>

                            <!-- DESCRIPCIÓN -->

                            <td>
                                <div
                                    v-if="paymentMethod.description"
                                    class="payment-description"
                                >
                                    {{ paymentMethod.description }}
                                </div>

                                <div
                                    v-else
                                    class="payment-description empty"
                                >
                                    Sin descripción
                                </div>
                            </td>

                            <!-- ORDEN -->

                            <td class="text-center">
                                <span class="order-badge">
                                    {{ paymentMethod.sort_order }}
                                </span>
                            </td>

                            <!-- ESTADO -->

                            <td>
                                <span
                                    v-if="paymentMethod.is_active"
                                    class="status-badge status-active"
                                >
                                    <span class="status-dot"></span>

                                    Activo
                                </span>

                                <span
                                    v-else
                                    class="status-badge status-inactive"
                                >
                                    <span class="status-dot"></span>

                                    Inactivo
                                </span>
                            </td>

                            <!-- ACCIONES -->

                            <td>
                                <div class="table-actions">
                                    <Link
                                        :href="
                                            admin.paymentMethods.edit(
                                                paymentMethod.id,
                                            ).url
                                        "
                                        class="action-btn action-btn-edit"
                                    >
                                        Editar
                                    </Link>

                                    <button
                                        type="button"
                                        class="action-btn action-btn-delete"
                                        @click="
                                            deletePaymentMethod(
                                                paymentMethod,
                                            )
                                        "
                                    >
                                        Eliminar
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <!-- EMPTY -->

                        <tr
                            v-if="
                                paymentMethods.data.length === 0
                            "
                        >
                            <td
                                colspan="6"
                                class="admin-table-empty"
                            >
                                <div class="empty-state">
                                    <div class="empty-state-icon">
                                        $
                                    </div>

                                    <strong>
                                        No se encontraron métodos
                                        de pago
                                    </strong>

                                    <span>
                                        Intenta cambiar el término
                                        de búsqueda.
                                    </span>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- PAGINATION -->

            <div
                v-if="paymentMethods.last_page > 1"
                class="admin-pagination-wrapper"
            >
                <div class="admin-pagination-info">
                    Mostrando

                    <strong>
                        {{ paymentMethods.from ?? 0 }}
                    </strong>

                    a

                    <strong>
                        {{ paymentMethods.to ?? 0 }}
                    </strong>

                    de

                    <strong>
                        {{ paymentMethods.total }}
                    </strong>
                </div>

                <nav class="admin-pagination">
                    <template
                        v-for="(link, index) in paymentMethods.links"
                        :key="index"
                    >
                        <Link
                            v-if="link.url"
                            :href="link.url"
                            class="pagination-btn"
                            :class="{
                                active: link.active,
                            }"
                            v-html="link.label"
                        />

                        <span
                            v-else
                            class="pagination-btn disabled"
                            v-html="link.label"
                        />
                    </template>
                </nav>
            </div>
        </section>
    </div>
</template>