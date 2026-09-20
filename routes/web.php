<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'name' => 'FEB Thesis API tes',
        'version' => '1.0.0',
        'description' => 'API untuk dataset skripsi mahasiswa FEB (1030 data)',
        // 'database' => 'new_nlp_model_1000',
        // 'endpoints' => [
        //     'GET /api/health' => 'Health check',
        //     'GET /api/theses' => 'List skripsi (pagination, filter, search, custom fields)',
        //     'GET /api/theses/stats' => 'Statistik skripsi (total, per tahun, per prodi)',
        //     'GET /api/theses/years' => 'Daftar tahun tersedia',
        //     'GET /api/theses/programs' => 'Daftar program studi tersedia',
        //     'GET /api/theses/{id}' => 'Detail skripsi by ID',
        // ],
        'query_parameters' => [
            'paginate' => 'true/false (default: true)',
            'per_page' => '1-100 (default: 10)',
            'fields' => 'Comma-separated kolom yang diinginkan',
            'years' => 'Comma-separated tahun (e.g., 2018,2017)',
            'programs' => 'Comma-separated program studi',
            'author' => 'Nama penulis (partial match)',
            'author_id' => 'NIM penulis (partial match)',
            'search' => 'Search gabungan (nama, NIM, judul)',
        ],
    ]);
});