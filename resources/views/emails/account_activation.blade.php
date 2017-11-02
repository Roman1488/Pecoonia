<!DOCTYPE html>
<html lang="en-US">
    <head>
        <meta charset="utf-8">
    </head>
    <body>
        <h2>Account Activation</h2>
        <div>Username: {{ $username }}</div>
        <div>
            Click here to activate your account: <a href="{{ $link }}"> {{ $link }} </a>
        </div>
    </body>
</html>