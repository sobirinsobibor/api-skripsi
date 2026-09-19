<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UndergraduateThesis extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'undergraduate_theses';

    // Daftar kolom valid di database yang boleh di-select secara eksplisit
    public static array $allowedColumns = [
        'id',
        'undergraduate_thesis_title',
        'undergraduate_thesis_slug',
        'undergraduate_thesis_abstract',
        'undergraduate_thesis_research_proposal',
        'undergraduate_thesis_research_report',
        'undergraduate_thesis_full_text',
        'undergraduate_thesis_year',
        'undergraduate_thesis_program',
        'undergraduate_thesis_author',
        'undergraduate_thesis_author_id',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'undergraduate_thesis_title',
        'undergraduate_thesis_slug',
        'undergraduate_thesis_abstract',
        'undergraduate_thesis_research_proposal',
        'undergraduate_thesis_research_report',
        'undergraduate_thesis_full_text',
        'undergraduate_thesis_year',
        'undergraduate_thesis_program',
        'undergraduate_thesis_author',
        'undergraduate_thesis_author_id',
    ];
}