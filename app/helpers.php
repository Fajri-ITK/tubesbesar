<?php


if (!function_exists('mb_strlen')) {
    function mb_strlen($s): int
    {
        return (int) preg_match_all('/./us', (string) $s);
    }
}

if (!function_exists('mb_substr')) {
    function mb_substr($s, int $start, ?int $length = null): string
    {
        preg_match_all('/./us', (string) $s, $m);

        return implode('', array_slice($m[0], $start, $length));
    }
}

/** escape output HTML. */
function e($v): string
{
    return htmlspecialchars((string) $v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** format rupiah: 75000 -> Rp 75.000 */
function rp($n): string
{
    return 'Rp ' . number_format((float) $n, 0, ',', '.');
}

/** format tanggal: 2026-11-10 -> 10 Nov 2026 */
function tgl(?string $d): string
{
    if (!$d) {
        return '-';
    }

    $t = strtotime($d);

    if ($t === false) {
        return (string) $d;
    }

    $bulan = [
        1 => 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun',
        'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des',
    ];

    return date('j', $t) . ' ' . $bulan[(int) date('n', $t)] . ' ' . date('Y', $t);
}

/** label status berwarna (sudah di-escape). */
function badge(?string $text): string
{
    $map = [
        'Diproses'    => 'warn',
        'Selesai'     => 'info',
        'Diambil'     => 'ok',
        'Belum Bayar' => 'danger',
        'DP'          => 'warn',
        'Lunas'       => 'ok',
        'Masuk'       => 'ok',
        'Keluar'      => 'danger',
        'Penyesuaian' => 'info',
        'Tunai'       => 'muted',
        'QRIS'        => 'info',
        'Aman'        => 'ok',
        'Menipis'     => 'warn',
        'Habis'       => 'danger',
    ];

    $text = (string) $text;

    return '<span class="badge badge-' . ($map[$text] ?? 'muted') . '">'
        . e($text) . '</span>';
}

/** pesan sukses dari parameter ?ok=... */
function okMessage(): ?string
{
    $map = [
        'simpan'  => 'Data berhasil disimpan.',
        'hapus'   => 'Data berhasil dihapus.',
        'pesanan' => 'Pesanan berhasil dibuat.',
        'status'  => 'Status pesanan diperbarui.',
        'bayar'   => 'Pembayaran berhasil dicatat.',
        'stok'    => 'Stok berhasil diperbarui.',
    ];

    return $map[$_GET['ok'] ?? ''] ?? null;
}

/** ngambil input POST berupa string yang sudah di-trim. */
function post(string $key): string
{
    $v = $_POST[$key] ?? '';

    return is_string($v) ? trim($v) : '';
}

/** cek tanggal Y-m-d yang valid. */
function validDate(?string $d): bool
{
    if (!$d) {
        return false;
    }

    $x = DateTime::createFromFormat('Y-m-d', $d);

    return $x !== false && $x->format('Y-m-d') === $d;
}

function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}
