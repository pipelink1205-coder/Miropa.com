<template>
    <div>
        <p class="mb-2 text-sm font-medium text-ink">
            Talla <span v-if="required" class="text-accent">*</span>
        </p>
        <div class="flex flex-wrap gap-2">
            <button
                v-for="size in sizes"
                :key="size"
                type="button"
                class="min-w-[2.75rem] rounded-full border px-3 py-1.5 text-xs font-medium transition"
                :class="modelValue === size ? 'border-accent bg-accent text-white' : 'border-zinc-200 text-ink-secondary hover:border-accent'"
                @click="$emit('update:modelValue', size)"
            >
                {{ size }}
            </button>
        </div>
        <p v-if="error" class="mt-1 text-xs text-red-600">{{ error }}</p>
    </div>
</template>

<script setup>
defineProps({
    sizes: { type: Array, default: () => [] },
    modelValue: { type: String, default: '' },
    required: { type: Boolean, default: true },
    error: { type: String, default: '' },
});

defineEmits(['update:modelValue']);
</script>
