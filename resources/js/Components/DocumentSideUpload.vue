<template>
    <div>
        <label :for="id" class="mb-2 block text-sm font-semibold text-ink">
            {{ label }}
        </label>
        <p v-if="hint" class="mb-3 text-xs text-ink-muted">{{ hint }}</p>

        <div
            class="rounded-2xl border-2 border-dashed border-zinc-200 bg-surface-muted/50 p-5 text-center transition"
            :class="modelValue ? 'border-accent/40 bg-accent-soft/30' : 'hover:border-zinc-300'"
        >
            <input
                :id="id"
                type="file"
                accept=".jpg,.jpeg,.png,.pdf,image/jpeg,image/png,application/pdf"
                class="sr-only"
                @change="onFileChange"
            />
            <label :for="id" class="cursor-pointer">
                <svg class="mx-auto h-9 w-9 text-ink-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                </svg>
                <p class="mt-2 text-sm font-medium text-ink">{{ fileLabel }}</p>
                <p class="mt-1 text-xs text-ink-muted">Toca para seleccionar</p>
            </label>

            <img
                v-if="previewUrl"
                :src="previewUrl"
                :alt="`Vista previa: ${label}`"
                class="mx-auto mt-4 max-h-40 rounded-xl border border-zinc-200 object-contain"
            />
        </div>

        <p v-if="error" class="mt-1 text-xs text-red-600">{{ error }}</p>
    </div>
</template>

<script setup>
import { computed, onUnmounted, ref, watch } from 'vue';

const props = defineProps({
    id: { type: String, required: true },
    label: { type: String, required: true },
    hint: { type: String, default: '' },
    error: { type: String, default: '' },
    modelValue: { type: [Object, null], default: null },
});

const emit = defineEmits(['update:modelValue', 'preview']);

const previewUrl = ref(null);

const fileLabel = computed(() =>
    props.modelValue instanceof File ? props.modelValue.name : 'Sin archivo seleccionado',
);

watch(() => props.modelValue, (file, oldFile) => {
    if (previewUrl.value) {
        URL.revokeObjectURL(previewUrl.value);
        previewUrl.value = null;
    }

    if (file instanceof File && file.type.startsWith('image/')) {
        previewUrl.value = URL.createObjectURL(file);
    }
});

function onFileChange(event) {
    const file = event.target.files?.[0] ?? null;
    emit('update:modelValue', file);
    emit('preview', file);
}

onUnmounted(() => {
    if (previewUrl.value) {
        URL.revokeObjectURL(previewUrl.value);
    }
});
</script>
