<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="page-heading">
    <div>
        <p class="eyebrow">TRANSAKSI</p>
        <h1>Pembayaran</h1>
        <p class="subheading">Catat pembayaran tunai atau QRIS. Status pembayaran pesanan diperbarui otomatis.</p>
    </div>
</div>

<?php require __DIR__ . '/../layouts/flash.php'; ?>

<form class="panel form" method="post" action="index.php?page=pembayaran&action=store">
    <h2>Catat Pembayaran</h2>

    <?php if (!$orders): ?>
        <p class="hint">Semua pesanan sudah lunas. Tidak ada tagihan yang perlu dicatat.</p>
    <?php else: ?>

        <div class="form-grid">

            <label>
                Pesanan (hanya yang belum lunas)
                <select name="id_pesanan" id="orderSel" required>
                    <?php foreach ($orders as $o): ?>
                        <option value="<?= e($o['id_pesanan']) ?>"
                                data-sisa="<?= e($o['sisa']) ?>"
                                <?= $selected === $o['id_pesanan'] ? 'selected' : '' ?>>
                            <?= e($o['id_pesanan'] . ' - ' . $o['nama_pelanggan'] . ' (sisa ' . rp($o['sisa']) . ')') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label>
                Tanggal
                <input type="date" name="tanggal_bayar" required
                       value="<?= e($old['tanggal_bayar'] ?? date('Y-m-d')) ?>">
            </label>

            <label>
                Metode
                <select name="metode_pembayaran">
                    <?php foreach (Store::METODE as $m): ?>
                        <option <?= ($old['metode_pembayaran'] ?? '') === $m ? 'selected' : '' ?>><?= e($m) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label>
                Jumlah Bayar (Rp)
                <input type="number" name="jumlah_bayar" id="amount" min="0.01" step="0.01" required
                       value="<?= e($old['jumlah_bayar'] ?? '') ?>">
                <small class="hint">
                    <button type="button" class="link-btn" id="fillSisa">Isi sebesar sisa tagihan</button>
                </small>
            </label>

        </div>

        <button class="button primary">Simpan Pembayaran</button>

        <script>
            const sel = document.getElementById('orderSel');
            const amount = document.getElementById('amount');

            function syncMax() {
                amount.max = sel.selectedOptions[0]?.dataset.sisa || '';
            }

            sel.addEventListener('change', syncMax);

            document.getElementById('fillSisa').onclick = () => {
                amount.value = sel.selectedOptions[0]?.dataset.sisa || '';
            };

            syncMax();
        </script>

    <?php endif; ?>
</form>

<section class="panel space">

    <div class="panel-head">
        <h2>Riwayat Pembayaran</h2>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Pesanan</th>
                    <th>Pelanggan</th>
                    <th>Tanggal</th>
                    <th>Metode</th>
                    <th class="num">Jumlah</th>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($rows as $r): ?>
                    <tr>
                        <td><?= (int) $r['id_pembayaran'] ?></td>
                        <td>
                            <a class="link" href="index.php?page=pesanan&action=detail&id=<?= urlencode($r['id_pesanan']) ?>">
                                <?= e($r['id_pesanan']) ?>
                            </a>
                        </td>
                        <td><?= e($r['nama_pelanggan']) ?></td>
                        <td><?= e(tgl($r['tanggal_bayar'])) ?></td>
                        <td><?= badge($r['metode_pembayaran']) ?></td>
                        <td class="num"><?= rp($r['jumlah_bayar']) ?></td>
                    </tr>
                <?php endforeach; ?>

                <?php if (!$rows): ?>
                    <tr><td colspan="6" class="empty">Belum ada pembayaran.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</section>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
