CREATE TABLE IF NOT EXISTS kelompok_sesi_kelas (
    kelompok_sesi_id INT UNSIGNED NOT NULL,
    kelas_id INT UNSIGNED NOT NULL,
    PRIMARY KEY (kelompok_sesi_id, kelas_id),
    CONSTRAINT fk_kelompok_multi_sesi FOREIGN KEY (kelompok_sesi_id) REFERENCES kelompok_sesi(id) ON DELETE CASCADE,
    CONSTRAINT fk_kelompok_multi_kelas FOREIGN KEY (kelas_id) REFERENCES kelas(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO kelompok_sesi_kelas (kelompok_sesi_id, kelas_id)
SELECT ks.id, ks.kelas_id FROM kelompok_sesi ks
WHERE NOT EXISTS (SELECT 1 FROM kelompok_sesi_kelas kk WHERE kk.kelompok_sesi_id = ks.id);
