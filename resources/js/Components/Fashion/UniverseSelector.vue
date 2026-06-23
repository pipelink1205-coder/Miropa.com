<template>
    <div>
        <div class="flex items-baseline justify-between gap-2">
            <div>
                <p class="text-sm font-medium text-ink">Universos <span class="font-normal text-ink-muted">(opcional)</span></p>
                <p class="mt-0.5 text-xs text-ink-secondary">
                    Ayuda a que te encuentren por estilo o intención. Máximo {{ max }}.
                </p>
            </div>
            <span v-if="selectedIds.length" class="text-xs text-ink-muted">{{ selectedIds.length }}/{{ max }}</span>
        </div>

        <div class="mt-3 flex flex-wrap gap-2">
            <button
                v-for="universe in universes"
                :key="universe.id"
                type="button"
                class="group/universe relative rounded-full border px-3 py-1.5 text-sm font-medium transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-accent/40"
                :class="isSelected(universe.id)
                    ? 'shadow-sm'
                    : 'opacity-80 hover:opacity-100'"
                :style="chipStyle(universe)"
                :title="universe.description || undefined"
                :aria-pressed="isSelected(universe.id)"
                @click="toggle(universe.id)"
            >
                {{ universe.name }}

                <span
                    v-if="universe.description"
                    role="tooltip"
                    class="pointer-events-none absolute bottom-[calc(100%+0.4rem)] left-1/2 z-30 w-52 -translate-x-1/2 rounded-lg bg-ink px-2.5 py-2 text-left text-[11px] font-normal leading-snug text-white opacity-0 shadow-lg transition duration-150 group-hover/universe:opacity-100 group-focus-visible/universe:opacity-100"
                >
                    {{ universe.description }}
                    <span
                        class="absolute left-1/2 top-full -translate-x-1/2 border-4 border-transparent border-t-ink"
                        aria-hidden="true"
                    />
                </span>
            </button>
        </div>

        <p v-if="error" class="mt-2 text-xs text-red-600">{{ error }}</p>
    </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    universes: { type: Array, default: () => [] },
    modelValue: { type: Array, default: () => [] },
    max: { type: Number, default: 3 },
    error: { type: String, default: '' },
});

const emit = defineEmits(['update:modelValue']);

const selectedIds = computed(() => props.modelValue ?? []);

function isSelected(id) {
    return selectedIds.value.map(Number).includes(Number(id));
}

function chipStyle(universe) {
    const color = universe.accent_color ?? '#c2410c';
    const selected = isSelected(universe.id);

    return {
        borderColor: color + (selected ? '' : '40'),
        color,
        backgroundColor: selected ? color + '18' : 'transparent',
    };
}

function toggle(id) {
    const normalizedId = Number(id);
    const current = selectedIds.value.map(Number);
    const index = current.indexOf(normalizedId);

    if (index >= 0) {
        current.splice(index, 1);
    } else if (current.length < props.max) {
        current.push(normalizedId);
    }

    emit('update:modelValue', current);
}
</script>
