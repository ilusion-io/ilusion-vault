export async function deriveKey(password: string, salt: Uint8Array): Promise<CryptoKey> {
    const encoder = new TextEncoder();
    const baseKey = await window.crypto.subtle.importKey(
        'raw',
        encoder.encode(password),
        'PBKDF2',
        false,
        ['deriveKey']
    );
    return window.crypto.subtle.deriveKey(
        {
            name: 'PBKDF2',
            salt: salt as any,
            iterations: 10000,
            hash: 'SHA-256'
        },
        baseKey,
        { name: 'AES-GCM', length: 256 },
        false,
        ['encrypt', 'decrypt']
    );
}

function arrayBufferToBase64(buffer: ArrayBuffer): string {
    let binary = '';
    const bytes = new Uint8Array(buffer);
    const len = bytes.byteLength;
    for (let i = 0; i < len; i++) {
        binary += String.fromCharCode(bytes[i]);
    }
    return window.btoa(binary);
}

export function base64ToArrayBuffer(base64: string): ArrayBuffer {
    const binaryString = window.atob(base64);
    const len = binaryString.length;
    const bytes = new Uint8Array(len);
    for (let i = 0; i < len; i++) {
        bytes[i] = binaryString.charCodeAt(i);
    }
    return bytes.buffer;
}

export async function encryptText(text: string, password: string): Promise<string> {
    const salt = window.crypto.getRandomValues(new Uint8Array(16));
    const iv = window.crypto.getRandomValues(new Uint8Array(12));
    const key = await deriveKey(password, salt);
    
    const encoder = new TextEncoder();
    const encrypted = await window.crypto.subtle.encrypt(
        { name: 'AES-GCM', iv: iv },
        key,
        encoder.encode(text)
    );
    
    const result = {
        ciphertext: arrayBufferToBase64(encrypted),
        salt: arrayBufferToBase64(salt.buffer),
        iv: arrayBufferToBase64(iv.buffer)
    };
    
    return JSON.stringify(result);
}

export async function decryptText(jsonStr: string, password: string): Promise<string> {
    try {
        const { ciphertext, salt, iv } = JSON.parse(jsonStr);
        const saltBuffer = base64ToArrayBuffer(salt);
        const ivBuffer = base64ToArrayBuffer(iv);
        const ciphertextBuffer = base64ToArrayBuffer(ciphertext);
        
        const key = await deriveKey(password, new Uint8Array(saltBuffer));
        const decrypted = await window.crypto.subtle.decrypt(
            { name: 'AES-GCM', iv: new Uint8Array(ivBuffer) },
            key,
            ciphertextBuffer
        );
        
        const decoder = new TextDecoder();
        return decoder.decode(decrypted);
    } catch (e) {
        throw new Error('Incorrect decryption key or corrupted payload.');
    }
}

export async function encryptFile(
    file: File,
    password: string
): Promise<{
    encryptedBlob: Blob;
    metadata: {
        encrypted_metadata: string;
        salt: string;
        iv: string;
    };
}> {
    // 1. Encrypt file content
    const fileSalt = window.crypto.getRandomValues(new Uint8Array(16));
    const fileIv = window.crypto.getRandomValues(new Uint8Array(12));
    const fileKey = await deriveKey(password, fileSalt);
    
    const fileBuffer = await file.arrayBuffer();
    const encryptedContent = await window.crypto.subtle.encrypt(
        { name: 'AES-GCM', iv: fileIv },
        fileKey,
        fileBuffer
    );
    
    // Create the self-contained Blob: Salt (16B) + IV (12B) + Ciphertext
    const header = new Uint8Array(16 + 12);
    header.set(fileSalt, 0);
    header.set(fileIv, 16);
    
    const encryptedBlob = new Blob([header, encryptedContent], { type: 'application/octet-stream' });
    
    // 2. Encrypt metadata
    const metaSalt = window.crypto.getRandomValues(new Uint8Array(16));
    const metaIv = window.crypto.getRandomValues(new Uint8Array(12));
    const metaKey = await deriveKey(password, metaSalt);
    
    const metaJson = JSON.stringify({
        name: file.name,
        type: file.type || 'application/octet-stream',
        size: file.size
    });
    
    const encoder = new TextEncoder();
    const encryptedMeta = await window.crypto.subtle.encrypt(
        { name: 'AES-GCM', iv: metaIv },
        metaKey,
        encoder.encode(metaJson)
    );
    
    return {
        encryptedBlob,
        metadata: {
            encrypted_metadata: arrayBufferToBase64(encryptedMeta),
            salt: arrayBufferToBase64(metaSalt.buffer),
            iv: arrayBufferToBase64(metaIv.buffer)
        }
    };
}

export async function decryptFile(
    encryptedBuffer: ArrayBuffer,
    password: string,
    metaJsonStr: string,
    metaSaltStr: string,
    metaIvStr: string
): Promise<{ decryptedFile: File; name: string; type: string }> {
    // 1. Decrypt metadata
    const metaSalt = new Uint8Array(base64ToArrayBuffer(metaSaltStr));
    const metaIv = new Uint8Array(base64ToArrayBuffer(metaIvStr));
    const metaCiphertext = base64ToArrayBuffer(metaJsonStr);
    
    const metaKey = await deriveKey(password, metaSalt);
    const decryptedMeta = await window.crypto.subtle.decrypt(
        { name: 'AES-GCM', iv: metaIv },
        metaKey,
        metaCiphertext
    );
    
    const decoder = new TextDecoder();
    const { name, type } = JSON.parse(decoder.decode(decryptedMeta));
    
    // 2. Decrypt file content (extract Salt and IV from the first 28 bytes)
    const fileSalt = new Uint8Array(encryptedBuffer.slice(0, 16));
    const fileIv = new Uint8Array(encryptedBuffer.slice(16, 28));
    const ciphertext = encryptedBuffer.slice(28);
    
    const fileKey = await deriveKey(password, fileSalt);
    const decryptedContent = await window.crypto.subtle.decrypt(
        { name: 'AES-GCM', iv: fileIv },
        fileKey,
        ciphertext
    );
    
    const decryptedBlob = new Blob([decryptedContent], { type: type });
    const decryptedFile = new File([decryptedBlob], name, { type: type });
    
    return { decryptedFile, name, type };
}
