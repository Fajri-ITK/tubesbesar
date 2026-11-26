<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="page-heading">
    <h1><?= $edit ? 'Edit' : 'Tambah' ?> Katalog Layanan</h1>

    <a class="button outline" href="index.php?page=layanan">← Kembali</a>
</div>

<?php require __DIR__ . '/../layouts/flash.php'; ?>

<form class="panel form" method="post" action="index.php?page=layanan&action=store">
    <input type="hidden" name="old_id" value="<?= $edit ? e($record['id_layanan'] ?? '') : '' ?>">

    <div class="form-grid">

        <label>
            ID Layanan
            <input type="text" name="id_layanan" maxlength="10" required
                   value="<?= e($record['id_layanan'] ?? '') ?>"
                   <?= $edit ? 'readonly' : '' ?>>
        </label>

        <label>
            Nama Layanan
            <input type="text" name="nama_layanan" maxlength="100" required
                   value="<?= e($record['nama_layanan'] ?? '') ?>">
        </label>

        <label>
            Kategori
            <input type="text" name="kategori" maxlength="30" required
                   value="<?= e($record['kategori'] ?? 'Jasa') ?>">
        </label>

        <label>
            Satuan
            <input type="text" name="satuan" maxlength="20" required placeholder="Lembar, Buku, ..."
                   value="<?= e($record['satuan'] ?? '') ?>">
        </label>

        <label>
            Harga (Rp)
            <input type="number" name="harga" required min="0" step="0.01"
                   value="<?= e($record['harga'] ?? '') ?>">
        </label>

    </div>

    <button class="button primary">Simpan</button>
</form>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
