<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UndergraduateThesisResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->undergraduate_thesis_title,
            'slug' => $this->undergraduate_thesis_slug,
            'abstract' => $this->undergraduate_thesis_abstract,
            'research_proposal' => $this->undergraduate_thesis_research_proposal,
            'research_report' => $this->undergraduate_thesis_research_report,
            'full_text' => $this->undergraduate_thesis_full_text,
            'year' => $this->undergraduate_thesis_year,
            'program' => $this->undergraduate_thesis_program,
            'author' => $this->undergraduate_thesis_author,
            'author_id' => $this->undergraduate_thesis_author_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}