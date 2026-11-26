<?php

require __DIR__ . '/../layouts/header.php';

$sisa = max(0, $total - $paid);

?>

<div class="page-heading">
    <div>
        <p class="eyebrow">DETAIL TRANSAKSI</p>

        <h1><?= e($order['id_pesanan']) ?></h1>

        <p class="subheading">
            <?= e($order['nama_pelanggan']) ?>
            · <?= e(tgl($order['tanggal_pesanan'])) ?>
            <?php if ($order['no_whatsapp']): ?>
                · WA <?= e($order['no_whatsapp']) ?>
            <?php endif; ?>
        </p>

        <?php if ($order['keterangan']): ?>
            <p class="subheading"><?= e($order['keterangan']) ?></p>
        <?php endif; ?>
    </div>

    <a class="button outline" href="index.php?page=pesanan">← Kembali</a>
</div>

<?php require __DIR__ . '/../layouts/flash.php'; ?>

<section class="panel">

    <div class="panel-head">
        <h2>Rincian Item</h2>

        <div class="status-line">
            <?= badge($order['status_pesanan']) ?>
            <?= badge($order['status_pembayaran']) ?>
        </div>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Jenis</th>
                    <th class="num">Jumlah</th>
                    <th class="num">Harga</th>
                    <th class="num">Subtotal</th>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($items as $i): ?>
                    <?php $isSvc = $i['id_layanan'] !== null; ?>
                    <tr>
                        <td><?= e($isSvc ? $i['nama_layanan'] : $i['nama_barang']) ?></td>
                        <td><?= $isSvc ? 'Layanan' : 'Barang' ?></td>
                        <td class="num">
                            <?= (int) $i['jumlah'] ?>
                            <?= e($isSvc ? $i['satuan_layanan'] : $i['satuan_barang']) ?>
                        </td>
                        <td class="num"><?= rp($i['harga_satuan']) ?></td>
                        <td class="num"><?= rp($i['jumlah'] * $i['harga_satuan']) ?></td>
                    </tr>
                <?php endforeach; ?>

                <?php if (!$items): ?>
                    <tr><td colspan="5" class="empty">Pesanan ini belum memiliki item.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="totals">
        <p>Total tagihan <b><?= rp($total) ?></b></p>
        <p>Total dibayar <b><?= rp($paid) ?></b></p>
        <p>Sisa tagihan <b><?= rp($sisa) ?></b></p>
    </div>

</section>

<section class="panel form space">
    <h2>Status Pesanan</h2>

    <form class="inline-form" method="post" action="index.php?page=pesanan&action=status">
        <input type="hidden" name="id_pesanan" value="<?= e($order['id_pesanan']) ?>">

        <select name="status_pesanan">
            <?php foreach (Store::STATUS_PESANAN as $s): ?>
                <option <?= $order['status_pesanan'] === $s ? 'selected' : '' ?>><?= e($s) ?></option>
            <?php endforeach; ?>
        </select>

        <button class="button primary">Simpan Status</button>
    </form>
</section>

<section class="panel space">

    <div class="panel-head">
        <h2>Riwayat Pembayaran</h2>

        <?php if ($sisa > 0): ?>
            <a href="index.php?page=pembayaran&id=<?= urlencode($order['id_pesanan']) ?>">
                Catat pembayaran →
            </a>
        <?php endif; ?>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Metode</th>
                    <th class="num">Jumlah</th>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($payments as $p): ?>
                    <tr>
                        <td><?= e(tgl($p['tanggal_bayar'])) ?></td>
                        <td><?= badge($p['metode_pembayaran']) ?></td>
                        <td class="num"><?= rp($p['jumlah_bayar']) ?></td>
                    </tr>
                <?php endforeach; ?>

                <?php if (!$payments): ?>
                    <tr><td colspan="3" class="empty">Belum ada pembayaran.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</section>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
