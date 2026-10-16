<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Produk Terlaris — Bluesea Lounge</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 10px; color: #1e293b; line-height: 1.4; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #0284c7; padding-bottom: 12px; }
        .header h1 { font-size: 16px; margin: 0; color: #0f172a; text-transform: uppercase; }
        .header p { margin: 2px 0 0; color: #64748b; font-size: 10px; }
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
        <p>Laporan Peringkat Produk Terlaris (Menu Terfavorit)</p>
        <p><strong>Periode:</strong> {{ $periodLabel }} | Dicetak pada: {{ now()->translatedFormat('d F Y H:i') }} WITA</p>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th width="10%" class="text-center">Peringkat</th>
                <th width="45%">Nama Produk Menu</th>
                <th width="15%">Kode SKU</th>
                <th width="15%" class="text-center">Unit Terjual</th>
                <th width="15%" class="text-right">Total Penjualan (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($topProducts as $idx => $item)
            <tr>
                <td class="text-center"><strong>#{{ $idx + 1 }}</strong></td>
                <td><strong>{{ $item->product_name_snapshot }}</strong></td>
                <td>{{ $item->sku_snapshot ?? '-' }}</td>
                <td class="text-center"><strong>{{ $item->total_quantity }} unit</strong></td>
                <td class="text-right">Rp {{ number_format($item->total_revenue, 0, ',', '.') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="text-center">Tidak ada data penjualan pada periode ini.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Dicetak otomatis oleh Sistem POS Bluesea Lounge
    </div>
</body>
</html>
