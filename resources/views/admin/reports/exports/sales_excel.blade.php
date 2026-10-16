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
        <tr><td colspan="7" class="header">BLUESEA LOUNGE — LAPORAN OMZET PENJUALAN</td></tr>
        <tr><td colspan="7" class="sub">Periode: {{ $periodLabel }}</td></tr>
        <tr><td colspan="7" class="sub">Dicetak pada: {{ now()->translatedFormat('d F Y H:i') }} WITA</td></tr>
        <tr><td colspan="7"></td></tr>

        <tr>
            <td colspan="2" style="font-weight: bold;">Total Omzet Valid:</td>
            <td class="num" style="font-weight: bold;">{{ $totalOmzet }}</td>
            <td colspan="2" style="font-weight: bold;">Transaksi Selesai:</td>
            <td colspan="2">{{ $totalTransactions }} transaksi ({{ $totalItemsSold }} unit)</td>
        </tr>
        <tr>
            <td colspan="2">Omzet Tunai:</td>
            <td class="num">{{ $cashOmzet }}</td>
            <td colspan="2">Transaksi Dibatalkan:</td>
            <td colspan="2">{{ $cancelledCount }} transaksi (Exclude)</td>
        </tr>
        <tr>
            <td colspan="2">Omzet QRIS:</td>
            <td class="num">{{ $qrisOmzet }}</td>
            <td colspan="4"></td>
        </tr>
        <tr><td colspan="7"></td></tr>

        <thead>
            <tr>
                <th>No</th>
                <th>No. Transaksi</th>
                <th>Waktu Transaksi</th>
                <th>Nama Kasir</th>
                <th>Metode Bayar</th>
                <th>Jumlah Item</th>
                <th>Total Tagihan (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($sales as $idx => $s)
            <tr>
                <td style="text-align: center;">{{ $idx + 1 }}</td>
                <td>{{ $s->transaction_number }}</td>
                <td>{{ $s->transaction_date->format('Y-m-d H:i') }}</td>
                <td>{{ $s->cashier->name }}</td>
                <td style="text-transform: uppercase;">{{ $s->payment_method }}</td>
                <td style="text-align: center;">{{ $s->items->sum('quantity') }}</td>
                <td class="num">{{ $s->total }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
