<?php

function formatCurrency(int $amount): String
{
    return 'Rp ' . number_format($amount, 0, ',', '.');
}

function formatDate(String $date): String
{
    $timestamp = strtotime($date);

    $hari = [
        'Sunday' => 'minggu',
        'Monday' => 'senin',
        'Tuesday' => 'selasa',
        'Wednesday' => 'rabu',
        'Thursday' => 'kamis',
        'Friday' => 'jumat',
        'Saturday' => 'sabtu',
    ];

    $bulan = [
        'January' => 'januari',
        'February' => 'februari',
        'March' => 'maret',
        'April' => 'april',
        'May' => 'mei',
        'June' => 'juni',
        'July' => 'juli',
        'August' => 'agustus',
        'September' => 'september',
        'October' => 'oktober',
        'November' => 'november',
        'December' => 'desember',
    ];

    $dayName = date('l', $timestamp);
    $monthName = date('F', $timestamp);

    return $hari[$dayName] . ' ' . date('d', $timestamp) . ' ' . $bulan[$monthName] . ' ' . date('Y', $timestamp);
}