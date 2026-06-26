<script setup lang="ts">
import { Head, usePage, Link, router } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import { logout } from '@/routes';
import { toast } from 'vue-sonner';
import { Toaster } from '@/components/ui/sonner';
import { useConfirm } from '@/composables/useConfirm';
import ConfirmModal from '@/components/ConfirmModal.vue';
import axios from 'axios';

const { confirm } = useConfirm();

interface Secret {
    secret_id: string;
    identifier: string | null;
    url: string;
    expiry_date: string;
    burn_on_read: boolean;
    recipient_email: string | null;
    created_at: string;
}

const props = defineProps<{
    secrets: Secret[];
}>();

const localSecrets = ref<Secret[]>([...props.secrets]);

watch(() => props.secrets, (newVal) => {
    localSecrets.value = [...newVal];
}, { deep: true });

const page = usePage();
const user = computed(() => page.props.auth?.user);

const formatDate = (dateStr: string) => {
    return new Date(dateStr).toLocaleString(undefined, {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const copyToClipboard = (text: string) => {
    navigator.clipboard.writeText(text);
    toast.success('Secret link copied to clipboard!');
};

const deleteSecret = async (secretId: string) => {
    const isConfirmed = await confirm({
        title: 'Delete Secret',
        message: 'Are you sure you want to delete this secret? This action is permanent and will delete all associated files.',
        confirmText: 'Delete',
        cancelText: 'Keep it',
        type: 'danger'
    });
    if (isConfirmed) {
        try {
            await axios.delete(`/api/secrets/${secretId}`);
            localSecrets.value = localSecrets.value.filter(s => s.secret_id !== secretId);
            toast.success('Secret deleted successfully.');
        } catch (error: any) {
            toast.error('Failed to delete secret: ' + (error.response?.data?.message || error.message));
        }
    }
};
</script>

<template>
    <Head title="Ilusion - Profile & Secrets" />

    <div class="vault-light bg-vault-background text-vault-on-background min-h-screen flex flex-col font-body-md antialiased selection:bg-[#dbe1ff] selection:text-[#00174b] relative overflow-x-hidden">
        <div class="absolute inset-0 bg-dot-grid pointer-events-none z-0"></div>

        <header class="fixed top-0 left-0 right-0 z-50 flex justify-between items-center px-4 sm:px-6 md:px-12 py-3 md:py-4 bg-vault-surface/80 backdrop-blur-xl border-b border-vault-outline-variant shadow-sm transition-all duration-300">
            <div class="flex items-center gap-4 sm:gap-8 max-w-[75rem] w-full mx-auto">
                <div class="flex items-center gap-4 sm:gap-8 flex-1">
                    <Link class="flex items-center gap-2 font-headline-md text-headline-md font-bold text-vault-on-surface hover:opacity-90" href="/">
                        <img src="/ilusion-logo.png" alt="Ilusion" class="w-10 h-10 md:w-12 md:h-12 object-contain" />
                        Ilusion
                    </Link>
                </div>
                <div class="flex items-center gap-3">
                    <template v-if="user">
                        <span class="font-body-md text-body-md text-vault-on-surface-variant select-none hidden sm:inline-block mr-2">
                            Hello <span class="font-semibold text-vault-on-surface">{{ user.name }}</span>
                        </span>
                        <Link
                            href="/profile"
                            class="bg-vault-surface-container-lowest border border-vault-outline-variant text-vault-on-surface font-label-md text-label-md py-2 px-4 rounded hover:bg-vault-surface-container-low transition-colors duration-200 scale-95 hover:scale-100 ease-in-out inline-flex items-center justify-center mr-2 font-bold"
                        >
                            Profile
                        </Link>
                        <Link
                            :href="logout().url"
                            method="post"
                            as="button"
                            class="bg-vault-surface-container-lowest border border-vault-outline-variant text-vault-on-surface font-label-md text-label-md py-2 px-4 rounded hover:bg-vault-surface-container-low transition-colors duration-200 scale-95 hover:scale-100 ease-in-out inline-flex items-center justify-center"
                        >
                            Logout
                        </Link>
                    </template>
                </div>
            </div>
        </header>

        <main class="flex-grow flex flex-col items-center justify-start px-4 sm:px-6 md:px-12 pt-24 pb-12 relative z-10 w-full max-w-[75rem] mx-auto gap-8">
            <!-- Profile Details Card -->
            <div class="w-full max-w-[60rem] bg-vault-surface-container-lowest border border-vault-outline-variant rounded-xl p-6 md:p-8 shadow-sm flex flex-col md:flex-row items-center gap-6">
                <div class="w-20 h-20 bg-gradient-to-tr from-vault-primary to-vault-secondary rounded-full flex items-center justify-center text-white text-3xl font-bold select-none shadow-md">
                    {{ user?.name ? user.name[0].toUpperCase() : 'U' }}
                </div>
                <div class="flex-grow text-center md:text-left">
                    <h1 class="text-2xl font-bold text-vault-on-surface mb-1">{{ user?.name }}</h1>
                    <p class="text-vault-on-surface-variant font-body-md">{{ user?.email }}</p>
                    <div class="mt-3 inline-flex items-center gap-2 px-3 py-1 bg-vault-surface border border-vault-outline-variant rounded-full text-xs font-semibold text-vault-secondary">
                        <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span>
                        Account Active
                    </div>
                </div>
                <div class="flex shrink-0">
                    <Link
                        href="/settings/profile"
                        class="bg-vault-surface-container-lowest border border-vault-outline-variant text-vault-on-surface font-label-md text-label-md py-2 px-4 rounded hover:bg-vault-surface-container-low transition-colors duration-200 flex items-center gap-2"
                    >
                        <span class="material-symbols-outlined text-[1.125rem]">settings</span>
                        Edit Profile
                    </Link>
                </div>
            </div>

            <!-- Active Secrets Card -->
            <div class="w-full max-w-[60rem] bg-vault-surface-container-lowest border border-vault-outline-variant rounded-xl shadow-sm overflow-hidden">
                <div class="px-6 py-5 border-b border-vault-outline-variant flex justify-between items-center bg-vault-surface/40">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-vault-secondary">vpn_key</span>
                        <h2 class="text-lg font-bold text-vault-on-surface">Active Secrets</h2>
                    </div>
                    <span class="bg-vault-primary/10 text-vault-primary px-2.5 py-0.5 rounded-full text-xs font-semibold">
                        {{ localSecrets.length }} Active
                    </span>
                </div>

                <!-- Empty State -->
                <div v-if="localSecrets.length === 0" class="p-12 text-center flex flex-col items-center justify-center">
                    <span class="material-symbols-outlined text-5xl text-vault-outline-variant mb-4">folder_open</span>
                    <p class="text-vault-on-surface-variant font-medium mb-1">No active secrets found</p>
                    <p class="text-sm text-vault-outline mb-6">Create a new secret to share it securely or store it in your vault.</p>
                    <Link href="/" class="bg-vault-primary text-white font-label-md px-5 py-2.5 rounded hover:bg-vault-primary/95 transition-colors shadow-sm">
                        Secure & Share
                    </Link>
                </div>

                <!-- Secrets Table / Grid -->
                <div v-else class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-vault-surface/30 text-xs font-semibold uppercase tracking-wider text-vault-secondary border-b border-vault-outline-variant">
                                <th class="py-4 px-6">Identifier / ID</th>
                                <th class="py-4 px-6">Created At</th>
                                <th class="py-4 px-6">Expires At</th>
                                <th class="py-4 px-6 text-center">Settings</th>
                                <th class="py-4 px-6 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-vault-outline-variant">
                            <tr v-for="secret in localSecrets" :key="secret.secret_id" class="hover:bg-vault-surface/20 transition-colors">
                                <td class="py-4 px-6 font-mono text-sm text-vault-on-surface max-w-[9.375rem] truncate select-text" :title="secret.identifier || secret.secret_id">
                                    {{ secret.identifier || secret.secret_id }}
                                </td>
                                <td class="py-4 px-6 text-sm text-vault-on-surface-variant">
                                    {{ formatDate(secret.created_at) }}
                                </td>
                                <td class="py-4 px-6 text-sm text-vault-on-surface-variant">
                                    {{ formatDate(secret.expiry_date) }}
                                </td>
                                <td class="py-4 px-6 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <span v-if="secret.burn_on_read" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300" title="Will burn after 1st read">
                                            Burn on Read
                                        </span>
                                        <span v-if="secret.recipient_email" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300" :title="'Emailed to: ' + secret.recipient_email">
                                            Emailed
                                        </span>
                                    </div>
                                </td>
                                <td class="py-4 px-6 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <button @click="copyToClipboard(secret.url)" class="p-2 text-vault-secondary hover:text-vault-primary hover:bg-vault-surface rounded transition-all duration-200" title="Copy Secret Link">
                                            <span class="material-symbols-outlined text-[1.25rem]">content_copy</span>
                                        </button>
                                        <a :href="secret.url" class="p-2 text-vault-secondary hover:text-vault-primary hover:bg-vault-surface rounded transition-all duration-200" target="_blank" title="View Secret">
                                            <span class="material-symbols-outlined text-[1.25rem]">open_in_new</span>
                                        </a>
                                        <button @click="deleteSecret(secret.secret_id)" class="p-2 text-vault-secondary hover:text-vault-error hover:bg-vault-surface rounded transition-all duration-200" title="Delete Secret">
                                            <span class="material-symbols-outlined text-[1.25rem]">delete</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>

        <ConfirmModal />
        <Toaster />
    </div>
</template>
