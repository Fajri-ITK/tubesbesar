<?php

require __DIR__ . '/config/app.php';
require __DIR__ . '/app/helpers.php';

$page   = $_GET['page'] ?? 'dashboard';
$action = $_GET['action'] ?? 'index';
$isPost = $_SERVER['REQUEST_METHOD'] === 'POST';

try {
    switch ($page) {
        case 'dashboard':
            require 'app/controllers/DashboardController.php';

            (new DashboardController())->index();
            break;

        case 'pesanan':
            require 'app/controllers/PesananController.php';

            $c = new PesananController();

            if ($action === 'create') {
                $c->create();
            } elseif ($action === 'store' && $isPost) {
                $c->store();
            } elseif ($action === 'detail') {
                $c->detail();
            } elseif ($action === 'status' && $isPost) {
                $c->status();
            } else {
                $c->index();
            }
            break;

        case 'layanan':
        case 'barang':
            require 'app/controllers/MasterController.php';

            $c = new MasterController();

            if ($action === 'create') {
                $c->form($page);
            } elseif ($action === 'edit') {
                $c->form($page, $_GET['id'] ?? '');
            } elseif ($action === 'store' && $isPost) {
                $c->save($page);
            } elseif ($action === 'delete' && $isPost) {
                $c->delete($page);
            } else {
                $c->index($page);
            }
            break;

        case 'pembayaran':
            require 'app/controllers/PaymentController.php';

            $c = new PaymentController();

            if ($action === 'store' && $isPost) {
                $c->store();
            } else {
                $c->index();
            }
            break;

        case 'stok':
            require 'app/controllers/StockController.php';

            $c = new StockController();

            if ($action === 'store' && $isPost) {
                $c->store();
            } else {
                $c->index();
            }
            break;

        case 'laporan':
            require 'app/controllers/ReportController.php';

            (new ReportController())->index();
            break;

        default:
            redirect('index.php');
    }
} catch (Throwable $e) {
    http_response_code(500);

    echo '<h2>Aplikasi mengalami kesalahan</h2>';

    $msg = $e instanceof RuntimeException
        ? $e->getMessage()
        : 'Terjadi kesalahan saat memproses permintaan.';

    echo '<p>' . e($msg) . '</p>';

    if (APP_DEBUG) {
        $real = $e->getPrevious() ?? $e;

        echo '<p><strong>Detail:</strong> ' . e($real->getMessage()) . '</p>';
        echo '<p><strong>File:</strong> ' . e($real->getFile()) . ':' . (int) $real->getLine() . '</p>';
    }
}
