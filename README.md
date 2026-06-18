# Ilusion Secrets 🤫

Ilusion Secrets is a high-security, end-to-end encrypted (E2EE) secret sharing and personal vault application. It allows you to generate zero-knowledge encrypted links containing sensitive text payloads and files.

Built with Laravel, Vue 3 (Inertia.js), and Tailwind CSS, it offers a beautifully designed aesthetic with strong client-side encryption. The backend never sees the plaintext of your secrets.

## Features

- 🔒 **End-to-End Encryption (E2EE)**: All payloads and files are encrypted and decrypted directly in the browser using AES-GCM (256-bit).
- 🙈 **Zero-Knowledge Architecture**: The server only stores the ciphertext. The decryption key is passed via the URL hash fragment (`#key`), which is never sent to the server.
- 📁 **Secure File Vaulting**: Attach multiple files to your secrets. Files are encrypted client-side before being uploaded to S3/Cloudflare R2.
- ⏱ **Auto-Burn & Expiry**: Set secrets to automatically self-destruct after being read once, or after a specific time limit (1 Hour, 1 Day, 1 Week, or Never for permanent vaulting).
- 📧 **Multi-Recipient Delivery**: Notify multiple users via email when a secret is shared.
- 🔑 **Two-Factor Authentication (2FA)**: High-security authentication for vault owners.

## Requirements

- PHP 8.2+
- Composer
- Node.js (v18+) & NPM
- SQLite, MySQL, or PostgreSQL
- An S3-compatible storage provider (e.g., Cloudflare R2) for file uploads

## Installation & Setup

1. **Clone the repository:**
   ```bash
   git clone https://github.com/ilusion-io/ilusion-secrets.git
   cd ilusion-secrets
   ```

2. **Install PHP dependencies:**
   ```bash
   composer install
   ```

3. **Install Node dependencies:**
   ```bash
   npm install
   ```

4. **Environment Configuration:**
   Copy the `.env.example` to `.env`:
   ```bash
   cp .env.example .env
   ```
   Generate the application key:
   ```bash
   php artisan key:generate
   ```

5. **Configure Cloudflare R2 / S3:**
   Open your `.env` file and configure your storage provider:
   ```env
   FILESYSTEM_DISK=r2

   R2_ACCESS_KEY_ID="your_access_key"
   R2_SECRET_ACCESS_KEY="your_secret_key"
   R2_BUCKET_NAME="your_bucket"
   R2_URL="https://your-custom-domain.com"
   R2_ENDPOINT="https://<ACCOUNT_ID>.r2.cloudflarestorage.com"
   ```

6. **Run Migrations:**
   ```bash
   php artisan migrate
   ```

7. **Compile Frontend Assets:**
   ```bash
   npm run build
   # Or run the dev server: npm run dev
   ```

8. **Start the Laravel Server:**
   ```bash
   php artisan serve
   ```

## Contributing
Contributions, issues, and feature requests are welcome. Feel free to check the issues page if you want to contribute.

## License
This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.
