<template>
    <div class="space-y-3">
        <a
            :href="`/auth/google/redirect`"
            class="flex items-center justify-center gap-2 w-full border border-gray-200 rounded-lg py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 transition"
        >
            <span class="text-lg">G</span>
            Continuar con Google
        </a>
        <a
            :href="`/auth/microsoft/redirect`"
            class="flex items-center justify-center gap-2 w-full border border-gray-200 rounded-lg py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 transition"
        >
            <span class="text-lg font-bold text-blue-600">M</span>
            Continuar con Microsoft
        </a>
    </div>
</template>
