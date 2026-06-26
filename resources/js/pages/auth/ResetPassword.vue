<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import { update } from '@/routes/password';

defineOptions({
    layout: {
        title: 'Reset password',
        description: 'Please enter your new password below',
    },
});

const props = defineProps<{
    token: string;
    email: string;
    passwordRules: string;
}>();

const inputEmail = ref(props.email);
</script>

<template>
    <Head title="Reset password" />

    <Form
        v-bind="update.form()"
        :transform="(data) => ({ ...data, token, email })"
        :reset-on-success="['password', 'password_confirmation']"
        v-slot="{ errors, processing }"
        class="flex flex-col gap-5"
    >
        <div class="flex flex-col gap-2">
            <label class="font-label-sm text-label-sm uppercase text-vault-secondary select-none" for="email">Email</label>
            <input
                id="email"
                type="email"
                name="email"
                autocomplete="email"
                v-model="inputEmail"
                class="w-full bg-vault-surface-container-low border border-vault-outline-variant rounded py-3 px-4 font-body-md text-body-md text-vault-on-surface focus:outline-none focus:border-vault-primary focus:ring-1 focus:ring-vault-primary transition-all placeholder:text-vault-outline"
                readonly
            />
            <div v-if="errors.email" class="font-label-sm text-vault-error mt-1">{{ errors.email }}</div>
        </div>

        <div class="flex flex-col gap-2">
            <label class="font-label-sm text-label-sm uppercase text-vault-secondary select-none" for="password">Password</label>
            <input
                id="password"
                type="password"
                name="password"
                autocomplete="new-password"
                class="w-full bg-vault-surface-container-low border border-vault-outline-variant rounded py-3 px-4 font-body-md text-body-md text-vault-on-surface focus:outline-none focus:border-vault-primary focus:ring-1 focus:ring-vault-primary transition-all placeholder:text-vault-outline"
                autofocus
                placeholder="Password"
            />
            <div v-if="errors.password" class="font-label-sm text-vault-error mt-1">{{ errors.password }}</div>
        </div>

        <div class="flex flex-col gap-2">
            <label class="font-label-sm text-label-sm uppercase text-vault-secondary select-none" for="password_confirmation">Confirm password</label>
            <input
                id="password_confirmation"
                type="password"
                name="password_confirmation"
                autocomplete="new-password"
                class="w-full bg-vault-surface-container-low border border-vault-outline-variant rounded py-3 px-4 font-body-md text-body-md text-vault-on-surface focus:outline-none focus:border-vault-primary focus:ring-1 focus:ring-vault-primary transition-all placeholder:text-vault-outline"
                placeholder="Confirm password"
            />
            <div v-if="errors.password_confirmation" class="font-label-sm text-vault-error mt-1">{{ errors.password_confirmation }}</div>
        </div>

        <button
            type="submit"
            :disabled="processing"
            class="mt-4 w-full bg-vault-primary text-vault-on-primary font-label-md text-label-md py-3 px-8 rounded hover:bg-vault-primary-container transition-colors flex items-center justify-center gap-2 shadow-[inset_0_-1px_0_rgba(0,0,0,0.2)] disabled:opacity-70 disabled:cursor-not-allowed"
        >
            <span v-if="processing" class="animate-spin h-4 w-4 border-2 border-vault-on-primary border-t-transparent rounded-full mr-1"></span>
            Reset Password
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
