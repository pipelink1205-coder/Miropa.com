<template>
    <footer class="mt-auto border-t border-zinc-200/80 bg-ink text-white" role="contentinfo">
        <div class="container-app py-16">
            <div class="grid grid-cols-1 gap-12 md:grid-cols-2 lg:grid-cols-4">
                <!-- Marca -->
                <div class="lg:col-span-1">
                    <Link href="/" class="inline-flex items-center gap-2">
                        <img
                            v-if="brand.logo_url"
                            :src="brand.logo_url"
                            :alt="brand.name"
                            class="h-8 w-auto brightness-0 invert"
                        />
                        <span v-else class="text-xl font-bold">{{ brand.name }}</span>
                    </Link>
                    <p class="mt-4 text-sm leading-relaxed text-zinc-400">
                        {{ brand.tagline }}
                    </p>
                </div>

                <!-- Comprar -->
                <div>
                    <h3 class="text-sm font-semibold uppercase tracking-wider text-zinc-300">Comprar</h3>
                    <ul class="mt-4 space-y-3">
                        <li><Link href="/anuncios" class="text-sm text-zinc-400 transition hover:text-white">Explorar anuncios</Link></li>
                        <li><Link href="/anuncios?sort=popular" class="text-sm text-zinc-400 transition hover:text-white">Más populares</Link></li>
                        <li><Link href="/anuncios?category=ropa-y-accesorios" class="text-sm text-zinc-400 transition hover:text-white">Moda y accesorios</Link></li>
                    </ul>
                </div>

                <!-- Vender -->
                <div>
                    <h3 class="text-sm font-semibold uppercase tracking-wider text-zinc-300">Vender</h3>
                    <ul class="mt-4 space-y-3">
                        <li><Link href="/listings/create" class="text-sm text-zinc-400 transition hover:text-white">Publicar anuncio</Link></li>
                        <li><Link href="/register" class="text-sm text-zinc-400 transition hover:text-white">Crear cuenta</Link></li>
                        <li><Link href="/dashboard" class="text-sm text-zinc-400 transition hover:text-white">Mi panel</Link></li>
                    </ul>
                </div>

                <!-- Cuenta -->
                <div>
                    <h3 class="text-sm font-semibold uppercase tracking-wider text-zinc-300">Cuenta</h3>
                    <ul class="mt-4 space-y-3">
                        <li><Link href="/login" class="text-sm text-zinc-400 transition hover:text-white">Ingresar</Link></li>
                        <li><Link href="/cuenta" class="text-sm text-zinc-400 transition hover:text-white">Mi cuenta</Link></li>
                        <li><Link href="/mensajes" class="text-sm text-zinc-400 transition hover:text-white">Mensajes</Link></li>
                    </ul>
                </div>
            </div>

            <div class="mt-12 flex flex-col items-center justify-between gap-4 border-t border-zinc-800 pt-8 sm:flex-row">
                <p class="text-sm text-zinc-500">
                    © {{ year }} {{ brand.name }}. Todos los derechos reservados.
                </p>
                <p class="text-sm text-zinc-500">
                    {{ brand.domain }}
                </p>
            </div>
        </div>
    </footer>
</template>

<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const brand = computed(() => usePage().props.brand ?? {
    name: 'Mi Ropa',
    domain: 'miropa.com',
    tagline: 'Compra y vende con confianza.',
    logo_url: null,
});

const year = new Date().getFullYear();
</script>
