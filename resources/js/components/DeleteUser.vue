<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import { useTemplateRef } from 'vue';
import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';

const passwordInput = useTemplateRef('passwordInput');
</script>

<template>
    <div class="space-y-6">
        <Heading
            variant="small"
            title="Delete account"
            description="Delete your account and all of its resources"
        />
        <div
            class="space-y-4 rounded-lg border border-red-500/20 bg-red-500/5 p-4"
        >
            <div class="relative space-y-0.5 text-red-500">
                <p class="font-medium">Warning</p>
                <p class="text-sm">
                    Please proceed with caution, this cannot be undone.
                </p>
            </div>
            <Dialog>
                <DialogTrigger as-child>
                    <button
                        type="button"
                        class="bg-red-600 hover:bg-red-700 text-white font-label-md text-label-md py-3 px-6 rounded transition-colors flex items-center justify-center cursor-pointer"
                        data-test="delete-user-button"
                    >
                        Delete account
                    </button>
                </DialogTrigger>
                <DialogContent class="bg-vault-surface-container-lowest border border-vault-outline-variant text-vault-on-surface">
                    <Form
                        v-bind="ProfileController.destroy.form()"
                        reset-on-success
                        @error="() => passwordInput?.focus()"
                        :options="{
                            preserveScroll: true,
                        }"
                        class="space-y-6"
                        v-slot="{ errors, processing, reset, clearErrors }"
                    >
                        <DialogHeader class="space-y-3">
                            <DialogTitle class="text-lg font-bold text-vault-on-surface"
                                >Are you sure you want to delete your
                                account?</DialogTitle
                            >
                            <DialogDescription class="text-sm text-vault-on-surface-variant">
                                Once your account is deleted, all of its
                                resources and data will also be permanently
                                deleted. Please enter your password to confirm
                                you would like to permanently delete your
                                account.
                            </DialogDescription>
                        </DialogHeader>

                        <div class="grid gap-2">
                            <label for="password" class="sr-only"
                                >Password</label
                            >
                            <PasswordInput
                                id="password"
                                name="password"
                                ref="passwordInput"
                                placeholder="Password"
                            />
                            <InputError :message="errors.password" />
                        </div>

                        <DialogFooter class="gap-2">
                            <DialogClose as-child>
                                <button
                                    type="button"
                                    class="bg-vault-surface-container-lowest border border-vault-outline-variant text-vault-on-surface font-label-md text-label-md py-3 px-6 rounded hover:bg-vault-surface-container-low transition-colors duration-200 cursor-pointer"
                                    @click="
                                        () => {
                                            clearErrors();
                                            reset();
                                        }
                                    "
                                >
                                    Cancel
                                </button>
                            </DialogClose>

                            <button
                                type="submit"
                                class="bg-red-600 hover:bg-red-700 text-white font-label-md text-label-md py-3 px-6 rounded transition-colors flex items-center justify-center cursor-pointer"
                                :disabled="processing"
                                data-test="confirm-delete-user-button"
                            >
                                Delete account
                            </button>
                        </DialogFooter>
                    </Form>
                </DialogContent>
            </Dialog>
        </div>
    </div>
</template>
