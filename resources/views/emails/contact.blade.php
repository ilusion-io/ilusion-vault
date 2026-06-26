<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Contact Form Submission</title>
    <style>
        body {font-family: Arial, sans-serif; background-color: #f9fafb; color: #1a202c; padding: 20px;}
        .container {max-width: 600px; margin: auto; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);}
        h2 {color: #2d3748;}
        p {margin: 0.5em 0;}
    </style>
</head>
<body>
<div class="container">
    <h2>New Contact Message from Ilusion Vault</h2>
    <p><strong>Name:</strong> {{ $name }}</p>
    <p><strong>Email:</strong> {{ $email }}</p>
    <p><strong>Message:</strong></p>
    <p>{{ $messageBody }}</p>
    <hr/>
    <p>You can also directly email us at <a href="mailto:hello@ilusion.io">hello@ilusion.io</a>.</p>
</div>
</body>
</html>
