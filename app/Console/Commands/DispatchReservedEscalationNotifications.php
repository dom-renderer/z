<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class DispatchReservedEscalationNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:escalation-notification';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }


    public function handle()
    {
        $oneMinuteAgo = now()->subMinute()->format('Y-m-d H:i:00');
        $currentTime = now()->format('Y-m-d H:i:00');
    
        $escalations = \App\Models\ReservedEscalation::with([
            'escalation' => function ($builder) {
                return $builder->withTrashed();
            },
            'escalation.workflowclist' => function ($builder) {
                return $builder->withTrashed();
            },
            'escalation.workflowclist.clist' => function ($builder) {
                return $builder->withTrashed();
            },
            'escalation.workflowclist.store' => function ($builder) {
                return $builder->withTrashed();
            },
            'escalation.workflowclist.dept' => function ($builder) {
                return $builder->withTrashed();
            },
            'escalation.workflowclist.usr' => function ($builder) {
                return $builder->withTrashed();
            }
        ])
        ->whereBetween('date', [$oneMinuteAgo, $currentTime])
        ->where('sent', 0)
        ->get();
    
        foreach ($escalations as $escalation) {
            if (isset($escalation->escalation)) {
                $allTemplates = \App\Models\NotificationTemplate::whereIn('id', $escalation->escalation->templates)->get();

                if (!empty($allTemplates)) {
                    foreach ($allTemplates as $template) {
                        $task = \App\Models\ChecklistTask::with(['workflowclist.sec' => function ($builder) {
                            return $builder->withTrashed();
                        }])->withTrashed()
                        ->where('id', $escalation->task_id)
                        ->first();

                        if ($task) {
                            $user = \App\Models\User::withTrashed()->where('id', $escalation->escalation->workflowclist->user_id)->first();

                            if ($escalation->escalation->workflowclist->branch_type == 1) {
                                $branch = \App\Models\Store::withTrashed()->where('id', $escalation->escalation->workflowclist->branch_id)->first();
                            } else {
                                $branch = \App\Models\Department::withTrashed()->where('id', $escalation->escalation->workflowclist->branch_id)->first();
                            }
    
                            $cname = \App\Models\DynamicForm::withTrashed()->where('id', $escalation->escalation->workflowclist->checklist_id)->first();
    
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
                                \Illuminate\Support\Facades\Mail::to($escalation->escalation->workflowclist->usr->email)->send(new \App\Mail\EscalationMail($template->title, $content));
                            }

                            echo "Escalation notification sent successfully\n";
                        } else {
                            echo "Task not found\n";
                        }
                    }
                }

                $escalation->update(['sent' => 1]);
            }
        }
    }
}
