-- =============================================================================
-- Migration SQL untuk Modul Admin E-Banking
-- Database: ta_perbankan
-- Jalankan script ini setelah database utama (database.sql) sudah ter-setup.
-- =============================================================================

-- 1. Tambahkan kolom pada tabel audit_log (ip_address, user_agent, admin_id)
ALTER TABLE audit_log
    ADD COLUMN ip_address VARCHAR(45) DEFAULT NULL AFTER deskripsi,
    ADD COLUMN user_agent TEXT DEFAULT NULL AFTER ip_address,
    ADD COLUMN admin_id INT DEFAULT NULL AFTER user_agent;

-- 2. Tambahkan kolom verifikasi pada tabel users
ALTER TABLE users
    ADD COLUMN verified_at TIMESTAMP NULL DEFAULT NULL AFTER updated_at,
    ADD COLUMN verified_by INT DEFAULT NULL AFTER verified_at;

-- 3. Tabel password_resets
CREATE TABLE IF NOT EXISTS password_resets (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    admin_id INT NOT NULL,
    temp_password VARCHAR(50) NOT NULL,
    reset_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_password_resets_user
    FOREIGN KEY (user_id)
    REFERENCES users(id),

    CONSTRAINT fk_password_resets_admin
    FOREIGN KEY (admin_id)
    REFERENCES users(id)
);

-- 4. Tabel account_verifications
CREATE TABLE IF NOT EXISTS account_verifications (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    admin_id INT NOT NULL,
    status_sebelum ENUM('Pending','Aktif','Nonaktif') NOT NULL,
    status_sesudah ENUM('Pending','Aktif','Nonaktif') NOT NULL,
    keterangan TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_account_verif_user
    FOREIGN KEY (user_id)
    REFERENCES users(id),

    CONSTRAINT fk_account_verif_admin
    FOREIGN KEY (admin_id)
    REFERENCES users(id)
);

-- 5. Tabel account_status_history (riwayat perubahan status akun)
CREATE TABLE IF NOT EXISTS account_status_history (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    admin_id INT NOT NULL,
    status_sebelum ENUM('Pending','Aktif','Nonaktif') NOT NULL,
    status_sesudah ENUM('Pending','Aktif','Nonaktif') NOT NULL,
    keterangan TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_status_history_user
    FOREIGN KEY (user_id)
    REFERENCES users(id),

    CONSTRAINT fk_status_history_admin
    FOREIGN KEY (admin_id)
    REFERENCES users(id)
);
