<?php

require_once __DIR__ . '/../models/Store.php';

class StockController
{
    public function index(?string $error = null, array $old = [])
    {
        $m = new Store();

        $rows     = $m->list('stok');
        $products = $m->list('barang');

        require __DIR__ . '/../views/stok/index.php';
    }

    public function store()
    {
        $data = [
            'id_barang'          => post('id_barang'),
            'tanggal_pencatatan' => post('tanggal_pencatatan'),
            'jenis_transaksi'    => post('jenis_transaksi'),
            'jumlah'             => post('jumlah'),
            'keterangan'         => post('keterangan'),
        ];

        try {
            (new Store())->addStock($data);
        } catch (DomainException $e) {
            $this->index($e->getMessage(), $data);

            return;
        }

        redirect('index.php?page=stok&ok=stok');
    }
}
