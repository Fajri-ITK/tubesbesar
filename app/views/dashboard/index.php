<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="page-heading">
    <div>
        <p class="eyebrow">RINGKASAN USAHA</p>
        <h1>Dashboard</h1>
        <p class="subheading">Ringkasan aktivitas berdasarkan data database.</p>
    </div>

    <a class="button primary" href="index.php?page=pesanan&action=create">＋ Buat Pesanan</a>
</div>

<div class="stats-grid">

    <article class="stat-card">
        <span>Total Pesanan</span>
        <strong><?= (int) $data['orders'] ?></strong>
        <small>Seluruh pesanan</small>
    </article>

    <article class="stat-card green">
        <span>Pembayaran Bulan Ini</span>
        <strong><?= rp($data['revenue']) ?></strong>
        <small>
            Piutang <?= rp($data['unpaid_sum']) ?>
            (<?= (int) $data['unpaid_count'] ?> pesanan)
        </small>
    </article>

    <article class="stat-card orange">
        <span>Pesanan Diproses</span>
        <strong><?= (int) $data['processing'] ?></strong>
        <small>Perlu ditindaklanjuti</small>
    </article>

    <article class="stat-card red">
        <span>Stok Menipis</span>
        <strong><?= (int) $data['low'] ?></strong>
        <small>Stok tersedia ≤ <?= LOW_STOCK_LIMIT ?></small>
    </article>

</div>

<div class="dashboard-grid">

    <section class="panel">
        <div class="panel-head">
            <div>
                <h2>Pesanan Terbaru</h2>
                <small>Data dari database</small>
            </div>

            <a href="index.php?page=pesanan">Lihat semua →</a>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Pelanggan</th>
                        <th>Tanggal</th>
                        <th class="num">Total</th>
                        <th>Pesanan</th>
                        <th>Pembayaran</th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($data['latest'] as $r): ?>
                        <tr>
                            <td>
                                <a class="link" href="index.php?page=pesanan&action=detail&id=<?= urlencode($r['id_pesanan']) ?>">
                                    <b><?= e($r['id_pesanan']) ?></b>
                                </a>
                            </td>
                            <td><?= e($r['nama_pelanggan']) ?></td>
                            <td><?= e(tgl($r['tanggal_pesanan'])) ?></td>
                            <td class="num"><?= rp($r['total']) ?></td>
                            <td><?= badge($r['status_pesanan']) ?></td>
                            <td><?= badge($r['status_pembayaran']) ?></td>
                        </tr>
                    <?php endforeach; ?>

                    <?php if (!$data['latest']): ?>
                        <tr><td colspan="6" class="empty">Belum ada pesanan.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

    <section class="panel">
        <div class="panel-head">
            <div>
                <h2>Perlu Restok</h2>
                <small>Stok maksimal <?= LOW_STOCK_LIMIT ?></small>
            </div>

            <a href="index.php?page=stok">Lihat stok →</a>
        </div>

        <div class="stock-list">
            <?php foreach ($data['lowitems'] as $b): ?>
                <div class="stock-row">
                    <b><?= e($b['nama_barang']) ?></b>
                    <span><?= (int) $b['stok_tersedia'] ?> <?= e($b['satuan']) ?></span>
                </div>
            <?php endforeach; ?>

            <?php if (!$data['lowitems']): ?>
                <p class="empty">Tidak ada stok menipis.</p>
            <?php endif; ?>
        </div>
    </section>

</div>

<section class="panel quick">
    <h2>Akses Cepat</h2>

    <a href="index.php?page=pesanan&action=create">＋ Buat Pesanan</a>
    <a href="index.php?page=pembayaran">Rp Catat Pembayaran</a>
    <a href="index.php?page=stok">▣ Tambah Stok</a>
</section>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
