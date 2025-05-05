<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Email Verification | Arxitest</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.5;
            color: #333;
            padding: 20px;
            max-width: 600px;
            margin: 0 auto;
        }
        .logo {
            max-width: 150px;
            margin-bottom: 20px;
        }
        .card {
            border-radius: 8px;
            background-color: #fff;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 25px;
            margin-bottom: 20px;
        }
        .header {
            font-weight: bold;
            font-size: 24px;
            margin-bottom: 16px;
            color: #333;
        }
        .content {
            margin-bottom: 24px;
            color: #555;
        }
        .verification-code {
            font-size: 32px;
            font-weight: bold;
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 16px;
            text-align: center;
            letter-spacing: 3px;
            margin: 24px 0;
        }
        .footer {
            font-size: 12px;
            color: #888;
            margin-top: 40px;
            padding-top: 16px;
            border-top: 1px solid #eee;
        }
    </style>
</head>
<body>
    <div style="text-align: center;">
        <img src="{{ asset('images/logo.png') }}" alt="Arxitest Logo" class="logo">
    </div>

    <div class="card">
        <div class="header">Verify Your Email Address</div>

        <div class="content">
            <p>Hello,</p>
            <p>Thanks for registering with Arxitest! To complete your registration, please enter the verification code below in the verification page:</p>
        </div>

        <div class="verification-code">
            {{ $code }}
        </div>

        <div class="content">
            <p>This code will expire in 10 minutes.</p>
            <p>If you didn't request this code, you can safely ignore this email.</p>
        </div>
    </div>

    <div class="footer">
        <p>© {{ date('Y') }} Arxitest. All rights reserved.</p>
        <p>If you have any questions, please contact our support team.</p>
    </div>
</body>
</html>
