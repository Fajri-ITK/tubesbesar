<?php

require __DIR__ . '/../layouts/header.php';

$s = $report['sum'];

$periode = ($from || $to)
    ? tgl($from ?: null) . ' – ' . tgl($to ?: null)
    : 'Seluruh periode';

?>

<div class="page-heading">
    <div>
        <p class="eyebrow">REKAPITULASI</p>
        <h1>Laporan</h1>
        <p class="subheading">Periode: <b><?= e($periode) ?></b></p>
    </div>
</div>

<?php require __DIR__ . '/../layouts/flash.php'; ?>

<form class="filters" method="get" action="index.php">
    <input type="hidden" name="page" value="laporan">

    <label>
        Dari
        <input type="date" name="dari" value="<?= e($from) ?>">
    </label>

    <label>
        Sampai
        <input type="date" name="sampai" value="<?= e($to) ?>">
    </label>

    <button class="button primary">Terapkan</button>
    <a class="button outline" href="index.php?page=laporan">Bulan ini</a>
    <a class="button outline" href="index.php?page=laporan&all=1">Semua periode</a>
</form>

<div class="stats-grid">

    <article class="stat-card">
        <span>Jumlah Pesanan</span>
        <strong><?= (int) $s['pesanan'] ?></strong>
        <small>Berdasarkan tanggal pesanan</small>
    </article>

    <article class="stat-card">
        <span>Total Tagihan</span>
        <strong><?= rp($s['tagihan']) ?></strong>
        <small>Nilai seluruh pesanan</small>
    </article>

    <article class="stat-card green">
        <span>Pembayaran Diterima</span>
        <strong><?= rp($s['diterima']) ?></strong>
        <small>Berdasarkan tanggal bayar</small>
    </article>

    <article class="stat-card red">
        <span>Piutang Pesanan Periode Ini</span>
        <strong><?= rp($s['piutang']) ?></strong>
        <small>Sisa tagihan sampai hari ini</small>
    </article>

</div>

<div class="grid-3">

    <section class="panel">
        <div class="panel-head"><h2>Pembayaran per Metode</h2></div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Metode</th><th class="num">Transaksi</th><th class="num">Total</th></tr></thead>
                <tbody>
                    <?php foreach ($report['metode'] as $r): ?>
                        <tr>
                            <td><?= badge($r['metode']) ?></td>
                            <td class="num"><?= (int) $r['n'] ?></td>
                            <td class="num"><?= rp($r['total']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>

    <section class="panel">
        <div class="panel-head"><h2>Pesanan per Status</h2></div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Status</th><th class="num">Pesanan</th><th class="num">Nilai</th></tr></thead>
                <tbody>
                    <?php foreach ($report['status'] as $r): ?>
                        <tr>
                            <td><?= badge($r['status']) ?></td>
                            <td class="num"><?= (int) $r['n'] ?></td>
                            <td class="num"><?= rp($r['total']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>

    <section class="panel">
        <div class="panel-head"><h2>Pesanan per Pembayaran</h2></div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Status</th><th class="num">Pesanan</th><th class="num">Nilai</th></tr></thead>
                <tbody>
                    <?php foreach ($report['bayar'] as $r): ?>
                        <tr>
                            <td><?= badge($r['status']) ?></td>
                            <td class="num"><?= (int) $r['n'] ?></td>
                            <td class="num"><?= rp($r['total']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>

</div>

<div class="grid-2">

    <section class="panel">
        <div class="panel-head"><h2>Penjualan Layanan</h2></div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Layanan</th><th class="num">Terjual</th><th class="num">Omzet</th></tr></thead>
                <tbody>
                    <?php foreach ($report['services'] as $r): ?>
                        <tr>
                            <td><?= e($r['nama']) ?></td>
                            <td class="num"><?= (int) $r['qty'] ?> <?= e($r['satuan']) ?></td>
                            <td class="num"><?= rp($r['omzet']) ?></td>
                        </tr>
                    <?php endforeach; ?>

                    <?php if (!$report['services']): ?>
                        <tr><td colspan="3" class="empty">Tidak ada data pada periode ini.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

    <section class="panel">
        <div class="panel-head"><h2>Penjualan Barang & ATK</h2></div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Barang</th><th class="num">Terjual</th><th class="num">Omzet</th></tr></thead>
                <tbody>
                    <?php foreach ($report['products'] as $r): ?>
                        <tr>
                            <td><?= e($r['nama']) ?></td>
                            <td class="num"><?= (int) $r['qty'] ?> <?= e($r['satuan']) ?></td>
                            <td class="num"><?= rp($r['omzet']) ?></td>
                        </tr>
                    <?php endforeach; ?>

                    <?php if (!$report['products']): ?>
                        <tr><td colspan="3" class="empty">Tidak ada data pada periode ini.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

</div>

<section class="panel space">
    <div class="panel-head">
        <div>
            <h2>Mutasi Stok</h2>
            <small>Jumlah pada riwayat stok di periode terpilih; kolom stok adalah posisi saat ini.</small>
        </div>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Barang</th>
                    <th class="num">Masuk</th>
                    <th class="num">Keluar</th>
                    <th class="num">Penyesuaian</th>
                    <th class="num">Stok Saat Ini</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($report['stock'] as $r): ?>
                    <tr>
                        <td><?= e($r['nama_barang']) ?></td>
                        <td class="num"><?= (int) $r['masuk'] ?></td>
                        <td class="num"><?= (int) $r['keluar'] ?></td>
                        <td class="num"><?= (int) $r['penyesuaian'] ?></td>
                        <td class="num"><?= (int) $r['stok_tersedia'] ?> <?= e($r['satuan']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<section class="panel space">
    <div class="panel-head">
        <div>
            <h2>Pesanan Belum Lunas</h2>
            <small>Seluruh waktu, tidak dibatasi periode</small>
        </div>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Pelanggan</th>
                    <th>Tanggal</th>
                    <th class="num">Total</th>
                    <th class="num">Dibayar</th>
                    <th class="num">Sisa</th>
                    <th>Pembayaran</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($report['unpaid'] as $r): ?>
                    <tr>
                        <td>
                            <a class="link" href="index.php?page=pesanan&action=detail&id=<?= urlencode($r['id_pesanan']) ?>">
                                <b><?= e($r['id_pesanan']) ?></b>
                            </a>
                        </td>
                        <td><?= e($r['nama_pelanggan']) ?></td>
                        <td><?= e(tgl($r['tanggal_pesanan'])) ?></td>
                        <td class="num"><?= rp($r['total']) ?></td>
                        <td class="num"><?= rp($r['dibayar']) ?></td>
                        <td class="num"><b><?= rp($r['sisa']) ?></b></td>
                        <td><?= badge($r['status_pembayaran']) ?></td>
                    </tr>
                <?php endforeach; ?>

                <?php if (!$report['unpaid']): ?>
                    <tr><td colspan="7" class="empty">Semua pesanan sudah lunas.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
