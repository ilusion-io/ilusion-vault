<x-mail::message>
# Verify Your Ilusion Account

Hello {{ $name }},

Thank you for registering an account with **Ilusion**, the secure, client-side encrypted secret sharing platform.

Please verify your email address to active your account and start sharing secrets securely.

<x-mail::button :url="$url" color="primary">
Verify Email Address
</x-mail::button>

If you did not register for an account, you can safely ignore this email.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
