<template>
    <div class="flex min-h-screen items-center justify-center bg-surface px-4 py-10">
        <div class="w-full max-w-lg">
            <div class="rounded-2xl border border-zinc-200 bg-surface-raised p-8 shadow-[var(--shadow-elevated)] md:p-10">
                <div class="mb-8 text-center">
                    <AppLogo class="justify-center" />
                    <h1 v-if="headline" class="mt-6 text-xl font-bold leading-snug text-ink md:text-2xl">
                        {{ headline }}
                    </h1>
                    <p v-if="subtitle" class="mt-2 text-sm leading-relaxed text-ink-secondary">
                        {{ subtitle }}
                    </p>
                </div>

                <slot />

                <p v-if="$slots.footer" class="mt-8 text-center text-sm text-ink-secondary">
                    <slot name="footer" />
                </p>
            </div>
        </div>
    </div>
</template>

<script setup>
import AppLogo from '@/Components/AppLogo.vue';

defineProps({
    headline: { type: String, default: '' },
    subtitle: { type: String, default: '' },
});
</script>
