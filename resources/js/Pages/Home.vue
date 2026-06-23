<template>
    <AppLayout title="Inicio">
        <!-- Hero compacto — visible above the fold on 14" laptops -->
        <section class="relative overflow-hidden bg-ink text-white">
            <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top_right,_rgba(194,65,12,0.25),_transparent_50%)]" aria-hidden="true" />
            <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_bottom_left,_rgba(13,148,136,0.15),_transparent_50%)]" aria-hidden="true" />

            <div class="container-app relative py-8 md:py-10 lg:py-11">
                <div class="home-hero">
                    <div class="home-hero__intro">
                        <p class="mb-2 inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-3 py-1 text-xs font-medium text-zinc-300 backdrop-blur-sm sm:text-sm">
                            ✨ Moda con historia
                        </p>

                        <h1 class="home-hero__title">
                            Tu ropa, nueva vida
                        </h1>

                        <p class="home-hero__subtitle">
                            Compra y vende ropa, calzado y accesorios con vendedores verificados y chat directo.
                        </p>
                    </div>

                    <div class="home-hero__actions">
                        <form
                            action="/anuncios"
                            method="get"
                            class="flex flex-col gap-2.5 sm:flex-row"
                            role="search"
                            aria-label="Buscar anuncios"
                        >
                            <label for="hero-search" class="sr-only">¿Qué estás buscando?</label>
                            <input
                                id="hero-search"
                                type="search"
                                name="q"
                                placeholder="Busca ropa, zapatos, accesorios..."
                                class="input-search flex-1 border-white/10 bg-white/95 py-2.5 text-ink shadow-lg backdrop-blur-sm"
                            />
                            <button type="submit" class="btn-primary shrink-0 px-6 py-2.5">
                                Buscar
                            </button>
                        </form>

                        <div class="flex flex-wrap items-center gap-3">
                            <Link href="/anuncios" class="btn-secondary border-white/20 bg-white/10 px-5 py-2.5 text-white hover:bg-white/20">
                                Explorar anuncios
                            </Link>
                            <Link href="/listings/create" class="text-sm font-semibold text-zinc-300 underline-offset-4 transition hover:text-white hover:underline">
                                Vender ahora →
                            </Link>
                        </div>

                        <dl class="home-hero__stats">
                            <div class="home-hero__stat">
                                <dt class="text-xs font-medium text-zinc-400">Anuncios activos</dt>
                                <dd class="mt-0.5 text-xl font-bold tabular-nums sm:text-2xl">{{ stats.active_listings }}</dd>
                            </div>
                            <div class="home-hero__stat">
                                <dt class="text-xs font-medium text-zinc-400">Categorías</dt>
                                <dd class="mt-0.5 text-xl font-bold tabular-nums sm:text-2xl">{{ stats.categories }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>
        </section>

        <!-- Moda — experiencia visual -->
        <section id="moda" class="home-section bg-surface" aria-labelledby="categories-heading">
            <div class="container-app">
                <SectionHeader
                    heading-id="categories-heading"
                    eyebrow="Segunda mano"
                    title="Moda"
                    subtitle="Ropa, calzado y accesorios de gente real — encuentra lo tuyo sin complicarte."
                    href="/anuncios"
                    link-label="Ver toda la moda"
                    class="mb-6 md:mb-8"
                />
                <div class="visual-category-grid visual-category-grid--fashion">
                    <VisualCategoryCard
                        v-for="category in fashionCategories"
                        :key="category.department"
                        :title="category.title"
                        :subtitle="category.subtitle"
                        :image="category.image"
                        :href="category.url"
                    />
                </div>

                <!-- Universos (eje transversal) -->
                <div v-if="fashionUniverses.length" class="mt-8 md:mt-10">
                    <h3 class="text-sm font-semibold uppercase tracking-wider text-accent">Universos</h3>
                    <p class="mt-1 text-sm text-ink-secondary">Descubre por intención, no solo por categoría.</p>
                    <div class="mt-4 flex flex-wrap gap-2">
                        <UniverseChip
                            v-for="universe in fashionUniverses"
                            :key="universe.slug"
                            :universe="universe"
                        />
                    </div>
                </div>
            </div>
        </section>

        <TrustBar />

        <!-- Otras categorías del marketplace -->
        <section
            v-if="otherCategories.length"
            class="home-section border-t border-zinc-100 bg-surface-muted/50"
            aria-labelledby="other-categories-heading"
        >
            <div class="container-app">
                <SectionHeader
                    heading-id="other-categories-heading"
                    eyebrow="Marketplace"
                    title="Otras categorías"
                    subtitle="Electrónica, hogar, vehículos y más — solo contacto por chat."
                    href="/anuncios"
                    link-label="Ver todos"
                    class="mb-6 md:mb-8"
                />
                <div class="visual-category-grid visual-category-grid--marketplace">
                    <VisualCategoryCard
                        v-for="category in otherCategories"
                        :key="category.search_category"
                        :title="category.title"
                        :subtitle="category.subtitle"
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
                    subtitle="Prendas y accesorios recién publicados por nuestra comunidad."
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
import ListingCard from '@/Components/ListingCard.vue';
import SectionHeader from '@/Components/SectionHeader.vue';
import SellerCard from '@/Components/SellerCard.vue';
import TrustBar from '@/Components/TrustBar.vue';
import UniverseChip from '@/Components/Fashion/UniverseChip.vue';
import VisualCategoryCard from '@/Components/VisualCategoryCard.vue';
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

defineProps({
    fashionCategories: { type: Array, default: () => [] },
    otherCategories: { type: Array, default: () => [] },
    fashionUniverses: { type: Array, default: () => [] },
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
.home-hero {
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
}

.home-hero__intro {
    text-align: center;
}

.home-hero__title {
    font-size: clamp(1.75rem, 4vw, 2.5rem);
    font-weight: 700;
    line-height: 1.15;
    letter-spacing: -0.02em;
    color: #fff;
}

.home-hero__subtitle {
    margin-top: 0.625rem;
    margin-left: auto;
    margin-right: auto;
    max-width: 32rem;
    font-size: 0.9375rem;
    line-height: 1.5;
    color: rgb(212 212 216);
}

.home-hero__actions {
    display: flex;
    flex-direction: column;
    gap: 0.875rem;
    max-width: 32rem;
    margin-left: auto;
    margin-right: auto;
    width: 100%;
}

.home-hero__stats {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 0.625rem;
}

.home-hero__stat {
    border: 1px solid rgba(255, 255, 255, 0.1);
    background: rgba(255, 255, 255, 0.05);
    padding: 0.625rem 0.875rem;
    text-align: center;
    backdrop-filter: blur(4px);
    border-radius: 0.75rem;
}

.home-section {
    padding-top: 2rem;
    padding-bottom: 2rem;
}

@media (min-width: 768px) {
    .home-section {
        padding-top: 2.5rem;
        padding-bottom: 2.5rem;
    }
}

@media (min-width: 1024px) {
    .home-hero {
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(0, 22rem);
        align-items: center;
        gap: 2rem 3rem;
    }

    .home-hero__intro {
        text-align: left;
    }

    .home-hero__subtitle {
        margin-left: 0;
        margin-right: 0;
        font-size: 1rem;
    }

    .home-hero__actions {
        margin-left: 0;
        margin-right: 0;
        max-width: none;
    }
}

.visual-category-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 0.875rem;
}

@media (min-width: 640px) {
    .visual-category-grid {
        gap: 1rem;
    }

    .visual-category-grid--fashion {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }

    .visual-category-grid--marketplace {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }
}

@media (min-width: 1024px) {
    .visual-category-grid--fashion {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }

    .visual-category-grid--marketplace {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }
}
</style>
