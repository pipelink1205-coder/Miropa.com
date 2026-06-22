<template>
    <AppLayout title="Verificar identidad">
        <div class="container-app py-10 md:py-14">
            <div class="mx-auto max-w-xl">
                <Link
                    href="/cuenta"
                    class="mb-6 inline-flex items-center gap-1 text-sm font-medium text-ink-secondary transition hover:text-accent"
                >
                    ← Volver a Mi cuenta
                </Link>

                <h1 class="text-section-title">Verificar identidad</h1>
                <p class="mt-2 text-ink-secondary">
                    Sube una foto clara de tu documento. Un administrador lo revisará y, si es aprobado, verás el badge
                    <strong class="text-ink">✓ Identidad</strong> en tu perfil.
                </p>

                <div
                    v-if="$page.props.flash?.success"
                    class="mt-6 rounded-2xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-sm text-emerald-800"
                    role="status"
                >
                    {{ $page.props.flash.success }}
                </div>

                <form
                    @submit.prevent="submit"
                    class="card-premium mt-8 space-y-6 p-6 md:p-8"
                    enctype="multipart/form-data"
                >
                    <div>
                        <label for="document_type" class="mb-2 block text-sm font-semibold text-ink">
                            Tipo de documento
                        </label>
                        <select
                            id="document_type"
                            v-model="form.document_type"
                            class="w-full rounded-xl border border-zinc-200 bg-surface-raised px-4 py-3 text-sm text-ink focus:border-accent focus:outline-none focus:ring-2 focus:ring-accent/20"
                            required
                        >
                            <option value="cedula">Cédula de ciudadanía</option>
                            <option value="foreign_id">Cédula de extranjería</option>
                            <option value="passport">Pasaporte</option>
                        </select>
                        <p v-if="form.errors.document_type" class="mt-1 text-xs text-red-600">
                            {{ form.errors.document_type }}
                        </p>
                    </div>

                    <div>
                        <label for="document" class="mb-2 block text-sm font-semibold text-ink">
                            Archivo del documento
                        </label>
                        <div
                            class="rounded-2xl border-2 border-dashed border-zinc-200 bg-surface-muted/50 p-6 text-center transition"
                            :class="previewUrl ? 'border-accent/40 bg-accent-soft/30' : 'hover:border-zinc-300'"
                        >
                            <input
                                id="document"
                                type="file"
                                accept=".jpg,.jpeg,.png,.pdf,image/jpeg,image/png,application/pdf"
                                class="sr-only"
                                @change="onFileChange"
                            />
                            <label for="document" class="cursor-pointer">
                                <svg class="mx-auto h-10 w-10 text-ink-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                </svg>
                                <p class="mt-3 text-sm font-medium text-ink">
                                    {{ fileLabel }}
                                </p>
                                <p class="mt-1 text-xs text-ink-muted">JPG, PNG o PDF · máximo 5 MB</p>
                            </label>

                            <img
                                v-if="previewUrl"
                                :src="previewUrl"
                                alt="Vista previa del documento"
                                class="mx-auto mt-4 max-h-48 rounded-xl border border-zinc-200 object-contain"
                            />
                        </div>
                        <p v-if="form.errors.document" class="mt-1 text-xs text-red-600">
                            {{ form.errors.document }}
                        </p>
                    </div>

                    <ul class="space-y-2 rounded-xl bg-surface-muted px-4 py-3 text-xs text-ink-secondary">
                        <li>• La imagen debe ser legible (sin reflejos ni cortes).</li>
                        <li>• Solo usamos el documento para verificar tu identidad.</li>
                        <li>• La revisión puede tardar hasta 48 horas hábiles.</li>
                    </ul>

                    <button
                        type="submit"
                        class="btn-primary w-full"
                        :disabled="form.processing || ! form.document"
                    >
                        {{ form.processing ? 'Enviando...' : 'Enviar documento' }}
                    </button>
                </form>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const form = useForm({
    document_type: 'cedula',
    document: null,
});

const previewUrl = ref(null);

const fileLabel = computed(() => {
    if (form.document instanceof File) {
        return form.document.name;
    }

    return 'Haz clic para seleccionar archivo';
});

function onFileChange(event) {
    const file = event.target.files?.[0] ?? null;
    form.document = file;

    if (previewUrl.value) {
        URL.revokeObjectURL(previewUrl.value);
        previewUrl.value = null;
    }

    if (file && file.type.startsWith('image/')) {
        previewUrl.value = URL.createObjectURL(file);
    }
}

function submit() {
    form.post(route('identity.verify.store'), {
        forceFormData: true,
        preserveScroll: true,
    });
}
</script>
