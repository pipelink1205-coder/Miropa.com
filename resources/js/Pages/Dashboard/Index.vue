<template>
    <AppLayout title="Mi panel">
        <div class="max-w-7xl mx-auto px-4 py-10">
            <!-- Header -->
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Mi panel</h1>
                    <p class="text-gray-500 text-sm mt-1">Bienvenido, {{ user.name }}</p>
                </div>
                <Link
                    href="/listings/create"
                    class="bg-accent text-white px-5 py-2.5 rounded-full font-semibold text-sm hover:bg-accent-hover transition"
                >
                    + Publicar anuncio
                </Link>
            </div>

            <!-- Confianza y reputación -->
            <div class="bg-white rounded-xl border border-gray-100 p-5 mb-8">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="text-sm font-bold text-gray-800">Tu confianza y reputación</h2>
                    <Link href="/cuenta" class="text-xs text-accent hover:underline">Ver detalle →</Link>
                </div>
                <VerificationBadges
                    :email="trust.email_verified"
                    :phone="trust.phone_verified"
                    :identity="trust.identity_verified"
                />
                <div class="flex items-center gap-4 mt-3 text-sm text-gray-500">
                    <RatingStars v-if="trust.ratings_count > 0" :rating="trust.rating_avg" :count="trust.ratings_count" show-count />
                    <span v-else class="text-xs">Sin reseñas aún</span>
                    <span>{{ trust.sales_count }} ventas</span>
                </div>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-10">
                <div class="bg-white rounded-xl border border-gray-100 p-4 text-center">
                    <p class="text-3xl font-bold text-accent">{{ user.profile?.sales_count ?? 0 }}</p>
                    <p class="text-xs text-gray-500 mt-1">Ventas</p>
                </div>
                <div class="bg-white rounded-xl border border-gray-100 p-4 text-center">
                    <p class="text-3xl font-bold text-accent">{{ user.profile?.purchases_count ?? 0 }}</p>
                    <p class="text-xs text-gray-500 mt-1">Compras</p>
                </div>
                <div class="bg-white rounded-xl border border-gray-100 p-4 text-center">
                    <p class="text-3xl font-bold text-yellow-500">{{ user.profile?.rating_avg ?? '—' }}</p>
                    <p class="text-xs text-gray-500 mt-1">Reputación</p>
                </div>
                <div class="bg-white rounded-xl border border-gray-100 p-4 text-center">
                    <p class="text-3xl font-bold text-gray-700">{{ listings.total }}</p>
                    <p class="text-xs text-gray-500 mt-1">Anuncios</p>
                </div>
            </div>

            <!-- Trueques -->
            <div v-if="$page.props.features?.trade_enabled" class="mb-10 rounded-xl border border-violet-100 bg-violet-50/40 p-5">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="text-sm font-bold text-violet-900">Trueques presenciales</h2>
                        <p class="mt-1 text-xs text-violet-700">
                            Intercambia prendas con usuarios verificados. Ana y Bruno de demo ya están listos para probar.
                        </p>
                    </div>
                    <Link
                        href="/trueques"
                        class="inline-flex items-center justify-center rounded-full bg-violet-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-violet-700"
                    >
                        Ver trueques
                        <span
                            v-if="$page.props.pending_trade_offers_count > 0"
                            class="ml-2 rounded-full bg-white/20 px-2 py-0.5 text-xs"
                        >
                            {{ $page.props.pending_trade_offers_count }} pendiente(s)
                        </span>
                    </Link>
                </div>
            </div>

            <!-- Mis anuncios -->
            <h2 class="text-lg font-bold text-gray-800 mb-4">Mis anuncios</h2>

            <div v-if="listings.data.length === 0" class="text-center py-16 text-gray-400">
                <p class="text-5xl mb-3">📦</p>
                <p class="font-medium">Todavía no tienes anuncios</p>
                <Link href="/listings/create" class="text-accent text-sm mt-2 block hover:underline">Publica tu primer artículo</Link>
            </div>

            <div v-else class="space-y-3">
                <div
                    v-for="listing in listings.data"
                    :key="listing.id"
                    class="bg-white rounded-xl border border-gray-100 p-4 flex items-center gap-4"
                >
                    <div class="w-16 h-16 rounded-lg bg-gray-100 overflow-hidden shrink-0">
                        <img
                            v-if="listingImageUrl(listing)"
                            :src="listingImageUrl(listing)"
                            class="w-full h-full object-cover"
                        />
                        <div v-else class="w-full h-full flex items-center justify-center text-gray-300 text-xl">📷</div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-medium text-gray-800 truncate">{{ listing.title }}</p>
                        <p class="text-accent font-bold text-sm">{{ listing.price_formatted }}</p>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        <span class="text-xs px-2 py-1 rounded-full font-medium" :class="statusClass(listing.status)">
                            {{ statusLabel(listing.status) }}
                        </span>
                        <Link :href="`/listings/${listing.id}/edit`" class="text-xs text-gray-500 hover:text-accent">Editar</Link>
                    </div>
                </div>
            </div>

            <!-- Paginación básica -->
            <div v-if="listings.last_page > 1" class="flex justify-center gap-2 mt-6">
                <Link
                    v-for="page in listings.last_page"
                    :key="page"
                    :href="`/dashboard?page=${page}`"
                    class="px-3 py-1 rounded-lg text-sm border"
                    :class="page === listings.current_page ? 'bg-accent text-white border-accent' : 'text-gray-600 hover:bg-gray-50'"
                >
                    {{ page }}
                </Link>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import RatingStars from '@/Components/RatingStars.vue';
import VerificationBadges from '@/Components/VerificationBadges.vue';
import { Link } from '@inertiajs/vue3';

defineProps({
    user:     Object,
    trust:    Object,
    listings: Object,
});

function statusClass(status) {
    return {
        active:   'bg-green-100 text-green-700',
        draft:    'bg-gray-100 text-gray-600',
        reserved: 'bg-yellow-100 text-yellow-700',
        sold:     'bg-blue-100 text-blue-700',
        paused:   'bg-orange-100 text-orange-700',
    }[status] ?? 'bg-gray-100 text-gray-600';
}

function statusLabel(status) {
    return {
        active:   'Activo',
        draft:    'Borrador',
        reserved: 'Reservado',
        sold:     'Vendido',
        paused:   'Pausado',
    }[status] ?? status;
}

function listingImageUrl(listing) {
    if (typeof listing.primary_image === 'string') {
        return listing.primary_image;
    }

    return listing.primary_image?.url
        ?? listing.images?.[0]?.url
        ?? null;
}
</script>
