<template>
    <div class="space-y-5">
        <div v-if="showListingType" class="flex flex-wrap gap-2">
            <button
                type="button"
                class="rounded-full px-4 py-2 text-sm font-medium transition"
                :class="listingType === 'individual' ? 'bg-accent text-white' : 'bg-surface-muted text-ink-secondary hover:bg-zinc-200'"
                @click="$emit('update:listingType', 'individual')"
            >
                Prenda individual
            </button>
            <button
                type="button"
                class="rounded-full px-4 py-2 text-sm font-medium transition"
                :class="listingType === 'lote' ? 'bg-accent text-white' : 'bg-surface-muted text-ink-secondary hover:bg-zinc-200'"
                @click="$emit('update:listingType', 'lote')"
            >
                Lote / paquete
            </button>
        </div>

        <div>
            <p class="mb-2 text-sm font-medium text-ink">Departamento</p>
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                <VisualCategoryCard
                    v-for="dept in tree"
                    :key="dept.id"
                    selectable
                    compact
                    :selected="selectedDept?.id === dept.id"
                    :title="visualFor(dept).title"
                    :subtitle="visualFor(dept).subtitle"
                    :image="visualFor(dept).image"
                    :show-cta="false"
                    @select="selectDepartment(dept)"
                />
            </div>
        </div>

        <div v-if="selectedDept?.key === 'ninos' && selectedDept.segments?.length">
            <p class="mb-2 text-sm font-medium text-ink">Segmento</p>
            <div class="flex flex-wrap gap-2">
                <button
                    v-for="seg in selectedDept.segments"
                    :key="seg.id"
                    type="button"
                    class="rounded-full px-4 py-2 text-sm font-medium transition"
                    :class="selectedSegment?.id === seg.id ? 'bg-accent text-white' : 'bg-surface-muted text-ink-secondary hover:bg-zinc-200'"
                    @click="selectSegment(seg)"
                >
                    {{ seg.name }}
                </button>
            </div>
        </div>

        <div v-if="activeCategories.length">
            <p class="mb-2 text-sm font-medium text-ink">Categoría</p>
            <div class="flex flex-wrap gap-2">
                <button
                    v-for="cat in activeCategories"
                    :key="cat.id"
                    type="button"
                    class="rounded-full px-4 py-2 text-sm font-medium transition"
                    :class="selectedCategory?.id === cat.id ? 'bg-ink text-white' : 'bg-surface-muted text-ink-secondary hover:bg-zinc-200'"
                    @click="selectCategory(cat)"
                >
                    {{ cat.name }}
                </button>
            </div>
        </div>

        <div v-if="selectedCategory?.types?.length">
            <p class="mb-2 text-sm font-medium text-ink">Tipo de prenda</p>
            <div class="flex flex-wrap gap-2">
                <button
                    v-for="tipo in selectedCategory.types"
                    :key="tipo.id"
                    type="button"
                    class="rounded-full border px-3 py-1.5 text-xs font-medium transition"
                    :class="modelValue === tipo.id ? 'border-accent bg-accent-soft text-accent' : 'border-zinc-200 text-ink-secondary hover:border-accent'"
                    @click="selectType(tipo)"
                >
                    {{ tipo.name }}
                </button>
            </div>
        </div>

        <p v-if="breadcrumb" class="rounded-lg bg-surface-muted px-3 py-2 text-xs text-ink-secondary">
            {{ breadcrumb }}
        </p>

        <p v-if="error" class="text-xs text-red-600">{{ error }}</p>
    </div>
</template>

<script setup>
import VisualCategoryCard from '@/Components/VisualCategoryCard.vue';
import { computed, ref, watch } from 'vue';

const props = defineProps({
    tree: { type: Array, default: () => [] },
    visuals: { type: Object, default: () => ({}) },
    modelValue: { type: [Number, null], default: null },
    listingType: { type: String, default: 'individual' },
    error: { type: String, default: '' },
});

const emit = defineEmits(['update:modelValue', 'update:listingType', 'context-change']);

const selectedDept = ref(null);
const selectedSegment = ref(null);
const selectedCategory = ref(null);

const showListingType = computed(() => selectedDept.value?.key === 'ninos');

const activeCategories = computed(() => {
    if (selectedDept.value?.key === 'ninos') {
        return selectedSegment.value?.categories ?? [];
    }

    return selectedDept.value?.categories ?? [];
});

const breadcrumb = computed(() => {
    const parts = ['Moda'];
    if (selectedDept.value) {
        parts.push(selectedDept.value.name);
    }
    if (selectedSegment.value) {
        parts.push(selectedSegment.value.name);
    }
    if (selectedCategory.value) {
        parts.push(selectedCategory.value.name);
    }
    const tipo = selectedCategory.value?.types?.find(t => t.id === props.modelValue);
    if (tipo) {
        parts.push(tipo.name);
    }

    return parts.length > 1 ? parts.join(' › ') : '';
});

function visualFor(dept) {
    const key = dept.key ?? dept.slug?.replace(/^moda-/, '');
    const visual = props.visuals?.[key];

    return {
        title: visual?.title ?? dept.name,
        subtitle: visual?.subtitle ?? dept.description ?? '',
        image: visual?.image ?? '/images/categories/mujer.jpg',
    };
}

function selectDepartment(dept) {
    selectedDept.value = dept;
    selectedSegment.value = null;
    selectedCategory.value = null;
    emit('update:modelValue', null);
    if (dept.key !== 'ninos') {
        emit('update:listingType', 'individual');
    }
}

function selectSegment(seg) {
    selectedSegment.value = seg;
    selectedCategory.value = null;
    emit('update:modelValue', null);
}

function selectCategory(cat) {
    selectedCategory.value = cat;
    emit('update:modelValue', null);
}

function selectType(tipo) {
    emit('update:modelValue', tipo.id);
    emit('context-change', tipo.id);
}

watch(() => props.modelValue, (id) => {
    if (! id || props.tree.length === 0) {
        return;
    }
    syncFromCategoryId(id);
}, { immediate: true });

function syncFromCategoryId(id) {
    for (const dept of props.tree) {
        const categories = dept.key === 'ninos'
            ? (dept.segments ?? []).flatMap(s => (s.categories ?? []).map(c => ({ ...c, _segment: s })))
            : (dept.categories ?? []).map(c => ({ ...c, _segment: null }));

        for (const cat of categories) {
            const tipo = cat.types?.find(t => t.id === id);
            if (tipo) {
                selectedDept.value = dept;
                selectedSegment.value = cat._segment;
                selectedCategory.value = cat;
                return;
            }
        }
    }
}
</script>
