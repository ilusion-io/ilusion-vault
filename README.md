# Ilusion Secrets

A secret-sharing and personal vault tool built around one principle: the server should never know what you're storing. Everything is encrypted in the browser before it leaves your device, and the decryption key never touches our backend.

Built with Laravel, Vue 3 (Inertia.js), and Tailwind CSS.

---

## How it works

When you create a secret, Ilusion encrypts your text or files directly in the browser using AES-GCM 256-bit encryption. The resulting ciphertext is sent to the server — the key is not. Instead, the key travels as a URL hash fragment (the part after `#`), which browsers never include in HTTP requests. Only someone with the full link can decrypt the secret.

---

## Features

**End-to-end encryption** — Secrets are encrypted and decrypted entirely client-side. The server stores only ciphertext it cannot read.

**Secure file attachments** — Attach multiple files to any secret. Each file is encrypted before upload and stored on S3-compatible storage (Cloudflare R2 or equivalent).

**Burn after reading** — Set a secret to self-destruct after the first view, or after a fixed window: one hour, one day, one week, or never (for permanent vaulting).

**Email delivery** — Share secrets with multiple recipients by email. They receive a notification with the secure link.

**Two-factor authentication** — Vault owners can protect their accounts with TOTP-based 2FA.

---

## Requirements

- PHP 8.2 or later
- Composer
- Node.js v18 or later with NPM
- SQLite, MySQL, or PostgreSQL
- An S3-compatible storage provider (Cloudflare R2 recommended)

---

## Setup

**1. Clone the repository**

```bash
git clone https://github.com/ilusion-io/ilusion-secrets.git
cd ilusion-secrets
```

**2. Install dependencies**

```bash
composer install
npm install
```

**3. Configure the environment**

```bash
cp .env.example .env
php artisan key:generate
```

**4. Configure storage**

Open `.env` and add your S3 / Cloudflare R2 credentials:

```env
FILESYSTEM_DISK=r2

R2_ACCESS_KEY_ID="your_access_key"
R2_SECRET_ACCESS_KEY="your_secret_key"
R2_BUCKET_NAME="your_bucket"
R2_URL="https://your-custom-domain.com"
R2_ENDPOINT="https://<ACCOUNT_ID>.r2.cloudflarestorage.com"
```

**5. Run migrations**

```bash
php artisan migrate
```

**6. Build the frontend**

```bash
npm run build
```

For local development, use `npm run dev` to start the Vite dev server with hot reload.

**7. Start the server**

```bash
php artisan serve
```

---

## Contributing

Issues and pull requests are welcome. If you have a feature idea or run into a bug, open an issue first so we can discuss it before you spend time on a PR.

## License

MIT — see [LICENSE](LICENSE) for details.
