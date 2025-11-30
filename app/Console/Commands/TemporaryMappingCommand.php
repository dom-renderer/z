<?php

namespace App\Console\Commands;

use App\Models\ChecklistScheduling;
use App\Models\ChecklistSchedulingExtra;
use App\Models\ChecklistTask;
use Illuminate\Console\Command;

class TemporaryMappingCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'map:data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Temporary mapping command';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function handle() {

        foreach (ChecklistScheduling::with(['checklist.store'])->get() as $row) {
            $extra = ChecklistSchedulingExtra::updateOrCreate([
                'checklist_scheduling_id' => $row->id
            ], [
                'checklist_scheduling_id' => $row->id,
                'user_id' => $row->user_id,
                'branch_id' => $row->branch_id,
                'branch_type' => $row->branch_type,
                'store_id' => $row->checklist->store->id ?? null
            ]);

            ChecklistTask::scheduling()->where('checklist_scheduling_id', $row->id)->update([
                'checklist_scheduling_id' => $extra->id
            ]);

            echo "updated schedling extra \n";
        }        
    }

    public function swapPageInChecklist() {
        $checklists = \App\Models\DynamicForm::where('created_at', '>=', '2025-04-28 00:00:00')->get();

        foreach ($checklists as $checklist) {
            $checklist->update([
                'schema' => collect($checklist->schema)->put(0, $checklist->schema[1])->put(1, $checklist->schema[0])->toArray()
            ]);
        }

        $checklists = ChecklistTask::where('created_at', '>=', '2025-04-28 00:00:00')->get();

        foreach ($checklists as $checklist) {
            $checklist->update([
                'form' => collect($checklist->form)->put(0, $checklist->form[1])->put(1, $checklist->form[0])->toArray()
            ]);
        }
    }

    public function scheduleOnceChecklist()
    {
        $checklists = \App\Models\DynamicForm::where('created_at', '>=', '2025-04-28 00:00:00')->get();

        foreach ($checklists as $checklist) {
            $store = \App\Models\Store::find($checklist->branch_id);
            $branch = \App\Models\Designation::where('user_id', $store->dom_id)->where('type', 3)->first();

            if ($store && $branch) {
        
                $json = [
                    'branch_type' => 2,
                    'branch_id' => 1,
                    'user_id' => 26,
                    'templates' => [6, 8]
                ];
        
                $checklistScheduling = ChecklistScheduling::create([
                    'notification_title' => "DoM Inspection - {$checklist->name}",
                    'notification_description' => "DoM Inspection for {$checklist->name} is ready",
                    'checklist_id' => $checklist->id,
                    'branch_type' => 2,
                    'branch_id' => $branch->type_id,
                    'user_id' => $store->dom_id,
        
                    'do_not_allow_late_submission' => 0,
        
                    'checker_branch_type' => 2,
                    'checker_branch_id' => 1,
                    'checker_user_id' => 26,
        
                    'frequency_type' => 12,
                    'start' => '2025-04-28 08:00:00',
                    'completion_data' => $json
                ]);
        
                ChecklistTask::create([
                    'code' => \App\Helpers\Helper::generateTaskNumber('2025-04-28 08:00:00', 26),
                    'checklist_scheduling_id' => $checklistScheduling->id,
                    'form' => $checklistScheduling->checklist->schema ?? [],
                    'date' => '2025-04-28 08:00:00',
                    'type' => 0
                ]);    
        
            }
        }
    }
}
