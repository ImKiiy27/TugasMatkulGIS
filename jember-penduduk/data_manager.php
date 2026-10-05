<?php
require 'config/koneksi.php';
// Get Data for Edit
$edit_data = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $res = $conn->query("SELECT * FROM kecamatan WHERE id = $id");
    $edit_data = $res->fetch_assoc();
}

// Get All Data
// Implementing Search and Sort
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$sort = isset($_GET['sort']) ? $conn->real_escape_string($_GET['sort']) : 'nama';
$order = isset($_GET['order']) && $_GET['order'] === 'desc' ? 'DESC' : 'ASC';

$sql = "SELECT * FROM kecamatan";
if ($search) {
    $sql .= " WHERE nama LIKE '%$search%'";
}
$sql .= " ORDER BY $sort $order";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Data Kecamatan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/data_manager.css">
</head>
<body>
<header>
    <div>
        <h1>Visualisasi Penduduk Kabupaten Jember 2024</h1>
        <p>Kelola data jumlah penduduk, kepadatan dan laju pertumbuhan per kecamatan</p>
    </div>
    <div>
        <a href="index.php" class="btn-nav">Peta Visualisasi</a>
        <a href="spk.php" class="btn-nav" style="background-color: #27ae60; color: white;">SPK Prioritas</a>
    </div>
</header>

<div class="container">
    <h2 class="mb-4">Kelola Data Kecamatan</h2>

    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><?= $edit_data ? 'Edit Data' : 'Tambah Data' ?></h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?= $edit_data ? 'proses_edit.php' : 'proses_tambah.php' ?>">
                        <input type="hidden" name="id" value="<?= $edit_data['id'] ?? 0 ?>">
                        <div class="mb-3">
                            <label class="form-label">Nama Kecamatan</label>
                            <input type="text" class="form-control" name="nama" value="<?= $edit_data['nama'] ?? '' ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Jumlah Penduduk (jiwa)</label>
                            <input type="number" class="form-control" name="jumlah_penduduk" value="<?= $edit_data['jumlah_penduduk'] ?? '' ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Laju Pertumbuhan (%)</label>
                            <input type="number" step="0.01" class="form-control" name="laju_pertumbuhan" value="<?= $edit_data['laju_pertumbuhan'] ?? '' ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Luas Wilayah (km²)</label>
                            <input type="number" step="0.01" class="form-control" name="luas_wilayah" value="<?= $edit_data['luas_wilayah'] ?? '' ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Latitude</label>
                            <input type="number" step="0.000001" class="form-control" name="latitude" value="<?= $edit_data['latitude'] ?? '' ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Longitude</label>
                            <input type="number" step="0.000001" class="form-control" name="longitude" value="<?= $edit_data['longitude'] ?? '' ?>" required>
                        </div>
                        <button type="submit" class="btn btn-custom w-100">Simpan</button>
                        <?php if($edit_data): ?>
                            <a href="data_manager.php" class="btn btn-secondary w-100 mt-2">Batal</a>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Daftar Kecamatan</h5>
                    <form class="d-flex" method="GET">
                        <input type="hidden" name="sort" value="<?= htmlspecialchars($sort) ?>">
                        <input type="hidden" name="order" value="<?= htmlspecialchars($order) ?>">
                        <input class="form-control form-control-sm me-2" type="search" name="search" placeholder="Cari kecamatan..." value="<?= htmlspecialchars($search) ?>">
                        <button class="btn btn-sm btn-light" type="submit">Cari</button>
                    </form>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0">
                            <?php 
                                $next_order = $order === 'ASC' ? 'desc' : 'asc';
                                $icon = $order === 'ASC' ? '<i class="bi bi-caret-up-fill"></i>' : '<i class="bi bi-caret-down-fill"></i>';
                            ?>
                            <thead class="table-light">
                                <tr>
                                    <th><a href="?sort=nama&order=<?= $sort=='nama'?$next_order:'asc' ?>&search=<?= $search ?>">Kecamatan <?= $sort=='nama'?$icon:'' ?></a></th>
                                    <th><a href="?sort=jumlah_penduduk&order=<?= $sort=='jumlah_penduduk'?$next_order:'asc' ?>&search=<?= $search ?>">Penduduk <?= $sort=='jumlah_penduduk'?$icon:'' ?></a></th>
                                    <th><a href="?sort=luas_wilayah&order=<?= $sort=='luas_wilayah'?$next_order:'asc' ?>&search=<?= $search ?>">Luas (km²) <?= $sort=='luas_wilayah'?$icon:'' ?></a></th>
                                    <th><a href="?sort=laju_pertumbuhan&order=<?= $sort=='laju_pertumbuhan'?$next_order:'asc' ?>&search=<?= $search ?>">Laju (%) <?= $sort=='laju_pertumbuhan'?$icon:'' ?></a></th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if($result->num_rows > 0): ?>
                                    <?php while($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($row['nama']) ?></td>
                                        <td><?= number_format($row['jumlah_penduduk'], 0, ',', '.') ?></td>
                                        <td><?= number_format($row['luas_wilayah'], 2, ',', '.') ?></td>
                                        <td><?= $row['laju_pertumbuhan'] ?></td>
                                        <td>
                                            <a href="?edit=<?= $row['id'] ?>&search=<?= $search ?>&sort=<?= $sort ?>&order=<?= $order ?>" class="btn btn-sm btn-warning"><i class="bi bi-pencil"></i></a>
                                            <a href="proses_hapus.php?delete=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Hapus data kecamatan ini?');"><i class="bi bi-trash"></i></a>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr><td colspan="5" class="text-center">Tidak ada data.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
