const modalKelompok = new bootstrap.Modal(document.getElementById('kelompokModal'));
let selectedSiswa = new Set();
let selectedKelas = new Set();
let currentAction = 'create';
function escapeHtml(value) {
    return String(value).replace(/[&<>"']/g, char => ({'&':'&amp;', '<':'&lt;', '>':'&gt;', '"':'&quot;', "'":'&#039;'}[char]));
}
function updateJumlahDipilih() {
    $('#jumlahDipilih').text(selectedSiswa.size + ' Siswa');
    const boxes = [...document.querySelectorAll('.siswa-checkbox')];
    const checked = boxes.filter(box => box.checked).length;
    $('#selectAllSiswa').prop('checked', boxes.length > 0 && checked === boxes.length)
        .prop('indeterminate', checked > 0 && checked < boxes.length);
}
function tampilkanKelas() {
    const tingkat = $('#tingkat').val();
    const rows = dataKelas.filter(k => tingkat === 'semua' || k.tingkat === tingkat);
    $('#daftarKelas').html(rows.length ? rows.map(k => `
        <div class="form-check py-1">
            <input class="form-check-input kelas-checkbox" type="checkbox" name="kelas_ids[]"
                value="${Number(k.id)}" id="kelas_${Number(k.id)}" ${selectedKelas.has(Number(k.id)) ? 'checked' : ''}>
            <label class="form-check-label" for="kelas_${Number(k.id)}">${escapeHtml(k.nama_kelas)}</label>
        </div>`).join('') : '<div class="text-muted">' + (tingkat ? 'Belum ada kelas pada tingkat ini.' : 'Silakan pilih tingkat terlebih dahulu.') + '</div>');
    const checked = rows.filter(k => selectedKelas.has(Number(k.id))).length;
    $('#selectAllKelas').prop('disabled', !rows.length).prop('checked', rows.length > 0 && checked === rows.length)
        .prop('indeterminate', checked > 0 && checked < rows.length);
}
function sinkronkanPeserta() {
    const valid = new Set(dataSiswa.filter(s => selectedKelas.has(Number(s.kelas_id))).map(s => Number(s.id)));
    selectedSiswa = new Set([...selectedSiswa].filter(id => valid.has(id)));
    tampilkanKelas();
    tampilkanSiswa();
}
function tampilkanSiswa() {
    const keyword = $('#searchSiswa').val().trim().toLowerCase();
    const rows = dataSiswa.filter(s => selectedKelas.has(Number(s.kelas_id)) &&
        [s.nama_lengkap, s.nis, s.nisn, s.nama_kelas].some(v => String(v || '').toLowerCase().includes(keyword)));
    $('#selectAllContainer').toggle(rows.length > 0);
    $('#daftarSiswa').html(rows.length ? rows.map(s => `
        <div class="form-check border-bottom py-2">
            <input class="form-check-input siswa-checkbox" type="checkbox" value="${Number(s.id)}"
                id="siswa_${Number(s.id)}" ${selectedSiswa.has(Number(s.id)) ? 'checked' : ''}>
            <label class="form-check-label w-100" for="siswa_${Number(s.id)}">
                <strong>${escapeHtml(s.nama_lengkap)}</strong><br>
                <small class="text-muted">${escapeHtml(s.nama_kelas || '-')} | NIS: ${escapeHtml(s.nis || '-')} | NISN: ${escapeHtml(s.nisn || '-')}</small>
            </label>
        </div>`).join('') : `<div class="text-center text-muted py-4">${selectedKelas.size ? 'Tidak ada siswa yang ditemukan.' : 'Silakan pilih tingkat dan kelas terlebih dahulu.'}</div>`);
    updateJumlahDipilih();
}
function openModalCreate() {
    currentAction = 'create';
    $('#kelompokForm')[0].reset();
    $('#kelompokId').val('');
    selectedSiswa.clear();
    selectedKelas.clear();
    $('#modalTitle').text('Tambah Kelompok Sesi');
    tampilkanKelas();
    tampilkanSiswa();
    modalKelompok.show();
}
function openModalEdit(id) {
    const sesi = dataKelompokSesi.find(s => Number(s.id) === Number(id));
    if (!sesi) return;
    $('#kelompokForm')[0].reset();
    currentAction = 'update';
    $('#modalTitle').text('Edit Kelompok Sesi');
    $('#kelompokId').val(sesi.id);
    $('#nama_kelompok').val(sesi.nama_kelompok);
    selectedKelas = new Set(sesi.kelas_ids.map(Number));
    const tingkat = new Set(dataKelas.filter(k => selectedKelas.has(Number(k.id))).map(k => k.tingkat));
    $('#tingkat').val(tingkat.size === 1 ? [...tingkat][0] : 'semua');
    selectedSiswa = new Set(sesi.siswa_ids.map(Number));
    tampilkanKelas();
    tampilkanSiswa();
    modalKelompok.show();
}
$('#tingkat').on('change', function() {
    const tingkat = this.value;
    selectedKelas = new Set(dataKelas.filter(k => tingkat === 'semua' || k.tingkat === tingkat).map(k => Number(k.id)));
    sinkronkanPeserta();
});
$(document).on('change', '.kelas-checkbox', function() {
    this.checked ? selectedKelas.add(Number(this.value)) : selectedKelas.delete(Number(this.value));
    sinkronkanPeserta();
});
$('#selectAllKelas').on('change', function() {
    selectedKelas = this.checked ? new Set([...document.querySelectorAll('.kelas-checkbox')].map(box => Number(box.value))) : new Set();
    sinkronkanPeserta();
});
$('#searchSiswa').on('input', tampilkanSiswa);
$(document).on('change', '.siswa-checkbox', function() {
    this.checked ? selectedSiswa.add(Number(this.value)) : selectedSiswa.delete(Number(this.value));
    updateJumlahDipilih();
});
$('#selectAllSiswa').on('change', function() {
    const checked = this.checked;
    $('.siswa-checkbox').each(function() {
        this.checked = checked;
        checked ? selectedSiswa.add(Number(this.value)) : selectedSiswa.delete(Number(this.value));
    });
    updateJumlahDipilih();
});
async function kirimKelompok(formData) {
    const response = await fetch('kelompok_sesi.php', {method: 'POST', body: formData});
    const result = await response.json();
    if (!response.ok || !result.success) throw new Error(result.message || 'Perubahan gagal disimpan.');
    await Swal.fire('Berhasil!', result.message, 'success');
    window.location.reload();
}
$('#kelompokForm').on('submit', async function(event) {
    event.preventDefault();
    if (!selectedKelas.size || !selectedSiswa.size) {
        Swal.fire('Perhatian!', 'Silakan pilih minimal satu kelas dan satu siswa.', 'warning');
        return;
    }
    const formData = new FormData(this);
    formData.append('action', currentAction);
    selectedSiswa.forEach(id => formData.append('siswa_ids[]', id));
    const button = $(this).find('[type="submit"]').prop('disabled', true);
    try { await kirimKelompok(formData); }
    catch (error) { Swal.fire('Gagal!', error.message, 'error'); }
    finally { button.prop('disabled', false); }
});
async function deleteKelompok(id) {
    const sesi = dataKelompokSesi.find(s => Number(s.id) === Number(id));
    if (!sesi) return;
    const result = await Swal.fire({
        title: 'Hapus Kelompok?', text: 'Apakah Anda yakin ingin menghapus ' + sesi.nama_kelompok + '?',
        icon: 'warning', showCancelButton: true, confirmButtonColor: '#d33',
        confirmButtonText: 'Ya, Hapus!', cancelButtonText: 'Batal'
    });
    if (!result.isConfirmed) return;
    const formData = new FormData();
    formData.append('action', 'delete');
    formData.append('id', id);
    formData.append('csrf_token', csrfToken);
    try { await kirimKelompok(formData); }
    catch (error) { Swal.fire('Gagal!', error.message, 'error'); }
}
