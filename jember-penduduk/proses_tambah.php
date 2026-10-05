<?php
require 'config/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = $conn->real_escape_string($_POST['nama']);
    $jumlah = (int)$_POST['jumlah_penduduk'];
    $laju = (float)$_POST['laju_pertumbuhan'];
    $luas = (float)$_POST['luas_wilayah'];
    $lat = (float)$_POST['latitude'];
    $lng = (float)$_POST['longitude'];

    $sql = "INSERT INTO kecamatan (nama, jumlah_penduduk, laju_pertumbuhan, luas_wilayah, latitude, longitude) 
            VALUES ('$nama', $jumlah, $laju, $luas, $lat, $lng)";
    $conn->query($sql);
}

header("Location: data_manager.php");
exit;
