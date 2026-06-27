<x-mail::message>
# You Have a Secure Message

{{ $senderName ? $senderName . ' has' : 'Someone has' }} securely shared an encrypted secret with you via **Ilusion**.

To maintain zero-knowledge security, the contents of this secret are encrypted client-side. We cannot read it. 

<x-mail::button :url="$secretUrl" color="primary">
Retrieve Secret
</x-mail::button>


---

**Note:** If the secret was configured to "Burn on View", it will be permanently deleted immediately after it is read for the first time.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
