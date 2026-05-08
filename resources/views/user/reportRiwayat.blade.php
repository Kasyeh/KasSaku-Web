<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Laporan Keuangan</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #1f2937;
            margin: 0;
            background: #f5f7fb;
        }

        .page {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            padding: 20px;
        }

        .header {
            border-bottom: 2px solid #e5e7eb;
            padding-bottom: 10px;
            margin-bottom: 14px;
        }

        .title {
            font-size: 20px;
            font-weight: 700;
            color: #111827;
            margin: 0;
            text-align: center;
            letter-spacing: .2px;
        }

        .subtitle {
            margin-top: 6px;
            text-align: center;
            font-size: 11px;
            color: #6b7280;
        }

        .period-chip {
            display: inline-block;
            margin-top: 8px;
            padding: 4px 10px;
            border-radius: 999px;
            border: 1px solid #c7d2fe;
            background: #eef2ff;
            color: #3730a3;
            font-size: 10px;
            font-weight: 700;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
        }

        .data-table {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            overflow: hidden;
        }

        .data-table th,
        .data-table td {
            border: 1px solid #e5e7eb;
            padding: 8px 7px;
            text-align: left;
            vertical-align: middle;
        }

        .data-table th {
            background: #f3f4f6;
            color: #374151;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: .3px;
        }

        .data-table tbody tr:nth-child(even) {
            background: #fafafa;
        }

        .col-no {
            width: 32px;
            text-align: center;
        }

        .col-nominal {
            text-align: right;
            white-space: nowrap;
            font-weight: 700;
        }

        .summary {
            margin-top: 14px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            overflow: hidden;
        }

        .summary th,
        .summary td {
            border: 1px solid #e5e7eb;
            padding: 8px 10px;
        }

        .summary th {
            width: 45%;
            background: #f9fafb;
            color: #374151;
            text-align: left;
        }

        .summary td {
            text-align: right;
            font-weight: 700;
        }

        .total-in {
            color: #047857;
        }

        .total-out {
            color: #b91c1c;
        }

        .total-balance {
            color: #1d4ed8;
        }

        .footer-note {
            margin-top: 10px;
            text-align: right;
            font-size: 9px;
            color: #9ca3af;
        }
    </style>
</head>

<body>
    <div class="page">
        <div class="header">
            <h2 class="title">Laporan Keuangan</h2>
            <div class="subtitle">
                <span class="period-chip">{{ $tanggal }}</span>
            </div>
        </div>

        <table class="data-table">
            <thead>
                <tr>
                    <th class="col-no">No</th>
                    <th>Tanggal</th>
                    <th>Jenis</th>
                    <th>Catatan</th>
                    <th>Uang Masuk</th>
                    <th>Uang Keluar</th>
                    <th class="col-nominal">Nominal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($transaksi as $i => $t)
                    <tr>
                        <td class="col-no">{{ $i + 1 }}</td>
                        <td>{{ \Carbon\Carbon::parse($t->tanggal ?? now())->format('d-m-Y') }}</td>
                        <td>{{ ucfirst($t->kategori) }}</td>
                        <td>{{ $t->keterangan ?? '-' }}</td>
                        <td>{{ $t->tipe == 'pemasukan' ? 'Uang Masuk' : '-' }}</td>
                        <td>{{ $t->tipe == 'pengeluaran' ? 'Uang Keluar' : '-' }}</td>
                        <td class="col-nominal">Rp {{ number_format($t->nominal, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <table class="summary">
            <tr>
                <th>Total Uang Masuk</th>
                <td class="total-in">Rp {{ number_format($totalPemasukan, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <th>Total Uang Keluar</th>
                <td class="total-out">Rp {{ number_format($totalPengeluaran, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <th>Sisa Uang</th>
                <td class="total-balance">Rp {{ number_format($saldo, 0, ',', '.') }}</td>
            </tr>
        </table>

        <div class="footer-note">Dokumen dibuat otomatis oleh KasSaku</div>
    </div>
</body>

</html>