<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps<{
  competitor: string;
}>();

const page = usePage();
const user = computed(() => page.props.auth?.user);
const isPro = computed(() => page.props.auth?.is_pro);

interface CompetitorData {
  name: string;
  slug: string;
  tagline: string;
  intro: string;
  pros: string[];
  cons: string[];
  features: {
    feature: string;
    description: string;
    ilusion: string;
    competitor: string;
    ilusionBetter: boolean;
  }[];
}

const data = computed<CompetitorData>(() => {
  if (props.competitor === 'firefox-send') {
    return {
      name: 'Firefox Send',
      slug: 'firefox-send',
      tagline: 'Looking for a reliable, active Firefox Send alternative?',
      intro: 'Firefox Send was a popular end-to-end encrypted file sharing service by Mozilla. However, it was permanently discontinued in 2020. Ilusion Vault offers a modern, client-side encrypted alternative with richer features, customizable expiries, password hints, and recipient alerts.',
      pros: [
        'Client-side AES-256 encryption running completely in the browser',
        'Custom expiry times (e.g., minutes, hours, days, burn-on-read, permanent Pro)',
        'Recipient read notifications (email alerts when someone accesses your link)',
        'Decryption password hints to easily share passwords out-of-band'
      ],
      cons: [
        'Discontinued by Mozilla in 2020',
        'No longer receives security updates, bug fixes, or official hosting',
        'Lacked advanced customization like custom address naming or custom hints'
      ],
      features: [
        { feature: 'Service Status', description: 'Is the service actively hosted and maintained?', ilusion: 'Active & Maintained', competitor: 'Discontinued (2020)', ilusionBetter: true },
        { feature: 'Encryption Algorithm', description: 'Security level of stored/shared secrets', ilusion: 'AES-256-GCM Client-Side', competitor: 'AES-128-GCM Client-Side', ilusionBetter: true },
        { feature: 'Storage Type', description: 'Where secrets are held', ilusion: 'Zero-Knowledge Server', competitor: 'Expired / No longer active', ilusionBetter: true },
        { feature: 'Max File Size', description: 'Upload limit per share', ilusion: 'Up to 100MB (Pro) / 15MB (Free)', competitor: 'Expired', ilusionBetter: true },
        { feature: 'Read Notifications', description: 'Be notified when a link is opened', ilusion: 'Yes (Email alert on read)', competitor: 'No', ilusionBetter: true },
        { feature: 'Password Hints', description: 'Hints to safely convey the decryption key', ilusion: 'Yes (Custom text hints)', competitor: 'No', ilusionBetter: true },
      ]
    };
  } else if (props.competitor === 'bitwarden-send') {
    return {
      name: 'Bitwarden Send',
      slug: 'bitwarden-send',
      tagline: 'Comparing Bitwarden Send vs Ilusion Vault',
      intro: 'Bitwarden Send is a feature within the Bitwarden password manager for sharing files and text. While secure, it requires setting up or paying for a password manager suite to share files and lacks a sleek, dedicated sharing UX. Ilusion Vault provides a dedicated, visually gorgeous, standalone zero-logs secret vault.',
      pros: [
        'No extension or account creation needed to send or receive secrets',
        'Ultra-sleek, visual, and modern user interface',
        'Decryption password hints (allow safe hint sharing without sending key)',
        'Zero-knowledge client-side encryption runs entirely in your browser'
      ],
      cons: [
        'Requires Bitwarden account / premium subscription for file uploads',
        'Interface is less visual and built inside a password manager dashboard',
        'No support for password hints (you must share keys entirely out-of-band)'
      ],
      features: [
        { feature: 'Standalone Web UI', description: 'Requires extension or complex app setups', ilusion: 'Yes (Instant web app)', competitor: 'Requires account/app', ilusionBetter: true },
        { feature: 'Encryption Standard', description: 'Security level of stored/shared secrets', ilusion: 'AES-256-GCM Client-Side', competitor: 'AES-256 Client-Side', ilusionBetter: false },
        { feature: 'Max File Size', description: 'Upload limit per share', ilusion: 'Up to 100MB (Pro) / 15MB (Free)', competitor: '100MB (Requires Premium)', ilusionBetter: true },
        { feature: 'Password Hint Option', description: 'Safely display clues to decrypt secrets', ilusion: 'Yes (Pro)', competitor: 'No', ilusionBetter: true },
        { feature: 'Burn on Read', description: 'Self-destruction after single open', ilusion: 'Yes (Instant deletion)', competitor: 'Yes (Deletes on expiry/read)', ilusionBetter: false },
        { feature: 'User Experience', description: 'Look and feel of the sharing interface', ilusion: 'Gorgeous M3-Inspired UI', competitor: 'Basic dashboard utility', ilusionBetter: true },
      ]
    };
  } else {
    // 1password-send
    return {
      name: '1Password Secure Sharing',
      slug: '1password-send',
      tagline: 'Comparing 1Password Secure Sharing vs Ilusion Vault',
      intro: '1Password secure sharing (also known as Psst!) lets users share credentials and items from their vault. However, it is strictly tied to a paid 1Password subscription and is tailored around password vault items rather than general-purpose zero-knowledge text and file sharing. Ilusion Vault is a modern, standalone secret portal accessible to everyone.',
      pros: [
        'Independent of any password manager subscription',
        'Dedicated secure text and file sharing tools',
        'Custom decryption password hints',
        'Instant, anonymous, and non-custodial sharing flows'
      ],
      cons: [
        'Requires active paid 1Password subscription',
        'Geared mainly toward sharing pre-existing vault logins and credentials',
        'No custom landing page styling or standalone recipient alerts'
      ],
      features: [
        { feature: 'Subscription Cost', description: 'Required account type to share', ilusion: 'Free Tier Available (Pro optional)', competitor: 'Paid 1Password Subscription', ilusionBetter: true },
        { feature: 'Sharing Type', description: 'What type of content can be sent', ilusion: 'Any Text / Files / Attachments', competitor: 'Credentials / Vault items', ilusionBetter: true },
        { feature: 'Client-Side Encryption', description: 'Zero-knowledge guarantee', ilusion: 'Yes (Browser-side AES-256)', competitor: 'Yes', ilusionBetter: false },
        { feature: 'Password Hints', description: 'Clues to decrypt without revealing keys', ilusion: 'Yes (Custom hints)', competitor: 'No', ilusionBetter: true },
        { feature: 'Recipient Alerts', description: 'Sender is notified when the link is read', ilusion: 'Yes (Email notifications)', competitor: 'No', ilusionBetter: true },
        { feature: 'Visual UI Customization', description: 'Modern, interactive aesthetic', ilusion: 'Premium Light theme', competitor: 'Standard portal theme', ilusionBetter: true },
      ]
    };
  }
});
</script>

<template>
  <Head :title="`Ilusion Vault vs ${data.name} — Open Source Encrypted Vault Alternative`">
    <meta name="description" :content="`Compare Ilusion Vault with ${data.name}. See how our open-source, zero-knowledge encrypted vault compares on client-side encryption, file sharing limits, and secure storage features.`" />
    <link rel="canonical" :href="`https://ilusion.io/vs/${data.slug}`" />
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
          <Link v-if="!isPro" href="/pricing" class="font-label-md text-label-md text-vault-on-surface hover:text-vault-primary transition-colors duration-200 mr-2 uppercase tracking-widest hidden sm:inline-block border-b-2 border-transparent hover:border-vault-primary">Pricing</Link>
          <Link href="/contact" class="font-label-md text-label-md text-vault-on-surface hover:text-vault-primary transition-colors duration-200 mr-2 uppercase tracking-widest hidden sm:inline-block border-b-2 border-transparent hover:border-vault-primary">Contact</Link>
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

    <main class="flex-grow flex flex-col items-center justify-center px-4 sm:px-6 md:px-12 pt-32 pb-16 relative z-10 w-full max-w-[75rem] mx-auto">
      <div class="text-center mb-12 max-w-3xl mx-auto">
        <span class="bg-vault-primary/10 text-vault-primary font-label-md text-xs px-3 py-1 rounded-full uppercase tracking-wider font-semibold mb-3 inline-block">Comparison Review</span>
        <h1 class="font-display text-4xl md:text-[3rem] font-bold text-vault-on-surface mb-4 leading-tight tracking-tight select-none">{{ data.tagline }}</h1>
        <p class="text-base sm:text-lg text-vault-on-surface-variant leading-relaxed">{{ data.intro }}</p>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6 w-full mb-12">
        <div class="bg-vault-surface-container-lowest border border-vault-outline-variant rounded-xl p-6 sm:p-8 shadow-sm">
          <div class="flex items-center gap-2 mb-4">
            <span class="material-symbols-outlined text-green-600 text-[1.5rem]" style="font-variation-settings: 'FILL' 1;">check_circle</span>
            <h3 class="font-display text-xl font-bold text-vault-on-surface">Why Choose Ilusion Vault?</h3>
          </div>
          <ul class="flex flex-col gap-3">
            <li v-for="(pro, idx) in data.pros" :key="idx" class="flex gap-2.5 items-start text-vault-on-surface-variant font-body-md text-sm">
              <span class="material-symbols-outlined text-green-600 text-[1.125rem] mt-0.5" style="font-variation-settings: 'FILL' 1;">check</span>
              <span>{{ pro }}</span>
            </li>
          </ul>
        </div>

        <div class="bg-vault-surface-container-lowest border border-vault-outline-variant rounded-xl p-6 sm:p-8 shadow-sm">
          <div class="flex items-center gap-2 mb-4">
            <span class="material-symbols-outlined text-red-600 text-[1.5rem]" style="font-variation-settings: 'FILL' 1;">error</span>
            <h3 class="font-display text-xl font-bold text-vault-on-surface">Downsides of {{ data.name }}</h3>
          </div>
          <ul class="flex flex-col gap-3">
            <li v-for="(con, idx) in data.cons" :key="idx" class="flex gap-2.5 items-start text-vault-on-surface-variant font-body-md text-sm">
              <span class="material-symbols-outlined text-red-500 text-[1.125rem] mt-0.5" style="font-variation-settings: 'FILL' 1;">close</span>
              <span>{{ con }}</span>
            </li>
          </ul>
        </div>
      </div>

      <div class="w-full bg-vault-surface-container-lowest border border-vault-outline-variant rounded-xl shadow-sm overflow-hidden mb-12">
        <div class="p-6 border-b border-vault-outline-variant bg-vault-surface-container-low/30">
          <h2 class="font-display text-xl font-bold text-vault-on-surface">Detailed Feature Breakdown</h2>
        </div>
        <div class="overflow-x-auto">
          <table class="w-full text-left border-collapse">
            <thead>
              <tr class="bg-vault-surface-container-low/50 text-vault-secondary font-label-md text-xs tracking-wider uppercase border-b border-vault-outline-variant">
                <th class="py-4 px-6">Feature</th>
                <th class="py-4 px-6">Ilusion Vault</th>
                <th class="py-4 px-6">{{ data.name }}</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-vault-outline-variant">
              <tr v-for="(f, idx) in data.features" :key="idx" class="hover:bg-vault-surface-container-low/10 transition-colors">
                <td class="py-5 px-6">
                  <div class="font-semibold text-vault-on-surface font-display text-sm">{{ f.feature }}</div>
                  <div class="text-[0.75rem] text-vault-secondary/80 mt-0.5 max-w-sm">{{ f.description }}</div>
                </td>
                <td class="py-5 px-6 font-semibold text-sm" :class="f.ilusionBetter ? 'text-vault-primary' : 'text-vault-on-surface'">
                  <div class="flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-[1rem]" style="font-variation-settings: 'FILL' 1;">check_circle</span>
                    {{ f.ilusion }}
                  </div>
                </td>
                <td class="py-5 px-6 text-sm text-vault-on-surface-variant">
                  <div class="flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-[1.125rem]" :class="f.ilusionBetter ? 'text-vault-outline/50' : 'text-vault-on-surface-variant'">
                      {{ f.ilusionBetter ? 'cancel' : 'check_circle' }}
                    </span>
                    {{ f.competitor }}
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <div class="text-center bg-vault-surface-container-low/40 border border-vault-outline-variant rounded-xl p-8 sm:p-12 w-full max-w-3xl mx-auto shadow-sm relative overflow-hidden">
        <div class="absolute -top-12 -left-12 w-32 h-32 bg-vault-primary/5 rounded-full blur-2xl"></div>
        <div class="absolute -bottom-12 -right-12 w-32 h-32 bg-vault-primary/5 rounded-full blur-2xl"></div>
        
        <h2 class="font-display text-2xl sm:text-3xl font-bold text-vault-on-surface mb-3">Ready to share secrets securely?</h2>
        <p class="text-vault-on-surface-variant mb-6 max-w-xl mx-auto">Create end-to-end encrypted secret links with custom expiry parameters, passwords, and file uploads. No sign-up required.</p>
        
        <div class="flex flex-col sm:flex-row justify-center items-center gap-4">
          <Link href="/" class="w-full sm:w-auto bg-vault-primary text-vault-on-primary font-label-md text-label-md py-3 px-8 rounded hover:bg-vault-primary-container transition-all flex items-center justify-center gap-2 shadow-[inset_0_-1px_0_rgba(0,0,0,0.2)] active:shadow-none active:translate-y-[1px]">
            <span class="material-symbols-outlined text-[1.125rem]" style="font-variation-settings: 'FILL' 1;">add</span>
            Share a Secret
          </Link>
          <Link href="/pricing" class="w-full sm:w-auto bg-vault-surface-container-lowest border border-vault-outline-variant text-vault-on-surface font-label-md text-label-md py-3 px-8 rounded hover:bg-vault-surface-container-low transition-colors duration-200">
            View Pricing
          </Link>
        </div>
      </div>
    </main>

    <footer class="w-full py-6 md:py-8 px-4 sm:px-6 md:px-12 flex flex-col md:flex-row justify-between items-center gap-6 bg-vault-surface border-t border-vault-outline-variant z-10 relative mt-auto">
      <div class="flex items-center gap-2 font-label-md text-label-md uppercase tracking-widest text-vault-on-surface">
        <img src="/ilusion-logo.png" alt="Ilusion" class="w-8 h-8 object-contain opacity-80" />
        Ilusion
      </div>
      <div class="flex gap-6 flex-wrap justify-center">
        <Link class="font-label-sm text-label-sm text-vault-on-secondary-container hover:text-vault-on-surface transition-colors duration-200" href="/security">Security</Link>
        <Link class="font-label-sm text-label-sm text-vault-on-secondary-container hover:text-vault-on-surface transition-colors duration-200" href="/terms">Terms</Link>
        <Link class="font-label-sm text-label-sm text-vault-on-secondary-container hover:text-vault-on-surface transition-colors duration-200" href="/privacy">Privacy</Link>
        <Link class="font-label-sm text-label-sm text-vault-on-secondary-container hover:text-vault-on-surface transition-colors duration-200" href="/status">Status</Link>
        <Link class="font-label-sm text-label-sm text-vault-on-secondary-container hover:text-vault-on-surface transition-colors duration-200" href="/docs">Docs</Link>
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
</style>
