<?php
// Mengembalikan data kecamatan dalam format JSON untuk dipakai Leaflet & Chart.js
header('Content-Type: application/json; charset=utf-8');
require '../config/koneksi.php';

$sql = "SELECT id, nama, jumlah_penduduk, laju_pertumbuhan, latitude, longitude, luas_wilayah
        FROM kecamatan ORDER BY jumlah_penduduk DESC";
$result = $conn->query($sql);

$data = [];
while ($row = $result->fetch_assoc()) {
    $kepadatan = 0;
    if ($row['luas_wilayah'] > 0) {
        $kepadatan = round($row['jumlah_penduduk'] / $row['luas_wilayah'], 2);
    }
    
    $data[] = [
        'id'       => (int) $row['id'],
        'nama'     => $row['nama'],
        'jumlah'   => (int) $row['jumlah_penduduk'],
        'laju'     => (float) $row['laju_pertumbuhan'],
        'luas'     => (float) $row['luas_wilayah'],
        'kepadatan'=> $kepadatan,
        'lat'      => (float) $row['latitude'],
        'lng'      => (float) $row['longitude'],
    ];
}

echo json_encode($data);
$conn->close();
?>
