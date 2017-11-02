<!DOCTYPE html>
<html lang="en-US">
    <head>
        <meta charset="utf-8">
    </head>
    <body>
        <h2>Password Reset</h2>
        <div>Username: {{ $user->user_name }}</div>
        <div>
            Click here to reset your password: <a href="{{ $link = url('/#!/password/reset', $token).'?email='.urlencode($user->getEmailForPasswordReset()) }}"> {{ $link }} </a><br />
            This link will expire in {{ config('auth.passwords.users.expire') }} minutes.
        </div>
    </body>
</html>