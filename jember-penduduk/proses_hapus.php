<?php
require 'config/koneksi.php';

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM kecamatan WHERE id = $id");
}

header("Location: data_manager.php");
exit;
