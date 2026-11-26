<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="page-heading">
    <div>
        <p class="eyebrow">MASTER DATA</p>
        <h1>Barang & ATK</h1>
    </div>

    <a class="button primary" href="index.php?page=barang&action=create">＋ Tambah</a>
</div>

<?php require __DIR__ . '/../layouts/flash.php'; ?>

<section class="panel">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nama Barang</th>
                    <th>Satuan</th>
                    <th class="num">Harga Jual</th>
                    <th class="num">Stok</th>
                    <th>Status Stok</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($rows as $r): ?>
                    <?php
                    $stok = (int) $r['stok_tersedia'];
                    $st   = $stok <= 0 ? 'Habis' : ($stok <= LOW_STOCK_LIMIT ? 'Menipis' : 'Aman');
                    ?>
                    <tr>
                        <td><?= e($r['id_barang']) ?></td>
                        <td><?= e($r['nama_barang']) ?></td>
                        <td><?= e($r['satuan']) ?></td>
                        <td class="num"><?= rp($r['harga_jual']) ?></td>
                        <td class="num"><?= $stok ?></td>
                        <td><?= badge($st) ?></td>
                        <td>
                            <a class="small-btn"
                               href="index.php?page=barang&action=edit&id=<?= urlencode($r['id_barang']) ?>">Edit</a>

                            <form class="inline" method="post"
                                  action="index.php?page=barang&action=delete"
                                  onsubmit="return confirm('Hapus barang ini?')">
                                <input type="hidden" name="id" value="<?= e($r['id_barang']) ?>">
                                <button class="small-btn danger">Hapus</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>

                <?php if (!$rows): ?>
                    <tr><td colspan="7" class="empty">Belum ada data.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
