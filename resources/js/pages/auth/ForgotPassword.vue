<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import { login } from '@/routes';
import { email } from '@/routes/password';

defineOptions({
    layout: {
        title: 'Forgot password',
        description: 'Enter your email to receive a password reset link',
    },
});

defineProps<{
    status?: string;
}>();
</script>

<template>
    <Head title="Forgot password" />

    <div
        v-if="status"
        class="mb-6 text-center font-label-md text-vault-primary bg-vault-primary/10 py-3 rounded"
    >
        {{ status }}
    </div>

    <Form v-bind="email.form()" v-slot="{ errors, processing }" class="flex flex-col gap-5">
        <div class="flex flex-col gap-2">
            <label class="font-label-sm text-label-sm uppercase text-vault-secondary select-none" for="email">Email Address</label>
            <input
                id="email"
                type="email"
                name="email"
                autocomplete="off"
                required
                autofocus
                class="w-full bg-vault-surface-container-low border border-vault-outline-variant rounded py-3 px-4 font-body-md text-body-md text-vault-on-surface focus:outline-none focus:border-vault-primary focus:ring-1 focus:ring-vault-primary transition-all placeholder:text-vault-outline"
                placeholder="name@example.com"
            />
            <div v-if="errors.email" class="font-label-sm text-vault-error mt-1">{{ errors.email }}</div>
        </div>

        <button
            type="submit"
            :disabled="processing"
            class="mt-4 w-full bg-vault-primary text-vault-on-primary font-label-md text-label-md py-3 px-8 rounded hover:bg-vault-primary-container transition-colors flex items-center justify-center gap-2 shadow-[inset_0_-1px_0_rgba(0,0,0,0.2)] disabled:opacity-70 disabled:cursor-not-allowed"
        >
            <span v-if="processing" class="animate-spin h-4 w-4 border-2 border-vault-on-primary border-t-transparent rounded-full mr-1"></span>
            Send Link
            <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
        </button>

        <div class="mt-4 pt-6 border-t border-vault-outline-variant/50 text-center">
            <p class="font-body-md text-[13px] text-vault-on-surface-variant">
                Or, return to
                <Link :href="login()" class="font-label-md text-vault-primary hover:text-vault-primary-container ml-1 transition-colors">
                    log in
                </Link>
            </p>
        </div>
    </Form>
</template>

<style scoped>
.font-label-md {
    font-family: 'Inter', sans-serif;
    font-size: 13px;
    font-weight: 600;
    line-height: 1;
    letter-spacing: 0.05em;
}
.font-label-sm {
    font-family: 'Inter', sans-serif;
    font-size: 11px;
    font-weight: 600;
    line-height: 1;
    letter-spacing: 0.08em;
}
.font-body-md {
    font-family: 'Inter', sans-serif;
    font-weight: 400;
}
</style>
