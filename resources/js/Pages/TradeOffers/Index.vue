<template>
    <AppLayout title="Trueques">
        <div class="max-w-4xl mx-auto px-4 py-10">
            <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Trueques</h1>
                    <p class="mt-1 text-sm text-gray-500">Propuestas de intercambio presencial entre perfiles verificados.</p>
                </div>
                <Link href="/dashboard" class="text-sm text-accent hover:underline">← Volver al panel</Link>
            </div>

            <div
                v-if="! tradeEligibility.eligible"
                class="mb-6 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900"
            >
                {{ tradeEligibility.reason }}
                <Link href="/cuenta" class="ml-1 font-semibold text-accent hover:underline">Ver requisitos</Link>
            </div>

            <div
                v-if="$page.props.flash?.success"
                class="mb-6 rounded-xl border border-trust/20 bg-trust-soft px-4 py-3 text-sm text-trust"
            >
                {{ $page.props.flash.success }}
            </div>

            <div v-if="offers.length === 0" class="rounded-2xl border border-dashed border-gray-200 bg-white py-16 text-center">
                <p class="text-4xl mb-3">🔄</p>
                <p class="font-medium text-gray-700">Aún no tienes propuestas de trueque</p>
                <p class="mt-2 text-sm text-gray-500 max-w-md mx-auto">
                    Busca anuncios con la etiqueta “Acepta trueque” y propón intercambiar una de tus prendas.
                </p>
                <Link href="/anuncios" class="mt-4 inline-block text-sm font-semibold text-accent hover:underline">
                    Explorar anuncios
                </Link>
            </div>

            <div v-else class="space-y-4">
                <article
                    v-for="offer in offers"
                    :key="offer.id"
                    class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm"
                >
                    <div class="flex flex-wrap items-center justify-between gap-2 mb-4">
                        <span class="rounded-full px-2.5 py-1 text-xs font-semibold" :class="statusClass(offer.status)">
                            {{ statusLabel(offer.status) }}
                        </span>
                        <span class="text-xs text-gray-400">{{ formatDate(offer.created_at) }}</span>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="rounded-xl border border-gray-100 p-3">
                            <p class="text-[10px] font-bold uppercase tracking-wide text-gray-400 mb-2">Quieren</p>
                            <TradeListingMini :listing="offer.target_listing" />
                        </div>
                        <div class="rounded-xl border border-violet-100 bg-violet-50/40 p-3">
                            <p class="text-[10px] font-bold uppercase tracking-wide text-violet-500 mb-2">Ofrecen</p>
                            <TradeListingMini :listing="offer.offered_listing" />
                        </div>
                    </div>

                    <p v-if="offer.message" class="mt-4 text-sm text-gray-600 whitespace-pre-wrap">{{ offer.message }}</p>

                    <p class="mt-3 text-xs text-gray-500">
                        {{ offer.is_proposer ? 'Tú propones' : `Propuesta de ${offer.proposer?.name}` }}
                    </p>

                    <div class="mt-4 flex flex-wrap gap-2">
                        <Link
                            v-if="offer.conversation_id"
                            :href="`/mensajes/${offer.conversation_id}`"
                            class="rounded-xl border border-gray-200 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50"
                        >
                            Abrir chat
                        </Link>

                        <template v-if="offer.status === 'pending' && offer.is_target_owner">
                            <button
                                type="button"
                                class="rounded-xl bg-trust px-4 py-2 text-sm font-semibold text-white hover:opacity-90 disabled:opacity-50"
                                :disabled="updatingId === offer.id"
                                @click="updateStatus(offer, 'accepted')"
                            >
                                Aceptar
                            </button>
                            <button
                                type="button"
                                class="rounded-xl border border-red-200 px-4 py-2 text-sm font-semibold text-red-600 hover:bg-red-50 disabled:opacity-50"
                                :disabled="updatingId === offer.id"
                                @click="updateStatus(offer, 'rejected')"
                            >
                                Rechazar
                            </button>
                        </template>

                        <button
                            v-if="offer.status === 'pending' && offer.is_proposer"
                            type="button"
                            class="rounded-xl border border-gray-200 px-4 py-2 text-sm font-semibold text-gray-600 hover:bg-gray-50 disabled:opacity-50"
                            :disabled="updatingId === offer.id"
                            @click="updateStatus(offer, 'cancelled')"
                        >
                            Cancelar propuesta
                        </button>

                        <template v-if="offer.status === 'accepted'">
                            <button
                                type="button"
                                class="rounded-xl bg-accent px-4 py-2 text-sm font-semibold text-white hover:bg-accent-hover disabled:opacity-50"
                                :disabled="updatingId === offer.id"
                                @click="updateStatus(offer, 'completed')"
                            >
                                Intercambio realizado
                            </button>
                            <button
                                v-if="offer.is_proposer"
                                type="button"
                                class="rounded-xl border border-gray-200 px-4 py-2 text-sm font-semibold text-gray-600 hover:bg-gray-50 disabled:opacity-50"
                                :disabled="updatingId === offer.id"
                                @click="updateStatus(offer, 'cancelled')"
                            >
                                Cancelar acuerdo
                            </button>
                        </template>
                    </div>

                    <p v-if="actionError[offer.id]" class="mt-2 text-xs text-red-600">{{ actionError[offer.id] }}</p>

                    <p
                        v-if="offer.status === 'accepted'"
                        class="mt-3 rounded-lg border border-trust/20 bg-trust-soft px-3 py-2 text-xs text-trust"
                    >
                        Encuentro presencial: elige un lugar público y de día. Cuando intercambien las prendas, marca “Intercambio realizado”.
                    </p>
                </article>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import TradeListingMini from '@/Components/TradeListingMini.vue';
import { Link, router } from '@inertiajs/vue3';
import axios from 'axios';
import { reactive, ref } from 'vue';

defineProps({
    offers: { type: Array, default: () => [] },
    tradeEligibility: { type: Object, default: () => ({ eligible: true, reason: null }) },
});

const updatingId = ref(null);
const actionError = reactive({});

const statusLabels = {
    pending: 'Pendiente',
    accepted: 'Aceptada',
    rejected: 'Rechazada',
    cancelled: 'Cancelada',
    completed: 'Completada',
};

const statusClasses = {
    pending: 'bg-amber-100 text-amber-800',
    accepted: 'bg-trust-soft text-trust',
    rejected: 'bg-red-100 text-red-700',
    cancelled: 'bg-gray-100 text-gray-600',
    completed: 'bg-accent-soft text-accent',
};

function statusLabel(status) {
    return statusLabels[status] ?? status;
}

function statusClass(status) {
    return statusClasses[status] ?? 'bg-gray-100 text-gray-600';
}

function formatDate(iso) {
    if (! iso) return '';
    return new Date(iso).toLocaleString('es-CO', { dateStyle: 'medium', timeStyle: 'short' });
}

async function updateStatus(offer, status) {
    updatingId.value = offer.id;
    actionError[offer.id] = null;

    try {
        await axios.patch(`/trueques/${offer.id}/status`, { status });
        router.reload({ only: ['offers'], preserveScroll: true });
    } catch (error) {
        actionError[offer.id] = error.response?.data?.message ?? 'No se pudo actualizar la propuesta.';
    } finally {
        updatingId.value = null;
    }
}
</script>
