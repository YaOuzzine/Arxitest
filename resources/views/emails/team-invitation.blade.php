<!-- resources/views/emails/team-invitation.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Team Invitation</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: linear-gradient(to right, #4F46E5, #7C3AED);
            color: white;
            padding: 20px;
            border-radius: 8px 8px 0 0;
        }
        .content {
            background-color: #f9fafb;
            padding: 20px;
            border-radius: 0 0 8px 8px;
            border: 1px solid #e5e7eb;
            border-top: none;
        }
        .button {
            display: inline-block;
            background: linear-gradient(to right, #4F46E5, #7C3AED);
            color: white;
            text-decoration: none;
            padding: 12px 24px;
            border-radius: 6px;
            font-weight: 600;
            margin: 20px 0;
        }
        .footer {
            margin-top: 30px;
            font-size: 12px;
            color: #6b7280;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>You've been invited!</h1>
    </div>
    <div class="content">
        <p>Hello,</p>
        <p><strong>{{ $inviterName }}</strong> has invited you to join <strong>{{ $team->name }}</strong> on Arxitest as a <strong>{{ ucfirst($role) }}</strong>.</p>

        @if($isRegistered)
            <p>Since you already have an Arxitest account, you can accept this invitation directly.</p>
            <a href="{{ $registrationLink }}" class="button">Accept Invitation</a>
        @else
            <p>To join this team, you'll need to create an Arxitest account first. Arxitest is a collaborative testing platform that helps teams create, manage, and run automated tests.</p>
            <a href="{{ $registrationLink }}" class="button">Create Account & Join Team</a>
            <p>After creating your account, you'll automatically see this invitation and can accept or decline it.</p>
        @endif

        <p>This invitation will expire in 7 days.</p>
    </div>
    <div class="footer">
        <p>If you didn't expect this invitation, you can simply ignore this email.</p>
        <p>&copy; {{ date('Y') }} Arxitest. All rights reserved.</p>
    </div>
</body>
</html>
