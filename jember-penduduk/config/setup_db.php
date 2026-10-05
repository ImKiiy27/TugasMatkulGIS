<?php
require 'koneksi.php';

$sql_alter = "ALTER TABLE kecamatan ADD COLUMN IF NOT EXISTS luas_wilayah DECIMAL(10,2) DEFAULT 0";
$conn->query($sql_alter);

$data_luas = [
    'Kencong' => 65.4,
    'Gumuk Mas' => 90.53, // Adjusting name to match database
    'Puger' => 158.97,
    'Wuluhan' => 139.26,
    'Ambulu' => 101.41,
    'Tempurejo' => 515.99,
    'Silo' => 344.42,
    'Mayang' => 57.28,
    'Mumbulsari' => 90,
    'Jenggawah' => 61.24,
    'Ajung' => 58.84,
    'Rambipuji' => 55,
    'Balung' => 49.39,
    'Umbulsari' => 71.46,
    'Semboro' => 45.2,
    'Jombang' => 53.89,
    'Sumberbaru' => 161.04,
    'Tanggul' => 202.62,
    'Bangsalsari' => 161.75,
    'Panti' => 181,
    'Sukorambi' => 46.92,
    'Arjasa' => 35.88,
    'Pakusari' => 31.26,
    'Kalisat' => 52.67,
    'Ledokombo' => 133.84,
    'Sumberjambe' => 132.93,
    'Sukowono' => 45.13,
    'Jelbuk' => 73.38,
    'Kaliwates' => 24.74,
    'Sumbersari' => 35.98,
    'Patrang' => 36.71
];

foreach ($data_luas as $nama => $luas) {
    $stmt = $conn->prepare("UPDATE kecamatan SET luas_wilayah = ? WHERE nama = ?");
    $stmt->bind_param("ds", $luas, $nama);
    $stmt->execute();
}

echo "Database updated successfully.";
$conn->close();
?>
