<?php
require_once __DIR__ . '/kelompok_sesi_data.php';
require_once __DIR__ . '/../includes/header.php';
?>
<?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

<div id="page-content-wrapper">

    <nav class="navbar navbar-expand-lg navbar-light top-navbar px-4 py-3">
        <h5 class="mb-0 fw-bold">Kelompok Sesi Ujian</h5>
    </nav>

    <div class="container-fluid p-4">

        <div class="card border-0 shadow-sm p-4">

            <div class="d-flex justify-content-between align-items-center mb-3">

                <div>
                    <h6 class="fw-bold m-0">
                        <i class="fa-solid fa-users-viewfinder me-2 text-primary"></i>
                        Daftar Kelompok Sesi
                    </h6>

                    <small class="text-muted">
                        Kelola kelompok peserta untuk digunakan pada pengaturan jadwal ujian.
                    </small>
                </div>

                <button
                    type="button"
                    class="btn btn-primary btn-sm rounded-2"
                    onclick="openModalCreate()">

                    <i class="fa-solid fa-plus me-1"></i>
                    Tambah Kelompok

                </button>

            </div>

            <div class="table-responsive">

                <table
                    id="tableKelompokSesi"
                    class="table table-striped table-hover align-middle w-100">

                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Kelompok Sesi</th>
                            <th>Kelas</th>
                            <th>Jumlah Peserta</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody><?php if (!$kelompokSesi): ?><tr><td colspan="6" class="text-center text-muted py-4">Belum ada kelompok sesi. Klik Tambah Kelompok untuk memilih peserta dari data siswa.</td></tr><?php endif; ?>

                        <?php foreach ($kelompokSesi as $index => $sesi): ?>

                        <tr>

                            <td>
                                <?= $index + 1 ?>
                            </td>

                            <td class="fw-semibold">
                                <?= sanitize($sesi['nama_kelompok']) ?>
                            </td>

                            <td>
                                <span class="badge bg-secondary">
                                    <?= sanitize($sesi['kelas']) ?>
                                </span>
                            </td>

                            <td>
                                <span class="badge bg-info text-dark">
                                    <i class="fa-solid fa-users me-1"></i>
                                    <?= $sesi['jumlah_peserta'] ?> Siswa
                                </span>
                            </td>

                            <td>

                                <?php if ($sesi['status'] === 'Aktif'): ?>

                                    <span class="badge bg-success">
                                        Aktif
                                    </span>

                                <?php else: ?>

                                    <span class="badge bg-secondary">
                                        Non-Aktif
                                    </span>

                                <?php endif; ?>

                            </td>

                            <td>

                                <button
                                    type="button"
                                    class="btn btn-warning btn-sm text-white me-1"
                                    onclick="openModalEdit(<?= $sesi['id'] ?>)">

                                    <i class="fa-solid fa-pen-to-square"></i>

                                </button>

                                <button
                                    type="button"
                                    class="btn btn-danger btn-sm"
                                    onclick="deleteKelompok(<?= (int)$sesi['id'] ?>)">

                                    <i class="fa-solid fa-trash"></i>

                                </button>

                            </td>

                        </tr>

                        <?php endforeach; ?>

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>


<!-- Modal Tambah / Edit -->

<div
    class="modal fade"
    id="kelompokModal"
    tabindex="-1"
    aria-hidden="true">

    <div class="modal-dialog modal-lg modal-dialog-centered">

        <div class="modal-content">

            <div class="modal-header">

                <h5
                    class="modal-title fw-bold"
                    id="modalTitle">

                    Tambah Kelompok Sesi

                </h5>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal">
                </button>

            </div>

            <form id="kelompokForm"><input type="hidden" name="csrf_token" value="<?= sanitize($_SESSION['csrf_token']) ?>">

                <div class="modal-body">

                    <input
                        type="hidden"
                        name="id"
                        id="kelompokId">

                    <div class="mb-3">

                        <label
                            class="form-label fw-semibold">

                            Nama Kelompok Sesi

                        </label>

                        <input
                            type="text"
                            name="nama_kelompok"
                            id="nama_kelompok"
                            class="form-control"
                            placeholder="Contoh: Kelompok Sesi 1" maxlength="100"
                            required>

                    </div>


                    <div class="mb-3">

                        <label
                            class="form-label fw-semibold">

                            Kelas

                        </label>

                        <select
                            name="kelas_id"
                            id="kelas_id"
                            class="form-select"
                            required>

                            <option value="">
                                -- Pilih Kelas --
                            </option>

                            <?php foreach ($kelas as $k): ?>

                                <option value="<?= $k['id'] ?>">
                                    <?= sanitize($k['nama_kelas']) ?>
                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>


                    <div class="mb-3">

                        <div class="d-flex justify-content-between align-items-center mb-2">

                            <label class="form-label fw-semibold mb-0">
                                Pilih Siswa
                            </label>

                            <span
                                class="badge bg-primary"
                                id="jumlahDipilih">

                                0 Siswa

                            </span>

                        </div>


                        <div class="input-group mb-2">

                            <span class="input-group-text">
                                <i class="fa-solid fa-magnifying-glass"></i>
                            </span>

                            <input
                                type="text"
                                id="searchSiswa"
                                class="form-control"
                                placeholder="Cari nama, NIS, atau NISN...">

                        </div>


                        <div
                            id="selectAllContainer"
                            class="border rounded-top bg-light px-3 py-2"
                            style="display:none;">

                            <div class="form-check">

                                <input
                                    type="checkbox"
                                    class="form-check-input"
                                    id="selectAllSiswa">

                                <label
                                    for="selectAllSiswa"
                                    class="form-check-label fw-semibold">

                                    Pilih Semua Siswa

                                </label>

                            </div>

                        </div>


                        <div
                            id="daftarSiswa"
                            class="border rounded-bottom p-2"
                            style="max-height:300px; overflow-y:auto;">

                            <div class="text-center text-muted py-4">

                                <i class="fa-solid fa-users fa-2x mb-2"></i>

                                <div>
                                    Silakan pilih kelas terlebih dahulu.
                                </div>

                            </div>

                        </div>

                    </div>


                    <div class="alert alert-info mb-0">

                        <i class="fa-solid fa-circle-info me-2"></i>

                        Kelompok sesi hanya digunakan untuk mengelompokkan
                        peserta. Jadwal ujian akan ditentukan pada menu
                        <strong>Pengaturan Jadwal</strong>.

                    </div>

                </div>


                <div class="modal-footer">

                    <button
                        type="button"
                        class="btn btn-secondary"
                        data-bs-dismiss="modal">

                        Batal

                    </button>

                    <button
                        type="submit"
                        class="btn btn-primary">

                        <i class="fa-solid fa-save me-1"></i>
                        Simpan Kelompok

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>


<?php require_once __DIR__ . '/../includes/footer.php'; ?>


<script>
const dataSiswa = <?= json_encode($siswa, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) ?>;
const dataKelompokSesi = <?= json_encode($kelompokSesi, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) ?>;
const csrfToken = <?= json_encode($_SESSION['csrf_token']) ?>;
</script>
<script src="../assets/js/kelompok_sesi.js"></script>