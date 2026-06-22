<template>
    <Transition
        enter-active-class="transition duration-300 ease-out"
        enter-from-class="translate-y-full opacity-0"
        enter-to-class="translate-y-0 opacity-100"
        leave-active-class="transition duration-200 ease-in"
        leave-from-class="translate-y-0 opacity-100"
        leave-to-class="translate-y-full opacity-0"
    >
        <div
            v-if="visible"
            class="fixed bottom-4 left-4 right-4 z-50 mx-auto max-w-md"
        >
            <Link
                :href="`/mensajes/${toast.conversationId}`"
                class="flex items-start gap-3 bg-gray-900 text-white rounded-2xl shadow-2xl px-4 py-3 hover:bg-gray-800 transition"
                @click="dismiss"
            >
                <div class="w-10 h-10 rounded-full bg-indigo-500 flex items-center justify-center font-bold text-sm shrink-0">
                    {{ toast.senderName.charAt(0).toUpperCase() }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="font-semibold text-sm">{{ toast.senderName }}</p>
                    <p v-if="toast.listingTitle" class="text-xs text-gray-400 truncate">{{ toast.listingTitle }}</p>
                    <p class="text-sm text-gray-200 truncate mt-0.5">{{ toast.body }}</p>
                </div>
                <button
                    type="button"
                    class="text-gray-400 hover:text-white text-lg leading-none shrink-0 p-1"
                    @click.prevent="dismiss"
                >
                    ×
                </button>
            </Link>
        </div>
    </Transition>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import { ref, watch, onUnmounted } from 'vue';

const props = defineProps({
    toast: Object,
});

const emit = defineEmits(['dismiss']);

const visible = ref(false);
let timer;

watch(() => props.toast, (value) => {
    clearTimeout(timer);
    if (! value) {
        visible.value = false;
        return;
    }

    visible.value = true;
    timer = setTimeout(() => emit('dismiss', value.id), 6000);
}, { immediate: true });

function dismiss() {
    clearTimeout(timer);
    visible.value = false;
    emit('dismiss', props.toast?.id);
}

onUnmounted(() => clearTimeout(timer));
</script>
