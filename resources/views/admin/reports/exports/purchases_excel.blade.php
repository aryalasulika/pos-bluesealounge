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
        <tr><td colspan="6" class="header">BLUESEA LOUNGE — LAPORAN PEMBELIAN BARANG</td></tr>
        <tr><td colspan="6" class="sub">Periode: {{ $periodLabel }}</td></tr>
        <tr><td colspan="6" class="sub">Dicetak pada: {{ now()->translatedFormat('d F Y H:i') }} WITA</td></tr>
        <tr><td colspan="6"></td></tr>
        <tr>
            <td colspan="2" style="font-weight: bold;">Total Biaya Pembelian:</td>
            <td class="num" colspan="4" style="font-weight: bold;">{{ $totalCost }}</td>
        </tr>
        <tr><td colspan="6"></td></tr>

        <thead>
            <tr>
                <th>No</th>
                <th>No. Pembelian</th>
                <th>Tanggal</th>
                <th>Pemasok / Supplier</th>
                <th>Rincian Barang</th>
                <th>Total Biaya (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($purchases as $idx => $p)
            <tr>
                <td style="text-align: center;">{{ $idx + 1 }}</td>
                <td>{{ $p->purchase_number }}</td>
                <td>{{ $p->purchase_date->format('Y-m-d') }}</td>
                <td>{{ $p->supplier_name ?? '-' }}</td>
                <td>
                    @foreach ($p->items as $it)
                        {{ $it->product_name_snapshot }} ({{ $it->quantity }} {{ $it->unit }}),
                    @endforeach
                </td>
                <td class="num">{{ $p->total_amount }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
