<template>
    <AppLayout title="Inicio">
        <!-- Hero -->
        <section class="relative overflow-hidden bg-ink text-white">
            <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top_right,_rgba(194,65,12,0.25),_transparent_50%)]" aria-hidden="true" />
            <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_bottom_left,_rgba(13,148,136,0.15),_transparent_50%)]" aria-hidden="true" />

            <div class="container-app relative py-20 md:py-28 lg:py-32">
                <div class="mx-auto max-w-3xl text-center">
                    <p class="mb-4 inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-4 py-1.5 text-sm font-medium text-zinc-300 backdrop-blur-sm">
                        <span class="h-2 w-2 rounded-full bg-trust" aria-hidden="true" />
                        Marketplace de moda de segunda mano
                    </p>

                    <h1 class="text-display text-white">
                        Tu ropa, nueva vida
                    </h1>

                    <p class="mx-auto mt-6 max-w-xl text-lg leading-relaxed text-zinc-300 md:text-xl">
                        Compra y vende en {{ brand.domain }} con vendedores verificados, chat directo y una experiencia pensada para ti.
                    </p>

                    <form
                        action="/anuncios"
                        method="get"
                        class="mx-auto mt-10 flex max-w-xl flex-col gap-3 sm:flex-row"
                        role="search"
                        aria-label="Buscar anuncios"
                    >
                        <label for="hero-search" class="sr-only">¿Qué estás buscando?</label>
                        <input
                            id="hero-search"
                            type="search"
                            name="q"
                            placeholder="Busca ropa, zapatos, accesorios..."
                            class="input-search flex-1 border-white/10 bg-white/95 text-ink shadow-lg backdrop-blur-sm"
                        />
                        <button type="submit" class="btn-primary shrink-0 px-8">
                            Buscar
                        </button>
                    </form>

                    <div class="mt-10 flex flex-wrap items-center justify-center gap-4">
                        <Link href="/anuncios" class="btn-secondary border-white/20 bg-white/10 text-white hover:bg-white/20">
                            Explorar anuncios
                        </Link>
                        <Link href="/listings/create" class="text-sm font-semibold text-zinc-300 underline-offset-4 transition hover:text-white hover:underline">
                            Vender ahora →
                        </Link>
                    </div>
                </div>

                <!-- Stats -->
                <dl class="mx-auto mt-16 grid max-w-2xl grid-cols-2 gap-6 md:grid-cols-2 md:gap-8">
                    <div class="rounded-2xl border border-white/10 bg-white/5 px-6 py-5 text-center backdrop-blur-sm">
                        <dt class="text-sm font-medium text-zinc-400">Anuncios activos</dt>
                        <dd class="mt-1 text-3xl font-bold tabular-nums">{{ stats.active_listings }}</dd>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-white/5 px-6 py-5 text-center backdrop-blur-sm">
                        <dt class="text-sm font-medium text-zinc-400">Categorías</dt>
                        <dd class="mt-1 text-3xl font-bold tabular-nums">{{ stats.categories }}</dd>
                    </div>
                </dl>
            </div>
        </section>

        <TrustBar />

        <!-- Categorías -->
        <section class="section-padding bg-surface" aria-labelledby="categories-heading">
            <div class="container-app">
                <SectionHeader
                    heading-id="categories-heading"
                    eyebrow="Descubre"
                    title="Explorar por categoría"
                    subtitle="Encuentra lo que buscas navegando por nuestras categorías más populares."
                    href="/anuncios"
                    link-label="Ver todas"
                    class="mb-10 md:mb-14"
                />
                <div class="category-grid">
                    <CategoryCard
                        v-for="category in categories"
                        :key="category.name"
                        :name="category.name"
                        :image="category.image"
                        :href="category.url"
                    />
                </div>
            </div>
        </section>

        <!-- Destacados -->
        <section
            v-if="featuredListings.length"
            class="section-padding bg-surface-muted"
            aria-labelledby="featured-heading"
        >
            <div class="container-app">
                <SectionHeader
                    eyebrow="Lo más visto"
                    title="Anuncios destacados"
                    subtitle="Las publicaciones con más interés esta semana."
                    href="/anuncios?sort=popular"
                    link-label="Ver populares"
                    class="mb-10 md:mb-14"
                />
                <div class="grid grid-cols-2 gap-4 md:grid-cols-4 md:gap-6">
                    <ListingCard
                        v-for="listing in featuredListings"
                        :key="listing.id"
                        :listing="listing"
                        featured
                    />
                </div>
            </div>
        </section>

        <!-- Recientes -->
        <section class="section-padding bg-surface" aria-labelledby="recent-heading">
            <div class="container-app">
                <SectionHeader
                    eyebrow="Novedades"
                    title="Publicaciones recientes"
                    subtitle="Lo último que acaban de publicar nuestros vendedores."
                    href="/anuncios"
                    link-label="Ver más"
                    class="mb-10 md:mb-14"
                />

                <div v-if="recentListings.length" class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4 lg:gap-6">
                    <ListingCard
                        v-for="listing in recentListings"
                        :key="listing.id"
                        :listing="listing"
                    />
                </div>

                <div
                    v-else
                    class="rounded-[var(--radius-card)] border border-dashed border-zinc-200 bg-surface-raised px-8 py-16 text-center"
                >
                    <p class="text-lg font-semibold text-ink">Aún no hay publicaciones</p>
                    <p class="mt-2 text-ink-secondary">Sé el primero en publicar y dale vida a tu guardarropa.</p>
                    <Link href="/listings/create" class="btn-primary mt-6 inline-flex">
                        Publicar anuncio
                    </Link>
                </div>
            </div>
        </section>

        <!-- Vendedores -->
        <section
            v-if="featuredSellers.length"
            class="section-padding bg-surface-muted"
            aria-labelledby="sellers-heading"
        >
            <div class="container-app">
                <SectionHeader
                    eyebrow="Comunidad"
                    title="Vendedores destacados"
                    subtitle="Perfiles activos con publicaciones verificadas y buena reputación."
                    class="mb-10 md:mb-14"
                />
                <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-6 lg:gap-6">
                    <SellerCard
                        v-for="seller in featuredSellers"
                        :key="seller.id"
                        :seller="seller"
                    />
                </div>
            </div>
        </section>

        <!-- CTA vendedores -->
        <section class="section-padding bg-surface" aria-labelledby="cta-heading">
            <div class="container-app">
                <div class="relative overflow-hidden rounded-[var(--radius-card)] bg-gradient-to-br from-accent to-accent-hover px-8 py-16 text-center text-white shadow-[var(--shadow-elevated)] md:px-16 md:py-20">
                    <div class="absolute -right-16 -top-16 h-64 w-64 rounded-full bg-white/10 blur-3xl" aria-hidden="true" />
                    <div class="absolute -bottom-16 -left-16 h-64 w-64 rounded-full bg-black/10 blur-3xl" aria-hidden="true" />

                    <h2 id="cta-heading" class="relative text-3xl font-bold tracking-tight md:text-4xl">
                        ¿Tienes ropa que ya no usas?
                    </h2>
                    <p class="relative mx-auto mt-4 max-w-lg text-lg text-orange-100">
                        Publica en minutos, llega a compradores verificados y convierte tu clóset en ingresos.
                    </p>
                    <div class="relative mt-8 flex flex-wrap items-center justify-center gap-4">
                        <Link href="/register" class="inline-flex items-center rounded-full bg-white px-8 py-3.5 text-sm font-semibold text-accent transition hover:bg-orange-50">
                            Crear cuenta gratis
                        </Link>
                        <Link href="/listings/create" class="text-sm font-semibold text-white underline-offset-4 hover:underline">
                            Ya tengo cuenta →
                        </Link>
                    </div>
                </div>
            </div>
        </section>
    </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import CategoryCard from '@/Components/CategoryCard.vue';
import ListingCard from '@/Components/ListingCard.vue';
import SectionHeader from '@/Components/SectionHeader.vue';
import SellerCard from '@/Components/SellerCard.vue';
import TrustBar from '@/Components/TrustBar.vue';
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

defineProps({
    categories: { type: Array, default: () => [] },
    recentListings: { type: Array, default: () => [] },
    featuredListings: { type: Array, default: () => [] },
    featuredSellers: { type: Array, default: () => [] },
    stats: {
        type: Object,
        default: () => ({ active_listings: 0, categories: 0 }),
    },
});

const brand = computed(() => usePage().props.brand ?? { domain: 'miropa.com' });
</script>

<style scoped>
.category-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 1rem;
}

@media (min-width: 640px) {
    .category-grid {
        gap: 1.25rem;
    }
}

@media (min-width: 768px) {
    .category-grid {
        grid-template-columns: repeat(4, minmax(0, 1fr));
    }
}

@media (min-width: 1280px) {
    .category-grid {
        grid-template-columns: repeat(6, minmax(0, 1fr));
    }
}
</style>
