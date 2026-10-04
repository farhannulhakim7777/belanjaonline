<?php
session_start();

// Inisialisasi variabel
$nama_pembeli = '';
$produk = '';
$kategori = '';
$jumlah = '';
$hasil = null;
$error = '';

// Inisialisasi array riwayat transaksi di session
if (!isset($_SESSION['riwayat_transaksi'])) {
    $_SESSION['riwayat_transaksi'] = [];
}

// Cek tombol reset
if (isset($_POST['reset'])) {
    unset($_SESSION['riwayat_transaksi']);
    $_SESSION['riwayat_transaksi'] = [];
    header('Location: index.php');
    exit();
}

// Array produk dengan kategori dan harga
$daftar_produk = [
    'Mouse' => ['kategori' => 'Elektronik', 'harga' => 75000],
    'Keyboard' => ['kategori' => 'Elektronik', 'harga' => 150000],
    'Monitor' => ['kategori' => 'Elektronik', 'harga' => 1200000],
    'Laptop' => ['kategori' => 'Elektronik', 'harga' => 8500000],
    'Kopi' => ['kategori' => 'Minuman', 'harga' => 20000],
    'Teh' => ['kategori' => 'Minuman', 'harga' => 15000],
    'Jus Jeruk' => ['kategori' => 'Minuman', 'harga' => 25000],
    'Soda' => ['kategori' => 'Minuman', 'harga' => 10000],
    'Roti' => ['kategori' => 'Makanan', 'harga' => 15000],
    'Nasi Goreng' => ['kategori' => 'Makanan', 'harga' => 35000],
    'Mie Goreng' => ['kategori' => 'Makanan', 'harga' => 25000],
    'Snack' => ['kategori' => 'Makanan', 'harga' => 10000],
    'Kaos' => ['kategori' => 'Fashion', 'harga' => 80000],
    'Kemeja' => ['kategori' => 'Fashion', 'harga' => 150000],
    'Celana' => ['kategori' => 'Fashion', 'harga' => 200000],
    'Jaket' => ['kategori' => 'Fashion', 'harga' => 350000],
    'Sendok' => ['kategori' => 'Peralatan Rumah', 'harga' => 15000],
    'Garpu' => ['kategori' => 'Peralatan Rumah', 'harga' => 15000],
    'Piring' => ['kategori' => 'Peralatan Rumah', 'harga' => 25000],
    'Gelas' => ['kategori' => 'Peralatan Rumah', 'harga' => 20000]
];

// Cek apakah array produk valid menggunakan is_array()
if (is_array($daftar_produk)) {
    // Ambil daftar nama produk menggunakan array_keys
    $list_produk = array_keys($daftar_produk);
}

// Daftar kategori yang tersedia
$daftar_kategori = ['Elektronik', 'Makanan', 'Minuman', 'Fashion', 'Peralatan Rumah'];

// Menggunakan join() untuk menggabungkan kategori menjadi teks
$teks_kategori = join(', ', $daftar_kategori);

// Proses form jika method POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ambil data dari form dengan pengecekan isset()
    $nama_pembeli = isset($_POST['nama']) ? trim($_POST['nama']) : '';
    $produk = isset($_POST['produk']) ? $_POST['produk'] : '';
    $kategori = isset($_POST['kategori']) ? $_POST['kategori'] : '';
    $jumlah = isset($_POST['jumlah']) ? $_POST['jumlah'] : '';

    // Validasi input
    if (empty($nama_pembeli)) {
        $error = 'Nama pembeli harus diisi';
    } elseif (empty($produk)) {
        $error = 'Produk harus dipilih';
    } elseif (empty($kategori)) {
        $error = 'Kategori harus dipilih';
    } elseif (empty($jumlah) || !is_numeric($jumlah)) {
        $error = 'Jumlah pembelian harus diisi dengan angka';
    } elseif ($jumlah <= 0) {
        $error = 'Jumlah pembelian tidak boleh 0 atau negatif';
    } else {
        // Cek apakah produk tersedia menggunakan in_array()
        if (!in_array($produk, $list_produk)) {
            $error = 'Produk tidak tersedia';
        } else {
            // Menggunakan list() untuk mengambil data produk dari array
            list($kategori_produk, $harga_produk) = [
                $daftar_produk[$produk]['kategori'],
                $daftar_produk[$produk]['harga']
            ];

            // Cek apakah kategori sesuai
            if ($kategori != $kategori_produk) {
                $error = 'Kategori produk tidak sesuai';
            } else {
                // Hitung total belanja
                $total_belanja = $harga_produk * $jumlah;

                // Logika diskon berdasarkan total belanja
                if ($total_belanja < 100000) {
                    $diskon = 0;
                    $persentase_diskon = 0;
                } elseif ($total_belanja < 300000) {
                    $diskon = $total_belanja * 0.05;
                    $persentase_diskon = 5;
                } else {
                    $diskon = $total_belanja * 0.10;
                    $persentase_diskon = 10;
                }

                // Hitung total bayar setelah diskon
                $total_bayar = $total_belanja - $diskon;

                // Logika status pembelian berdasarkan jumlah barang
                if ($jumlah >= 1 && $jumlah <= 2) {
                    $status_pembelian = 'Pembelian Normal';
                } elseif ($jumlah >= 3 && $jumlah <= 5) {
                    $status_pembelian = 'Pembelian Sedang';
                } else {
                    $status_pembelian = 'Pembelian Banyak';
                }

                // Menggunakan implode() untuk menggabungkan ringkasan barang
                $ringkasan_barang = implode(' | ', [
                    $produk,
                    $kategori,
                    number_format($jumlah, 0, ',', '.')
                ]);

                // Simpan hasil
                $hasil = [
                    'nama' => $nama_pembeli,
                    'produk' => $produk,
                    'kategori' => $kategori,
                    'harga_satuan' => $harga_produk,
                    'jumlah' => $jumlah,
                    'total_belanja' => $total_belanja,
                    'diskon' => $diskon,
                    'persentase_diskon' => $persentase_diskon,
                    'total_bayar' => $total_bayar,
                    'status' => $status_pembelian,
                    'ringkasan' => $ringkasan_barang
                ];

                // Simpan ke riwayat transaksi di session
                $_SESSION['riwayat_transaksi'][] = $hasil;

                // Batasi riwayat hanya 5 transaksi terakhir
                if (count($_SESSION['riwayat_transaksi']) > 5) {
                    array_shift($_SESSION['riwayat_transaksi']);
                }

                // Reset form setelah berhasil
                $nama_pembeli = '';
                $produk = '';
                $kategori = '';
                $jumlah = '';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Belanja Online</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <div class="card">
            <h1>Sistem Belanja Online</h1>
            <p class="subtitle">Silakan isi formulir untuk melakukan pembelian</p>

            <?php if ($error): ?>
                <div class="error">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="nama">Nama Pembeli</label>
                    <input type="text" id="nama" name="nama"
                           placeholder="Masukkan nama pembeli" required>
                </div>

                <div class="form-group">
                    <label for="produk">Produk</label>
                    <select id="produk" name="produk" required>
                        <option value="">-- Pilih Produk --</option>
                        <?php foreach ($list_produk as $p): ?>
                            <option value="<?php echo htmlspecialchars($p); ?>">
                                <?php echo htmlspecialchars($p); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="kategori">Kategori Produk</label>
                    <select id="kategori" name="kategori" required>
                        <option value="">-- Pilih Kategori --</option>
                        <?php foreach ($daftar_kategori as $k): ?>
                            <option value="<?php echo htmlspecialchars($k); ?>">
                                <?php echo htmlspecialchars($k); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="jumlah">Jumlah Pembelian</label>
                    <input type="number" id="jumlah" name="jumlah"
                           placeholder="Masukkan jumlah barang" min="1" required>
                </div>

                <button type="submit" class="btn">Proses Belanja</button>
            </form>

            <div class="info">
                <p><strong>Kategori tersedia:</strong> <?php echo htmlspecialchars($teks_kategori); ?></p>
            </div>
        </div>

        <?php if ($hasil): ?>
            <div class="card result">
                <h2>Hasil Pembelian</h2>
                <table class="result-table">
                    <tr>
                        <td>Nama Pembeli</td>
                        <td><?php echo htmlspecialchars($hasil['nama']); ?></td>
                    </tr>
                    <tr>
                        <td>Produk</td>
                        <td><?php echo htmlspecialchars($hasil['produk']); ?></td>
                    </tr>
                    <tr>
                        <td>Kategori</td>
                        <td><?php echo htmlspecialchars($hasil['kategori']); ?></td>
                    </tr>
                    <tr>
                        <td>Harga Satuan</td>
                        <td>Rp <?php echo number_format($hasil['harga_satuan'], 0, ',', '.'); ?></td>
                    </tr>
                    <tr>
                        <td>Jumlah Barang</td>
                        <td><?php echo number_format($hasil['jumlah'], 0, ',', '.'); ?></td>
                    </tr>
                    <tr>
                        <td>Total Belanja</td>
                        <td>Rp <?php echo number_format($hasil['total_belanja'], 0, ',', '.'); ?></td>
                    </tr>
                    <tr>
                        <td>Diskon</td>
                        <td><?php echo $hasil['persentase_diskon']; ?>% (Rp <?php echo number_format($hasil['diskon'], 0, ',', '.'); ?>)</td>
                    </tr>
                    <tr class="total">
                        <td>Total Bayar</td>
                        <td>Rp <?php echo number_format($hasil['total_bayar'], 0, ',', '.'); ?></td>
                    </tr>
                    <tr>
                        <td>Status Pembelian</td>
                        <td><?php echo htmlspecialchars($hasil['status']); ?></td>
                    </tr>
                </table>

                <div class="summary">
                    <p><strong>Ringkasan:</strong> <?php echo htmlspecialchars($hasil['ringkasan']); ?></p>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!empty($_SESSION['riwayat_transaksi'])): ?>
            <div class="card history">
                <h2>Riwayat Transaksi (5 Terakhir)</h2>
                <table class="history-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama</th>
                            <th>Produk</th>
                            <th>Jumlah</th>
                            <th>Total Bayar</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = 1;
                        foreach ($_SESSION['riwayat_transaksi'] as $transaksi):
                        ?>
                            <tr>
                                <td><?php echo $no++; ?></td>
                                <td><?php echo htmlspecialchars($transaksi['nama']); ?></td>
                                <td><?php echo htmlspecialchars($transaksi['produk']); ?></td>
                                <td><?php echo number_format($transaksi['jumlah'], 0, ',', '.'); ?></td>
                                <td>Rp <?php echo number_format($transaksi['total_bayar'], 0, ',', '.'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <form method="POST" action="" style="margin-top: 20px;">
                    <button type="submit" name="reset" class="btn-reset">Hapus Riwayat & Reset</button>
                </form>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
