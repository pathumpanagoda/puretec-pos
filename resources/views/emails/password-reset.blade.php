<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset - Pure POS</title>
</head>
<body style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f4f4; margin: 0; padding: 20px;">
    <div style="max-width: 500px; margin: 0 auto; background: #ffffff; border-radius: 15px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.1);">
        <!-- Header -->
        <div style="background: linear-gradient(135deg, #0d6e8a, #084c61); padding: 30px; text-align: center;">
            <h1 style="color: #ffffff; margin: 0; font-size: 24px;">Pure POS</h1>
            <p style="color: rgba(255,255,255,0.8); margin: 10px 0 0; font-size: 14px;">Password Reset Request</p>
        </div>

        <!-- Content -->
        <div style="padding: 30px;">
            <p style="color: #333; font-size: 16px; margin-bottom: 20px;">
                Hello <strong>{{ $user->name }}</strong>,
            </p>

            <p style="color: #555; font-size: 14px; line-height: 1.6; margin-bottom: 25px;">
                We received a request to reset your password for your Pure POS account. Click the button below to create a new password:
            </p>

            <!-- Button -->
            <div style="text-align: center; margin: 30px 0;">
                <a href="{{ $resetUrl }}"
                   style="display: inline-block; background: linear-gradient(135deg, #0d6e8a, #084c61); color: #ffffff; text-decoration: none; padding: 14px 40px; border-radius: 8px; font-weight: 600; font-size: 16px;">
                    Reset Password
                </a>
            </div>

            <p style="color: #888; font-size: 13px; line-height: 1.6;">
                This link will expire in <strong>60 minutes</strong>. If you didn't request a password reset, you can safely ignore this email.
            </p>

            <hr style="border: none; border-top: 1px solid #eee; margin: 25px 0;">

            <p style="color: #999; font-size: 12px; margin-bottom: 5px;">
                If the button doesn't work, copy and paste this link into your browser:
            </p>
            <p style="color: #0d6e8a; font-size: 12px; word-break: break-all;">
                {{ $resetUrl }}
            </p>
        </div>

        <!-- Footer -->
        <div style="background: #f8f9fa; padding: 20px; text-align: center; border-top: 1px solid #eee;">
            <p style="color: #888; font-size: 12px; margin: 0;">
                &copy; {{ date('Y') }} Pure POS by Nexfloit. All rights reserved.
            </p>
        </div>
    </div>
</body>
</html>
