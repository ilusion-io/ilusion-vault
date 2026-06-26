<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import {
    index as confirmOptions,
    store as confirmStore,
} from '@/actions/Laravel/Passkeys/Http/Controllers/PasskeyConfirmationController';
import PasskeyVerify from '@/components/PasskeyVerify.vue';
import { store } from '@/routes/password/confirm';

defineOptions({
    layout: {
        title: 'Confirm password',
        description:
            'This is a secure area of the application. Please confirm your password before continuing.',
    },
});
</script>

<template>
    <Head title="Confirm password" />

    <PasskeyVerify
        :routes="{
            options: confirmOptions(),
            submit: confirmStore(),
        }"
        label="Confirm with passkey"
        loading-label="Confirming..."
        separator="Or confirm with password"
        class="mb-6"
    />

    <Form
        v-bind="store.form()"
        reset-on-success
        v-slot="{ errors, processing }"
        class="flex flex-col gap-5"
    >
        <div class="flex flex-col gap-2">
            <label class="font-label-sm text-label-sm uppercase text-vault-secondary select-none" for="password">Password</label>
            <input
                id="password"
                type="password"
                name="password"
                class="w-full bg-vault-surface-container-low border border-vault-outline-variant rounded py-3 px-4 font-body-md text-body-md text-vault-on-surface focus:outline-none focus:border-vault-primary focus:ring-1 focus:ring-vault-primary transition-all placeholder:text-vault-outline"
                required
                autocomplete="current-password"
                autofocus
                placeholder="••••••••"
            />
            <div v-if="errors.password" class="font-label-sm text-vault-error mt-1">{{ errors.password }}</div>
        </div>

        <button
            type="submit"
            :disabled="processing"
            class="mt-2 w-full bg-vault-primary text-vault-on-primary font-label-md text-label-md py-3 px-8 rounded hover:bg-vault-primary-container transition-colors flex items-center justify-center gap-2 shadow-[inset_0_-1px_0_rgba(0,0,0,0.2)] disabled:opacity-70 disabled:cursor-not-allowed"
        >
            <span v-if="processing" class="animate-spin h-4 w-4 border-2 border-vault-on-primary border-t-transparent rounded-full mr-1"></span>
            Confirm Password
            <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
        </button>
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
