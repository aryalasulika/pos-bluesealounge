<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <style>
        .header { font-size: 14pt; font-weight: bold; }
        .sub { font-size: 10pt; color: #555; }
        th { background-color: #0f172a; color: #ffffff; font-weight: bold; border: 1px solid #000; text-align: left; }
        td { border: 1px solid #ccc; }
        .num { mso-number-format:"\#\,\#\#0"; text-align: right; }
    </style>
</head>
<body>
    <table>
        <tr><td colspan="5" class="header">BLUESEA LOUNGE — LAPORAN PRODUK TERLARIS</td></tr>
        <tr><td colspan="5" class="sub">Periode: {{ $periodLabel }}</td></tr>
        <tr><td colspan="5" class="sub">Dicetak pada: {{ now()->translatedFormat('d F Y H:i') }} WITA</td></tr>
        <tr><td colspan="5"></td></tr>

        <thead>
            <tr>
                <th>Peringkat</th>
                <th>Nama Produk Menu</th>
                <th>Kode SKU</th>
                <th>Unit Terjual</th>
                <th>Total Penjualan (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($topProducts as $idx => $item)
            <tr>
                <td style="text-align: center;">{{ $idx + 1 }}</td>
                <td>{{ $item->product_name_snapshot }}</td>
                <td>{{ $item->sku_snapshot ?? '-' }}</td>
                <td style="text-align: center;">{{ $item->total_quantity }}</td>
                <td class="num">{{ $item->total_revenue }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
