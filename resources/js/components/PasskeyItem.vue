<script setup lang="ts">
import { KeyRound, Trash2 } from '@lucide/vue';
import { ref } from 'vue';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import type { Passkey } from '@/types/auth';

const props = defineProps<{
    passkey: Passkey;
}>();

const emit = defineEmits<{
    remove: [id: number, onError: () => void];
}>();

const isDeleting = ref(false);

const handleDelete = () => {
    isDeleting.value = true;
    emit('remove', props.passkey.id, () => {
        isDeleting.value = false;
    });
};
</script>

<template>
    <div class="flex items-center justify-between border-b border-vault-outline-variant/60 p-4 last:border-b-0">
        <div class="flex items-center gap-4">
            <div
                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-vault-surface-container-low"
            >
                <KeyRound class="h-5 w-5 text-vault-outline" />
            </div>
            <div class="space-y-1">
                <div class="flex items-center gap-2.5">
                    <p class="font-medium tracking-tight text-vault-on-surface">{{ passkey.name }}</p>
                    <span
                        v-if="passkey.authenticator"
                        class="inline-flex items-center gap-1 rounded-md bg-vault-surface-container px-2 py-0.5 text-[11px] font-medium tracking-wide text-vault-secondary uppercase ring-1 ring-vault-outline-variant ring-inset"
                    >
                        {{ passkey.authenticator }}
                    </span>
                </div>
                <p class="text-sm text-vault-on-surface-variant">
                    Added {{ passkey.created_at_diff }}
                    <template v-if="passkey.last_used_at_diff">
                        <span class="mx-1 text-vault-outline/50">/</span>
                        Last used {{ passkey.last_used_at_diff }}
                    </template>
                </p>
            </div>
        </div>

        <Dialog>
            <DialogTrigger as-child>
                <button
                    type="button"
                    class="text-red-500 hover:bg-red-500/10 hover:text-red-600 p-2 rounded transition-colors flex items-center justify-center cursor-pointer"
                >
                    <Trash2 class="h-4 w-4" />
                    <span class="sr-only">Remove</span>
                </button>
            </DialogTrigger>

            <DialogContent class="bg-vault-surface-container-lowest border border-vault-outline-variant text-vault-on-surface">
                <DialogTitle class="text-lg font-bold text-vault-on-surface">Remove passkey</DialogTitle>
                <DialogDescription class="text-sm text-vault-on-surface-variant">
                    Are you sure you want to remove the "{{ passkey.name }}"
                    passkey? You will no longer be able to use it to sign in.
                </DialogDescription>
                <DialogFooter class="gap-2">
                    <DialogClose as-child>
                        <button
                            type="button"
                            class="bg-vault-surface-container-lowest border border-vault-outline-variant text-vault-on-surface font-label-md text-label-md py-3 px-6 rounded hover:bg-vault-surface-container-low transition-colors duration-200 cursor-pointer"
                        >
                            Cancel
                        </button>
                    </DialogClose>
                    <button
                        type="button"
                        class="bg-red-600 hover:bg-red-700 text-white font-label-md text-label-md py-3 px-6 rounded transition-colors flex items-center justify-center cursor-pointer"
                        :disabled="isDeleting"
                        @click="handleDelete"
                    >
                        {{ isDeleting ? 'Removing...' : 'Remove passkey' }}
                    </button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </div>
</template>
