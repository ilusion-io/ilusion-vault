<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { home } from '@/routes';

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div class="vault-light bg-vault-background text-vault-on-background min-h-screen flex flex-col font-body-md antialiased selection:bg-[#dbe1ff] selection:text-[#00174b] relative overflow-x-hidden">
        <div class="absolute inset-0 bg-dot-grid pointer-events-none z-0"></div>

        <header class="fixed top-0 left-0 right-0 z-50 flex justify-between items-center px-6 md:px-12 py-4 bg-vault-surface/80 backdrop-blur-xl border-b border-vault-outline-variant shadow-sm transition-all duration-300">
            <div class="flex items-center gap-8 max-w-[1200px] w-full mx-auto">
                <div class="flex items-center gap-8 flex-1">
                    <Link class="flex items-center gap-2 font-headline-md text-headline-md font-bold text-vault-on-surface hover:opacity-90" :href="home()">
                        <img src="/ilusion-logo.png" alt="Ilusion" class="w-10 h-10 md:w-12 md:h-12 object-contain" />
                        Ilusion
                    </Link>
                    <nav class="hidden md:flex gap-6">
                        <a class="font-body-md text-body-md text-vault-on-surface-variant hover:text-vault-primary transition-colors duration-200" href="#">API</a>
                        <a class="font-body-md text-body-md text-vault-on-surface-variant hover:text-vault-primary transition-colors duration-200" href="#">Pricing</a>
                    </nav>
                </div>
            </div>
        </header>

        <main class="flex-grow flex flex-col items-center justify-center px-4 sm:px-6 md:px-12 pt-24 pb-12 relative z-10 w-full max-w-[1200px] mx-auto">
            <div class="w-full max-w-[480px] bg-vault-surface-container-lowest border border-vault-outline-variant rounded p-6 md:p-8 shadow-sm">
                <div class="flex flex-col items-center gap-2 mb-8">
                    <h1 class="font-headline-md text-headline-md font-bold text-vault-on-surface">{{ title }}</h1>
                    <p class="font-body-md text-body-md text-vault-on-surface-variant text-center">
                        {{ description }}
                    </p>
                </div>
                <slot />
            </div>
        </main>
    </div>
</template>

<style scoped>
.bg-dot-grid {
    background-image: radial-gradient(#c3c6d7 1px, transparent 1px);
    background-size: 24px 24px;
    background-position: -11px -11px;
    opacity: 0.3;
}
.font-headline-md {
    font-family: 'Inter', sans-serif;
    font-size: 20px;
    font-weight: 600;
    line-height: 1.2;
    letter-spacing: -0.01em;
}
.font-body-md {
    font-family: 'Inter', sans-serif;
    font-size: 14px;
    font-weight: 400;
    line-height: 1.5;
}
.text-headline-md { font-size: 24px; line-height: 32px; }
.text-body-md { font-size: 14px; line-height: 20px; }
</style>
