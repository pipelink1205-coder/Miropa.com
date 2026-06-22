import { ref, watch, onMounted, onUnmounted, computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { whenEchoReady, dispatchChatMessage } from '@/composables/useEcho';

export function useMessageNotifications() {
    const page = usePage();
    const unreadCount = ref(page.props.unread_messages_count ?? 0);
    const toast = ref(null);
    let cleanupEcho;

    const userId = computed(() => page.props.auth?.user?.id);

    watch(() => page.props.unread_messages_count, (count) => {
        if (count !== undefined) {
            unreadCount.value = count;
        }
    });

    const badgeLabel = computed(() => {
        if (unreadCount.value <= 0) return '';
        return unreadCount.value > 99 ? '99+' : String(unreadCount.value);
    });

    function isViewingConversation(conversationId) {
        const pattern = new RegExp(`/mensajes/${conversationId}(?:\\?|$)`);
        return pattern.test(page.url);
    }

    function handleIncomingMessage(event) {
        const conversationId = event.conversation_id;
        const senderId = event.message?.sender_id;

        if (senderId === userId.value) {
            return;
        }

        if (isViewingConversation(conversationId)) {
            dispatchChatMessage(event);
            return;
        }

        unreadCount.value += 1;
        toast.value = {
            id: Date.now(),
            conversationId,
            senderName: event.sender_name ?? 'Alguien',
            listingTitle: event.listing_title ?? '',
            body: event.message?.body ?? '',
        };
    }

    function dismissToast(id) {
        if (toast.value?.id === id) {
            toast.value = null;
        }
    }

    function subscribe(Echo) {
        Echo.private(`App.Models.User.${userId.value}`)
            .listen('.MessageSent', handleIncomingMessage);
    }

    function unsubscribe() {
        if (window.Echo && userId.value) {
            window.Echo.leave(`App.Models.User.${userId.value}`);
        }
    }

    onMounted(() => {
        if (userId.value) {
            cleanupEcho = whenEchoReady(subscribe);
        }
    });

    watch(userId, (id, previous) => {
        if (previous) {
            unsubscribe();
        }
        if (id && window.Echo) {
            subscribe(window.Echo);
        }
    });

    onUnmounted(() => {
        cleanupEcho?.();
        unsubscribe();
    });

    return {
        unreadCount,
        badgeLabel,
        toast,
        dismissToast,
    };
}
