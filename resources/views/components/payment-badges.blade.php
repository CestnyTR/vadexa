{{--
    iyzico üye iş yeri başvurusu "iyzico ile Öde", Visa ve Mastercard logolarının
    sitede yer almasını ister. Aşağıdakiler yer tutucu (placeholder) rozetlerdir;
    canlıya almadan önce iyzico logo paketindeki resmî görsellerle değiştirin:
    https://www.iyzico.com (Logo paketi)
--}}
<div class="flex items-center gap-2" aria-label="Ödeme yöntemleri">
    <!-- iyzico ile Öde -->
    <span class="inline-flex items-center h-7 px-2 rounded bg-[#1e64ff] text-white text-xs font-semibold tracking-tight">
        iyzico <span class="font-normal ml-1 opacity-90">ile Öde</span>
    </span>
    <!-- Visa -->
    <span class="inline-flex items-center h-7 px-2 rounded bg-white border border-gray-200 text-[#1a1f71] text-sm font-bold italic">
        VISA
    </span>
    <!-- Mastercard -->
    <span class="inline-flex items-center h-7 px-2 rounded bg-white border border-gray-200 gap-1">
        <span class="w-3.5 h-3.5 rounded-full bg-[#eb001b] inline-block"></span>
        <span class="w-3.5 h-3.5 rounded-full bg-[#f79e1b] inline-block -ml-2 opacity-90"></span>
        <span class="text-[10px] font-semibold text-gray-700 ml-0.5">mastercard</span>
    </span>
</div>
