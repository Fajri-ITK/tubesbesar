<?php

require_once __DIR__ . '/BaseModel.php';
require_once __DIR__ . '/Store.php';

class Report extends BaseModel
{
    /** Kondisi tanggal "kolom >= dari AND kolom <= sampai" (batas boleh kosong). */
    private function between(string $col, ?string $from, ?string $to): array
    {
        $w = ['1 = 1'];
        $p = [];

        if ($from) {
            $w[] = "$col >= ?";
            $p[] = $from;
        }

        if ($to) {
            $w[] = "$col <= ?";
            $p[] = $to;
        }

        return [implode(' AND ', $w), $p];
    }

    /** Lengkapi baris hasil GROUP BY agar semua kategori enum selalu tampil. */
    private function fill(array $rows, string $key, array $all): array
    {
        $by = [];

        foreach ($rows as $r) {
            $by[$r[$key]] = $r;
        }

        $out = [];

        foreach ($all as $name) {
            $out[] = $by[$name] ?? [$key => $name, 'n' => 0, 'total' => 0];
        }

        return $out;
    }

    public function build(?string $from, ?string $to): array
    {
        [$wOrder, $pOrder] = $this->between('x.tanggal_pesanan', $from, $to);
        [$wPay, $pPay]     = $this->between('tanggal_bayar', $from, $to);
        [$wItem, $pItem]   = $this->between('p.tanggal_pesanan', $from, $to);
        [$wStock, $pStock] = $this->between('s.tanggal_pencatatan', $from, $to);

        $orders = '(' . self::ORDERS_SQL . ') x';

        $sum = $this->row(
            "SELECT COUNT(*) AS pesanan,
                    COALESCE(SUM(x.total), 0) AS tagihan,
                    COALESCE(SUM(GREATEST(x.sisa, 0)), 0) AS piutang
             FROM $orders WHERE $wOrder",
            $pOrder
        );

        $sum['diterima'] = (float) $this->value(
            "SELECT COALESCE(SUM(jumlah_bayar), 0) FROM pembayaran WHERE $wPay",
            $pPay,
            0
        );

        return [
            'sum' => $sum,

            'metode' => $this->fill(
                $this->rows(
                    "SELECT metode_pembayaran AS metode, COUNT(*) AS n, SUM(jumlah_bayar) AS total
                     FROM pembayaran WHERE $wPay
                     GROUP BY metode_pembayaran",
                    $pPay
                ),
                'metode',
                Store::METODE
            ),

            'status' => $this->fill(
                $this->rows(
                    "SELECT x.status_pesanan AS status, COUNT(*) AS n, SUM(x.total) AS total
                     FROM $orders WHERE $wOrder
                     GROUP BY x.status_pesanan",
                    $pOrder
                ),
                'status',
                Store::STATUS_PESANAN
            ),

            'bayar' => $this->fill(
                $this->rows(
                    "SELECT x.status_pembayaran AS status, COUNT(*) AS n, SUM(x.total) AS total
                     FROM $orders WHERE $wOrder
                     GROUP BY x.status_pembayaran",
                    $pOrder
                ),
                'status',
                Store::STATUS_PEMBAYARAN
            ),

            'services' => $this->rows(
                "SELECT l.nama_layanan AS nama, l.satuan,
                        SUM(d.jumlah) AS qty, SUM(d.jumlah * d.harga_satuan) AS omzet
                 FROM detail_pesanan d
                 JOIN pesanan p ON p.id_pesanan = d.id_pesanan
                 JOIN layanan l ON l.id_layanan = d.id_layanan
                 WHERE $wItem
                 GROUP BY l.id_layanan, l.nama_layanan, l.satuan
                 ORDER BY omzet DESC, nama",
                $pItem
            ),

            'products' => $this->rows(
                "SELECT b.nama_barang AS nama, b.satuan,
                        SUM(d.jumlah) AS qty, SUM(d.jumlah * d.harga_satuan) AS omzet
                 FROM detail_pesanan d
                 JOIN pesanan p ON p.id_pesanan = d.id_pesanan
                 JOIN barang b ON b.id_barang = d.id_barang
                 WHERE $wItem
                 GROUP BY b.id_barang, b.nama_barang, b.satuan
                 ORDER BY omzet DESC, nama",
                $pItem
            ),

            'stock' => $this->rows(
                "SELECT b.id_barang, b.nama_barang, b.satuan, b.stok_tersedia,
                        COALESCE(SUM(CASE WHEN s.jenis_transaksi = 'Masuk'       THEN s.jumlah END), 0) AS masuk,
                        COALESCE(SUM(CASE WHEN s.jenis_transaksi = 'Keluar'      THEN s.jumlah END), 0) AS keluar,
                        COALESCE(SUM(CASE WHEN s.jenis_transaksi = 'Penyesuaian' THEN s.jumlah END), 0) AS penyesuaian
                 FROM barang b
                 LEFT JOIN pencatatan_stok s
                        ON s.id_barang = b.id_barang AND $wStock
                 GROUP BY b.id_barang, b.nama_barang, b.satuan, b.stok_tersedia
                 ORDER BY b.nama_barang",
                $pStock
            ),

            // Piutang berjalan: tidak dibatasi periode, karena tagihan yang
            // belum lunas tetap perlu ditagih kapan pun pesanannya dibuat.
            'unpaid' => $this->rows(
                "SELECT * FROM $orders WHERE x.sisa > 0
                 ORDER BY x.tanggal_pesanan, x.id_pesanan"
            ),
        ];
    }
}
