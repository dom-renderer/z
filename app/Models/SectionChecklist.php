<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class SectionChecklist extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    public function checklist() {
        return $this->belongsTo(DynamicForm::class, 'checklist_id', 'id');
    }

    public function section() {
        return $this->belongsTo(Section::class, 'section_id', 'id');
    }
}
