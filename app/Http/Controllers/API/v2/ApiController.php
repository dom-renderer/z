<?php

namespace App\Http\Controllers\Api\v2;

use App\Http\Controllers\API\ApiController as v1;
use Illuminate\Support\Facades\Validator;
use App\Models\TaskDeviceInformation;
use App\Models\NonAuthorizedMaterial;
use App\Models\PhysicalClosingStock;
use App\Models\FranchisorFeedback;
use Illuminate\Support\Facades\DB;
use App\Models\ProductCategory;
use App\Models\TicketAttachment;
use App\Models\RescheduledTask;
use App\Models\ExpiryMaterial;
use \Illuminate\Support\Str;
use App\Models\ChecklistTask;
use Illuminate\Http\Request;
use App\Models\RedoAction;
use App\Models\AuditRemark;
use App\Helpers\Helper;
use App\Models\Product;
use App\Models\Ticket;
use Carbon\Carbon;

class ApiController extends \App\Http\Controllers\Controller
{
    public function submission(Request $request) {
        DB::beginTransaction();

        try {
            TaskDeviceInformation::create([
                'eloquent' => ChecklistTask::class,
                'eloquent_id' => $request->task_id,
                'user_id' => auth()->check() ? auth()->user()->id : null,
                'device_model' => $request->device_model,
                'network_speed' => $request->network_speed,
                'device_version' => $request->device_version
            ]);

            $validator = Validator::make($request->all(), [
                'task_id' => 'required|exists:checklist_tasks,id',
                'status' => 'required|in:1,2',
                'type' => 'required|in:1,2',//1 = Full JSON | 2 = Partial JSON
                'data' => 'required'
            ]);

            if ($validator->fails()) { 
                $errorString = implode(",",$validator->messages()->all());
                return response()->json(['error' => $errorString], 401);
            }

            $task = ChecklistTask::findOrFail($request->task_id);

            if ($task->status == Helper::$status['in-verification']) {
                return response()->json(['error' => 'This Checklist already submitted.']);
            }

            if (!file_exists(storage_path('app/public/workflow-task-uploads'))) {
                mkdir(storage_path('app/public/workflow-task-uploads'), 0777, true);
            }

            if (is_string($request->data) && $request->data != "NONE") {
                $data = json_decode($request->data, true);
            } else {
                $data = $request->data;
            }

            $filesToBeRemoved = [];
            $currentJson = $task->data;

            if ($request->data != "NONE") {
                if ($request->type == 2) {
                    if (empty($currentJson)) {
                        $currentJson = [];
                    }

                    foreach ($data as $row) {
                        if (self::hasValueByName($currentJson, $row['name'])) {
                            foreach ($currentJson as &$item) {
                                if (isset($item->name) && $item->name === $row['name']) {

                                    if (property_exists($item, 'isFile') &&  $item->isFile) {
                                        if (is_array($item->value)) {
                                            foreach ($item->value as $fileVal) {
                                                if (!empty($fileVal) && !Str::contains($fileVal, '|to_be_generated.png') && is_file(storage_path("app/public/workflow-task-uploads/{$fileVal}"))) {
                                                    $fileDoesExists = false;
                                                    if (is_array($row['value'])) {
                                                        foreach ($row['value'] as $rw) {
                                                            if ($rw == $fileVal) {
                                                                $fileDoesExists = true;
                                                                continue;
                                                            }
                                                        }
                                                    } else if (is_string($row['value'])) {
                                                        if ($row['value'] == $item->value) {
                                                                $fileDoesExists = true;
                                                        }
                                                    }

                                                    if ($fileDoesExists === false) {
                                                        $filesToBeRemoved[] = storage_path("app/public/workflow-task-uploads/{$fileVal}");
                                                    }
                                                }
                                            }
                                        } else if (is_string($item->value)) {
                                            if (!empty($item->value) && !Str::contains($item->value, '|to_be_generated.png') && is_file(storage_path("app/public/workflow-task-uploads/{$item->value}"))) {
                                                $fileDoesExists = false;
                                                
                                                if (is_array($row['value'])) {
                                                    foreach ($row['value'] as $rw) {
                                                        if ($rw == $item->value) {
                                                            $fileDoesExists = true;
                                                            continue;
                                                        }
                                                    }
                                                } else if (is_string($row['value'])) {
                                                    if ($row['value'] == $item->value) {
                                                            $fileDoesExists = true;
                                                    }
                                                }

                                                if ($fileDoesExists === false) {
                                                    $filesToBeRemoved[] = storage_path("app/public/workflow-task-uploads/{$item->value}");
                                                }
                                            }
                                        }
                                    }

                                    if ($row['isFile'] && is_array($row['value'])) {
                                        $finalImgArrObj = [];
                                        foreach ($row['value'] as $tfov) {
                                            if (!Str::contains($tfov, '|to_be_generated.png')) {
                                                $finalImgArrObj[] = $tfov;
                                            }
                                        }

                                        $row['value'] = $finalImgArrObj;
                                    } else if ($row['isFile'] && is_string($row['value'])) {
                                        if (!Str::contains($row['value'], '|to_be_generated.png')) {
                                            $finalImgArrObj = [$row['value']];
                                        }

                                        $row['value'] = $finalImgArrObj;
                                    }

                                    $item->value = $row['value'];

                                    if (property_exists($item, 'value_label') && isset($row['value_label'])) {
                                        $item->value_label = $row['value_label'];
                                    }

                                    continue;
                                }
                            }
                        } else {
                            if (!is_array($currentJson) && ($currentJson == '{}' || empty($currentJson))) {
                                $currentJson = [];
                            } else if (is_object($currentJson)) {
                                $currentJson = (array) $currentJson;
                            }

                            if (isset($row) && array_key_exists('isFile', $row)) {
                                if (is_array($row['value'])) {
                                    $finalImgArr = [];
                                    foreach ($row['value'] as $thisFileRow) {
                                        if (!Str::contains($thisFileRow, '|to_be_generated.png')) {
                                            $finalImgArr[] = $thisFileRow;
                                        }
                                    }

                                    $row['value'] = $finalImgArr;
                                } else if (is_string($row['value'])) {
                                    if (Str::contains($row['value'], '|to_be_generated.png')) {
                                        $row['value'] = [];
                                    }
                                }
                            }

                            array_push($currentJson, $row);
                        }
                    }

                    foreach ($currentJson as &$item) {
                        if (is_array($item)) {
                            $item = (object) $item;
                        }
                    }
                    unset($item);

                    usort($currentJson, function ($a, $b) {
                        $pageComparison = (int)$a->page <=> (int)$b->page;
                        
                        if ($pageComparison === 0) {
                            $aIndex = isset($a->index) ? (int)$a->index : PHP_INT_MAX;
                            $bIndex = isset($b->index) ? (int)$b->index : PHP_INT_MAX;
                            
                            return $aIndex <=> $bIndex;
                        }

                        return $pageComparison;
                    });

                    $task->data = $currentJson;

                } else {
                    foreach ($data as &$dt) {
                        if (array_key_exists('isFile', $dt) && $dt['isFile'] == true) {
                            if (is_array($dt['value'])) {
                                foreach ($dt['value'] as &$tempRow) {
                                    if (strpos($tempRow, 'SIGN-20') !== false) {
                                        continue;
                                    }
                                    $tempRow = Helper::downloadBase64File($tempRow, ('SIGN-' . date('YmdHis') . uniqid()), storage_path('app/public/workflow-task-uploads'));
                                }
                            } else {
                                if (strpos($dt['value'], 'SIGN-20') !== false) {
                                    continue;
                                }
                                $dt['value'] = Helper::downloadBase64File($dt['value'], ('SIGN-' . date('YmdHis') . uniqid()), storage_path('app/public/workflow-task-uploads'));
                            }
                        }
                    }

                    $task->data = $data;
                }
            }

            if ($task->type == 0 && isset($task->parent->parent->checker_user_id)) {
                $task->status = $request->status;

                if ($request->status == Helper::$status['in-verification']) {
                    \App\Jobs\DisapproveNoAnsweredFields::dispatch($task->id);
                }
            } else {
                if ($request->status == Helper::$status['in-verification']) {
                    $task->status = Helper::$status['completed'];
                    $task->completion_date = now();
                } else {
                    $task->status = $request->status;
                }
            }

            if ($task->type == 0 && $request->status == Helper::$status['in-verification']) {
                v1::dispatchNotifications($task);
            }

            if (empty($task->started_at)) {
                if (!empty($request->starting_date)) {
                    $task->started_at = date('Y-m-d H:i:s', strtotime($request->starting_date));
                } else {
                    $task->started_at = now();
                }
            }

            $task->save();

            if ($request->status == Helper::$status['in-verification']) {
                $task = ChecklistTask::find($request->task_id);
                $task->completion_date = now();

                if ($task->type == 1) {
                    $task->status = Helper::$status['completed'];
                }

                $task->save();
            }

            if ($task->status == 2 || $task->status == 3) {
                \App\Jobs\GenerateOptimizedTaskPdf::dispatch($task->id);
            }

            if (!empty($filesToBeRemoved)) {
                foreach ($filesToBeRemoved as $filesToBeRemovedFile) {
                    if (is_file($filesToBeRemovedFile)) {
                        //keep for logs
                        // unlink($filesToBeRemovedFile);
                    }
                }            
            }

            // STATIC FORM SUBMISSION

            ExpiryMaterial::where('task_id', $task->id)->delete();
            NonAuthorizedMaterial::where('task_id', $task->id)->delete();
            FranchisorFeedback::where('task_id', $task->id)->delete();
            PhysicalClosingStock::where('task_id', $task->id)->delete();
            AuditRemark::where('task_id', $task->id)->delete();
            
            if ($request->has('EXPITY_MATERIAL_FORM') && is_array($request->EXPITY_MATERIAL_FORM)) {
                foreach ($request->EXPITY_MATERIAL_FORM as $row) {
                    ExpiryMaterial::create($row);
                }
            }

            if ($request->has('NON_AUTHORIZED_MATERIAL_FORM') && is_array($request->NON_AUTHORIZED_MATERIAL_FORM)) {
                foreach ($request->NON_AUTHORIZED_MATERIAL_FORM as $row) {
                    NonAuthorizedMaterial::create($row);
                }
            }

            if ($request->has('FRANCHISOR_FEEDBACK_SUMMARY_FORM') && is_array($request->FRANCHISOR_FEEDBACK_SUMMARY_FORM)) {
                foreach ($request->FRANCHISOR_FEEDBACK_SUMMARY_FORM as $row) {
                    FranchisorFeedback::create($row);
                }
            }
            
            if ($request->has('PHYSICAL_CLSOING_STOCK_FORM') && is_array($request->PHYSICAL_CLSOING_STOCK_FORM)) {
                foreach ($request->PHYSICAL_CLSOING_STOCK_FORM as $row) {
                    PhysicalClosingStock::create($row);
                }
            }
            
            if ($request->has('AUDIT_SUMMARY') && is_array($request->AUDIT_SUMMARY)) {
                foreach ($request->AUDIT_SUMMARY as $row) {
                    AuditRemark::create($row);
                }
            }

            // STATIC FORM SUBMISSION

            DB::commit();
            return response()->json(['success' => 'Checklist submitted successfully.', 'data' => $data]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Server is busy at the moment. Please try again later!', 'exception_message' => $e->getMessage(), 'exception_line' => $e->getLine()]);
        }
    }

    public static function hasValueByName($items, $targetName) {
        foreach ($items as $item) {
            if (isset($item->name) && $item->name === $targetName) {
                return !empty($item->name);
            }
        }
        return false;
    }

    public function tasks(Request $request) {
        $page = $request->page > -1 ? $request->page : 0;
        $perPage = $request->record_per_page > 0 ? $request->record_per_page : 5;
        $skip = $page * $perPage;

        $filterCompending = $request->status;
        $filterFrom = date('Y-m-d H:i:s', strtotime($request->from));
        $filterTo = date('Y-m-d H:i:s', strtotime($request->to));

        $tasks = ChecklistTask::with(['restasks', 'submissionentries', 'redos', 'parent.parent', 'parent.user' => function ($builder) {
            return $builder->withTrashed();
        }])
        ->when(auth()->check(), function ($inBldr) {
            $inBldr->where(function ($innerBuilder) {
                $innerBuilder->whereHas('parent.parent', function ($query) {
                    $query->where('checker_user_id', auth()->user()->id);
                })
                ->orWhereHas('parent', function ($query) {
                    $query->where('user_id', auth()->user()->id);
                });
            })
            ->when(is_numeric(request('current_store_id')) && request('current_store_id') > 0, function ($builder) {
                $builder->whereHas('parent.actstore', function ($query) {
                    $query->where('id', request('current_store_id'));
                });
            });
        })
        ->when($request->showCancelled == 1, function ($builder) {
            return $builder->where('cancelled', 1);
        })
        ->when($request->showCancelled == 2, function ($builder) {
            return $builder->where('cancelled', 0);
        })
        ->when($filterCompending == 1, function ($builder) {
            return $builder->where('status', 0);
        })
        ->when($filterCompending == 2, function ($builder) {
            return $builder->where('status', 1);
        })
        ->when($filterCompending == 3, function ($builder) {
            return $builder->where('status', 2);
        })
        ->when($filterCompending == 4, function ($builder) {
            return $builder->where('status', 3);
        })
        
        ->when($request->task_type == 1, function ($builder) {
            $builder->whereHas('parent', function ($query) {
                $query->where('user_id', auth()->user()->id);
            });
        })
        ->when($request->task_type == 1 && in_array(request('filter_status'), ['PENDING', 'IN_PROGRESS', 'PENDING_VERIFICATION', 'VERIFIED', 'COMPLETED']), function ($builder) {
            if (request('filter_status') == 'PENDING') {
                $builder->where('status', 0);
            } else if (request('filter_status') == 'IN_PROGRESS') {
                $builder->where('status', 1);
            } else if (request('filter_status') == 'PENDING_VERIFICATION') {
                $builder->where('status', 2);
            } else if (request('filter_status') == 'VERIFIED') {
                $builder->where('status', 3)
                ->whereHas('parent.parent', function ($query) {
                    $query->where('checker_user_id', '>', 0);
                });
            } else if (request('filter_status') == 'COMPLETED') {
                $builder->where('status', 3)
                ->whereHas('parent.parent', function ($query) {
                    $query->whereNull('checker_user_id');
                });
            }
        })


        ->when($request->task_type == 2, function ($builder) {
            $builder->whereHas('parent.parent', function ($query) {
                $query->where('checker_user_id', auth()->user()->id);
            });
        })

        ->when($request->task_type == 2 && in_array(request('filter_status'), ['PENDING_VERIFICATION', 'REASSIGNED', 'VERIFYING', 'VERIFIED']), function ($builder) {
            if (request('filter_status') == 'PENDING_VERIFICATION') {
                $builder->where('status', 2)
                ->whereDoesntHave('redos', function ($query) {
                    $query->where('status', [0,1]);
                });
            } else if (request('filter_status') == 'REASSIGNED') {
                $builder->where('status', 2)
                ->whereDoesntHave('redos', function ($query) {
                    $query->where('status', 1);
                })
                ->whereHas('redos', function ($query) {
                    $query->where('status', 0);
                });                
            } else if (request('filter_status') == 'VERIFYING') {
                $builder->where('status', 2)
                ->whereHas('redos', function ($query) {
                    $query->where('status', 1);
                });
            } else if (request('filter_status') == 'VERIFIED') {
                $builder->where('status', 3);
            }
        })

        ->when($request->task_type == 2 && !in_array(request('filter_status'), ['PENDING_VERIFICATION', 'REASSIGNED', 'VERIFYING', 'VERIFIED']), function ($builder) {
            $builder->where('status', 2);
        })
        ->when(is_numeric($request->checklist_template_id) && $request->checklist_template_id > 0, function ($builder) {
            $builder->whereHas('parent.parent', function ($query) {
                $query->where('checklist_id', request('checklist_template_id'));
            });
        })

        ->scheduling();

        if (!empty($request->from) && !empty($request->to)) {
            $tasks = $tasks->where(\DB::raw("DATE_FORMAT(date, '%Y-%m-%d')"), '>=', date('Y-m-d', strtotime($filterFrom)))
            ->where(\DB::raw("DATE_FORMAT(date, '%Y-%m-%d')"), '<=', date('Y-m-d', strtotime($filterTo)));
        } else if (!empty($request->from) && empty($request->to)) {
            $tasks = $tasks->where(\DB::raw("DATE_FORMAT(date, '%Y-%m-%d')"), '>=', date('Y-m-d', strtotime($filterFrom)));
        } else if (empty($request->from) && !empty($request->to)) {
            $tasks = $tasks->where(\DB::raw("DATE_FORMAT(date, '%Y-%m-%d')"), '<=', date('Y-m-d', strtotime($filterTo)));
        } else {
            $tasks = $tasks->where(function ($where) {
                $where->where(\DB::raw("DATE_FORMAT(date, '%Y-%m-%d')"), date('Y-m-d'))
                ->orWhere(\DB::raw("DATE_FORMAT(date, '%Y-%m-%d')"), date('Y-m-d'));
            });
        }

        $taskCount = $tasks->clone()->count();

        $tasks = $tasks
        ->orderBy('date', 'ASC')
        ->skip($skip)
        ->take($perPage)
        ->get()
        ->map(function ($el) {
            if ($el->status == 0) {
                $statusLabel = 'PENDING';
            } else if ($el->status == 1) {
                $statusLabel = 'IN-PROGRESS';
            } else if ($el->status == 2) {

                if (request('task_type') == 1 && request('filter_status') == 'PENDING_VERIFICATION') {
                    $statusLabel = 'PENDING-VERIFICATION';
                } else {
                    if (isset($el->parent->parent->checker_user_id)) {
                        if ($el->redos()->where('status', 1)->count() == 0 && $el->redos()->where('status', 0)->count() > 0) {
                            $statusLabel = 'REASSIGNED';
                        } else if ($el->redos()->where('status', 0)->count() == 0 && $el->redos()->where('status', 1)->count() == 0) {
                            $statusLabel = 'PENDING-VERIFICATION';
                        } else {
                            $statusLabel = 'VERIFYING';
                        }
                    } else {
                        $statusLabel = 'COMPLETED';
                    }
                }

            } else {
                if (isset($el->parent->parent->checker_user_id)) {
                    $statusLabel = 'VERIFIED';
                } else {
                    $statusLabel = 'COMPLETED';
                }
            }

            $tempTime = Helper::calculateTotalTime($el->id);

            $theStartTime = (isset($el->parent->parent->start_grace_time) ? date('d-m-Y H:i', strtotime(Helper::addGraceTime(date('d-m-Y H:i:s', strtotime(date('d-m-Y', strtotime($el->date)) . ' ' . (isset($el->parent->parent->start_at) ? $el->parent->parent->start_at : '23:59:59'))), $el->parent->parent->start_grace_time))) : null);
            $theEndTime = isset($el->parent->parent->end_grace_time) ? date('d-m-Y H:i', strtotime(Helper::addGraceTime(date('d-m-Y H:i:s', strtotime(date('d-m-Y', strtotime($el->date)) . ' ' . (isset($el->parent->parent->completed_by) ? $el->parent->parent->completed_by : '23:59:59'))), $el->parent->parent->end_grace_time))) : null;

            $theFulfilledJson = [];
            if (isset($el->form)) {
                $theFulfilledJson = $el->form;
                if (!empty($el->data)) {
                    foreach ($el->data as $row) {
                        if (isset($theFulfilledJson[$row->page - 1]) && is_array($theFulfilledJson[$row->page - 1])) {
                            foreach ($theFulfilledJson[$row->page - 1] as $thisRowKey => $thisRow) {
                                if (property_exists($thisRow, 'name') && $thisRow->name == $row->name) {
                                    $theFulfilledJson[$row->page - 1][$thisRowKey]->value = $row->value;
                                }
                            }
                        }
                    }
                }
            }

            return [
                'checklist_task_id' => $el->id,
                'checklist_id' => $el->parent->parent->checklist_id,
                'branch_type' => isset($el->parent->actstore->name) ? $el->parent->actstore->name : '',
                'store_name' => isset($el->parent->actstore->name) ? $el->parent->actstore->name : '',
                'store_code' => isset($el->parent->actstore->code) ? $el->parent->actstore->code : '',                
                'branch_id' => $el->parent->branch_id,
                'user' => $el->parent->user,
                'checklist_title' => $el->parent->parent->checklist->name ?? '',
                'code' => $el->code,
                'schema_encoded' => $theFulfilledJson,
                'data' => isset($el->data) ? $el->data : null,
                'status' => $el->status,
                'cancelled' => $el->cancelled,
                'cancellation_reason' => $el->cancellation_reason,
                'is_point_checklist' => Helper::isPointChecklist(isset($el->form) ? $el->form : []),
                'status_label' => $statusLabel,
                'check_inout' => $el->submissionentries()->latest()->get()->toArray(),
                
                'reschedulings' => RescheduledTask::where('task_id', $el->id)->latest()->first(),                
                'do_not_allow_late_submission' => boolval($el->parent->parent->do_not_allow_late_submission),
                
                'date' => date('d-m-Y H:i', strtotime($el->date)),
                'should_start_at' => date('d-m-Y ', strtotime($el->date)) . (isset($el->parent->parent->start_at) ? date('H:i', strtotime($el->parent->parent->start_at)) : '00:00'),
                'should_completed_by' => date('d-m-Y ', strtotime($el->date)) . (isset($el->parent->parent->completed_by) ? date('H:i', strtotime($el->parent->parent->completed_by)) : '23:59'),
                'grace_start_time' => $theStartTime,
                'grace_end_time' => $theEndTime,

                'grace_start' => isset($el->parent->parent->start_grace_time) ? $el->parent->parent->start_grace_time : null,
                'grace_end' => isset($el->parent->parent->end_grace_time) ? $el->parent->parent->end_grace_time : null,

                'should_complete_in' => isset($el->parent->parent->hours_required) ? $el->parent->parent->hours_required : null,
                'time_spent' => $tempTime,
                'remaining_time' => Helper::calculateRemainingTime(isset($el->parent->parent->hours_required) ? $el->parent->parent->hours_required : '00:00:00', $tempTime),
                
                'allow_rescheduling' => intval(isset($el->parent->parent) ? $el->parent->parent->allow_rescheduling : 0),
                'can_reschedule_on_working_day' => boolval($el->parent->parent->allow_double_rescheduling),

                'excel_export' => route('task-export-excel', $el->id),
                'is_checker' => $el->parent->parent->checker_user_id == auth()->user()->id,
                'redo_action' => RedoAction::where('task_id', $el->id)->where('status', 0)->get()->toArray(),
                'pdf_export' => route('task-export-compressed-pdf', $el->id),

                'ExpiryMaterial' => ExpiryMaterial::where('task_id', $el->id)->get(),
                'NonAuthorizedMaterial' => NonAuthorizedMaterial::where('task_id', $el->id)->get(),
                'FranchisorFeedback' => FranchisorFeedback::where('task_id', $el->id)->get(),
                'PhysicalClosingStock' => PhysicalClosingStock::where('task_id', $el->id)->get(),
                'AuditRemark' => AuditRemark::where('task_id', $el->id)->get(),

                'tickets' => Ticket::where('task_id', $el->id)->latest()->get()->map(function ($ticketEl) {

                    $statusName = 'Pending';

                    if ($ticketEl->status_id == 2 && empty($ticketEl->completed_at)) {
                        $statusName = 'In Progress';
                    } else if (!empty($ticketEl->completed_at)) {
                        $statusName = 'Completed';
                    }

                    return [
                        'id' => $ticketEl->id,
                        'subject' => $ticketEl->subject,
                        'field_id' => $ticketEl->field_id,
                        'ticket_number' => $ticketEl->ticket_number,
                        'content' => $ticketEl->content,
                        'html' => $ticketEl->html,
                        'is_closed' => empty($ticketEl->completed_at) ? false : true,
                        'status' => $statusName,
                        'status_id' => !empty($ticketEl->completed_at) ? 0 : $ticketEl->status_id,
                        'priority' => $ticketEl->priority->name ?? '',
                        'priority_id' => $ticketEl->priority_id,
                        'status_color' => $ticketEl->status->color ?? '',
                        'priority_color' => $ticketEl->priority->color ?? '',
                        'department' => $ticketEl->department->name ?? '',
                        'estimate_date' => date('Y-m-d', strtotime($ticketEl->estimate_time)),
                        'department_id' => $ticketEl->department_id,
                        'created_at' => $ticketEl->created_at,
                        'opened' => Carbon::parse($ticketEl->created_at)->diffInDays(now()),
                        'last_updated_at' => $ticketEl->updated_at,
                        'created_by' => isset($ticketEl->user) ? ($ticketEl->user->name . ' ' . $ticketEl->user->middle_name . ' ' . $ticketEl->user->last_name) : '',
                        'attachments' => $ticketEl->atchmnts->map(function ($tAttch) {
                            if ((empty($tAttch->comment_id) || $tAttch->comment_id == 0) && (empty($tAttch->history_id) || $tAttch->history_id == 0)) {
                                return [
                                    'id' => $tAttch->id,
                                    'url' => asset("storage/ticket-uploads/" . $tAttch->file)
                                ];
                            }
                        })->filter()->values()->toArray(),
                        'history' => $ticketEl->histories->map(function ($hEl) {
                            return [
                                'id' => $hEl->id,
                                'description' => $hEl->description,
                                'attachments' => TicketAttachment::where('history_id', $hEl->id)->get()->map(function ($celem) {
                                    return [
                                        'url' => asset("storage/ticket-uploads/{$celem->file}")
                                    ];
                                }),
                                'created_at' => $hEl->created_at
                            ];
                        })->sortByDesc('id')->values()->toArray(),
                        'comments' => $ticketEl->allcomments->map(function ($cEl) {
                            return [
                                'id' => $cEl->id,
                                'content' => $cEl->content,
                                'html' => $cEl->html,
                                'attachments' => TicketAttachment::where('comment_id', $cEl->id)->get()->map(function ($celem) {
                                    return [
                                        'url' => asset("storage/ticket-uploads/{$celem->file}")
                                    ];
                                }),
                                'user' => isset($cEl->user) ? ($cEl->user->name . ' ' . $cEl->user->middle_name . ' ' . $cEl->user->last_name) : '',
                                'created_at' => $cEl->created_at
                            ];
                        })->values()->toArray()
                    ];
                })                
            ];
        });
        
        $tasks = $tasks->toArray();

        return response()->json(['success' => $tasks, 'total_records' => $taskCount, 'page' => intval($page), 'record_per_page' => $perPage], 200); 
    }

    public function submitImages(Request $request) {
        $validator = Validator::make($request->all(), [
            'task_id' => 'required|exists:checklist_tasks,id',
            'data' => 'required'
        ]);

        if ($validator->fails()) { 
            $errorString = implode(",",$validator->messages()->all());
            return response()->json(['error' => $errorString], 401);
        }

        $task = ChecklistTask::find($request->task_id);
        $existingJson = $task->data;
        $originals = $thumbnails = [];
        $mergerArray = [];

        try {

            if (is_string($request->data)) {
                $data = json_decode($request->data, true);
            } else {
                $data = $request->data;
            }
            
            if (!empty($data)) {

                $allOfTheFields = [];
                foreach ($data as $dataKey => $dataRow) {
                    $allOfTheFields[$dataRow['field_id']] = $dataRow;
                }

                foreach ($data as $dataKey => $dataRow) {
                    if (!empty($existingJson)) {
                        foreach ($existingJson as &$row) {
                            if (property_exists($row, 'name') && $row->name == $dataRow['field_id']) {
                                if (isset($allOfTheFields[$dataRow['field_id']])) {
                                    unset($allOfTheFields[$dataRow['field_id']]);
                                }

                                $tmpArr = [];
                                foreach ($dataRow['values'] as $dt) {
                                    $tempName = ('SIGN-' . date('YmdHis') . uniqid());
                                    $image = Helper::downloadBase64File($dt, $tempName, storage_path('app/public/workflow-task-uploads'));

                                    if (is_file(storage_path("app/public/workflow-task-uploads/{$image}"))) {
                                        $img2 = Helper::createImageThumbnail(storage_path("app/public/workflow-task-uploads/{$image}"), storage_path("app/public/workflow-task-uploads-thumbnails/{$image}"), 200, 200);
                                        if ($img2 && is_file(storage_path("app/public/workflow-task-uploads-thumbnails/{$image}"))) {

                                            if (isset($thumbnails[$row->name])) {
                                                $thumbnails[$row->name]['values'][] = $image;
                                            } else {
                                                $thumbnails[$row->name]['field_name'] = $row->name;
                                                $thumbnails[$row->name]['values'][] = $image;
                                            }

                                        } else {
                                            return response()->json(['error' => "Error occured while generating thumbnail"]);
                                        }
                                    } else {
                                        return response()->json(['error' => "Error occured while generating image"]);
                                    }

                                    $tmpArr[] = $image;
                                }

                                if (is_string($row->value) && !empty($row->value)) {
                                    array_push($tmpArr, $row->value);
                                } else if (is_array($row->value) && !empty($row->value)) {
                                    $tmpArr = array_merge($tmpArr, $row->value);
                                }
                                
                                $row->value = $tmpArr;

                                if (isset($dataRow['timestamp'])) {
                                    $row->timestamp = $dataRow['timestamp'];
                                }

                                $row->value = $tmpArr;
                            }
                        }
                    }
                }

                $allOfTheFields = array_filter($allOfTheFields);
                if (!empty($allOfTheFields)) {

                    foreach ($allOfTheFields as $line) {

                        $tmpArr = [];
                        foreach ($line['values'] as $dt) {
                            $tempName = ('SIGN-' . date('YmdHis') . uniqid());
                            $image = Helper::downloadBase64File($dt, $tempName, storage_path('app/public/workflow-task-uploads'));

                            if (is_file(storage_path("app/public/workflow-task-uploads/{$image}"))) {
                                $img2 = Helper::createImageThumbnail(storage_path("app/public/workflow-task-uploads/{$image}"), storage_path("app/public/workflow-task-uploads-thumbnails/{$image}"), 200, 200);
                                if ($img2 && is_file(storage_path("app/public/workflow-task-uploads-thumbnails/{$image}"))) {
                                    if (isset($thumbnails[$line['field_id']])) {
                                        $thumbnails[$line['field_id']]['values'][] = $image;
                                    } else {
                                        $thumbnails[$line['field_id']]['field_name'] = $line['field_id'];
                                        $thumbnails[$line['field_id']]['values'][] = $image;
                                    }

                                } else {
                                    return response()->json(['error' => "Error occured while generating thumbnail"]);
                                }
                            } else {
                                return response()->json(['error' => "Error occured while generating image"]);
                            }

                            $tmpArr[] = $image;
                        }

                        $mergerArray[] = (object)[
                            "className" => $line['className'],
                            "page" => $line['page'],
                            "index" => $line['index'],
                            "label" => $line['label'],
                            "timestamp" => isset($line['timestamp']) ? $line['timestamp'] : null,
                            "name" => $line['field_id'],
                            "value" => $tmpArr,
                            "isFile" => true
                        ];
                    }
                }

                if (empty($existingJson) || $existingJson == '{}') {
                    $task->data = $mergerArray;
                } else {
                    if (!is_array($existingJson)) {
                        $existingJson = (array) $existingJson;
                    }

                    $tmpAr = array_merge($mergerArray, $existingJson);
                    $task->data = array_filter($tmpAr);
                }

                $task->status = $task->status == 0 ? 1 : $task->status;
                $task->save();

                $tempJsonForReindexing = $task->data;

                usort($tempJsonForReindexing, function ($a, $b) {
                    $pageComparison = (int)$a->page <=> (int)$b->page;
                    
                    if ($pageComparison === 0) {
                        $aIndex = isset($a->index) ? (int)$a->index : PHP_INT_MAX;
                        $bIndex = isset($b->index) ? (int)$b->index : PHP_INT_MAX;

                        return $aIndex <=> $bIndex;
                    }

                    return $pageComparison;
                });

                $task->data = $tempJsonForReindexing;
                $task->save();

                return response()->json(['success' => "Image uploaded successfully", "thumbnails" => array_values($thumbnails)]);
            } else {
                return response()->json(['error' => "No JSON Found"]);
            }

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage(), 'line' => $e->getLine()]);
        }
    }

    public function refreshTaskListing(Request $request) {
        $validator = Validator::make($request->all(), [
            'ids' => 'required|array',
            'ids.*' => 'exists:checklist_tasks,id'
        ]);

        if ($validator->fails()) { 
            $errorString = implode(",",$validator->messages()->all());
            return response()->json(['error' => $errorString], 401);
        }
        $data = [];

        foreach ($request->ids as $task) {
            $taskELoquent = ChecklistTask::withTrashed()->selectRaw('id, cancelled, status, deleted_at')->where('id', $task)->first();
            $shouldKeep = true;

            if (!empty($taskELoquent->deleted_at)) {
                $shouldKeep = false;
            } else if ($taskELoquent->status == 3) {
                $shouldKeep = false;
            } else if ($taskELoquent->cancelled == 1) {
                $shouldKeep = false;
            }

            $data[] = [
                'task_id' => intval($task),
                'status' => intval($shouldKeep)
            ];
        }

        return response()->json(['success' => $data]);
    }

    public function categories(Request $request) {
        return response()->json(['status' => true, 'data' => ProductCategory::select('id', 'name', 'description')->where('status', true)->get()]);
    }

    public function products(Request $request) {
        return response()->json(['status' => true, 'data' => Product::select('id', 'sku', 'category_id', 'name', 'description', 'uom')->when(request()->filled('category_id'), function ($builder) {
            $builder->where('category_id', request('category_id'));
        })->where('status', true)->get()]);
    }

    public function uoms(Request $request) {
        return response()->json(['status' => true, 'data' => Product::select('uom')->where('status', true)->groupBy('uom')->get()]);
    }
}