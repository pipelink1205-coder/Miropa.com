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
                                :class="activeImage === img.url ? 'border-indigo-500' : 'border-transparent'"
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

                    <!-- Atributos -->
                    <div v-if="listing.attributes && Object.keys(listing.attributes).length" class="bg-white rounded-2xl border border-gray-100 p-6">
                        <h2 class="font-bold text-gray-800 mb-3">Características</h2>
                        <div class="grid grid-cols-2 gap-3">
                            <div v-for="(value, key) in listing.attributes" :key="key" class="text-sm">
                                <span class="text-gray-500">{{ key }}</span>
                                <span class="block font-medium text-gray-800">{{ value }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Panel derecho -->
                <div class="lg:col-span-1 space-y-4">
                    <!-- Precio y acciones -->
                    <div class="bg-white rounded-2xl border border-gray-100 p-6 sticky top-24">
                        <div class="mb-4">
                            <h1 class="text-xl font-bold text-gray-900 mb-1">{{ listing.title }}</h1>
                            <div class="flex items-center gap-2">
                                <span class="text-3xl font-bold text-indigo-600">{{ listing.price_formatted }}</span>
                                <span v-if="listing.is_negotiable" class="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded-full font-medium">Negociable</span>
                            </div>
                            <div class="flex items-center gap-3 mt-2 text-xs text-gray-400">
                                <span v-if="listing.location">📍 {{ listing.location.city }}</span>
                                <span>{{ listing.published_at }}</span>
                                <span>👁 {{ listing.views_count }}</span>
                            </div>
                        </div>

                        <div class="flex gap-2 mb-4">
                            <span class="text-xs px-2 py-1 rounded-full font-medium bg-gray-100 text-gray-600">
                                {{ listing.condition?.name }}
                            </span>
                            <span class="text-xs px-2 py-1 rounded-full font-medium bg-indigo-50 text-indigo-600">
                                {{ listing.category?.name }}
                            </span>
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
                                    class="block w-full text-center bg-indigo-600 text-white py-3 rounded-xl font-semibold hover:bg-indigo-700 transition mb-2"
                                >
                                    Verifica tu email para contactar
                                </Link>
                            </template>
                            <template v-else-if="contact?.needs_phone">
                                <Link
                                    href="/telefono/verificar"
                                    class="block w-full text-center bg-indigo-600 text-white py-3 rounded-xl font-semibold hover:bg-indigo-700 transition mb-2"
                                >
                                    Verifica tu celular para contactar
                                </Link>
                            </template>
                            <template v-else>
                                <Link
                                    v-if="contact?.conversation_id"
                                    :href="`/mensajes/${contact.conversation_id}`"
                                    class="block w-full text-center bg-indigo-600 text-white py-3 rounded-xl font-semibold hover:bg-indigo-700 transition mb-2"
                                >
                                    Continuar conversación
                                </Link>
                                <button
                                    v-else
                                    type="button"
                                    @click="showContactModal = true"
                                    class="w-full bg-indigo-600 text-white py-3 rounded-xl font-semibold hover:bg-indigo-700 transition mb-2"
                                >
                                    Contactar vendedor
                                </button>
                            </template>
                            <button class="w-full border border-gray-200 py-3 rounded-xl font-semibold text-gray-700 hover:bg-gray-50 transition text-sm">
                                {{ listing.is_favorited ? '❤️ En favoritos' : '🤍 Agregar a favoritos' }}
                            </button>
                        </template>
                        <template v-else>
                            <Link href="/login" class="block w-full text-center bg-indigo-600 text-white py-3 rounded-xl font-semibold hover:bg-indigo-700 transition">
                                Inicia sesión para contactar
                            </Link>
                        </template>
                    </div>

                    <!-- Vendedor -->
                    <div v-if="listing.seller" class="bg-white rounded-2xl border border-gray-100 p-5">
                        <h3 class="font-bold text-gray-800 mb-3 text-sm">Vendedor</h3>
                        <Link :href="`/u/${listing.seller.username}`" class="flex items-center gap-3 hover:opacity-80">
                            <div class="w-11 h-11 rounded-full bg-indigo-100 flex items-center justify-center font-bold text-indigo-700">
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
                        class="w-full border rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-400 focus:outline-none resize-none"
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
                            class="flex-1 bg-indigo-600 text-white py-2.5 rounded-xl text-sm font-semibold hover:bg-indigo-700 disabled:opacity-50"
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
import SellerTrust from '@/Components/SellerTrust.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    listing:         Object,
    relatedListings: Array,
    contact:         Object,
});

const showContactModal = ref(false);

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
