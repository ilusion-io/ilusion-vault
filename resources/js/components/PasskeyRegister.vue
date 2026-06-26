<script setup lang="ts">
import { usePasskeyRegister } from '@laravel/passkeys/vue';
import { ref } from 'vue';
import InputError from '@/components/InputError.vue';

const emit = defineEmits<{
    success: [];
}>();

const getDefaultPasskeyName = () => {
    const ua = navigator.userAgent;

    const browser = ['Chrome', 'Firefox', 'Safari', 'Edge', 'Opera'].find(
        (browser) => new RegExp(browser).test(ua),
    );

    const os = ['iPhone', 'iPad', 'Android', 'Mac', 'Windows'].find((os) =>
        new RegExp(os).test(ua),
    );

    return [browser, os].filter(Boolean).join(' on ') || '';
};

const name = ref(getDefaultPasskeyName());
const showForm = ref(false);

const { register, isLoading, error, isSupported } = usePasskeyRegister({
    onSuccess: () => {
        name.value = '';
        showForm.value = false;
        emit('success');
    },
});

const handleSubmit = async (event: Event) => {
    event.preventDefault();

    if (!name.value.trim()) {
        return;
    }

    await register(name.value);
};

const handleCancel = () => {
    showForm.value = false;
    name.value = '';
};
</script>

<template>
    <div v-if="!isSupported" class="text-sm text-vault-on-surface-variant">
        Passkeys are not supported in this browser.
    </div>

    <button
        v-else-if="!showForm"
        type="button"
        class="bg-vault-surface-container-lowest border border-vault-outline-variant text-vault-on-surface font-label-md text-label-md py-3 px-6 rounded hover:bg-vault-surface-container-low transition-colors duration-200 cursor-pointer"
        @click="showForm = true"
    >
        Add passkey
    </button>

    <form
        v-else
        @submit="handleSubmit"
        class="space-y-4 rounded-lg border border-vault-outline-variant bg-vault-surface-container-lowest p-4"
    >
        <div class="grid gap-2">
            <label for="passkey-name" class="font-label-sm text-label-sm uppercase text-vault-secondary select-none">Passkey name</label>
            <input
                id="passkey-name"
                type="text"
                v-model="name"
                placeholder="e.g., MacBook Pro, iPhone"
                class="w-full bg-vault-surface-container-lowest border border-vault-outline-variant rounded py-3 px-4 font-body-md text-body-md text-vault-on-surface focus:outline-none focus:border-vault-primary focus:ring-1 focus:ring-vault-primary transition-all placeholder:text-vault-outline"
                autofocus
            />
            <p class="text-xs text-vault-on-surface-variant">
                A name helps you identify this passkey later.
            </p>
        </div>

        <InputError v-if="error" :message="error" />

        <div class="flex gap-2">
            <button
                type="submit"
                :disabled="isLoading || !name.trim()"
                class="bg-vault-primary text-vault-on-primary font-label-md text-label-md py-3 px-8 rounded hover:bg-vault-primary-container transition-colors flex items-center justify-center gap-2 shadow-[inset_0_-1px_0_rgba(0,0,0,0.2)] active:shadow-none active:translate-y-[1px] disabled:opacity-70 disabled:cursor-not-allowed cursor-pointer"
            >
                {{ isLoading ? 'Registering...' : 'Register passkey' }}
            </button>
            <button
                type="button"
                class="bg-vault-surface-container-lowest border border-vault-outline-variant text-vault-on-surface font-label-md text-label-md py-3 px-6 rounded hover:bg-vault-surface-container-low transition-colors duration-200 cursor-pointer"
                @click="handleCancel"
            >
                Cancel
            </button>
        </div>
    </form>
</template>
