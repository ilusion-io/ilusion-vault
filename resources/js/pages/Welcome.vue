<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { profile, login, register, home, logout } from '@/routes';
import axios from 'axios';
import {
    encryptText,
    decryptText,
    encryptFile,
    decryptFile,
    base64ToArrayBuffer,
    deriveKey
} from '@/lib/crypto';

const props = defineProps<{
    initialSecretId?: string;
}>();

const payload = ref('');
const expiry = ref('7 Days');
const password = ref('');
const customAddress = ref('');
const showAdvanced = ref(false);
const recipientEmail = ref('');
const encryptionHint = ref('');
const burnOnRead = ref(false);

const attachedFiles = ref<File[]>([]);
const fileInputRef = ref<HTMLInputElement | null>(null);
const dragOver = ref(false);

const isCreated = ref(false);
const createdLink = ref('');
const copied = ref(false);

const decryptedFiles = ref<Array<{
    name: string;
    downloadUrl: string;
    type: string;
    isDecrypting: boolean;
    decryptedDataUrl?: string;
}>>([]);

function generateRandomKey(length = 16): string {
    const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    const array = new Uint8Array(length);
    window.crypto.getRandomValues(array);
    let key = '';
    for (let i = 0; i < length; i++) {
        key += chars[array[i] % chars.length];
    }
    return key;
}

const retrieveId = ref('');
const retrievedSecret = ref<string | null>(null);
const isRetrieving = ref(false);

const activeTab = ref<'write' | 'preview'>('write');
const textareaRef = ref<HTMLTextAreaElement | null>(null);

// FIX: this was referenced in the template (@focus/@blur on the <select>)
// but never declared, which throws in dev mode.
const activeFocus = ref<string | null>(null);

const charCount = computed(() => payload.value.length);
const charWarning = computed(() => charCount.value > 10000);

// Simple secure Markdown parser for preview mode
const compiledMarkdown = computed(() => {
    if (!payload.value.trim()) {
        return '<p class="text-vault-outline/70 italic select-none">No content to preview yet. Start typing in the "Write" tab...</p>';
    }

    // HTML escape to prevent XSS in preview
    let html = payload.value
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;');

    // Headers
    html = html.replace(/^### (.*$)/gim, '<h3 class="text-base font-bold mt-4 mb-2 text-vault-on-surface">$1</h3>');
    html = html.replace(/^## (.*$)/gim, '<h2 class="text-lg font-bold mt-5 mb-2 text-vault-on-surface">$1</h2>');
    html = html.replace(/^# (.*$)/gim, '<h1 class="text-xl font-bold mt-6 mb-3 text-vault-on-surface border-b border-vault-outline-variant/30 pb-1">$1</h1>');

    // Bold & Italic
    html = html.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
    html = html.replace(/\*(.*?)\*/g, '<em>$1</em>');

    // Code blocks
    html = html.replace(/```([\s\S]*?)```/g, '<pre class="bg-vault-surface-container border border-vault-outline-variant/60 rounded p-3 my-3 font-mono text-xs overflow-x-auto select-text">$1</pre>');

    // Inline code
    html = html.replace(/`([^`]+)`/g, '<code class="bg-vault-surface-container px-1.5 py-0.5 rounded font-mono text-xs text-vault-primary">$1</code>');

    // Lists
    html = html.replace(/^\s*-\s+(.*$)/gim, '<li class="ml-4 list-disc text-vault-on-surface-variant">$1</li>');
    html = html.replace(/^\s*\*\s+(.*$)/gim, '<li class="ml-4 list-disc text-vault-on-surface-variant">$1</li>');

    // Line breaks
    html = html.replace(/\n/gim, '<br />');

    return html;
});

// Markdown Insertion function with cursor management
function insertMarkdown(syntax: string, placeholder = '') {
    const textarea = textareaRef.value;
    if (!textarea) return;

    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;
    const text = payload.value;

    const selectedText = text.substring(start, end) || placeholder;
    const replacement = syntax.replace('$', selectedText);

    payload.value = text.substring(0, start) + replacement + text.substring(end);

    setTimeout(() => {
        textarea.focus();
        const selectionStart = start + replacement.indexOf(selectedText);
        textarea.setSelectionRange(selectionStart, selectionStart + selectedText.length);
    }, 50);
}

const isSubmitting = ref(false);

async function handleCreateSecret() {
    if (!payload.value.trim()) return;

    isSubmitting.value = true;
    try {
        const encKey = password.value || generateRandomKey();
        const encryptedTextJson = await encryptText(payload.value, encKey);

        const formData = new FormData();
        formData.append('payload', encryptedTextJson);
        formData.append('expiry', expiry.value);
        if (password.value) formData.append('password', password.value);
        if (customAddress.value) formData.append('custom_address', customAddress.value);
        formData.append('burn_on_read', burnOnRead.value ? '1' : '0');
        if (recipientEmail.value) formData.append('recipient_email', recipientEmail.value);
        if (encryptionHint.value) formData.append('encryption_hint', encryptionHint.value);
        
        const fileMetadataArray: any[] = [];
        for (let i = 0; i < attachedFiles.value.length; i++) {
            const file = attachedFiles.value[i];
            const { encryptedBlob, metadata } = await encryptFile(file, encKey);
            formData.append('files[]', encryptedBlob, `file_${i}`);
            fileMetadataArray.push(metadata);
        }
        
        if (fileMetadataArray.length > 0) {
            formData.append('file_metadata', JSON.stringify(fileMetadataArray));
        }

        const response = await axios.post('/api/secrets', formData, {
            headers: {
                'Accept': 'application/json'
            }
        });

        let finalUrl = response.data.url;
        if (!password.value) {
            finalUrl += `#${encKey}`;
        }
        createdLink.value = finalUrl;
        isCreated.value = true;
    } catch (error: any) {
        console.error('Error creating secret:', error);
        alert('Failed to create secret. ' + (error.response?.data?.message || error.message));
    } finally {
        isSubmitting.value = false;
    }
}

function handleKeydown(e: KeyboardEvent) {
    if ((e.metaKey || e.ctrlKey) && e.key === 'Enter') {
        handleCreateSecret();
    }
}

function handleCopy() {
    navigator.clipboard.writeText(createdLink.value);
    copied.value = true;
    setTimeout(() => {
        copied.value = false;
    }, 2000);
}

function handleCreateAnother() {
    payload.value = '';
    password.value = '';
    customAddress.value = '';
    attachedFiles.value = [];
    recipientEmail.value = '';
    encryptionHint.value = '';
    showAdvanced.value = false;
    burnOnRead.value = false;
    activeTab.value = 'write';
    isCreated.value = false;
    createdLink.value = '';
}

function triggerFileInput() {
    fileInputRef.value?.click();
}

function handleFileSelect(e: Event) {
    const target = e.target as HTMLInputElement;
    if (target.files) {
        addFiles(target.files);
    }
}

function handleFileDrop(e: DragEvent) {
    dragOver.value = false;
    if (e.dataTransfer?.files) {
        addFiles(e.dataTransfer.files);
    }
}

function addFiles(fileList: FileList) {
    for (let i = 0; i < fileList.length; i++) {
        const file = fileList[i];
        const alreadyExists = attachedFiles.value.some(f => f.name === file.name && f.size === file.size);
        if (!alreadyExists) {
            attachedFiles.value.push(file);
        }
    }
}

function removeFile(index: number) {
    attachedFiles.value.splice(index, 1);
}

function formatBytes(bytes: number, decimals = 1) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const dm = decimals < 0 ? 0 : decimals;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
}

const isDecrypting = ref(false);
const decryptionKey = ref('');
const fetchedSecretPayload = ref<any>(null);
const decryptedPayload = ref<string | null>(null);
const decryptError = ref('');

async function handleRetrieve() {
    if (!retrieveId.value.trim()) return;

    isRetrieving.value = true;
    isDecrypting.value = true; // Set to true immediately so the compose form is not shown
    decryptError.value = '';
    try {
        let id = retrieveId.value.trim();
        if (id.includes('/secret/')) {
            id = id.split('/secret/').pop() || id;
        }

        const response = await axios.get(`/api/secrets/${id}`);
        fetchedSecretPayload.value = response.data;
        
        // Hide created state if active
        isCreated.value = false;
        
        // Scroll to top to show the container
        window.scrollTo({ top: 0, behavior: 'smooth' });

        // Auto decrypt if hash key is present
        if (decryptionKey.value) {
            handleDecrypt();
        }
    } catch (error: any) {
        console.error('Error retrieving secret:', error);
        alert(error.response?.data?.message || 'Secret not found or expired.');
        isDecrypting.value = false; // Reset if retrieval failed
    } finally {
        isRetrieving.value = false;
    }
}

async function handleDecrypt() {
    if (!decryptionKey.value.trim()) {
        decryptError.value = 'Decryption key is required';
        return;
    }
    
    decryptError.value = '';
    try {
        const decrypted = await decryptText(
            fetchedSecretPayload.value.encrypted_payload,
            decryptionKey.value.trim()
        );
        decryptedPayload.value = decrypted;
        
        // Decrypt metadata names
        decryptedFiles.value = [];
        if (fetchedSecretPayload.value.file_paths) {
            const decoder = new TextDecoder();
            for (let i = 0; i < fetchedSecretPayload.value.file_paths.length; i++) {
                const file = fetchedSecretPayload.value.file_paths[i];
                if (!file.encrypted_metadata) continue;
                
                const metaSalt = new Uint8Array(base64ToArrayBuffer(file.salt));
                const metaIv = new Uint8Array(base64ToArrayBuffer(file.iv));
                const metaCiphertext = base64ToArrayBuffer(file.encrypted_metadata);
                
                const metaKey = await deriveKey(decryptionKey.value.trim(), metaSalt);
                const decryptedMeta = await window.crypto.subtle.decrypt(
                    { name: 'AES-GCM', iv: metaIv },
                    metaKey,
                    metaCiphertext
                );
                
                const meta = JSON.parse(decoder.decode(decryptedMeta));
                decryptedFiles.value.push({
                    name: meta.name,
                    type: meta.type,
                    downloadUrl: file.download_url,
                    isDecrypting: false
                });
            }
            // Auto download files after decryption
            for (let i = 0; i < decryptedFiles.value.length; i++) {
                downloadAndDecryptFile(i);
            }
        }
    } catch (error: any) {
        console.error('Decryption failed:', error);
        decryptError.value = 'Incorrect decryption key or corrupted payload.';
    }
}

async function downloadAndDecryptFile(index: number) {
    const file = decryptedFiles.value[index];
    if (!file) return;

    if (file.decryptedDataUrl) {
        triggerDownload(file.decryptedDataUrl, file.name);
        return;
    }

    file.isDecrypting = true;
    try {
        const payloadFile = fetchedSecretPayload.value.file_paths[index];
        
        const response = await axios.get(payloadFile.download_url, {
            responseType: 'arraybuffer'
        });
        
        const { decryptedFile } = await decryptFile(
            response.data,
            decryptionKey.value.trim(),
            payloadFile.encrypted_metadata,
            payloadFile.salt,
            payloadFile.iv
        );
        
        const url = URL.createObjectURL(decryptedFile);
        file.decryptedDataUrl = url;
        triggerDownload(url, file.name);
    } catch (error: any) {
        console.error('File decryption failed:', error);
        alert('Failed to decrypt and download file.');
    } finally {
        file.isDecrypting = false;
    }
}

function triggerDownload(url: string, filename: string) {
    const a = document.createElement('a');
    a.href = url;
    a.download = filename;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
}

const decryptedCopied = ref(false);

function handleCopyDecrypted() {
    if (!decryptedPayload.value) return;
    navigator.clipboard.writeText(decryptedPayload.value);
    decryptedCopied.value = true;
    setTimeout(() => {
        decryptedCopied.value = false;
    }, 2000);
}

function handleClearRetrieved() {
    retrievedSecret.value = null;
    decryptedPayload.value = null;
    isDecrypting.value = false;
    fetchedSecretPayload.value = null;
    decryptedFiles.value = [];
    decryptionKey.value = '';
    retrieveId.value = '';
    decryptedCopied.value = false;
}

onMounted(() => {
    const urlParams = new URLSearchParams(window.location.search);
    const secretParam = urlParams.get('secret');
    
    // Read the encryption key from URL hash if available
    const hashKey = window.location.hash.slice(1);
    if (hashKey) {
        decryptionKey.value = hashKey;
    }

    if (secretParam) {
        // Immediately clear the query parameter and hash from browser URL bar
        window.history.replaceState({}, document.title, '/');
        retrieveId.value = secretParam;
        handleRetrieve();
    } else if (props.initialSecretId) {
        retrieveId.value = props.initialSecretId;
        handleRetrieve();
    }
});
</script>

<template>
    <Head title="Ilusion - Secure Composer">
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="true" />
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&amp;display=swap" rel="stylesheet" />
        <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400&amp;display=swap" rel="stylesheet" />
        <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet" />
    </Head>

    <div class="vault-light bg-vault-background text-vault-on-background min-h-screen flex flex-col font-body-md antialiased selection:bg-[#dbe1ff] selection:text-[#00174b] relative overflow-x-hidden">
        <div class="absolute inset-0 bg-dot-grid pointer-events-none z-0"></div>

        <header class="fixed top-0 left-0 right-0 z-50 flex justify-between items-center px-4 sm:px-6 md:px-12 py-3 md:py-4 bg-vault-surface/80 backdrop-blur-xl border-b border-vault-outline-variant shadow-sm transition-all duration-300">
            <div class="flex items-center gap-4 sm:gap-8 max-w-[75rem] w-full mx-auto">
                <div class="flex items-center gap-4 sm:gap-8 flex-1">
                    <Link class="flex items-center gap-2 font-headline-md text-headline-md font-bold text-vault-on-surface hover:opacity-90" :href="home()">
                        <img src="/ilusion-logo.png" alt="Ilusion" class="w-10 h-10 md:w-12 md:h-12 object-contain" />
                        Ilusion
                    </Link>
                    <nav class="hidden md:flex gap-6">
                        <a class="font-body-md text-body-md text-vault-on-surface-variant hover:text-vault-primary transition-colors duration-200" href="#"></a>
                        <a class="font-body-md text-body-md text-vault-on-surface-variant hover:text-vault-primary transition-colors duration-200" href="#"></a>
                    </nav>
                </div>
                <div class="flex items-center gap-3">
                    <template v-if="$page.props.auth?.user">
                        <span class="font-body-md text-body-md text-vault-on-surface-variant select-none hidden sm:inline-block mr-2">
                            Hello <span class="font-semibold text-vault-on-surface">{{ $page.props.auth.user.name }}</span>
                        </span>
                        <Link
                            :href="profile()"
                            class="bg-vault-surface-container-lowest border border-vault-outline-variant text-vault-on-surface font-label-md text-label-md py-2 px-4 rounded hover:bg-vault-surface-container-low transition-colors duration-200 scale-95 hover:scale-100 ease-in-out inline-flex items-center justify-center mr-2"
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
                    <template v-else>
                        <Link
                            :href="login()"
                            class="bg-vault-surface-container-lowest border border-vault-outline-variant text-vault-on-surface font-label-md text-label-md py-2 px-4 rounded hover:bg-vault-surface-container-low transition-colors duration-200 scale-95 hover:scale-100 ease-in-out inline-flex items-center justify-center"
                        >
                            Sign In
                        </Link>
                    </template>
                </div>
            </div>
        </header>

        <main class="flex-grow flex flex-col items-center justify-center px-4 sm:px-6 md:px-12 pt-24 pb-12 relative z-10 w-full max-w-[75rem] mx-auto">
            <div class="text-center mb-6 max-w-2xl mx-auto select-none px-2">
                <h1 class="font-display text-4xl md:text-[3rem] font-bold text-vault-on-surface mb-4 md:mb-6 leading-tight tracking-tight">Share secretly. Store securely.</h1>
                <div class="flex items-center justify-center gap-3 font-label-sm text-label-sm uppercase text-vault-secondary tracking-widest">
                    <span>AES-256</span>
                    <span class="text-vault-outline-variant">|</span>
                    <span>Zero-Logs</span>
                    <span class="text-vault-outline-variant">|</span>
                    <span>E2EE</span>
                </div>
            </div>

            <div class="w-full max-w-[60rem] bg-vault-surface-container-lowest border border-vault-outline-variant rounded-xl p-4 sm:p-6 md:p-8 shadow-sm">
                <form v-if="!isCreated && !isDecrypting" @submit.prevent="handleCreateSecret" class="flex flex-col gap-4 md:gap-5">
                    <div class="flex flex-col gap-2">
                        <div class="flex justify-between items-center select-none">
                            <label class="font-label-sm text-label-sm uppercase text-vault-secondary" for="secret-content">Payload</label>

                            <div class="flex items-center gap-3">
                                <div v-show="activeTab === 'write'" class="hidden sm:flex items-center gap-2 text-vault-outline">
                                    <button type="button" @click="insertMarkdown('**$**', 'bold')" class="hover:text-vault-primary transition-colors font-bold text-[0.6875rem] px-0.5" title="Bold">B</button>
                                    <button type="button" @click="insertMarkdown('*$*', 'italic')" class="hover:text-vault-primary transition-colors italic text-[0.6875rem] px-0.5" title="Italic">I</button>
                                    <button type="button" @click="insertMarkdown('# $', 'Heading')" class="hover:text-vault-primary transition-colors text-[0.6875rem] px-0.5" title="Heading">H</button>
                                    <button type="button" @click="insertMarkdown('```\n$\n```', 'code')" class="hover:text-vault-primary transition-colors text-[0.6875rem] px-0.5 font-mono" title="Code Block">&lt;/&gt;</button>
                                    <button type="button" @click="insertMarkdown('- $', 'list item')" class="hover:text-vault-primary transition-colors text-[0.6875rem] px-0.5" title="List Item">•</button>
                                </div>
                                <span v-show="activeTab === 'write'" class="text-vault-outline/20 hidden sm:inline">|</span>
                                <div class="flex gap-2">
                                    <button
                                        type="button"
                                        @click="activeTab = 'write'"
                                        class="font-label-sm text-[0.625rem] uppercase tracking-wider transition-colors"
                                        :class="activeTab === 'write' ? 'text-vault-primary font-bold' : 'text-vault-outline hover:text-vault-on-surface'"
                                    >
                                        Write
                                    </button>
                                    <span class="text-vault-outline/20">|</span>
                                    <button
                                        type="button"
                                        @click="activeTab = 'preview'"
                                        class="font-label-sm text-[0.625rem] uppercase tracking-wider transition-colors"
                                        :class="activeTab === 'preview' ? 'text-vault-primary font-bold' : 'text-vault-outline hover:text-vault-on-surface'"
                                    >
                                        Preview
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="relative">
                            <textarea
                                v-show="activeTab === 'write'"
                                ref="textareaRef"
                                v-model="payload"
                                id="secret-content"
                                @keydown="handleKeydown"
                                class="w-full bg-vault-surface-container-low border border-vault-outline-variant rounded p-4 font-mono-custom text-sm text-vault-on-surface focus:outline-none focus:border-vault-primary focus:ring-1 focus:ring-vault-primary transition-all resize-none placeholder:text-vault-outline h-[10rem] md:h-[11.25rem]"
                                placeholder="Enter code, credentials, or text to encrypt locally..."
                                autocomplete="off"
                                spellcheck="false"
                                required
                            ></textarea>
                            <div v-show="activeTab === 'write' && charCount > 0" class="absolute bottom-2 right-3 font-mono-custom text-[0.625rem] select-none pointer-events-none" :class="charWarning ? 'text-vault-error' : 'text-vault-outline/50'">{{ charCount.toLocaleString() }}</div>

                            <div
                                v-show="activeTab === 'preview'"
                                class="w-full bg-vault-surface-container-low border border-vault-outline-variant rounded p-4 font-body-md text-body-md text-vault-on-surface overflow-y-auto select-text h-[10rem] md:h-[11.25rem] preview-container"
                                v-html="compiledMarkdown"
                            ></div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-12 gap-5 pt-4 border-t border-vault-outline-variant">
                        <div class="flex flex-col gap-2 md:col-span-3">
                            <label class="font-label-sm text-label-sm uppercase text-vault-secondary select-none" for="expiry">Expiry</label>
                            <div class="relative">
                                <select
                                    v-model="expiry"
                                    id="expiry"
                                    @focus="activeFocus = 'expiry'"
                                    @blur="activeFocus = null"
                                    class="w-full appearance-none bg-vault-surface-container-lowest border border-vault-outline-variant rounded py-3 pl-4 pr-10 font-body-md text-body-md text-vault-on-surface focus:outline-none focus:border-vault-primary focus:ring-1 focus:ring-vault-primary transition-all"
                                >
                                    <option>Never</option>
                                    <option>15 Days</option>
                                    <option>7 Days</option>
                                    <option>1 Day</option>
                                    <option>1 Hour</option>
                                </select>
                                <span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-vault-outline pointer-events-none text-[1.25rem] select-none">expand_more</span>
                            </div>
                        </div>
                        <div class="flex flex-col gap-2 md:col-span-2">
                            <label class="font-label-sm text-label-sm uppercase text-vault-secondary select-none">Burn on View</label>
                            <div class="flex items-center h-[2.875rem]">
                                <button
                                    type="button"
                                    @click="burnOnRead = !burnOnRead"
                                    :class="burnOnRead ? 'bg-vault-primary' : 'bg-vault-surface-container-high'"
                                    class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-vault-primary focus:ring-offset-2"
                                    role="switch"
                                    :aria-checked="burnOnRead"
                                >
                                    <span
                                        :class="burnOnRead ? 'translate-x-5' : 'translate-x-0'"
                                        class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                                    ></span>
                                </button>
                                <span class="font-body-md text-body-md text-vault-on-surface-variant ml-3 select-none">
                                    {{ burnOnRead ? 'Yes' : 'No' }}
                                </span>
                            </div>
                        </div>
                        <div class="flex flex-col gap-2 md:col-span-4">
                            <label class="font-label-sm text-label-sm uppercase text-vault-secondary select-none" for="password">Decryption Key</label>
                            <input
                                v-model="password"
                                type="password"
                                id="password"
                                autocomplete="new-password"
                                class="w-full bg-vault-surface-container-lowest border border-vault-outline-variant rounded py-3 px-4 font-body-md text-body-md text-vault-on-surface focus:outline-none focus:border-vault-primary focus:ring-1 focus:ring-vault-primary transition-all placeholder:text-vault-outline"
                                placeholder="Enter decryption key"
                                required
                            />
                        </div>
                        <div class="flex flex-col gap-2 md:col-span-3">
                            <label class="font-label-sm text-label-sm uppercase text-vault-secondary select-none" for="custom-address">Custom Address (Optional)</label>
                            <input
                                v-model="customAddress"
                                type="text"
                                id="custom-address"
                                class="w-full bg-vault-surface-container-lowest border border-vault-outline-variant rounded py-3 px-4 font-body-md text-body-md text-vault-on-surface focus:outline-none focus:border-vault-primary focus:ring-1 focus:ring-vault-primary transition-all placeholder:text-vault-outline"
                                placeholder="Add custom address"
                            />
                        </div>
                    </div>
 
                    <!-- File Attachment Section -->
                    <div class="pt-3 border-t border-vault-outline-variant flex flex-col gap-2">
                        <label class="font-label-sm text-label-sm uppercase text-vault-secondary select-none">Attachments (Optional)</label>
                        <div 
                            class="border border-dashed border-vault-outline-variant rounded-lg p-3 flex flex-col items-center justify-center bg-vault-surface-container-lowest hover:bg-vault-surface-container-low/30 hover:border-vault-primary/50 transition-all cursor-pointer select-none"
                            @click="triggerFileInput"
                            @dragover.prevent="dragOver = true"
                            @dragleave.prevent="dragOver = false"
                            @drop.prevent="handleFileDrop"
                            :class="{ 'border-vault-primary bg-vault-primary/5': dragOver }"
                        >
                            <input 
                                type="file" 
                                ref="fileInputRef" 
                                class="hidden" 
                                @change="handleFileSelect" 
                                multiple
                            />
                            <div class="flex items-center gap-2 text-vault-outline hover:text-vault-primary transition-colors">
                                <span class="material-symbols-outlined text-[1.25rem]">attach_file</span>
                                <span class="font-body-md text-body-md text-vault-secondary">Drag & drop files here, or <span class="text-vault-primary font-medium">browse</span></span>
                            </div>
                            <p class="font-label-sm text-[0.625rem] text-vault-secondary/70 mt-1">Maximum size: 10MB per file</p>
                        </div>

                        <!-- Selected Files List -->
                        <div v-if="attachedFiles.length > 0" class="flex flex-col gap-2 mt-2">
                            <div 
                                v-for="(file, index) in attachedFiles" 
                                :key="index"
                                class="flex items-center justify-between bg-vault-surface-container-low border border-vault-outline-variant/60 rounded px-3 py-2 text-vault-on-surface font-body-md text-body-md animate-[fadeIn_0.2s_ease-out]"
                            >
                                <div class="flex items-center gap-2 overflow-hidden mr-4">
                                    <span class="material-symbols-outlined text-vault-outline text-[1.125rem]">description</span>
                                    <span class="truncate">{{ file.name }}</span>
                                    <span class="text-vault-secondary text-[0.6875rem] font-mono-custom">({{ formatBytes(file.size) }})</span>
                                </div>
                                <button 
                                    type="button" 
                                    @click="removeFile(index)" 
                                    class="text-vault-outline hover:text-vault-error transition-colors flex items-center justify-center p-0.5 rounded-full hover:bg-vault-outline-variant/30"
                                    title="Remove file"
                                >
                                    <span class="material-symbols-outlined text-[1.125rem]">close</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Advanced Options Section -->
                    <div v-show="showAdvanced" class="pt-4 mt-2 border-t border-vault-outline-variant grid grid-cols-1 md:grid-cols-2 gap-5 animate-[fadeIn_0.2s_ease-out]">
                        <div class="flex flex-col gap-2">
                            <label class="font-label-sm text-label-sm uppercase text-vault-secondary select-none" for="recipient-email">Recipient Email(s) (Optional)</label>
                            <input
                                v-model="recipientEmail"
                                type="text"
                                id="recipient-email"
                                class="w-full bg-vault-surface-container-lowest border border-vault-outline-variant rounded py-3 px-4 font-body-md text-body-md text-vault-on-surface focus:outline-none focus:border-vault-primary focus:ring-1 focus:ring-vault-primary transition-all placeholder:text-vault-outline"
                                placeholder="e.g. user1@example.com, user2@example.com"
                            />
                        </div>
                        <div class="flex flex-col gap-2">
                            <label class="font-label-sm text-label-sm uppercase text-vault-secondary select-none" for="encryption-hint">Encryption Hint (Optional)</label>
                            <input
                                v-model="encryptionHint"
                                type="text"
                                id="encryption-hint"
                                class="w-full bg-vault-surface-container-lowest border border-vault-outline-variant rounded py-3 px-4 font-body-md text-body-md text-vault-on-surface focus:outline-none focus:border-vault-primary focus:ring-1 focus:ring-vault-primary transition-all placeholder:text-vault-outline"
                                placeholder="Add a hint to help decrypt"
                            />
                        </div>
                    </div>

                    <div class="pt-4 mt-2 border-t border-vault-outline-variant flex flex-col md:flex-row justify-between items-center gap-4">
                        <button
                            type="button"
                            @click="showAdvanced = !showAdvanced"
                            class="w-full md:w-auto bg-vault-surface-container-lowest border border-vault-outline-variant text-vault-on-surface font-label-md text-label-md py-3 px-6 rounded hover:bg-vault-surface-container-low transition-colors duration-200 scale-95 hover:scale-100 ease-in-out inline-flex items-center justify-center gap-2"
                        >
                            <span class="material-symbols-outlined text-[1.125rem] transition-transform duration-200" :class="{ 'rotate-180': showAdvanced }">expand_more</span>
                            Advanced
                        </button>
                        <button
                            type="submit"
                            :disabled="isSubmitting || (!payload.trim() && attachedFiles.length === 0)"
                            class="w-full md:w-auto bg-vault-primary text-vault-on-primary font-label-md text-label-md py-3 px-8 rounded hover:bg-vault-primary-container transition-colors flex items-center justify-center gap-2 shadow-[inset_0_-1px_0_rgba(0,0,0,0.2)] active:shadow-none active:translate-y-[1px] disabled:opacity-70 disabled:cursor-not-allowed"
                            aria-label="Encrypt and create secret link"
                        >
                            <span v-if="!isSubmitting" class="material-symbols-outlined text-[1.125rem]" style="font-variation-settings: 'FILL' 1;">lock</span>
                            <span v-else class="material-symbols-outlined text-[1.125rem] animate-spin">sync</span>
                            {{ isSubmitting ? 'Encrypting...' : 'Encrypt & Store' }}
                        </button>
                    </div>
                </form>

                <div v-else-if="isCreated" class="flex flex-col gap-6 animate-[fadeIn_0.3s_ease-out]">
                    <div class="flex items-center gap-3 text-vault-primary">
                        <span class="material-symbols-outlined text-[1.75rem]">verified</span>
                        <h2 class="font-headline-md text-headline-md font-bold">Secret Link Generated</h2>
                    </div>

                    <p class="font-body-md text-body-md text-vault-on-surface-variant">
                        This payload is encrypted and stored securely in your vault. Share this link, or keep it for yourself. It will be destroyed based on the expiry config (<strong>{{ expiry }}</strong>).
                    </p>

                    <div class="flex flex-col md:flex-row gap-6 items-center">
                        <div class="flex-shrink-0 bg-vault-surface-container-low p-4 rounded-xl border border-vault-outline-variant flex items-center justify-center bg-white">
                            <img :src="`https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=${encodeURIComponent(createdLink)}&bgcolor=ffffff&color=000000`" alt="Secret QR Code" class="rounded-md w-[7.5rem] h-[7.5rem] md:w-[9.375rem] md:h-[9.375rem]" />
                        </div>
                        <div class="flex flex-col gap-2 w-full">
                            <label class="font-label-sm text-label-sm uppercase text-vault-secondary select-none">Secret URL</label>
                            <div class="flex flex-col sm:flex-row gap-2">
                                <input
                                    readonly
                                    :value="createdLink"
                                    class="w-full bg-vault-surface-container-low border border-vault-outline-variant rounded py-3 px-4 font-mono-custom text-sm text-vault-on-surface focus:outline-none"
                                />
                                <button
                                    @click="handleCopy"
                                    type="button"
                                    class="bg-vault-primary text-vault-on-primary font-label-md text-label-md px-6 py-3 rounded hover:bg-vault-primary-container transition-colors flex items-center justify-center gap-2 whitespace-nowrap"
                                >
                                    <span class="material-symbols-outlined text-[1.125rem]">{{ copied ? 'done' : 'content_copy' }}</span>
                                    {{ copied ? 'Copied' : 'Copy' }}
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="pt-6 border-t border-vault-outline-variant/50 flex justify-end">
                        <button
                            @click="handleCreateAnother"
                            type="button"
                            class="w-full md:w-auto bg-vault-surface-container-low border border-vault-outline-variant text-vault-on-surface font-label-md text-label-md py-3 px-8 rounded hover:bg-vault-surface-container-high transition-colors"
                        >
                            Create Another
                        </button>
                    </div>
                </div>
                <div v-else-if="isDecrypting" class="flex flex-col gap-6 animate-[fadeIn_0.3s_ease-out]">
                    <div v-if="!fetchedSecretPayload" class="flex flex-col items-center justify-center py-12 gap-4">
                        <span class="material-symbols-outlined text-[2.25rem] animate-spin text-vault-primary">sync</span>
                        <p class="text-vault-secondary font-body-md">Retrieving and preparing secure payload...</p>
                    </div>
                    <div v-else class="flex flex-col gap-6">
                        <!-- Decrypt/Read View -->
                        <div class="flex items-center gap-3 text-vault-primary">
                            <span class="material-symbols-outlined text-[1.75rem]">lock_open</span>
                            <h2 class="font-headline-md text-headline-md font-bold">Secret Retrieved</h2>
                        </div>

                        <div v-if="fetchedSecretPayload?.encryption_hint" class="bg-vault-surface-container-low border border-vault-outline-variant p-4 rounded text-vault-on-surface text-sm">
                            <strong class="text-vault-secondary">Hint:</strong> {{ fetchedSecretPayload.encryption_hint }}
                        </div>

                        <div v-if="!decryptedPayload" class="flex flex-col gap-4">
                            <p class="font-body-md text-body-md text-vault-on-surface-variant">
                                This secret requires a decryption key.
                            </p>
                            
                            <div class="flex flex-col gap-2">
                                <label class="font-label-sm text-label-sm uppercase text-vault-secondary select-none">Decryption Key</label>
                                <input
                                    v-model="decryptionKey"
                                    type="password"
                                    @keyup.enter="handleDecrypt"
                                    class="w-full bg-vault-surface-container-low border border-vault-outline-variant rounded py-3 px-4 font-body-md text-body-md text-vault-on-surface focus:outline-none focus:border-vault-primary focus:ring-1 focus:ring-vault-primary transition-all placeholder:text-vault-outline"
                                    placeholder="Enter key to decrypt..."
                                />
                                <p v-if="decryptError" class="text-vault-error text-xs">{{ decryptError }}</p>
                            </div>
                            
                            <button
                                @click="handleDecrypt"
                                type="button"
                                class="bg-vault-primary text-vault-on-primary font-label-md text-label-md py-3 px-8 rounded hover:bg-vault-primary-container transition-colors mt-2"
                            >
                                Decrypt Payload
                            </button>
                        </div>

                        <div v-else class="flex flex-col gap-4">
                            <div class="flex justify-between items-center select-none">
                                <label class="font-label-sm text-label-sm uppercase text-vault-secondary select-none">Decrypted Payload</label>
                                <button
                                    @click="handleCopyDecrypted"
                                    type="button"
                                    class="text-vault-primary hover:text-vault-primary-container font-label-sm text-[0.625rem] uppercase tracking-wider transition-colors flex items-center gap-1"
                                >
                                    <span class="material-symbols-outlined text-[1rem]">{{ decryptedCopied ? 'done' : 'content_copy' }}</span>
                                    {{ decryptedCopied ? 'Copied' : 'Copy' }}
                                </button>
                            </div>
                            <pre class="w-full bg-vault-surface-container-low border border-vault-outline-variant rounded p-4 font-mono-custom text-sm text-vault-on-surface overflow-x-auto break-all whitespace-pre-wrap select-text max-h-[25rem] overflow-y-auto">{{ decryptedPayload }}</pre>
                            
                            <div v-if="decryptedFiles.length > 0" class="flex flex-col gap-2 mt-4">
                                <label class="font-label-sm text-label-sm uppercase text-vault-secondary select-none">Attached Files</label>
                                <ul class="flex flex-col gap-2">
                                    <li 
                                        v-for="(file, i) in decryptedFiles" 
                                        :key="i" 
                                        @click="downloadAndDecryptFile(i)"
                                        class="bg-vault-surface-container-low hover:bg-vault-surface-container-high border border-vault-outline-variant p-3 rounded flex items-center justify-between cursor-pointer transition-colors group"
                                    >
                                        <span class="font-mono-custom text-sm text-vault-on-surface truncate pr-4 group-hover:text-vault-primary transition-colors">{{ file.name }}</span>
                                        <div class="text-vault-primary font-label-sm text-xs flex items-center gap-1 shrink-0 select-none">
                                            <span class="material-symbols-outlined text-[1rem] animate-spin" v-if="file.isDecrypting">sync</span>
                                            <span class="material-symbols-outlined text-[1rem]" v-else>download</span>
                                            {{ file.isDecrypting ? 'Decrypting...' : 'Download' }}
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <div class="pt-6 border-t border-vault-outline-variant/50 flex justify-end">
                            <button
                                @click="handleClearRetrieved"
                                type="button"
                                class="w-full md:w-auto bg-vault-surface-container-low border border-vault-outline-variant text-vault-on-surface font-label-md text-label-md py-3 px-8 rounded hover:bg-vault-surface-container-high transition-colors"
                            >
                                Done
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div v-show="!isDecrypting" id="retrieve-section" class="mt-8 md:mt-12 w-full max-w-[35rem] flex flex-col items-center px-4">
                <h2 class="font-label-sm text-label-sm uppercase text-vault-secondary mb-4 tracking-widest text-center select-none">Retrieve a Secret</h2>

                <div class="relative w-full">
                    <input
                        v-model="retrieveId"
                        @keyup.enter="handleRetrieve"
                        class="w-full bg-transparent border border-vault-outline-variant rounded py-3 px-10 font-mono-custom text-sm text-center text-vault-on-surface focus:outline-none focus:border-vault-primary focus:ring-1 focus:ring-vault-primary transition-colors placeholder:text-vault-outline/50"
                        placeholder="Paste URL or ID..."
                        type="text"
                    />
                    <button
                        @click="handleRetrieve"
                        :disabled="!retrieveId.trim() || isRetrieving"
                        aria-label="Retrieve secret"
                        class="absolute right-0 top-1/2 -translate-y-1/2 text-vault-outline hover:text-vault-primary transition-colors flex items-center justify-center h-full px-2 disabled:opacity-30"
                    >
                        <span v-if="!isRetrieving" class="material-symbols-outlined">arrow_forward</span>
                        <span v-else class="animate-spin h-5 w-5 border-2 border-vault-primary border-t-transparent rounded-full"></span>
                    </button>
                </div>

                <div v-if="false" class="w-full mt-6 bg-vault-surface-container-low border border-vault-outline-variant rounded p-6 shadow-inner relative animate-[fadeIn_0.3s_ease-out]">
                    <button
                        @click="handleClearRetrieved"
                        aria-label="Clear retrieved secret"
                        class="absolute top-3 right-3 text-vault-outline hover:text-vault-on-surface transition-colors"
                    >
                        <span class="material-symbols-outlined text-[1.125rem]">close</span>
                    </button>
                    <pre class="font-mono-custom text-xs text-vault-on-surface whitespace-pre-wrap break-all select-text">{{ retrievedSecret }}</pre>
                </div>
            </div>
        </main>

        <footer class="w-full py-6 md:py-8 px-4 sm:px-6 md:px-12 flex flex-col md:flex-row justify-between items-center gap-6 bg-vault-surface border-t border-vault-outline-variant z-10 relative mt-auto">
            <div class="flex items-center gap-2 font-label-md text-label-md uppercase tracking-widest text-vault-on-surface">
                <img src="/ilusion-logo.png" alt="Ilusion" class="w-8 h-8 object-contain opacity-80" />
                Ilusion
            </div>
            <div class="flex gap-6 flex-wrap justify-center">
                <a class="font-label-sm text-label-sm text-vault-on-secondary-container hover:text-vault-on-surface transition-colors duration-200" href="#">Security</a>
                <a class="font-label-sm text-label-sm text-vault-on-secondary-container hover:text-vault-on-surface transition-colors duration-200" href="#">Terms</a>
                <a class="font-label-sm text-label-sm text-vault-on-secondary-container hover:text-vault-on-surface transition-colors duration-200" href="#">Privacy</a>
                <a class="font-label-sm text-label-sm text-vault-on-secondary-container hover:text-vault-on-surface transition-colors duration-200" href="#">Status</a>
                <a class="font-label-sm text-label-sm text-vault-on-secondary-container hover:text-vault-on-surface transition-colors duration-200" href="#">Docs</a>
            </div>
            <div class="font-label-sm text-label-sm text-vault-secondary">
                © 2024 Ilusion Protocol. Encrypted by default.
            </div>
        </footer>


    </div>
</template>

<style scoped>
/*
 * ============================================================
 * REQUIRED: Tailwind theme tokens (add this to your GLOBAL CSS,
 * e.g. resources/css/app.css — NOT here). Tailwind only emits
 * utilities like bg-vault-primary / text-vault-on-surface if
 * these are registered as theme colors. Without this block,
 * every vault-* class below compiles to nothing, which is the
 * most likely reason your render doesn't match the design.
 *
 * @theme {
 *   --color-vault-background: var(--vault-background);
 *   --color-vault-surface: var(--vault-surface);
 *   --color-vault-surface-container-lowest: var(--vault-surface-container-lowest);
 *   --color-vault-surface-container-low: var(--vault-surface-container-low);
 *   --color-vault-surface-container: var(--vault-surface-container);
 *   --color-vault-surface-container-high: var(--vault-surface-container-high);
 *   --color-vault-on-surface: var(--vault-on-surface);
 *   --color-vault-on-surface-variant: var(--vault-on-surface-variant);
 *   --color-vault-outline-variant: var(--vault-outline-variant);
 *   --color-vault-outline: var(--vault-outline);
 *   --color-vault-primary: var(--vault-primary);
 *   --color-vault-on-primary: var(--vault-on-primary);
 *   --color-vault-primary-container: var(--vault-primary-container);
 *   --color-vault-secondary: var(--vault-secondary);
 *   --color-vault-on-secondary-container: var(--vault-on-secondary-container);
 *   --color-vault-on-background: var(--vault-on-background);
 *   --color-vault-error: #b3261e;
 * }
 *
 * Then give --vault-* sane defaults in :root (and optionally a
 * .dark override) — the .vault-light class below will keep
 * overriding them locally regardless of the ancestor theme.
 * ============================================================
 */

.font-display {
    font-family: 'Inter', sans-serif;
    font-size: 3rem;
    font-weight: 700;
    line-height: 1.0;
    letter-spacing: -0.04em;
}
.font-headline-lg {
    font-family: 'Inter', sans-serif;
    font-size: 2rem;
    font-weight: 700;
    line-height: 1.1;
    letter-spacing: -0.02em;
}
.font-headline-md {
    font-family: 'Inter', sans-serif;
    font-size: 1.25rem;
    font-weight: 600;
    line-height: 1.2;
    letter-spacing: -0.01em;
}
.font-body-lg {
    font-family: 'Inter', sans-serif;
    font-size: 1rem;
    font-weight: 400;
    line-height: 1.5;
}
.font-body-md {
    font-family: 'Inter', sans-serif;
    font-size: 0.875rem;
    font-weight: 400;
    line-height: 1.5;
}
.font-label-md {
    font-family: 'Inter', sans-serif;
    font-size: 0.75rem;
    font-weight: 600;
    line-height: 1;
    letter-spacing: 0.08em;
}
.font-label-sm {
    font-family: 'Inter', sans-serif;
    font-size: 0.625rem;
    font-weight: 600;
    line-height: 1;
    letter-spacing: 0.1em;
}
.font-mono-custom {
    font-family: 'JetBrains Mono', monospace;
}

.text-display { font-size: 3rem; line-height: 56px; }
.text-headline-md { font-size: 1.5rem; line-height: 32px; }
.text-body-md { font-size: 0.875rem; line-height: 20px; }
.text-label-md { font-size: 0.875rem; line-height: 20px; font-weight: 500; }
.text-label-sm { font-size: 0.6875rem; line-height: 16px; font-weight: 500; }

@media (max-width: 768px) {
    .font-display { font-size: 2rem; }
    .font-headline-lg { font-size: 1.5rem; }
}

/*
 * FIX: this base rule is required for the Material Symbols
 * ligature icons (lock, expand_more, arrow_forward, verified,
 * content_copy, close) to render as glyphs instead of literal
 * text. The Google Fonts <link> only loads the font file — it
 * does not include this class.
 */
.material-symbols-outlined {
    font-family: 'Material Symbols Outlined';
    font-weight: normal;
    font-style: normal;
    font-size: 1.5rem;
    line-height: 1;
    letter-spacing: normal;
    text-transform: none;
    display: inline-block;
    white-space: nowrap;
    word-wrap: normal;
    direction: ltr;
    -webkit-font-feature-settings: 'liga';
    -webkit-font-smoothing: antialiased;
    font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
}

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
    --color-vault-background: #f7f9fb;
    --color-vault-surface: #f7f9fb;
    --color-vault-surface-container-lowest: #ffffff;
    --color-vault-surface-container-low: #f2f4f6;
    --color-vault-surface-container: #eceef0;
    --color-vault-surface-container-high: #e6e8ea;
    --color-vault-on-surface: #191c1e;
    --color-vault-on-surface-variant: #434655;
    --color-vault-outline-variant: #c3c6d7;
    --color-vault-outline: #737686;
    --color-vault-primary: #004ac6;
    --color-vault-on-primary: #ffffff;
    --color-vault-primary-container: #2563eb;
    --color-vault-secondary: #565e74;
    --color-vault-on-secondary-container: #5c647a;
    --color-vault-on-background: #191c1e;
    --color-vault-on-primary-fixed: #00174b;
    --color-vault-primary-fixed: #dbe1ff;
    color-scheme: light;
}

.bg-dot-grid {
    background-image: radial-gradient(#c3c6d7 1px, transparent 1px);
    background-size: 24px 24px;
    background-position: -11px -11px;
    opacity: 0.3;
}

.preview-container :deep(h1) {
    font-size: 1.25rem;
    font-weight: 700;
    margin-top: 16px;
    margin-bottom: 8px;
    border-bottom: 1px solid var(--color-vault-outline-variant);
    padding-bottom: 4px;
    color: var(--color-vault-on-surface);
}
.preview-container :deep(h2) {
    font-size: 1.125rem;
    font-weight: 700;
    margin-top: 14px;
    margin-bottom: 8px;
    color: var(--color-vault-on-surface);
}
.preview-container :deep(h3) {
    font-size: 0.9375rem;
    font-weight: 600;
    margin-top: 12px;
    margin-bottom: 6px;
    color: var(--color-vault-on-surface);
}
.preview-container :deep(pre) {
    background-color: var(--color-vault-surface-container);
    border: 1px solid var(--color-vault-outline-variant);
    border-radius: 4px;
    padding: 12px;
    margin: 12px 0;
    font-family: 'JetBrains Mono', monospace;
    font-size: 0.75rem;
    overflow-x: auto;
}
.preview-container :deep(code) {
    background-color: var(--color-vault-surface-container-low);
    padding: 2px 6px;
    border-radius: 4px;
    font-family: 'JetBrains Mono', monospace;
    font-size: 0.75rem;
    color: var(--color-vault-primary);
}
.preview-container :deep(ul) {
    list-style-type: disc;
    margin-left: 20px;
    margin-top: 8px;
    margin-bottom: 8px;
}
.preview-container :deep(li) {
    margin-bottom: 4px;
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
