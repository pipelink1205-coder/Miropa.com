<template>
    <AppLayout title="Cómo funciona">
        <div class="container-app max-w-3xl py-12">
            <h1 class="text-3xl font-bold text-ink">Cómo funciona Mi Ropa</h1>
            <p class="mt-2 text-ink-secondary">Compra y vende moda de segunda vida con confianza.</p>

            <div class="mt-10 space-y-8">
                <section>
                    <h2 class="text-lg font-semibold text-ink">Para comprar</h2>
                    <ol class="mt-3 list-inside list-decimal space-y-2 text-sm text-ink-secondary">
                        <li>Explora Moda por departamento (Mujer, Hombre, Niños) o usa los filtros.</li>
                        <li>Revisa fotos, talla, color, estado y sello Segunda Vida ♻.</li>
                        <li>Contacta al vendedor por chat para preguntas o coordinar entrega.</li>
                        <li>Compra protegida con pago en plataforma — próximamente.</li>
                    </ol>
                </section>
                <section>
                    <h2 class="text-lg font-semibold text-ink">Para vender</h2>
                    <ol class="mt-3 list-inside list-decimal space-y-2 text-sm text-ink-secondary">
                        <li>Crea tu cuenta y verifica tu celular.</li>
                        <li>Publica con fotos claras, categoría correcta, talla y estado honestos.</li>
                        <li>Responde rápido — las ventas mejoran cuando contestas en menos de 24 h.</li>
                    </ol>
                </section>
                <section class="rounded-xl border border-accent/20 bg-accent-soft p-5">
                    <p class="text-sm text-ink">
                        <strong>Segunda vida:</strong> cada prenda usada evita producción nueva y reduce impacto ambiental. Buscamos dar transparencia con métricas estimadas en cada anuncio Moda.
                    </p>
                </section>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
</script>
