<?php

namespace App\Jobs;

use App\Models\ChecklistEscalation;
use App\Models\WorkflowChecklist;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CompletionNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $task;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($task)
    {
        $this->task = $task;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $checklistWorkflow = WorkflowChecklist::where('id', $this->task->workflow_checklist_id ?? null)->first();

        if ($checklistWorkflow) {
            $template = ChecklistEscalation::where('workflow_checklist_id', $checklistWorkflow->id)->where('type', 1)->first();

            if ($template) {
                $allTemplates = \App\Models\NotificationTemplate::whereIn('id', $template->templates)->get();

                foreach ($allTemplates as $template) {
                    $user = \App\Models\User::withTrashed()->where('id', $checklistWorkflow->user_id)->first();

                    if ($checklistWorkflow->branch_type == 1) {
                        $branch = \App\Models\Store::withTrashed()->where('id', $checklistWorkflow->branch_id)->first();
                    } else {
                        $branch = \App\Models\Department::withTrashed()->where('id', $checklistWorkflow->branch_id)->first();
                    }
        
                    $cname = \App\Models\DynamicForm::withTrashed()->where('id', $checklistWorkflow->checklist_id)->first();
        
                    $content = str_replace(array_keys(\App\Helpers\Helper::$notificationTemplatePlaceholders), [
                        $user->name ?? 'N/A',
                        $user->username ?? 'N/A',
                        $user->phone_number ?? 'N/A',
                        $user->email ?? 'N/A',
                        $branch->name ?? 'N/A',
                        $cname->name ?? 'N/A',
                        $task->sec->name ?? 'N/A'
                    ], $template->content);
        
                    if ($template->type == 1) {
                        $deviceTokens = [];
                        if (isset($user->id) && $user->id > 0) {
                            $deviceTokens = \App\Models\DeviceToken::where('user_id', $user->id)->pluck('token')->toArray();
                        }
        
                        if (!empty($deviceTokens)) {
                            \App\Helpers\Helper::sendPushNotification($deviceTokens, [
                                'title' => $template->title,
                                'description' => $content
                            ]);
                        }
                    } else {
                        \Illuminate\Support\Facades\Mail::to($checklistWorkflow->usr->email)->send(new \App\Mail\EscalationMail($template->title, $content));
                    }
                }
            }
        }
    }
}
