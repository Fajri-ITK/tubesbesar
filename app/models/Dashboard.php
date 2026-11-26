<?php

require_once __DIR__ . '/BaseModel.php';

class Dashboard extends BaseModel
{
    public function data(): array
    {

        $from = date('Y-m-01');
        $to   = date('Y-m-t');

        $unpaid = $this->row(
            'SELECT COUNT(*) AS n, COALESCE(SUM(sisa), 0) AS sisa
             FROM (' . self::ORDERS_SQL . ') x
             WHERE x.sisa > 0'
        );

        return [
            'orders' => (int) $this->value('SELECT COUNT(*) FROM pesanan', [], 0),

            'revenue' => (float) $this->value(
                'SELECT COALESCE(SUM(jumlah_bayar), 0)
                 FROM pembayaran
                 WHERE tanggal_bayar BETWEEN ? AND ?',
                [$from, $to],
                0
            ),

            'processing' => (int) $this->value(
                "SELECT COUNT(*) FROM pesanan WHERE status_pesanan = 'Diproses'",
                [],
                0
            ),

            'unpaid_count' => (int) $unpaid['n'],
            'unpaid_sum'   => (float) $unpaid['sisa'],

            'low' => (int) $this->value(
                'SELECT COUNT(*) FROM barang WHERE stok_tersedia <= ?',
                [LOW_STOCK_LIMIT],
                0
            ),

            'latest' => $this->rows(
                self::ORDERS_SQL . '
                 ORDER BY p.tanggal_pesanan DESC, p.id_pesanan DESC
                 LIMIT 7'
            ),

            'lowitems' => $this->rows(
                'SELECT * FROM barang
                 WHERE stok_tersedia <= ?
                 ORDER BY stok_tersedia, nama_barang
                 LIMIT 6',
                [LOW_STOCK_LIMIT]
            ),
        ];
    }
}
