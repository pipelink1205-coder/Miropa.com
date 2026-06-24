<template>
    <AppLayout :title="`Chat con ${otherUser.name}`">
        <div class="max-w-3xl mx-auto px-4 py-6 flex flex-col relative" style="height: calc(100vh - 80px)">

            <!-- Cabecera -->
            <div class="flex items-center gap-3 pb-4 border-b border-gray-200 shrink-0">
                <Link :href="route('messages.index')" class="text-gray-400 hover:text-gray-600 mr-1">←</Link>
                <img
                    :src="otherUser.avatar_path ?? `https://ui-avatars.com/api/?name=${encodeURIComponent(otherUser.name)}&background=6366f1&color=fff`"
                    :alt="otherUser.name"
                    class="w-10 h-10 rounded-full object-cover"
                />
                <div class="flex-1 min-w-0">
                    <p class="font-semibold text-gray-900">{{ otherUser.name }}</p>
                    <Link :href="route('listings.show', conversation.listing?.slug)" class="text-xs text-accent truncate hover:underline">
                        {{ conversation.listing?.title }}
                    </Link>
                </div>
                <span v-if="conversation.listing?.price_formatted" class="text-sm font-bold text-green-700">
                    {{ conversation.listing?.price_formatted }}
                </span>
            </div>

            <!-- Mensajes -->
            <div ref="messagesEl" class="flex-1 overflow-y-auto py-4 space-y-3">
                <div
                    v-for="msg in allMessages"
                    :key="msg.id"
                    class="flex"
                    :class="msg.sender_id === auth.user.id ? 'justify-end' : 'justify-start'"
                >
                    <div
                        class="max-w-xs lg:max-w-md px-4 py-2.5 rounded-2xl text-sm"
                        :class="msg.sender_id === auth.user.id
                            ? 'bg-accent text-white rounded-br-sm'
                            : 'bg-gray-100 text-gray-900 rounded-bl-sm'"
                    >
                        <p class="whitespace-pre-wrap break-words">{{ msg.body }}</p>
                        <p class="text-xs mt-1 opacity-60 text-right">{{ formatTime(msg.created_at) }}</p>
                    </div>
                </div>
            </div>

            <!-- Input -->
            <form @submit.prevent="send" class="flex items-end gap-2 pt-3 border-t border-gray-200 shrink-0">
                <p v-if="form.errors.body" class="absolute bottom-20 left-4 right-4 text-xs text-red-600 bg-red-50 border border-red-100 rounded-lg px-3 py-2">
                    {{ form.errors.body }}
                </p>
                <textarea
                    v-model="form.body"
                    @keydown.enter.exact.prevent="send"
                    rows="1"
                    placeholder="Escribe un mensaje..."
                    class="flex-1 resize-none border rounded-2xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-accent/20 focus:outline-none max-h-32 overflow-y-auto"
                    :disabled="sending"
                />
                <button
                    type="submit"
                    :disabled="!form.body.trim() || sending"
                    class="bg-accent text-white rounded-full w-10 h-10 flex items-center justify-center disabled:opacity-40 hover:bg-accent-hover shrink-0"
                >
                    <svg v-if="!sending" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                    </svg>
                    <span v-else class="w-4 h-4 border-2 border-white/40 border-t-white rounded-full animate-spin" />
                </button>
            </form>
        </div>
    </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { whenEchoReady } from '@/composables/useEcho';
import { Link, useForm, usePage } from '@inertiajs/vue3';
import { ref, computed, onMounted, onUnmounted, nextTick, watch } from 'vue';
import axios from 'axios';

const props = defineProps({
    conversation: Object,
    messages: Array,
});

const { auth } = usePage().props;
const messagesEl = ref(null);
const form = useForm({ body: '' });
const sending = ref(false);
const realtimeMessages = ref([]);
const knownIds = ref(new Set(props.messages.map((m) => m.id)));

let echoCleanup;
let pollInterval;

const otherUser = computed(() => {
    return props.conversation.buyer_id === auth.user.id
        ? props.conversation.seller
        : props.conversation.buyer;
});

const allMessages = computed(() => {
    const merged = [...props.messages, ...realtimeMessages.value];
    const seen = new Set();

    return merged.filter((message) => {
        if (seen.has(message.id)) {
            return false;
        }
        seen.add(message.id);
        return true;
    });
});

watch(() => props.messages, (messages) => {
    knownIds.value = new Set(messages.map((m) => m.id));
    realtimeMessages.value = [];
    nextTick(scrollBottom);
}, { deep: true });

function formatTime(dateStr) {
    if (!dateStr) return '';
    return new Date(dateStr).toLocaleTimeString('es-CO', { hour: '2-digit', minute: '2-digit' });
}

function appendMessage(message) {
    if (! message?.id || knownIds.value.has(message.id)) {
        return;
    }

    knownIds.value.add(message.id);
    realtimeMessages.value.push(message);
    nextTick(scrollBottom);
}

function handleIncomingEvent(event) {
    if (event.conversation_id !== props.conversation.id) {
        return;
    }

    if (event.message?.sender_id !== auth.user.id) {
        appendMessage(event.message);
    }
}

async function send() {
    const body = form.body.trim();
    if (! body || sending.value) {
        return;
    }

    sending.value = true;

    try {
        const { data } = await axios.post(route('messages.send', props.conversation.id), { body });
        appendMessage(data.data);
        form.reset('body');
        form.clearErrors();
        nextTick(scrollBottom);
    } catch (error) {
        if (error.response?.status === 422 && error.response.data?.errors?.body) {
            form.setError('body', error.response.data.errors.body[0]);
        }
    } finally {
        sending.value = false;
    }
}

async function pollMessages() {
    const lastId = Math.max(0, ...knownIds.value);

    try {
        const { data } = await axios.get(`/mensajes/${props.conversation.id}/messages`, {
            params: { after: lastId },
        });

        data.data.forEach(appendMessage);
    } catch {
        // Respaldo silencioso
    }
}

function scrollBottom() {
    if (messagesEl.value) {
        messagesEl.value.scrollTop = messagesEl.value.scrollHeight;
    }
}

onMounted(() => {
    scrollBottom();

    window.addEventListener('chat:message-received', onChatEvent);

    echoCleanup = whenEchoReady((Echo) => {
        Echo.private(`conversation.${props.conversation.id}`)
            .listen('.MessageSent', handleIncomingEvent);
    });

    pollInterval = setInterval(pollMessages, 4000);
});

function onChatEvent(e) {
    handleIncomingEvent(e.detail);
}

onUnmounted(() => {
    window.removeEventListener('chat:message-received', onChatEvent);
    echoCleanup?.();

    if (window.Echo) {
        window.Echo.leave(`conversation.${props.conversation.id}`);
    }

    clearInterval(pollInterval);
});
</script>
