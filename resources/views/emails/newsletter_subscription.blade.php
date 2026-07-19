<!DOCTYPE html>
<html>
<head>
    <title>Langganan Newsletter Baru</title>
</head>
<body style="font-family: sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <h2>Pesan Hubungi Kami Baru</h2>
    <p>Halo Admin RuangIT,</p>
    <p>Ada kiriman pesan baru melalui form kontak (Hubungi Kami) di website Anda:</p>
    <div style="background: #f4f4f5; padding: 16px; border-radius: 8px; font-size: 15px; border: 1px solid #e4e4e7; margin: 15px 0;">
        <p style="margin: 0 0 10px;"><strong>Pengirim:</strong> {{ $email }}</p>
        <p style="margin: 0;"><strong>Pesan:</strong></p>
        <p style="margin: 5px 0 0; white-space: pre-wrap; font-style: italic; color: #555;">{{ $userMessage }}</p>
    </div>
    <p>Silakan balas pesan ini langsung ke alamat email pengirim di atas.</p>
</body>
</html>
