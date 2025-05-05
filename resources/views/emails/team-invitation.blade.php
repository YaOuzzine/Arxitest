<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Invitation to {{ $team->name }} on Arxitest</title>
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
        .button {
            display: inline-block;
            background-color: #4F46E5;
            color: white;
            text-decoration: none;
            padding: 12px 20px;
            border-radius: 6px;
            font-weight: 500;
            margin-bottom: 16px;
        }
        .button:hover {
            background-color: #4338CA;
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
        <div class="header">You've been invited to join {{ $team->name }}</div>

        <div class="content">
            <p>Hello,</p>
            <p>{{ $inviterName }} has invited you to join their team on Arxitest - the intelligent test automation platform that streamlines your software testing lifecycle.</p>
            <p>You've been invited as a <strong>{{ ucfirst($role) }}</strong> on the team.</p>
        </div>

        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ route('invitations.accept', $token) }}" class="button">Accept Invitation</a>
        </div>

        <div class="content">
            <p>If you don't have an Arxitest account yet, you'll be able to create one after accepting the invitation.</p>
            <p>This invitation will expire in 7 days.</p>
        </div>
    </div>

    <div class="footer">
        <p>© {{ date('Y') }} Arxitest. All rights reserved.</p>
        <p>If you didn't expect this invitation, you can safely ignore this email.</p>
    </div>
</body>
</html>
