<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="utf-8">
    <title>Pasūtījums gatavs saņemšanai</title>
</head>
<body style="font-family: Arial, sans-serif; color: #0f172a; line-height: 1.5;">
    <h1 style="font-size: 22px; margin-bottom: 8px;">DeviceLab</h1>
    <p>Jūsu pasūtījums ir gatavs saņemšanai.</p>

    <table cellpadding="6" cellspacing="0" style="border-collapse: collapse;">
        <tr>
            <td style="font-weight: bold;">Pasūtījums</td>
            <td>{{ $order->order_number }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold;">Ierīce</td>
            <td>{{ trim(($order->device?->brand ?? '') . ' ' . ($order->device?->model ?? '')) ?: 'nav norādīta' }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold;">Filiāle</td>
            <td>{{ $order->branch?->name ?: 'nav norādīta' }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold;">Summa</td>
            <td>{{ number_format((float) $order->final_cost, 2) }} EUR</td>
        </tr>
    </table>

    <p style="margin-top: 20px;">Jūsu pasūtījums ir gatavs saņemšanai. Lūdzu, veiciet apmaksu savā profilā.</p>
</body>
</html>
