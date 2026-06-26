<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { ref, computed } from 'vue';

const page = usePage();
const user = computed(() => page.props.auth?.user);

const name = ref('');
const email = ref('');
const message = ref('');
const isSubmitting = ref(false);
const submitted = ref(false);
const error = ref('');

async function handleSubmit() {
  if (!name.value || !email.value || !message.value) {
    error.value = 'All fields are required.';

    return;
  }

  isSubmitting.value = true;
  error.value = '';

  try {
    await axios.post('/contact', {
      name: name.value,
      email: email.value,
      message: message.value,
    });
    submitted.value = true;
    // Reset fields
    name.value = '';
    email.value = '';
    message.value = '';
  } catch (e: any) {
    error.value = e.response?.data?.message || 'Failed to send message.';
  } finally {
    isSubmitting.value = false;
  }
}
</script>

<template>
  <Head title="Contact — Ilusion Vault | Encrypted Data Vault">
    <meta name="description" content="Get in touch with the Ilusion Vault team. Send us your feedback, bug reports, deployment assistance requests, or partnership queries." />
    <link rel="canonical" href="https://ilusion.io/contact" />
  </Head>

  <div class="vault-light bg-vault-background text-vault-on-background min-h-screen flex flex-col font-body-md antialiased selection:bg-[#dbe1ff] selection:text-[#00174b] relative overflow-x-hidden">
    <div class="absolute inset-0 bg-dot-grid pointer-events-none z-0"></div>

    <header class="fixed top-0 left-0 right-0 z-50 flex justify-between items-center px-4 sm:px-6 md:px-12 py-3 md:py-4 bg-vault-surface/80 backdrop-blur-xl border-b border-vault-outline-variant shadow-sm transition-all duration-300">
      <div class="flex items-center gap-4 sm:gap-8 max-w-[75rem] w-full mx-auto justify-between">
        <Link class="flex items-center gap-2 font-headline-md text-[1.25rem] font-bold text-vault-on-surface hover:opacity-90" href="/">
          <img src="/ilusion-logo.png" alt="Ilusion" class="w-10 h-10 md:w-12 md:h-12 object-contain" />
          Ilusion
        </Link>
        <div class="flex items-center gap-3">
          <Link href="/contact" class="font-label-md text-label-md text-vault-primary hover:text-vault-on-surface transition-colors duration-200 mr-2 uppercase tracking-widest hidden sm:inline-block border-b-2 border-vault-primary">Contact</Link>
          <template v-if="user">
            <Link href="/profile" class="bg-vault-surface-container-lowest border border-vault-outline-variant text-vault-on-surface font-label-md text-label-md py-2 px-4 rounded hover:bg-vault-surface-container-low transition-colors duration-200">Dashboard</Link>
          </template>
          <template v-else>
            <Link href="/login" class="bg-vault-surface-container-lowest border border-vault-outline-variant text-vault-on-surface font-label-md text-label-md py-2 px-4 rounded hover:bg-vault-surface-container-low transition-colors duration-200">Log in</Link>
            <Link href="/register" class="bg-[#18181b] text-white font-label-md text-label-md py-2 px-4 rounded hover:bg-black transition-colors duration-200">Sign up</Link>
          </template>
        </div>
      </div>
    </header>

    <main class="flex-grow flex flex-col items-center justify-center px-4 sm:px-6 md:px-12 pt-32 pb-16 relative z-10 w-full max-w-[64rem] mx-auto">
      <div class="w-full max-w-2xl bg-vault-surface-container-lowest border border-vault-outline-variant rounded-xl p-8 shadow-sm relative">
        <h1 class="font-display text-3xl font-bold text-vault-on-surface mb-3 leading-tight tracking-tight">Contact Us</h1>
        <p class="text-base text-vault-on-surface-variant mb-6">
          Have a question, feedback, or need support? Fill out the form below, and we'll get back to you soon. 
          You can also email us directly at <a href="mailto:hello@ilusion.io" class="text-vault-primary font-semibold hover:underline">hello@ilusion.io</a>.
        </p>

        <form @submit.prevent="handleSubmit" class="flex flex-col gap-4">
          <div class="flex flex-col gap-2">
            <label class="font-label-sm text-label-sm uppercase text-vault-secondary select-none" for="name">Name</label>
            <input
              v-model="name"
              type="text"
              id="name"
              class="w-full bg-vault-surface-container-lowest border border-vault-outline-variant rounded py-3 px-4 font-body-md text-body-md text-vault-on-surface focus:outline-none focus:border-vault-primary focus:ring-1 focus:ring-vault-primary transition-all placeholder:text-vault-outline"
              placeholder="Your Name"
              required
            />
          </div>

          <div class="flex flex-col gap-2">
            <label class="font-label-sm text-label-sm uppercase text-vault-secondary select-none" for="email">Email Address</label>
            <input
              v-model="email"
              type="email"
              id="email"
              class="w-full bg-vault-surface-container-lowest border border-vault-outline-variant rounded py-3 px-4 font-body-md text-body-md text-vault-on-surface focus:outline-none focus:border-vault-primary focus:ring-1 focus:ring-vault-primary transition-all placeholder:text-vault-outline"
              placeholder="you@example.com"
              required
            />
          </div>

          <div class="flex flex-col gap-2">
            <label class="font-label-sm text-label-sm uppercase text-vault-secondary select-none" for="message">Message</label>
            <textarea
              v-model="message"
              id="message"
              rows="5"
              class="w-full bg-vault-surface-container-lowest border border-vault-outline-variant rounded py-3 px-4 font-body-md text-body-md text-vault-on-surface focus:outline-none focus:border-vault-primary focus:ring-1 focus:ring-vault-primary transition-all placeholder:text-vault-outline resize-none"
              placeholder="How can we help you?"
              required
            ></textarea>
          </div>

          <div v-if="error" class="text-red-600 bg-red-50 border border-red-200 rounded p-3 text-sm font-medium">
            {{ error }}
          </div>

          <div v-if="submitted" class="text-green-700 bg-green-50 border border-green-200 rounded p-3 text-sm font-medium flex items-center gap-2 animate-[fadeIn_0.3s_ease-out]">
            <span class="material-symbols-outlined text-[1.25rem]" style="font-variation-settings: 'FILL' 1;">check_circle</span>
            Your message has been sent successfully! Thank you.
          </div>

          <div class="pt-4 flex justify-end">
            <button
              type="submit"
              :disabled="isSubmitting"
              class="w-full sm:w-auto bg-vault-primary text-vault-on-primary font-label-md text-label-md py-3 px-8 rounded hover:bg-vault-primary-container transition-colors flex items-center justify-center gap-2 shadow-[inset_0_-1px_0_rgba(0,0,0,0.2)] active:shadow-none active:translate-y-[1px] disabled:opacity-70 disabled:cursor-not-allowed"
            >
              <span v-if="!isSubmitting" class="material-symbols-outlined text-[1.125rem]" style="font-variation-settings: 'FILL' 1;">send</span>
              <span v-else class="material-symbols-outlined text-[1.125rem] animate-spin">sync</span>
              {{ isSubmitting ? 'Sending...' : 'Send Message' }}
            </button>
          </div>
        </form>
      </div>
    </main>

    <footer class="w-full py-6 md:py-8 px-4 sm:px-6 md:px-12 flex flex-col md:flex-row justify-between items-center gap-6 bg-vault-surface border-t border-vault-outline-variant z-10 relative mt-auto">
      <div class="flex items-center gap-2 font-label-md text-label-md uppercase tracking-widest text-vault-on-surface">
        <img src="/ilusion-logo.png" alt="Ilusion" class="w-8 h-8 object-contain opacity-80" />
        Ilusion
      </div>
      <div class="flex gap-6 flex-wrap justify-center">
        <Link class="font-label-sm text-label-sm text-vault-on-secondary-container hover:text-vault-on-surface transition-colors duration-200" href="/contact">Contact</Link>
        <Link class="font-label-sm text-label-sm text-vault-on-secondary-container hover:text-vault-on-surface transition-colors duration-200" href="/vs/bitwarden-send">vs Bitwarden</Link>
        <Link class="font-label-sm text-label-sm text-vault-on-secondary-container hover:text-vault-on-surface transition-colors duration-200" href="/vs/firefox-send">vs Firefox Send</Link>
        <Link class="font-label-sm text-label-sm text-vault-on-secondary-container hover:text-vault-on-surface transition-colors duration-200" href="/vs/1password-send">vs 1Password</Link>
      </div>
      <div class="flex items-center gap-4 font-label-sm text-label-sm text-vault-secondary">
        <span>© 2026 Ilusion Vault</span>
        <a href="https://github.com/ilusion-io/ilusion-vault" target="_blank" rel="noopener noreferrer" class="hover:text-vault-on-surface transition-colors duration-200" title="GitHub">
            <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M12 .297c-6.63 0-12 5.373-12 12 0 5.303 3.438 9.8 8.205 11.385.6.113.82-.258.82-.577 0-.285-.01-1.04-.015-2.04-3.338.724-4.042-1.61-4.042-1.61C4.422 18.07 3.633 17.7 3.633 17.7c-1.087-.744.084-.729.084-.729 1.205.084 1.838 1.236 1.838 1.236 1.07 1.835 2.809 1.305 3.495.998.108-.776.417-1.305.76-1.605-2.665-.3-5.466-1.332-5.466-5.93 0-1.31.465-2.38 1.235-3.22-.135-.303-.54-1.523.105-3.176 0 0 1.005-.322 3.3 1.23.96-.267 1.98-.399 3-.405 1.02.006 2.04.138 3 .405 2.28-1.552 3.285-1.23 3.285-1.23.645 1.653.24 2.873.12 3.176.765.84 1.23 1.91 1.23 3.22 0 4.61-2.805 5.625-5.475 5.92.42.36.81 1.096.81 2.22 0 1.606-.015 2.896-.015 3.286 0 .315.21.69.825.57C20.565 22.092 24 17.592 24 12.297c0-6.627-5.373-12-12-12"/></svg>
        </a>
        <a href="https://in.linkedin.com/company/ilusion-io" target="_blank" rel="noopener noreferrer" class="hover:text-vault-on-surface transition-colors duration-200" title="LinkedIn">
            <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.779-1.75-1.75s.784-1.75 1.75-1.75 1.75.779 1.75 1.75-.784 1.75-1.75 1.75zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/></svg>
        </a>
      </div>
    </footer>
  </div>
</template>

<style scoped>
.font-display { font-family: 'Inter', sans-serif; }
.font-headline-md { font-family: 'Inter', sans-serif; }
.font-body-md { font-family: 'Inter', sans-serif; font-size: 0.875rem; line-height: 1.6; }
.font-label-md { font-family: 'Inter', sans-serif; font-size: 0.75rem; font-weight: 600; letter-spacing: 0.08em; text-transform: uppercase; }
.font-label-sm { font-family: 'Inter', sans-serif; font-size: 0.625rem; font-weight: 600; letter-spacing: 0.1em; }

.vault-light {
    --vault-background: #f7f9fb;
    --vault-surface: #f7f9fb;
    --vault-surface-container-lowest: #ffffff;
    --vault-surface-container-low: #f2f4f6;
    --vault-surface-container: #eceef0;
    --vault-surface-container-high: #e6e8ea;
    --vault-on-surface: #191c1e;
    --vault-on-surface-variant: #434655;
    --vault-outline-variant: #c3c6d7;
    --vault-outline: #737686;
    --vault-primary: #004ac6;
    --vault-on-primary: #ffffff;
    --vault-primary-container: #2563eb;
    --vault-secondary: #565e74;
}

.bg-dot-grid {
    background-image: radial-gradient(#c3c6d7 1px, transparent 1px);
    background-size: 24px 24px;
    background-position: -11px -11px;
    opacity: 0.3;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(4px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>
