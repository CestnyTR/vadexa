<!DOCTYPE html>
<html lang="tr">
<head><meta charset="utf-8"></head>
<body style="font-family: Arial, sans-serif; background:#f3f4f6; padding:24px; color:#111827;">
    <div style="max-width:480px; margin:0 auto; background:#ffffff; border-radius:12px; padding:32px;">
        <h1 style="font-size:20px; margin:0 0 16px;">Ekip Daveti</h1>
        <p style="font-size:14px; line-height:1.6; color:#374151;">
            <strong>{{ $tenantName }}</strong> sizi Vadexa hesabına <strong>{{ $role }}</strong> olarak davet etti.
            Hesabınızı oluşturmak için aşağıdaki bağlantıya tıklayın:
        </p>
        <p style="text-align:center; margin:28px 0;">
            <a href="{{ $acceptUrl }}"
               style="background:#4f46e5; color:#ffffff; text-decoration:none; padding:12px 24px; border-radius:8px; font-size:14px; display:inline-block;">
                Daveti Kabul Et
            </a>
        </p>
        <p style="font-size:12px; color:#9ca3af; line-height:1.6;">
            Bağlantı çalışmıyorsa şu adresi tarayıcınıza yapıştırın:<br>
            <span style="word-break:break-all;">{{ $acceptUrl }}</span>
        </p>
    </div>
</body>
</html>
