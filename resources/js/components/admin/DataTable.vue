<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import type { Component } from 'vue';

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface Pagination {
    data: Record<string, any>[];
    links: PaginationLink[];
    current_page: number;
    last_page: number;
    from: number | null;
    to: number | null;
    total: number;
}

interface TableColumn {
    key: string;
    label: string;
    class?: string;
    headerClass?: string;
    value?: (row: Record<string, any>) => any;
    icon?:
        | Component
        | ((row: Record<string, any>) => Component | undefined);
}

interface TableAction {
    key: string;
    label: string;
    icon?: Component;
    class?: string;
    href?: (row: Record<string, any>) => string;
    onClick?: (row: Record<string, any>) => void;
}

const props = defineProps<{
    columns: TableColumn[];
    pagination: Pagination;
    actions?: TableAction[];
    search?: string;
    searchPlaceholder?: string;
    searchIcon?: Component;
    counterLabel?: string;
    emptyTitle?: string;
    emptyDescription?: string;
    emptyIcon?: Component;
    rowKey?: (row: Record<string, any>) => string | number;
}>();

const emit = defineEmits<{
    (event: 'update:search', value: string): void;
    (event: 'search'): void;
}>();

const getValue = (
    row: Record<string, any>,
    column: TableColumn,
): any => {
    if (column.value) {
        return column.value(row);
    }

    return column.key
        .split('.')
        .reduce(
            (value, key) => value?.[key],
            row,
        );
};

const resolveColumnIcon = (
    column: TableColumn,
    row: Record<string, any>,
): Component | undefined => {
    if (!column.icon) {
        return undefined;
    }

    if (typeof column.icon === 'function') {
        return column.icon(row);
    }

    return column.icon;
};

const getRowKey = (
    row: Record<string, any>,
    index: number,
): string | number => {
    if (props.rowKey) {
        return props.rowKey(row);
    }

    return row.id ?? index;
};

const handleAction = (
    action: TableAction,
    row: Record<string, any>,
): void => {
    if (action.onClick) {
        action.onClick(row);
    }
};
</script>

<template>
    <div class="data-table-card">
        <!-- TOOLBAR -->

        <div class="data-table-toolbar">
            <form
                v-if="props.search !== undefined"
                class="data-table-search-form"
                @submit.prevent="emit('search')"
            >
                <div class="data-table-search-wrapper">
                    <component
                        :is="props.searchIcon"
                        v-if="props.searchIcon"
                        :size="16"
                        :stroke-width="2"
                        class="data-table-search-icon"
                    />

                    <input
                        :value="props.search"
                        type="search"
                        :placeholder="
                            props.searchPlaceholder ??
                            'Buscar...'
                        "
                        class="data-table-search-input"
                        @input="
                            emit(
                                'update:search',
                                ($event.target as HTMLInputElement)
                                    .value,
                            )
                        "
                    />
                </div>

                <button
                    type="submit"
                    class="admin-btn admin-btn-search"
                >
                    <component
                        :is="props.searchIcon"
                        v-if="props.searchIcon"
                        :size="14"
                        :stroke-width="2"
                    />

                    Buscar
                </button>
            </form>

            <div class="data-table-counter">
                {{ props.pagination.total }}
                {{ props.counterLabel ?? 'registros' }}
            </div>
        </div>

        <!-- TABLE -->

        <div class="data-table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th
                            v-for="column in props.columns"
                            :key="column.key"
                            :class="column.headerClass"
                        >
                            {{ column.label }}
                        </th>

                        <th
                            v-if="
                                props.actions &&
                                props.actions.length
                            "
                            class="text-right"
                        >
                            Acciones
                        </th>
                    </tr>
                </thead>

                <tbody>
                    <tr
                        v-for="(row, rowIndex) in props.pagination.data"
                        :key="getRowKey(row, rowIndex)"
                    >
                        <td
                            v-for="column in props.columns"
                            :key="column.key"
                            :class="column.class"
                        >
                            <!-- CUSTOM SLOT -->

                            <template
                                v-if="
                                    $slots[
                                        `cell-${column.key}`
                                    ]
                                "
                            >
                                <slot
                                    :name="`cell-${column.key}`"
                                    :row="row"
                                    :value="
                                        getValue(
                                            row,
                                            column,
                                        )
                                    "
                                />
                            </template>

                            <!-- DEFAULT CELL -->

                            <div
                                v-else
                                class="data-table-cell"
                            >
                                <component
                                    :is="
                                        resolveColumnIcon(
                                            column,
                                            row,
                                        )
                                    "
                                    v-if="
                                        resolveColumnIcon(
                                            column,
                                            row,
                                        )
                                    "
                                    :size="15"
                                    :stroke-width="2"
                                    class="data-table-cell-icon"
                                />

                                <span>
                                    {{
                                        getValue(
                                            row,
                                            column,
                                        ) ?? '—'
                                    }}
                                </span>
                            </div>
                        </td>

                        <!-- ACTIONS -->

                        <td
                            v-if="
                                props.actions &&
                                props.actions.length
                            "
                        >
                            <div class="data-table-actions">
                                <template
                                    v-for="action in props.actions"
                                    :key="action.key"
                                >
                                    <Link
                                        v-if="action.href"
                                        :href="action.href(row)"
                                        class="action-btn"
                                        :class="action.class"
                                    >
                                        <component
                                            :is="action.icon"
                                            v-if="action.icon"
                                            :size="14"
                                            :stroke-width="2"
                                        />

                                        {{ action.label }}
                                    </Link>

                                    <button
                                        v-else
                                        type="button"
                                        class="action-btn"
                                        :class="action.class"
                                        @click="
                                            handleAction(
                                                action,
                                                row,
                                            )
                                        "
                                    >
                                        <component
                                            :is="action.icon"
                                            v-if="action.icon"
                                            :size="14"
                                            :stroke-width="2"
                                        />

                                        {{ action.label }}
                                    </button>
                                </template>
                            </div>
                        </td>
                    </tr>

                    <!-- EMPTY -->

                    <tr
                        v-if="
                            props.pagination.data.length === 0
                        "
                    >
                        <td
                            :colspan="
                                props.columns.length +
                                (props.actions?.length ? 1 : 0)
                            "
                            class="data-table-empty"
                        >
                            <div class="data-table-empty-state">
                                <div class="data-table-empty-icon">
                                    <component
                                        :is="props.emptyIcon"
                                        v-if="props.emptyIcon"
                                        :size="22"
                                        :stroke-width="1.8"
                                    />
                                </div>

                                <div>
                                    <strong>
                                        {{
                                            props.emptyTitle ??
                                            'No se encontraron registros.'
                                        }}
                                    </strong>

                                    <p>
                                        {{
                                            props.emptyDescription ??
                                            'No hay información disponible para mostrar.'
                                        }}
                                    </p>
                                </div>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- PAGINATION -->

        <div
            v-if="props.pagination.last_page > 1"
            class="data-table-pagination-wrapper"
        >
            <div class="data-table-pagination-info">
                Mostrando

                <strong>
                    {{ props.pagination.from ?? 0 }}
                </strong>

                a

                <strong>
                    {{ props.pagination.to ?? 0 }}
                </strong>

                de

                <strong>
                    {{ props.pagination.total }}
                </strong>

                {{ props.counterLabel ?? 'registros' }}
            </div>

            <div class="data-table-pagination">
                <template
                    v-for="(link, index) in props.pagination.links"
                    :key="index"
                >
                    <Link
                        v-if="link.url"
                        :href="link.url"
                        class="data-table-pagination-btn"
                        :class="{
                            active: link.active,
                        }"
                        v-html="link.label"
                    />

                    <span
                        v-else
                        class="data-table-pagination-btn disabled"
                        v-html="link.label"
                    />
                </template>
            </div>
        </div>
    </div>
</template>

<style scoped>
.data-table-card {
    width: 100%;
    overflow: hidden;
    border: 1px solid var(--sc-page-border);
    border-radius: 16px;
    background: #ffffff;
}

.data-table-toolbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
    padding: 18px 20px;
    border-bottom: 1px solid var(--sc-page-border);
}

.data-table-search-form {
    display: flex;
    align-items: center;
    gap: 10px;
    min-width: 0;
}

.data-table-search-wrapper {
    position: relative;
    width: 320px;
    max-width: 100%;
}

.data-table-search-icon {
    position: absolute;
    top: 50%;
    left: 12px;
    color: #91a0aa;
    transform: translateY(-50%);
    pointer-events: none;
}

.data-table-search-input {
    width: 100%;
    height: 38px;
    padding: 0 12px 0 36px;
    border: 1px solid var(--sc-page-border);
    border-radius: 9px;
    outline: none;
    background: #ffffff;
    color: var(--sc-page-text);
    font-family: inherit;
    font-size: 12px;
    transition:
        border-color 0.2s ease,
        box-shadow 0.2s ease;
}

.data-table-search-input:focus {
    border-color: #a9d9f2;
    box-shadow: 0 0 0 3px rgba(36, 158, 219, 0.08);
}

.data-table-counter {
    flex: 0 0 auto;
    color: var(--sc-page-text-secondary);
    font-size: 12px;
    font-weight: 600;
}

.data-table-wrapper {
    width: 100%;
    overflow-x: auto;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
}

.data-table thead th {
    padding: 13px 18px;
    border-bottom: 1px solid var(--sc-page-border);
    background: #fbfcfd;
    color: #7b8b97;
    font-size: 11px;
    font-weight: 700;
    text-align: left;
    white-space: nowrap;
}

.data-table tbody td {
    padding: 15px 18px;
    border-bottom: 1px solid #edf1f4;
    color: var(--sc-page-text);
    font-size: 12px;
    vertical-align: middle;
}

.data-table tbody tr:last-child td {
    border-bottom: 0;
}

.data-table tbody tr:hover {
    background: #fcfdfe;
}

.data-table-cell {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    min-width: 0;
}

.data-table-cell-icon {
    flex: 0 0 auto;
    color: #7b8b97;
}

.data-table-actions {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 6px;
}

.data-table-empty {
    padding: 45px 20px !important;
}

.data-table-empty-state {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 14px;
    color: var(--sc-page-text-secondary);
}

.data-table-empty-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 44px;
    height: 44px;
    border-radius: 12px;
    background: #f3f7f9;
    color: #91a0aa;
}

.data-table-empty-state strong {
    display: block;
    color: var(--sc-page-text);
    font-size: 13px;
}

.data-table-empty-state p {
    margin: 4px 0 0;
    color: var(--sc-page-text-secondary);
    font-size: 11px;
}

.data-table-pagination-wrapper {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
    padding: 16px 20px;
    border-top: 1px solid var(--sc-page-border);
}

.data-table-pagination-info {
    color: var(--sc-page-text-secondary);
    font-size: 11px;
}

.data-table-pagination-info strong {
    color: var(--sc-page-text);
    font-weight: 700;
}

.data-table-pagination {
    display: flex;
    align-items: center;
    gap: 4px;
}

.data-table-pagination-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 30px;
    height: 30px;
    padding: 0 9px;
    border: 1px solid var(--sc-page-border);
    border-radius: 7px;
    background: #ffffff;
    color: var(--sc-page-text-secondary);
    font-size: 11px;
    font-weight: 600;
    text-decoration: none;
    transition:
        background-color 0.2s ease,
        border-color 0.2s ease,
        color 0.2s ease;
}

.data-table-pagination-btn:hover,
.data-table-pagination-btn.active {
    border-color: #a9d9f2;
    background: #eaf6fc;
    color: #1769a8;
}

.data-table-pagination-btn.disabled {
    cursor: default;
    opacity: 0.45;
}

@media (max-width: 760px) {
    .data-table-toolbar,
    .data-table-pagination-wrapper {
        align-items: stretch;
        flex-direction: column;
    }

    .data-table-search-form {
        width: 100%;
    }

    .data-table-search-wrapper {
        flex: 1;
        width: auto;
    }

    .data-table-pagination {
        justify-content: flex-start;
        overflow-x: auto;
    }
}

.data-table-action.action-btn-view,
.data-table-action.action-btn-detail {
    border-color: #e5eaee;
    background: #f8fafb;
    color: #7b8b97;
}

.data-table-action.action-btn-view:hover,
.data-table-action.action-btn-detail:hover {
    border-color: #d5dde2;
    background: #f1f5f7;
    color: #5f707c;
}

.data-table-action.action-btn-edit {
    border-color: #cfe7f5;
    background: #eaf6fc;
    color: #1769a8;
}

.data-table-action.action-btn-edit:hover {
    border-color: #a9d9f2;
    background: #dff1fa;
    color: #1769a8;
}

.data-table-action.action-btn-delete {
    border-color: #f2d1d5;
    background: #fff3f4;
    color: #c94d59;
}

.data-table-action.action-btn-delete:hover {
    border-color: #e9b8be;
    background: #fde7e9;
    color: #b83d4a;
}

</style>

