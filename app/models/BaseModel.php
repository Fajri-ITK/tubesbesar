<?php

require_once __DIR__ . '/../core/Database.php';

class BaseModel
{
    protected PDO $db;

    /**
     * detail pesanan dengan total tagihan, jumlah dibayar, dan sisa tagihan
     * bisa aja sih tambahkan WHERE / ORDER BY di belakangnya, atau bungkus sebagai subquery
     */
    protected const ORDERS_SQL = "
        SELECT p.*,
               COALESCE(d.total, 0)                      AS total,
               COALESCE(b.dibayar, 0)                    AS dibayar,
               COALESCE(d.total, 0) - COALESCE(b.dibayar, 0) AS sisa
        FROM pesanan p
        LEFT JOIN (
            SELECT id_pesanan, SUM(jumlah * harga_satuan) AS total
            FROM detail_pesanan
            GROUP BY id_pesanan
        ) d ON d.id_pesanan = p.id_pesanan
        LEFT JOIN (
            SELECT id_pesanan, SUM(jumlah_bayar) AS dibayar
            FROM pembayaran
            GROUP BY id_pesanan
        ) b ON b.id_pesanan = p.id_pesanan";

    public function __construct()
    {
        $this->db = Database::connection();
    }

    protected function run(string $sql, array $params = []): PDOStatement
    {
        $s = $this->db->prepare($sql);
        $s->execute($params);

        return $s;
    }

    protected function rows(string $sql, array $params = []): array
    {
        return $this->run($sql, $params)->fetchAll();
    }

    protected function row(string $sql, array $params = []): ?array
    {
        $r = $this->run($sql, $params)->fetch();

        return $r === false ? null : $r;
    }

    /** ambil satu nilai (kolom pertama baris pertama) */
    protected function value(string $sql, array $params = [], $default = null)
    {
        $v = $this->run($sql, $params)->fetchColumn();

        return $v === false ? $default : $v;
    }

    /** jalannin $fn dalam transaksi; rollback otomatis bila ada exception. */
    protected function transaction(callable $fn)
    {
        $this->db->beginTransaction();

        try {
            $result = $fn();
            $this->db->commit();

            return $result;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            throw $e;
        }
    }
}
