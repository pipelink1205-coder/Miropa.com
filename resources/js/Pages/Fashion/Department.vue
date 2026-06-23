<template>
    <AppLayout :title="pageTitle">
        <div class="container-app py-8">
            <!-- Breadcrumb -->
            <nav class="mb-4 text-sm text-ink-secondary" aria-label="Breadcrumb">
                <ol class="flex flex-wrap items-center gap-1.5">
                    <li v-for="(crumb, i) in breadcrumbs" :key="i" class="flex items-center gap-1.5">
                        <span v-if="i > 0" class="text-ink-muted">›</span>
                        <Link v-if="crumb.href" :href="crumb.href" class="hover:text-accent">{{ crumb.label }}</Link>
                        <span v-else class="font-medium text-ink">{{ crumb.label }}</span>
                    </li>
                </ol>
            </nav>

            <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-ink md:text-3xl">
                        Prendas para {{ department.name.toLowerCase() }}
                    </h1>
                    <p class="mt-1 text-sm text-ink-secondary">
                        <span class="font-semibold text-ink">{{ listings.total }}</span> resultados
                    </p>
                </div>
                <select
                    v-model="localFilters.sort"
                    class="input-search max-w-xs py-2"
                    @change="applyFilters"
                >
                    <option value="recent">Más recientes</option>
                    <option value="price_asc">Menor precio</option>
                    <option value="price_desc">Mayor precio</option>
                    <option value="popular">Más vistos</option>
                </select>
            </div>

            <!-- Niños: segmento (Bebé / Niña / Niño) -->
            <div v-if="segmentChips.length" class="mb-4 flex flex-wrap gap-2">
                <button
                    type="button"
                    class="rounded-full px-4 py-2 text-sm font-medium transition"
                    :class="!localFilters.segment ? 'bg-ink text-white' : 'bg-surface-muted text-ink-secondary hover:bg-zinc-200'"
                    @click="setSegment('')"
                >
                    Todo
                </button>
                <button
                    v-for="seg in segmentChips"
                    :key="seg.value"
                    type="button"
                    class="rounded-full px-4 py-2 text-sm font-medium transition"
                    :class="localFilters.segment === seg.value ? 'bg-accent text-white' : 'bg-surface-muted text-ink-secondary hover:bg-zinc-200'"
                    @click="setSegment(seg.value)"
                >
                    {{ seg.name }}
                </button>
            </div>

            <!-- Categorías (Ropa, Calzado, …) — en Niños solo tras elegir segmento -->
            <div v-if="navigationChips.length" class="mb-6 flex flex-wrap gap-2">
                <button
                    type="button"
                    class="rounded-full px-4 py-2 text-sm font-medium transition"
                    :class="!localFilters.categoria ? 'bg-ink text-white' : 'bg-surface-muted text-ink-secondary hover:bg-zinc-200'"
                    @click="clearCategoryFilter"
                >
                    Todo
                </button>
                <button
                    v-for="chip in navigationChips"
                    :key="chip.slug"
                    type="button"
                    class="rounded-full px-4 py-2 text-sm font-medium transition"
                    :class="localFilters.categoria === chip.slug ? 'bg-accent text-white' : 'bg-surface-muted text-ink-secondary hover:bg-zinc-200'"
                    @click="selectChip(chip)"
                >
                    {{ chip.name }}
                </button>
            </div>

            <p
                v-else-if="department.slug === 'ninos' && localFilters.segment"
                class="mb-6 text-sm text-ink-secondary"
            >
                No hay categorías para este segmento.
            </p>
            <p
                v-else-if="department.slug === 'ninos' && !localFilters.segment"
                class="mb-6 text-sm text-ink-secondary"
            >
                Elige Bebé, Niña o Niño para ver categorías de ropa, calzado y accesorios.
            </p>

            <!-- Tipos (subcategoría) -->
            <div v-if="tipos.length" class="mb-6 flex flex-wrap gap-2">
                <button
                    v-for="tipo in tipos"
                    :key="tipo.slug"
                    type="button"
                    class="rounded-full border border-zinc-200 px-3 py-1.5 text-xs font-medium transition hover:border-accent hover:text-accent"
                    :class="localFilters.tipo === tipo.slug ? 'border-accent bg-accent-soft text-accent' : 'text-ink-secondary'"
                    @click="setTipo(tipo.slug)"
                >
                    {{ tipo.name }}
                </button>
            </div>

            <div class="flex flex-col gap-6 lg:flex-row lg:gap-8">
                <!-- Sidebar filtros desktop -->
                <aside class="hidden w-60 shrink-0 lg:block">
                    <FashionFilters
                        v-model="localFilters"
                        :conditions="conditions"
                        :brands="brands"
                        :sizes="activeSizes"
                        :height-sizes="heightSizes"
                        :show-height-sizes="department.slug === 'ninos'"
                        :colors="colors"
                        :universes="universes"
                        @apply="applyFilters"
                        @clear="clearAllFilters"
                    />
                </aside>

                <div class="min-w-0 flex-1">
                    <!-- Mobile filtros -->
                    <div class="mb-4 lg:hidden">
                        <button type="button" class="btn-secondary w-full" @click="showMobileFilters = !showMobileFilters">
                            {{ showMobileFilters ? 'Ocultar filtros' : 'Filtros' }}
                        </button>
                        <div v-if="showMobileFilters" class="mt-4 rounded-xl border border-zinc-200 bg-surface-raised p-4">
                            <FashionFilters
                                v-model="localFilters"
                                :conditions="conditions"
                                :brands="brands"
                                :sizes="activeSizes"
                        :height-sizes="heightSizes"
                        :show-height-sizes="department.slug === 'ninos'"
                                :colors="colors"
                                :universes="universes"
                                @apply="applyFilters"
                                @clear="clearAllFilters"
                            />
                        </div>
                    </div>

                    <!-- Grid -->
                    <div v-if="listings.data.length === 0" class="rounded-xl border border-dashed border-zinc-200 py-16 text-center">
                        <p class="font-medium text-ink">No hay resultados</p>
                        <p class="mt-1 text-sm text-ink-secondary">Prueba otros filtros o explora otra categoría.</p>
                    </div>

                    <div v-else class="grid grid-cols-2 gap-4 md:grid-cols-3 xl:grid-cols-4">
                        <div v-for="listing in listings.data" :key="listing.id" class="relative">
                            <ListingCard :listing="listing" />
                            <p
                                v-if="listing.second_life_impact?.label"
                                class="mt-1.5 text-[10px] leading-tight text-trust"
                            >
                                ♻ {{ listing.second_life_impact.label }}
                            </p>
                            <p v-if="listing.condition?.name" class="mt-0.5 text-[10px] text-ink-muted">
                                {{ listing.condition.name }}
                            </p>
                        </div>
                    </div>

                    <div v-if="listings.last_page > 1" class="mt-8 flex justify-center gap-2">
                        <component
                            :is="listings.prev_page_url ? Link : 'span'"
                            :href="listings.prev_page_url"
                            class="btn-secondary px-4 py-2 text-sm"
                            :class="{ 'opacity-40 pointer-events-none': !listings.prev_page_url }"
                        >
                            ← Anterior
                        </component>
                        <span class="flex items-center px-3 text-sm text-ink-secondary">
                            {{ listings.current_page }} / {{ listings.last_page }}
                        </span>
                        <component
                            :is="listings.next_page_url ? Link : 'span'"
                            :href="listings.next_page_url"
                            class="btn-secondary px-4 py-2 text-sm"
                            :class="{ 'opacity-40 pointer-events-none': !listings.next_page_url }"
                        >
                            Siguiente →
                        </component>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import FashionFilters from '@/Components/Fashion/FashionFilters.vue';
import ListingCard from '@/Components/ListingCard.vue';
import { Link, router } from '@inertiajs/vue3';
import { computed, reactive, ref, watch } from 'vue';

const props = defineProps({
    department: Object,
    listings: Object,
    filters: { type: Object, default: () => ({}) },
    segmentChips: { type: Array, default: () => [] },
    navigationChips: { type: Array, default: () => [] },
    tipos: { type: Array, default: () => [] },
    conditions: { type: Array, default: () => [] },
    brands: { type: Array, default: () => [] },
    sizes: { type: Object, default: () => ({}) },
    colors: { type: Array, default: () => [] },
    universes: { type: Array, default: () => [] },
    breadcrumbs: { type: Array, default: () => [] },
});

const showMobileFilters = ref(false);

const localFilters = reactive({
    q: props.filters.q ?? '',
    segment: props.filters.segment ?? '',
    categoria: props.filters.categoria ?? '',
    tipo: props.filters.tipo ?? '',
    brand: props.filters.brand ?? '',
    size: props.filters.size ?? '',
    size_note: props.filters.size_note ?? '',
    color: props.filters.color ?? '',
    condition: props.filters.condition ?? '',
    min_price: props.filters.min_price ?? '',
    max_price: props.filters.max_price ?? '',
    mode: props.filters.mode ?? '',
    universe: props.filters.universe ?? '',
    sort: props.filters.sort ?? 'recent',
});

const pageTitle = computed(() => `Moda ${props.department.name}`);

const activeSizes = computed(() => {
    if (props.department.slug !== 'ninos') {
        const cat = localFilters.categoria ?? '';
        if (cat.includes('calzado')) {
            return props.sizes.calzado ?? [];
        }
        return props.sizes.ropa ?? [];
    }

    const segment = localFilters.segment;
    if (segment === 'bebe') {
        return props.sizes.bebe ?? [];
    }
    if (segment === 'nina' || segment === 'nino') {
        return [...(props.sizes.toddler ?? []), ...(props.sizes.nino_nina ?? [])];
    }

    return [
        ...(props.sizes.bebe ?? []),
        ...(props.sizes.toddler ?? []),
        ...(props.sizes.nino_nina ?? []),
    ];
});

const heightSizes = computed(() => props.sizes.altura_cm ?? []);

watch(() => props.filters, (f) => {
    Object.assign(localFilters, {
        q: f.q ?? '',
        segment: f.segment ?? '',
        categoria: f.categoria ?? '',
        tipo: f.tipo ?? '',
        brand: f.brand ?? '',
        size: f.size ?? '',
        size_note: f.size_note ?? '',
        color: f.color ?? '',
        condition: f.condition ?? '',
        min_price: f.min_price ?? '',
        max_price: f.max_price ?? '',
        mode: f.mode ?? '',
        universe: f.universe ?? '',
        sort: f.sort ?? 'recent',
    });
}, { deep: true });

function baseUrl() {
    return `/moda/${props.department.slug}`;
}

function applyFilters() {
    const params = Object.fromEntries(
        Object.entries(localFilters).filter(([, v]) => v !== '' && v != null),
    );
    router.get(baseUrl(), params, {
        preserveState: true,
        replace: true,
        preserveScroll: true,
        only: ['listings', 'filters', 'tipos', 'navigationChips'],
    });
}

function clearAllFilters() {
    Object.assign(localFilters, {
        q: '', segment: '', categoria: '', tipo: '', brand: '', size: '', size_note: '', color: '',
        condition: '', min_price: '', max_price: '', mode: '', universe: '', sort: 'recent',
    });
    applyFilters();
}

function setSegment(value) {
    localFilters.segment = value;
    localFilters.categoria = '';
    localFilters.tipo = '';
    applyFilters();
}

function selectChip(chip) {
    localFilters.categoria = chip.slug;
    localFilters.tipo = '';
    applyFilters();
}

function clearCategoryFilter() {
    localFilters.categoria = '';
    localFilters.tipo = '';
    applyFilters();
}

function setTipo(slug) {
    localFilters.tipo = localFilters.tipo === slug ? '' : slug;
    applyFilters();
}

</script>
