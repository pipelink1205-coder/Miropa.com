<template>
    <Head>
        <meta name="description" :content="`${listing.title} — ${listing.price_formatted} — ${listing.condition?.name ?? ''}`" />
    </Head>
    <AppLayout :title="listing.title">
        <div class="max-w-7xl mx-auto px-4 py-10">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

                <!-- Galería + info -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Galería -->
                    <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
                        <div class="aspect-[4/3] bg-gray-100">
                            <img
                                v-if="activeImage"
                                :src="activeImage"
                                :alt="listing.title"
                                class="w-full h-full object-contain"
                            />
                            <div v-else class="w-full h-full flex items-center justify-center text-gray-300 text-6xl">📷</div>
                        </div>
                        <div v-if="listing.images?.length > 1" class="flex gap-2 p-3 overflow-x-auto">
                            <button
                                v-for="img in listing.images"
                                :key="img.id"
                                @click="activeImage = img.url"
                                class="w-16 h-16 rounded-lg overflow-hidden border-2 shrink-0"
                                :class="activeImage === img.url ? 'border-accent' : 'border-transparent'"
                            >
                                <img :src="img.url" class="w-full h-full object-cover" />
                            </button>
                        </div>
                    </div>

                    <!-- Descripción -->
                    <div class="bg-white rounded-2xl border border-gray-100 p-6">
                        <h2 class="font-bold text-gray-800 mb-3">Descripción</h2>
                        <p class="text-gray-600 text-sm whitespace-pre-line">{{ listing.description }}</p>
                    </div>

                    <!-- Características / detalles Moda -->
                    <div v-if="listing.detail_rows?.length" class="rounded-2xl border border-zinc-200 bg-surface-raised p-6">
                        <h2 class="mb-3 font-bold text-ink">Características</h2>
                        <dl class="grid grid-cols-2 gap-x-4 gap-y-3">
                            <div v-for="row in listing.detail_rows" :key="row.label" class="text-sm">
                                <dt class="text-ink-secondary">{{ row.label }}</dt>
                                <dd class="mt-0.5 font-medium text-ink">{{ row.value }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <!-- Panel derecho -->
                <div class="lg:col-span-1 space-y-4">
                    <!-- Precio y acciones -->
                    <div class="bg-white rounded-2xl border border-gray-100 p-6 sticky top-24">
                        <div class="mb-4">
                            <h1 class="text-xl font-bold text-gray-900 mb-1">{{ listing.title }}</h1>
                            <div class="flex items-center gap-2">
                                <span class="text-3xl font-bold text-accent">{{ listing.price_formatted }}</span>
                                <span v-if="listing.is_negotiable" class="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded-full font-medium">Negociable</span>
                            </div>
                            <div class="flex items-center gap-3 mt-2 text-xs text-gray-400">
                                <span v-if="listing.location">📍 {{ listing.location.city }}</span>
                                <span>{{ listing.published_at }}</span>
                                <span>👁 {{ listing.views_count }}</span>
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center gap-2 mb-4">
                            <SaleModeBadge :sale-mode="listing.category?.sale_mode ?? 'marketplace'" />
                            <span class="rounded-full bg-surface-muted px-2 py-1 text-xs font-medium text-ink-secondary">
                                {{ listing.condition?.name }}
                            </span>
                            <span class="rounded-full bg-accent-soft px-2 py-1 text-xs font-medium text-accent">
                                {{ listing.category?.name }}
                            </span>
                            <span v-if="listing.size" class="rounded-full bg-surface-muted px-2 py-1 text-xs font-medium text-ink">
                                Talla {{ listing.size }}
                            </span>
                            <span v-if="listing.color" class="rounded-full bg-surface-muted px-2 py-1 text-xs font-medium text-ink">
                                {{ listing.color }}
                            </span>
                        </div>

                        <p
                            v-if="listing.second_life_impact?.label"
                            class="mb-4 rounded-xl border border-trust/20 bg-trust/5 px-4 py-3 text-sm text-trust"
                        >
                            ♻ {{ listing.second_life_impact.label }}
                        </p>

                        <div
                            v-if="isClassified"
                            class="mb-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900"
                            role="status"
                        >
                            <strong class="font-semibold">Trato directo</strong> — esta venta se coordina por fuera de la app. Usa el chat para acordar detalles con el vendedor.
                        </div>

                        <div v-if="$page.props.flash?.error" class="mb-4 text-sm text-red-700 bg-red-50 border border-red-100 rounded-lg px-4 py-3">
                            {{ $page.props.flash.error }}
                        </div>

                        <template v-if="$page.props.auth?.user">
                            <template v-if="contact?.is_owner">
                                <p class="text-sm text-gray-500 text-center mb-2">Este es tu anuncio</p>
                                <Link
                                    href="/listings/create"
                                    class="block w-full text-center border border-gray-200 py-3 rounded-xl font-semibold text-gray-700 hover:bg-gray-50 transition text-sm mb-2"
                                >
                                    Publicar otro artículo
                                </Link>
                            </template>
                            <template v-else-if="contact?.needs_email">
                                <Link
                                    href="/email/verify"
                                    class="block w-full text-center bg-accent text-white py-3 rounded-xl font-semibold hover:bg-accent-hover transition mb-2"
                                >
                                    Verifica tu email para contactar
                                </Link>
                            </template>
                            <template v-else-if="contact?.needs_phone">
                                <Link
                                    href="/telefono/verificar"
                                    class="block w-full text-center bg-accent text-white py-3 rounded-xl font-semibold hover:bg-accent-hover transition mb-2"
                                >
                                    Verifica tu celular para contactar
                                </Link>
                            </template>
                            <template v-else>
                                <button
                                    v-if="contact?.can_purchase && $page.props.features?.checkout_enabled"
                                    type="button"
                                    class="w-full bg-accent text-white py-3 rounded-xl font-semibold mb-2 hover:bg-accent-hover transition"
                                >
                                    Comprar con Mi Ropa
                                </button>
                                <p
                                    v-else-if="contact?.can_purchase"
                                    class="mb-2 rounded-xl border border-zinc-200 bg-surface-muted px-4 py-3 text-center text-sm text-ink-secondary"
                                >
                                    Compra protegida — próximamente. Coordina por chat mientras tanto.
                                </p>
                                <Link
                                    v-if="contact?.conversation_id"
                                    :href="`/mensajes/${contact.conversation_id}`"
                                    class="block w-full text-center bg-accent text-white py-3 rounded-xl font-semibold hover:bg-accent-hover transition mb-2"
                                >
                                    Continuar conversación
                                </Link>
                                <button
                                    v-else
                                    type="button"
                                    @click="showContactModal = true"
                                    class="w-full bg-accent text-white py-3 rounded-xl font-semibold hover:bg-accent-hover transition mb-2"
                                >
                                    Contactar vendedor
                                </button>
                            </template>
                            <button
                                type="button"
                                class="w-full border border-zinc-200 py-3 rounded-xl font-semibold text-ink transition text-sm hover:bg-surface-muted"
                                @click="toggleFavorite"
                            >
                                {{ isFavorited ? '❤️ En favoritos' : '🤍 Agregar a favoritos' }}
                            </button>
                        </template>
                        <template v-else>
                            <Link href="/login" class="block w-full text-center bg-accent text-white py-3 rounded-xl font-semibold hover:bg-accent-hover transition">
                                Inicia sesión para contactar
                            </Link>
                        </template>
                    </div>

                    <!-- Vendedor -->
                    <div v-if="listing.seller" class="bg-white rounded-2xl border border-gray-100 p-5">
                        <h3 class="font-bold text-gray-800 mb-3 text-sm">Vendedor</h3>
                        <Link :href="`/u/${listing.seller.username}`" class="flex items-center gap-3 hover:opacity-80">
                            <div class="w-11 h-11 rounded-full bg-accent-soft flex items-center justify-center font-bold text-accent">
                                {{ listing.seller.name.charAt(0).toUpperCase() }}
                            </div>
                            <div>
                                <p class="font-medium text-sm text-gray-900">{{ listing.seller.name }}</p>
                                <p class="text-xs text-gray-400">@{{ listing.seller.username }}</p>
                                <SellerTrust
                                    v-if="listing.seller?.trust"
                                    :trust="listing.seller.trust"
                                    compact
                                    class="mt-1"
                                />
                            </div>
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Relacionados -->
            <div v-if="relatedListings.length" class="mt-12">
                <h2 class="text-lg font-bold text-gray-800 mb-4">Artículos relacionados</h2>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    <ListingCard v-for="l in relatedListings" :key="l.id" :listing="l" />
                </div>
            </div>
        </div>

        <!-- Modal contactar vendedor -->
        <div
            v-if="showContactModal"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40"
            @click.self="showContactModal = false"
        >
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-1">Contactar vendedor</h3>
                <p class="text-sm text-gray-500 mb-4">
                    Escribe tu mensaje sobre <strong>{{ listing.title }}</strong>
                </p>
                <form @submit.prevent="submitContact">
                    <textarea
                        v-model="contactForm.body"
                        rows="4"
                        class="w-full border rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-accent/20 focus:outline-none resize-none"
                        placeholder="Hola, me interesa este artículo. ¿Sigue disponible?"
                    />
                    <p v-if="contactForm.errors.body" class="text-red-500 text-xs mt-1">{{ contactForm.errors.body }}</p>
                    <div class="flex gap-3 mt-4">
                        <button
                            type="button"
                            @click="showContactModal = false"
                            class="flex-1 border border-gray-200 py-2.5 rounded-xl text-sm font-semibold text-gray-600 hover:bg-gray-50"
                        >
                            Cancelar
                        </button>
                        <button
                            type="submit"
                            :disabled="contactForm.processing || !contactForm.body.trim()"
                            class="flex-1 bg-accent text-white py-2.5 rounded-xl text-sm font-semibold hover:bg-accent-hover disabled:opacity-50"
                        >
                            {{ contactForm.processing ? 'Enviando...' : 'Enviar mensaje' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import ListingCard from '@/Components/ListingCard.vue';
import SaleModeBadge from '@/Components/SaleModeBadge.vue';
import SellerTrust from '@/Components/SellerTrust.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

const props = defineProps({
    listing:         Object,
    relatedListings: Array,
    contact:         Object,
});

const isClassified = computed(
    () => props.listing.category?.sale_mode === 'classified',
);

const showContactModal = ref(false);
const isFavorited = ref(props.listing.is_favorited ?? false);

watch(() => props.listing.is_favorited, (value) => {
    isFavorited.value = value ?? false;
});

function toggleFavorite() {
    router.post(`/anuncios/${props.listing.slug}/favorito`, {}, {
        preserveScroll: true,
        onSuccess: () => {
            isFavorited.value = ! isFavorited.value;
        },
    });
}

const contactForm = useForm({
    body: 'Hola, me interesa este artículo. ¿Sigue disponible?',
});

function submitContact() {
    contactForm.post(`/anuncios/${props.listing.slug}/contactar`, {
        preserveScroll: true,
        onSuccess: () => { showContactModal.value = false; },
    });
}

watch(showContactModal, (open) => {
    if (open && !contactForm.body.trim()) {
        contactForm.body = 'Hola, me interesa este artículo. ¿Sigue disponible?';
    }
});

const activeImage = ref(
    props.listing.images?.find(i => i.is_primary)?.url
    ?? props.listing.images?.[0]?.url
    ?? null
);
</script>
