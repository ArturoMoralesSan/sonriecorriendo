<script setup lang="ts">
import {
    ChevronDown,
    ChevronUp,
    Plus,
    Trash2,
} from 'lucide-vue-next';

interface PriceForm {
    name: string;
    price: string;
    starts_at: string;
    ends_at: string;
    capacity: string;
    sort_order: number;
    is_active: boolean;
}

interface InclusionForm {
    name: string;
    description: string;
    type: string;
    included: boolean;
    sort_order: number;
}

interface CategoryForm {
    name: string;
    description: string;
    min_age: string;
    max_age: string;
    gender: string;
    sort_order: number;
    is_active: boolean;
}

interface DistanceForm {
    name: string;
    distance: string;
    unit: string;
    start_time: string;
    capacity: string;
    sort_order: number;
    is_active: boolean;
    prices: PriceForm[];
    inclusions: InclusionForm[];
    categories: CategoryForm[];
}

const props = defineProps<{
    form: {
        distances: DistanceForm[];
    };
    getError: (key: string) => string | undefined;
    getFieldClass: (key: string) => string;
    expandedDistances: number[];
}>();

const emit = defineEmits<{
    (event: 'add-distance'): void;
    (event: 'remove-distance', index: number): void;
    (event: 'toggle-distance', index: number): void;
    (event: 'add-price', distanceIndex: number): void;
    (
        event: 'remove-price',
        distanceIndex: number,
        priceIndex: number,
    ): void;
    (event: 'add-inclusion', distanceIndex: number): void;
    (
        event: 'remove-inclusion',
        distanceIndex: number,
        inclusionIndex: number,
    ): void;
    (event: 'add-category', distanceIndex: number): void;
    (
        event: 'remove-category',
        distanceIndex: number,
        categoryIndex: number,
    ): void;
}>();

const isExpanded = (index: number): boolean => {
    return props.expandedDistances.includes(index);
};

const distanceError = (
    distanceIndex: number,
    field: string,
): string | undefined => {
    return props.getError(
        `distances.${distanceIndex}.${field}`,
    );
};

const distanceFieldClass = (
    distanceIndex: number,
    field: string,
): string => {
    return props.getFieldClass(
        `distances.${distanceIndex}.${field}`,
    );
};

const priceError = (
    distanceIndex: number,
    priceIndex: number,
    field: string,
): string | undefined => {
    return props.getError(
        `distances.${distanceIndex}.prices.${priceIndex}.${field}`,
    );
};

const priceFieldClass = (
    distanceIndex: number,
    priceIndex: number,
    field: string,
): string => {
    return props.getFieldClass(
        `distances.${distanceIndex}.prices.${priceIndex}.${field}`,
    );
};

const inclusionError = (
    distanceIndex: number,
    inclusionIndex: number,
    field: string,
): string | undefined => {
    return props.getError(
        `distances.${distanceIndex}.inclusions.${inclusionIndex}.${field}`,
    );
};

const inclusionFieldClass = (
    distanceIndex: number,
    inclusionIndex: number,
    field: string,
): string => {
    return props.getFieldClass(
        `distances.${distanceIndex}.inclusions.${inclusionIndex}.${field}`,
    );
};

const categoryError = (
    distanceIndex: number,
    categoryIndex: number,
    field: string,
): string | undefined => {
    return props.getError(
        `distances.${distanceIndex}.categories.${categoryIndex}.${field}`,
    );
};

const categoryFieldClass = (
    distanceIndex: number,
    categoryIndex: number,
    field: string,
): string => {
    return props.getFieldClass(
        `distances.${distanceIndex}.categories.${categoryIndex}.${field}`,
    );
};
</script>

<template>
    <section class="distances-step">
        <div class="distances-step__header">
            <h2>
                Distancias
            </h2>

            <p>
                Configura las distancias de la carrera, sus precios,
                beneficios y categorías.
            </p>
        </div>

        <div class="distances-step__list">
            <article
                v-for="(distance, distanceIndex) in props.form.distances"
                :key="distanceIndex"
                class="distances-step__card"
            >
                <div class="distances-step__card-header">
                    <button
                        type="button"
                        class="distances-step__toggle"
                        @click="
                            emit(
                                'toggle-distance',
                                distanceIndex,
                            )
                        "
                    >
                        <div class="distances-step__main">
                            <span class="distances-step__number">
                                {{ distanceIndex + 1 }}
                            </span>

                            <div class="distances-step__heading">
                                <strong>
                                    {{
                                        distance.name ||
                                        `Distancia ${distanceIndex + 1}`
                                    }}
                                </strong>

                                <span
                                    v-if="distance.distance"
                                >
                                    {{ distance.distance }}
                                    {{ distance.unit }}
                                </span>
                            </div>
                        </div>

                        <span
                            class="distances-step__toggle-icon"
                        >
                            <ChevronUp
                                v-if="
                                    isExpanded(
                                        distanceIndex,
                                    )
                                "
                                :size="17"
                            />

                            <ChevronDown
                                v-else
                                :size="17"
                            />
                        </span>
                    </button>

                    <button
                        v-if="props.form.distances.length > 1"
                        type="button"
                        class="distances-step__delete"
                        title="Eliminar distancia"
                        @click="
                            emit(
                                'remove-distance',
                                distanceIndex,
                            )
                        "
                    >
                        <Trash2 :size="15" />
                    </button>
                </div>

                <div
                    v-if="isExpanded(distanceIndex)"
                    class="distances-step__body"
                >
                    <!-- INFORMACIÓN GENERAL -->
                    <div class="distances-step__subsection">
                        <div
                            class="distances-step__subsection-heading"
                        >
                            <h3>
                                Información de la distancia
                            </h3>

                            <p>
                                Define las características principales
                                de esta distancia.
                            </p>
                        </div>

                        <div
                            class="distances-step__form-grid"
                        >
                            <div
                                class="distances-step__field"
                            >
                                <label
                                    class="distances-step__label"
                                >
                                    Nombre
                                    <span>*</span>
                                </label>

                                <input
                                    v-model="distance.name"
                                    type="text"
                                    :class="
                                        distanceFieldClass(
                                            distanceIndex,
                                            'name',
                                        )
                                    "
                                    placeholder="Ej. 5K"
                                />

                                <p
                                    v-if="
                                        distanceError(
                                            distanceIndex,
                                            'name',
                                        )
                                    "
                                    class="distances-step__error"
                                >
                                    {{
                                        distanceError(
                                            distanceIndex,
                                            'name',
                                        )
                                    }}
                                </p>
                            </div>

                            <div
                                class="distances-step__field"
                            >
                                <label
                                    class="distances-step__label"
                                >
                                    Distancia
                                    <span>*</span>
                                </label>

                                <div
                                    class="distances-step__inline"
                                >
                                    <input
                                        v-model="
                                            distance.distance
                                        "
                                        type="number"
                                        min="0"
                                        step="0.01"
                                        :class="
                                            distanceFieldClass(
                                                distanceIndex,
                                                'distance',
                                            )
                                        "
                                        placeholder="5"
                                    />

                                    <select
                                        v-model="
                                            distance.unit
                                        "
                                        :class="
                                            distanceFieldClass(
                                                distanceIndex,
                                                'unit',
                                            )
                                        "
                                    >
                                        <option value="km">
                                            km
                                        </option>

                                        <option value="m">
                                            m
                                        </option>
                                    </select>
                                </div>

                                <p
                                    v-if="
                                        distanceError(
                                            distanceIndex,
                                            'distance',
                                        )
                                    "
                                    class="distances-step__error"
                                >
                                    {{
                                        distanceError(
                                            distanceIndex,
                                            'distance',
                                        )
                                    }}
                                </p>
                            </div>

                            <div
                                class="distances-step__field"
                            >
                                <label
                                    class="distances-step__label"
                                >
                                    Hora de salida
                                </label>

                                <input
                                    v-model="
                                        distance.start_time
                                    "
                                    type="time"
                                    :class="
                                        distanceFieldClass(
                                            distanceIndex,
                                            'start_time',
                                        )
                                    "
                                />

                                <p
                                    v-if="
                                        distanceError(
                                            distanceIndex,
                                            'start_time',
                                        )
                                    "
                                    class="distances-step__error"
                                >
                                    {{
                                        distanceError(
                                            distanceIndex,
                                            'start_time',
                                        )
                                    }}
                                </p>
                            </div>

                            <div
                                class="distances-step__field"
                            >
                                <label
                                    class="distances-step__label"
                                >
                                    Capacidad
                                </label>

                                <input
                                    v-model="
                                        distance.capacity
                                    "
                                    type="number"
                                    min="0"
                                    :class="
                                        distanceFieldClass(
                                            distanceIndex,
                                            'capacity',
                                        )
                                    "
                                    placeholder="Ej. 500"
                                />

                                <p
                                    v-if="
                                        distanceError(
                                            distanceIndex,
                                            'capacity',
                                        )
                                    "
                                    class="distances-step__error"
                                >
                                    {{
                                        distanceError(
                                            distanceIndex,
                                            'capacity',
                                        )
                                    }}
                                </p>
                            </div>

                            <div
                                class="distances-step__field"
                            >
                                <label
                                    class="distances-step__label"
                                >
                                    Orden
                                </label>

                                <input
                                    v-model.number="
                                        distance.sort_order
                                    "
                                    type="number"
                                    min="0"
                                    :class="
                                        distanceFieldClass(
                                            distanceIndex,
                                            'sort_order',
                                        )
                                    "
                                />

                                <p
                                    v-if="
                                        distanceError(
                                            distanceIndex,
                                            'sort_order',
                                        )
                                    "
                                    class="distances-step__error"
                                >
                                    {{
                                        distanceError(
                                            distanceIndex,
                                            'sort_order',
                                        )
                                    }}
                                </p>
                            </div>

                            <div
                                class="distances-step__field"
                            >
                                <label
                                    class="distances-step__checkbox-label"
                                >
                                    <input
                                        v-model="
                                            distance.is_active
                                        "
                                        type="checkbox"
                                    />

                                    <span>
                                        Distancia activa
                                    </span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- PRECIOS -->
                    <div class="distances-step__subsection">
                        <div
                            class="distances-step__dynamic-header"
                        >
                            <div
                                class="distances-step__subsection-heading"
                            >
                                <h3>
                                    Precios
                                </h3>

                                <p>
                                    Configura los precios disponibles
                                    para esta distancia.
                                </p>
                            </div>

                            <button
                                type="button"
                                class="admin-btn admin-btn-secondary distances-step__add-button"
                                @click="
                                    emit(
                                        'add-price',
                                        distanceIndex,
                                    )
                                "
                            >
                                <Plus :size="14" />
                                Agregar precio
                            </button>
                        </div>

                        <div class="distances-step__dynamic-list">
                            <div
                                v-for="(
                                    price, priceIndex
                                ) in distance.prices"
                                :key="priceIndex"
                                class="distances-step__dynamic-item"
                            >
                                <div
                                    class="distances-step__dynamic-item-header"
                                >
                                    <span>
                                        Precio
                                        {{ priceIndex + 1 }}
                                    </span>

                                    <button
                                        v-if="
                                            distance.prices
                                                .length > 1
                                        "
                                        type="button"
                                        class="distances-step__item-delete"
                                        @click="
                                            emit(
                                                'remove-price',
                                                distanceIndex,
                                                priceIndex,
                                            )
                                        "
                                    >
                                        <Trash2 :size="14" />
                                    </button>
                                </div>

                                <div
                                    class="distances-step__form-grid"
                                >
                                    <div
                                        class="distances-step__field"
                                    >
                                        <label
                                            class="distances-step__label"
                                        >
                                            Nombre
                                            <span>*</span>
                                        </label>

                                        <input
                                            v-model="
                                                price.name
                                            "
                                            type="text"
                                            :class="
                                                priceFieldClass(
                                                    distanceIndex,
                                                    priceIndex,
                                                    'name',
                                                )
                                            "
                                            placeholder="Ej. Preventa"
                                        />

                                        <p
                                            v-if="
                                                priceError(
                                                    distanceIndex,
                                                    priceIndex,
                                                    'name',
                                                )
                                            "
                                            class="distances-step__error"
                                        >
                                            {{
                                                priceError(
                                                    distanceIndex,
                                                    priceIndex,
                                                    'name',
                                                )
                                            }}
                                        </p>
                                    </div>

                                    <div
                                        class="distances-step__field"
                                    >
                                        <label
                                            class="distances-step__label"
                                        >
                                            Precio
                                            <span>*</span>
                                        </label>

                                        <input
                                            v-model="
                                                price.price
                                            "
                                            type="number"
                                            min="0"
                                            step="0.01"
                                            :class="
                                                priceFieldClass(
                                                    distanceIndex,
                                                    priceIndex,
                                                    'price',
                                                )
                                            "
                                            placeholder="359.00"
                                        />

                                        <p
                                            v-if="
                                                priceError(
                                                    distanceIndex,
                                                    priceIndex,
                                                    'price',
                                                )
                                            "
                                            class="distances-step__error"
                                        >
                                            {{
                                                priceError(
                                                    distanceIndex,
                                                    priceIndex,
                                                    'price',
                                                )
                                            }}
                                        </p>
                                    </div>

                                    <div
                                        class="distances-step__field"
                                    >
                                        <label
                                            class="distances-step__label"
                                        >
                                            Inicio de vigencia
                                        </label>

                                        <input
                                            v-model="
                                                price.starts_at
                                            "
                                            type="datetime-local"
                                            :class="
                                                priceFieldClass(
                                                    distanceIndex,
                                                    priceIndex,
                                                    'starts_at',
                                                )
                                            "
                                        />

                                        <p
                                            v-if="
                                                priceError(
                                                    distanceIndex,
                                                    priceIndex,
                                                    'starts_at',
                                                )
                                            "
                                            class="distances-step__error"
                                        >
                                            {{
                                                priceError(
                                                    distanceIndex,
                                                    priceIndex,
                                                    'starts_at',
                                                )
                                            }}
                                        </p>
                                    </div>

                                    <div
                                        class="distances-step__field"
                                    >
                                        <label
                                            class="distances-step__label"
                                        >
                                            Fin de vigencia
                                        </label>

                                        <input
                                            v-model="
                                                price.ends_at
                                            "
                                            type="datetime-local"
                                            :class="
                                                priceFieldClass(
                                                    distanceIndex,
                                                    priceIndex,
                                                    'ends_at',
                                                )
                                            "
                                        />

                                        <p
                                            v-if="
                                                priceError(
                                                    distanceIndex,
                                                    priceIndex,
                                                    'ends_at',
                                                )
                                            "
                                            class="distances-step__error"
                                        >
                                            {{
                                                priceError(
                                                    distanceIndex,
                                                    priceIndex,
                                                    'ends_at',
                                                )
                                            }}
                                        </p>
                                    </div>

                                    <div
                                        class="distances-step__field"
                                    >
                                        <label
                                            class="distances-step__label"
                                        >
                                            Capacidad
                                        </label>

                                        <input
                                            v-model="
                                                price.capacity
                                            "
                                            type="number"
                                            min="0"
                                            :class="
                                                priceFieldClass(
                                                    distanceIndex,
                                                    priceIndex,
                                                    'capacity',
                                                )
                                            "
                                            placeholder="Ej. 100"
                                        />

                                        <p
                                            v-if="
                                                priceError(
                                                    distanceIndex,
                                                    priceIndex,
                                                    'capacity',
                                                )
                                            "
                                            class="distances-step__error"
                                        >
                                            {{
                                                priceError(
                                                    distanceIndex,
                                                    priceIndex,
                                                    'capacity',
                                                )
                                            }}
                                        </p>
                                    </div>

                                    <div
                                        class="distances-step__field"
                                    >
                                        <label
                                            class="distances-step__label"
                                        >
                                            Orden
                                        </label>

                                        <input
                                            v-model.number="
                                                price.sort_order
                                            "
                                            type="number"
                                            min="0"
                                            :class="
                                                priceFieldClass(
                                                    distanceIndex,
                                                    priceIndex,
                                                    'sort_order',
                                                )
                                            "
                                        />

                                        <p
                                            v-if="
                                                priceError(
                                                    distanceIndex,
                                                    priceIndex,
                                                    'sort_order',
                                                )
                                            "
                                            class="distances-step__error"
                                        >
                                            {{
                                                priceError(
                                                    distanceIndex,
                                                    priceIndex,
                                                    'sort_order',
                                                )
                                            }}
                                        </p>
                                    </div>

                                    <div
                                        class="distances-step__field"
                                    >
                                        <label
                                            class="distances-step__checkbox-label"
                                        >
                                            <input
                                                v-model="
                                                    price.is_active
                                                "
                                                type="checkbox"
                                            />

                                            <span>
                                                Precio activo
                                            </span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- INCLUSIONES -->
                    <div class="distances-step__subsection">
                        <div
                            class="distances-step__dynamic-header"
                        >
                            <div
                                class="distances-step__subsection-heading"
                            >
                                <h3>
                                    Inclusiones
                                </h3>

                                <p>
                                    Define lo que incluye la inscripción
                                    a esta distancia.
                                </p>
                            </div>

                            <button
                                type="button"
                                class="admin-btn admin-btn-secondary distances-step__add-button"
                                @click="
                                    emit(
                                        'add-inclusion',
                                        distanceIndex,
                                    )
                                "
                            >
                                <Plus :size="14" />
                                Agregar inclusión
                            </button>
                        </div>

                        <div class="distances-step__dynamic-list">
                            <div
                                v-for="(
                                    inclusion,
                                    inclusionIndex
                                ) in distance.inclusions"
                                :key="inclusionIndex"
                                class="distances-step__dynamic-item"
                            >
                                <div
                                    class="distances-step__dynamic-item-header"
                                >
                                    <span>
                                        Inclusión
                                        {{ inclusionIndex + 1 }}
                                    </span>

                                    <button
                                        v-if="
                                            distance.inclusions
                                                .length > 1
                                        "
                                        type="button"
                                        class="distances-step__item-delete"
                                        @click="
                                            emit(
                                                'remove-inclusion',
                                                distanceIndex,
                                                inclusionIndex,
                                            )
                                        "
                                    >
                                        <Trash2 :size="14" />
                                    </button>
                                </div>

                                <div
                                    class="distances-step__form-grid"
                                >
                                    <div
                                        class="distances-step__field"
                                    >
                                        <label
                                            class="distances-step__label"
                                        >
                                            Nombre
                                            <span>*</span>
                                        </label>

                                        <input
                                            v-model="
                                                inclusion.name
                                            "
                                            type="text"
                                            :class="
                                                inclusionFieldClass(
                                                    distanceIndex,
                                                    inclusionIndex,
                                                    'name',
                                                )
                                            "
                                            placeholder="Ej. Medalla finisher"
                                        />

                                        <p
                                            v-if="
                                                inclusionError(
                                                    distanceIndex,
                                                    inclusionIndex,
                                                    'name',
                                                )
                                            "
                                            class="distances-step__error"
                                        >
                                            {{
                                                inclusionError(
                                                    distanceIndex,
                                                    inclusionIndex,
                                                    'name',
                                                )
                                            }}
                                        </p>
                                    </div>

                                    <div
                                        class="distances-step__field"
                                    >
                                        <label
                                            class="distances-step__label"
                                        >
                                            Tipo
                                        </label>

                                        <select
                                            v-model="
                                                inclusion.type
                                            "
                                            :class="
                                                inclusionFieldClass(
                                                    distanceIndex,
                                                    inclusionIndex,
                                                    'type',
                                                )
                                            "
                                        >
                                            <option value="benefit">
                                                Beneficio
                                            </option>

                                            <option value="kit">
                                                Kit
                                            </option>

                                            <option value="service">
                                                Servicio
                                            </option>

                                            <option value="other">
                                                Otro
                                            </option>
                                        </select>

                                        <p
                                            v-if="
                                                inclusionError(
                                                    distanceIndex,
                                                    inclusionIndex,
                                                    'type',
                                                )
                                            "
                                            class="distances-step__error"
                                        >
                                            {{
                                                inclusionError(
                                                    distanceIndex,
                                                    inclusionIndex,
                                                    'type',
                                                )
                                            }}
                                        </p>
                                    </div>

                                    <div
                                        class="distances-step__field distances-step__field--full"
                                    >
                                        <label
                                            class="distances-step__label"
                                        >
                                            Descripción
                                        </label>

                                        <textarea
                                            v-model="
                                                inclusion.description
                                            "
                                            :class="[
                                                inclusionFieldClass(
                                                    distanceIndex,
                                                    inclusionIndex,
                                                    'description',
                                                ),
                                                'distances-step__textarea',
                                            ]"
                                            placeholder="Describe brevemente esta inclusión..."
                                        ></textarea>

                                        <p
                                            v-if="
                                                inclusionError(
                                                    distanceIndex,
                                                    inclusionIndex,
                                                    'description',
                                                )
                                            "
                                            class="distances-step__error"
                                        >
                                            {{
                                                inclusionError(
                                                    distanceIndex,
                                                    inclusionIndex,
                                                    'description',
                                                )
                                            }}
                                        </p>
                                    </div>

                                    <div
                                        class="distances-step__field"
                                    >
                                        <label
                                            class="distances-step__checkbox-label"
                                        >
                                            <input
                                                v-model="
                                                    inclusion.included
                                                "
                                                type="checkbox"
                                            />

                                            <span>
                                                Incluido
                                            </span>
                                        </label>
                                    </div>

                                    <div
                                        class="distances-step__field"
                                    >
                                        <label
                                            class="distances-step__label"
                                        >
                                            Orden
                                        </label>

                                        <input
                                            v-model.number="
                                                inclusion.sort_order
                                            "
                                            type="number"
                                            min="0"
                                            :class="
                                                inclusionFieldClass(
                                                    distanceIndex,
                                                    inclusionIndex,
                                                    'sort_order',
                                                )
                                            "
                                        />

                                        <p
                                            v-if="
                                                inclusionError(
                                                    distanceIndex,
                                                    inclusionIndex,
                                                    'sort_order',
                                                )
                                            "
                                            class="distances-step__error"
                                        >
                                            {{
                                                inclusionError(
                                                    distanceIndex,
                                                    inclusionIndex,
                                                    'sort_order',
                                                )
                                            }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- CATEGORÍAS -->
                    <div class="distances-step__subsection">
                        <div
                            class="distances-step__dynamic-header"
                        >
                            <div
                                class="distances-step__subsection-heading"
                            >
                                <h3>
                                    Categorías
                                </h3>

                                <p>
                                    Configura las categorías disponibles
                                    para esta distancia.
                                </p>
                            </div>

                            <button
                                type="button"
                                class="admin-btn admin-btn-secondary distances-step__add-button"
                                @click="
                                    emit(
                                        'add-category',
                                        distanceIndex,
                                    )
                                "
                            >
                                <Plus :size="14" />
                                Agregar categoría
                            </button>
                        </div>

                        <div class="distances-step__dynamic-list">
                            <div
                                v-for="(
                                    category,
                                    categoryIndex
                                ) in distance.categories"
                                :key="categoryIndex"
                                class="distances-step__dynamic-item"
                            >
                                <div
                                    class="distances-step__dynamic-item-header"
                                >
                                    <span>
                                        Categoría
                                        {{ categoryIndex + 1 }}
                                    </span>

                                    <button
                                        v-if="
                                            distance.categories
                                                .length > 1
                                        "
                                        type="button"
                                        class="distances-step__item-delete"
                                        @click="
                                            emit(
                                                'remove-category',
                                                distanceIndex,
                                                categoryIndex,
                                            )
                                        "
                                    >
                                        <Trash2 :size="14" />
                                    </button>
                                </div>

                                <div
                                    class="distances-step__form-grid"
                                >
                                    <div
                                        class="distances-step__field"
                                    >
                                        <label
                                            class="distances-step__label"
                                        >
                                            Nombre
                                            <span>*</span>
                                        </label>

                                        <input
                                            v-model="
                                                category.name
                                            "
                                            type="text"
                                            :class="
                                                categoryFieldClass(
                                                    distanceIndex,
                                                    categoryIndex,
                                                    'name',
                                                )
                                            "
                                            placeholder="Ej. Juvenil"
                                        />

                                        <p
                                            v-if="
                                                categoryError(
                                                    distanceIndex,
                                                    categoryIndex,
                                                    'name',
                                                )
                                            "
                                            class="distances-step__error"
                                        >
                                            {{
                                                categoryError(
                                                    distanceIndex,
                                                    categoryIndex,
                                                    'name',
                                                )
                                            }}
                                        </p>
                                    </div>

                                    <div
                                        class="distances-step__field"
                                    >
                                        <label
                                            class="distances-step__label"
                                        >
                                            Género
                                        </label>

                                        <select
                                            v-model="
                                                category.gender
                                            "
                                            :class="
                                                categoryFieldClass(
                                                    distanceIndex,
                                                    categoryIndex,
                                                    'gender',
                                                )
                                            "
                                        >
                                            <option value="mixed">
                                                Mixto
                                            </option>

                                            <option value="male">
                                                Masculino
                                            </option>

                                            <option value="female">
                                                Femenino
                                            </option>
                                        </select>

                                        <p
                                            v-if="
                                                categoryError(
                                                    distanceIndex,
                                                    categoryIndex,
                                                    'gender',
                                                )
                                            "
                                            class="distances-step__error"
                                        >
                                            {{
                                                categoryError(
                                                    distanceIndex,
                                                    categoryIndex,
                                                    'gender',
                                                )
                                            }}
                                        </p>
                                    </div>

                                    <div
                                        class="distances-step__field"
                                    >
                                        <label
                                            class="distances-step__label"
                                        >
                                            Edad mínima
                                        </label>

                                        <input
                                            v-model="
                                                category.min_age
                                            "
                                            type="number"
                                            min="0"
                                            :class="
                                                categoryFieldClass(
                                                    distanceIndex,
                                                    categoryIndex,
                                                    'min_age',
                                                )
                                            "
                                            placeholder="Ej. 18"
                                        />

                                        <p
                                            v-if="
                                                categoryError(
                                                    distanceIndex,
                                                    categoryIndex,
                                                    'min_age',
                                                )
                                            "
                                            class="distances-step__error"
                                        >
                                            {{
                                                categoryError(
                                                    distanceIndex,
                                                    categoryIndex,
                                                    'min_age',
                                                )
                                            }}
                                        </p>
                                    </div>

                                    <div
                                        class="distances-step__field"
                                    >
                                        <label
                                            class="distances-step__label"
                                        >
                                            Edad máxima
                                        </label>

                                        <input
                                            v-model="
                                                category.max_age
                                            "
                                            type="number"
                                            min="0"
                                            :class="
                                                categoryFieldClass(
                                                    distanceIndex,
                                                    categoryIndex,
                                                    'max_age',
                                                )
                                            "
                                            placeholder="Ej. 39"
                                        />

                                        <p
                                            v-if="
                                                categoryError(
                                                    distanceIndex,
                                                    categoryIndex,
                                                    'max_age',
                                                )
                                            "
                                            class="distances-step__error"
                                        >
                                            {{
                                                categoryError(
                                                    distanceIndex,
                                                    categoryIndex,
                                                    'max_age',
                                                )
                                            }}
                                        </p>
                                    </div>

                                    <div
                                        class="distances-step__field"
                                    >
                                        <label
                                            class="distances-step__label"
                                        >
                                            Orden
                                        </label>

                                        <input
                                            v-model.number="
                                                category.sort_order
                                            "
                                            type="number"
                                            min="0"
                                            :class="
                                                categoryFieldClass(
                                                    distanceIndex,
                                                    categoryIndex,
                                                    'sort_order',
                                                )
                                            "
                                        />

                                        <p
                                            v-if="
                                                categoryError(
                                                    distanceIndex,
                                                    categoryIndex,
                                                    'sort_order',
                                                )
                                            "
                                            class="distances-step__error"
                                        >
                                            {{
                                                categoryError(
                                                    distanceIndex,
                                                    categoryIndex,
                                                    'sort_order',
                                                )
                                            }}
                                        </p>
                                    </div>

                                    <div
                                        class="distances-step__field"
                                    >
                                        <label
                                            class="distances-step__checkbox-label"
                                        >
                                            <input
                                                v-model="
                                                    category.is_active
                                                "
                                                type="checkbox"
                                            />

                                            <span>
                                                Categoría activa
                                            </span>
                                        </label>
                                    </div>

                                    <div
                                        class="distances-step__field distances-step__field--full"
                                    >
                                        <label
                                            class="distances-step__label"
                                        >
                                            Descripción
                                        </label>

                                        <textarea
                                            v-model="
                                                category.description
                                            "
                                            :class="[
                                                categoryFieldClass(
                                                    distanceIndex,
                                                    categoryIndex,
                                                    'description',
                                                ),
                                                'distances-step__textarea',
                                            ]"
                                            placeholder="Describe esta categoría..."
                                        ></textarea>

                                        <p
                                            v-if="
                                                categoryError(
                                                    distanceIndex,
                                                    categoryIndex,
                                                    'description',
                                                )
                                            "
                                            class="distances-step__error"
                                        >
                                            {{
                                                categoryError(
                                                    distanceIndex,
                                                    categoryIndex,
                                                    'description',
                                                )
                                            }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </article>
        </div>

        <button
            type="button"
            class="distances-step__add-distance"
            @click="emit('add-distance')"
        >
            <Plus :size="15" />
            Agregar distancia
        </button>
    </section>
</template>

<style scoped>
.distances-step {
    width: 100%;
}

.distances-step__header {
    margin-bottom: 26px;
}

.distances-step__header h2 {
    margin: 0;
    color: var(--sc-page-text);
    font-size: 16px;
    font-weight: 700;
    letter-spacing: -0.01em;
}

.distances-step__header p {
    margin: 6px 0 0;
    color: var(--sc-page-text-secondary);
    font-size: 12px;
    line-height: 1.5;
}

.distances-step__list {
    display: flex;
    flex-direction: column;
    gap: 13px;
}

.distances-step__card {
    overflow: hidden;
    border: 1px solid var(--sc-page-border);
    border-radius: 10px;
    background: #ffffff;
}

.distances-step__card-header {
    display: flex;
    align-items: center;
    min-height: 66px;
    padding: 0 14px;
    background: #fbfcfd;
}

.distances-step__toggle {
    display: flex;
    align-items: center;
    justify-content: space-between;
    width: 100%;
    min-width: 0;
    padding: 0;
    border: 0;
    background: transparent;
    color: var(--sc-page-text);
    font-family: inherit;
    cursor: pointer;
}

.distances-step__main {
    display: flex;
    align-items: center;
    gap: 11px;
    min-width: 0;
}

.distances-step__number {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 34px;
    height: 34px;
    flex: 0 0 34px;
    border: 1px solid #dbeaf1;
    border-radius: 8px;
    background: #f1f8fb;
    color: #5c95b1;
    font-size: 11px;
    font-weight: 700;
}

.distances-step__heading {
    display: flex;
    align-items: baseline;
    gap: 9px;
    min-width: 0;
}

.distances-step__heading strong {
    overflow: hidden;
    color: var(--sc-page-text);
    font-size: 13px;
    font-weight: 700;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.distances-step__heading span {
    color: #8497a3;
    font-size: 11px;
    white-space: nowrap;
}

.distances-step__toggle-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    color: #8799a5;
}

.distances-step__delete {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    flex: 0 0 32px;
    margin-left: 5px;
    border: 0;
    border-radius: 7px;
    background: transparent;
    color: #a2adb5;
    cursor: pointer;
    transition:
        color 0.2s ease,
        background-color 0.2s ease;
}

.distances-step__delete:hover {
    background: #fff2f1;
    color: #d95c4f;
}

.distances-step__body {
    padding: 25px 21px 21px;
    border-top: 1px solid var(--sc-page-border);
}

.distances-step__subsection {
    padding-top: 23px;
    margin-top: 23px;
    border-top: 1px solid #edf1f3;
}

.distances-step__subsection:first-child {
    padding-top: 0;
    margin-top: 0;
    border-top: 0;
}

.distances-step__subsection-heading {
    margin-bottom: 17px;
}

.distances-step__subsection-heading h3 {
    margin: 0;
    color: var(--sc-page-text);
    font-size: 13px;
    font-weight: 700;
}

.distances-step__subsection-heading p {
    margin: 5px 0 0;
    color: var(--sc-page-text-secondary);
    font-size: 11px;
}

.distances-step__form-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    column-gap: 20px;
}

.distances-step__field {
    min-width: 0;
    margin-bottom: 17px;
}

.distances-step__field--full {
    grid-column: 1 / -1;
}

.distances-step__label {
    display: block;
    margin-bottom: 8px;
    color: var(--sc-page-text);
    font-size: 13px;
    font-weight: 650;
}

.distances-step__label span {
    color: #d95c4f;
}

.distances-step__error {
    margin: 6px 0 0;
    color: #d95c4f;
    font-size: 11px;
    line-height: 1.4;
}

.distances-step__inline {
    display: grid;
    grid-template-columns: minmax(0, 1fr) 100px;
    gap: 8px;
}

.distances-step__textarea {
    height: auto;
    min-height: 105px;
    padding-top: 12px;
    padding-bottom: 12px;
    resize: vertical;
}

.distances-step__dynamic-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 15px;
    margin-bottom: 16px;
}

.distances-step__dynamic-header
    .distances-step__subsection-heading {
    margin-bottom: 0;
}

.distances-step__add-button {
    white-space: nowrap;
}

.distances-step__dynamic-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.distances-step__dynamic-item {
    position: relative;
    padding: 16px 16px 3px;
    border: 1px solid #e5ecef;
    border-radius: 9px;
    background: #fcfdfe;
}

.distances-step__dynamic-item-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 13px;
}

.distances-step__dynamic-item-header > span {
    color: #82939e;
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.04em;
}

.distances-step__item-delete {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 28px;
    height: 28px;
    border: 0;
    border-radius: 7px;
    background: transparent;
    color: #a5b0b7;
    cursor: pointer;
}

.distances-step__item-delete:hover {
    background: #fff2f1;
    color: #d95c4f;
}

.distances-step__checkbox-label {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    min-height: 44px;
    color: var(--sc-page-text);
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
}

.distances-step__checkbox-label input {
    width: 16px;
    height: 16px;
    margin: 0;
    accent-color: #70a7c1;
}

.distances-step__add-distance {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    width: 100%;
    min-height: 46px;
    margin-top: 15px;
    border: 1px dashed #c9dce6;
    border-radius: 9px;
    background: #fbfdfe;
    color: #568eac;
    font-family: inherit;
    font-size: 11px;
    font-weight: 650;
    cursor: pointer;
    transition:
        border-color 0.2s ease,
        background-color 0.2s ease;
}

.distances-step__add-distance:hover {
    border-color: #a9d9f2;
    background: #f7fcff;
}

@media (max-width: 900px) {
    .distances-step__form-grid {
        grid-template-columns: 1fr;
    }

    .distances-step__field--full {
        grid-column: auto;
    }

    .distances-step__dynamic-header {
        align-items: stretch;
        flex-direction: column;
    }

    .distances-step__add-button {
        align-self: flex-start;
    }
}

@media (max-width: 640px) {
    .distances-step__body {
        padding: 19px 13px;
    }

    .distances-step__heading span {
        display: none;
    }

    .distances-step__inline {
        grid-template-columns: 1fr;
    }
}
</style>