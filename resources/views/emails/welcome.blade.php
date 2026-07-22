<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Welcome to Employee LEMS</title>
</head>
<body style="margin:0;padding:24px;background:#f3f4f6;color:#1f2937;font-family:Arial,sans-serif;">
    <div style="max-width:640px;margin:0 auto;background:#ffffff;border:1px solid #e5e7eb;border-radius:12px;padding:32px;">
        <h1 style="margin-top:0;color:#1d4ed8;font-size:24px;">Welcome to Employee LEMS, {{ $user->name }}!</h1>
        <p>Your account has been created successfully.</p>
        <p>Your registered email address is <strong>{{ $user->email }}</strong>.</p>
        <p>You can now sign in and use the features available to your assigned role. Contact an administrator if your role or account details need correction.</p>
        <p style="margin-top:32px;color:#4b5563;">
            Regards,<br>
            <strong>Muhammad Ali Nawaz</strong><br>
            Employee LEMS
        </p>
    </div>
</body>
</html>
