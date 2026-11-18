CREATE DATABASE IF NOT EXISTS db_fotokopi_samboja;
USE db_fotokopi_samboja;

-- 1. Tabel Master Pelanggan
CREATE TABLE pelanggan (
    id_pelanggan VARCHAR(10) PRIMARY KEY,
    nama_pelanggan VARCHAR(100) NOT NULL,
    no_whatsapp VARCHAR(20)
);

-- 2. Tabel Pesanan (Transaksi Utama)
CREATE TABLE pesanan (
    id_pesanan VARCHAR(10) PRIMARY KEY,
    id_pelanggan VARCHAR(10) NOT NULL,
    tanggal_pesanan DATE NOT NULL,
    keterangan VARCHAR(255),
    CONSTRAINT fk_pesanan_pelanggan
        FOREIGN KEY (id_pelanggan)
        REFERENCES pelanggan(id_pelanggan)
);

-- 3. Tabel Master Katalog Layanan (Jasa)
CREATE TABLE layanan (
    id_layanan VARCHAR(10) PRIMARY KEY,
    nama_layanan VARCHAR(100) NOT NULL,
    kategori VARCHAR(30) NOT NULL DEFAULT 'Jasa',
    satuan VARCHAR(20) NOT NULL,
    harga DECIMAL(12,2) NOT NULL DEFAULT 0,
    CONSTRAINT chk_harga_layanan CHECK (harga >= 0)
);

-- 4. Tabel Master Katalog Barang (ATK)
CREATE TABLE barang (
    id_barang VARCHAR(10) PRIMARY KEY,
    nama_barang VARCHAR(100) NOT NULL,
    satuan VARCHAR(20) NOT NULL,
    harga_jual DECIMAL(12,2) NOT NULL DEFAULT 0,
    stok_tersedia INT NOT NULL DEFAULT 0,
    CONSTRAINT chk_harga_barang CHECK (harga_jual >= 0),
    CONSTRAINT chk_stok_barang CHECK (stok_tersedia >= 0)
);

-- 5. Tabel Detail Pesanan (Perantara/Junction Table M:N)
CREATE TABLE detail_pesanan (
    id_detail INT AUTO_INCREMENT PRIMARY KEY,
    id_pesanan VARCHAR(10) NOT NULL,
    id_layanan VARCHAR(10) NULL,
    id_barang VARCHAR(10) NULL,
    jumlah INT NOT NULL,
    harga_satuan DECIMAL(12,2) NOT NULL,

    CONSTRAINT fk_detail_pesanan
        FOREIGN KEY (id_pesanan)
        REFERENCES pesanan(id_pesanan),

    CONSTRAINT fk_detail_layanan
        FOREIGN KEY (id_layanan)
        REFERENCES layanan(id_layanan),

    CONSTRAINT fk_detail_barang
        FOREIGN KEY (id_barang)
        REFERENCES barang(id_barang),
    -- Constraint Exclusive Arc (Hanya boleh Layanan ATAU Barang)
    CONSTRAINT chk_satu_jenis_item CHECK (
        (id_layanan IS NOT NULL AND id_barang IS NULL)
        OR
        (id_layanan IS NULL AND id_barang IS NOT NULL)
    ),

    CONSTRAINT chk_jumlah_detail CHECK (jumlah > 0),
    CONSTRAINT chk_harga_detail CHECK (harga_satuan >= 0)
);

-- 6. Tabel Nota (Ringkasan Transaksi / Relasi 1:1 Opsional)
CREATE TABLE nota (
    id_nota VARCHAR(10) PRIMARY KEY,
    id_pesanan VARCHAR(10) NOT NULL UNIQUE,
    tanggal_nota DATE NOT NULL,
    total_nota DECIMAL(12,2) NOT NULL DEFAULT 0,

    CONSTRAINT fk_nota_pesanan
        FOREIGN KEY (id_pesanan)
        REFERENCES pesanan(id_pesanan),

    CONSTRAINT chk_total_nota CHECK (total_nota >= 0)
);

-- 7. Tabel Pembayaran (Riwayat Bayar Tunai / QRIS)
CREATE TABLE pembayaran (
    id_pembayaran INT AUTO_INCREMENT PRIMARY KEY,
    id_pesanan VARCHAR(10) NOT NULL,
    tanggal_bayar DATE NOT NULL,
    metode_pembayaran ENUM('Tunai','QRIS') NOT NULL,
    jumlah_bayar DECIMAL(12,2) NOT NULL,

    CONSTRAINT fk_pembayaran_pesanan
        FOREIGN KEY (id_pesanan)
        REFERENCES pesanan(id_pesanan),

    CONSTRAINT chk_jumlah_bayar CHECK (jumlah_bayar >= 0)
);

-- 8. Tabel Pencatatan Stok (Audit Persediaan Barang Fisik)
CREATE TABLE pencatatan_stok (
    id_pencatatan INT AUTO_INCREMENT PRIMARY KEY,
    id_barang VARCHAR(10) NOT NULL,
    tanggal_pencatatan DATE NOT NULL,
    jenis_transaksi ENUM('Masuk','Keluar','Penyesuaian') NOT NULL,
    jumlah INT NOT NULL,
    keterangan VARCHAR(255),

    CONSTRAINT fk_stok_barang
        FOREIGN KEY (id_barang)
        REFERENCES barang(id_barang),

    CONSTRAINT chk_jumlah_stok CHECK (jumlah > 0)
);

ALTER TABLE pesanan
ADD COLUMN status_pesanan ENUM('Diproses','Selesai','Diambil') NOT NULL DEFAULT 'Diproses';

-- 1. Insert Pelanggan
INSERT INTO pelanggan VALUES
('C001', 'Budi Santoso', '081234567890'),
('C002', 'Siti Aminah', '089876543210');

-- 2. Insert Katalog Layanan (Jasa)
INSERT INTO layanan VALUES
('L001', 'Fotokopi', 'Jasa', 'Lembar', 300),
('L002', 'Print Dokumen', 'Jasa', 'Lembar', 1000),
('L003', 'Scan Dokumen', 'Jasa', 'Lembar', 2000),
('L004', 'Cetak Foto', 'Jasa', 'Lembar', 3000),
('L005', 'Laminasi', 'Jasa', 'Lembar', 5000);

-- 3. Insert Katalog Barang (ATK)
INSERT INTO barang VALUES
('B001', 'Pulpen Pilot', 'Buah', 2500, 20),
('B002', 'Kertas HVS A4', 'Rim', 55000, 10);

-- 4. Insert Transaksi Pesanan
INSERT INTO pesanan (id_pesanan, id_pelanggan, tanggal_pesanan, keterangan, status_pesanan) VALUES
('P001', 'C001', '2026-10-20', 'Print berkas dan fotokopi', 'Diambil'),
('P002', 'C002', '2026-10-20', 'Beli pulpen dan fotokopi', 'Diambil'),
('P003', 'C001', '2026-10-21', 'Print dan scan dokumen', 'Selesai');

-- 5. Insert Detail Pesanan
INSERT INTO detail_pesanan (id_pesanan, id_layanan, id_barang, jumlah, harga_satuan) VALUES
('P001', 'L001', NULL, 20, 300),
('P001', 'L002', NULL, 10, 1000),
('P002', 'L001', NULL, 30, 300),
('P002', NULL, 'B001', 2, 2500),
('P003', 'L002', NULL, 15, 1000),
('P003', 'L003', NULL, 2, 2000);

-- 6. Insert Nota
INSERT INTO nota VALUES
('N001', 'P001', '2026-10-20', 16000),
('N002', 'P002', '2026-10-20', 14000),
('N003', 'P003', '2026-10-21', 19000);

-- 7. Insert Pembayaran (Tunai / QRIS)
INSERT INTO pembayaran (id_pesanan, tanggal_bayar, metode_pembayaran, jumlah_bayar) VALUES
('P001', '2026-10-20', 'Tunai', 16000),
('P002', '2026-10-20', 'QRIS', 14000),
('P003', '2026-10-21', 'Tunai', 19000);

-- 8. Insert Pencatatan Stok
INSERT INTO pencatatan_stok (id_barang, tanggal_pencatatan, jenis_transaksi, jumlah, keterangan) VALUES
('B001', '2026-10-20', 'Masuk', 10, 'Stok masuk dari supplier'),
('B001', '2026-10-20', 'Keluar', 2, 'Barang terjual P002'),
('B002', '2026-10-21', 'Masuk', 3, 'Pembelian stok HVS');