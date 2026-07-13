<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Şirket / Künye Bilgileri
    |--------------------------------------------------------------------------
    |
    | iyzico üye iş yeri başvurusu için sitede bulunması zorunlu olan yasal
    | bilgiler. Değerleri .env üzerinden doldurun; İletişim ve yasal sayfalar
    | bu bilgileri otomatik gösterir.
    |
    | type: 'tacir' (ticaret unvanı + MERSİS) veya 'esnaf' (ad soyad + VKN)
    |
    */

    'type' => env('COMPANY_TYPE', 'tacir'),

    'legal_name' => env('COMPANY_LEGAL_NAME', 'Vadexa Yazılım A.Ş.'),
    'brand' => env('COMPANY_BRAND', 'Vadexa'),
    'trademark' => env('COMPANY_TRADEMARK', 'Vadexa'),

    'mersis' => env('COMPANY_MERSIS', ''),
    'tax_office' => env('COMPANY_TAX_OFFICE', ''),
    'tax_number' => env('COMPANY_TAX_NUMBER', ''),

    'address' => env('COMPANY_ADDRESS', ''),
    'email' => env('COMPANY_EMAIL', 'info@vadexa.com'),
    'kep' => env('COMPANY_KEP', ''),
    'phone' => env('COMPANY_PHONE', ''),

    // Mensubu olunan meslek odası ve davranış kuralları
    'chamber' => env('COMPANY_CHAMBER', 'İstanbul Ticaret Odası'),
    'code_of_conduct_url' => env('COMPANY_CODE_OF_CONDUCT_URL', ''),

];
