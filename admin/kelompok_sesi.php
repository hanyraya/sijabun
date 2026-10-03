```php
<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../config/database.php';

$db = Database::getInstance();

$kelas = $db->query("
    SELECT id, nama_kelas
    FROM kelas
    ORDER BY nama_kelas ASC
")->fetchAll();

$siswa = $db->query("
    SELECT 
        s.id,
        s.kelas_id,
        s.nis,
        s.nisn,
        s.nama_lengkap,
        k.nama_kelas
    FROM siswa s
    LEFT JOIN kelas k ON s.kelas_id = k.id
    ORDER BY k.nama_kelas ASC, s.nama_lengkap ASC
")->fetchAll();

$kelompokSesi = [
    [
        'id' => 1,
        'nama_kelompok' => 'Kelompok Sesi 1',
        'kelas' => 'X TKJ 1',
        'jumlah_peserta' => 30,
        'status' => 'Aktif'
    ],
    [
        'id' => 2,
        'nama_kelompok' => 'Kelompok Sesi 2',
        'kelas' => 'X TKJ 2',
        'jumlah_peserta' => 30,
        'status' => 'Aktif'
    ]
];
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

                    <tbody>

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
                                    onclick="deleteKelompok(
                                        <?= $sesi['id'] ?>,
                                        '<?= sanitize($sesi['nama_kelompok']) ?>'
                                    )">

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

            <form id="kelompokForm">

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
                            placeholder="Contoh: Kelompok Sesi 1"
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

const dataSiswa = <?= json_encode(
    $siswa,
    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
) ?>;

const dataKelompokSesi = <?= json_encode(
    $kelompokSesi,
    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
) ?>;

let modal;
let currentAction = 'create';


$(document).ready(function() {

    $('#tableKelompokSesi').DataTable();

    modal = new bootstrap.Modal(
        document.getElementById('kelompokModal')
    );

    $('#kelas_id').on('change', function() {
        tampilkanSiswa();
    });

    $('#searchSiswa').on('keyup', function() {
        tampilkanSiswa();
    });

    $('#selectAllSiswa').on('change', function() {

        $('.siswa-checkbox:visible').prop(
            'checked',
            $(this).is(':checked')
        );

        updateJumlahDipilih();

    });

    $(document).on(
        'change',
        '.siswa-checkbox',
        function() {

            updateJumlahDipilih();
            updateSelectAll();

        }
    );

});


function openModalCreate() {

    currentAction = 'create';

    $('#modalTitle').text('Tambah Kelompok Sesi');

    $('#kelompokForm')[0].reset();

    $('#kelompokId').val('');

    $('#searchSiswa').val('');

    $('#jumlahDipilih').text('0 Siswa');

    $('#selectAllSiswa').prop('checked', false);

    $('#selectAllContainer').hide();

    $('#daftarSiswa').html(`
        <div class="text-center text-muted py-4">
            <i class="fa-solid fa-users fa-2x mb-2"></i>
            <div>
                Silakan pilih kelas terlebih dahulu.
            </div>
        </div>
    `);

    modal.show();

}


function tampilkanSiswa() {

    const kelasId = $('#kelas_id').val();
    const keyword = $('#searchSiswa').val().toLowerCase().trim();
    const container = $('#daftarSiswa');

    container.empty();

    $('#selectAllSiswa').prop('checked', false);
    $('#jumlahDipilih').text('0 Siswa');

    if (!kelasId) {

        $('#selectAllContainer').hide();

        container.html(`
            <div class="text-center text-muted py-4">
                <i class="fa-solid fa-users fa-2x mb-2"></i>
                <div>
                    Silakan pilih kelas terlebih dahulu.
                </div>
            </div>
        `);

        return;
    }


    let siswaKelas = dataSiswa.filter(function(siswa) {

        return siswa.kelas_id == kelasId;

    });


    if (keyword !== '') {

        siswaKelas = siswaKelas.filter(function(siswa) {

            const nama = (siswa.nama_lengkap || '').toLowerCase();
            const nis = (siswa.nis || '').toLowerCase();
            const nisn = (siswa.nisn || '').toLowerCase();

            return (
                nama.includes(keyword) ||
                nis.includes(keyword) ||
                nisn.includes(keyword)
            );

        });

    }


    if (siswaKelas.length === 0) {

        $('#selectAllContainer').hide();

        container.html(`
            <div class="text-center text-muted py-4">
                <i class="fa-solid fa-user-slash fa-2x mb-2"></i>
                <div>
                    Tidak ada siswa yang ditemukan.
                </div>
            </div>
        `);

        return;
    }


    $('#selectAllContainer').show();


    siswaKelas.forEach(function(siswa) {

        container.append(`

            <div class="form-check border-bottom py-2">

                <input
                    class="form-check-input siswa-checkbox"
                    type="checkbox"
                    name="siswa_ids[]"
                    value="${siswa.id}"
                    id="siswa_${siswa.id}">

                <label
                    class="form-check-label w-100"
                    for="siswa_${siswa.id}">

                    <strong>
                        ${escapeHtml(siswa.nama_lengkap || '-')}
                    </strong>

                    <br>

                    <small class="text-muted">
                        NIS: ${escapeHtml(siswa.nis || '-')}
                        &nbsp; | &nbsp;
                        NISN: ${escapeHtml(siswa.nisn || '-')}
                    </small>

                </label>

            </div>

        `);

    });


    updateSelectAll();

}


function updateJumlahDipilih() {

    const jumlah =
        $('.siswa-checkbox:checked').length;

    $('#jumlahDipilih').text(
        jumlah + ' Siswa'
    );

}


function updateSelectAll() {

    const semua =
        $('.siswa-checkbox:visible').length;

    const dipilih =
        $('.siswa-checkbox:visible:checked').length;

    $('#selectAllSiswa').prop(
        'checked',
        semua > 0 && semua === dipilih
    );

}


function escapeHtml(value) {

    return String(value)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');

}


function openModalEdit(id) {

    currentAction = 'update';

    $('#modalTitle').text('Edit Kelompok Sesi');

    const sesi = dataKelompokSesi.find(function(item) {

        return item.id == id;

    });

    if (!sesi) {

        Swal.fire(
            'Gagal!',
            'Data kelompok tidak ditemukan.',
            'error'
        );

        return;
    }

    $('#kelompokId').val(sesi.id);

    $('#nama_kelompok').val(
        sesi.nama_kelompok
    );

    modal.show();

}


$('#kelompokForm').on('submit', function(e) {

    e.preventDefault();

    const namaKelompok =
        $('#nama_kelompok').val().trim();

    const kelasId =
        $('#kelas_id').val();

    const siswaDipilih =
        $('.siswa-checkbox:checked').length;


    if (!namaKelompok) {

        Swal.fire(
            'Perhatian!',
            'Nama kelompok wajib diisi.',
            'warning'
        );

        return;
    }


    if (!kelasId) {

        Swal.fire(
            'Perhatian!',
            'Silakan pilih kelas terlebih dahulu.',
            'warning'
        );

        return;
    }


    if (siswaDipilih === 0) {

        Swal.fire(
            'Perhatian!',
            'Silakan pilih minimal satu siswa.',
            'warning'
        );

        return;
    }


    Swal.fire({
        title: 'Berhasil!',
        text:
            'Kelompok "' +
            namaKelompok +
            '" berisi ' +
            siswaDipilih +
            ' siswa.',
        icon: 'success'
    }).then(function() {

        modal.hide();

    });

});


function deleteKelompok(id, nama) {

    Swal.fire({

        title: 'Hapus Kelompok?',

        text:
            'Apakah Anda yakin ingin menghapus ' +
            nama +
            '?',

        icon: 'warning',

        showCancelButton: true,

        confirmButtonColor: '#d33',

        cancelButtonColor: '#3085d6',

        confirmButtonText: 'Ya, Hapus!',

        cancelButtonText: 'Batal'

    }).then(function(result) {

        if (result.isConfirmed) {

            Swal.fire(
                'Terhapus!',
                'Data kelompok sesi berhasil dihapus.',
                'success'
            );

        }

    });

}

</script>
```

**Perubahan utamanya:** sekarang halaman ini tidak lagi mengenal `ujian`, `tanggal`, `jam`, atau `durasi`. Dia hanya menyimpan konsep **siapa saja yang berada dalam kelompok sesi** dan dari **kelas mana**.

Nantinya di **Pengaturan Jadwal Ujian**, pilihan kelompok sesi bisa dibuat seperti:

```text
Ujian       : [ Sumatif Tengah Semester ▼ ]
Kelompok    : [ Kelompok Sesi 1 ▼ ]
Tanggal     : [ 05-10-2026 ]
Jam         : [ 07:00 ]
Durasi      : [ 90 menit ]
```

Jadi kelompok yang dibuat di halaman ini menjadi **master peserta** yang tinggal dipanggil oleh Pengaturan Jadwal.
