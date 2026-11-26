<?php

require __DIR__ . '/../layouts/header.php';

$hasFilter = $filter['q'] !== '' || $filter['status'] !== '' || $filter['bayar'] !== '';

?>

<div class="page-heading">
    <div>
        <p class="eyebrow">TRANSAKSI</p>
        <h1>Data Pesanan</h1>
        <p class="subheading">Daftar pesanan pelanggan.</p>
    </div>

    <a class="button primary" href="index.php?page=pesanan&action=create">＋ Buat Pesanan</a>
</div>

<?php require __DIR__ . '/../layouts/flash.php'; ?>

<form class="filters" method="get" action="index.php">
    <input type="hidden" name="page" value="pesanan">

    <label>
        Cari
        <input type="search" name="q" placeholder="ID, nama, atau WhatsApp"
               value="<?= e($filter['q']) ?>">
    </label>

    <label>
        Status Pesanan
        <select name="status">
            <option value="">Semua</option>
            <?php foreach (Store::STATUS_PESANAN as $s): ?>
                <option <?= $filter['status'] === $s ? 'selected' : '' ?>><?= e($s) ?></option>
            <?php endforeach; ?>
        </select>
    </label>

    <label>
        Pembayaran
        <select name="bayar">
            <option value="">Semua</option>
            <?php foreach (Store::STATUS_PEMBAYARAN as $s): ?>
                <option <?= $filter['bayar'] === $s ? 'selected' : '' ?>><?= e($s) ?></option>
            <?php endforeach; ?>
        </select>
    </label>

    <button class="button primary">Terapkan</button>

    <?php if ($hasFilter): ?>
        <a class="button outline" href="index.php?page=pesanan">Reset</a>
    <?php endif; ?>
</form>

<section class="panel">

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Pelanggan</th>
                    <th>WhatsApp</th>
                    <th>Tanggal</th>
                    <th class="num">Total</th>
                    <th class="num">Dibayar</th>
                    <th class="num">Sisa</th>
                    <th>Pesanan</th>
                    <th>Pembayaran</th>
                    <th></th>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($rows as $r): ?>
                    <tr>
                        <td><b><?= e($r['id_pesanan']) ?></b></td>
                        <td><?= e($r['nama_pelanggan']) ?></td>
                        <td><?= e($r['no_whatsapp'] ?? '-') ?></td>
                        <td><?= e(tgl($r['tanggal_pesanan'])) ?></td>
                        <td class="num"><?= rp($r['total']) ?></td>
                        <td class="num"><?= rp($r['dibayar']) ?></td>
                        <td class="num"><?= rp(max(0, $r['sisa'])) ?></td>
                        <td><?= badge($r['status_pesanan']) ?></td>
                        <td><?= badge($r['status_pembayaran']) ?></td>
                        <td>
                            <a class="small-btn"
                               href="index.php?page=pesanan&action=detail&id=<?= urlencode($r['id_pesanan']) ?>">Detail</a>
                        </td>
                    </tr>
                <?php endforeach; ?>

                <?php if (!$rows): ?>
                    <tr>
                        <td colspan="10" class="empty">
                            <?= $hasFilter ? 'Tidak ada pesanan yang cocok dengan filter.' : 'Belum ada pesanan.' ?>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</section>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
