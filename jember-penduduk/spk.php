<?php
require 'config/koneksi.php';

// Ambil data kecamatan
$sql = "SELECT * FROM kecamatan";
$result = $conn->query($sql);

$data = [];
$max_kepadatan = 0;
$max_laju = 0;
$max_penduduk = 0;
$min_laju = 0; // Untuk normalisasi nilai negatif

// Pass 1: Kumpulkan data dan cari nilai ekstrem
while ($row = $result->fetch_assoc()) {
    $kepadatan = $row['luas_wilayah'] > 0 ? $row['jumlah_penduduk'] / $row['luas_wilayah'] : 0;
    
    $data[] = [
        'nama' => $row['nama'],
        'kepadatan' => $kepadatan,
        'laju' => $row['laju_pertumbuhan'],
        'penduduk' => $row['jumlah_penduduk']
    ];
    
    if ($kepadatan > $max_kepadatan) $max_kepadatan = $kepadatan;
    if ($row['jumlah_penduduk'] > $max_penduduk) $max_penduduk = $row['jumlah_penduduk'];
    if ($row['laju_pertumbuhan'] < $min_laju) $min_laju = $row['laju_pertumbuhan'];
}

// Sesuaikan nilai laju agar tidak ada yang negatif untuk pembagian (shifting)
$shift_laju = $min_laju < 0 ? abs($min_laju) : 0;
foreach ($data as $d) {
    $adjusted_laju = $d['laju'] + $shift_laju;
    if ($adjusted_laju > $max_laju) $max_laju = $adjusted_laju;
}

// Bobot Kriteria
$w_kepadatan = 0.5; // 50%
$w_laju = 0.3;      // 30%
$w_penduduk = 0.2;  // 20%

// Pass 2: Hitung Normalisasi & Preferensi (SAW)
foreach ($data as &$d) {
    // Normalisasi (Benefit semua)
    $n_kepadatan = $max_kepadatan > 0 ? $d['kepadatan'] / $max_kepadatan : 0;
    $adjusted_laju = $d['laju'] + $shift_laju;
    $n_laju = $max_laju > 0 ? $adjusted_laju / $max_laju : 0;
    $n_penduduk = $max_penduduk > 0 ? $d['penduduk'] / $max_penduduk : 0;
    
    // Nilai Akhir (Preferensi)
    $d['skor'] = ($n_kepadatan * $w_kepadatan) + ($n_laju * $w_laju) + ($n_penduduk * $w_penduduk);
}
unset($d);

// Sort berdasarkan skor tertinggi
usort($data, function($a, $b) {
    return $b['skor'] <=> $a['skor'];
});

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SPK Prioritas Pembangunan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/spk.css">
</head>
<body>
<header>
    <div>
        <h1>SPK Prioritas Pembangunan Infrastruktur (Metode SAW)</h1>
        <p>Sistem Pendukung Keputusan untuk menentukan prioritas wilayah pengembangan berdasarkan Kepadatan (50%), Laju Pertumbuhan (30%), & Jumlah Penduduk (20%)</p>
    </div>
    <div>
        <a href="index.php" class="btn-nav">Peta Visualisasi</a>
        <a href="data_manager.php" class="btn-nav" >Kelola Data</a>
    </div>
</header>

<div class="container mt-4">
    <div class="card mb-4 shadow-sm">
        <div class="card-body bg-light">
            <h5 class="card-title text-primary">Penjelasan Kriteria (Benefit)</h5>
            <ul>
                <li><strong>C1 - Kepadatan Penduduk (Bobot 50%):</strong> Semakin padat, semakin butuh penataan tata ruang & infrastruktur publik.</li>
                <li><strong>C2 - Laju Pertumbuhan (Bobot 30%):</strong> Wilayah yang tumbuh cepat membutuhkan antisipasi fasilitas lebih awal.</li>
                <li><strong>C3 - Jumlah Penduduk (Bobot 20%):</strong> Populasi tinggi merepresentasikan demand/kebutuhan yang besar.</li>
            </ul>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header text-white" style="background-color: #27ae60;">
            <h5 class="mb-0">Hasil Perankingan (Ranking)</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0 text-center">
                    <thead class="table-light">
                        <tr>
                            <th>Rank</th>
                            <th class="text-start">Kecamatan</th>
                            <th>Kepadatan (C1)</th>
                            <th>Laju (C2)</th>
                            <th>Penduduk (C3)</th>
                            <th>Skor Akhir (V)</th>
                            <th>Status Prioritas</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $rank = 1;
                        foreach($data as $d): 
                            $badge = 'bg-secondary';
                            $status = 'Rendah';
                            if ($rank <= 5) { $badge = 'bg-danger'; $status = 'Sangat Tinggi'; }
                            elseif ($rank <= 15) { $badge = 'bg-warning text-dark'; $status = 'Tinggi'; }
                        ?>
                        <tr>
                            <td><strong><?= $rank++ ?></strong></td>
                            <td class="text-start"><?= htmlspecialchars($d['nama']) ?></td>
                            <td><?= number_format($d['kepadatan'], 2, ',', '.') ?></td>
                            <td><?= number_format($d['laju'], 2, ',', '.') ?>%</td>
                            <td><?= number_format($d['penduduk'], 0, ',', '.') ?></td>
                            <td><strong><?= number_format($d['skor'], 4, ',', '.') ?></strong></td>
                            <td><span class="badge <?= $badge ?>"><?= $status ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</body>
</html>
