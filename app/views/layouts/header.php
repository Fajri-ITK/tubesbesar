<?php

$page = $_GET['page'] ?? 'dashboard';

$titles = [
    'dashboard' => 'Dashboard',
    'pesanan' => 'Pesanan',
    'layanan' => 'Katalog Layanan',
    'barang' => 'Barang & ATK',
    'pembayaran' => 'Pembayaran',
    'stok' => 'Manajemen Stok',
    'laporan' => 'Laporan'
];

$title = $titles[$page] ?? 'Dashboard';

function activeNav($a, $b)
{
    return $a === $b ? 'active' : '';
}

?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>
        <?= e($title) ?> | Fotokopi Samboja
    </title>

    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>
    <div class="app-shell">

        <aside class="sidebar" id="sidebar">

            <a class="brand" href="index.php">
                <span class="brand-mark">FS</span>

                <span>
                    <strong>FOTOKOPI</strong>
                    <small>SAMBOJA · ADMIN</small>
                </span>
            </a>

            <div class="nav-caption">
                MENU UTAMA
            </div>

            <nav class="nav-menu">

                <a
                    class="<?= activeNav($page, 'dashboard') ?>"
                    href="index.php"
                >
                    ▦ &nbsp;Dashboard
                </a>

                <a
                    class="<?= activeNav($page, 'pesanan') ?>"
                    href="index.php?page=pesanan"
                >
                    ▤ &nbsp;Pesanan
                </a>

                <div class="nav-caption nav-caption-spaced">
                    MASTER DATA
                </div>

                <a
                    class="<?= activeNav($page, 'layanan') ?>"
                    href="index.php?page=layanan"
                >
                    ▧ &nbsp;Katalog Layanan
                </a>

                <a
                    class="<?= activeNav($page, 'barang') ?>"
                    href="index.php?page=barang"
                >
                    ▣ &nbsp;Barang & ATK
                </a>

                <div class="nav-caption nav-caption-spaced">
                    TRANSAKSI & LAPORAN
                </div>

                <a
                    class="<?= activeNav($page, 'pembayaran') ?>"
                    href="index.php?page=pembayaran"
                >
                    ▤ &nbsp;Pembayaran
                </a>

                <a
                    class="<?= activeNav($page, 'stok') ?>"
                    href="index.php?page=stok"
                >
                    ▥ &nbsp;Manajemen Stok
                </a>

                <a
                    class="<?= activeNav($page, 'laporan') ?>"
                    href="index.php?page=laporan"
                >
                    ▧ &nbsp;Laporan
                </a>

            </nav>

            <div class="sidebar-bottom">

                <div class="store-card">
                    <b>Kelola usaha lebih mudah</b>

                    <p>
                        Pantau pesanan, pembayaran, dan stok dalam satu tempat.
                    </p>
                </div>

                <div class="logout-link">
                    Sistem Admin Fotokopi
                </div>

            </div>

        </aside>

        <main class="main-area">

            <header class="topbar">

                <button class="menu-toggle" id="menuToggle">
                    ☰
                </button>

                <div class="breadcrumb">
                    Halaman / <b><?= e($title) ?></b>
                </div>

                <!-- <div class="profile">

                    <span class="avatar">A</span>

                    <span>
                        <b>Administrator</b>
                        <small>Pengelola</small>
                    </span>

                </div> -->

            </header>

            <section class="content">