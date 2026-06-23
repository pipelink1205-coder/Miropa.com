<template>
    <div>
        <p class="mb-2 text-sm font-medium text-ink">
            Color <span v-if="required" class="text-accent">*</span>
        </p>
        <ul class="grid grid-cols-2 gap-2 sm:grid-cols-3" role="list">
            <li v-for="color in colors" :key="color.name">
                <button
                    type="button"
                    class="flex w-full items-center gap-2.5 rounded-lg border px-2 py-2 text-left text-sm transition"
                    :class="modelValue === color.name
                        ? 'border-accent bg-accent-soft ring-2 ring-accent/30'
                        : 'border-zinc-200 bg-surface-raised hover:border-zinc-300'"
                    :aria-pressed="modelValue === color.name"
                    @click="$emit('update:modelValue', color.name)"
                >
                    <span
                        class="h-5 w-5 shrink-0 rounded-full border border-zinc-200"
                        :class="{
                            'bg-gradient-to-br from-pink-400 via-yellow-300 to-blue-400': color.pattern === 'multicolor',
                            'bg-zinc-100': color.pattern === 'other',
                        }"
                        :style="color.hex ? { backgroundColor: color.hex } : undefined"
                    />
                    <span class="font-medium text-ink">{{ color.name }}</span>
                </button>
            </li>
        </ul>
        <div v-if="modelValue === 'Otro'" class="mt-2">
            <input
                v-model="otherColor"
                type="text"
                maxlength="64"
                class="input-search py-2 text-sm"
                placeholder="Describe el color"
                @input="$emit('update:otherColor', otherColor)"
            />
        </div>
        <p v-if="error" class="mt-1 text-xs text-red-600">{{ error }}</p>
    </div>
</template>

<script setup>
import { ref, watch } from 'vue';

const props = defineProps({
    colors: { type: Array, default: () => [] },
    modelValue: { type: String, default: '' },
    otherColorValue: { type: String, default: '' },
    required: { type: Boolean, default: true },
    error: { type: String, default: '' },
});

defineEmits(['update:modelValue', 'update:otherColor']);

const otherColor = ref(props.otherColorValue);

watch(() => props.otherColorValue, (v) => {
    otherColor.value = v;
});
</script>
