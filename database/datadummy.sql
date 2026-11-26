SET NAMES utf8mb4;
USE db_fotokopi_samboja;

-- Kosongkan data lama (urutan anak -> induk) agar file ini aman dijalankan ulang.
SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE pembayaran;
TRUNCATE TABLE detail_pesanan;
TRUNCATE TABLE pencatatan_stok;
TRUNCATE TABLE pesanan;
TRUNCATE TABLE barang;
TRUNCATE TABLE layanan;
SET FOREIGN_KEY_CHECKS = 1;


-- 1. Data Master Katalog Layanan (Jasa)
INSERT INTO layanan (id_layanan, nama_layanan, kategori, satuan, harga) VALUES
('LYN01', 'Fotokopi Hitam Putih A4', 'Jasa', 'Lembar', 500.00),
('LYN02', 'Fotokopi Hitam Putih F4', 'Jasa', 'Lembar', 600.00),
('LYN03', 'Print Dokumen Warna A4', 'Jasa', 'Lembar', 1500.00),
('LYN04', 'Jilid Lakban', 'Jasa', 'Buku', 5000.00),
('LYN05', 'Jilid Spiral Kawat', 'Jasa', 'Buku', 15000.00),
('LYN06', 'Laminating A4', 'Jasa', 'Lembar', 4000.00),
('LYN07', 'Scan Dokumen', 'Jasa', 'Lembar', 2000.00);

-- 2. Data Master Katalog Barang (ATK)
-- Catatan: stok_tersedia diselaraskan dengan riwayat pencatatan_stok di bawah
INSERT INTO barang (id_barang, nama_barang, satuan, harga_jual, stok_tersedia) VALUES
('BRG01', 'Pulpen Hitam Standard AE7', 'Pcs', 3000.00, 45),
('BRG02', 'Kertas HVS A4 70gsm Sidu', 'Rim', 52000.00, 18),
('BRG03', 'Buku Tulis Folio 100 Lembar', 'Buku', 14000.00, 22),
('BRG04', 'Map Kertas Snelhecter', 'Pcs', 2500.00, 60),
('BRG05', 'Penghapus Karet Putih', 'Pcs', 2000.00, 35),
('BRG06', 'Penggaris Plastik 30 cm', 'Pcs', 4500.00, 15);

-- 3. Data Pencatatan Stok (Audit Persediaan Barang Fisik)
INSERT INTO pencatatan_stok (id_barang, tanggal_pencatatan, jenis_transaksi, jumlah, keterangan) VALUES
('BRG01', '2026-11-01', 'Masuk', 50, 'Kulakan awal bulan pulpen'),
('BRG02', '2026-11-01', 'Masuk', 20, 'Stok awal kertas HVS A4'),
('BRG03', '2026-11-02', 'Masuk', 25, 'Kulakan buku tulis folio'),
('BRG04', '2026-11-02', 'Masuk', 65, 'Kulakan map snelhecter'),
('BRG05', '2026-11-03', 'Masuk', 40, 'Kulakan penghapus'),
('BRG06', '2026-11-03', 'Masuk', 15, 'Kulakan penggaris'),
('BRG01', '2026-11-10', 'Keluar', 5, 'Terjual lewat pesanan'),
('BRG02', '2026-11-12', 'Keluar', 2, 'Penjualan langsung (tanpa pesanan)'),
('BRG03', '2026-11-14', 'Keluar', 3, 'Terjual lewat pesanan'),
('BRG04', '2026-11-18', 'Keluar', 5, 'Terjual lewat pesanan'),
('BRG05', '2026-11-20', 'Penyesuaian', 5, '[Opname 40 -> 35] Penyesuaian stok opname / rusak');

-- 4. Data Pesanan (Transaksi Utama)
INSERT INTO pesanan (id_pesanan, nama_pelanggan, no_whatsapp, tanggal_pesanan, keterangan, status_pesanan, status_pembayaran) VALUES
('PSN001', 'Rina Wulandari', '081254001122', '2026-11-10', 'Fotokopi materi kuliah & beli pulpen', 'Diambil', 'Lunas'),
('PSN002', 'Budi Santoso', '081347889900', '2026-11-12', 'Print proposal dan jilid lakban 2 rangkap', 'Selesai', 'Lunas'),
('PSN003', 'Siti Aminah', '085246112233', '2026-11-14', 'Fotokopi berkas administrasi & buku tulis', 'Diambil', 'Lunas'),
('PSN004', 'Ahmad Fauzi', '082153778811', '2026-11-18', 'Jilid spiral kawat laporan PKL + map', 'Diproses', 'DP'),
('PSN005', 'Dewi Lestari', NULL, '2026-11-22', 'Laminating kartu & scan dokumen', 'Selesai', 'Belum Bayar'),
('PSN006', 'Hendra Wijaya', '081154990011', '2026-11-24', 'Print warna dan fotokopi F4', 'Diproses', 'Lunas');

-- 5. Data Detail Pesanan (Hanya id_layanan ATAU id_barang per baris)
INSERT INTO detail_pesanan (id_pesanan, id_layanan, id_barang, jumlah, harga_satuan) VALUES
-- PSN001: 120 lbr fotokopi A4 (60.000) + 5 pulpen (15.000) = 75.000
('PSN001', 'LYN01', NULL, 120, 500.00),
('PSN001', NULL, 'BRG01', 5, 3000.00),
-- PSN002: 40 lbr print warna A4 (60.000) + 2 jilid lakban (10.000) = 70.000
('PSN002', 'LYN03', NULL, 40, 1500.00),
('PSN002', 'LYN04', NULL, 2, 5000.00),
-- PSN003: 80 lbr fotokopi A4 (40.000) + 3 buku folio (42.000) = 82.000
('PSN003', 'LYN01', NULL, 80, 500.00),
('PSN003', NULL, 'BRG03', 3, 14000.00),
-- PSN004: 3 jilid spiral (45.000) + 5 map snelhecter (12.500) = 57.500
('PSN004', 'LYN05', NULL, 3, 15000.00),
('PSN004', NULL, 'BRG04', 5, 2500.00),
-- PSN005: 5 laminating A4 (20.000) + 10 scan dokumen (20.000) = 40.000
('PSN005', 'LYN06', NULL, 5, 4000.00),
('PSN005', 'LYN07', NULL, 10, 2000.00),
-- PSN006: 20 lbr print warna A4 (30.000) + 100 lbr fotokopi F4 (60.000) = 90.000
('PSN006', 'LYN03', NULL, 20, 1500.00),
('PSN006', 'LYN02', NULL, 100, 600.00);

-- 6. Data Pembayaran
INSERT INTO pembayaran (id_pesanan, tanggal_bayar, metode_pembayaran, jumlah_bayar) VALUES
('PSN001', '2026-11-10', 'Tunai', 75000.00),
('PSN002', '2026-11-12', 'QRIS', 70000.00),
('PSN003', '2026-11-14', 'Tunai', 82000.00),
('PSN004', '2026-11-18', 'QRIS', 30000.00), -- DP dari total 57.500
('PSN006', '2026-11-24', 'QRIS', 90000.00);
-- Catatan: PSN005 sengaja tidak punya baris pembayaran karena status_pembayaran = 'Belum Bayar'