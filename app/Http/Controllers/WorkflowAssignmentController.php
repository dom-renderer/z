<?php

namespace App\Http\Controllers;

use App\Models\ChecklistEscalation;
use App\Models\WorkflowAssignment;
use App\Models\ReservedEscalation;
use App\Models\WorkflowChecklist;
use App\Models\WorkflowTemplate;
use App\Models\ChecklistTask;
use Illuminate\Http\Request;
use App\Models\DynamicForm;
use App\Models\Section;
use Carbon\Carbon;

class WorkflowAssignmentController extends Controller
{
    public function index(Request $request) {
        if ($request->ajax()) {
            return datatables()
            ->eloquent(WorkflowAssignment::with(['template']))
            ->addColumn('wftemplate', function ($row) {
                return $row->template->name ?? '';
            })
            ->addColumn('comprate', function ($row) {
                $tasks = ChecklistTask::withTrashed()->whereHas('workflowclist', function ($builder) use ($row) {
                    $builder->withTrashed()
                    ->where('workflow_assignment_id', $row->id);
                })
                ->get();

                $final = $total = $filled = 0;

                foreach ($tasks as $task) {
                    $total += \App\Helpers\Helper::getCountHavingKey($task['form'] ?? [], 'name');
                    $filled += \App\Helpers\Helper::getCountHavingKey($task['data'] ?? [], 'name');                    
                }

                try {
                    if ($total > 0) {
                        $final = ($filled / $total) * 100;
                    }
                } catch (\Exception $e) {}

                return number_format($final, 2) . '%';
            })
            ->addColumn('action', function ($row) {
                $action = '';

                if (auth()->user()->can('workflow-assignments.show')) {
                    $action .= '<a href="'.route("workflow-assignments.show", encrypt($row->id)).'" class="btn btn-warning btn-sm me-2"> Show </a>';
                }

                if (auth()->user()->can('workflow-assignments.destroy')) {
                    $action .= '<form method="POST" action="'.route("workflow-assignments.destroy", encrypt($row->id)).'" style="display:inline;"><input type="hidden" name="_method" value="DELETE"><input type="hidden" name="_token" value="'.csrf_token().'"><button type="submit" class="btn btn-danger btn-sm deleteGroup">Delete</button></form>';
                }

                return $action;
            })
            ->rawColumns(['action'])
            ->toJson();
        }

        $page_title = 'Workflow Assignments';
        $page_description = 'Manage workflow assignments here';
        return view('workflow-assignments.index',compact('page_title', 'page_description'));
    }

    public function create() {
        $page_title = 'Workflow Assignments Add';

        return view('workflow-assignments.create', compact( 'page_title'));
    }

    public function store(Request $request) {

        $request->validate([
            'name' => 'required',
            'template' => 'required|exists:workflow_templates,id',
            'start' => 'required',
            'end' => 'required'
        ]);

        $start = date('Y-m-d H:i:s', strtotime($request->start));
        $end = date('Y-m-d H:i:s', strtotime($request->end));

        $workCompletionTimePeriod = Carbon::parse($start)->diffInMinutes(Carbon::parse($end));
        $checklistCompletionTimePeriod = 0;

        $allSec = $workflowJson = $allSections = [];
        $theTemplate = WorkflowTemplate::find($request->template);
        if (isset($theTemplate->section_id)) {
            $checklistHierarchy = Section::getDescendantsTree($theTemplate->section_id, true);

            if(!empty($checklistHierarchy)) {
                array_push($allSections, ...$checklistHierarchy['checklist_ids']);
                if (!empty($checklistHierarchy['children'])) {
                    self::getAllChildrenChecklistIds($checklistHierarchy['children'], $allSections);
                }

                array_push($allSec, ...array_fill(0, count($checklistHierarchy['checklist_ids']), $checklistHierarchy['id']));
                if (!empty($checklistHierarchy['children'])) {
                    self::getAllChildrenSectionIds($checklistHierarchy['children'], $allSec);
                }
            }
            /* Json creation */
            $workflowJson = Section::getTreeForWorkflow($theTemplate->section_id, true);
            /* Json creation */
        }

        if (!empty($allSections)) {
            $checklistCompletionTimePeriod = self::formatTime(DynamicForm::select('completion_time', 'completion_time_type')->whereIn('id', $allSections)->get(['completion_time', 'completion_time_type'])->toArray());
        }

        if ($checklistCompletionTimePeriod > $workCompletionTimePeriod) {
            return redirect()->back()->with('error','Minimum gap between two dates should be ' . self::minutesToHumanReadable($checklistCompletionTimePeriod))->withInput();
        }

        if (count($allSections) != count($allSec)) {
            \Log::critical('Error white assignment creation : checklists: ' . implode(',', $allSections) . ' and sections' . implode(',', $allSec));
            return redirect()->back()->with('error','Something went wrong! Please try again')->withInput();
        }

        \DB::beginTransaction();

        $createdWorkflow = WorkflowAssignment::create([
            'name' => $request->name,
            'workflow_id' => $request->template,
            'start_date' => $start,
            'end_date' => $end
        ]);

        $escalationStartDate = $upcomingDate = Carbon::parse($start);

        if (!empty($workflowJson)) {
            foreach ($allSections as $key => $dynamicForm) {
                $dynamicForm = DynamicForm::find($dynamicForm);

                if ($key) {
                    $dynamicFormMinusOne = DynamicForm::find($allSections[$key - 1]);
                    if ($dynamicFormMinusOne->completion_time_type == 0) {
                        $escalationStartDate = $upcomingDate = Carbon::parse($upcomingDate)->addMinutes($dynamicFormMinusOne->completion_time);
                    } else if ($dynamicFormMinusOne->completion_time_type == 1) {
                        $escalationStartDate = $upcomingDate = Carbon::parse($upcomingDate)->addHours($dynamicFormMinusOne->completion_time);
                    } else if ($dynamicFormMinusOne->completion_time_type == 2) {
                        $escalationStartDate = $upcomingDate = Carbon::parse($upcomingDate)->addDays($dynamicFormMinusOne->completion_time);
                    }
                }

                $createdTsk = ChecklistTask::create([
                    'type' => 1,
                    'workflow_id' => $createdWorkflow->id,
                    'section_id' => $allSec[$key],
                    'checklist_id' => $dynamicForm->id,
                    'code' => \App\Helpers\Helper::generateWorfklowTaskNumber(),
                    'date' => $upcomingDate,
                    'status' => 0,
                    'form' => $dynamicForm->schema
                ]);

                if ($dynamicForm->completion_time_type == 0) {
                    $escalationStartDate = Carbon::parse($upcomingDate)->addMinutes($dynamicForm->completion_time);
                } else if ($dynamicForm->completion_time_type == 1) {
                    $escalationStartDate = Carbon::parse($upcomingDate)->addHours($dynamicForm->completion_time);
                } else if ($dynamicForm->completion_time_type == 2) {
                    $escalationStartDate = Carbon::parse($upcomingDate)->addDays($dynamicForm->completion_time);
                }

                if (!$dynamicForm->escalations->isEmpty()) {
                    foreach ($dynamicForm->escalations->where('type', 0) as $key2 => $esc) {

                        if ($esc->time_type == 0) {
                            $escalationStartDate = Carbon::parse($escalationStartDate)->addMinutes($esc->time);
                        } else if ($esc->time_type == 1) {
                            $escalationStartDate = Carbon::parse($escalationStartDate)->addHours($esc->time);
                        } else if ($esc->time_type == 2) {
                            $escalationStartDate = Carbon::parse($escalationStartDate)->addDays($esc->time);
                        }

                        ReservedEscalation::create([
                            'escalation_id' => $esc->id,
                            'task_id' => $createdTsk->id,
                            'date' => $escalationStartDate
                        ]);
                    }
                }
            }
        }

        WorkflowAssignment::where('id', $createdWorkflow->id)->update([
            'workflow_json' => $workflowJson
        ]);

        \DB::commit();
    
        return redirect()->route('workflow-assignments.index')->with('success','Workflow assigned successfully');
    }

    private static function minutesToHumanReadable($minutes) {
        $days = floor($minutes / 1440);
        $hours = floor(($minutes % 1440) / 60);
        $remainingMinutes = $minutes % 60;

        $result = [];

        if ($days > 0) {
            $result[] = $days . ' day' . ($days > 1 ? 's' : '');
        }

        if ($hours > 0) {
            $result[] = $hours . ' hour' . ($hours > 1 ? 's' : '');
        }

        if ($remainingMinutes > 0) {
            $result[] = $remainingMinutes . ' minute' . ($remainingMinutes > 1 ? 's' : '');
        }

        return implode(' ', $result);
    }

    private static function formatTime($times = []) {
        $minutes = 0;

        foreach ($times as $time) {
            if ($time['completion_time'] > 0) {
                if ($time['completion_time_type'] == 0) {
                    $minutes += $time['completion_time'];
                } else if ($time['completion_time_type'] == 1) {
                    $minutes += ($time['completion_time'] * 60);
                } else if ($time['completion_time_type'] == 2) {
                    $minutes += ($time['completion_time'] * 60 * 24);
                }
            }
        }

        return $minutes;
    }

    private static function getAllChildrenChecklistIds($array = [], &$allSections) {
        foreach ($array as $value) {
            array_push($allSections, ...$value['checklist_ids']);
            if (!empty($value['children'])) {
                self::getAllChildrenChecklistIds($value['children'], $allSections);
            }
        }
    }

    private static function getAllChildrenSectionIds($array = [], &$allSec) {
        foreach ($array as $value) {
            array_push($allSec, ...array_fill(0, count($value['checklist_ids']), $value['id']));
            if (!empty($value['children'])) {
                self::getAllChildrenSectionIds($value['children'], $allSec);
            }
        }
    }

    public function show(Request $request, $id) {
        $page_title = 'Workflow Assignment Show';
        $assignment = WorkflowAssignment::find(decrypt($id));
    
        $allSectionNames = Section::withTrashed()->select('name', 'id')->pluck('name', 'id')->toArray();
        $allChecklistNames = DynamicForm::withTrashed()->select('name', 'id')->pluck('name', 'id')->toArray();
      
        $json = json_decode(json_encode($assignment->workflow_json), true);
      
        if (isset($json['id'])) {
          $json['name'] = isset($allSectionNames[$json['id']]) ? $allSectionNames[$json['id']] : '';
        }
      
        $tempArr = [];
        if (isset($json['checklist_ids'])) {
          foreach ($json['checklist_ids'] as &$row) {
            if (isset($row['id'])) {
              $temp = $row['id'];
              $tempArr[] = $temp;
              $row = [
                'id' => $temp,
                'name' => isset($allChecklistNames[$temp]) ? $allChecklistNames[$temp] : ''
              ];
            }
          }
          $json['className'] = Section::$classNames[array_rand(Section::$classNames)];
          $json['checklist_title'] = self::arrayToHtmlListWithPercentage($tempArr, $json['id'], $assignment);
        }
        
        if (is_array($json) && array_key_exists('name', $json)) {
            $json['name'] .= (' - ' . self::getSectionPercentage($tempArr, $json['id'], $assignment) . '%');
        }

        $tempArr = [];
      
        if (isset($json['children']) && !empty($json['children'])) {
          self::recursive($json['children'], $allSectionNames, $allChecklistNames, $assignment);
        }

        return view('workflow-assignments.show', compact('assignment', 'page_title', 'json'));
    }

    public static function arrayToHtmlListWithPercentage($array, $sId, $assignment) {
        if (!is_array($array)) {
            return '';
        }
  
        $html = '<ul>';
        $array = DynamicForm::withTrashed()->select('id', 'name')->whereIn('id', $array)->pluck('name', 'id')->toArray();
  
        foreach ($array as $cId => $item) {
            $totalTasksOfSectionOfWorkflow = ChecklistTask::withTrashed()
            ->where('type', 1)
            ->whereIn('workflow_checklist_id', $assignment->specificclist->pluck('id')->toArray())
            ->whereHas('workflowclist.clist', function ($builder) use ($cId) {
                return $builder->where('id', $cId);
            })
            ->whereHas('workflowclist.sec' , function ($builder) use ($sId) {
                return $builder->where('id', $sId);
            })
            ->get()
            ->toArray();
            
            $total = $filled = $final = 0;
            
            foreach ($totalTasksOfSectionOfWorkflow as $totalTasksOfSectionOfWorkflowRow) {
                $total += \App\Helpers\Helper::getCountHavingKey($totalTasksOfSectionOfWorkflowRow['form'] ?? [], 'name');
                $filled += \App\Helpers\Helper::getCountHavingKey($totalTasksOfSectionOfWorkflowRow['data'] ?? [], 'name');
            }
  
            try {
                if ($total > 0) {
                    $final = ($filled / $total) * 100;
                }
            } catch (\Exception $e) {}
  
            $html .= '<li>' . htmlspecialchars($item) . ' - <strong> ' . number_format($final, 2) . ' % </strong> </li>';
        }
        
        $html .= '</ul>';
        
        return $html;
    }

    public static function getSectionPercentage($array, $sId, $assignment) {
        $final = $filled = $total = 0;

        foreach ($array as $cId) {
            $totalTasksOfSectionOfWorkflow = ChecklistTask::withTrashed()
            ->where('type', 1)
            ->whereIn('workflow_checklist_id', $assignment->specificclist->pluck('id')->toArray())
            ->whereHas('workflowclist.clist', function ($builder) use ($cId) {
                return $builder->where('id', $cId);
            })
            ->whereHas('workflowclist.sec' , function ($builder) use ($sId) {
                return $builder->where('id', $sId);
            })
            ->get()
            ->toArray();

            foreach ($totalTasksOfSectionOfWorkflow as $totalTasksOfSectionOfWorkflowRow) {
                $total += \App\Helpers\Helper::getCountHavingKey($totalTasksOfSectionOfWorkflowRow['form'] ?? [], 'name');
                $filled += \App\Helpers\Helper::getCountHavingKey($totalTasksOfSectionOfWorkflowRow['data'] ?? [], 'name');
            }  
        }
       
        try {
            if ($total > 0) {
                $final = ($filled / $total) * 100;
            }
        } catch (\Exception $e) {}

        return number_format($final, 2);
    }

    public static function recursive(&$data, $allSectionNames = [], $allChecklistNames = [], $assignment) {
        foreach ($data as &$row) {
          if (isset($row['id'])) {
            $row['name'] = isset($allSectionNames[$row['id']]) ? $allSectionNames[$row['id']] : '';
          }
          
          $tempArr = [];
          if (isset($row['checklist_ids'])) {
            foreach ($row['checklist_ids'] as &$r) {
              if (isset($r['id'])) {
                $temp = $r['id'];
                $tempArr[] = $temp;
                $r = [
                  'id' => $temp,
                  'name' => isset($allChecklistNames[$temp]) ? $allChecklistNames[$temp] : ''
                ];
              }
            }
            $row['className'] = Section::$classNames[array_rand(Section::$classNames)];
            $row['checklist_title'] = self::arrayToHtmlListWithPercentage($tempArr, $row['id'], $assignment);
        }
        if (array_key_exists('name', $row)) {
            $row['name'] .= (' - ' . self::getSectionPercentage($tempArr, $row['id'], $assignment) . '%');
        }

        $tempArr = [];
    
          if (isset($row['children']) && !empty($row['children'])) {
            self::recursive($row['children'], $allSectionNames, $allChecklistNames, $assignment);
          }
        }
      }

    public function destroy(Request $request, $id) {
        $department = WorkflowAssignment::find(decrypt($id));
        $department->delete();
        
        return redirect()->route('workflow-assignments.index')->with('success','Workflow assignment removed successfully');
    }

    public function taskList(Request $request) {
        if ($request->ajax()) {
            $tasks = ChecklistTask::workflows()
            ->with([
                'workflowclist' => function ($builder) {
                    return $builder->withTrashed();
                },
                'workflowclist.wftmp' => function ($builder) {
                    return $builder->withTrashed();
                },
                'workflowclist.sec' => function ($builder) {
                    return $builder->withTrashed();
                },
                'workflowclist.wftmpasgmt' => function ($builder) {
                    return $builder->withTrashed();
                },
                'workflowclist.clist' => function ($builder) {
                    return $builder->withTrashed();
                },
                'workflowclist.usr' => function ($builder) {
                    return $builder->withTrashed();
                },
                'workflowclist.store' => function ($builder) {
                    return $builder->withTrashed();
                },
                'workflowclist.dept' => function ($builder) {
                    return $builder->withTrashed();
                }
            ])
            ->when(!empty($request->user), function ($builder) {
                return $builder->whereHas('workflowclist', function ($innerBuilder) {
                    $innerBuilder->whereIn('user_id', is_string(request('user')) ? explode(',', request('user')) : (is_array(request('user')) ? request('user') : []));
                });
            })
            ->when(!empty($request->checklist), function ($builder) {
                return $builder->whereHas('workflowclist', function ($innerBuilder) {
                    $innerBuilder->whereIn('checklist_id', is_string(request('checklist')) ? explode(',', request('checklist')) : (is_array(request('checklist')) ? request('checklist') : []));
                });
            })
            ->when(!empty($request->from), function ($builder) {
                return $builder->where(\DB::raw("DATE_FORMAT(date, '%Y-%m-%d')"), '>=', date('Y-m-d', strtotime(request('from'))));
            })
            ->when(!empty($request->to), function ($builder) {
                return $builder->where(\DB::raw("DATE_FORMAT(date, '%Y-%m-%d')"), '<=', date('Y-m-d', strtotime(request('to'))));
            })
            ->when($request->status === '0' || $request->status === '1' || $request->status === '2', function ($builder) {
                $status = request('status');
                if ($status == 2) {
                    $status = 3;
                }

                return $builder->where('status', $status);
            })
            ->when(!empty($request->asgmt), function ($builder) {
                return $builder->whereHas('workflowclist', function ($innerBuilder) {
                    $innerBuilder->whereIn('workflow_assignment_id', is_string(request('asgmt')) ? explode(',', request('asgmt')) : (is_array(request('asgmt')) ? request('asgmt') : []));
                });
            })
            ->when(!empty($request->section), function ($builder) {
                return $builder->whereHas('workflowclist', function ($innerBuilder) {
                    $innerBuilder->whereIn('section_id', is_string(request('section')) ? explode(',', request('section')) : (is_array(request('section')) ? request('section') : []));
                });
            })
            ->orderBy('created_at', 'DESC');

            return datatables()
            ->eloquent($tasks)
            ->editColumn('date', function ($row) {
                return date('d-m-Y H:i', strtotime($row->date));
            })
            ->addColumn('waname', function ($row) {
                return $row->workflowclist->wftmpasgmt->name ?? '';
            })
            ->addColumn('sname', function ($row) {
                return $row->workflowclist->sec->name ?? '';
            })
            ->addColumn('cname', function ($row) {
                return $row->workflowclist->clist->name ?? '';
            })
            ->addColumn('usr', function ($row) {
                return $row->workflowclist->usr->name ?? '';
            })
            ->addColumn('comprate', function ($row) {
                $final = 0;
                $total = \App\Helpers\Helper::getCountHavingKey($row->form ?? [], 'name');
                $filled = \App\Helpers\Helper::getCountHavingKey($row->data ?? [], 'name');                    

                try {
                    $final = ($filled / $total) * 100;
                } catch (\Exception $e) {}

                return number_format($final, 2) . '%';
            })
            ->addColumn('status', function ($row) {
                if ($row->status == 0) {
                    return '<span class="badge bg-warning">Pending</span>';
                } else if ($row->status == 1) {
                    return '<span class="badge bg-info">In-Progress</span>';
                } else {
                    return '<span class="badge bg-success">Completed</span>';
                }
            })
            ->addColumn('action', function ($row) {
                $action = '';

                if (in_array($row->status, [1, 2, 3]) && !empty($row->data) && auth()->user()->can('workflow-assignments.tasks-view')) {
                    $action .= '<a href="'.route("workflow-assignments.tasks-view", encrypt($row->id)).'" class="btn btn-warning btn-sm me-2"> Show </a>';
                    $action .= '<a href="'.route("task-export-excel", $row->id).'" class="btn btn-success btn-sm me-2"> Excel </a>';
                    $action .= '<a href="'.route("task-export-pdf", $row->id).'" class="btn btn-danger btn-sm me-2"> PDF </a>';
                }

                return $action;
            })
            ->rawColumns(['status', 'action'])
            ->toJson();
        }

        $page_title = 'Workflow Assignments Tasks';

        return view('workflow-assignments.task-list', compact('page_title'));
    }

    public function taskView(Request $request, $id) {
        $task = ChecklistTask::where('id', decrypt($id))
        ->with([
            'workflowclist' => function ($builder) {
                return $builder->withTrashed();
            },
            'workflowclist.wftmp' => function ($builder) {
                return $builder->withTrashed();
            },
            'workflowclist.sec' => function ($builder) {
                return $builder->withTrashed();
            },
            'workflowclist.wftmpasgmt' => function ($builder) {
                return $builder->withTrashed();
            },
            'workflowclist.clist' => function ($builder) {
                return $builder->withTrashed();
            },
            'workflowclist.usr' => function ($builder) {
                return $builder->withTrashed();
            },
            'workflowclist.store' => function ($builder) {
                return $builder->withTrashed();
            },
            'workflowclist.dept' => function ($builder) {
                return $builder->withTrashed();
            }
        ])->first();

        $page_title = 'Task - ' . $task->code;

        return view('workflow-assignments.view-task', compact('page_title', 'task'));
    }

    public function templateForConfiguration(Request $request) {
        $template = WorkflowTemplate::find($request->id);

        if ($template) {

            $allSections = $allChecklists = [];
            if (isset($template->section_id)) {
                $checklistHierarchy = Section::getDescendantsTree($template->section_id, true);
    
                if(!empty($checklistHierarchy)) {
                    array_push($allChecklists, ...$checklistHierarchy['checklist_ids']);
                    if (!empty($checklistHierarchy['children'])) {
                        self::getAllChildrenChecklistIds($checklistHierarchy['children'], $allChecklists);
                    }
    
                    array_push($allSections, ...array_fill(0, count($checklistHierarchy['checklist_ids']), $checklistHierarchy['id']));
                    if (!empty($checklistHierarchy['children'])) {
                        self::getAllChildrenSectionIds($checklistHierarchy['children'], $allSections);
                    }
                }
            }

            if (count($allChecklists) > 0 && count($allSections) == count($allChecklists)) {
                $allSectionLabels = Section::select('id', 'name')->whereIn('id', $allSections)->pluck('name', 'id')->toArray();
                $allChecklistLabels = DynamicForm::select('id', 'name')->whereIn('id', $allChecklists)->pluck('name', 'id')->toArray();

                return response()->json(['status' => true, 'html' => view('workflow-assignments.configuration', compact('allChecklists', 'allSections', 'allSectionLabels', 'allChecklistLabels'))->render()]);
            }
        }

        return response()->json(['status' => false, 'message' => 'Couldn\'t found the selected template.']);
    }

    public function saveConfiguredTemplate(Request $request) {
        $template = WorkflowTemplate::find($request->template);
        $workflowJson = Section::getTreeForWorkflow($template->section_id, true);

        if ($template) {
            $start = date('Y-m-d H:i:s', strtotime($request->start));
            $end = date('Y-m-d H:i:s', strtotime($request->end));
            
            $workCompletionTimePeriod = Carbon::parse($start)->diffInMinutes(Carbon::parse($end));
            $checklistCompletionTimePeriod = 0;

            foreach ($request->template_time_type as $key => $type) {
                if ($type > 0 && isset($request->template_time[$key])) {
                    if ($type == 0) {
                        $checklistCompletionTimePeriod += $request->template_time[$key];
                    } else if ($type == 1) {
                        $checklistCompletionTimePeriod += ($request->template_time[$key] * 60);
                    } else if ($type == 2) {
                        $checklistCompletionTimePeriod += ($request->template_time[$key] * 60 * 24);
                    }
                }
            }

            if ($checklistCompletionTimePeriod > $workCompletionTimePeriod) {
                return response()->json(['status' => false, 'message' => 'Minimum gap between two dates should be ' . self::minutesToHumanReadable($checklistCompletionTimePeriod)]);
            }

            $escalationStartDate = $upcomingDate = Carbon::parse($start);
            $allSections = $request->template_section;
            $allChecklists = $request->template_checklist;
            $allTimeTypes = $request->template_time_type;
            $allTimes = $request->template_time;
            $allBranchTypes = $request->template_branch_type;
            $allBranch = $request->template_branch;
            $allUsers = $request->template_user;
            $allEscalations = $request->template_tat;
            $allCompletions = $request->template_ctat;

            \DB::beginTransaction();

            try {
                $createdWorkflow = WorkflowAssignment::create([
                    'name' => $request->name,
                    'workflow_id' => $request->template,
                    'start_date' => $start,
                    'end_date' => $end
                ]);

                foreach ($allSections as $index => $thisSec) {
                    $thisDynamicForm = DynamicForm::withTrashed()->where('id', $allChecklists[$index])->first();

                    if ($index && isset($allTimeTypes[$index - 1])) {
                        if ($allTimeTypes[$index - 1] == 0) {
                            $escalationStartDate = $upcomingDate = Carbon::parse($upcomingDate)->addMinutes($allTimes[$index - 1]);
                        } else if ($allTimeTypes[$index - 1] == 1) {
                            $escalationStartDate = $upcomingDate = Carbon::parse($upcomingDate)->addHours($allTimes[$index - 1]);
                        } else if ($allTimeTypes[$index - 1] == 2) {
                            $escalationStartDate = $upcomingDate = Carbon::parse($upcomingDate)->addDays($allTimes[$index - 1]);
                        }
                    }

                    $createdWorkflowChecklist = WorkflowChecklist::create([
                        'workflow_template_id' => $request->template,
                        'workflow_assignment_id' => $createdWorkflow->id,
                        'section_id' => $thisSec,
                        'checklist_id' => $allChecklists[$index],
                        'branch_type' => $allBranchTypes[$index],
                        'branch_id' => $allBranch[$index],
                        'user_id' => $allUsers[$index],
                        'completion_time' => $allTimes[$index],
                        'completion_time_type' => $allTimeTypes[$index]
                    ]);

                    $createdTsk = ChecklistTask::create([
                        'type' => 1,
                        'workflow_checklist_id' => $createdWorkflowChecklist->id,
                        'code' => \App\Helpers\Helper::generateWorfklowTaskNumber(),
                        'date' => $upcomingDate,
                        'form' => $thisDynamicForm->schema ?? null,
                        'status' => 0
                    ]);

                    if (isset($allCompletions[$index]) && is_string($allCompletions[$index])) {
                        $completionDecoded = json_decode($allCompletions[$index]);

                        ChecklistEscalation::create([
                            'type' => 1,
                            'workflow_checklist_id' => $createdWorkflowChecklist->id,
                            'time' => 0,
                            'time_type' => 0,
                            'templates' => array_merge($completionDecoded->mail_templates, $completionDecoded->pn_templates),
                            'level' => 0
                        ]);
                    }

                    if ($allTimeTypes[$index] == 0) {
                        $escalationStartDate = Carbon::parse($upcomingDate)->addMinutes($allTimes[$index]);
                    } else if ($allTimeTypes[$index] == 1) {
                        $escalationStartDate = Carbon::parse($upcomingDate)->addHours($allTimes[$index]);
                    } else if ($allTimeTypes[$index] == 2) {
                        $escalationStartDate = Carbon::parse($upcomingDate)->addDays($allTimes[$index]);
                    }

                    if (!empty($allEscalations[$index]) && is_string($allEscalations[$index])) {
                        $escalation = json_decode($allEscalations[$index]);

                        if (is_countable((array)$escalation) && count((array)$escalation) > 0) {
                            $level = 0;
                            foreach ($escalation as $level => $row) {

                                if ($row->time_type == 0) {
                                    $escalationStartDate = Carbon::parse($escalationStartDate)->addMinutes($row->time_type);
                                } else if ($row->time_type == 1) {
                                    $escalationStartDate = Carbon::parse($escalationStartDate)->addHours($row->time_type);
                                } else if ($row->time_type == 2) {
                                    $escalationStartDate = Carbon::parse($escalationStartDate)->addDays($row->time_type);
                                }

                                $thisEscalationChecklist = ChecklistEscalation::create([
                                    'workflow_checklist_id' => $createdWorkflowChecklist->id,
                                    'time' => $row->time,
                                    'time_type' => $row->time_type,
                                    'templates' => array_merge($row->mail_templates, $row->pn_templates),
                                    'level' => $level + 1
                                ]);

                                ReservedEscalation::create([
                                    'escalation_id' => $thisEscalationChecklist->id,
                                    'task_id' => $createdTsk->id,
                                    'date' => $escalationStartDate
                                ]);
                            }
                        }
                    }
                }

                WorkflowAssignment::where('id', $createdWorkflow->id)->update([
                    'workflow_json' => $workflowJson
                ]);

            } catch (\Exception $e) {
                \DB::rollBack();
                \Log::error('WORKFLOW ASSIGNMENT CREATION : ' . $e->getMessage());
                return response()->json(['status' => false, 'message' => 'Something went wrong please try again later.']);
            }

            \DB::commit();
            return response()->json(['status' => true, 'message' => 'Workflow Assignment created Successfully.']);
        }

        return response()->json(['status' => false, 'message' => 'Couldn\'t found the selected template.']);
    }

    public function assignmentLists(Request $request) {
        $queryString = trim($request->searchQuery);
        $page = $request->input('page', 1);
        $limit = 10;
    
        $query = WorkflowAssignment::query();
    
        if (!empty($queryString)) {
            $query->where('name', 'LIKE', "%{$queryString}%");
        }
    
        $data = $query->paginate($limit, ['*'], 'page', $page);
    
        return response()->json([
            'items' => $data->map(function ($item) {
                return [
                    'id' => $item->id,
                    'text' => $item->name
                ];
            }),
            'pagination' => [
                'more' => $data->hasMorePages()
            ]
        ]);
    }
}
