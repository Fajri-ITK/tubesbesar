<?php

require __DIR__ . '/../layouts/header.php';

// Aman ditanam di dalam <script> (mencegah penutupan tag lewat data).
$J = JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE;

$jsSvc = array_map(fn($x) => [
    'id'    => $x['id_layanan'],
    'name'  => $x['nama_layanan'],
    'unit'  => $x['satuan'],
    'price' => (float) $x['harga'],
], $services);

$jsPrd = array_map(fn($x) => [
    'id'    => $x['id_barang'],
    'name'  => $x['nama_barang'],
    'unit'  => $x['satuan'],
    'price' => (float) $x['harga_jual'],
    'stock' => (int) $x['stok_tersedia'],
], $products);

$initial = $oldItems
    ? array_map(fn($i) => [
        'kind' => $i['kind'],
        'id'   => $i['id'],
        'qty'  => (int) $i['qty'] ?: 1,
    ], $oldItems)
    : [['kind' => 'layanan', 'id' => '', 'qty' => 1]];

?>

<div class="page-heading">
    <div>
        <p class="eyebrow">TRANSAKSI BARU</p>
        <h1>Buat Pesanan</h1>
    </div>

    <a class="button outline" href="index.php?page=pesanan">← Kembali</a>
</div>

<?php require __DIR__ . '/../layouts/flash.php'; ?>

<form class="panel form" method="post" action="index.php?page=pesanan&action=store">
    <h2>Informasi Pelanggan</h2>

    <div class="form-grid">

        <label>
            ID Pesanan
            <input value="Dibuat otomatis (PSN + nomor urut)" disabled>
        </label>

        <label>
            Nama Pelanggan
            <input name="nama_pelanggan" maxlength="100" required
                   value="<?= e($old['nama_pelanggan'] ?? '') ?>">
        </label>

        <label>
            Nomor WhatsApp
            <input name="no_whatsapp" maxlength="20" placeholder="Opsional"
                   value="<?= e($old['no_whatsapp'] ?? '') ?>">
        </label>

        <label>
            Tanggal Pesanan
            <input type="date" name="tanggal_pesanan" required
                   value="<?= e($old['tanggal_pesanan'] ?? date('Y-m-d')) ?>">
        </label>

        <label class="wide">
            Keterangan
            <textarea name="keterangan" maxlength="255"><?= e($old['keterangan'] ?? '') ?></textarea>
        </label>

    </div>

    <div class="panel-head form-title">
        <h2>Item Pesanan</h2>

        <button type="button" class="button outline" id="addItem">＋ Item</button>
    </div>

    <div id="itemRows"></div>

    <div class="totals">
        <p>Total tagihan <b id="grandTotal">Rp 0</b></p>
    </div>

    <p class="hint">
        Harga diambil otomatis dari katalog. Barang/ATK akan mengurangi stok saat pesanan disimpan.
    </p>

    <button class="button primary" type="submit">Simpan Pesanan</button>
</form>

<script>
    const svc = <?= json_encode($jsSvc, $J) ?>;
    const prd = <?= json_encode($jsPrd, $J) ?>;
    const initial = <?= json_encode($initial, $J) ?>;

    const rowsEl = document.getElementById('itemRows');
    const grand = document.getElementById('grandTotal');

    const fmt = n => 'Rp ' + Math.round(n).toLocaleString('id-ID');
    const esc = s => String(s).replace(/[&<>"']/g, c => (
        {'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'}[c]
    ));

    function listFor(row) {
        return row.querySelector('.kind').value === 'layanan' ? svc : prd;
    }

    function fillItems(row, selectedId) {
        const isSvc = row.querySelector('.kind').value === 'layanan';
        const sel = row.querySelector('.item-select');

        sel.innerHTML = listFor(row).map(x =>
            `<option value="${esc(x.id)}" ${!isSvc && x.stock < 1 ? 'disabled' : ''}>` +
            `${esc(x.name)}${isSvc ? '' : (x.stock < 1 ? ' (habis)' : ' (stok ' + x.stock + ')')}</option>`
        ).join('');

        if (selectedId) sel.value = selectedId;

        if (!sel.value || sel.selectedOptions[0]?.disabled) {
            const first = [...sel.options].find(o => !o.disabled);
            if (first) sel.value = first.value;
        }

        refresh(row);
    }

    function refresh(row) {
        const isSvc = row.querySelector('.kind').value === 'layanan';
        const item = listFor(row).find(x => x.id === row.querySelector('.item-select').value);
        const qtyEl = row.querySelector('.qty');
        const qty = parseInt(qtyEl.value, 10) || 0;
        const price = item ? item.price : 0;

        qtyEl.max = !isSvc && item ? item.stock : '';

        row.querySelector('.price').value = item ? fmt(price) + ' / ' + item.unit : '-';
        row.querySelector('.sub').value = fmt(price * qty);
        row.dataset.sub = price * qty;

        total();
    }

    function total() {
        let t = 0;
        document.querySelectorAll('.item-row').forEach(r => t += Number(r.dataset.sub || 0));
        grand.textContent = fmt(t);
    }

    function addRow(item) {
        const row = document.createElement('div');
        row.className = 'form-grid item-row';

        row.innerHTML = `
            <label>Jenis
                <select name="kind[]" class="kind">
                    <option value="layanan">Layanan</option>
                    <option value="barang">Barang / ATK</option>
                </select>
            </label>
            <label>Item
                <select name="item_id[]" class="item-select"></select>
            </label>
            <label>Jumlah
                <input type="number" name="qty[]" class="qty" min="1" step="1" required>
            </label>
            <label>Harga Satuan
                <input type="text" class="price" readonly tabindex="-1">
            </label>
            <label>Subtotal
                <input type="text" class="sub" readonly tabindex="-1">
            </label>
            <button type="button" class="small-btn danger remove" title="Hapus item">✕</button>`;

        row.querySelector('.kind').value = item.kind === 'barang' ? 'barang' : 'layanan';
        row.querySelector('.qty').value = item.qty || 1;

        rowsEl.append(row);
        fillItems(row, item.id);
    }

    document.addEventListener('change', e => {
        const row = e.target.closest('.item-row');
        if (!row) return;

        if (e.target.classList.contains('kind')) fillItems(row);
        if (e.target.classList.contains('item-select')) refresh(row);
    });

    document.addEventListener('input', e => {
        if (e.target.classList.contains('qty')) refresh(e.target.closest('.item-row'));
    });

    document.addEventListener('click', e => {
        if (!e.target.classList.contains('remove')) return;

        if (document.querySelectorAll('.item-row').length > 1) {
            e.target.closest('.item-row').remove();
            total();
        }
    });

    document.getElementById('addItem').onclick = () => addRow({kind: 'layanan', qty: 1});

    initial.forEach(addRow);
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
