<!DOCTYPE html>
<html>

<head>
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }

        .header,
        .footer {
            text-align: center;
        }

        .content {
            margin-top: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        .text-center {
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="header">
        <h2>{{ $title }}</h2>
        <p>
            Tanggal Cetak Data :
            <strong>
                {{ $date }}
            </strong>
        </p>
    </div>
    <div class="content">
        <table>
            <thead>
                <tr>
                    <th class="text-center">Invoice ID</th>
                    <th class="text-center">Tanggal Order</th>
                    <th>Nama User</th>
                    <th>Username Kasir</th>
                    <th class="text-center">Total Harga</th>
                    <th class="text-center">Status Order</th>
                    <th class="text-center">Tipe Transaksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($transaksi as $data)
                    <tr>
                        <td class="text-center">{{ $data->invoiceId }}</td>
                        <td class="text-center">{{ \Carbon\Carbon::parse($data->tanggalOrder)->translatedFormat('d F Y') }}</td>
                        <td>{{ $data->namaUser }}</td>
                        <td>{{ $data->usernameKasir }}</td>
                        <td class="text-center">{{ 'Rp ' . number_format($data->totalHarga, 0, ',', '.') }}</td>
                        <td class="text-center">{{ $data->statusOrder == 'PAID' ? 'Sudah Bayar' : 'Belum Bayar' }}</td>
                        <td class="text-center">{{ $data->tipeTransaksi == 'CASH' ? 'Cash' : ($data->tipeTransaksi == 'TRANSFER' ? 'Online Payment' : 'Belum Bayar') }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</body>

</html>
