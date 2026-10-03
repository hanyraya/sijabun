<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/database.php';
check_access([1]);
$db = Database::getInstance();
foreach (explode(';', file_get_contents(__DIR__ . '/../database/2026_10_03_kelompok_sesi.sql')) as $sql) {
    if (trim($sql) !== '') $db->exec($sql);
}
foreach (explode(';', file_get_contents(__DIR__ . '/../database/2026_10_03_kelompok_sesi_multi_kelas.sql')) as $sql) {
    if (trim($sql) !== '') $db->exec($sql);
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=utf-8');
    try {
        if (!is_string($_POST['csrf_token'] ?? null) || !verify_csrf($_POST['csrf_token'])) {
            http_response_code(403);
            throw new InvalidArgumentException('Sesi tidak valid. Muat ulang halaman.');
        }
        $action = $_POST['action'] ?? '';
        $id = (int)($_POST['id'] ?? 0);
        if (!in_array($action, ['create', 'update', 'delete'], true)) throw new InvalidArgumentException('Tindakan tidak valid.');
        $db->beginTransaction();
        if ($action !== 'create') {
            $stmt = $db->prepare('SELECT id FROM kelompok_sesi WHERE id = ? FOR UPDATE');
            $stmt->execute([$id]);
            if (!$stmt->fetch()) throw new InvalidArgumentException('Kelompok tidak ditemukan.');
        }
        if ($action === 'delete') {
            $stmt = $db->prepare('DELETE FROM kelompok_sesi WHERE id = ?');
            $stmt->execute([$id]);
        } else {
            $nama = trim((string)($_POST['nama_kelompok'] ?? ''));
            $rawKelas = $_POST['kelas_ids'] ?? [];
            $tingkat = $_POST['tingkat'] ?? '';
            if (!is_array($rawKelas)) throw new InvalidArgumentException('Daftar kelas tidak valid.');
            $kelasIds = array_values(array_unique(array_map('intval', $rawKelas)));
            $rawIds = $_POST['siswa_ids'] ?? [];
            if (!is_array($rawIds)) throw new InvalidArgumentException('Daftar siswa tidak valid.');
            $ids = array_values(array_unique(array_map('intval', $rawIds)));
            if ($nama === '' || mb_strlen($nama) > 100 || !$kelasIds || min($kelasIds) <= 0 || !$ids || min($ids) <= 0 || !in_array($tingkat, ['X', 'XI', 'XII', 'semua'], true)) {
                throw new InvalidArgumentException('Isi nama kelompok (maksimal 100 karakter), tingkat, minimal satu kelas, dan minimal satu siswa.');
            }
            $kelasMarks = implode(',', array_fill(0, count($kelasIds), '?'));
            $stmt = $db->prepare("SELECT id, tingkat FROM kelas WHERE id IN ($kelasMarks) FOR UPDATE");
            $stmt->execute($kelasIds);
            $validKelas = $stmt->fetchAll();
            if (count($validKelas) !== count($kelasIds)) throw new InvalidArgumentException('Kelas tidak valid.');
            foreach ($validKelas as $k) {
                if ($tingkat !== 'semua' && $k['tingkat'] !== $tingkat) throw new InvalidArgumentException('Kelas harus sesuai tingkat yang dipilih.');
            }
            $kelasId = $kelasIds[0];
            $marks = implode(',', array_fill(0, count($ids), '?'));
            $stmt = $db->prepare("SELECT id FROM siswa WHERE kelas_id IN ($kelasMarks) AND id IN ($marks) FOR UPDATE");
            $stmt->execute(array_merge($kelasIds, $ids));
            if (count($stmt->fetchAll()) !== count($ids)) throw new InvalidArgumentException('Semua peserta harus berasal dari kelas yang dipilih. Muat ulang jika data siswa berubah.');
            if ($action === 'create') {
                $stmt = $db->prepare('INSERT INTO kelompok_sesi (nama_kelompok, kelas_id) VALUES (?, ?)');
                $stmt->execute([$nama, $kelasId]);
                $id = (int)$db->lastInsertId();
            } else {
                $stmt = $db->prepare('UPDATE kelompok_sesi SET nama_kelompok = ?, kelas_id = ? WHERE id = ?');
                $stmt->execute([$nama, $kelasId, $id]);
                $stmt = $db->prepare('DELETE FROM kelompok_sesi_siswa WHERE kelompok_sesi_id = ?');
                $stmt->execute([$id]);
            }
            $stmt = $db->prepare('DELETE FROM kelompok_sesi_kelas WHERE kelompok_sesi_id = ?');
            $stmt->execute([$id]);
            $stmt = $db->prepare('INSERT INTO kelompok_sesi_kelas (kelompok_sesi_id, kelas_id) VALUES (?, ?)');
            foreach ($kelasIds as $kid) $stmt->execute([$id, $kid]);
            $stmt = $db->prepare('INSERT INTO kelompok_sesi_siswa (kelompok_sesi_id, siswa_id) VALUES (?, ?)');
            foreach ($ids as $sid) $stmt->execute([$id, $sid]);
        }
        $db->commit();
        echo json_encode(['success' => true, 'message' => $action === 'delete' ? 'Kelompok berhasil dihapus.' : 'Kelompok berhasil disimpan.']);
    } catch (Throwable $e) {
        if ($db->inTransaction()) $db->rollBack();
        if (http_response_code() !== 403) http_response_code($e instanceof InvalidArgumentException ? 422 : 500);
        if (!($e instanceof InvalidArgumentException)) error_log('Kelompok sesi: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => $e instanceof InvalidArgumentException ? $e->getMessage() : 'Gagal menyimpan perubahan. Silakan coba lagi.']);
    }
    exit;
}
$kelas = $db->query('SELECT id, nama_kelas, tingkat FROM kelas ORDER BY FIELD(tingkat, "X", "XI", "XII"), nama_kelas')->fetchAll();
$siswa = $db->query('SELECT s.id, s.kelas_id, s.nis, s.nisn, s.nama_lengkap, k.nama_kelas FROM siswa s LEFT JOIN kelas k ON k.id = s.kelas_id ORDER BY k.nama_kelas, s.nama_lengkap')->fetchAll();
$kelompokSesi = $db->query('SELECT ks.*,
    (SELECT GROUP_CONCAT(k.nama_kelas ORDER BY k.nama_kelas SEPARATOR ", ") FROM kelompok_sesi_kelas kk JOIN kelas k ON k.id = kk.kelas_id WHERE kk.kelompok_sesi_id = ks.id) AS kelas,
    (SELECT COUNT(*) FROM kelompok_sesi_siswa a JOIN siswa s ON s.id = a.siswa_id JOIN kelompok_sesi_kelas kk ON kk.kelompok_sesi_id = a.kelompok_sesi_id AND kk.kelas_id = s.kelas_id WHERE a.kelompok_sesi_id = ks.id) AS jumlah_peserta
    FROM kelompok_sesi ks ORDER BY ks.id DESC')->fetchAll();
$anggota = [];
foreach ($db->query('SELECT a.kelompok_sesi_id, a.siswa_id FROM kelompok_sesi_siswa a JOIN siswa s ON s.id = a.siswa_id JOIN kelompok_sesi_kelas kk ON kk.kelompok_sesi_id = a.kelompok_sesi_id AND kk.kelas_id = s.kelas_id')->fetchAll() as $row) {
    $anggota[(int)$row['kelompok_sesi_id']][] = (int)$row['siswa_id'];
}
$kelasKelompok = [];
foreach ($db->query('SELECT kelompok_sesi_id, kelas_id FROM kelompok_sesi_kelas')->fetchAll() as $row) {
    $kelasKelompok[(int)$row['kelompok_sesi_id']][] = (int)$row['kelas_id'];
}
foreach ($kelompokSesi as &$kelompok) {
    $kelompok['siswa_ids'] = $anggota[(int)$kelompok['id']] ?? [];
    $kelompok['kelas_ids'] = $kelasKelompok[(int)$kelompok['id']] ?? [];
}
unset($kelompok);
