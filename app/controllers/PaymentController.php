<?php

require_once __DIR__ . '/../models/Store.php';

class PaymentController
{
    public function index(?string $error = null, array $old = [])
    {
        $m = new Store();

        $rows   = $m->list('pembayaran');
        $orders = $m->unpaidOrders();

        // dari tombol "Catat pembayaran" di halaman detail pesanan.
        $selected = $old['id_pesanan'] ?? (string) ($_GET['id'] ?? '');

        require __DIR__ . '/../views/pembayaran/index.php';
    }

    public function store()
    {
        $data = [
            'id_pesanan'        => post('id_pesanan'),
            'tanggal_bayar'     => post('tanggal_bayar'),
            'metode_pembayaran' => post('metode_pembayaran'),
            'jumlah_bayar'      => post('jumlah_bayar'),
        ];

        try {
            (new Store())->addPayment($data);
        } catch (DomainException $e) {
            $this->index($e->getMessage(), $data);

            return;
        }

        redirect('index.php?page=pembayaran&ok=bayar');
    }
}
