<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class WorkflowChecklist extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    public function task() {
        return $this->hasOne(ChecklistTask::class, 'workflow_checklist_id');
    }

    public function wftmp() {
        return $this->belongsTo(WorkflowTemplate::class, 'workflow_template_id');
    }

    public function wftmpasgmt() {
        return $this->belongsTo(WorkflowAssignment::class, 'workflow_assignment_id');
    }

    public function sec() {
        return $this->belongsTo(Section::class, 'section_id');
    }

    public function clist() {
        return $this->belongsTo(DynamicForm::class, 'checklist_id');
    }

    public function usr() {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function store() {
        return $this->belongsTo(Store::class, 'branch_id');
    }

    public function dept() {
        return $this->belongsTo(Department::class, 'branch_id');
    }
}
