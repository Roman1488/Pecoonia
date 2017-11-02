<!DOCTYPE html>
<html lang="en-US">
    <head>
        <meta charset="utf-8">
    </head>
    <body>

        <p>From: {{ $name }} <{{ $email }}></p>
        <p>Subject: {{ $subject }}</p>

        <p>
            Message Body: <br>
            {{ $bodyMessage }}
        </p>

        -- 
        This e-mail was sent from a contact form on Pecoonia (http://staging.pecoonia.com)
    </body>
</html>