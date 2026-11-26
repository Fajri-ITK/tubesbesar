CREATE DATABASE IF NOT EXISTS db_fotokopi_samboja;
USE db_fotokopi_samboja;


-- 1. Tabel Pesanan (Transaksi Utama)
CREATE TABLE pesanan (
    id_pesanan VARCHAR(10) PRIMARY KEY,
    nama_pelanggan VARCHAR(100) NOT NULL,
    no_whatsapp VARCHAR(20),
    tanggal_pesanan DATE NOT NULL,
    keterangan VARCHAR(255),
    status_pesanan ENUM(
        'Diproses',
        'Selesai',
        'Diambil'
    ) NOT NULL DEFAULT 'Diproses',
    status_pembayaran ENUM(
    'Belum Bayar',
    'DP',
    'Lunas'
    ) NOT NULL DEFAULT 'Belum Bayar'
);

-- 2. Tabel Master Katalog Layanan (Jasa)
CREATE TABLE layanan (
    id_layanan VARCHAR(10) PRIMARY KEY,
    nama_layanan VARCHAR(100) NOT NULL,
    kategori VARCHAR(30) NOT NULL DEFAULT 'Jasa',
    satuan VARCHAR(20) NOT NULL,
    harga DECIMAL(12,2) NOT NULL DEFAULT 0,
    CONSTRAINT chk_harga_layanan CHECK (harga >= 0)
);

-- 3. Tabel Master Katalog Barang (ATK)
CREATE TABLE barang (
    id_barang VARCHAR(10) PRIMARY KEY,
    nama_barang VARCHAR(100) NOT NULL,
    satuan VARCHAR(20) NOT NULL,
    harga_jual DECIMAL(12,2) NOT NULL DEFAULT 0,
    stok_tersedia INT NOT NULL DEFAULT 0,
    CONSTRAINT chk_harga_barang CHECK (harga_jual >= 0),
    CONSTRAINT chk_stok_barang CHECK (stok_tersedia >= 0)
);

-- 4. Tabel Detail Pesanan (Perantara/Junction Table M:N)
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


-- 5. Tabel Pembayaran
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

-- 6. Tabel Pencatatan Stok (Audit Persediaan Barang Fisik)
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