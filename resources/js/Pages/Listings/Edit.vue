<template>
    <AppLayout title="Editar anuncio">
        <div class="max-w-2xl mx-auto px-4 py-10">
            <h1 class="text-2xl font-bold text-gray-900 mb-2">Editar anuncio</h1>
            <p class="text-gray-500 text-sm mb-8">Actualiza los datos de tu artículo.</p>

            <!-- Indicador de pasos -->
            <div class="flex items-center gap-2 mb-8">
                <div
                    v-for="(label, i) in steps"
                    :key="i"
                    class="flex items-center gap-2"
                >
                    <div
                        class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold"
                        :class="step === i + 1 ? 'bg-indigo-600 text-white' : step > i + 1 ? 'bg-green-500 text-white' : 'bg-gray-200 text-gray-500'"
                    >
                        {{ step > i + 1 ? '✓' : i + 1 }}
                    </div>
                    <span class="text-sm hidden sm:block" :class="step === i + 1 ? 'text-indigo-600 font-medium' : 'text-gray-400'">{{ label }}</span>
                    <div v-if="i < steps.length - 1" class="w-8 h-px bg-gray-200" />
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-gray-100 p-6">
                <!-- Paso 1: Categoría -->
                <div v-if="step === 1">
                    <h2 class="font-bold text-gray-800 mb-4">¿Qué estás vendiendo?</h2>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                        <button
                            v-for="cat in categories"
                            :key="cat.id"
                            @click="selectCategory(cat)"
                            type="button"
                            class="flex flex-col items-center gap-1 p-4 border-2 rounded-xl hover:border-indigo-400 transition"
                            :class="selectedParent?.id === cat.id ? 'border-indigo-500 bg-indigo-50' : 'border-gray-100'"
                        >
                            <span class="text-xs font-medium text-center">{{ cat.name }}</span>
                        </button>
                    </div>

                    <div v-if="selectedParent && selectedParent.children?.length" class="mt-4">
                        <p class="text-sm font-medium text-gray-700 mb-2">Subcategoría</p>
                        <div class="flex flex-wrap gap-2">
                            <button
                                v-for="child in selectedParent.children"
                                :key="child.id"
                                @click="form.category_id = child.id"
                                type="button"
                                class="text-sm px-3 py-1.5 rounded-full border"
                                :class="form.category_id === child.id ? 'bg-indigo-600 text-white border-indigo-600' : 'border-gray-200 text-gray-600 hover:border-indigo-300'"
                            >
                                {{ child.name }}
                            </button>
                        </div>
                    </div>

                    <p v-if="form.errors.category_id" class="text-red-500 text-xs mt-2">{{ form.errors.category_id }}</p>
                </div>

                <!-- Paso 2: Datos del artículo -->
                <div v-if="step === 2" class="space-y-4">
                    <h2 class="font-bold text-gray-800 mb-2">Datos del artículo</h2>

                    <div>
                        <label class="text-sm font-medium text-gray-700">Título</label>
                        <input v-model="form.title" type="text" maxlength="120"
                            class="mt-1 w-full border rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-400 focus:outline-none"
                            placeholder="Ej: iPhone 13 128GB negro" />
                        <p v-if="form.errors.title" class="text-red-500 text-xs mt-1">{{ form.errors.title }}</p>
                    </div>

                    <div>
                        <label class="text-sm font-medium text-gray-700">Descripción</label>
                        <textarea v-model="form.description" rows="5" maxlength="5000"
                            class="mt-1 w-full border rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-400 focus:outline-none"
                            placeholder="Describe el estado, qué incluye, por qué lo vendes..." />
                        <p v-if="form.errors.description" class="text-red-500 text-xs mt-1">{{ form.errors.description }}</p>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-medium text-gray-700">Precio (COP)</label>
                            <input v-model="form.price" type="number" min="0"
                                class="mt-1 w-full border rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-400 focus:outline-none" />
                            <p v-if="form.errors.price" class="text-red-500 text-xs mt-1">{{ form.errors.price }}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-700">Estado</label>
                            <select v-model="form.condition_id"
                                class="mt-1 w-full border rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                                <option value="">Seleccionar</option>
                                <option v-for="c in conditions" :key="c.id" :value="c.id">{{ c.name }}</option>
                            </select>
                            <p v-if="form.errors.condition_id" class="text-red-500 text-xs mt-1">{{ form.errors.condition_id }}</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <input v-model="form.is_negotiable" type="checkbox" id="neg" class="rounded" />
                        <label for="neg" class="text-sm text-gray-600">Precio negociable</label>
                    </div>

                    <div>
                        <label class="text-sm font-medium text-gray-700">Ubicación</label>
                        <select v-model="form.location_id"
                            class="mt-1 w-full border rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                            <option value="">Sin especificar</option>
                            <option v-for="l in locations" :key="l.id" :value="l.id">{{ l.city }}</option>
                        </select>
                    </div>
                </div>

                <!-- Paso 3: Fotos -->
                <div v-if="step === 3">
                    <h2 class="font-bold text-gray-800 mb-4">Fotos del artículo</h2>

                    <div v-if="existingImages.length" class="grid grid-cols-4 gap-2 mb-4">
                        <div v-for="image in existingImages" :key="image.id" class="relative aspect-square">
                            <img :src="image.url" :alt="form.title" class="w-full h-full object-cover rounded-lg" />
                            <button
                                @click="removeExistingImage(image.id)"
                                type="button"
                                class="absolute top-1 right-1 w-5 h-5 bg-red-500 text-white rounded-full text-xs flex items-center justify-center leading-none"
                            >×</button>
                            <span v-if="image.is_primary" class="absolute bottom-1 left-1 text-xs bg-indigo-600 text-white px-1 rounded">Principal</span>
                        </div>
                    </div>

                    <div
                        v-if="totalImages < 8"
                        class="border-2 border-dashed border-gray-200 rounded-xl p-8 text-center cursor-pointer hover:border-indigo-400 transition"
                        @click="$refs.fileInput.click()"
                        @dragover.prevent
                        @drop.prevent="onDrop"
                    >
                        <p class="text-sm text-gray-500">Arrastra fotos aquí o haz clic para agregar más</p>
                        <p class="text-xs text-gray-400 mt-1">{{ totalImages }}/8 fotos · JPG, PNG, WebP · 5MB cada una</p>
                        <input ref="fileInput" type="file" multiple accept="image/*" class="hidden" @change="onFileChange" />
                    </div>

                    <div v-if="previews.length" class="grid grid-cols-4 gap-2 mt-4">
                        <div v-for="(preview, i) in previews" :key="`new-${i}`" class="relative aspect-square">
                            <img :src="preview" class="w-full h-full object-cover rounded-lg" alt="Nueva imagen" />
                            <button
                                @click="removeNewImage(i)"
                                type="button"
                                class="absolute top-1 right-1 w-5 h-5 bg-red-500 text-white rounded-full text-xs flex items-center justify-center leading-none"
                            >×</button>
                            <span v-if="!existingImages.length && i === 0" class="absolute bottom-1 left-1 text-xs bg-indigo-600 text-white px-1 rounded">Principal</span>
                        </div>
                    </div>
                </div>

                <!-- Navegación -->
                <div class="flex justify-between mt-8">
                    <button
                        v-if="step > 1"
                        @click="step--"
                        type="button"
                        class="px-5 py-2.5 border rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-50"
                    >
                        Atrás
                    </button>
                    <div v-else />

                    <button
                        v-if="step < 3"
                        @click="nextStep"
                        type="button"
                        class="px-6 py-2.5 bg-indigo-600 text-white rounded-lg text-sm font-semibold hover:bg-indigo-700"
                    >
                        Siguiente
                    </button>

                    <div v-if="step === 3" class="flex gap-3">
                        <button
                            @click="submit('draft')"
                            type="button"
                            :disabled="form.processing"
                            class="px-5 py-2.5 border rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-50 disabled:opacity-50"
                        >
                            Guardar borrador
                        </button>
                        <button
                            v-if="listing.status !== 'sold'"
                            @click="submit(listing.status === 'paused' ? 'paused' : 'active')"
                            type="button"
                            :disabled="form.processing"
                            class="px-6 py-2.5 bg-indigo-600 text-white rounded-lg text-sm font-semibold hover:bg-indigo-700 disabled:opacity-50"
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
import { useForm } from '@inertiajs/vue3';
import { computed, onMounted, ref } from 'vue';

const props = defineProps({
    listing: Object,
    categories: Array,
    conditions: Array,
    locations: Array,
});

const step = ref(1);
const steps = ['Categoría', 'Datos', 'Fotos'];
const selectedParent = ref(null);
const previews = ref([]);
const imageFiles = ref([]);
const existingImages = ref([...(props.listing.images ?? [])]);

const form = useForm({
    category_id: props.listing.category_id,
    condition_id: props.listing.condition_id ?? '',
    location_id: props.listing.location_id ?? '',
    title: props.listing.title ?? '',
    description: props.listing.description ?? '',
    price: props.listing.price ?? '',
    is_negotiable: props.listing.is_negotiable ?? false,
    status: props.listing.status ?? 'draft',
    remove_image_ids: [],
    images: [],
});

const totalImages = computed(() => existingImages.value.length + imageFiles.value.length);

onMounted(() => {
    initializeCategorySelection();
});

function initializeCategorySelection() {
    const categoryId = props.listing.category_id;
    if (!categoryId) {
        return;
    }

    for (const parent of props.categories) {
        if (parent.id === categoryId) {
            selectedParent.value = parent;
            form.category_id = parent.id;
            return;
        }

        const child = parent.children?.find((item) => item.id === categoryId);
        if (child) {
            selectedParent.value = parent;
            form.category_id = child.id;
            return;
        }
    }
}

function selectCategory(cat) {
    selectedParent.value = cat;
    if (!cat.children?.length) {
        form.category_id = cat.id;
    } else if (!cat.children.some((child) => child.id === form.category_id)) {
        form.category_id = null;
    }
}

function nextStep() {
    if (step.value === 1 && !form.category_id) {
        form.errors.category_id = 'Selecciona una categoría.';
        return;
    }
    form.clearErrors();
    step.value++;
}

function onFileChange(e) {
    addFiles(Array.from(e.target.files));
    e.target.value = '';
}

function onDrop(e) {
    addFiles(Array.from(e.dataTransfer.files).filter((file) => file.type.startsWith('image/')));
}

function addFiles(files) {
    const remaining = 8 - totalImages.value;
    files.slice(0, remaining).forEach((file) => {
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
    existingImages.value = existingImages.value.filter((image) => image.id !== id);
    form.remove_image_ids.push(id);
}

function submit(status) {
    form.status = status;
    form.images = imageFiles.value;
    form.post(`/listings/${props.listing.id}`, {
        forceFormData: true,
        _method: 'put',
    });
}
</script>
