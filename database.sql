-- Database name: ta_perbankan;

CREATE TABLE roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_role VARCHAR(50) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO roles (nama_role) VALUES
('Admin'),
('Nasabah');

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role_id INT NOT NULL,
    nama_lengkap VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    no_hp VARCHAR(20),
    foto_profil VARCHAR(255) DEFAULT 'default.png',
    status_akun ENUM('Pending','Aktif','Nonaktif') DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL,

    CONSTRAINT fk_users_role
    FOREIGN KEY (role_id)
    REFERENCES roles(id)
);

INSERT INTO users
(role_id,nama_lengkap,email,password,no_hp,status_akun)
VALUES
(1,'Administrator','admin@bank.com','$2a$12$oRkJ8fwIsNSHNQgJnSQd..pq1kSR5sgaiRx2Ms7r4VhBYuvmA30Iy','081234567890','Aktif'),
(2,'Budi Santoso','budi@mail.com','$2a$12$re/KjMG8.o7n5ZTkfH7lHO0dvaqCzJFX/A1knIiU2.PbLIa.S3HEG','081122334455','Aktif');

CREATE TABLE rekening (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    nomor_rekening_encrypted TEXT NOT NULL,
    jenis_rekening ENUM('Tabungan','Giro') DEFAULT 'Tabungan',
    saldo DECIMAL(18,2) DEFAULT 0,
    status_rekening ENUM('Aktif','Blokir') DEFAULT 'Aktif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_rekening_user
    FOREIGN KEY (user_id)
    REFERENCES users(id)
);

INSERT INTO rekening
(user_id,nomor_rekening_encrypted,saldo)
VALUES
(2,'duwg6Dji2Wh8lFnQr/j6qHw6G2CecG5+kJ8FVKsOTq8=',5000000),
(2,'RGej7It9tv6zLgqZbe9zMbm2tp+5nZCQ5kovJa+50Es=',2500000);

CREATE TABLE merchant (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_merchant VARCHAR(100) NOT NULL,
    status_merchant ENUM('Aktif','Nonaktif') DEFAULT 'Aktif'
);

INSERT INTO merchant (nama_merchant)
VALUES
('Alfamart'),
('Indomaret');

CREATE TABLE provider_ewallet (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_provider VARCHAR(100) NOT NULL,
    biaya_admin DECIMAL(12,2) DEFAULT 2500
);

INSERT INTO provider_ewallet
(nama_provider,biaya_admin)
VALUES
('OVO',2500),
('DANA',2500);

CREATE TABLE transaksi (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    rekening_id INT NOT NULL,
    jenis_transaksi ENUM('SETOR','TARIK','TRANSFER_KELUAR','TRANSFER_MASUK','TOPUP_EWALLET') NOT NULL,
    nominal DECIMAL(18,2) NOT NULL,
    saldo_sebelum DECIMAL(18,2) NOT NULL,
    saldo_sesudah DECIMAL(18,2) NOT NULL,
    keterangan TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_transaksi_rekening
    FOREIGN KEY (rekening_id)
    REFERENCES rekening(id)
);

INSERT INTO transaksi
(rekening_id,jenis_transaksi,nominal,saldo_sebelum,saldo_sesudah,keterangan)
VALUES
(1,'SETOR',500000,4500000,5000000,'Setor melalui Alfamart'),
(1,'TARIK',200000,5000000,4800000,'Tarik tunai');

CREATE TABLE transfer (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    rekening_pengirim_id INT NOT NULL,
    rekening_penerima_id INT NOT NULL,
    nominal DECIMAL(18,2) NOT NULL,
    catatan VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_transfer_pengirim
    FOREIGN KEY (rekening_pengirim_id)
    REFERENCES rekening(id),

    CONSTRAINT fk_transfer_penerima
    FOREIGN KEY (rekening_penerima_id)
    REFERENCES rekening(id)
);

INSERT INTO transfer
(rekening_pengirim_id,rekening_penerima_id,nominal,catatan)
VALUES
(1,2,100000,'Transfer keluarga'),
(2,1,50000,'Transfer balik');

CREATE TABLE topup_ewallet (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    rekening_id INT NOT NULL,
    provider_id INT NOT NULL,
    nomor_tujuan VARCHAR(30) NOT NULL,
    nominal DECIMAL(18,2) NOT NULL,
    biaya_admin DECIMAL(18,2) NOT NULL,
    total_potongan DECIMAL(18,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_topup_rekening
    FOREIGN KEY (rekening_id)
    REFERENCES rekening(id),

    CONSTRAINT fk_topup_provider
    FOREIGN KEY (provider_id)
    REFERENCES provider_ewallet(id)
);

INSERT INTO topup_ewallet
(rekening_id,provider_id,nomor_tujuan,nominal,biaya_admin,total_potongan)
VALUES
(1,1,'081234567890',100000,2500,102500),
(1,2,'082345678901',50000,2500,52500);

CREATE TABLE login_log (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    login_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    logout_at TIMESTAMP NULL,

    FOREIGN KEY (user_id)
    REFERENCES users(id)
);

INSERT INTO login_log
(user_id,login_at)
VALUES
(1,'2026-06-06 14:64:00'),
(2,'2026-06-06 05:00:00');

CREATE TABLE audit_log (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    aktivitas VARCHAR(255) NOT NULL,
    deskripsi TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id)
    REFERENCES users(id)
);

INSERT INTO audit_log
(user_id,aktivitas,deskripsi)VALUES
(1,'Login','Admin berhasil login ke sistem'),
(2,'Transfer','Nasabah melakukan transfer ke rekening lain');