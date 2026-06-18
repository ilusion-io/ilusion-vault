<script setup lang="ts">
import { ref, nextTick } from 'vue';
import { Form, Head } from '@inertiajs/vue3';
import { store } from '@/routes/two-factor/login';

defineOptions({
    layout: {
        title: 'Two-Factor Verification',
        description: 'Verify your identity using your authenticator application or a recovery code.',
    },
});

const recovery = ref(false);
const codeInput = ref<HTMLInputElement | null>(null);
const recoveryInput = ref<HTMLInputElement | null>(null);

const toggleRecovery = async () => {
    recovery.value = !recovery.value;
    await nextTick();

    if (recovery.value) {
        recoveryInput.value?.focus();
    } else {
        codeInput.value?.focus();
    }
};
</script>

<template>
    <Head title="Two-Factor Verification" />

    <div class="mb-4 text-sm text-vault-secondary text-center">
        <template v-if="!recovery">
            Please confirm access to your account by entering the authentication code provided by your authenticator application.
        </template>
        <template v-else>
            Please confirm access to your account by entering one of your emergency recovery codes.
        </template>
    </div>

    <Form
        v-bind="store.form()"
        v-slot="{ errors, processing }"
        class="flex flex-col gap-5"
    >
        <!-- Authenticator Code Input -->
        <div v-if="!recovery" class="flex flex-col gap-2">
            <label class="font-label-sm text-label-sm uppercase text-vault-secondary select-none" for="code">
                Authenticator Code
            </label>
            <input
                id="code"
                ref="codeInput"
                type="text"
                name="code"
                inputmode="numeric"
                pattern="[0-9]*"
                autofocus
                autocomplete="one-time-code"
                class="w-full bg-vault-surface-container-low border border-vault-outline-variant rounded py-3 px-4 font-mono text-center text-lg tracking-[0.3em] text-vault-on-surface focus:outline-none focus:border-vault-primary focus:ring-1 focus:ring-vault-primary transition-all placeholder:text-vault-outline"
                placeholder="000000"
            />
            <div v-if="errors.code" class="font-label-sm text-vault-error mt-1">{{ errors.code }}</div>
        </div>

        <!-- Recovery Code Input -->
        <div v-else class="flex flex-col gap-2">
            <label class="font-label-sm text-label-sm uppercase text-vault-secondary select-none" for="recovery_code">
                Recovery Code
            </label>
            <input
                id="recovery_code"
                ref="recoveryInput"
                type="text"
                name="recovery_code"
                autocomplete="one-time-code"
                class="w-full bg-vault-surface-container-low border border-vault-outline-variant rounded py-3 px-4 font-mono text-body-md text-vault-on-surface focus:outline-none focus:border-vault-primary focus:ring-1 focus:ring-vault-primary transition-all placeholder:text-vault-outline"
                placeholder="abcdef-12345"
            />
            <div v-if="errors.recovery_code" class="font-label-sm text-vault-error mt-1">{{ errors.recovery_code }}</div>
        </div>

        <!-- Form Actions -->
        <div class="flex flex-col gap-3">
            <button
                type="submit"
                :disabled="processing"
                class="w-full bg-vault-primary text-vault-on-primary font-label-md text-label-md py-3 px-8 rounded hover:bg-vault-primary-container transition-colors flex items-center justify-center gap-2 shadow-[inset_0_-1px_0_rgba(0,0,0,0.2)] disabled:opacity-70 disabled:cursor-not-allowed cursor-pointer"
            >
                <span v-if="processing" class="animate-spin h-4 w-4 border-2 border-vault-on-primary border-t-transparent rounded-full mr-1"></span>
                Verify
            </button>

            <button
                type="button"
                @click="toggleRecovery"
                class="text-center font-label-sm text-[11px] text-vault-primary hover:text-vault-primary-container transition-colors py-2 uppercase cursor-pointer"
            >
                {{ recovery ? 'Use an authenticator code' : 'Use a recovery code' }}
            </button>
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
