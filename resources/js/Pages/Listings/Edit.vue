<template>
    <AppLayout title="Editar anuncio">
        <div class="container-app max-w-3xl py-10">
            <h1 class="text-2xl font-bold text-ink md:text-3xl">Editar anuncio</h1>
            <p class="mt-1 text-sm text-ink-secondary">Actualiza los datos de tu artículo.</p>

            <!-- Indicador de pasos -->
            <div class="mt-6 flex flex-wrap items-center gap-2">
                <div v-for="(label, i) in activeSteps" :key="label" class="flex items-center gap-2">
                    <div
                        class="flex h-7 w-7 items-center justify-center rounded-full text-xs font-bold"
                        :class="step === i + 1 ? 'bg-accent text-white' : step > i + 1 ? 'bg-trust text-white' : 'bg-surface-muted text-ink-muted'"
                    >
                        {{ step > i + 1 ? '✓' : i + 1 }}
                    </div>
                    <span class="hidden text-sm sm:block" :class="step === i + 1 ? 'font-medium text-accent' : 'text-ink-muted'">{{ label }}</span>
                    <div v-if="i < activeSteps.length - 1" class="hidden h-px w-6 bg-zinc-200 sm:block" />
                </div>
            </div>

            <div class="mt-8 rounded-2xl border border-zinc-200 bg-surface-raised p-6">
                <!-- MODA paso 1: categoría -->
                <div v-if="isFashion && step === 1">
                    <h2 class="font-bold text-ink">¿Qué vas a vender?</h2>
                    <p class="mt-1 text-sm text-ink-secondary">Elige departamento, categoría y tipo de prenda.</p>
                    <div class="mt-5">
                        <FashionCategoryPicker
                            v-model="form.category_id"
                            v-model:listing-type="form.listing_type"
                            :tree="fashionPickerTree"
                            :visuals="fashionVisuals"
                            :error="form.errors.category_id"
                        />
                    </div>
                </div>

                <!-- GENERAL paso 1 -->
                <div v-if="!isFashion && step === 1">
                    <h2 class="font-bold text-ink">¿Qué vas a vender?</h2>
                    <p class="mt-1 text-sm text-ink-secondary">Elige una categoría del marketplace general.</p>
                    <div class="mt-4 grid grid-cols-2 gap-2 sm:grid-cols-3">
                        <button
                            v-for="cat in otherCats"
                            :key="cat.id"
                            type="button"
                            class="rounded-xl border px-3 py-2.5 text-sm font-medium transition"
                            :class="form.category_id === cat.id ? 'border-accent bg-accent-soft text-accent' : 'border-zinc-200 text-ink-secondary hover:border-accent'"
                            @click="selectGeneralCategory(cat)"
                        >
                            {{ cat.name }}
                        </button>
                    </div>
                    <div v-if="selectedParent?.children?.length" class="mt-4">
                        <p class="mb-2 text-sm font-medium text-ink">Subcategoría</p>
                        <div class="flex flex-wrap gap-2">
                            <button
                                v-for="child in selectedParent.children"
                                :key="child.id"
                                type="button"
                                class="rounded-full border px-3 py-1.5 text-xs font-medium"
                                :class="form.category_id === child.id ? 'border-accent bg-accent text-white' : 'border-zinc-200 text-ink-secondary'"
                                @click="form.category_id = child.id"
                            >
                                {{ child.name }}
                            </button>
                        </div>
                    </div>
                    <p v-if="form.errors.category_id" class="mt-2 text-xs text-red-600">{{ form.errors.category_id }}</p>
                </div>

                <!-- MODA paso 2 / GENERAL paso 3: Fotos -->
                <div v-if="(isFashion && step === 2) || (!isFashion && step === 3)">
                    <h2 class="font-bold text-ink">Fotos del artículo</h2>
                    <ul v-if="photoTips.length" class="mt-2 list-inside list-disc text-xs text-ink-secondary">
                        <li v-for="tip in photoTips" :key="tip">{{ tip }}</li>
                    </ul>

                    <div v-if="existingImages.length" class="mt-4 grid grid-cols-4 gap-2">
                        <div v-for="image in existingImages" :key="image.id" class="relative aspect-square">
                            <img :src="image.url" :alt="form.title" class="h-full w-full rounded-lg object-cover" />
                            <button
                                type="button"
                                class="absolute right-1 top-1 flex h-5 w-5 items-center justify-center rounded-full bg-red-500 text-xs text-white"
                                @click="removeExistingImage(image.id)"
                            >×</button>
                            <span v-if="image.is_primary" class="absolute bottom-1 left-1 rounded bg-accent px-1 text-[10px] text-white">Principal</span>
                        </div>
                    </div>

                    <div
                        v-if="totalImages < 8"
                        class="mt-4 cursor-pointer rounded-xl border-2 border-dashed border-zinc-200 p-8 text-center transition hover:border-accent"
                        @click="$refs.fileInput.click()"
                        @dragover.prevent
                        @drop.prevent="onDrop"
                    >
                        <p class="text-3xl">📷</p>
                        <p class="mt-2 text-sm text-ink-secondary">Arrastra fotos o haz clic · {{ totalImages }}/8 · JPG, PNG, WebP</p>
                        <input ref="fileInput" type="file" multiple accept="image/*" class="hidden" @change="onFileChange" />
                    </div>

                    <div v-if="previews.length" class="mt-4 grid grid-cols-4 gap-2">
                        <div v-for="(preview, i) in previews" :key="`new-${i}`" class="relative aspect-square">
                            <img :src="preview" class="h-full w-full rounded-lg object-cover" alt="" />
                            <button type="button" class="absolute right-1 top-1 flex h-5 w-5 items-center justify-center rounded-full bg-red-500 text-xs text-white" @click="removeNewImage(i)">×</button>
                            <span v-if="!existingImages.length && i === 0" class="absolute bottom-1 left-1 rounded bg-accent px-1 text-[10px] text-white">Principal</span>
                        </div>
                    </div>
                </div>

                <!-- MODA paso 3: Detalles -->
                <div v-if="isFashion && step === 3" class="space-y-6">
                    <h2 class="font-bold text-ink">Detalles de la prenda</h2>

                    <div v-if="publishContext.show_brand">
                        <label class="text-sm font-medium text-ink">Marca <span v-if="publishContext.brand_required" class="text-accent">*</span></label>
                        <select v-model="form.brand_id" class="input-search mt-1 py-2 text-sm">
                            <option value="">Seleccionar marca</option>
                            <option v-for="b in brands" :key="b.id" :value="b.id">{{ b.name }}</option>
                        </select>
                        <input
                            v-if="!form.brand_id"
                            v-model="form.brand_name"
                            type="text"
                            class="input-search mt-2 py-2 text-sm"
                            placeholder="O escribe otra marca"
                        />
                        <p v-if="form.errors.brand_id" class="mt-1 text-xs text-red-600">{{ form.errors.brand_id }}</p>
                    </div>

                    <FashionSizeChips
                        v-if="publishContext.sizes?.length"
                        v-model="form.size"
                        :sizes="publishContext.sizes"
                        :error="form.errors.size"
                    />

                    <div v-if="publishContext.show_size_mismatch" class="space-y-2 rounded-lg border border-zinc-200 p-3">
                        <label class="flex items-center gap-2 text-sm text-ink-secondary">
                            <input v-model="sizeMismatch" type="checkbox" class="rounded text-accent" />
                            La talla de etiqueta no coincide con cómo queda
                        </label>
                        <div v-if="sizeMismatch" class="grid grid-cols-2 gap-2">
                            <input v-model="form.size_label" type="text" class="input-search py-2 text-sm" placeholder="Etiqueta (ej. M)" />
                            <input v-model="form.size_fits_as" type="text" class="input-search py-2 text-sm" placeholder="Queda como (ej. S)" />
                        </div>
                    </div>

                    <div v-if="publishContext.show_size_note">
                        <label class="text-sm font-medium text-ink">Altura de referencia (opcional)</label>
                        <select v-model="form.size_note" class="input-search mt-1 py-2 text-sm">
                            <option value="">Sin referencia</option>
                            <option v-for="h in publishContext.height_sizes" :key="h" :value="h">{{ h }}</option>
                        </select>
                    </div>

                    <FashionColorSwatches
                        v-if="publishContext.show_color"
                        v-model="form.color"
                        :colors="fashionColors"
                        :error="form.errors.color"
                    />

                    <div v-if="publishContext.show_measurements" class="grid grid-cols-3 gap-3">
                        <div>
                            <label class="text-xs font-medium text-ink-secondary">Pecho (cm)</label>
                            <input v-model="form.measurements.bust_cm" type="number" min="0" class="input-search mt-1 py-2 text-sm" />
                        </div>
                        <div>
                            <label class="text-xs font-medium text-ink-secondary">Cintura (cm)</label>
                            <input v-model="form.measurements.waist_cm" type="number" min="0" class="input-search mt-1 py-2 text-sm" />
                        </div>
                        <div>
                            <label class="text-xs font-medium text-ink-secondary">Largo (cm)</label>
                            <input v-model="form.measurements.length_cm" type="number" min="0" class="input-search mt-1 py-2 text-sm" />
                        </div>
                    </div>

                    <div v-if="publishContext.show_sole_length">
                        <label class="text-xs font-medium text-ink-secondary">Longitud plantilla (cm)</label>
                        <input v-model="form.measurements.sole_length_cm" type="number" min="0" step="0.1" class="input-search mt-1 py-2 text-sm" />
                    </div>

                    <div>
                        <p class="mb-2 text-sm font-medium text-ink">Estado <span class="text-accent">*</span></p>
                        <div class="space-y-1.5">
                            <label
                                v-for="cond in fashionConditions"
                                :key="cond.id"
                                class="flex cursor-pointer items-center gap-2.5 rounded-lg border px-3 py-2 text-sm transition"
                                :class="form.condition_id === cond.id ? 'border-accent bg-accent-soft' : 'border-zinc-200 hover:bg-surface-muted'"
                            >
                                <input v-model="form.condition_id" type="radio" :value="cond.id" class="text-accent" />
                                <span class="font-medium text-ink">{{ cond.name }}</span>
                            </label>
                        </div>
                        <p v-if="form.errors.condition_id" class="mt-1 text-xs text-red-600">{{ form.errors.condition_id }}</p>
                    </div>

                    <UniverseSelector
                        v-if="hasFashionUniverses"
                        v-model="form.universe_ids"
                        :universes="availableUniverses"
                        :error="form.errors.universe_ids"
                        class="border-t border-zinc-100 pt-6"
                    />
                </div>

                <!-- MODA paso 4 / GENERAL paso 2: Precio y datos -->
                <div v-if="(isFashion && step === 4) || (!isFashion && step === 2)" class="space-y-4">
                    <h2 class="font-bold text-ink">{{ isFashion ? 'Precio y publicación' : 'Datos del artículo' }}</h2>

                    <div
                        v-if="isFashion"
                        id="universos-editar"
                        class="rounded-xl border-2 border-accent/30 bg-accent-soft p-4 shadow-sm"
                    >
                        <UniverseSelector
                            v-if="hasFashionUniverses"
                            v-model="form.universe_ids"
                            :universes="availableUniverses"
                            :error="form.errors.universe_ids"
                        />
                    </div>

                    <div>
                        <label class="text-sm font-medium text-ink">Título</label>
                        <input v-model="form.title" type="text" maxlength="120" class="input-search mt-1 py-2.5 text-sm" :placeholder="titlePlaceholder" />
                        <p v-if="form.errors.title" class="mt-1 text-xs text-red-600">{{ form.errors.title }}</p>
                    </div>

                    <div>
                        <label class="text-sm font-medium text-ink">Descripción</label>
                        <textarea v-model="form.description" rows="5" maxlength="5000" class="input-search mt-1 py-2.5 text-sm" placeholder="Estado, qué incluye, defectos, material..." />
                        <p v-if="form.errors.description" class="mt-1 text-xs text-red-600">{{ form.errors.description }}</p>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-medium text-ink">Precio (COP)</label>
                            <input v-model="form.price" type="number" min="0" class="input-search mt-1 py-2.5 text-sm" />
                            <p v-if="form.errors.price" class="mt-1 text-xs text-red-600">{{ form.errors.price }}</p>
                        </div>
                        <div v-if="!isFashion">
                            <label class="text-sm font-medium text-ink">Estado</label>
                            <select v-model="form.condition_id" class="input-search mt-1 py-2.5 text-sm">
                                <option value="">Seleccionar</option>
                                <option v-for="c in conditions" :key="c.id" :value="c.id">{{ c.name }}</option>
                            </select>
                            <p v-if="form.errors.condition_id" class="mt-1 text-xs text-red-600">{{ form.errors.condition_id }}</p>
                        </div>
                    </div>

                    <div v-if="isFashion" class="space-y-2">
                        <p class="text-sm font-medium text-ink">Modo de venta</p>
                        <label v-for="mode in fashionListingModes" :key="mode.value" class="flex cursor-pointer gap-3 rounded-lg border p-3 text-sm" :class="form.listing_mode === mode.value ? 'border-accent bg-accent-soft' : 'border-zinc-200'">
                            <input v-model="form.listing_mode" type="radio" :value="mode.value" class="mt-0.5 text-accent" />
                            <span>
                                <span class="font-medium text-ink">{{ mode.label }}</span>
                                <span class="mt-0.5 block text-xs text-ink-secondary">{{ mode.description }}</span>
                            </span>
                        </label>
                    </div>

                    <label class="flex items-center gap-2 text-sm text-ink-secondary">
                        <input v-model="form.is_negotiable" type="checkbox" class="rounded text-accent" />
                        Precio negociable
                    </label>

                    <div v-if="canShowTradeOption" class="rounded-xl border border-violet-100 bg-violet-50/60 p-4">
                        <label class="flex items-start gap-3 text-sm cursor-pointer">
                            <input v-model="form.accepts_trade" type="checkbox" class="mt-1 rounded text-accent" />
                            <span>
                                <span class="font-medium text-ink">Acepto trueque</span>
                                <span class="mt-1 block text-xs text-ink-secondary">
                                    Otros usuarios verificados podrán proponerte intercambiar otra prenda por esta.
                                </span>
                            </span>
                        </label>
                    </div>
                    <p
                        v-else-if="tradePublish?.enabled && !tradePublish?.eligible"
                        class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-xs text-amber-900"
                    >
                        {{ tradePublish.ineligible_reason }}
                    </p>

                    <div>
                        <label class="text-sm font-medium text-ink">Ubicación</label>
                        <select v-model="form.location_id" class="input-search mt-1 py-2.5 text-sm">
                            <option value="">Sin especificar</option>
                            <option v-for="l in locations" :key="l.id" :value="l.id">{{ l.city }}</option>
                        </select>
                    </div>
                </div>

                <!-- MODA paso 5: Revisión -->
                <div v-if="isFashion && step === 5">
                    <h2 class="font-bold text-ink">Revisión</h2>
                    <p class="mt-1 text-sm text-ink-secondary">Confirma antes de guardar.</p>
                    <dl class="mt-4 space-y-2 text-sm">
                        <div v-if="form.brand_id || form.brand_name" class="flex justify-between gap-4 border-b border-zinc-100 py-2">
                            <dt class="text-ink-secondary">Marca</dt>
                            <dd class="font-medium text-ink">{{ brandLabel }}</dd>
                        </div>
                        <div v-if="form.size" class="flex justify-between gap-4 border-b border-zinc-100 py-2">
                            <dt class="text-ink-secondary">Talla</dt>
                            <dd class="font-medium text-ink">{{ form.size }}</dd>
                        </div>
                        <div v-if="form.color" class="flex justify-between gap-4 border-b border-zinc-100 py-2">
                            <dt class="text-ink-secondary">Color</dt>
                            <dd class="font-medium text-ink">{{ form.color }}</dd>
                        </div>
                        <div class="flex justify-between gap-4 border-b border-zinc-100 py-2">
                            <dt class="text-ink-secondary">Título</dt>
                            <dd class="text-right font-medium text-ink">{{ form.title }}</dd>
                        </div>
                        <div class="flex justify-between gap-4 border-b border-zinc-100 py-2">
                            <dt class="text-ink-secondary">Precio</dt>
                            <dd class="font-medium text-ink">${{ Number(form.price || 0).toLocaleString('es-CO') }}</dd>
                        </div>
                        <div v-if="selectedUniverseLabels.length" class="flex justify-between gap-4 border-b border-zinc-100 py-2">
                            <dt class="text-ink-secondary">Universos</dt>
                            <dd class="text-right font-medium text-ink">{{ selectedUniverseLabels.join(', ') }}</dd>
                        </div>
                        <div class="flex justify-between gap-4 py-2">
                            <dt class="text-ink-secondary">Fotos</dt>
                            <dd class="font-medium text-ink">{{ totalImages }}</dd>
                        </div>
                    </dl>
                </div>

                <!-- Navegación -->
                <div class="mt-8 flex justify-between">
                    <button v-if="step > 1" type="button" class="btn-secondary px-5 py-2.5 text-sm" @click="step--">Atrás</button>
                    <div v-else />

                    <button v-if="step < maxStep" type="button" class="btn-primary px-6 py-2.5 text-sm" @click="nextStep">Siguiente</button>

                    <div v-else class="flex gap-3">
                        <button type="button" class="btn-secondary px-5 py-2.5 text-sm" :disabled="form.processing" @click="submit('draft')">Guardar borrador</button>
                        <button
                            v-if="listing.status !== 'sold'"
                            type="button"
                            class="btn-primary px-6 py-2.5 text-sm"
                            :disabled="form.processing"
                            @click="submit(listing.status === 'paused' ? 'paused' : 'active')"
                        >
                            {{ form.processing ? 'Guardando...' : 'Guardar cambios' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import FashionCategoryPicker from '@/Components/Fashion/FashionCategoryPicker.vue';
import FashionColorSwatches from '@/Components/Fashion/FashionColorSwatches.vue';
import FashionSizeChips from '@/Components/Fashion/FashionSizeChips.vue';
import UniverseSelector from '@/Components/Fashion/UniverseSelector.vue';
import { useForm, usePage } from '@inertiajs/vue3';
import { computed, onMounted, ref, watch } from 'vue';

const props = defineProps({
    listing: Object,
    fashionPickerTree: { type: Array, default: () => [] },
    fashionPublishContexts: { type: Object, default: () => ({}) },
    fashionVisuals: { type: Object, default: () => ({}) },
    fashionConditions: { type: Array, default: () => [] },
    fashionColors: { type: Array, default: () => [] },
    fashionListingModes: { type: Array, default: () => [] },
    fashionUniverses: { type: Array, default: () => [] },
    brands: { type: Array, default: () => [] },
    otherCategories: { type: Array, default: () => [] },
    conditions: Array,
    locations: Array,
    tradePublish: { type: Object, default: () => ({ enabled: false, eligible: false, eligible_condition_slugs: [] }) },
});

const page = usePage();

const availableUniverses = computed(() => {
    if (props.fashionUniverses?.length) {
        return props.fashionUniverses;
    }

    return page.props.fashionUniverses ?? [];
});

const vertical = ref(props.listing.is_fashion ? 'fashion' : 'general');
const step = ref(1);
const selectedParent = ref(null);
const previews = ref([]);
const imageFiles = ref([]);
const existingImages = ref([...(props.listing.images ?? [])]);
const sizeMismatch = ref(Boolean(props.listing.size_label || props.listing.size_fits_as));

const fashionSteps = ['Qué vendes', 'Fotos', 'Detalles', 'Precio', 'Revisión'];
const generalSteps = ['Categoría', 'Datos', 'Fotos'];

const isFashion = computed(() => vertical.value === 'fashion');
const hasFashionUniverses = computed(() => availableUniverses.value.length > 0);
const activeSteps = computed(() => (isFashion.value ? fashionSteps : generalSteps));
const maxStep = computed(() => activeSteps.value.length);
const totalImages = computed(() => existingImages.value.length + imageFiles.value.length);

const publishContext = computed(() => {
    if (!form.category_id) {
        return defaultContext();
    }

    return props.fashionPublishContexts[form.category_id] ?? defaultContext();
});

const photoTips = computed(() => publishContext.value.photo_tips ?? []);

const titlePlaceholder = computed(() =>
    isFashion.value ? 'Ej: Vestido floral Zara talla M' : 'Ej: iPhone 13 128GB negro',
);

const brandLabel = computed(() => {
    if (form.brand_id) {
        return props.brands.find(b => b.id === form.brand_id)?.name ?? '';
    }

    return form.brand_name;
});

const selectedUniverseLabels = computed(() =>
    availableUniverses.value
        .filter(u => form.universe_ids.map(Number).includes(Number(u.id)))
        .map(u => u.name),
);

const otherCats = computed(() => props.otherCategories ?? []);

const form = useForm({
    category_id: props.listing.category_id ?? null,
    condition_id: props.listing.condition_id ?? '',
    location_id: props.listing.location_id ?? '',
    brand_id: props.listing.brand_id ?? '',
    brand_name: props.listing.brand_name ?? '',
    size: props.listing.size ?? '',
    size_note: props.listing.size_note ?? '',
    size_label: props.listing.size_label ?? '',
    size_fits_as: props.listing.size_fits_as ?? '',
    color: props.listing.color ?? '',
    listing_mode: props.listing.listing_mode ?? 'compra_protegida',
    listing_type: props.listing.listing_type ?? 'individual',
    title: props.listing.title ?? '',
    description: props.listing.description ?? '',
    price: props.listing.price ?? '',
    is_negotiable: props.listing.is_negotiable ?? false,
    accepts_trade: props.listing.accepts_trade ?? false,
    measurements: {
        bust_cm: props.listing.measurements?.bust_cm ?? '',
        waist_cm: props.listing.measurements?.waist_cm ?? '',
        length_cm: props.listing.measurements?.length_cm ?? '',
        sole_length_cm: props.listing.measurements?.sole_length_cm ?? '',
    },
    status: props.listing.status ?? 'draft',
    remove_image_ids: [],
    images: [],
    universe_ids: [...(props.listing.universe_ids ?? [])],
});

const selectedConditionSlug = computed(() => {
    const pool = isFashion.value ? props.fashionConditions : props.conditions;
    const id = Number(form.condition_id);

    return pool?.find(c => Number(c.id) === id)?.slug ?? null;
});

const canShowTradeOption = computed(() => {
    if (! props.tradePublish?.enabled || ! props.tradePublish?.eligible) {
        return false;
    }

    if (! selectedConditionSlug.value) {
        return false;
    }

    return props.tradePublish.eligible_condition_slugs.includes(selectedConditionSlug.value);
});

watch(selectedConditionSlug, (slug) => {
    if (! slug || ! props.tradePublish.eligible_condition_slugs.includes(slug)) {
        form.accepts_trade = false;
    }
});

function defaultContext() {
    return {
        sizes: [],
        show_brand: true,
        brand_required: false,
        show_color: true,
        show_measurements: false,
        show_sole_length: false,
        show_size_mismatch: false,
        show_size_note: false,
        height_sizes: [],
        photo_tips: [],
    };
}

function selectGeneralCategory(cat) {
    selectedParent.value = cat;
    form.category_id = cat.children?.length ? null : cat.id;
    form.universe_ids = [];
}

function initializeGeneralCategory() {
    const categoryId = props.listing.category_id;
    if (!categoryId || isFashion.value) {
        return;
    }

    for (const parent of otherCats.value) {
        if (parent.id === categoryId) {
            selectedParent.value = parent;
            return;
        }

        const child = parent.children?.find(item => item.id === categoryId);
        if (child) {
            selectedParent.value = parent;
            return;
        }
    }
}

onMounted(() => {
    initializeGeneralCategory();
});

watch(() => form.category_id, (id) => {
    const ctx = id ? (props.fashionPublishContexts[id] ?? null) : null;
    if (ctx?.default_size && !form.size) {
        form.size = ctx.default_size;
    }
});

function nextStep() {
    form.clearErrors();

    if (isFashion.value) {
        if (step.value === 1 && !form.category_id) {
            form.setError('category_id', 'Selecciona el tipo de prenda.');
            return;
        }
        if (step.value === 3) {
            if (publishContext.value.show_brand && publishContext.value.brand_required && !form.brand_id && !form.brand_name) {
                form.setError('brand_id', 'Indica la marca.');
                return;
            }
            if (publishContext.value.sizes?.length && !form.size) {
                form.setError('size', 'Selecciona la talla.');
                return;
            }
            if (publishContext.value.show_color && !form.color) {
                form.setError('color', 'Selecciona el color.');
                return;
            }
            if (!form.condition_id) {
                form.setError('condition_id', 'Selecciona el estado.');
                return;
            }
        }
        if (step.value === 4) {
            if (!form.title || form.title.length < 5) {
                form.setError('title', 'Título mínimo 5 caracteres.');
                return;
            }
            if (!form.description || form.description.length < 20) {
                form.setError('description', 'Descripción mínimo 20 caracteres.');
                return;
            }
            if (!form.price && form.price !== 0) {
                form.setError('price', 'Indica el precio.');
                return;
            }
        }
    } else {
        if (step.value === 1 && !form.category_id) {
            form.setError('category_id', 'Selecciona una categoría.');
            return;
        }
        if (step.value === 2) {
            if (!form.title || form.title.length < 5) {
                form.setError('title', 'Título mínimo 5 caracteres.');
                return;
            }
            if (!form.description || form.description.length < 20) {
                form.setError('description', 'Descripción mínimo 20 caracteres.');
                return;
            }
            if (!form.price && form.price !== 0) {
                form.setError('price', 'Indica el precio.');
                return;
            }
            if (!form.condition_id) {
                form.setError('condition_id', 'Selecciona el estado.');
                return;
            }
        }
    }

    step.value++;
}

function onFileChange(e) {
    addFiles(Array.from(e.target.files));
    e.target.value = '';
}

function onDrop(e) {
    addFiles(Array.from(e.dataTransfer.files).filter(f => f.type.startsWith('image/')));
}

function addFiles(files) {
    const remaining = 8 - totalImages.value;
    files.slice(0, remaining).forEach(file => {
        imageFiles.value.push(file);
        previews.value.push(URL.createObjectURL(file));
    });
}

function removeNewImage(index) {
    URL.revokeObjectURL(previews.value[index]);
    imageFiles.value.splice(index, 1);
    previews.value.splice(index, 1);
}

function removeExistingImage(id) {
    existingImages.value = existingImages.value.filter(image => image.id !== id);
    form.remove_image_ids.push(id);
}

function submit(status) {
    form.status = status;
    form.images = imageFiles.value;
    if (!isFashion.value) {
        form.universe_ids = [];
    }

    form.put(`/listings/${props.listing.id}`, {
        forceFormData: imageFiles.value.length > 0,
    });
}
</script>
