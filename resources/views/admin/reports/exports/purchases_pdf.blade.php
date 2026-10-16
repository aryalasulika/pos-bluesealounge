<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Pembelian Barang — Bluesea Lounge</title>
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
        <p>Laporan Riwayat Pembelian Barang Operasional & Supplier</p>
        <p><strong>Periode:</strong> {{ $periodLabel }} | Dicetak pada: {{ now()->translatedFormat('d F Y H:i') }} WITA</p>
    </div>

    <div class="summary-box">
        <strong>Total Biaya Pembelian Barang:</strong> Rp {{ number_format($totalCost, 0, ',', '.') }} |
        <strong>Jumlah Faktur Pengadaan:</strong> {{ $purchases->count() }} faktur
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="20%">No. Pembelian</th>
                <th width="15%">Tanggal</th>
                <th width="20%">Pemasok / Supplier</th>
                <th width="25%">Rincian Barang</th>
                <th width="15%" class="text-right">Total Biaya (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($purchases as $idx => $p)
            <tr>
                <td class="text-center">{{ $idx + 1 }}</td>
                <td><strong>{{ $p->purchase_number }}</strong></td>
                <td>{{ $p->purchase_date->format('d/m/Y') }}</td>
                <td>{{ $p->supplier_name ?? '-' }}</td>
                <td>
                    @foreach ($p->items as $it)
                        {{ $it->product_name_snapshot }} ({{ $it->quantity }} {{ $it->unit }}),
                    @endforeach
                </td>
                <td class="text-right font-bold">Rp {{ number_format($p->total_amount, 0, ',', '.') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center">Tidak ada data pembelian pada periode ini.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Dicetak otomatis oleh Sistem POS Bluesea Lounge
    </div>
</body>
</html>
