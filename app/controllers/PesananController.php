<?php

require_once __DIR__ . '/../models/Store.php';

class PesananController
{
    public function index()
    {
        $filter = [
            'q'      => trim((string) ($_GET['q'] ?? '')),
            'status' => (string) ($_GET['status'] ?? ''),
            'bayar'  => (string) ($_GET['bayar'] ?? ''),
        ];

        $rows = (new Store())->orders($filter);

        require __DIR__ . '/../views/pesanan/index.php';
    }

    /** $old / $oldItems / $error dipakai buat  ngisi ulang form klo gagal simpan. */
    public function create(?string $error = null, array $old = [], array $oldItems = [])
    {
        $opts = (new Store())->options();

        $services = $opts['services'];
        $products = $opts['products'];

        require __DIR__ . '/../views/pesanan/create.php';
    }

    public function store()
    {
        $items = [];

        foreach ((array) ($_POST['kind'] ?? []) as $i => $kind) {
            $items[] = [
                'kind' => (string) $kind,
                'id'   => (string) ($_POST['item_id'][$i] ?? ''),
                'qty'  => $_POST['qty'][$i] ?? '',
            ];
        }

        $data = [
            'nama_pelanggan'  => post('nama_pelanggan'),
            'no_whatsapp'     => post('no_whatsapp'),
            'tanggal_pesanan' => post('tanggal_pesanan'),
            'keterangan'      => post('keterangan'),
        ];

        try {
            $id = (new Store())->createOrder($data, $items);
        } catch (DomainException $e) {
            $this->create($e->getMessage(), $data, $items);

            return;
        }

        redirect('index.php?page=pesanan&action=detail&id=' . urlencode($id) . '&ok=pesanan');
    }

    public function detail(?string $error = null)
    {
        $id = (string) ($_GET['id'] ?? '');

        $m     = new Store();
        $order = $m->findOrder($id);

        if (!$order) {
            redirect('index.php?page=pesanan');
        }

        $items    = $m->details($id);
        $payments = $m->payments($id);

        $total = (float) $order['total'];
        $paid  = (float) $order['dibayar'];

        require __DIR__ . '/../views/pesanan/detail.php';
    }

    public function status()
    {
        $id = post('id_pesanan');

        try {
            (new Store())->updateOrderStatus($id, post('status_pesanan'));
        } catch (DomainException $e) {
            $_GET['id'] = $id;
            $this->detail($e->getMessage());

            return;
        }

        redirect('index.php?page=pesanan&action=detail&id=' . urlencode($id) . '&ok=status');
    }
}
