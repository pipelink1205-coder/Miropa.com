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

                <IdentityVerificationNotice class="mt-6" />

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

                    <DocumentSideUpload
                        id="document_front"
                        v-model="form.document_front"
                        :label="sideLabels.front.title"
                        :hint="sideLabels.front.hint"
                        :error="form.errors.document_front"
                    />

                    <DocumentSideUpload
                        id="document_back"
                        v-model="form.document_back"
                        :label="sideLabels.back.title"
                        :hint="sideLabels.back.hint"
                        :error="form.errors.document_back"
                    />

                    <ul class="space-y-2 rounded-xl bg-surface-muted px-4 py-3 text-xs text-ink-secondary">
                        <li>• Sube una foto por cada cara; deben verse datos y bordes completos.</li>
                        <li>• Evita reflejos, sombras y recortes.</li>
                        <li>• JPG, PNG o PDF · máximo 5 MB por archivo.</li>
                        <li>• La revisión puede tardar hasta 48 horas hábiles.</li>
                    </ul>

                    <button
                        type="submit"
                        class="btn-primary w-full"
                        :disabled="form.processing || ! canSubmit"
                    >
                        {{ form.processing ? 'Enviando...' : 'Enviar documento' }}
                    </button>
                </form>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import DocumentSideUpload from '@/Components/DocumentSideUpload.vue';
import IdentityVerificationNotice from '@/Components/IdentityVerificationNotice.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const form = useForm({
    document_type: 'cedula',
    document_front: null,
    document_back: null,
});

const sideLabels = computed(() => {
    if (form.document_type === 'passport') {
        return {
            front: {
                title: 'Página principal (foto y datos)',
                hint: 'La página donde aparece tu foto y nombre.',
            },
            back: {
                title: 'Reverso o segunda página',
                hint: 'La otra página visible del pasaporte.',
            },
        };
    }

    return {
        front: {
            title: 'Frente del documento',
            hint: 'La cara con tu foto y número de documento.',
        },
        back: {
            title: 'Reverso del documento',
            hint: 'La cara opuesta con huella, código o datos adicionales.',
        },
    };
});

const canSubmit = computed(() =>
    form.document_front instanceof File && form.document_back instanceof File,
);

function submit() {
    form.post(route('identity.verify.store'), {
        forceFormData: true,
        preserveScroll: true,
    });
}
</script>
