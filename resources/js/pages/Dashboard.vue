<script setup lang="ts">
import { computed, ref } from 'vue'
import {
    Head,
    Link,
    router,
} from '@inertiajs/vue3'
import {
    ArrowUpRight,
    CalendarDays,
    ChevronRight,
    CircleDollarSign,
    CreditCard,
    ShoppingBag,
    TrendingDown,
    TrendingUp,
    Users,
} from 'lucide-vue-next'
import VueApexCharts from 'vue3-apexcharts'

import admin from '@/routes/admin'

interface DashboardGrowth {
    users: number
    orders: number
    productsSold: number
    revenue: number
}

interface DashboardStats {
    users: number
    orders: number
    productsSold: number
    revenue: number
    growth: DashboardGrowth
}

interface RevenueChartItem {
    date: string
    revenue: number
    orders: number
}

interface ProductChartItem {
    name: string
    quantity: number
    revenue: number
}

interface PaymentMethodChartItem {
    name: string
    amount: number
}

interface RegistrationChartItem {
    date: string
    registrations: number
}

interface RecentOrder {
    id: number
    folio?: string
    customer: string
    email: string
    product: string
    amount: number
    status: string
    date: string
}

interface DashboardPeriod {
    label: string
    month: string
    startDate: string
    endDate: string
}

const props = defineProps<{
    stats?: DashboardStats
    revenueChart?: RevenueChartItem[]
    productChart?: ProductChartItem[]
    paymentMethodChart?: PaymentMethodChartItem[]
    registrationsChart?: RegistrationChartItem[]
    registrationsTotal?: number
    recentOrders?: RecentOrder[]
    period?: DashboardPeriod
}>()

/* =========================================================
   FILTRO DE FECHAS
========================================================= */

const startDate = ref(
    props.period?.startDate ?? '',
)

const endDate = ref(
    props.period?.endDate ?? '',
)

const loading = ref(false)

const applyDateFilter = () => {
    if (!startDate.value || !endDate.value) {
        return
    }

    if (startDate.value > endDate.value) {
        const temporary = startDate.value

        startDate.value = endDate.value
        endDate.value = temporary
    }

    loading.value = true

    router.get(
        admin.dashboard().url,
        {
            start_date: startDate.value,
            end_date: endDate.value,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
            onFinish: () => {
                loading.value = false
            },
        },
    )
}

const clearDateFilter = () => {
    const today = new Date()

    const year = today.getFullYear()

    const month = String(
        today.getMonth() + 1,
    ).padStart(2, '0')

    startDate.value = `${year}-${month}-01`

    const lastDay = new Date(
        year,
        today.getMonth() + 1,
        0,
    ).getDate()

    endDate.value = `${year}-${month}-${String(
        lastDay,
    ).padStart(2, '0')}`

    applyDateFilter()
}

/* =========================================================
   DATOS
========================================================= */

const stats = computed(() => {
    return (
        props.stats ?? {
            users: 0,
            orders: 0,
            productsSold: 0,
            revenue: 0,
            growth: {
                users: 0,
                orders: 0,
                productsSold: 0,
                revenue: 0,
            },
        }
    )
})

const revenueChart = computed(
    () => props.revenueChart ?? [],
)

const productChart = computed(
    () => props.productChart ?? [],
)

const paymentMethodChart = computed(
    () => props.paymentMethodChart ?? [],
)

const registrationsChart = computed(
    () => props.registrationsChart ?? [],
)

const registrationsTotal = computed(
    () => props.registrationsTotal ?? 0,
)

const recentOrders = computed(
    () => props.recentOrders ?? [],
)

const currentPeriod = computed(() => {
    return props.period?.label ?? 'Periodo seleccionado'
})

/* =========================================================
   FORMATOS
========================================================= */

const formatCurrency = (value: number) => {
    return new Intl.NumberFormat('es-MX', {
        style: 'currency',
        currency: 'MXN',
        maximumFractionDigits: 0,
    }).format(value)
}

const formatPercentage = (value: number) => {
    return `${Math.abs(value).toFixed(1)}%`
}

const growthClass = (value: number) => {
    return value >= 0
        ? 'positive'
        : 'negative'
}

/* =========================================================
   GRÁFICA DE INGRESOS
========================================================= */

const revenueSeries = computed(() => [
    {
        name: 'Ingresos',

        data: revenueChart.value.map(
            (item) => item.revenue,
        ),
    },
])

const revenueChartOptions = computed(() => ({
    chart: {
        type: 'area',

        toolbar: {
            show: false,
        },

        zoom: {
            enabled: false,
        },

        fontFamily: 'Inter, sans-serif',
    },

    colors: ['#249EDB'],

    stroke: {
        curve: 'smooth',
        width: 3,
    },

    fill: {
        type: 'gradient',

        gradient: {
            shadeIntensity: 1,
            opacityFrom: 0.24,
            opacityTo: 0.02,
            stops: [0, 90, 100],
        },
    },

    dataLabels: {
        enabled: false,
    },

    grid: {
        borderColor: '#EEF3F7',
        strokeDashArray: 4,

        padding: {
            left: 10,
            right: 10,
        },
    },

    xaxis: {
        categories: revenueChart.value.map(
            (item) => item.date,
        ),

        labels: {
            style: {
                colors: '#94A3B8',
                fontSize: '11px',
            },
        },

        axisBorder: {
            show: false,
        },

        axisTicks: {
            show: false,
        },
    },

    yaxis: {
        labels: {
            formatter: (value: number) => {
                if (value >= 1000) {
                    return `$${Math.round(
                        value / 1000,
                    )}k`
                }

                return `$${Math.round(value)}`
            },

            style: {
                colors: '#94A3B8',
                fontSize: '11px',
            },
        },
    },

    tooltip: {
        theme: 'light',

        y: {
            formatter: (value: number) =>
                formatCurrency(value),
        },
    },

    markers: {
        size: 0,

        hover: {
            size: 6,
        },
    },
}))

/* =========================================================
   GRÁFICA DE PRODUCTOS
========================================================= */

const productSeries = computed(() => {
    return productChart.value.map(
        (product) => product.quantity,
    )
})

const productTotal = computed(() => {
    return productChart.value.reduce(
        (total, product) =>
            total + product.quantity,
        0,
    )
})

const productPercentages = computed(() => {
    return productChart.value.map(
        (product) => {
            if (productTotal.value === 0) {
                return 0
            }

            return Math.round(
                (product.quantity /
                    productTotal.value) *
                    100,
            )
        },
    )
})

const productChartColors = [
    '#249EDB',
    '#6753B7',
    '#D94C9A',
    '#18B89A',
    '#B9C7D5',
]

const productChartOptions = computed(() => ({
    chart: {
        type: 'donut',

        toolbar: {
            show: false,
        },

        fontFamily: 'Inter, sans-serif',
    },

    labels: productChart.value.map(
        (product) => product.name,
    ),

    colors: productChartColors.slice(
        0,
        productChart.value.length,
    ),

    legend: {
        show: false,
    },

    stroke: {
        width: 4,
        colors: ['#FFFFFF'],
    },

    plotOptions: {
        pie: {
            donut: {
                size: '72%',

                labels: {
                    show: true,

                    name: {
                        show: true,
                        color: '#64748B',
                        fontSize: '12px',
                    },

                    value: {
                        show: true,
                        color: '#172B4D',
                        fontSize: '22px',
                        fontWeight: 700,

                        formatter: (
                            value: string,
                        ) => {
                            return `${value} uds.`
                        },
                    },

                    total: {
                        show: true,
                        label: 'Productos',

                        color: '#64748B',

                        formatter: () => {
                            return `${productTotal.value}`
                        },
                    },
                },
            },
        },
    },

    dataLabels: {
        enabled: false,
    },

    responsive: [
        {
            breakpoint: 480,

            options: {
                chart: {
                    width: 260,
                },
            },
        },
    ],
}))

/* =========================================================
   GRÁFICA DE REGISTROS
========================================================= */

const registrationSeries = computed(() => [
    {
        name: 'Registros',

        data: registrationsChart.value.map(
            (item) => item.registrations,
        ),
    },
])

const registrationChartOptions = computed(() => ({
    chart: {
        type: 'bar',

        toolbar: {
            show: false,
        },

        fontFamily: 'Inter, sans-serif',
    },

    colors: ['#249EDB'],

    plotOptions: {
        bar: {
            borderRadius: 6,
            columnWidth: '48%',
        },
    },

    dataLabels: {
        enabled: false,
    },

    grid: {
        show: false,
    },

    xaxis: {
        categories: registrationsChart.value.map(
            (item) => item.date,
        ),

        labels: {
            style: {
                colors: '#94A3B8',
                fontSize: '11px',
            },
        },

        axisBorder: {
            show: false,
        },

        axisTicks: {
            show: false,
        },
    },

    yaxis: {
        show: false,
    },

    tooltip: {
        theme: 'light',
    },
}))

/* =========================================================
   GRÁFICA DE MÉTODOS DE PAGO
========================================================= */

const paymentMethodSeries = computed(() => {
    return paymentMethodChart.value.map(
        (method) => method.amount,
    )
})

const paymentMethodTotal = computed(() => {
    return paymentMethodChart.value.reduce(
        (total, method) =>
            total + method.amount,
        0,
    )
})

const paymentMethodPercentages = computed(() => {
    return paymentMethodChart.value.map(
        (method) => {
            if (paymentMethodTotal.value === 0) {
                return 0
            }

            return Math.round(
                (method.amount /
                    paymentMethodTotal.value) *
                    100,
            )
        },
    )
})

const paymentMethodColors = [
    '#249EDB',
    '#6753B7',
    '#D94C9A',
    '#18B89A',
    '#F59E0B',
]

const paymentMethodChartOptions = computed(
    () => ({
        chart: {
            type: 'donut',

            toolbar: {
                show: false,
            },

            fontFamily: 'Inter, sans-serif',
        },

        labels: paymentMethodChart.value.map(
            (method) => method.name,
        ),

        colors: paymentMethodColors.slice(
            0,
            paymentMethodChart.value.length,
        ),

        legend: {
            show: false,
        },

        stroke: {
            width: 4,
            colors: ['#FFFFFF'],
        },

        plotOptions: {
            pie: {
                donut: {
                    size: '72%',

                    labels: {
                        show: true,

                        name: {
                            show: true,
                            color: '#64748B',
                            fontSize: '12px',
                        },

                        value: {
                            show: true,
                            color: '#172B4D',
                            fontSize: '20px',
                            fontWeight: 700,

                            formatter: (
                                value: string,
                            ) => {
                                return formatCurrency(
                                    Number(value),
                                )
                            },
                        },

                        total: {
                            show: true,
                            label: 'Pagado',

                            color: '#64748B',

                            formatter: () => {
                                return formatCurrency(
                                    paymentMethodTotal.value,
                                )
                            },
                        },
                    },
                },
            },
        },

        dataLabels: {
            enabled: false,
        },

        responsive: [
            {
                breakpoint: 480,

                options: {
                    chart: {
                        width: 260,
                    },
                },
            },
        ],
    }),
)
</script>

<template>
    <Head title="Dashboard" />

    <div class="dashboard-page">
        <!-- =================================================
             HEADER
        ================================================== -->

        <header class="dashboard-header">
            <div>
                <p class="dashboard-eyebrow">
                    Panel administrativo
                </p>

                <h1 class="dashboard-title">
                    ¡Hola! 👋
                </h1>

                <p class="dashboard-subtitle">
                    Aquí tienes un resumen de lo que está
                    pasando en Sonríe Corriendo.
                </p>
            </div>

            <div
                class="dashboard-header-actions"
                style="
                    display: flex;
                    align-items: center;
                    gap: 8px;
                    flex-wrap: wrap;
                "
            >
                <label
                    class="dashboard-date"
                    style="cursor: default;"
                >
                    <CalendarDays :size="17" />

                    <span>
                        Desde
                    </span>

                    <input
                        v-model="startDate"
                        type="date"
                        aria-label="Fecha inicial"
                    />
                </label>

                <label
                    class="dashboard-date"
                    style="cursor: default;"
                >
                    <CalendarDays :size="17" />

                    <span>
                        Hasta
                    </span>

                    <input
                        v-model="endDate"
                        type="date"
                        aria-label="Fecha final"
                    />
                </label>

                <button
                    type="button"
                    class="card-filter"
                    :disabled="loading"
                    @click="applyDateFilter"
                >
                    {{ loading ? 'Buscando...' : 'Aplicar' }}
                </button>

                <button
                    type="button"
                    class="card-filter"
                    :disabled="loading"
                    @click="clearDateFilter"
                >
                    Limpiar
                </button>
            </div>
        </header>

        <!-- =================================================
             PERIODO
        ================================================== -->

        <div
            style="
                margin-bottom: 18px;
                color: #64748B;
                font-size: 13px;
            "
        >
            Mostrando información del
            <strong style="color: #172B4D;">
                {{ currentPeriod }}
            </strong>
        </div>

        <!-- =================================================
             STATS
        ================================================== -->

        <section class="stats-grid">
            <!-- USUARIOS -->

            <article class="stat-card">
                <div class="stat-card-top">
                    <div class="stat-icon stat-icon-blue">
                        <Users :size="21" />
                    </div>

                    <span
                        class="stat-growth"
                        :class="
                            growthClass(
                                stats.growth.users,
                            )
                        "
                    >
                        <TrendingUp
                            v-if="stats.growth.users >= 0"
                            :size="14"
                        />

                        <TrendingDown
                            v-else
                            :size="14"
                        />

                        {{
                            formatPercentage(
                                stats.growth.users,
                            )
                        }}
                    </span>
                </div>

                <div class="stat-content">
                    <span class="stat-label">
                        Usuarios registrados
                    </span>

                    <strong class="stat-value">
                        {{
                            stats.users.toLocaleString(
                                'es-MX',
                            )
                        }}
                    </strong>

                    <span class="stat-description">
                        acumulados
                    </span>
                </div>
            </article>

            <!-- VENTAS -->

            <article class="stat-card">
                <div class="stat-card-top">
                    <div class="stat-icon stat-icon-purple">
                        <ShoppingBag :size="21" />
                    </div>

                    <span
                        class="stat-growth"
                        :class="
                            growthClass(
                                stats.growth.orders,
                            )
                        "
                    >
                        <TrendingUp
                            v-if="stats.growth.orders >= 0"
                            :size="14"
                        />

                        <TrendingDown
                            v-else
                            :size="14"
                        />

                        {{
                            formatPercentage(
                                stats.growth.orders,
                            )
                        }}
                    </span>
                </div>

                <div class="stat-content">
                    <span class="stat-label">
                        Ventas
                    </span>

                    <strong class="stat-value">
                        {{
                            stats.orders.toLocaleString(
                                'es-MX',
                            )
                        }}
                    </strong>

                    <span class="stat-description">
                        en el periodo
                    </span>
                </div>
            </article>

            <!-- PRODUCTOS -->

            <article class="stat-card">
                <div class="stat-card-top">
                    <div class="stat-icon stat-icon-pink">
                        <ShoppingBag :size="21" />
                    </div>

                    <span
                        class="stat-growth"
                        :class="
                            growthClass(
                                stats.growth.productsSold,
                            )
                        "
                    >
                        <TrendingUp
                            v-if="
                                stats.growth.productsSold >= 0
                            "
                            :size="14"
                        />

                        <TrendingDown
                            v-else
                            :size="14"
                        />

                        {{
                            formatPercentage(
                                stats.growth.productsSold,
                            )
                        }}
                    </span>
                </div>

                <div class="stat-content">
                    <span class="stat-label">
                        Productos vendidos
                    </span>

                    <strong class="stat-value">
                        {{
                            stats.productsSold.toLocaleString(
                                'es-MX',
                            )
                        }}
                    </strong>

                    <span class="stat-description">
                        en el periodo
                    </span>
                </div>
            </article>

            <!-- INGRESOS -->

            <article class="stat-card">
                <div class="stat-card-top">
                    <div class="stat-icon stat-icon-green">
                        <CircleDollarSign :size="21" />
                    </div>

                    <span
                        class="stat-growth"
                        :class="
                            growthClass(
                                stats.growth.revenue,
                            )
                        "
                    >
                        <TrendingUp
                            v-if="stats.growth.revenue >= 0"
                            :size="14"
                        />

                        <TrendingDown
                            v-else
                            :size="14"
                        />

                        {{
                            formatPercentage(
                                stats.growth.revenue,
                            )
                        }}
                    </span>
                </div>

                <div class="stat-content">
                    <span class="stat-label">
                        Ingresos
                    </span>

                    <strong class="stat-value">
                        {{ formatCurrency(stats.revenue) }}
                    </strong>

                    <span class="stat-description">
                        en el periodo
                    </span>
                </div>
            </article>
        </section>

        <!-- =================================================
             ROW 1 - INGRESOS + PRODUCTOS
        ================================================== -->

        <section
            class="dashboard-grid dashboard-grid-main"
        >
            <!-- INGRESOS -->

            <article class="dashboard-card revenue-card">
                <div class="dashboard-card-header">
                    <div>
                        <h2 class="dashboard-card-title">
                            Ingresos
                        </h2>

                        <p class="dashboard-card-subtitle">
                            Rendimiento de ventas durante el
                            periodo seleccionado
                        </p>
                    </div>
                </div>

                <div class="revenue-summary">
                    <div class="revenue-summary-value">
                        {{ formatCurrency(stats.revenue) }}
                    </div>

                    <div
                        class="revenue-summary-change"
                        :class="
                            growthClass(
                                stats.growth.revenue,
                            )
                        "
                    >
                        <TrendingUp
                            v-if="stats.growth.revenue >= 0"
                            :size="15"
                        />

                        <TrendingDown
                            v-else
                            :size="15"
                        />

                        {{
                            formatPercentage(
                                stats.growth.revenue,
                            )
                        }}
                    </div>

                    <span>
                        vs. periodo anterior
                    </span>
                </div>

                <div class="revenue-chart">
                    <VueApexCharts
                        type="area"
                        height="300"
                        :options="revenueChartOptions"
                        :series="revenueSeries"
                    />
                </div>
            </article>

            <!-- PRODUCTOS -->

            <article class="dashboard-card products-card">
                <div class="dashboard-card-header">
                    <div>
                        <h2 class="dashboard-card-title">
                            Ventas por producto
                        </h2>

                        <p class="dashboard-card-subtitle">
                            Productos vendidos en el periodo
                        </p>
                    </div>
                </div>

                <div class="product-chart">
                    <VueApexCharts
                        v-if="productChart.length"
                        type="donut"
                        height="245"
                        :options="productChartOptions"
                        :series="productSeries"
                    />

                    <div
                        v-else
                        class="dashboard-empty"
                    >
                        No hay productos vendidos en este
                        periodo.
                    </div>
                </div>

                <div
                    v-if="productChart.length"
                    class="product-legend"
                >
                    <div
                        v-for="(
                            product, index
                        ) in productChart"
                        :key="product.name"
                        class="legend-item"
                    >
                        <span
                            class="legend-dot"
                            :style="{
                                backgroundColor:
                                    productChartColors[
                                        index
                                    ],
                            }"
                        ></span>

                        <span>
                            {{ product.name }}
                        </span>

                        <strong>
                            {{
                                productPercentages[
                                    index
                                ]
                            }}%
                        </strong>
                    </div>
                </div>
            </article>
        </section>

        <!-- =================================================
             ROW 2
        ================================================== -->

        <section
            class="dashboard-grid dashboard-grid-secondary"
        >
            <!-- REGISTROS -->

            <article
                class="dashboard-card registrations-card"
            >
                <div class="dashboard-card-header">
                    <div>
                        <h2 class="dashboard-card-title">
                            Nuevos registros
                        </h2>

                        <p class="dashboard-card-subtitle">
                            Usuarios registrados en el periodo
                        </p>
                    </div>

                    <div class="small-growth">
                        <ArrowUpRight :size="15" />

                        {{ registrationsTotal }}
                    </div>
                </div>

                <div class="registration-total">
                    {{ registrationsTotal }}

                    <span>
                        registros
                    </span>
                </div>

                <div class="registration-chart">
                    <VueApexCharts
                        type="bar"
                        height="190"
                        :options="
                            registrationChartOptions
                        "
                        :series="registrationSeries"
                    />
                </div>
            </article>

            <!-- MÉTODOS DE PAGO -->

            <article
                class="dashboard-card events-card"
            >
                <div class="dashboard-card-header">
                    <div>
                        <h2 class="dashboard-card-title">
                            Métodos de pago
                        </h2>

                        <p class="dashboard-card-subtitle">
                            Distribución de pagos en el periodo
                        </p>
                    </div>

                    <CreditCard :size="20" />
                </div>

                <div
                    v-if="paymentMethodChart.length"
                    class="payment-method-content"
                >
                    <div class="payment-method-chart">
                        <VueApexCharts
                            type="donut"
                            height="210"
                            :options="
                                paymentMethodChartOptions
                            "
                            :series="
                                paymentMethodSeries
                            "
                        />
                    </div>

                    <div class="product-legend">
                        <div
                            v-for="(
                                method, index
                            ) in paymentMethodChart"
                            :key="method.name"
                            class="legend-item"
                        >
                            <span
                                class="legend-dot"
                                :style="{
                                    backgroundColor:
                                        paymentMethodColors[
                                            index
                                        ],
                                }"
                            ></span>

                            <span>
                                {{ method.name }}
                            </span>

                            <strong>
                                {{
                                    paymentMethodPercentages[
                                        index
                                    ]
                                }}%
                            </strong>
                        </div>
                    </div>
                </div>

                <div
                    v-else
                    class="dashboard-empty"
                >
                    No hay pagos registrados en este
                    periodo.
                </div>
            </article>
        </section>

        <!-- =================================================
             VENTAS
        ================================================== -->

        <section class="dashboard-card orders-card">
            <div class="dashboard-card-header">
                <div>
                    <h2 class="dashboard-card-title">
                        Ventas recientes
                    </h2>

                    <p class="dashboard-card-subtitle">
                        Últimas compras realizadas en el
                        periodo
                    </p>
                </div>

                <Link
                    :href="admin.dashboard()"
                    class="view-all-link"
                >
                    Ver todos
                    <ChevronRight :size="15" />
                </Link>
            </div>

            <div class="orders-table-wrapper">
                <table class="orders-table">
                    <thead>
                        <tr>
                            <th>
                                Pedido
                            </th>

                            <th>
                                Cliente
                            </th>

                            <th>
                                Producto
                            </th>

                            <th>
                                Fecha
                            </th>

                            <th>
                                Total
                            </th>

                            <th>
                                Estado
                            </th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr
                            v-for="order in recentOrders"
                            :key="order.id"
                        >
                            <td>
                                <span class="order-number">
                                    #{{
                                        order.folio ??
                                        order.id
                                    }}
                                </span>
                            </td>

                            <td>
                                <div class="customer-cell">
                                    <div class="customer-avatar">
                                        {{
                                            order.customer
                                                .charAt(0)
                                                .toUpperCase()
                                        }}
                                    </div>

                                    <div>
                                        <strong>
                                            {{
                                                order.customer
                                            }}
                                        </strong>

                                        <span>
                                            {{ order.email }}
                                        </span>
                                    </div>
                                </div>
                            </td>

                            <td>
                                <span class="product-name">
                                    {{ order.product }}
                                </span>
                            </td>

                            <td>
                                <span class="order-date">
                                    {{ order.date }}
                                </span>
                            </td>

                            <td>
                                <strong class="order-amount">
                                    {{
                                        formatCurrency(
                                            order.amount,
                                        )
                                    }}
                                </strong>
                            </td>

                            <td>
                                <span
                                    class="order-status"
                                    :class="{
                                        paid:
                                            order.status ===
                                            'Completado',

                                        pending:
                                            order.status ===
                                            'Pendiente',
                                    }"
                                >
                                    {{ order.status }}
                                </span>
                            </td>
                        </tr>

                        <tr
                            v-if="!recentOrders.length"
                        >
                            <td
                                colspan="6"
                                class="dashboard-table-empty"
                            >
                                No hay ventas registradas en
                                este periodo.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</template>
