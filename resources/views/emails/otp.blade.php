<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Kode OTP KasSaku</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .container {
            background-color: #f9f9f9;
            border-radius: 10px;
            padding: 30px;
            border: 1px solid #e0e0e0;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #6366f1; /* StitchPrimary style */
        }
        .otp-code {
            font-size: 32px;
            font-weight: bold;
            letter-spacing: 5px;
            text-align: center;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            border: 2px dashed #6366f1;
            margin: 20px 0;
            color: #6366f1;
        }
        .footer {
            margin-top: 30px;
            font-size: 12px;
            color: #777;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">KasSaku</div>
        </div>
        
        <p>Halo, <strong>{{ $username }}</strong>!</p>
        
        <p>Kami menerima permintaan untuk mereset kata sandi akun KasSaku Anda. Gunakan kode OTP di bawah ini untuk melanjutkan proses reset password:</p>
        
        <div class="otp-code">
            {{ $otp }}
        </div>
        
        <p>Kode ini berlaku selama <strong>10 menit</strong>. Jika Anda tidak merasa melakukan permintaan ini, silakan abaikan email ini.</p>
        
        <p>Terima kasih,<br>Tim KasSaku</p>
        
        <div class="footer">
            &copy; {{ date('Y') }} KasSaku. Semua hak dilindungi.
        </div>
    </div>
</body>
</html>
