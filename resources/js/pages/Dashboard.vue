<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import {
    ArrowUpRight,
    CalendarDays,
    ChevronRight,
    CircleDollarSign,
    ShoppingBag,
    Ticket,
    TrendingDown,
    TrendingUp,
    Users,
} from 'lucide-vue-next'
import VueApexCharts from 'vue3-apexcharts'


import admin from '@/routes/admin'

interface DashboardStats {
    users: number
    orders: number
    tickets: number
    revenue: number
}

interface RecentOrder {
    id: number
    customer: string
    email: string
    product: string
    amount: number
    status: string
    date: string
}

interface UpcomingEvent {
    id: number
    name: string
    date: string
    location: string
    registered: number
    capacity: number
}

const props = defineProps<{
    stats?: DashboardStats
    recentOrders?: RecentOrder[]
    upcomingEvents?: UpcomingEvent[]
}>()

const stats = props.stats ?? {
    users: 1248,
    orders: 386,
    tickets: 742,
    revenue: 128450,
}

const recentOrders = props.recentOrders ?? [
    {
        id: 1024,
        customer: 'María González',
        email: 'maria@email.com',
        product: 'Kit 10K Sonríe Corriendo',
        amount: 850,
        status: 'Pagado',
        date: 'Hoy, 09:42',
    },
    {
        id: 1023,
        customer: 'Carlos Hernández',
        email: 'carlos@email.com',
        product: 'Kit 5K Sonríe Corriendo',
        amount: 650,
        status: 'Pagado',
        date: 'Hoy, 08:31',
    },
    {
        id: 1022,
        customer: 'Ana Martínez',
        email: 'ana@email.com',
        product: 'Kit Infantil',
        amount: 450,
        status: 'Pendiente',
        date: 'Ayer, 18:20',
    },
    {
        id: 1021,
        customer: 'Luis Ramírez',
        email: 'luis@email.com',
        product: 'Kit 10K + Playera',
        amount: 990,
        status: 'Pagado',
        date: 'Ayer, 16:05',
    },
]

const upcomingEvents = props.upcomingEvents ?? [
    {
        id: 1,
        name: 'Sonríe Corriendo 2026',
        date: '18 Oct 2026',
        location: 'Durango, Dgo.',
        registered: 742,
        capacity: 1000,
    },
    {
        id: 2,
        name: 'Carrera Infantil',
        date: '08 Nov 2026',
        location: 'Parque Guadiana',
        registered: 184,
        capacity: 300,
    },
    {
        id: 3,
        name: 'Corre por una Sonrisa',
        date: '22 Nov 2026',
        location: 'Durango, Dgo.',
        registered: 325,
        capacity: 500,
    },
]

const formatCurrency = (value: number) => {
    return new Intl.NumberFormat('es-MX', {
        style: 'currency',
        currency: 'MXN',
        maximumFractionDigits: 0,
    }).format(value)
}

/* =========================================================
   GRÁFICA DE INGRESOS
   ========================================================= */

const revenueSeries = [
    {
        name: 'Ingresos',
        data: [
            18500,
            22400,
            19800,
            27600,
            24300,
            31900,
            28600,
            35100,
            32700,
            38200,
            36100,
            41500,
        ],
    },
]

const revenueChartOptions = {
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
        categories: [
            '01 Sep',
            '03 Sep',
            '05 Sep',
            '07 Sep',
            '09 Sep',
            '11 Sep',
            '13 Sep',
            '15 Sep',
            '17 Sep',
            '19 Sep',
            '21 Sep',
            '23 Sep',
        ],
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
                return `$${Math.round(value / 1000)}k`
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
            formatter: (value: number) => formatCurrency(value),
        },
    },

    markers: {
        size: 0,
        hover: {
            size: 6,
        },
    },
}

/* =========================================================
   GRÁFICA DE PRODUCTOS
   ========================================================= */

const productSeries = [46, 31, 15, 8]

const productChartOptions = {
    chart: {
        type: 'donut',
        toolbar: {
            show: false,
        },
        fontFamily: 'Inter, sans-serif',
    },

    labels: [
        'Kit 10K',
        'Kit 5K',
        'Kit Infantil',
        'Otros',
    ],

    colors: [
        '#249EDB',
        '#6753B7',
        '#D94C9A',
        '#B9C7D5',
    ],

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
                        formatter: (value: string) => `${value}%`,
                    },

                    total: {
                        show: true,
                        label: 'Ventas',
                        color: '#64748B',
                        formatter: () => '100%',
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
}

/* =========================================================
   GRÁFICA DE REGISTROS
   ========================================================= */

const registrationSeries = [
    {
        name: 'Registros',
        data: [32, 45, 38, 61, 48, 74, 69],
    },
]

const registrationChartOptions = {
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
        categories: [
            'Lun',
            'Mar',
            'Mié',
            'Jue',
            'Vie',
            'Sáb',
            'Dom',
        ],
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
}
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
                    Aquí tienes un resumen de lo que está pasando
                    en Sonríe Corriendo.
                </p>
            </div>

            <div class="dashboard-header-actions">
                <button class="dashboard-date">
                    <CalendarDays :size="17" />

                    <span>
                        Septiembre 2026
                    </span>
                </button>
            </div>
        </header>

        <!-- =================================================
             STATS
        ================================================== -->

        <section class="stats-grid">
            <article class="stat-card">
                <div class="stat-card-top">
                    <div class="stat-icon stat-icon-blue">
                        <Users :size="21" />
                    </div>

                    <span class="stat-growth positive">
                        <TrendingUp :size="14" />
                        12.5%
                    </span>
                </div>

                <div class="stat-content">
                    <span class="stat-label">
                        Usuarios registrados
                    </span>

                    <strong class="stat-value">
                        {{ stats.users.toLocaleString('es-MX') }}
                    </strong>

                    <span class="stat-description">
                        vs. mes anterior
                    </span>
                </div>
            </article>

            <article class="stat-card">
                <div class="stat-card-top">
                    <div class="stat-icon stat-icon-purple">
                        <ShoppingBag :size="21" />
                    </div>

                    <span class="stat-growth positive">
                        <TrendingUp :size="14" />
                        8.2%
                    </span>
                </div>

                <div class="stat-content">
                    <span class="stat-label">
                        Pedidos
                    </span>

                    <strong class="stat-value">
                        {{ stats.orders.toLocaleString('es-MX') }}
                    </strong>

                    <span class="stat-description">
                        este mes
                    </span>
                </div>
            </article>

            <article class="stat-card">
                <div class="stat-card-top">
                    <div class="stat-icon stat-icon-pink">
                        <Ticket :size="21" />
                    </div>

                    <span class="stat-growth positive">
                        <TrendingUp :size="14" />
                        18.4%
                    </span>
                </div>

                <div class="stat-content">
                    <span class="stat-label">
                        Boletos vendidos
                    </span>

                    <strong class="stat-value">
                        {{ stats.tickets.toLocaleString('es-MX') }}
                    </strong>

                    <span class="stat-description">
                        acumulados
                    </span>
                </div>
            </article>

            <article class="stat-card">
                <div class="stat-card-top">
                    <div class="stat-icon stat-icon-green">
                        <CircleDollarSign :size="21" />
                    </div>

                    <span class="stat-growth positive">
                        <TrendingUp :size="14" />
                        15.8%
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
                        ingresos del mes
                    </span>
                </div>
            </article>
        </section>

        <!-- =================================================
             ROW 1 - INGRESOS + PRODUCTOS
        ================================================== -->

        <section class="dashboard-grid dashboard-grid-main">
            <article class="dashboard-card revenue-card">
                <div class="dashboard-card-header">
                    <div>
                        <h2 class="dashboard-card-title">
                            Ingresos
                        </h2>

                        <p class="dashboard-card-subtitle">
                            Rendimiento de ventas durante el mes
                        </p>
                    </div>

                    <button class="card-filter">
                        Este mes
                        <ChevronRight :size="15" />
                    </button>
                </div>

                <div class="revenue-summary">
                    <div class="revenue-summary-value">
                        $128,450
                    </div>

                    <div class="revenue-summary-change">
                        <TrendingUp :size="15" />
                        15.8%
                    </div>

                    <span>
                        vs. mes anterior
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

            <article class="dashboard-card products-card">
                <div class="dashboard-card-header">
                    <div>
                        <h2 class="dashboard-card-title">
                            Ventas por producto
                        </h2>

                        <p class="dashboard-card-subtitle">
                            Distribución de kits vendidos
                        </p>
                    </div>
                </div>

                <div class="product-chart">
                    <VueApexCharts
                        type="donut"
                        height="245"
                        :options="productChartOptions"
                        :series="productSeries"
                    />
                </div>

                <div class="product-legend">
                    <div class="legend-item">
                        <span class="legend-dot blue"></span>
                        <span>Kit 10K</span>
                        <strong>46%</strong>
                    </div>

                    <div class="legend-item">
                        <span class="legend-dot purple"></span>
                        <span>Kit 5K</span>
                        <strong>31%</strong>
                    </div>

                    <div class="legend-item">
                        <span class="legend-dot pink"></span>
                        <span>Kit Infantil</span>
                        <strong>15%</strong>
                    </div>

                    <div class="legend-item">
                        <span class="legend-dot gray"></span>
                        <span>Otros</span>
                        <strong>8%</strong>
                    </div>
                </div>
            </article>
        </section>

        <!-- =================================================
             ROW 2
        ================================================== -->

        <section class="dashboard-grid dashboard-grid-secondary">
            <!-- REGISTROS -->
            <article class="dashboard-card registrations-card">
                <div class="dashboard-card-header">
                    <div>
                        <h2 class="dashboard-card-title">
                            Nuevos registros
                        </h2>

                        <p class="dashboard-card-subtitle">
                            Usuarios registrados esta semana
                        </p>
                    </div>

                    <div class="small-growth">
                        <ArrowUpRight :size="15" />
                        12.4%
                    </div>
                </div>

                <div class="registration-total">
                    367
                    <span>registros</span>
                </div>

                <div class="registration-chart">
                    <VueApexCharts
                        type="bar"
                        height="190"
                        :options="registrationChartOptions"
                        :series="registrationSeries"
                    />
                </div>
            </article>

            <!-- EVENTOS -->
            <article class="dashboard-card events-card">
                <div class="dashboard-card-header">
                    <div>
                        <h2 class="dashboard-card-title">
                            Próximos eventos
                        </h2>

                        <p class="dashboard-card-subtitle">
                            Eventos activos
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

                <div class="events-list">
                    <div
                        v-for="event in upcomingEvents"
                        :key="event.id"
                        class="event-item"
                    >
                        <div class="event-date">
                            <span>
                                {{ event.date.split(' ')[0] }}
                            </span>

                            <small>
                                {{ event.date.split(' ')[1] }}
                            </small>
                        </div>

                        <div class="event-info">
                            <strong>
                                {{ event.name }}
                            </strong>

                            <span>
                                {{ event.location }}
                            </span>
                        </div>

                        <div class="event-progress">
                            <div class="event-progress-label">
                                <span>
                                    {{ event.registered }}
                                    registrados
                                </span>

                                <span>
                                    {{ event.capacity }}
                                </span>
                            </div>

                            <div class="progress-track">
                                <div
                                    class="progress-fill"
                                    :style="{
                                        width: `${Math.min(
                                            (event.registered /
                                                event.capacity) *
                                                100,
                                            100,
                                        )}%`,
                                    }"
                                ></div>
                            </div>
                        </div>
                    </div>
                </div>
            </article>
        </section>

        <!-- =================================================
             PEDIDOS
        ================================================== -->

        <section class="dashboard-card orders-card">
            <div class="dashboard-card-header">
                <div>
                    <h2 class="dashboard-card-title">
                        Pedidos recientes
                    </h2>

                    <p class="dashboard-card-subtitle">
                        Últimas compras realizadas
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
                                    #{{ order.id }}
                                </span>
                            </td>

                            <td>
                                <div class="customer-cell">
                                    <div class="customer-avatar">
                                        {{ order.customer.charAt(0) }}
                                    </div>

                                    <div>
                                        <strong>
                                            {{ order.customer }}
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
                                    {{ formatCurrency(order.amount) }}
                                </strong>
                            </td>

                            <td>
                                <span
                                    class="order-status"
                                    :class="{
                                        paid:
                                            order.status === 'Pagado',
                                        pending:
                                            order.status === 'Pendiente',
                                    }"
                                >
                                    {{ order.status }}
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</template>