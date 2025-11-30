<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SendTaskNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:notification';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send Task Notification';

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

        $tasks = \App\Models\ChecklistTask::with(['workflowclist.usr', 'parent.user'])
                 ->pending()
                 ->whereBetween('date', [$oneMinuteAgo, $currentTime])
                 ->get();

        if ($tasks) {
            foreach ($tasks as $task) {

                if ($task->type == 0) {
                    $deviceTokens = \App\Models\DeviceToken::select('token')
                    ->where('user_id', $task->parent->user_id)
                    ->pluck('token')
                    ->toArray();

                    if (!empty($deviceTokens)) {
                        \App\Helpers\Helper::sendPushNotification($deviceTokens, [
                            'title' => isset($task->parent->parent->notification_title) ? $task->parent->parent->notification_title : $task->code,
                            'description' => isset($task->parent->parent->notification_description) ? $task->parent->parent->notification_description : ''
                        ]);
                    }

                    if (isset($task->parent->user->email)) {
                        \Illuminate\Support\Facades\Mail::to($task->parent->user->email)->send(new \App\Mail\EscalationMail(isset($task->parent->parent->notification_title) ? $task->parent->parent->notification_title : $task->code, isset($task->parent->parent->notification_description) ? $task->parent->parent->notification_description : ''));
                    }

                } else if ($task->type == 1) {
                    $deviceTokens = \App\Models\DeviceToken::select('token')
                    ->when(isset($task->workflowclist->usr), function ($innerBuilder) use ($task) {
                        return $innerBuilder->where('user_id', $task->workflowclist->usr);
                    })
                    ->pluck('token')
                    ->toArray();

                    if (!empty($deviceTokens)) {
                        \App\Helpers\Helper::sendPushNotification($deviceTokens, [
                            'title' => isset($tasks->workflowclist->wftmpasgmt->name) ? $tasks->workflowclist->wftmpasgmt->name : 'Workflow',
                            'description' => "$task->code is ready for submission"
                        ]);
                    }

                    if (isset($task->workflowclist->usr->email)) {
                        \Illuminate\Support\Facades\Mail::to($task->workflowclist->usr->email)->send(new \App\Mail\EscalationMail(isset($tasks->workflowclist->wftmpasgmt->name) ? $tasks->workflowclist->wftmpasgmt->name : 'Workflow', "$task->code is ready for submission"));
                    }
                }
            }
        }
    }
}
