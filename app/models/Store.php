<?php

require_once __DIR__ . '/BaseModel.php';

/**
 * Seluruh akses data untuk layanan, barang, pesanan, pembayaran, dan stok.
 *
 * Kesalahan yang disebabkan input pengguna dilempar sebagai DomainException
 * (pesannya aman ditampilkan). Kesalahan teknis tetap PDOException.
 */
class Store extends BaseModel
{
    public const STATUS_PESANAN    = ['Diproses', 'Selesai', 'Diambil'];
    public const STATUS_PEMBAYARAN = ['Belum Bayar', 'DP', 'Lunas'];
    public const METODE            = ['Tunai', 'QRIS'];
    public const JENIS_STOK        = ['Masuk', 'Keluar', 'Penyesuaian'];

    private const MASTER = [
        'layanan' => ['table' => 'layanan', 'pk' => 'id_layanan', 'name' => 'nama_layanan', 'prefix' => 'LYN', 'pad' => 2],
        'barang'  => ['table' => 'barang',  'pk' => 'id_barang',  'name' => 'nama_barang',  'prefix' => 'BRG', 'pad' => 2],
    ];

    /* ------------------------------------------------------------------
     * Daftar sederhana
     * ----------------------------------------------------------------*/

    public function list(string $type): array
    {
        $q = [
            'layanan' => 'SELECT * FROM layanan ORDER BY nama_layanan',

            'barang' => 'SELECT * FROM barang ORDER BY nama_barang',

            'pembayaran' => '
                SELECT b.*, p.nama_pelanggan
                FROM pembayaran b
                JOIN pesanan p ON p.id_pesanan = b.id_pesanan
                ORDER BY b.tanggal_bayar DESC, b.id_pembayaran DESC',

            'stok' => '
                SELECT s.*, b.nama_barang, b.satuan
                FROM pencatatan_stok s
                JOIN barang b ON b.id_barang = s.id_barang
                ORDER BY s.tanggal_pencatatan DESC, s.id_pencatatan DESC',
        ];

        if (!isset($q[$type])) {
            throw new InvalidArgumentException('Jenis daftar tidak dikenal: ' . $type);
        }

        return $this->rows($q[$type]);
    }

    /* ------------------------------------------------------------------
     * Master data: layanan & barang
     * ----------------------------------------------------------------*/

    public function findMaster(string $type, string $id): ?array
    {
        $m = self::MASTER[$type] ?? null;

        if ($m === null) {
            return null;
        }

        return $this->row(
            "SELECT * FROM {$m['table']} WHERE {$m['pk']} = ?",
            [$id]
        );
    }

    /** Usulan ID berikutnya, mis. LYN08 atau BRG07. */
    public function suggestId(string $type): string
    {
        $m = self::MASTER[$type];

        return $this->nextId($m['table'], $m['pk'], $m['prefix'], $m['pad']);
    }

    private function nextId(string $table, string $pk, string $prefix, int $pad): string
    {
        $start = strlen($prefix) + 1;

        $max = (int) $this->value(
            "SELECT MAX(CAST(SUBSTRING($pk, $start) AS UNSIGNED))
             FROM $table
             WHERE $pk REGEXP ?",
            ['^' . $prefix . '[0-9]+$'],
            0
        );

        return $prefix . str_pad((string) ($max + 1), $pad, '0', STR_PAD_LEFT);
    }

    /**
     * Simpan layanan / barang.
     * Stok barang TIDAK diubah saat edit; gunakan menu Manajemen Stok.
     * Saat barang baru dibuat dengan stok awal > 0, dicatat juga stok "Masuk"
     * agar riwayat stok tetap sinkron dengan stok_tersedia.
     */
    public function saveMaster(string $type, array $d, bool $edit): void
    {
        $this->transaction(function () use ($type, $d, $edit) {
            if ($type === 'layanan') {
                if ($edit) {
                    $this->run(
                        'UPDATE layanan
                         SET nama_layanan = ?, kategori = ?, satuan = ?, harga = ?
                         WHERE id_layanan = ?',
                        [$d['nama_layanan'], $d['kategori'], $d['satuan'], $d['harga'], $d['id_layanan']]
                    );
                } else {
                    $this->insertUnique(
                        'INSERT INTO layanan (id_layanan, nama_layanan, kategori, satuan, harga)
                         VALUES (?, ?, ?, ?, ?)',
                        [$d['id_layanan'], $d['nama_layanan'], $d['kategori'], $d['satuan'], $d['harga']],
                        $d['id_layanan']
                    );
                }

                return;
            }

            if ($edit) {
                $this->run(
                    'UPDATE barang
                     SET nama_barang = ?, satuan = ?, harga_jual = ?
                     WHERE id_barang = ?',
                    [$d['nama_barang'], $d['satuan'], $d['harga_jual'], $d['id_barang']]
                );

                return;
            }

            $stok = (int) $d['stok_tersedia'];

            $this->insertUnique(
                'INSERT INTO barang (id_barang, nama_barang, satuan, harga_jual, stok_tersedia)
                 VALUES (?, ?, ?, ?, ?)',
                [$d['id_barang'], $d['nama_barang'], $d['satuan'], $d['harga_jual'], $stok],
                $d['id_barang']
            );

            if ($stok > 0) {
                $this->run(
                    "INSERT INTO pencatatan_stok
                        (id_barang, tanggal_pencatatan, jenis_transaksi, jumlah, keterangan)
                     VALUES (?, ?, 'Masuk', ?, 'Stok awal')",
                    [$d['id_barang'], date('Y-m-d'), $stok]
                );
            }
        });
    }

    private function insertUnique(string $sql, array $params, string $id): void
    {
        try {
            $this->run($sql, $params);
        } catch (PDOException $e) {
            if (($e->errorInfo[1] ?? 0) === 1062) {
                throw new DomainException("ID $id sudah dipakai. Gunakan ID lain.");
            }

            throw $e;
        }
    }

    public function deleteMaster(string $type, string $id): void
    {
        $m = self::MASTER[$type];

        $record = $this->findMaster($type, $id);

        if (!$record) {
            throw new DomainException('Data tidak ditemukan.');
        }

        try {
            $this->run("DELETE FROM {$m['table']} WHERE {$m['pk']} = ?", [$id]);
        } catch (PDOException $e) {
            // 1451 = baris masih direferensikan tabel lain (foreign key)
            if (($e->errorInfo[1] ?? 0) === 1451) {
                $name = $record[$m['name']];

                throw new DomainException(
                    $type === 'layanan'
                        ? "Layanan \"$name\" tidak bisa dihapus karena sudah dipakai pada pesanan."
                        : "Barang \"$name\" tidak bisa dihapus karena sudah dipakai pada pesanan atau riwayat stok."
                );
            }

            throw $e;
        }
    }

    /** Pilihan item untuk form pesanan. */
    public function options(): array
    {
        return [
            'services' => $this->rows('SELECT * FROM layanan ORDER BY nama_layanan'),
            'products' => $this->rows('SELECT * FROM barang ORDER BY nama_barang'),
        ];
    }

    /* ------------------------------------------------------------------
     * Pesanan
     * ----------------------------------------------------------------*/

    /** Daftar pesanan + filter opsional: q (teks), status, bayar. */
    public function orders(array $f = []): array
    {
        $where  = [];
        $params = [];

        $q = trim((string) ($f['q'] ?? ''));

        if ($q !== '') {
            $like = '%' . $q . '%';
            $where[] = '(p.id_pesanan LIKE ? OR p.nama_pelanggan LIKE ? OR p.no_whatsapp LIKE ?)';
            array_push($params, $like, $like, $like);
        }

        if (in_array($f['status'] ?? '', self::STATUS_PESANAN, true)) {
            $where[]  = 'p.status_pesanan = ?';
            $params[] = $f['status'];
        }

        if (in_array($f['bayar'] ?? '', self::STATUS_PEMBAYARAN, true)) {
            $where[]  = 'p.status_pembayaran = ?';
            $params[] = $f['bayar'];
        }

        $sql = self::ORDERS_SQL
            . ($where ? ' WHERE ' . implode(' AND ', $where) : '')
            . ' ORDER BY p.tanggal_pesanan DESC, p.id_pesanan DESC';

        return $this->rows($sql, $params);
    }

    public function findOrder(string $id): ?array
    {
        return $this->row(self::ORDERS_SQL . ' WHERE p.id_pesanan = ?', [$id]);
    }

    /** Pesanan yang masih punya sisa tagihan (untuk form pembayaran). */
    public function unpaidOrders(): array
    {
        return $this->rows(
            'SELECT * FROM (' . self::ORDERS_SQL . ') x
             WHERE x.sisa > 0
             ORDER BY x.tanggal_pesanan, x.id_pesanan'
        );
    }

    public function details(string $id): array
    {
        return $this->rows(
            'SELECT d.*, l.nama_layanan, l.satuan AS satuan_layanan,
                    b.nama_barang, b.satuan AS satuan_barang
             FROM detail_pesanan d
             LEFT JOIN layanan l ON l.id_layanan = d.id_layanan
             LEFT JOIN barang  b ON b.id_barang  = d.id_barang
             WHERE d.id_pesanan = ?
             ORDER BY d.id_detail',
            [$id]
        );
    }

    public function payments(string $id): array
    {
        return $this->rows(
            'SELECT * FROM pembayaran
             WHERE id_pesanan = ?
             ORDER BY tanggal_bayar, id_pembayaran',
            [$id]
        );
    }

    /**
     * Buat pesanan baru. Mengembalikan ID pesanan yang dibuat.
     * Harga selalu diambil dari database (bukan dari input form).
     * Item barang mengurangi stok dan mencatat stok "Keluar" dalam transaksi yang sama.
     */
    public function createOrder(array $d, array $items): string
    {
        $nama = trim($d['nama_pelanggan'] ?? '');
        $wa   = trim($d['no_whatsapp'] ?? '');
        $ket  = trim($d['keterangan'] ?? '');
        $tgl  = $d['tanggal_pesanan'] ?? '';

        if ($nama === '' || mb_strlen($nama) > 100) {
            throw new DomainException('Nama pelanggan wajib diisi (maksimal 100 karakter).');
        }

        if ($wa !== '' && !preg_match('/^\+?[0-9\s\-]{6,20}$/', $wa)) {
            throw new DomainException('Nomor WhatsApp hanya boleh berisi angka (6-20 digit).');
        }

        if (!validDate($tgl)) {
            throw new DomainException('Tanggal pesanan tidak valid.');
        }

        if (mb_strlen($ket) > 255) {
            throw new DomainException('Keterangan maksimal 255 karakter.');
        }

        if (!$items) {
            throw new DomainException('Tambahkan minimal satu item pesanan.');
        }

        return $this->transaction(function () use ($nama, $wa, $ket, $tgl, $items) {
            $id = $this->nextId('pesanan', 'id_pesanan', 'PSN', 3);

            $this->run(
                "INSERT INTO pesanan
                    (id_pesanan, nama_pelanggan, no_whatsapp, tanggal_pesanan, keterangan, status_pesanan)
                 VALUES (?, ?, ?, ?, ?, 'Diproses')",
                [$id, $nama, $wa ?: null, $tgl, $ket ?: null]
            );

            foreach ($items as $n => $it) {
                $no   = $n + 1;
                $kind = $it['kind'] ?? '';
                $ref  = (string) ($it['id'] ?? '');
                $qty  = filter_var($it['qty'] ?? null, FILTER_VALIDATE_INT);

                if ($qty === false || $qty < 1) {
                    throw new DomainException("Item #$no: jumlah harus bilangan bulat lebih dari nol.");
                }

                if ($kind === 'layanan') {
                    $r = $this->row('SELECT harga FROM layanan WHERE id_layanan = ?', [$ref]);

                    if (!$r) {
                        throw new DomainException("Item #$no: layanan tidak ditemukan.");
                    }

                    $this->run(
                        'INSERT INTO detail_pesanan (id_pesanan, id_layanan, id_barang, jumlah, harga_satuan)
                         VALUES (?, ?, NULL, ?, ?)',
                        [$id, $ref, $qty, $r['harga']]
                    );

                    continue;
                }

                if ($kind !== 'barang') {
                    throw new DomainException("Item #$no: jenis item tidak valid.");
                }

                $r = $this->row(
                    'SELECT nama_barang, harga_jual, stok_tersedia
                     FROM barang WHERE id_barang = ? FOR UPDATE',
                    [$ref]
                );

                if (!$r) {
                    throw new DomainException("Item #$no: barang tidak ditemukan.");
                }

                if ((int) $r['stok_tersedia'] < $qty) {
                    throw new DomainException(
                        "Stok {$r['nama_barang']} tidak mencukupi (tersisa {$r['stok_tersedia']})."
                    );
                }

                $this->run(
                    'INSERT INTO detail_pesanan (id_pesanan, id_layanan, id_barang, jumlah, harga_satuan)
                     VALUES (?, NULL, ?, ?, ?)',
                    [$id, $ref, $qty, $r['harga_jual']]
                );

                $this->run(
                    'UPDATE barang SET stok_tersedia = stok_tersedia - ? WHERE id_barang = ?',
                    [$qty, $ref]
                );

                $this->run(
                    "INSERT INTO pencatatan_stok
                        (id_barang, tanggal_pencatatan, jenis_transaksi, jumlah, keterangan)
                     VALUES (?, ?, 'Keluar', ?, ?)",
                    [$ref, $tgl, $qty, 'Penjualan pesanan ' . $id]
                );
            }

            return $id;
        });
    }

    public function updateOrderStatus(string $id, string $status): void
    {
        if (!in_array($status, self::STATUS_PESANAN, true)) {
            throw new DomainException('Status pesanan tidak valid.');
        }

        if (!$this->findOrder($id)) {
            throw new DomainException('Pesanan tidak ditemukan.');
        }

        $this->run(
            'UPDATE pesanan SET status_pesanan = ? WHERE id_pesanan = ?',
            [$status, $id]
        );
    }

// =====================================================
// pembayaran
// =====================================================

    public function addPayment(array $d): void
    {
        $id     = (string) ($d['id_pesanan'] ?? '');
        $tgl    = (string) ($d['tanggal_bayar'] ?? '');
        $metode = (string) ($d['metode_pembayaran'] ?? '');
        $raw    = $d['jumlah_bayar'] ?? '';

        if (!validDate($tgl)) {
            throw new DomainException('Tanggal pembayaran tidak valid.');
        }

        if (!in_array($metode, self::METODE, true)) {
            throw new DomainException('Metode pembayaran tidak valid.');
        }

        if (!is_numeric($raw) || (float) $raw <= 0) {
            throw new DomainException('Jumlah pembayaran harus lebih dari nol.');
        }

        // dijadikan integer biar gak ada pembulatan desimal (who knows?)
        $amount = (int) round((float) $raw * 100);

        $this->transaction(function () use ($id, $tgl, $metode, $amount) {
            $order = $this->row(
                'SELECT id_pesanan FROM pesanan WHERE id_pesanan = ? FOR UPDATE',
                [$id]
            );

            if (!$order) {
                throw new DomainException('Pesanan tidak ditemukan.');
            }

            $total = (int) round((float) $this->value(
                'SELECT COALESCE(SUM(jumlah * harga_satuan), 0)
                 FROM detail_pesanan WHERE id_pesanan = ?',
                [$id],
                0
            ) * 100);

            $paid = (int) round((float) $this->value(
                'SELECT COALESCE(SUM(jumlah_bayar), 0)
                 FROM pembayaran WHERE id_pesanan = ?',
                [$id],
                0
            ) * 100);

            $sisa = $total - $paid;

            if ($sisa <= 0) {
                throw new DomainException("Pesanan $id sudah lunas.");
            }

            if ($amount > $sisa) {
                throw new DomainException(
                    'Jumlah pembayaran melebihi sisa tagihan (' . rp($sisa / 100) . ').'
                );
            }

            $this->run(
                'INSERT INTO pembayaran (id_pesanan, tanggal_bayar, metode_pembayaran, jumlah_bayar)
                 VALUES (?, ?, ?, ?)',
                [$id, $tgl, $metode, number_format($amount / 100, 2, '.', '')]
            );

            $newPaid = $paid + $amount;

            $status = $newPaid >= $total ? 'Lunas' : 'DP';

            $this->run(
                'UPDATE pesanan SET status_pembayaran = ? WHERE id_pesanan = ?',
                [$status, $id]
            );
        });
    }

// =====================================================
// stok
// =====================================================

    /**
     * aku sering lupa disini
     *  - masuk / keluar  : jumlah = banyaknya barang yang masuk / keluar
     *  - penyesuaian     : jumlah = stok opname
     *                      selisih terhadap stok sistem dicatat jadi jumlah
     *                      arah perubahan ditulis di keterangan
     */
    public function addStock(array $d): void
    {
        $id    = (string) ($d['id_barang'] ?? '');
        $tgl   = (string) ($d['tanggal_pencatatan'] ?? '');
        $jenis = (string) ($d['jenis_transaksi'] ?? '');
        $ket   = trim((string) ($d['keterangan'] ?? ''));
        $qty   = filter_var($d['jumlah'] ?? null, FILTER_VALIDATE_INT);

        if (!validDate($tgl)) {
            throw new DomainException('Tanggal pencatatan tidak valid.');
        }

        if (!in_array($jenis, self::JENIS_STOK, true)) {
            throw new DomainException('Jenis transaksi stok tidak valid.');
        }

        $min = $jenis === 'Penyesuaian' ? 0 : 1;

        if ($qty === false || $qty < $min) {
            throw new DomainException(
                $jenis === 'Penyesuaian'
                    ? 'Stok fisik harus bilangan bulat, minimal 0.'
                    : 'Jumlah harus bilangan bulat lebih dari nol.'
            );
        }

        $this->transaction(function () use ($id, $tgl, $jenis, $ket, $qty) {
            $b = $this->row(
                'SELECT nama_barang, stok_tersedia FROM barang WHERE id_barang = ? FOR UPDATE',
                [$id]
            );

            if (!$b) {
                throw new DomainException('Barang tidak ditemukan.');
            }

            $cur = (int) $b['stok_tersedia'];

            if ($jenis === 'Masuk') {
                $new = $cur + $qty;
                $log = $qty;
            } elseif ($jenis === 'Keluar') {
                if ($qty > $cur) {
                    throw new DomainException(
                        "Stok {$b['nama_barang']} tidak mencukupi (tersisa $cur)."
                    );
                }

                $new = $cur - $qty;
                $log = $qty;
            } else {
                $new = $qty;
                $log = abs($new - $cur);

                if ($log === 0) {
                    throw new DomainException(
                        "Stok fisik sama dengan stok sistem ($cur); tidak ada yang perlu disesuaikan."
                    );
                }

                $ket = "[Opname $cur -> $new] " . $ket;
            }

            $this->run(
                'UPDATE barang SET stok_tersedia = ? WHERE id_barang = ?',
                [$new, $id]
            );

            $this->run(
                'INSERT INTO pencatatan_stok
                    (id_barang, tanggal_pencatatan, jenis_transaksi, jumlah, keterangan)
                 VALUES (?, ?, ?, ?, ?)',
                [$id, $tgl, $jenis, $log, mb_substr(trim($ket), 0, 255) ?: null]
            );
        });
    }
}
