<template>
    <Link
        v-if="interactive && !selectable"
        :href="href"
        class="visual-category-card group block focus-visible:outline-offset-4"
        :class="{ 'visual-category-card--compact': compact, 'visual-category-card--selected': selected }"
    >
        <img :src="image" :alt="title" class="visual-category-card__image" loading="lazy" />
        <div class="visual-category-card__overlay" aria-hidden="true">
            <div class="visual-category-card__content">
                <h3 class="visual-category-card__title">{{ title }}</h3>
                <p v-if="subtitle" class="visual-category-card__subtitle">{{ subtitle }}</p>
                <span v-if="showCta" class="visual-category-card__cta">Explorar →</span>
            </div>
        </div>
        <span class="sr-only">Explorar {{ title }}</span>
    </Link>

    <button
        v-else
        type="button"
        class="visual-category-card group block w-full text-left focus-visible:outline-offset-4"
        :class="{ 'visual-category-card--compact': compact, 'visual-category-card--selected': selected }"
        @click="$emit('select')"
    >
        <img :src="image" :alt="title" class="visual-category-card__image" loading="lazy" />
        <div class="visual-category-card__overlay" aria-hidden="true">
            <div class="visual-category-card__content">
                <h3 class="visual-category-card__title">{{ title }}</h3>
                <p v-if="subtitle" class="visual-category-card__subtitle">{{ subtitle }}</p>
                <span v-if="showCta" class="visual-category-card__cta">Seleccionar →</span>
            </div>
        </div>
    </button>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    title: { type: String, required: true },
    subtitle: { type: String, default: '' },
    image: { type: String, required: true },
    href: { type: String, default: '#' },
    compact: { type: Boolean, default: false },
    interactive: { type: Boolean, default: true },
    selectable: { type: Boolean, default: false },
    selected: { type: Boolean, default: false },
    showCta: { type: Boolean, default: true },
});

defineEmits(['select']);
</script>

<style scoped>
.visual-category-card {
    position: relative;
    height: 176px;
    overflow: hidden;
    border-radius: 16px;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
    transition: transform 0.25s ease, box-shadow 0.25s ease;
    border: 2px solid transparent;
}

.visual-category-card--compact {
    height: 152px;
    border-radius: 14px;
}

.visual-category-card--compact .visual-category-card__title {
    font-size: 1.125rem;
}

.visual-category-card--compact .visual-category-card__subtitle {
    font-size: 0.75rem;
}

.visual-category-card--selected {
    border-color: rgb(79 70 229);
    box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.15);
}

.visual-category-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
}

.visual-category-card__image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.35s ease;
}

.visual-category-card:hover .visual-category-card__image {
    transform: scale(1.04);
}

.visual-category-card__overlay {
    position: absolute;
    inset: 0;
    display: flex;
    align-items: flex-end;
    background: linear-gradient(to top, rgba(0, 0, 0, 0.62) 0%, rgba(0, 0, 0, 0.08) 55%, transparent 100%);
    padding: 1rem 1.125rem;
}

.visual-category-card__content {
    color: #fff;
}

.visual-category-card__title {
    font-size: 1.375rem;
    font-weight: 600;
    line-height: 1.2;
    letter-spacing: -0.01em;
    color: #fff;
}

.visual-category-card__subtitle {
    margin-top: 0.15rem;
    font-size: 0.8125rem;
    line-height: 1.35;
    color: rgba(255, 255, 255, 0.88);
}

.visual-category-card__cta {
    display: inline-block;
    margin-top: 0.35rem;
    font-size: 0.75rem;
    font-weight: 600;
    color: rgba(255, 255, 255, 0.95);
    opacity: 0.85;
    transition: opacity 0.2s ease;
}

.visual-category-card:hover .visual-category-card__cta {
    opacity: 1;
}

@media (max-width: 640px) {
    .visual-category-card {
        height: 152px;
    }

    .visual-category-card__title {
        font-size: 1.25rem;
    }
}
</style>
