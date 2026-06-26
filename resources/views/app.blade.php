<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"  @class(['dark' => ($appearance ?? 'system') == 'dark'])>
    <head>
        <!-- Google tag (gtag.js) -->
        <script async src="https://www.googletagmanager.com/gtag/js?id=G-Y6YRXZ0046"></script>
        <script>
          window.dataLayer = window.dataLayer || [];
          function gtag(){dataLayer.push(arguments);}
          gtag('js', new Date());

          gtag('config', 'G-Y6YRXZ0046');
        </script>
        
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <!-- Open Graph / Facebook -->
        <meta property="og:type" content="website" />
        <meta property="og:url" content="{{ url()->current() }}" />
        <meta property="og:title" content="Ilusion Vault — Encrypted Data Vault & Secure Sharing" />
        <meta property="og:description" content="Store and share passwords, API keys, and sensitive files. Zero-knowledge encryption in the browser. Open source system." />
        <meta property="og:image" content="{{ url('/social-preview.png') }}" />

        <!-- Twitter / X -->
        <meta name="twitter:card" content="summary_large_image" />
        <meta name="twitter:url" content="{{ url()->current() }}" />
        <meta name="twitter:title" content="Ilusion Vault — Encrypted Data Vault & Secure Sharing" />
        <meta name="twitter:description" content="Store and share passwords, API keys, and sensitive files. Zero-knowledge encryption in the browser. Open source system." />
        <meta name="twitter:image" content="{{ url('/social-preview.png') }}" />
        <meta name="twitter:site" content="@shreyashdata" />

        {{-- Inline script to detect system dark mode preference and apply it immediately --}}
        <script>
            (function() {
                const appearance = '{{ $appearance ?? "system" }}';

                if (appearance === 'system') {
                    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

                    if (prefersDark) {
                        document.documentElement.classList.add('dark');
                    }
                }
            })();
        </script>

        {{-- Inline style to set the HTML background color based on our theme in app.css --}}
        <style>
            html {
                background-color: oklch(1 0 0);
            }

            html.dark {
                background-color: oklch(0.145 0 0);
            }
        </style>

        <link rel="icon" type="image/png" href="/favicon-dark.png">
        <link rel="apple-touch-icon" href="/favicon-dark.png">

        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
        <link rel="preload" as="style" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" />
        <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet" media="print" onload="this.media='all'" />

        @fonts

        @vite(['resources/css/app.css', 'resources/js/app.ts', "resources/js/pages/{$page['component']}.vue"])
        <x-inertia::head>
            <title>{{ config('app.name', 'Laravel') }}</title>
        </x-inertia::head>

        <script type="application/ld+json">
        {
            "@@context": "https://schema.org",
            "@@type": "SoftwareApplication",
            "name": "Ilusion Vault",
            "description": "Open-source, zero-knowledge encrypted vault for storing and sharing passwords, API keys, and files securely. Browser-encrypted with AES-256.",
            "url": "https://ilusion.io",
            "applicationCategory": "SecurityApplication",
            "operatingSystem": "Web",
            "offers": [
                {
                    "@@type": "Offer",
                    "price": "0",
                    "priceCurrency": "USD",
                    "name": "Free"
                },
                {
                    "@@type": "Offer",
                    "price": "2",
                    "priceCurrency": "USD",
                    "name": "Pro"
                }
            ],
            "creator": {
                "@@type": "Organization",
                "name": "Ilusion",
                "url": "https://ilusion.io"
            }
        }
        </script>
    </head>
    <body class="font-sans antialiased">
        <x-inertia::app />
    </body>
</html>
