<template>
    <div class="space-y-3">
        <!-- Fotos reales: sin IA -->
        <div class="rounded-lg border border-trust/25 bg-trust-soft/70 px-3.5 py-3">
            <p class="text-sm font-semibold text-ink">Fotos reales, sin edición con IA</p>
            <p class="mt-1.5 text-xs leading-relaxed text-ink-secondary">
                En {{ brandName }} la confianza empieza con lo que se ve. Sube imágenes tomadas con tu celular o cámara,
                <strong class="font-medium text-ink">sin filtros de inteligencia artificial</strong> ni retoques que cambien el color, la forma o el estado real del artículo.
            </p>
            <p class="mt-1.5 text-xs leading-relaxed text-ink-secondary">
                No retocamos ni “mejoramos” tus fotos con tecnología de IA: lo que subes es lo que verá quien compra.
                Así la comunidad sabe exactamente qué está recibiendo y puede confiar en cada publicación.
            </p>
        </div>

        <ul v-if="tips.length" class="list-inside list-disc text-xs text-ink-secondary">
            <li v-for="tip in tips" :key="tip">{{ tip }}</li>
        </ul>

        <p class="text-xs text-ink-secondary">
            <span class="font-medium text-ink">La primera foto es la portada.</span>
            Arrástrala para cambiar el orden · {{ items.length }}/{{ max }}
        </p>

        <div class="grid grid-cols-4 gap-2 sm:grid-cols-4">
            <div
                v-for="(item, index) in items"
                :key="item.key"
                draggable="true"
                class="group relative aspect-square cursor-grab overflow-hidden rounded-lg border bg-white transition active:cursor-grabbing"
                :class="index === 0
                    ? 'border-accent/70 ring-1 ring-accent/25'
                    : dragOverIndex === index
                        ? 'border-accent/50 bg-accent-soft/20'
                        : 'border-zinc-200 hover:border-zinc-300'"
                @dragstart="onDragStart(item.key, $event)"
                @dragover.prevent="onDragOver(index)"
                @dragleave="onDragLeave(index)"
                @drop.prevent="onDrop(index)"
                @dragend="onDragEnd"
            >
                <img :src="item.preview" alt="" class="h-full w-full object-cover" draggable="false" />
                <button
                    type="button"
                    class="absolute right-0.5 top-0.5 flex h-5 w-5 items-center justify-center rounded-full bg-black/55 text-xs text-white opacity-0 transition group-hover:opacity-100"
                    aria-label="Quitar foto"
                    @click.stop="removeItem(item.key)"
                >
                    ×
                </button>
                <span
                    v-if="index === 0"
                    class="absolute bottom-0.5 left-0.5 rounded bg-accent px-1 py-px text-[9px] font-semibold leading-tight text-white"
                >
                    Portada
                </span>
                <span
                    v-else
                    class="absolute bottom-0.5 left-0.5 rounded bg-black/45 px-1 text-[9px] text-white"
                >
                    {{ index + 1 }}
                </span>
            </div>

            <button
                v-if="items.length < max"
                type="button"
                class="flex aspect-square flex-col items-center justify-center rounded-lg border border-dashed border-zinc-200 bg-zinc-50/80 text-zinc-400 transition hover:border-accent/60 hover:bg-accent-soft/20 hover:text-accent"
                @click="openFilePicker"
                @dragover.prevent
                @drop.prevent="onAddDrop"
            >
                <span class="text-lg leading-none">+</span>
                <span class="mt-0.5 text-[9px]">Agregar</span>
            </button>
        </div>

        <div
            v-if="items.length < max"
            class="cursor-pointer rounded-lg border border-dashed border-zinc-200 px-3 py-2.5 text-center transition hover:border-accent/50"
            @click="openFilePicker"
            @dragover.prevent
            @drop.prevent="onAddDrop"
        >
            <p class="text-xs text-ink-secondary">Arrastra fotos aquí · JPG, PNG, WebP</p>
            <input ref="fileInput" type="file" multiple accept="image/*" class="hidden" @change="onFileChange" />
        </div>
    </div>
</template>

<script setup>
import { usePage } from '@inertiajs/vue3';
import { computed, onMounted, ref, watch } from 'vue';

const props = defineProps({
    existingImages: { type: Array, default: () => [] },
    isEdit: { type: Boolean, default: false },
    max: { type: Number, default: 8 },
    tips: { type: Array, default: () => [] },
});

const emit = defineEmits(['update:payload']);

const page = usePage();
const brandName = computed(() => page.props.brand?.name ?? 'Mi Ropa');

const fileInput = ref(null);
const items = ref([]);
const removedIds = ref([]);
const dragKey = ref(null);
const dragOverIndex = ref(null);

function makeKey() {
    return `k-${Date.now()}-${Math.random().toString(36).slice(2, 9)}`;
}

function initFromExisting() {
    if (! props.existingImages.length) {
        return;
    }

    const sorted = [...props.existingImages].sort((a, b) => {
        if (a.is_primary && ! b.is_primary) {
            return -1;
        }
        if (! a.is_primary && b.is_primary) {
            return 1;
        }

        return (a.position ?? 0) - (b.position ?? 0);
    });

    items.value = sorted.map(img => ({
        key: `existing-${img.id}`,
        existingId: img.id,
        file: null,
        preview: img.url,
    }));
}

onMounted(initFromExisting);

function openFilePicker() {
    fileInput.value?.click();
}

function addFiles(fileList) {
    const files = Array.from(fileList).filter(f => f.type.startsWith('image/'));
    const remaining = props.max - items.value.length;

    files.slice(0, remaining).forEach(file => {
        items.value.push({
            key: makeKey(),
            existingId: null,
            file,
            preview: URL.createObjectURL(file),
        });
    });
}

function onFileChange(e) {
    addFiles(e.target.files);
    e.target.value = '';
}

function onAddDrop(e) {
    addFiles(e.dataTransfer.files);
}

function removeItem(key) {
    const item = items.value.find(i => i.key === key);

    if (! item) {
        return;
    }

    if (item.existingId) {
        removedIds.value.push(item.existingId);
    }

    if (item.file) {
        URL.revokeObjectURL(item.preview);
    }

    items.value = items.value.filter(i => i.key !== key);
}

function onDragStart(key, event) {
    dragKey.value = key;
    event.dataTransfer.effectAllowed = 'move';
}

function onDragOver(index) {
    if (dragKey.value !== null) {
        dragOverIndex.value = index;
    }
}

function onDragLeave(index) {
    if (dragOverIndex.value === index) {
        dragOverIndex.value = null;
    }
}

function reorderItems(fromKey, toIndex) {
    const list = [...items.value];
    const fromIndex = list.findIndex(i => i.key === fromKey);

    if (fromIndex === -1 || fromIndex === toIndex) {
        return;
    }

    const [moved] = list.splice(fromIndex, 1);
    list.splice(toIndex, 0, moved);
    items.value = list;
}

function onDrop(toIndex) {
    if (dragKey.value) {
        reorderItems(dragKey.value, toIndex);
    }

    onDragEnd();
}

function onDragEnd() {
    dragKey.value = null;
    dragOverIndex.value = null;
}

function buildPayload() {
    const newFiles = [];
    const newIndexByKey = {};

    items.value.forEach(item => {
        if (item.file) {
            newIndexByKey[item.key] = newFiles.length;
            newFiles.push(item.file);
        }
    });

    const firstItem = items.value[0] ?? null;
    let primaryImage = null;

    if (firstItem) {
        primaryImage = firstItem.existingId
            ? `e:${firstItem.existingId}`
            : `n:${newIndexByKey[firstItem.key]}`;
    }

    if (props.isEdit) {
        const imageOrder = items.value.map(item => {
            if (item.existingId) {
                return `e:${item.existingId}`;
            }

            return `n:${newIndexByKey[item.key]}`;
        });

        return {
            mode: 'edit',
            files: newFiles,
            primaryIndex: 0,
            imageOrder,
            primaryImage,
            removeIds: [...removedIds.value],
        };
    }

    return {
        mode: 'create',
        files: items.value.map(i => i.file).filter(Boolean),
        primaryIndex: 0,
        imageOrder: [],
        primaryImage: null,
        removeIds: [],
    };
}

watch([items, removedIds], () => {
    emit('update:payload', buildPayload());
}, { deep: true, immediate: true });

defineExpose({ buildPayload });
</script>
