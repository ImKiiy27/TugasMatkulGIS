<?php
require 'config/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)$_POST['id'];
    $nama = $conn->real_escape_string($_POST['nama']);
    $jumlah = (int)$_POST['jumlah_penduduk'];
    $laju = (float)$_POST['laju_pertumbuhan'];
    $luas = (float)$_POST['luas_wilayah'];
    $lat = (float)$_POST['latitude'];
    $lng = (float)$_POST['longitude'];

    if ($id > 0) {
        $sql = "UPDATE kecamatan SET 
                nama='$nama', jumlah_penduduk=$jumlah, laju_pertumbuhan=$laju, 
                luas_wilayah=$luas, latitude=$lat, longitude=$lng 
                WHERE id=$id";
        $conn->query($sql);
    }
}

header("Location: data_manager.php");
exit;
