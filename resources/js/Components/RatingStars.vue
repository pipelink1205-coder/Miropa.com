<template>
    <div class="flex items-center gap-0.5">
        <span
            v-for="n in 5"
            :key="n"
            class="text-sm"
            :class="n <= Math.round(rating) ? 'text-yellow-400' : 'text-gray-300'"
        >★</span>
        <span v-if="showCount" class="text-xs text-gray-500 ml-1">({{ count }})</span>
    </div>
</template>

<script setup>
defineProps({
    rating:     { type: Number, default: 0 },
    count:      { type: Number, default: 0 },
    showCount:  { type: Boolean, default: false },
});
</script>
