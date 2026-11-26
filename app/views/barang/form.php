<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="page-heading">
    <h1><?= $edit ? 'Edit' : 'Tambah' ?> Barang & ATK</h1>

    <a class="button outline" href="index.php?page=barang">← Kembali</a>
</div>

<?php require __DIR__ . '/../layouts/flash.php'; ?>

<form class="panel form" method="post" action="index.php?page=barang&action=store">
    <input type="hidden" name="old_id" value="<?= $edit ? e($record['id_barang'] ?? '') : '' ?>">

    <div class="form-grid">

        <label>
            ID Barang
            <input type="text" name="id_barang" maxlength="10" required
                   value="<?= e($record['id_barang'] ?? '') ?>"
                   <?= $edit ? 'readonly' : '' ?>>
        </label>

        <label>
            Nama Barang
            <input type="text" name="nama_barang" maxlength="100" required
                   value="<?= e($record['nama_barang'] ?? '') ?>">
        </label>

        <label>
            Satuan
            <input type="text" name="satuan" maxlength="20" required placeholder="Pcs, Rim, Buku, ..."
                   value="<?= e($record['satuan'] ?? '') ?>">
        </label>

        <label>
            Harga Jual (Rp)
            <input type="number" name="harga_jual" required min="0" step="0.01"
                   value="<?= e($record['harga_jual'] ?? '') ?>">
        </label>

        <?php if ($edit): ?>
            <label class="wide">
                Stok Tersedia
                <input type="text" value="<?= (int) ($record['stok_tersedia'] ?? 0) ?>" readonly>
                <small class="hint">
                    Stok tidak diubah dari sini. Gunakan menu
                    <a href="index.php?page=stok">Manajemen Stok</a>
                    agar tercatat di riwayat.
                </small>
            </label>
        <?php else: ?>
            <label class="wide">
                Stok Awal
                <input type="number" name="stok_tersedia" required min="0"
                       value="<?= e($record['stok_tersedia'] ?? '0') ?>">
                <small class="hint">Jika lebih dari 0, otomatis dicatat sebagai stok masuk "Stok awal".</small>
            </label>
        <?php endif; ?>

    </div>

    <button class="button primary">Simpan</button>
</form>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
