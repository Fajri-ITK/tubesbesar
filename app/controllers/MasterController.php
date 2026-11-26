<?php

require_once __DIR__ . '/../models/Store.php';

class MasterController
{
    private const PK = ['layanan' => 'id_layanan', 'barang' => 'id_barang'];

    private function valid($t): bool
    {
        return isset(self::PK[$t]);
    }

    public function index($t, ?string $error = null)
    {
        if (!$this->valid($t)) {
            redirect('index.php');
        }

        $rows = (new Store())->list($t);

        require __DIR__ . '/../views/' . $t . '/index.php';
    }

    /**
     * tampilkan form. $old (isian sebelumnya) dan $error dipake buat
     * validasi gagal agar pengguna tidak perlu mengetik ulang.
     */
    public function form($t, $id = null, ?array $old = null, ?string $error = null)
    {
        if (!$this->valid($t)) {
            redirect('index.php');
        }

        $store = new Store();
        $edit  = $id !== null && $id !== '';

        $existing = $edit ? $store->findMaster($t, (string) $id) : null;

        if ($edit && !$existing) {
            redirect('index.php?page=' . $t);
        }

        $record = $old ?? $existing ?? [self::PK[$t] => $store->suggestId($t)];

        require __DIR__ . '/../views/' . $t . '/form.php';
    }

    public function save($t)
    {
        if (!$this->valid($t)) {
            redirect('index.php');
        }

        $store = new Store();
        $oldId = post('old_id');
        $edit  = $oldId !== '';
        $pk    = self::PK[$t];

        $d = $t === 'layanan'
            ? [
                'nama_layanan' => post('nama_layanan'),
                'kategori'     => post('kategori'),
                'satuan'       => post('satuan'),
                'harga'        => post('harga'),
            ]
            : [
                'nama_barang'   => post('nama_barang'),
                'satuan'        => post('satuan'),
                'harga_jual'    => post('harga_jual'),
                'stok_tersedia' => post('stok_tersedia'),
            ];

        // pas edit, ID selalu dari old_id (field ID di form cuma readonly).
        $d[$pk] = $edit ? $oldId : strtoupper(post($pk));

        $error = $this->validate($t, $d, $edit);

        if (!$error && !$edit && $store->findMaster($t, $d[$pk])) {
            $error = 'ID ' . $d[$pk] . ' sudah dipakai. Gunakan ID lain.';
        }

        if (!$error) {
            try {
                $store->saveMaster($t, $d, $edit);
                redirect('index.php?page=' . $t . '&ok=simpan');
            } catch (DomainException $e) {
                $error = $e->getMessage();
            }
        }

        $this->form($t, $edit ? $oldId : null, $d, $error);
    }

    private function validate(string $t, array $d, bool $edit): ?string
    {
        $pk = self::PK[$t];

        if (!$edit && !preg_match('/^[A-Z0-9]{1,10}$/', $d[$pk])) {
            return 'ID hanya boleh huruf dan angka, maksimal 10 karakter.';
        }

        $name = $t === 'layanan' ? 'nama_layanan' : 'nama_barang';

        if ($d[$name] === '' || mb_strlen($d[$name]) > 100) {
            return 'Nama wajib diisi (maksimal 100 karakter).';
        }

        if ($d['satuan'] === '' || mb_strlen($d['satuan']) > 20) {
            return 'Satuan wajib diisi (maksimal 20 karakter).';
        }

        if ($t === 'layanan' && ($d['kategori'] === '' || mb_strlen($d['kategori']) > 30)) {
            return 'Kategori wajib diisi (maksimal 30 karakter).';
        }

        $price = $t === 'layanan' ? $d['harga'] : $d['harga_jual'];

        if (!is_numeric($price) || (float) $price < 0 || (float) $price > 9999999999) {
            return 'Harga harus berupa angka, minimal 0.';
        }

        if ($t === 'barang' && !$edit) {
            $stok = filter_var($d['stok_tersedia'], FILTER_VALIDATE_INT);

            if ($stok === false || $stok < 0) {
                return 'Stok awal harus bilangan bulat, minimal 0.';
            }
        }

        return null;
    }

    public function delete($t)
    {
        if (!$this->valid($t)) {
            redirect('index.php');
        }

        try {
            (new Store())->deleteMaster($t, post('id'));
        } catch (DomainException $e) {
            $this->index($t, $e->getMessage());

            return;
        }

        redirect('index.php?page=' . $t . '&ok=hapus');
    }
}
