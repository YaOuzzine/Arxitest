<!-- resources/views/emails/team-invitation.blade.php -->
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #4b5563;
            background-color: #f9fafb;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        .header {
            text-align: center;
            padding: 20px 0;
            border-bottom: 1px solid #e5e7eb;
        }
        .content {
            padding: 20px 0;
        }
        .button {
            display: inline-block;
            background-color: #3b82f6;
            color: #ffffff;
            font-weight: 600;
            text-decoration: none;
            padding: 12px 24px;
            border-radius: 6px;
            margin: 20px 0;
            text-align: center;
        }
        .footer {
            text-align: center;
            padding: 20px 0;
            color: #9ca3af;
            font-size: 12px;
            border-top: 1px solid #e5e7eb;
        }
        .highlight {
            font-weight: 600;
            color: #374151;
        }
        .team-logo {
            width: 64px;
            height: 64px;
            border-radius: 8px;
            object-fit: cover;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="{{ asset('images/logo-icon.svg') }}" alt="Arxitest Logo" width="120">
        </div>

        <div class="content">
            <h2>You've been invited to join a team on Arxitest</h2>

            <p>Hello,</p>

            <p><span class="highlight">{{ $inviterName }}</span> has invited you to join the team <span class="highlight">"{{ $team->name }}"</span> on Arxitest as a <span class="highlight">{{ ucfirst($role) }}</span>.</p>

            @if($team->description)
                <p>{{ $team->description }}</p>
            @endif

            <p>To accept this invitation, please click the button below:</p>

            <div style="text-align: center;">
                <a href="{{ url('invitations/accept/' . $token) }}" class="button">Accept Invitation</a>
            </div>

            <p style="font-size: 14px; color: #6b7280;">This invitation will expire in 7 days. If you don't have an account yet, you'll be able to create one after accepting the invitation.</p>

            <p style="font-size: 14px; color: #6b7280;">If you did not expect to receive this invitation, you can safely ignore this email.</p>
        </div>

        <div class="footer">
            <p>&copy; {{ date('Y') }} Arxitest. All rights reserved.</p>
            <p>This email was sent to you because someone invited you to join their team on Arxitest.</p>
        </div>
    </div>
</body>
</html>
