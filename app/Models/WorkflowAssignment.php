<?php

namespace App\Models;

use Google\Service\Workflows\Workflow;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class WorkflowAssignment extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'workflow_json' => 'object'
    ];

    public function template() {
        return $this->belongsTo(WorkflowTemplate::class, 'workflow_id', 'id');
    }

    public function specificclist() {
        return $this->hasMany(WorkflowChecklist::class, 'workflow_assignment_id', 'id');
    }
}
