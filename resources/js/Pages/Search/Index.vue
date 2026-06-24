<template>
    <AppLayout :title="pageTitle">
        <div class="max-w-7xl mx-auto px-4 py-8">
            <!-- Barra de búsqueda móvil -->
            <form @submit.prevent="search" class="flex gap-2 mb-6 md:hidden">
                <input
                    v-model="localFilters.q"
                    type="search"
                    placeholder="Buscar..."
                    class="flex-1 px-4 py-2.5 border rounded-full text-sm focus:ring-2 focus:ring-accent/20 focus:outline-none"
                />
                <button type="submit" class="bg-accent text-white px-4 py-2.5 rounded-full text-sm font-medium">Buscar</button>
            </form>

            <div class="flex gap-6">
                <!-- Filtros sidebar -->
                <aside class="w-56 shrink-0 hidden md:block">
                    <form @submit.prevent="search" class="space-y-5">
                        <div>
                            <label class="text-sm font-semibold text-gray-700 block mb-2">Categoría</label>
                            <div class="space-y-1">
                                <label
                                    v-for="cat in categories"
                                    :key="cat.id"
                                    class="flex items-center gap-2 text-sm cursor-pointer hover:text-accent"
                                >
                                    <input type="radio" v-model="localFilters.category" :value="cat.slug" class="text-accent" />
                                    <span>{{ cat.icon }} {{ cat.name }}</span>
                                </label>
                            </div>
                        </div>

                        <div>
                            <label class="text-sm font-semibold text-gray-700 block mb-2">Estado</label>
                            <div class="space-y-1">
                                <label
                                    v-for="cond in conditions"
                                    :key="cond.id"
                                    class="flex items-center gap-2 text-sm cursor-pointer hover:text-accent"
                                >
                                    <input type="radio" v-model="localFilters.condition" :value="cond.id" class="text-accent" />
                                    {{ cond.name }}
                                </label>
                            </div>
                        </div>

                        <div>
                            <label class="text-sm font-semibold text-gray-700 block mb-2">Precio</label>
                            <div class="flex items-center gap-2">
                                <input v-model="localFilters.min_price" type="number" placeholder="Mín"
                                    class="w-full border rounded-lg px-2 py-1.5 text-sm focus:ring-accent/20 focus:outline-none" />
                                <span class="text-gray-400 text-xs">—</span>
                                <input v-model="localFilters.max_price" type="number" placeholder="Máx"
                                    class="w-full border rounded-lg px-2 py-1.5 text-sm focus:ring-accent/20 focus:outline-none" />
                            </div>
                        </div>

                        <button type="submit" class="w-full bg-accent text-white py-2 rounded-lg text-sm font-medium hover:bg-accent-hover">
                            Aplicar filtros
                        </button>
                        <button type="button" @click="clearFilters" class="w-full text-gray-400 text-sm hover:text-gray-600">
                            Limpiar filtros
                        </button>
                    </form>
                </aside>

                <!-- Resultados -->
                <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between mb-4">
                        <p class="text-sm text-gray-500">
                            <span class="font-medium text-gray-800">{{ listings.total }}</span> resultados
                            <span v-if="filters.q"> para "<strong>{{ filters.q }}</strong>"</span>
                        </p>
                        <select v-model="localFilters.sort" @change="search"
                            class="border rounded-lg px-3 py-1.5 text-sm focus:ring-2 focus:ring-accent/20 focus:outline-none">
                            <option value="recent">Más recientes</option>
                            <option value="price_asc">Menor precio</option>
                            <option value="price_desc">Mayor precio</option>
                            <option value="popular">Más vistos</option>
                        </select>
                    </div>

                    <div v-if="listings.data.length === 0" class="text-center py-20 text-gray-400">
                        <p class="text-5xl mb-3">🔍</p>
                        <p class="font-medium text-gray-600">No encontramos resultados</p>
                        <p class="text-sm mt-1">Prueba con otros términos o elimina los filtros.</p>
                    </div>

                    <div v-else class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                        <ListingCard
                            v-for="listing in listings.data"
                            :key="listing.id"
                            :listing="listing"
                        />
                    </div>

                    <!-- Paginación -->
                    <div v-if="listings.last_page > 1" class="flex justify-center gap-2 mt-8">
                        <component
                            :is="listings.prev_page_url ? Link : 'span'"
                            :href="listings.prev_page_url"
                            class="px-4 py-2 border rounded-lg text-sm"
                            :class="listings.prev_page_url ? 'hover:bg-gray-50 text-gray-700' : 'text-gray-300 cursor-default'"
                        >
                            ← Anterior
                        </component>
                        <span class="px-4 py-2 text-sm text-gray-500">
                            {{ listings.current_page }} / {{ listings.last_page }}
                        </span>
                        <component
                            :is="listings.next_page_url ? Link : 'span'"
                            :href="listings.next_page_url"
                            class="px-4 py-2 border rounded-lg text-sm"
                            :class="listings.next_page_url ? 'hover:bg-gray-50 text-gray-700' : 'text-gray-300 cursor-default'"
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
import ListingCard from '@/Components/ListingCard.vue';
import { Link, router } from '@inertiajs/vue3';
import { reactive, computed } from 'vue';

const props = defineProps({
    listings:   Object,
    filters:    Object,
    categories: Array,
    conditions: Array,
});

const localFilters = reactive({ ...props.filters });

const pageTitle = computed(() =>
    props.filters.q ? `Búsqueda: ${props.filters.q}` : 'Explorar anuncios'
);

function search() {
    router.get('/anuncios', localFilters, { preserveState: true, replace: true });
}

function clearFilters() {
    Object.assign(localFilters, { q: '', category: '', condition: '', min_price: '', max_price: '', sort: 'recent' });
    search();
}
</script>
