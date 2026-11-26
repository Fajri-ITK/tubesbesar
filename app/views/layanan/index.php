<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="page-heading">
    <div>
        <p class="eyebrow">MASTER DATA</p>
        <h1>Katalog Layanan</h1>
    </div>

    <a class="button primary" href="index.php?page=layanan&action=create">＋ Tambah</a>
</div>

<?php require __DIR__ . '/../layouts/flash.php'; ?>

<section class="panel">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nama Layanan</th>
                    <th>Kategori</th>
                    <th>Satuan</th>
                    <th class="num">Harga</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($rows as $r): ?>
                    <tr>
                        <td><?= e($r['id_layanan']) ?></td>
                        <td><?= e($r['nama_layanan']) ?></td>
                        <td><?= e($r['kategori']) ?></td>
                        <td><?= e($r['satuan']) ?></td>
                        <td class="num"><?= rp($r['harga']) ?></td>
                        <td>
                            <a class="small-btn"
                               href="index.php?page=layanan&action=edit&id=<?= urlencode($r['id_layanan']) ?>">Edit</a>

                            <form class="inline" method="post"
                                  action="index.php?page=layanan&action=delete"
                                  onsubmit="return confirm('Hapus layanan ini?')">
                                <input type="hidden" name="id" value="<?= e($r['id_layanan']) ?>">
                                <button class="small-btn danger">Hapus</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>

                <?php if (!$rows): ?>
                    <tr><td colspan="6" class="empty">Belum ada data.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
