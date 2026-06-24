<template>
    <div class="flex flex-wrap gap-1.5" :class="compact ? 'gap-1' : 'gap-1.5'">
        <span
            v-if="email"
            title="Email verificado"
            class="inline-flex items-center gap-0.5 font-medium text-blue-700 bg-blue-50 rounded-full"
            :class="compact ? 'text-[10px] px-1.5 py-0.5' : 'text-xs px-2 py-0.5'"
        >
            ✓ Email
        </span>
        <span
            v-if="phone"
            title="Teléfono verificado"
            class="inline-flex items-center gap-0.5 font-medium text-emerald-700 bg-emerald-50 rounded-full"
            :class="compact ? 'text-[10px] px-1.5 py-0.5' : 'text-xs px-2 py-0.5'"
        >
            ✓ Celular
        </span>
        <span
            v-if="identity"
            title="Identidad verificada"
            class="inline-flex items-center gap-0.5 font-medium text-accent bg-accent-soft rounded-full"
            :class="compact ? 'text-[10px] px-1.5 py-0.5' : 'text-xs px-2 py-0.5'"
        >
            ✓ Identidad
        </span>
    </div>
</template>

<script setup>
defineProps({
    email: { type: Boolean, default: false },
    phone: { type: Boolean, default: false },
    identity: { type: Boolean, default: false },
    compact: { type: Boolean, default: false },
});
</script>
