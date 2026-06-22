<template>
    <div class="flex min-h-screen flex-col bg-surface">
        <!-- Navbar -->
        <header class="sticky top-0 z-40 border-b border-zinc-200/80 bg-surface-raised/90 shadow-[var(--shadow-nav)] backdrop-blur-md">
            <div class="container-app">
                <div class="flex h-16 items-center justify-between gap-4 md:h-[4.25rem]">
                    <AppLogo />

                    <!-- Búsqueda global -->
                    <form action="/anuncios" method="GET" class="mx-4 hidden max-w-md flex-1 md:block lg:max-w-xl" role="search">
                        <label for="nav-search" class="sr-only">Buscar artículos</label>
                        <input
                            id="nav-search"
                            type="search"
                            name="q"
                            placeholder="Buscar artículos..."
                            class="input-search py-2.5"
                        />
                    </form>

                    <!-- Acciones -->
                    <div class="flex shrink-0 items-center gap-2 sm:gap-3">
                        <template v-if="$page.props.auth?.user">
                            <Link
                                href="/mensajes"
                                class="relative rounded-full p-2.5 text-ink-secondary transition hover:bg-surface-muted hover:text-accent"
                                title="Mensajes"
                                aria-label="Mensajes"
                            >
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                </svg>
                                <span
                                    v-if="badgeLabel"
                                    class="absolute -right-0.5 -top-0.5 flex h-[1.125rem] min-w-[1.125rem] items-center justify-center rounded-full bg-accent px-1 text-[10px] font-bold leading-none text-white"
                                >
                                    {{ badgeLabel }}
                                </span>
                            </Link>
                            <Link
                                href="/listings/create"
                                class="btn-primary hidden px-5 py-2.5 text-sm sm:inline-flex"
                            >
                                + Publicar
                            </Link>
                            <div class="relative" ref="menuRef">
                                <button
                                    type="button"
                                    :aria-expanded="menuOpen"
                                    aria-haspopup="true"
                                    aria-label="Menú de cuenta"
                                    @click="menuOpen = !menuOpen"
                                    class="flex items-center rounded-full p-1 transition hover:bg-surface-muted"
                                >
                                    <div class="flex h-9 w-9 items-center justify-center rounded-full bg-accent-soft text-sm font-bold text-accent">
                                        {{ $page.props.auth.user.name.charAt(0).toUpperCase() }}
                                    </div>
                                </button>
                                <div
                                    v-if="menuOpen"
                                    class="absolute right-0 z-50 mt-2 w-52 overflow-hidden rounded-2xl border border-zinc-100 bg-surface-raised py-1 shadow-[var(--shadow-elevated)]"
                                    role="menu"
                                >
                                    <Link href="/dashboard" class="block px-4 py-2.5 text-sm text-ink transition hover:bg-surface-muted" role="menuitem">Mi panel</Link>
                                    <Link href="/cuenta" class="block px-4 py-2.5 text-sm text-ink transition hover:bg-surface-muted" role="menuitem">Mi cuenta</Link>
                                    <Link href="/mensajes" class="flex items-center justify-between px-4 py-2.5 text-sm text-ink transition hover:bg-surface-muted" role="menuitem">
                                        <span>Mensajes</span>
                                        <span
                                            v-if="badgeLabel"
                                            class="flex h-[1.125rem] min-w-[1.125rem] items-center justify-center rounded-full bg-accent px-1 text-[10px] font-bold text-white"
                                        >
                                            {{ badgeLabel }}
                                        </span>
                                    </Link>
                                    <Link :href="`/u/${$page.props.auth.user.username}`" class="block px-4 py-2.5 text-sm text-ink transition hover:bg-surface-muted" role="menuitem">Mi perfil</Link>
                                    <hr class="my-1 border-zinc-100">
                                    <Link href="/logout" method="post" as="button" class="block w-full px-4 py-2.5 text-left text-sm text-red-600 transition hover:bg-red-50" role="menuitem">
                                        Cerrar sesión
                                    </Link>
                                </div>
                            </div>
                        </template>
                        <template v-else>
                            <Link href="/login" class="px-3 py-2 text-sm font-semibold text-ink-secondary transition hover:text-accent">
                                Ingresar
                            </Link>
                            <Link href="/register" class="btn-primary px-5 py-2.5 text-sm">
                                Publicar
                            </Link>
                        </template>
                    </div>
                </div>
            </div>
        </header>

        <main class="flex-1">
            <slot />
        </main>

        <AppFooter />

        <MessageToast
            v-if="toast"
            :toast="toast"
            @dismiss="dismissToast"
        />
    </div>
</template>

<script setup>
import AppFooter from '@/Components/AppFooter.vue';
import AppLogo from '@/Components/AppLogo.vue';
import MessageToast from '@/Components/MessageToast.vue';
import { useMessageNotifications } from '@/composables/useMessageNotifications';
import { Link } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted } from 'vue';

defineProps({ title: String });

const { badgeLabel, toast, dismissToast } = useMessageNotifications();

const menuOpen = ref(false);
const menuRef = ref(null);

function handleOutsideClick(e) {
    if (menuRef.value && ! menuRef.value.contains(e.target)) {
        menuOpen.value = false;
    }
}

onMounted(() => document.addEventListener('click', handleOutsideClick));
onUnmounted(() => document.removeEventListener('click', handleOutsideClick));
</script>
