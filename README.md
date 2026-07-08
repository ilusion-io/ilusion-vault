# Ilusion Vault 

**Open-source, zero-knowledge encrypted data vault for storing and sharing passwords, API keys, and files securely.**

Ilusion Vault lets you encrypt sensitive data directly in your browser and store it permanently — or share it via self-destructing links. The server never sees your plaintext. Access your vault from anywhere with just a URL and your encryption key.

**Live:** [ilusion.io](https://ilusion.io)

---

## Why Ilusion Vault?

- Encrypted Vault — Store passwords, API keys, `.env` files, and notes in a persistent encrypted vault accessible from any device.
- Secure Sharing — Generate self-destructing encrypted links to share credentials safely instead of pasting them in Slack, email, or chat.
- Zero-Knowledge — The server only stores ciphertext. Your encryption key travels via the URL hash (`#key`), which browsers never send to the server.
- Open Source — Fully transparent. Audit the code, self-host on your own server, or use our cloud-hosted version at [ilusion.io](https://ilusion.io).

## Features

-  **End-to-End Encryption** — AES-GCM (256-bit) client-side encryption via the Web Crypto API. Plaintext never leaves your browser.
- **Encrypted File Uploads** — Attach multiple files. Each file is encrypted in-browser before upload to S3/Cloudflare R2.
-  **Auto-Burn & Expiry** — Set data to self-destruct after one view, or expire after 1 Hour, 1 Day, 1 Week — or store permanently.
-  **Recipient Notifications** — Notify multiple recipients via email when you share encrypted data.
-  **Encryption Hints** — Add a hint to help the recipient remember the decryption key without exposing it.
-  **Two-Factor Authentication** — Secure your account with 2FA and WebAuthn Passkeys.
-  **QR Code Sharing** — Every encrypted link comes with a QR code for easy mobile access.

## Tech Stack

| Layer | Technology |
|---|---|
| Backend | Laravel 11, PHP 8.3+ |
| Frontend | Vue 3 (Composition API), Inertia.js |
| Styling | Tailwind CSS v4 |
| Auth | Laravel Fortify, WebAuthn (Passkeys), 2FA |
| Storage | S3-compatible (Cloudflare R2) |

## Requirements

- PHP 8.2+
- Composer
- Node.js (v18+) & NPM
- SQLite, MySQL, or PostgreSQL
- An S3-compatible storage provider (e.g., Cloudflare R2) for encrypted file uploads

## Quick Start

```bash
# 1. Clone the repository
git clone https://github.com/ilusion-io/ilusion-vault.git
cd ilusion-vault

# 2. Install dependencies
composer install
npm install

# 3. Configure environment
cp .env.example .env
php artisan key:generate

# 4. Run migrations
php artisan migrate

# 5. Build frontend & start server
npm run build
php artisan serve
```

### Configure Encrypted File Storage (Cloudflare R2 / S3)

Add the following to your `.env` file:

```env
FILESYSTEM_DISK=r2

R2_ACCESS_KEY_ID="your_access_key"
R2_SECRET_ACCESS_KEY="your_secret_key"
R2_BUCKET_NAME="your_bucket"
R2_URL="https://your-custom-domain.com"
R2_ENDPOINT="https://<ACCOUNT_ID>.r2.cloudflarestorage.com"
```

## How It Works

```
┌─────────────┐     Encrypted Data      ┌─────────────┐
│   Browser    │ ──────────────────────► │   Server    │
│ (Encrypts)   │                         │ (Stores     │
│              │     URL + #Key          │  Ciphertext)│
│              │ ◄────────────────────── │             │
└─────────────┘                          └─────────────┘
```

1. You enter your data and an encryption key in the browser.
2. The browser encrypts everything locally using AES-256-GCM.
3. Only the **ciphertext** is sent to the server.
4. You receive a URL with the decryption key in the hash fragment (`#key`).
5. The hash fragment is **never** transmitted to the server — it stays client-side.

## Managed Hosting

Don't want to self-host? Use our cloud-hosted version at **[ilusion.io](https://ilusion.io)** — or [contact us](https://ilusion.io/contact) if you need help deploying Ilusion Vault on your own infrastructure.

## Contributing

Contributions, issues, and feature requests are welcome! Feel free to check the [issues page](https://github.com/ilusion-io/ilusion-vault/issues).

## License

This project is licensed under the MIT License — see the [LICENSE](LICENSE) file for details.
