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
        <tr><td colspan="7" class="header">BLUESEA LOUNGE — LAPORAN STOK & VALUASI PRODUK</td></tr>
        <tr><td colspan="7" class="sub">Dicetak pada: {{ now()->translatedFormat('d F Y H:i') }} WITA</td></tr>
        <tr><td colspan="7"></td></tr>
        <tr>
            <td colspan="2" style="font-weight: bold;">Total Fisik Persediaan:</td>
            <td>{{ $totalStockUnits }} item</td>
            <td colspan="2" style="font-weight: bold;">Estimasi Valuasi:</td>
            <td class="num" colspan="2" style="font-weight: bold;">{{ $totalStockValue }}</td>
        </tr>
        <tr><td colspan="7"></td></tr>

        <thead>
            <tr>
                <th>No</th>
                <th>Nama Produk Menu</th>
                <th>Kategori</th>
                <th>SKU</th>
                <th>Harga Jual</th>
                <th>Stok Saat Ini</th>
                <th>Nilai Persediaan (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($products as $idx => $p)
            <tr>
                <td style="text-align: center;">{{ $idx + 1 }}</td>
                <td>{{ $p->name }}</td>
                <td>{{ $p->category->name }}</td>
                <td>{{ $p->sku }}</td>
                <td class="num">{{ $p->selling_price }}</td>
                <td style="text-align: center;">{{ $p->current_stock }} {{ $p->unit }}</td>
                <td class="num">{{ $p->current_stock * $p->selling_price }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
