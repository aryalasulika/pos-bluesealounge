<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Omzet Penjualan — Bluesea Lounge</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 10px; color: #1e293b; line-height: 1.4; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #0284c7; padding-bottom: 12px; }
        .header h1 { font-size: 16px; margin: 0; color: #0f172a; text-transform: uppercase; letter-spacing: 1px; }
        .header p { margin: 2px 0 0; color: #64748b; font-size: 10px; }
        .meta-box { margin-bottom: 15px; }
        .summary-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        .summary-table td { padding: 8px; border: 1px solid #e2e8f0; }
        .summary-title { font-size: 9px; text-transform: uppercase; color: #64748b; font-weight: bold; }
        .summary-val { font-size: 13px; font-weight: bold; color: #0f172a; }
        .data-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .data-table th { background: #0f172a; color: #ffffff; padding: 6px 8px; font-size: 9px; text-transform: uppercase; text-align: left; }
        .data-table td { padding: 6px 8px; border-bottom: 1px solid #e2e8f0; font-size: 9px; }
        .data-table tr:nth-child(even) { background-color: #f8fafc; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .footer { margin-top: 30px; font-size: 8px; color: #94a3b8; text-align: right; border-top: 1px solid #e2e8f0; padding-top: 5px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Bluesea Lounge Airport POS</h1>
        <p>Laporan Resmi Omzet Penjualan Makanan & Minuman</p>
        <p><strong>Periode:</strong> {{ $periodLabel }} | Dicetak pada: {{ now()->translatedFormat('d F Y H:i') }} WITA</p>
    </div>

    <table class="summary-table">
        <tr>
            <td width="25%">
                <div class="summary-title">Total Omzet Valid</div>
                <div class="summary-val" style="color: #0284c7;">Rp {{ number_format($totalOmzet, 0, ',', '.') }}</div>
            </td>
            <td width="25%">
                <div class="summary-title">Jumlah Transaksi</div>
                <div class="summary-val">{{ $totalTransactions }} struk ({{ $totalItemsSold }} unit)</div>
            </td>
            <td width="25%">
                <div class="summary-title">Tunai / QRIS</div>
                <div class="summary-val" style="font-size: 11px;">
                    Tunai: Rp {{ number_format($cashOmzet, 0, ',', '.') }}<br>
                    QRIS: Rp {{ number_format($qrisOmzet, 0, ',', '.') }}
                </div>
            </td>
            <td width="25%">
                <div class="summary-title">Dibatalkan (Exclude)</div>
                <div class="summary-val" style="color: #e11d48;">{{ $cancelledCount }} (Rp {{ number_format($cancelledPotential, 0, ',', '.') }})</div>
            </td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="20%">No. Transaksi</th>
                <th width="15%">Waktu</th>
                <th width="15%">Kasir</th>
                <th width="10%">Metode</th>
                <th width="15%" class="text-center">Jml Item</th>
                <th width="20%" class="text-right">Total (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($sales as $idx => $s)
            <tr>
                <td class="text-center">{{ $idx + 1 }}</td>
                <td><strong>{{ $s->transaction_number }}</strong></td>
                <td>{{ $s->transaction_date->format('d/m/Y H:i') }}</td>
                <td>{{ $s->cashier->name }}</td>
                <td style="text-transform: uppercase;">{{ $s->payment_method }}</td>
                <td class="text-center">{{ $s->items->sum('quantity') }}</td>
                <td class="text-right font-bold">Rp {{ number_format($s->total, 0, ',', '.') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center">Tidak ada transaksi pada periode ini.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Dicetak otomatis oleh Sistem POS Bluesea Lounge • Seluruh data akurat dan konsisten dengan database.
    </div>
</body>
</html>
