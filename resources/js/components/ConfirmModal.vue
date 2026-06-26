<script setup lang="ts">
import { useConfirm } from '@/composables/useConfirm';

const { isVisible, options, handleConfirm, handleCancel } = useConfirm();
</script>

<template>
  <Transition
    enter-active-class="ease-out duration-300"
    enter-from-class="opacity-0"
    enter-to-class="opacity-100"
    leave-active-class="ease-in duration-200"
    leave-from-class="opacity-100"
    leave-to-class="opacity-0"
  >
    <div v-if="isVisible" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm select-none">
      <div 
        class="w-full max-w-[28rem] bg-vault-surface-container-lowest border border-vault-outline-variant rounded-xl shadow-2xl overflow-hidden animate-[scaleUp_0.2s_ease-out] text-vault-on-surface"
        @click.stop
      >
        <div class="p-6">
          <div class="flex items-start gap-4">
            <div 
              class="flex-shrink-0 w-12 h-12 rounded-full flex items-center justify-center"
              :class="{
                'bg-red-50 text-red-600 dark:bg-red-950/30 dark:text-red-400': options.type === 'danger',
                'bg-amber-50 text-amber-600 dark:bg-amber-950/30 dark:text-amber-400': options.type === 'warning',
                'bg-blue-50 text-blue-600 dark:bg-blue-950/30 dark:text-blue-400': options.type === 'info',
              }"
            >
              <span class="material-symbols-outlined text-[1.75rem]">
                {{ options.type === 'danger' ? 'delete_forever' : options.type === 'warning' ? 'warning' : 'info' }}
              </span>
            </div>
            <div class="flex-1">
              <h3 class="text-lg font-bold font-headline-md tracking-tight leading-6 mb-2">
                {{ options.title }}
              </h3>
              <p class="text-sm text-vault-on-surface-variant leading-relaxed">
                {{ options.message }}
              </p>
            </div>
          </div>
        </div>
        
        <div class="px-6 py-4 bg-vault-surface/40 border-t border-vault-outline-variant/65 flex justify-end gap-3">
          <button
            type="button"
            @click="handleCancel"
            class="px-5 py-2.5 rounded font-label-md text-sm bg-vault-surface-container-low border border-vault-outline-variant text-vault-on-surface hover:bg-vault-surface-container-high transition-colors"
          >
            {{ options.cancelText }}
          </button>
          <button
            type="button"
            @click="handleConfirm"
            class="px-5 py-2.5 rounded font-label-md text-sm transition-colors text-white"
            :class="{
              'bg-red-600 hover:bg-red-700 active:bg-red-800 shadow-sm': options.type === 'danger',
              'bg-amber-600 hover:bg-amber-700 active:bg-amber-800 shadow-sm': options.type === 'warning',
              'bg-blue-600 hover:bg-blue-700 active:bg-blue-800 shadow-sm': options.type === 'info',
            }"
          >
            {{ options.confirmText }}
          </button>
        </div>
      </div>
    </div>
  </Transition>
</template>

<style scoped>
@keyframes scaleUp {
  from {
    transform: scale(0.95);
    opacity: 0;
  }
  to {
    transform: scale(1);
    opacity: 1;
  }
}
</style>
