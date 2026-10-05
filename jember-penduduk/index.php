<?php
require 'config/koneksi.php';

// Ringkasan untuk kartu statistik
$ringkas = $conn->query("SELECT COUNT(*) AS jml_kec, SUM(jumlah_penduduk) AS total,
                                MAX(jumlah_penduduk) AS maks, MIN(jumlah_penduduk) AS min,
                                SUM(luas_wilayah) as total_luas
                         FROM kecamatan")->fetch_assoc();
$terbanyak = $conn->query("SELECT nama FROM kecamatan ORDER BY jumlah_penduduk DESC LIMIT 1")->fetch_assoc()['nama'];
$tercepat  = $conn->query("SELECT nama, laju_pertumbuhan FROM kecamatan ORDER BY laju_pertumbuhan DESC LIMIT 1")->fetch_assoc();
$turun     = $conn->query("SELECT COUNT(*) AS n FROM kecamatan WHERE laju_pertumbuhan < 0")->fetch_assoc()['n'];

// Data tabel
$tabel = $conn->query("SELECT * FROM kecamatan ORDER BY nama");
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Peta Penduduk Kabupaten Jember 2024</title>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<link rel="stylesheet" href="css/style.css">
</head>
<body>

<header>
    <div>
        <h1>Visualisasi Penduduk Kabupaten Jember 2024</h1>
        <p>Jumlah penduduk, kepadatan dan laju pertumbuhan per kecamatan — Sumber: BPS Kabupaten Jember</p>
    </div>
    <div>
        <a href="data_manager.php" class="btn-nav">Kelola Data</a>
        <a href="spk.php" class="btn-nav" style="background-color: #27ae60; color: white;">SPK Prioritas</a>
    </div>
</header>

<div class="container">

    <!-- Kartu ringkasan -->
    <div class="cards">
        <div class="card">
            <div class="label">Total Penduduk</div>
            <div class="value"><?= number_format($ringkas['total'], 0, ',', '.') ?></div>
            <div class="sub">Total Luas: <?= number_format($ringkas['total_luas'], 2, ',', '.') ?> km²</div>
        </div>
        <div class="card">
            <div class="label">Kepadatan Rata-rata</div>
            <div class="value"><?= $ringkas['total_luas'] > 0 ? number_format($ringkas['total'] / $ringkas['total_luas'], 0, ',', '.') : 0 ?></div>
            <div class="sub">jiwa / km²</div>
        </div>
        <div class="card">
            <div class="label">Kecamatan Terpadat (Jiwa)</div>
            <div class="value"><?= htmlspecialchars($terbanyak) ?></div>
            <div class="sub"><?= number_format($ringkas['maks'], 0, ',', '.') ?> jiwa</div>
        </div>
        <div class="card">
            <div class="label">Pertumbuhan Tercepat</div>
            <div class="value"><?= htmlspecialchars($tercepat['nama']) ?></div>
            <div class="sub"><?= number_format($tercepat['laju_pertumbuhan'], 2, ',', '.') ?>% per tahun</div>
        </div>
    </div>

    <!-- Peta -->
    <div class="panel">
        <h2>Peta Sebaran Penduduk per Kecamatan</h2>
        <div class="toolbar">
            <button id="btnJumlah" class="active" onclick="gantiMode('jumlah')">Jumlah Penduduk</button>
            <button id="btnLaju" onclick="gantiMode('laju')">Laju Pertumbuhan</button>
            <button id="btnKepadatan" onclick="gantiMode('kepadatan')">Kepadatan Penduduk</button>
        </div>
        <div id="map"></div>
    </div>

    <!-- Grafik -->
    <div class="grid2">
        <div class="panel">
            <h2>Jumlah Penduduk per Kecamatan (Jiwa)</h2>
            <canvas id="chartJumlah" height="420"></canvas>
        </div>
        <div class="panel">
            <h2>Kepadatan Penduduk (Jiwa/km²)</h2>
            <canvas id="chartKepadatan" height="420"></canvas>
        </div>
    </div>

    <!-- Tabel -->
    <div class="panel">
        <h2>Tabel Data</h2>
        <div style="overflow-x: auto;">
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Kecamatan</th>
                    <th style="text-align:right">Luas Wilayah (km²)</th>
                    <th style="text-align:right">Penduduk (jiwa)</th>
                    <th style="text-align:right">Kepadatan (jiwa/km²)</th>
                    <th style="text-align:right">Laju Pertumbuhan (%)</th>
                </tr>
            </thead>
            <tbody>
            <?php $no = 1; while ($r = $tabel->fetch_assoc()): 
                $kepadatan = $r['luas_wilayah'] > 0 ? $r['jumlah_penduduk'] / $r['luas_wilayah'] : 0;
            ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><?= htmlspecialchars($r['nama']) ?></td>
                    <td class="num"><?= number_format($r['luas_wilayah'], 2, ',', '.') ?></td>
                    <td class="num"><?= number_format($r['jumlah_penduduk'], 0, ',', '.') ?></td>
                    <td class="num"><?= number_format($kepadatan, 2, ',', '.') ?></td>
                    <td class="num <?= $r['laju_pertumbuhan'] < 0 ? 'neg' : 'pos' ?>">
                        <?= number_format($r['laju_pertumbuhan'], 2, ',', '.') ?>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
        </div>
    </div>
</div>

<footer>Data penduduk dan luas: BPS Kabupaten Jember. Batas kecamatan: batas-administrasi-indonesia (Alf-Anas), disederhanakan.</footer>

<script src="js/script.js"></script>
</body>
</html>
<?php $conn->close(); ?>
