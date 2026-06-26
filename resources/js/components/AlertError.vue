<script setup lang="ts">
import { AlertCircle } from '@lucide/vue';
import { computed } from 'vue';

type Props = {
    errors: string[];
    title?: string;
};

const props = withDefaults(defineProps<Props>(), {
    title: 'Something went wrong.',
});

const uniqueErrors = computed(() => Array.from(new Set(props.errors)));
</script>

<template>
    <div class="rounded-lg border border-red-500/20 bg-red-500/5 p-4 text-red-500 flex items-start gap-3">
        <AlertCircle class="size-5 shrink-0 mt-0.5" />
        <div>
            <p class="font-bold text-sm leading-tight mb-1">{{ title }}</p>
            <ul class="list-inside list-disc text-sm space-y-0.5">
                <li v-for="(error, index) in uniqueErrors" :key="index">
                    {{ error }}
                </li>
            </ul>
        </div>
    </div>
</template>
