<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class WorkflowTemplate extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    public function sectionwclist() {
        return $this->belongsTo(SectionChecklist::class, 'section_id', 'section_id');
    }

    public function section() {
        return $this->belongsTo(Section::class, 'section_id', 'id');
    }
}
