<template>
    <AppLayout title="Mensajes">
        <div class="max-w-3xl mx-auto px-4 py-8">
            <h1 class="text-2xl font-bold text-gray-900 mb-6">Mensajes</h1>

            <div v-if="conversations.length === 0" class="text-center py-16 text-gray-400">
                <p class="text-5xl mb-3">💬</p>
                <p class="font-medium text-gray-600">No tienes conversaciones aún</p>
                <p class="text-sm mt-1">Cuando contactes a alguien o te escriban por tus anuncios, tus mensajes aparecerán aquí.</p>
            </div>

            <div v-else class="divide-y divide-gray-100 border border-gray-200 rounded-xl overflow-hidden bg-white shadow-sm">
                <Link
                    v-for="conv in conversations"
                    :key="conv.id"
                    :href="route('messages.show', conv.id)"
                    class="flex items-center gap-4 px-5 py-4 hover:bg-gray-50 transition-colors"
                >
                    <!-- Avatar del otro usuario -->
                    <div class="relative shrink-0">
                        <img
                            :src="otherUser(conv)?.avatar_path ?? `https://ui-avatars.com/api/?name=${encodeURIComponent(otherUser(conv)?.name ?? 'U')}&background=6366f1&color=fff`"
                            :alt="otherUser(conv)?.name"
                            class="w-12 h-12 rounded-full object-cover"
                        />
                    </div>

                    <!-- Info -->
                    <div class="flex-1 min-w-0">
                        <div class="flex justify-between items-baseline">
                            <p class="font-semibold text-gray-900 truncate">{{ otherUser(conv)?.name }}</p>
                            <span class="text-xs text-gray-400 shrink-0 ml-2">{{ timeAgo(conv.last_message?.created_at) }}</span>
                        </div>
                        <p class="text-sm text-gray-500 truncate">
                            {{ conv.listing?.title ?? 'Anuncio eliminado' }}
                        </p>
                        <p class="text-sm text-gray-600 truncate mt-0.5">
                            {{ conv.last_message?.body ?? 'Sin mensajes' }}
                        </p>
                    </div>
                </Link>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, usePage } from '@inertiajs/vue3';

const props = defineProps({ conversations: Array });
const { auth } = usePage().props;

function otherUser(conv) {
    return conv.buyer_id === auth.user?.id ? conv.seller : conv.buyer;
}

function timeAgo(dateStr) {
    if (!dateStr) return '';
    const diff = (Date.now() - new Date(dateStr)) / 1000;
    if (diff < 60) return 'ahora';
    if (diff < 3600) return `${Math.floor(diff / 60)}m`;
    if (diff < 86400) return `${Math.floor(diff / 3600)}h`;
    return `${Math.floor(diff / 86400)}d`;
}
</script>
