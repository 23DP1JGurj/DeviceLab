<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="utf-8">
    <title>Jauns ziņojums</title>
</head>
<body style="font-family: Arial, sans-serif; color: #0f172a; line-height: 1.5;">
    <h1 style="font-size: 22px; margin-bottom: 8px;">DeviceLab</h1>
    <p style="margin-top: 0;">Saņemts jauns ziņojums no kontaktformas.</p>

    <table cellpadding="6" cellspacing="0" style="border-collapse: collapse;">
        <tr>
            <td style="font-weight: bold;">Vārds</td>
            <td>{{ $contactMessage->name }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold;">E-pasts</td>
            <td>{{ $contactMessage->email ?: 'nav norādīts' }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold;">Tālrunis</td>
            <td>{{ $contactMessage->phone ?: 'nav norādīts' }}</td>
        </tr>
    </table>

    <h2 style="font-size: 16px; margin-top: 22px;">Ziņa</h2>
    <p style="white-space: pre-line;">{{ $contactMessage->message }}</p>
</body>
</html>
