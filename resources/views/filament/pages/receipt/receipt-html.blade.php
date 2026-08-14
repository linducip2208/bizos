<div class="receipt-doc {{ $fontClass }}" style="font-family: ui-monospace, 'JetBrains Mono', Menlo, Consolas, monospace; color: #111827; max-width: 320px; margin: 0 auto;">
    @if ($logoUrl)
        <div style="text-align:center; margin-bottom:6px;">
            <img src="{{ $logoUrl }}" alt="logo" style="max-height:48px; display:inline-block;">
        </div>
    @endif

    @if ($company)
        <div style="text-align:center; font-weight:800; font-size:1.1em;">{{ $company->name }}</div>
    @endif
    @if ($branch?->name)
        <div style="text-align:center;">{{ $branch->name }}</div>
    @endif
    @if ($company?->address)
        <div style="text-align:center; font-size:.9em;">{{ $company->address }}</div>
    @endif
    @if ($company?->phone)
        <div style="text-align:center; font-size:.9em;">Telp: {{ $company->phone }}</div>
    @endif

    @if ($headerText)
        <div style="text-align:center; margin-top:6px;">{!! $headerText !!}</div>
    @endif

    <div style="border-top:1px dashed #9ca3af; margin:6px 0;"></div>

    <div style="display:flex; justify-content:space-between;"><span>No</span><span>{{ $transaction->receipt_number }}</span></div>
    <div style="display:flex; justify-content:space-between;"><span>Tgl</span><span>{{ $transaction->transaction_date?->format('d-m-Y H:i') }}</span></div>
    <div style="display:flex; justify-content:space-between;"><span>Kasir</span><span>{{ $transaction->cashier?->first_name ? trim($transaction->cashier->first_name . ' ' . ($transaction->cashier->last_name ?? '')) : '-' }}</span></div>
    @if ($transaction->member)
        <div style="display:flex; justify-content:space-between;"><span>Member</span><span>{{ $transaction->member->name }}</span></div>
    @endif

    <div style="border-top:1px dashed #9ca3af; margin:6px 0;"></div>

    @foreach ($items as $item)
        <div>{{ $item['name'] }}</div>
        <div style="display:flex; justify-content:space-between; padding-left:8px;">
            <span>{{ $item['quantity'] }} x {{ number_format($item['unit_price'], 0, ',', '.') }}</span>
            <span>{{ number_format($item['line_total'], 0, ',', '.') }}</span>
        </div>
    @endforeach

    <div style="border-top:1px dashed #9ca3af; margin:6px 0;"></div>

    <div style="display:flex; justify-content:space-between;"><span>Subtotal</span><span>{{ number_format($transaction->subtotal, 0, ',', '.') }}</span></div>
    @if ((float) $transaction->discount_total > 0)
        <div style="display:flex; justify-content:space-between;"><span>Diskon</span><span>-{{ number_format($transaction->discount_total, 0, ',', '.') }}</span></div>
    @endif
    @if ($layout?->show_tax_summary && (float) $transaction->tax_total > 0)
        <div style="display:flex; justify-content:space-between;"><span>Pajak</span><span>{{ number_format($transaction->tax_total, 0, ',', '.') }}</span></div>
    @endif
    <div style="display:flex; justify-content:space-between; font-weight:800; font-size:1.15em;"><span>TOTAL</span><span>{{ number_format($transaction->grand_total, 0, ',', '.') }}</span></div>

    @if ($layout?->show_payment_summary && !empty($payments))
        <div style="border-top:1px dashed #9ca3af; margin:6px 0;"></div>
        @foreach ($payments as $payment)
            <div style="display:flex; justify-content:space-between;"><span>{{ $payment['method'] }}</span><span>{{ number_format($payment['amount'], 0, ',', '.') }}</span></div>
        @endforeach
        @php $change = (float) $transaction->payments->sum('amount') - (float) $transaction->grand_total; @endphp
        @if ($change > 0)
            <div style="display:flex; justify-content:space-between;"><span>Kembalian</span><span>{{ number_format($change, 0, ',', '.') }}</span></div>
        @endif
    @endif

    @if ($qr)
        <div style="text-align:center; margin:8px 0;">
            <img src="{!! $qr !!}" alt="QR" style="width:120px; height:120px; display:inline-block;">
        </div>
    @endif

    @if ($footerText)
        <div style="border-top:1px dashed #9ca3af; margin:6px 0;"></div>
        <div style="text-align:center;">{!! $footerText !!}</div>
    @endif

    <div style="text-align:center; margin-top:8px; color:#6b7280;">Terima kasih 🙏</div>
</div>
