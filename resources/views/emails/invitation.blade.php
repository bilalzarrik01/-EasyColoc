<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EasyColoc Invitation</title>
</head>
<body style="margin:0; padding:0; background:#f3f4f6; font-family:Arial, sans-serif;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="padding:24px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:600px; background:#ffffff; border-radius:12px; overflow:hidden;">
                    <tr>
                        <td style="padding:24px;">
                            <h1 style="margin:0 0 12px; font-size:22px; color:#111827;">You're invited to EasyColoc</h1>
                            <p style="margin:0 0 10px; color:#374151; line-height:1.5;">
                                {{ $inviterName }} invited you to join <strong>{{ $colocationName }}</strong>.
                            </p>
                            <p style="margin:0 0 20px; color:#374151; line-height:1.5;">
                                Click the button below to open your invitation. Inside the page, you can accept or refuse.
                            </p>

                            <p style="margin:0 0 20px;">
                                <a href="{{ $invitationUrl }}"
                                   style="display:inline-block; padding:12px 18px; background:#2563eb; color:#ffffff; text-decoration:none; border-radius:8px; font-weight:600;">
                                    Open Invitation
                                </a>
                            </p>

                            <p style="margin:0; color:#6b7280; font-size:13px; line-height:1.5;">
                                If the button does not work, use this URL:<br>
                                <span style="word-break:break-all;">{{ $invitationUrl }}</span>
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
