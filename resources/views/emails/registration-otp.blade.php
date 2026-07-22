<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>Employee LEMS registration code</title></head>
<body style="font-family: Arial, sans-serif; color: #212529;">
    <h2>Employee LEMS Registration</h2>
    <p>Hello {{ $recipientName }},</p>
    <p>An administrator started an Employee LEMS account registration for this email address.</p>
    <p>Your one-time registration code is:</p>
    <p style="font-size: 28px; font-weight: bold; letter-spacing: 4px;">{{ $otp }}</p>
    <p>This code expires in 10 minutes. Ignore this message if you were not expecting an account.</p>
</body>
</html>
