<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import {
    CreditCard,
    Edit,
    Plus,
    Search,
    Trash2,
    WalletCards,
} from 'lucide-vue-next';
import Swal from 'sweetalert2';
import { ref } from 'vue';

import DataTable from '@/Components/Admin/DataTable.vue';
import PageHeader from '@/Components/Admin/PageHeader.vue';
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

const columns = [
    {
        key: 'payment_method',
        label: 'Método de pago',
    },
    {
        key: 'code',
        label: 'Código',
    },
    {
        key: 'description',
        label: 'Descripción',
    },
    {
        key: 'sort_order',
        label: 'Orden',
        headerClass: 'text-center',
        class: 'text-center',
    },
    {
        key: 'status',
        label: 'Estado',
    },
];

const actions = [
    {
        key: 'edit',
        label: 'Editar',
        icon: Edit,
        class: 'action-btn-edit',
        href: (paymentMethod: Record<string, any>) =>
            admin.paymentMethods.edit(paymentMethod.id).url,
    },
    {
        key: 'delete',
        label: 'Eliminar',
        icon: Trash2,
        class: 'action-btn-delete',
        onClick: (paymentMethod: Record<string, any>) =>
            deletePaymentMethod(paymentMethod as PaymentMethod),
    },
];

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
        <PageHeader
            eyebrow="Configuración"
            title="Métodos de pago"
            subtitle="Administra los métodos de pago disponibles para las órdenes de boletos."
            :actions="[
                {
                    label: 'Nuevo método de pago',
                    href: admin.paymentMethods.create().url,
                    icon: Plus,
                    variant: 'primary',
                },
            ]"
        />

        <DataTable
            :columns="columns"
            :pagination="paymentMethods"
            :actions="actions"
            :search="search"
            search-placeholder="Buscar método de pago..."
            counter-label="métodos"
            empty-title="No se encontraron métodos de pago."
            empty-description="Intenta cambiar el término de búsqueda."
            :search-icon="Search"
            :empty-icon="CreditCard"
            @update:search="search = $event"
            @search="submitSearch"
        >
            <!-- MÉTODO DE PAGO -->

            <template #cell-payment_method="{ row }">
                <div class="payment-method-cell">
                    <div class="payment-method-icon">
                        <WalletCards
                            :size="17"
                            :stroke-width="2"
                        />
                    </div>

                    <div class="payment-method-info">
                        <strong>
                            {{ row.name }}
                        </strong>

                        <span>
                            ID: {{ row.id }}
                        </span>
                    </div>
                </div>
            </template>

            <!-- CÓDIGO -->

            <template #cell-code="{ row }">
                <code class="payment-code">
                    {{ row.code }}
                </code>
            </template>

            <!-- DESCRIPCIÓN -->

            <template #cell-description="{ row }">
                <div
                    v-if="row.description"
                    class="payment-description"
                >
                    {{ row.description }}
                </div>

                <div
                    v-else
                    class="payment-description empty"
                >
                    Sin descripción
                </div>
            </template>

            <!-- ORDEN -->

            <template #cell-sort_order="{ row }">
                <span class="order-badge">
                    {{ row.sort_order }}
                </span>
            </template>

            <!-- ESTADO -->

            <template #cell-status="{ row }">
                <span
                    v-if="row.is_active"
                    class="status-badge status-active"
                >
                    Activo
                </span>

                <span
                    v-else
                    class="status-badge status-inactive"
                >


                    Inactivo
                </span>
            </template>
        </DataTable>
    </div>
</template>

<style scoped>
.payment-method-cell {
    display: flex;
    align-items: center;
    gap: 11px;
}

.payment-method-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    flex: 0 0 40px;
    width: 40px;
    height: 40px;
    border-radius: 10px;
    background: var(--sc-page-light);
    color: var(--sc-page-blue);
}

.payment-method-info {
    display: flex;
    flex-direction: column;
    min-width: 0;
    gap: 3px;
}

.payment-method-info strong {
    overflow: hidden;
    color: var(--sc-page-text);
    font-size: 13px;
    font-weight: 700;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.payment-method-info span {
    color: #8b9ba6;
    font-size: 10px;
}

.payment-code {
    display: inline-flex;
    align-items: center;
    padding: 5px 8px;
    border: 1px solid #e5eaee;
    border-radius: 6px;
    background: #f8fafb;
    color: #5f707c;
    font-family: monospace;
    font-size: 11px;
    font-weight: 600;
}

.payment-description {
    max-width: 360px;
    overflow: hidden;
    color: var(--sc-page-text-secondary);
    font-size: 11px;
    line-height: 1.45;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.payment-description.empty {
    color: #9aa7b0;
    font-style: italic;
}

.order-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 30px;
    height: 26px;
    padding: 0 8px;
    border-radius: 7px;
    background: #f3f6f8;
    color: #687983;
    font-size: 11px;
    font-weight: 700;
}

.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 5px 9px;
    border-radius: 7px;
    font-size: 10px;
    font-weight: 700;
    white-space: nowrap;
}

.status-active {
    background: var(--sc-page-green-light);
    color: #159c83;
}

.status-inactive {
    background: #feecee;
    color: #c93645;
}

.status-dot {
    width: 6px;
    height: 6px;
    flex: 0 0 6px;
    border-radius: 50%;
    background: currentColor;
}

:global(.data-table th.text-center),
:global(.data-table td.text-center) {
    text-align: center;
}
</style>