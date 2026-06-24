<template>
    <AppLayout :title="`Perfil de ${profileUser.name}`">
        <div class="max-w-7xl mx-auto px-4 py-10">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

                <!-- Panel lateral: info del vendedor -->
                <aside class="lg:col-span-1">
                    <div class="bg-white rounded-2xl border border-gray-100 p-6 sticky top-24">
                        <!-- Avatar -->
                        <div class="flex flex-col items-center text-center">
                            <div class="w-20 h-20 rounded-full bg-accent-soft flex items-center justify-center text-3xl font-bold text-accent mb-3">
                                {{ profileUser.name.charAt(0).toUpperCase() }}
                            </div>
                            <h1 class="text-xl font-bold text-gray-900">{{ profileUser.name }}</h1>
                            <p class="text-gray-500 text-sm">@{{ profileUser.username }}</p>

                            <div class="flex justify-center mt-3">
                                <VerificationBadges
                                    :email="trust.email_verified"
                                    :phone="trust.phone_verified"
                                    :identity="trust.identity_verified"
                                />
                            </div>
                        </div>

                        <!-- Stats -->
                        <div class="mt-6 space-y-3 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-500">Reputación</span>
                                <RatingStars :rating="trust.rating_avg" :count="trust.ratings_count" show-count />
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Ventas</span>
                                <span class="font-medium">{{ trust.sales_count }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Miembro desde</span>
                                <span class="font-medium">{{ memberSince }}</span>
                            </div>
                            <div v-if="profileUser.profile?.location" class="flex justify-between">
                                <span class="text-gray-500">Ubicación</span>
                                <span class="font-medium">{{ profileUser.profile.location.city }}</span>
                            </div>
                        </div>

                        <div v-if="profileUser.bio" class="mt-4 text-sm text-gray-600 text-center italic border-t pt-4">
                            "{{ profileUser.bio }}"
                        </div>
                    </div>
                </aside>

                <!-- Contenido principal -->
                <div class="lg:col-span-2 space-y-8">
                    <!-- Anuncios -->
                    <section>
                        <h2 class="text-lg font-bold text-gray-800 mb-4">
                            Anuncios activos
                            <span class="text-gray-400 font-normal text-sm">({{ listings.total }})</span>
                        </h2>

                        <div v-if="listings.data.length === 0" class="text-center py-10 text-gray-400">
                            <p>Este usuario no tiene anuncios activos.</p>
                        </div>

                        <div v-else class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                            <ListingCard
                                v-for="listing in listings.data"
                                :key="listing.id"
                                :listing="listing"
                            />
                        </div>
                    </section>

                    <!-- Reseñas -->
                    <section>
                        <h2 class="text-lg font-bold text-gray-800 mb-4">
                            Reseñas
                            <span class="text-gray-400 font-normal text-sm">({{ reviews.total }})</span>
                        </h2>

                        <div v-if="reviews.data.length === 0" class="text-center py-10 text-gray-400">
                            <p>Todavía no hay reseñas.</p>
                        </div>

                        <div v-else class="space-y-4">
                            <div
                                v-for="review in reviews.data"
                                :key="review.id"
                                class="bg-white rounded-xl border border-gray-100 p-4"
                            >
                                <div class="flex items-start gap-3">
                                    <div class="w-9 h-9 rounded-full bg-gray-100 flex items-center justify-center font-bold text-gray-600 text-sm shrink-0">
                                        {{ review.reviewer.name.charAt(0).toUpperCase() }}
                                    </div>
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <span class="font-medium text-sm">{{ review.reviewer.name }}</span>
                                            <span class="text-xs text-gray-400">como {{ review.role === 'buyer' ? 'comprador' : 'vendedor' }}</span>
                                            <RatingStars :rating="review.rating" />
                                        </div>
                                        <p v-if="review.comment" class="text-sm text-gray-600 mt-1">{{ review.comment }}</p>
                                        <p class="text-xs text-gray-400 mt-1">{{ review.created_at }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import ListingCard from '@/Components/ListingCard.vue';
import RatingStars from '@/Components/RatingStars.vue';
import VerificationBadges from '@/Components/VerificationBadges.vue';
import { computed } from 'vue';

const props = defineProps({
    profileUser: Object,
    trust:       Object,
    listings:    Object,
    reviews:     Object,
});

const memberSince = computed(() => {
    if (!props.profileUser.profile?.member_since) return '—';
    return new Date(props.profileUser.profile.member_since).toLocaleDateString('es-CO', {
        year: 'month', month: 'long', year: 'numeric',
    });
});
</script>
