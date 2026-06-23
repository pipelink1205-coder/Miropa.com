<template>
    <div class="space-y-5">
        <details open class="group">
            <summary class="cursor-pointer text-sm font-semibold text-ink">Modo</summary>
            <div class="mt-2 space-y-1.5">
                <label v-for="opt in modeOptions" :key="opt.value" class="flex items-center gap-2 text-sm">
                    <input v-model="model.mode" type="radio" :value="opt.value" class="text-accent" />
                    {{ opt.label }}
                </label>
                <label class="flex items-center gap-2 text-sm">
                    <input v-model="model.mode" type="radio" value="" class="text-accent" />
                    Todos
                </label>
            </div>
        </details>

        <details open class="group">
            <summary class="cursor-pointer text-sm font-semibold text-ink">Estado</summary>
            <div v-if="conditions.length" class="mt-2 space-y-1.5">
                <label v-for="cond in conditions" :key="cond.id" class="flex cursor-pointer items-center gap-2.5 rounded-lg border px-2 py-2 text-sm transition"
                    :class="model.condition === String(cond.id) ? 'border-accent bg-accent-soft' : 'border-transparent hover:bg-surface-muted'">
                    <input v-model="model.condition" type="radio" :value="String(cond.id)" class="text-accent" />
                    <span class="font-medium text-ink">{{ cond.name }}</span>
                </label>
                <label class="flex cursor-pointer items-center gap-2.5 rounded-lg px-2 py-1.5 text-sm text-ink-secondary">
                    <input v-model="model.condition" type="radio" value="" class="text-accent" />
                    Todos
                </label>
            </div>
            <p v-else class="mt-2 text-xs text-ink-muted">Estados no disponibles. Ejecuta el seeder de condiciones.</p>
        </details>

        <details class="group">
            <summary class="cursor-pointer text-sm font-semibold text-ink">Marca</summary>
            <select v-model="model.brand" class="input-search mt-2 py-2 text-sm" @change="emitApply">
                <option value="">Todas</option>
                <option v-for="brand in brands" :key="brand.id" :value="brand.id">{{ brand.name }}</option>
            </select>
        </details>

        <details class="group">
            <summary class="cursor-pointer text-sm font-semibold text-ink">Talla</summary>
            <select v-model="model.size" class="input-search mt-2 py-2 text-sm" @change="emitApply">
                <option value="">Todas</option>
                <option v-for="size in sizes" :key="size" :value="size">{{ size }}</option>
            </select>
            <select
                v-if="showHeightSizes"
                v-model="model.size_note"
                class="input-search mt-2 py-2 text-sm"
                @change="emitApply"
            >
                <option value="">Altura (cm) — opcional</option>
                <option v-for="h in heightSizes" :key="h" :value="h">{{ h }}</option>
            </select>
        </details>

        <details class="group">
            <summary class="cursor-pointer text-sm font-semibold text-ink">Color</summary>
            <fieldset class="mt-3">
                <legend class="sr-only">Filtrar por color</legend>
                <button
                    type="button"
                    class="mb-2 text-xs font-medium text-ink-secondary underline-offset-2 hover:text-accent hover:underline"
                    :class="{ 'text-accent': !model.color }"
                    @click="clearColor"
                >
                    Todos los colores
                </button>
                <ul class="grid grid-cols-2 gap-2" role="list">
                    <li v-for="color in colors" :key="color.name">
                        <button
                            type="button"
                            class="color-swatch group/swatch flex w-full items-center gap-2.5 rounded-lg border px-2 py-2 text-left transition"
                            :class="model.color === color.name
                                ? 'border-accent bg-accent-soft ring-2 ring-accent/30'
                                : 'border-zinc-200 bg-surface-raised hover:border-zinc-300'"
                            :aria-pressed="model.color === color.name"
                            :aria-label="`Color ${color.name}${model.color === color.name ? ', seleccionado' : ''}`"
                            @click="toggleColor(color.name)"
                        >
                            <span
                                class="color-swatch__dot relative h-7 w-7 shrink-0 rounded-full"
                                :class="{
                                    'color-swatch__dot--border': color.border,
                                    'color-swatch__dot--multicolor': color.pattern === 'multicolor',
                                    'color-swatch__dot--other': color.pattern === 'other',
                                }"
                                :style="color.hex ? { backgroundColor: color.hex } : undefined"
                                aria-hidden="true"
                            />
                            <span class="text-xs font-medium leading-tight text-ink">{{ color.name }}</span>
                        </button>
                    </li>
                </ul>
            </fieldset>
        </details>

        <details class="group">
            <summary class="cursor-pointer text-sm font-semibold text-ink">Precio</summary>
            <div class="mt-2 flex gap-2">
                <input v-model="model.min_price" type="number" placeholder="Mín" class="input-search py-2 text-sm" />
                <input v-model="model.max_price" type="number" placeholder="Máx" class="input-search py-2 text-sm" />
            </div>
        </details>

        <details v-if="universes.length" class="group">
            <summary class="cursor-pointer text-sm font-semibold text-ink">Universo</summary>
            <select v-model="model.universe" class="input-search mt-2 py-2 text-sm" @change="emitApply">
                <option value="">Todos</option>
                <option v-for="u in universes" :key="u.slug" :value="u.slug">{{ u.name }}</option>
            </select>
        </details>

        <button type="button" class="w-full text-sm font-medium text-ink-muted hover:text-ink" @click="$emit('clear')">
            Limpiar filtros
        </button>
    </div>
</template>

<script setup>
import { watch } from 'vue';

const model = defineModel({ type: Object, required: true });

defineProps({
    conditions: { type: Array, default: () => [] },
    brands: { type: Array, default: () => [] },
    sizes: { type: Array, default: () => [] },
    heightSizes: { type: Array, default: () => [] },
    showHeightSizes: { type: Boolean, default: false },
    colors: { type: Array, default: () => [] },
    universes: { type: Array, default: () => [] },
});

const emit = defineEmits(['apply', 'clear']);

let priceDebounce = null;

function emitApply() {
    emit('apply');
}

function toggleColor(name) {
    model.value.color = model.value.color === name ? '' : name;
    emitApply();
}

function clearColor() {
    model.value.color = '';
    emitApply();
}

watch(
    () => [model.value.mode, model.value.condition],
    () => emitApply(),
);

watch(
    () => [model.value.min_price, model.value.max_price],
    () => {
        clearTimeout(priceDebounce);
        priceDebounce = setTimeout(() => emitApply(), 450);
    },
);

const modeOptions = [
    { label: 'Compra protegida', value: 'compra_protegida' },
    { label: 'Trato directo', value: 'trato_directo' },
];
</script>

<style scoped>
.color-swatch__dot--border {
    box-shadow: inset 0 0 0 1px rgba(0, 0, 0, 0.12);
}

.color-swatch__dot--multicolor {
    background: conic-gradient(
        #dc2626,
        #eab308,
        #16a34a,
        #2563eb,
        #9333ea,
        #dc2626
    );
}

.color-swatch__dot--other {
    background: repeating-linear-gradient(
        -45deg,
        #e7e5e4,
        #e7e5e4 3px,
        #fafaf9 3px,
        #fafaf9 6px
    );
    box-shadow: inset 0 0 0 1px #d6d3d1;
}
</style>
