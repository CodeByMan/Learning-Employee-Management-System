<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $announcement->title }}</title>
</head>
<body style="font-family:Arial,sans-serif;color:#1f2937;">
    <h1 style="font-size:24px;">{{ $announcement->title }}</h1>
    <div style="white-space:pre-wrap;line-height:1.6;">{{ $announcement->content }}</div>
    <p style="margin-top:24px;color:#6b7280;font-size:13px;">Sent by {{ $announcement->sent_by }}</p>
</body>
</html>
