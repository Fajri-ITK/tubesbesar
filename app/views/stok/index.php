<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="page-heading">
    <div>
        <p class="eyebrow">PERSEDIAAN</p>
        <h1>Manajemen Stok</h1>
        <p class="subheading">Catat stok masuk, keluar, dan penyesuaian (stok opname).</p>
    </div>
</div>

<?php require __DIR__ . '/../layouts/flash.php'; ?>

<form class="panel form" method="post" action="index.php?page=stok&action=store">
    <h2>Catat Perubahan Stok</h2>

    <div class="form-grid">

        <label>
            Barang
            <select name="id_barang">
                <?php foreach ($products as $p): ?>
                    <option value="<?= e($p['id_barang']) ?>"
                            <?= ($old['id_barang'] ?? '') === $p['id_barang'] ? 'selected' : '' ?>>
                        <?= e($p['nama_barang'] . ' (stok ' . $p['stok_tersedia'] . ' ' . $p['satuan'] . ')') ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>

        <label>
            Tanggal
            <input type="date" name="tanggal_pencatatan" required
                   value="<?= e($old['tanggal_pencatatan'] ?? date('Y-m-d')) ?>">
        </label>

        <label>
            Jenis
            <select name="jenis_transaksi" id="jenis">
                <?php foreach (Store::JENIS_STOK as $j): ?>
                    <option <?= ($old['jenis_transaksi'] ?? '') === $j ? 'selected' : '' ?>><?= e($j) ?></option>
                <?php endforeach; ?>
            </select>
        </label>

        <label>
            <span id="jumlahLabel">Jumlah</span>
            <input type="number" name="jumlah" id="jumlah" min="1" step="1" required
                   value="<?= e($old['jumlah'] ?? '') ?>">
            <small class="hint" id="jumlahHint"></small>
        </label>

        <label class="wide">
            Keterangan
            <input name="keterangan" maxlength="200" placeholder="Opsional, mis. kulakan / rusak"
                   value="<?= e($old['keterangan'] ?? '') ?>">
        </label>

    </div>

    <button class="button primary">Simpan</button>
</form>

<script>
    const jenis = document.getElementById('jenis');
    const jumlah = document.getElementById('jumlah');
    const label = document.getElementById('jumlahLabel');
    const hint = document.getElementById('jumlahHint');

    function syncJenis() {
        const adj = jenis.value === 'Penyesuaian';

        label.textContent = adj ? 'Stok Fisik Hasil Hitung' : 'Jumlah';
        jumlah.min = adj ? 0 : 1;
        hint.textContent = adj
            ? 'Isi jumlah barang yang benar-benar ada. Sistem mencatat selisihnya terhadap stok saat ini.'
            : '';
    }

    jenis.addEventListener('change', syncJenis);
    syncJenis();
</script>

<section class="panel space">

    <div class="panel-head">
        <div>
            <h2>Posisi Stok Saat Ini</h2>
            <small>Menipis jika stok ≤ <?= LOW_STOCK_LIMIT ?></small>
        </div>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Barang</th>
                    <th class="num">Stok</th>
                    <th>Satuan</th>
                    <th>Status</th>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($products as $p): ?>
                    <?php
                    $stok = (int) $p['stok_tersedia'];
                    $st   = $stok <= 0 ? 'Habis' : ($stok <= LOW_STOCK_LIMIT ? 'Menipis' : 'Aman');
                    ?>
                    <tr>
                        <td><?= e($p['id_barang']) ?></td>
                        <td><?= e($p['nama_barang']) ?></td>
                        <td class="num"><?= $stok ?></td>
                        <td><?= e($p['satuan']) ?></td>
                        <td><?= badge($st) ?></td>
                    </tr>
                <?php endforeach; ?>

                <?php if (!$products): ?>
                    <tr><td colspan="5" class="empty">Belum ada barang.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</section>

<section class="panel space">

    <div class="panel-head">
        <h2>Riwayat Stok</h2>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Barang</th>
                    <th>Tanggal</th>
                    <th>Jenis</th>
                    <th class="num">Jumlah</th>
                    <th>Keterangan</th>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($rows as $r): ?>
                    <tr>
                        <td><?= (int) $r['id_pencatatan'] ?></td>
                        <td><?= e($r['nama_barang']) ?></td>
                        <td><?= e(tgl($r['tanggal_pencatatan'])) ?></td>
                        <td><?= badge($r['jenis_transaksi']) ?></td>
                        <td class="num"><?= (int) $r['jumlah'] ?> <?= e($r['satuan']) ?></td>
                        <td><?= e($r['keterangan'] ?? '') ?></td>
                    </tr>
                <?php endforeach; ?>

                <?php if (!$rows): ?>
                    <tr><td colspan="6" class="empty">Belum ada catatan stok.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</section>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
