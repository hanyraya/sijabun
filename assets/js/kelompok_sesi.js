const modalKelompok = new bootstrap.Modal(document.getElementById('kelompokModal'));
let selectedSiswa = new Set();
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
function tampilkanSiswa() {
    const kelasId = $('#kelas_id').val();
    const keyword = $('#searchSiswa').val().trim().toLowerCase();
    const rows = dataSiswa.filter(s => String(s.kelas_id) === kelasId &&
        [s.nama_lengkap, s.nis, s.nisn].some(v => String(v || '').toLowerCase().includes(keyword)));
    $('#selectAllContainer').toggle(rows.length > 0);
    $('#daftarSiswa').html(rows.length ? rows.map(s => `
        <div class="form-check border-bottom py-2">
            <input class="form-check-input siswa-checkbox" type="checkbox" value="${Number(s.id)}"
                id="siswa_${Number(s.id)}" ${selectedSiswa.has(Number(s.id)) ? 'checked' : ''}>
            <label class="form-check-label w-100" for="siswa_${Number(s.id)}">
                <strong>${escapeHtml(s.nama_lengkap)}</strong><br>
                <small class="text-muted">NIS: ${escapeHtml(s.nis || '-')} | NISN: ${escapeHtml(s.nisn || '-')}</small>
            </label>
        </div>`).join('') : `<div class="text-center text-muted py-4">${kelasId ? 'Tidak ada siswa yang ditemukan.' : 'Silakan pilih kelas terlebih dahulu.'}</div>`);
    updateJumlahDipilih();
}
function openModalCreate() {
    currentAction = 'create';
    $('#kelompokForm')[0].reset();
    $('#kelompokId').val('');
    selectedSiswa.clear();
    $('#modalTitle').text('Tambah Kelompok Sesi');
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
    $('#kelas_id').val(sesi.kelas_id);
    selectedSiswa = new Set(sesi.siswa_ids.map(Number));
    tampilkanSiswa();
    modalKelompok.show();
}
$('#kelas_id').on('change', () => { selectedSiswa.clear(); tampilkanSiswa(); });
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
    if (!selectedSiswa.size) {
        Swal.fire('Perhatian!', 'Silakan pilih minimal satu siswa.', 'warning');
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
