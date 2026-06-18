<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import { logout } from '@/routes';
import { send } from '@/routes/verification';

defineOptions({
    layout: {
        title: 'Email verification',
        description:
            'Please verify your email address by clicking on the link we just emailed to you.',
    },
});

defineProps<{
    status?: string;
}>();
</script>

<template>
    <Head title="Email verification" />

    <div
        v-if="status === 'verification-link-sent'"
        class="mb-6 text-center font-label-md text-vault-primary bg-vault-primary/10 py-3 rounded"
    >
        A new verification link has been sent to the email address you provided
        during registration.
    </div>

    <Form
        v-bind="send.form()"
        class="flex flex-col gap-5 text-center"
        v-slot="{ processing }"
    >
        <button
            type="submit"
            :disabled="processing"
            class="w-full bg-vault-surface-container-low border border-vault-outline-variant text-vault-on-surface font-label-md text-label-md py-3 px-8 rounded hover:bg-vault-surface-variant transition-colors flex items-center justify-center gap-2 shadow-[inset_0_-1px_0_rgba(0,0,0,0.1)] disabled:opacity-70 disabled:cursor-not-allowed"
        >
            <span v-if="processing" class="animate-spin h-4 w-4 border-2 border-vault-on-surface border-t-transparent rounded-full mr-1"></span>
            Resend verification email
        </button>

        <div class="mt-4 pt-6 border-t border-vault-outline-variant/50 text-center">
            <Link :href="logout().url" method="post" as="button" type="button" class="font-label-md text-vault-primary hover:text-vault-primary-container transition-colors">
                Log out
            </Link>
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
.font-body-md {
    font-family: 'Inter', sans-serif;
    font-weight: 400;
}
</style>
