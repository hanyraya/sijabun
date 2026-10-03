CREATE TABLE IF NOT EXISTS kelompok_sesi (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    nama_kelompok VARCHAR(100) NOT NULL,
    kelas_id INT UNSIGNED NOT NULL,
    status ENUM('Aktif', 'Non-Aktif') NOT NULL DEFAULT 'Aktif',
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_kelompok_sesi_kelas FOREIGN KEY (kelas_id) REFERENCES kelas(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS kelompok_sesi_siswa (
    kelompok_sesi_id INT UNSIGNED NOT NULL,
    siswa_id INT UNSIGNED NOT NULL,
    PRIMARY KEY (kelompok_sesi_id, siswa_id),
    CONSTRAINT fk_kelompok_sesi_anggota FOREIGN KEY (kelompok_sesi_id) REFERENCES kelompok_sesi(id) ON DELETE CASCADE,
    CONSTRAINT fk_kelompok_sesi_siswa FOREIGN KEY (siswa_id) REFERENCES siswa(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
