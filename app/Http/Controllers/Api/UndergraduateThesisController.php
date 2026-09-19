<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UndergraduateThesis;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UndergraduateThesisController extends Controller
{
    /**
     * Helper privat untuk memvalidasi & menyaring kolom SELECT yang diminta client.
     */
    private function parseSelectedColumns(Request $request): array
    {
        // Kolom default jika client tidak menentukan parameter ?fields=
        $defaultColumns = [
            'id',
            'undergraduate_thesis_title',
            'undergraduate_thesis_slug',
            'undergraduate_thesis_year',
            'undergraduate_thesis_program',
            'undergraduate_thesis_author',
            'undergraduate_thesis_author_id',
        ];

        if ($request->has('fields')) {
            $requestedFields = explode(',', $request->query('fields'));
            $requestedFields = array_map('trim', $requestedFields);

            // Filter strict hanya kolom yang terdaftar di $allowedColumns milik Model
            $filteredColumns = array_intersect($requestedFields, UndergraduateThesis::$allowedColumns);

            if (!empty($filteredColumns)) {
                // Wajibkan 'id' selalu terikut agar pagination & sorting tidak corrupt
                if (!in_array('id', $filteredColumns)) {
                    array_unshift($filteredColumns, 'id');
                }
                return array_values($filteredColumns);
            }
        }

        return $defaultColumns;
    }

    /**
     * Helper privat untuk menerapkan query filter (tahun, prodi, nama, nim, search).
     */
    private function applyFilters(Builder $query, Request $request): Builder
    {
        // 1. Filter Tahun (Multiple) - Support: ?years=2023,2024 atau ?years[]=2023
        $query->when($request->filled('years'), function ($q) use ($request) {
            $years = is_array($request->query('years'))
                ? $request->query('years')
                : explode(',', $request->query('years'));

            // Cast ke integer dan trim spasi
            $years = array_map(fn($item) => (int) trim($item), $years);
            $q->whereIn('undergraduate_thesis_year', array_filter($years));
        });

        // 2. Filter Program Studi (Multiple) - Support: ?programs=Informatika,Sistem Informasi
        $query->when($request->filled('programs'), function ($q) use ($request) {
            $programs = is_array($request->query('programs'))
                ? $request->query('programs')
                : explode(',', $request->query('programs'));

            $programs = array_map(fn($item) => trim($item), $programs);
            $q->whereIn('undergraduate_thesis_program', array_filter($programs));
        });

        // 3. Filter Nama Penulis (LIKE %...%, Case-Insensitive)
        $query->when($request->filled('author'), function ($q) use ($request) {
            $author = strtolower(trim($request->query('author')));
            $q->whereRaw('LOWER(undergraduate_thesis_author) LIKE ?', ["%{$author}%"]);
        });

        // 4. Filter NIM Penulis (LIKE %...%)
        $query->when($request->filled('author_id'), function ($q) use ($request) {
            $authorId = trim($request->query('author_id'));
            $q->where('undergraduate_thesis_author_id', 'LIKE', "%{$authorId}%");
        });

        // 5. Search Gabungan (Cari Kata Kunci di Nama, NIM, atau Judul sekaligus)
        $query->when($request->filled('search'), function ($q) use ($request) {
            $searchTerm = strtolower(trim($request->query('search')));
            $q->where(function ($subQuery) use ($searchTerm) {
                $subQuery->whereRaw('LOWER(undergraduate_thesis_author) LIKE ?', ["%{$searchTerm}%"])
                         ->orWhere('undergraduate_thesis_author_id', 'LIKE', "%{$searchTerm}%")
                         ->orWhereRaw('LOWER(undergraduate_thesis_title) LIKE ?', ["%{$searchTerm}%"]);
            });
        });

        return $query;
    }

    /**
     * 1. GET INDEX (Support Pagination & Non-Pagination, Dynamic Fields, Multi Filters)
     */
    public function index(Request $request): JsonResponse
    {
        // Parsel kolom yang di-select secara strict
        $columns = $this->parseSelectedColumns($request);

        // Buat Query
        $query = UndergraduateThesis::query()->select($columns);
        $query = $this->applyFilters($query, $request);
        $query->latest('id');

        // Validasi parameter paginate (Default: true)
        // Menerima nilai boolean/string: false, "false", 0, "0"
        $isPaginated = filter_var($request->query('paginate', true), FILTER_VALIDATE_BOOLEAN);

        if ($isPaginated) {
            // Batasi per_page (minimal 1, maksimal 100) agar tidak bisa diminta terlalu besar
            $perPage = max(1, min((int) $request->query('per_page', 10), 100));

            // withQueryString() agar filter ikut terbawa di next_page_url / links
            $data = $query->paginate($perPage)->withQueryString();
        } else {
            $data = $query->get();
        }

        return response()->json([
            'status' => 'success',
            'message' => $isPaginated
                ? 'Data berhasil diambil dengan pagination'
                : 'Seluruh data berhasil diambil tanpa pagination',
            'is_paginated' => $isPaginated,
            'selected_columns' => $columns,
            'data' => $data
        ], 200);
    }

    /**
     * 2. GET DETAIL BY ID
     */
    public function show(Request $request, int $id): JsonResponse
    {
        // Khusus detail: Jika tidak ada param 'fields', ambil semua kolom terdaftar secara eksplisit
        $columns = $request->has('fields')
            ? $this->parseSelectedColumns($request)
            : UndergraduateThesis::$allowedColumns;

        $data = UndergraduateThesis::query()
            ->select($columns)
            ->where('id', $id)
            ->first();

        if (!$data) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data skripsi tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Detail data skripsi ditemukan',
            'selected_columns' => $columns,
            'data' => $data
        ], 200);
    }

    /**
     * 3. GET STATISTICS
     */
    public function stats(Request $request): JsonResponse
    {
        $totalTheses = UndergraduateThesis::count();
        
        $byYear = UndergraduateThesis::query()
            ->select('undergraduate_thesis_year')
            ->selectRaw('COUNT(*) as count')
            ->groupBy('undergraduate_thesis_year')
            ->orderBy('undergraduate_thesis_year', 'desc')
            ->get()
            ->mapWithKeys(fn($item) => [$item->undergraduate_thesis_year => (int)$item->count]);

        $byProgram = UndergraduateThesis::query()
            ->select('undergraduate_thesis_program')
            ->selectRaw('COUNT(*) as count')
            ->groupBy('undergraduate_thesis_program')
            ->orderBy('count', 'desc')
            ->get()
            ->mapWithKeys(fn($item) => [$item->undergraduate_thesis_program => (int)$item->count]);

        $latestThesis = UndergraduateThesis::query()
            ->select(['id', 'undergraduate_thesis_title', 'undergraduate_thesis_year', 'undergraduate_thesis_program', 'undergraduate_thesis_author'])
            ->latest('id')
            ->first();

        return response()->json([
            'status' => 'success',
            'message' => 'Statistik data skripsi',
            'data' => [
                'total_theses' => $totalTheses,
                'by_year' => $byYear,
                'by_program' => $byProgram,
                'latest_thesis' => $latestThesis,
            ]
        ], 200);
    }

    /**
     * 4. GET AVAILABLE YEARS
     */
    public function years(): JsonResponse
    {
        $years = UndergraduateThesis::query()
            ->select('undergraduate_thesis_year')
            ->distinct()
            ->orderBy('undergraduate_thesis_year', 'desc')
            ->pluck('undergraduate_thesis_year')
            ->map(fn($year) => (int)$year)
            ->values();

        return response()->json([
            'status' => 'success',
            'message' => 'Daftar tahun tersedia',
            'data' => $years
        ], 200);
    }

    /**
     * 5. GET AVAILABLE PROGRAMS
     */
    public function programs(): JsonResponse
    {
        $programs = UndergraduateThesis::query()
            ->select('undergraduate_thesis_program')
            ->distinct()
            ->orderBy('undergraduate_thesis_program')
            ->pluck('undergraduate_thesis_program')
            ->values();

        return response()->json([
            'status' => 'success',
            'message' => 'Daftar program studi tersedia',
            'data' => $programs
        ], 200);
    }
}