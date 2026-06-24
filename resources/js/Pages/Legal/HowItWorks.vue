<template>
    <LegalPageShell
        title="Cómo funciona Mi Ropa"
        subtitle="Compra y vende moda de segunda vida con confianza."
        current="how"
    >
        <section>
            <h2 class="text-lg font-semibold text-ink">Para comprar</h2>
            <ol class="mt-3 list-inside list-decimal space-y-2">
                <li>Explora Moda por departamento (Mujer, Hombre, Niños) o usa universos y filtros.</li>
                <li>Revisa fotos, talla, color, estado y sello Segunda Vida ♻ con impacto estimado.</li>
                <li>Contacta al vendedor por chat para preguntas o coordinar entrega.</li>
                <li>Compra protegida con pago en plataforma — próximamente (mientras tanto, acuerda el pago directamente).</li>
            </ol>
        </section>

        <section>
            <h2 class="text-lg font-semibold text-ink">Para vender</h2>
            <ol class="mt-3 list-inside list-decimal space-y-2">
                <li>Crea tu cuenta, acepta los <Link href="/terminos" class="text-accent hover:underline">Términos</Link> y verifica tu celular.</li>
                <li>Publica con el asistente Moda: categoría correcta, fotos claras, talla y estado honestos.</li>
                <li>Elige hasta 3 universos (Vintage, Básicos, etc.) para que te encuentren en la home.</li>
                <li>Responde rápido — las ventas mejoran cuando contestas en menos de 24 h.</li>
            </ol>
        </section>

        <section>
            <h2 class="text-lg font-semibold text-ink">Modos de venta</h2>
            <div class="mt-3 grid gap-3 sm:grid-cols-2">
                <div class="rounded-xl border border-zinc-200 p-4">
                    <p class="font-medium text-ink">Compra protegida</p>
                    <p class="mt-1 text-xs">Pago dentro de la app con mediación (próximamente).</p>
                </div>
                <div class="rounded-xl border border-zinc-200 p-4">
                    <p class="font-medium text-ink">Trato directo</p>
                    <p class="mt-1 text-xs">Coordinas pago y entrega por chat, fuera de la plataforma.</p>
                </div>
            </div>
        </section>

        <section>
            <h2 class="text-lg font-semibold text-ink">Confianza y seguridad</h2>
            <ul class="mt-3 list-inside list-disc space-y-1">
                <li>Verificación de correo y teléfono obligatoria para publicar.</li>
                <li>Verificación de identidad opcional con badge visible en tu perfil.</li>
                <li>Reportes de anuncios revisados por el equipo de moderación.</li>
            </ul>
        </section>

        <section class="rounded-xl border border-accent/20 bg-accent-soft p-5">
            <p class="text-ink">
                <strong>Segunda vida:</strong> cada prenda usada evita producción nueva y reduce impacto ambiental.
                Mostramos métricas estimadas en anuncios Moda; no son certificaciones oficiales.
            </p>
        </section>
    </LegalPageShell>
</template>

<script setup>
import LegalPageShell from '@/Components/Legal/LegalPageShell.vue';
import { Link } from '@inertiajs/vue3';
</script>
