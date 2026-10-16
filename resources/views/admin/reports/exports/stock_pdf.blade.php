<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Stok Produk — Bluesea Lounge</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 10px; color: #1e293b; line-height: 1.4; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #0284c7; padding-bottom: 12px; }
        .header h1 { font-size: 16px; margin: 0; color: #0f172a; text-transform: uppercase; }
        .header p { margin: 2px 0 0; color: #64748b; font-size: 10px; }
        .summary-box { margin-bottom: 15px; padding: 10px; background: #f8fafc; border: 1px solid #e2e8f0; }
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
        <p>Laporan Persediaan Stok & Valuasi Produk Siap Jual</p>
        <p>Dicetak pada: {{ now()->translatedFormat('d F Y H:i') }} WITA</p>
    </div>

    <div class="summary-box">
        <strong>Total Fisik Persediaan:</strong> {{ number_format($totalStockUnits, 0, ',', '.') }} unit item | 
        <strong>Estimasi Valuasi Nilai Jual:</strong> Rp {{ number_format($totalStockValue, 0, ',', '.') }}
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="30%">Nama Produk Menu</th>
                <th width="15%">Kategori</th>
                <th width="15%">SKU</th>
                <th width="10%" class="text-center">Stok</th>
                <th width="10%" class="text-center">Min.</th>
                <th width="15%" class="text-right">Valuasi (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($products as $idx => $p)
            <tr>
                <td class="text-center">{{ $idx + 1 }}</td>
                <td><strong>{{ $p->name }}</strong></td>
                <td>{{ $p->category->name }}</td>
                <td>{{ $p->sku }}</td>
                <td class="text-center"><strong>{{ $p->current_stock }} {{ $p->unit }}</strong></td>
                <td class="text-center">{{ $p->minimum_stock }}</td>
                <td class="text-right">Rp {{ number_format($p->current_stock * $p->selling_price, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Dicetak otomatis oleh Sistem POS Bluesea Lounge
    </div>
</body>
</html>
