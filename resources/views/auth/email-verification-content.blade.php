<!DOCTYPE html>
<html>
<head>
    <title>Email Verification</title>
    <meta name="color-scheme" content="light only">
    <meta name="supported-color-schemes" content="light only">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap');
    </style>
</head>
<body style="font-family: 'Inter', Arial, sans-serif; margin: 0; padding: 0; background-color: #f8f8f8;">
    <div style="max-width: 600px; margin: 20px auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);">
        <!-- Dark-Mode Safe Header -->
        <div style="background: #252424 !important; padding: 30px 20px; text-align: center; border: 1px solid #000000;">
            <img
                src="https://i.ibb.co/4g5zNjdJ/logo-icon-w.png"
                alt="Arxitest Logo"
                style="max-height: 50px; max-width: 200px; display: block; margin: 0 auto;"
            >
        </div>
        <!-- Content -->
        <div style="padding: 30px;">
            <h1 style="color: #000000; font-weight: 600; font-size: 24px; margin-top: 0; margin-bottom: 20px;">Verify Your Email Address</h1>

            <p style="margin-bottom: 25px; color: #555555;">Thank you for registering with Arxitest. To complete your registration, please use the verification code below:</p>

            <!-- Verification code box -->
            <div style="background-color: #f0f0f0; padding: 20px; text-align: center; margin: 30px 0; border-radius: 6px; border-left: 4px solid #000000;">
                <h2 style="margin: 0; font-size: 28px; letter-spacing: 5px; color: #000000; font-weight: 600;">{{ $code }}</h2>
            </div>

            <p style="margin-bottom: 25px; color: #555555;">This code will expire in <strong>10 minutes</strong> for security reasons.</p>

            <p style="margin-bottom: 25px; color: #777777; font-size: 14px;">If you did not request this verification, please ignore this email or contact our support team if you have any concerns.</p>

            <div style="border-top: 1px solid #eeeeee; padding-top: 20px; margin-top: 30px;">
                <p style="margin: 0; color: #999999; font-size: 13px;">Thanks,<br>The <strong>Arxitest</strong> Team</p>
            </div>
        </div>

        <!-- Footer -->
        <div style="background: #f5f5f5; padding: 15px 30px; text-align: center; border-top: 1px solid #eeeeee;">
            <p style="margin: 0; color: #999999; font-size: 12px;">© 2023 Arxitest. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
