<!DOCTYPE html>
<html>
<head>
    <title>Reply to Your Message</title>
</head>
<body>
<p>Hello {{ $name }},</p>

<p>You recently sent us this message:</p>
<blockquote>
    "{{ $originalMessage }}"
</blockquote>

<p>Here is our reply:</p>
<p><strong>{{ $replyMessage }}</strong></p>

<p>Best regards,<br>{{ $senderName }}</p>
</body>
</html>
