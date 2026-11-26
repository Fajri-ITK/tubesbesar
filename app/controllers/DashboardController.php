<?php

require_once __DIR__ . '/../models/Dashboard.php';

class DashboardController
{
    public function index()
    {
        $data = (new Dashboard())->data();

        require __DIR__ . '/../views/dashboard/index.php';
    }
}
