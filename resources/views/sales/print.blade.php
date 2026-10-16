<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Thermal — {{ $sale->transaction_number }}</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 11px;
            line-height: 1.3;
            color: #000;
            background: #f1f5f9;
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .receipt-container {
            width: 80mm;
            max-width: 100%;
            background: #fff;
            padding: 4mm;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .divider {
            border-top: 1px dashed #000;
            margin: 6px 0;
        }
        .double-divider {
            border-top: 2px dashed #000;
            margin: 6px 0;
        }
        .flex-between {
            display: flex;
            justify-content: space-between;
        }
        .item-row {
            margin-bottom: 4px;
            page-break-inside: avoid;
            break-inside: avoid;
            overflow-wrap: anywhere;
        }
        .actions-bar {
            width: 80mm;
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
        }
        .print-hint {
            width: 80mm;
            max-width: 100%;
            text-align: center;
            font-size: 10px;
            color: #64748b;
            margin-bottom: 8px;
            line-height: 1.4;
        }
        .btn {
            padding: 6px 12px;
            font-size: 11px;
            font-weight: bold;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            text-decoration: none;
        }
        .btn-print { background: #0284c7; color: #fff; }
        .btn-close { background: #e2e8f0; color: #334155; }

        @media print {
            html, body {
                width: 80mm;
            }
            body {
                background: #fff;
                /* Inset kecil agar tepi struk tidak terpotong area tak-tercetak printer thermal */
                padding: 2mm 3mm;
                display: block;
                align-items: initial;
            }
            .actions-bar,
            .print-hint {
                display: none !important;
            }
            .receipt-container {
                width: 100%;
                max-width: 100%;
                border: none;
                box-shadow: none;
                padding: 0 0 4mm;
            }
            /* Lebar kertas thermal 80mm; tinggi mengikuti konten (kertas gulung) */
            @page {
                margin: 0;
                size: 80mm auto;
            }
        }
    </style>
</head>
<body>

    <div class="actions-bar">
        <a href="{{ route('sales.show', $sale) }}" class="btn btn-close">← Kembali</a>
        <button type="button" onclick="window.print()" class="btn btn-print">🖨️ Cetak Struk</button>
    </div>

    <p class="print-hint">
        Jika hasil cetak terpotong: pilih printer <strong>thermal 80mm</strong> dan matikan opsi
        <strong>&ldquo;Headers and footers&rdquo;</strong> pada dialog cetak browser.
    </p>

    <div class="receipt-container" id="thermal-receipt">
        <!-- Header -->
        <div class="text-center">
            <h1 class="font-bold" style="font-size: 14px; letter-spacing: 1px;">BLUESEA LOUNGE</h1>
            <p>Airport Terminal F&B</p>
            <p style="font-size: 9px;">Bandara International Area</p>
        </div>

        <div class="divider"></div>

        <!-- Meta -->
        <div>
            <div class="flex-between">
                <span>No: {{ $sale->transaction_number }}</span>
                <span>{{ $sale->transaction_date->format('d/m/y H:i') }} WITA</span>
            </div>
            <div class="flex-between">
                <span>Kasir: {{ $sale->cashier->name }}</span>
                <span>Bayar: {{ strtoupper($sale->payment_method) }}</span>
            </div>
            @if ($sale->isCancelled())
            <div class="text-center font-bold" style="color: #000; margin-top: 4px;">
                *** TRANSAKSI DIBATALKAN ***
            </div>
            @endif
        </div>

        <div class="divider"></div>

        <!-- Items -->
        <div>
            @foreach ($sale->items as $item)
            <div class="item-row">
                <div class="font-bold">{{ $item->product_name_snapshot }}</div>
                <div class="flex-between">
                    <span>{{ $item->quantity }} x {{ number_format($item->unit_price, 0, ',', '.') }}</span>
                    <span>{{ number_format($item->subtotal, 0, ',', '.') }}</span>
                </div>
            </div>
            @endforeach
        </div>

        <div class="double-divider"></div>

        <!-- Totals -->
        <div>
            <div class="flex-between">
                <span>Subtotal:</span>
                <span>Rp {{ number_format($sale->subtotal, 0, ',', '.') }}</span>
            </div>
            <div class="flex-between font-bold" style="font-size: 12px; margin-top: 2px;">
                <span>TOTAL:</span>
                <span>Rp {{ number_format($sale->total, 0, ',', '.') }}</span>
            </div>

            @if ($sale->payment_method === 'cash')
            <div class="flex-between" style="margin-top: 4px;">
                <span>Tunai:</span>
                <span>Rp {{ number_format($sale->payment_amount, 0, ',', '.') }}</span>
            </div>
            <div class="flex-between">
                <span>Kembali:</span>
                <span>Rp {{ number_format($sale->change_amount, 0, ',', '.') }}</span>
            </div>
            @else
            <div class="flex-between" style="margin-top: 4px;">
                <span>Status QRIS:</span>
                <span>LUNAS</span>
            </div>
            @endif
        </div>

        <div class="divider"></div>

        <!-- Footer -->
        <div class="text-center" style="font-size: 9px; margin-top: 6px;">
            <p>Terima Kasih Atas Kunjungan Anda</p>
            <p>Have a Safe Flight & Good Day!</p>
        </div>
    </div>

    <script>
        // Auto trigger print on page load
        window.addEventListener('load', function() {
            setTimeout(function() {
                window.print();
            }, 300);
        });
    </script>
</body>
</html>
