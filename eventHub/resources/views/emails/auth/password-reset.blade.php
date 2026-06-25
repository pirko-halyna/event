<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset Code</title>
</head>
<body style="margin:0;padding:0;background-color:#f4f4f5;font-family:'Helvetica Neue',Arial,sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0" border="0">
        <tr>
            <td align="center" style="padding:40px 16px;">
                <table width="560" cellpadding="0" cellspacing="0" border="0"
                       style="max-width:560px;background:#ffffff;border-radius:8px;overflow:hidden;box-shadow:0 1px 4px rgba(0,0,0,0.08);">

                    <tr>
                        <td style="background:#18181b;padding:28px 40px;">
                            <p style="margin:0;color:#ffffff;font-size:18px;font-weight:600;letter-spacing:0.5px;">
                                {{ config('app.name') }}
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:40px 40px 24px;">
                            <h1 style="margin:0 0 12px;color:#18181b;font-size:22px;font-weight:700;">
                                Password Reset
                            </h1>
                            <p style="margin:0 0 28px;color:#52525b;font-size:15px;line-height:1.6;">
                                Use the verification code below to reset your password.
                                It expires in <strong>{{ $expiryMinutes }} minutes</strong>.
                            </p>

                            <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td align="center"
                                        style="background:#f4f4f5;border:2px dashed #d4d4d8;border-radius:8px;padding:28px 20px;">
                                        <p style="margin:0 0 6px;color:#71717a;font-size:11px;text-transform:uppercase;letter-spacing:1.5px;">
                                            Verification Code
                                        </p>
                                        <p style="margin:0;color:#18181b;font-size:42px;font-weight:700;letter-spacing:14px;font-family:monospace;">
                                            {{ $code }}
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin:28px 0 0;background:#fefce8;border-left:3px solid #eab308;padding:12px 16px;border-radius:4px;color:#713f12;font-size:13px;line-height:1.5;">
                                If you did not request a password reset, ignore this email. Your account remains secure.
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:20px 40px;border-top:1px solid #f4f4f5;">
                            <p style="margin:0;color:#a1a1aa;font-size:12px;text-align:center;">
                                &copy; {{ date('Y') }} {{ config('app.name') }} &mdash; Do not reply to this email.
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
