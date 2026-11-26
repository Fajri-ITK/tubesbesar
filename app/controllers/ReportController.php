<?php

require_once __DIR__ . '/../models/Report.php';

class ReportController
{
    public function index()
    {
        $all = isset($_GET['all']);

        $from = (string) ($_GET['dari'] ?? date('Y-m-01'));
        $to   = (string) ($_GET['sampai'] ?? date('Y-m-d'));

        $error = null;

        if ($all) {
            $from = $to = '';
        } elseif (!validDate($from) || !validDate($to)) {
            $error = 'Format tanggal tidak valid; menampilkan bulan ini.';
            $from  = date('Y-m-01');
            $to    = date('Y-m-d');
        } elseif ($from > $to) {
            $error = 'Tanggal "dari" tidak boleh melebihi "sampai"; kedua tanggal ditukar.';
            [$from, $to] = [$to, $from];
        }

        $report = (new Report())->build($from ?: null, $to ?: null);

        require __DIR__ . '/../views/laporan/index.php';
    }
}
