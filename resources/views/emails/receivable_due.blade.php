<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #4f46e5; color: white; padding: 20px; border-radius: 8px 8px 0 0; text-align: center; }
        .content { background: #f9fafb; padding: 24px; border: 1px solid #e5e7eb; border-top: none; border-radius: 0 0 8px 8px; }
        .amount { font-size: 24px; font-weight: bold; color: #4f46e5; }
        .footer { margin-top: 20px; padding-top: 16px; border-top: 1px solid #e5e7eb; font-size: 12px; color: #9ca3af; }
    </style>
</head>
<body>
    <div class="header">
        <h2 style="margin:0;">Ödeme Bildirimi</h2>
    </div>
    <div class="content">
        <p>Sayin <strong>{{ $customerName }}</strong>,</p>
        <p>{{ $body }}</p>
        <p>Tutar: <span class="amount">{{ $amount }} TL</span></p>
        <p>Vade Tarihi: <strong>{{ $dueDate }}</strong></p>
        <div class="footer">
            <p>Bu otomatik bir bilgilendirme mesajidir.</p>
        </div>
    </div>
</body>
</html>
