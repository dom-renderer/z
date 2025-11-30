<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GenerateDashboard extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:dashboard';

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

        $query = "CREATE OR REPLACE VIEW main_dashboard AS
        SELECT 
            `checklist_tasks`.`id` as `checklist_tasks_id`,
            `checklist_tasks`.`status` as `checklist_tasks_status`,
            `checklist_tasks`.`date` as `checklist_tasks_date`,
            `checklist_tasks`.`data` as `checklist_tasks_data`,
            `checklist_scheduling_extras`.`id` as `checklist_scheduling_extras_id`,
            
            `checklist_schedulings`.`checker_user_id` as `checklist_schedulings_checker_user_id`,
            
            `assigned_user`.`id` as `assigned_user_id`,
            `assigned_user`.`name` as `assigned_user_name`,
            `assigned_user`.`middle_name` as `assigned_user_middle_name`,
            `assigned_user`.`last_name` as `assigned_user_last_name`,
            `assigned_user`.`employee_id` as `assigned_user_employee_id`,
            
            `checker_user`.`id` as `checker_user_id`,
            `checker_user`.`name` as `checker_user_name`,
            `checker_user`.`middle_name` as `checker_user_middle_name`,
            `checker_user`.`last_name` as `checker_user_last_name`,
            `checker_user`.`employee_id` as `checker_user_employee_id`,
            
            `stores`.`id` as `stores_id`,
            `stores`.`name` as `stores_name`,
            `stores`.`code` as `stores_code`,
            `stores`.`store_type` as `stores_store_type`,
            `stores`.`model_type` as `stores_model_type`,
            `cities`.`city_state` as `cities_city_state`,
            `cities`.`city_id` as `cities_city_id`

        FROM `checklist_tasks` 
        INNER JOIN `checklist_scheduling_extras` 
            ON `checklist_scheduling_extras`.`id` = `checklist_tasks`.`checklist_scheduling_id`
        INNER JOIN `checklist_schedulings`
            ON `checklist_schedulings`.`id` = `checklist_scheduling_extras`.`checklist_scheduling_id`
        INNER JOIN `users` AS `assigned_user` 
            ON `checklist_scheduling_extras`.`user_id` = `assigned_user`.`id`
        INNER JOIN `users` AS `checker_user` 
            ON `checklist_schedulings`.`checker_user_id` = `checker_user`.`id`
        INNER JOIN `stores` 
            ON `checklist_scheduling_extras`.`store_id` = `stores`.`id`
        INNER JOIN `cities` 
            ON `cities`.`city_id` = `stores`.`city`

        WHERE `checklist_tasks`.`type` = 0
        AND `checklist_tasks`.`deleted_at` IS NULL 

        ORDER BY `checklist_tasks`.`date` DESC";

        \DB::statement($query);


        if (\DB::table('information_schema.views')
            ->where('table_schema', '=', env('DB_DATABASE'))
            ->where('table_name', '=', 'main_dashboard')
            ->exists()) {

                \App\Models\Store::orderBy('name')->chunk(1000, function ($stores) {
                    foreach ($stores as $store) {
                        $storeId = $store->id;
                        $storeName = $store->name;
                        $storeCode = $store->code;

                        $data['bar_chart_label'][] = array_values($storeIds);
                        $data['bar_chart_store_ids'][] = array_keys($storeIds);
                    }
                });

                $data['bar_chart_label'] = array_values($storeIds);
                $data['bar_chart_store_ids'] = array_keys($storeIds);

                $pendingBarChartData = [];
                $barChartBarColour = [];

                $viewQuery = \DB::table('main_dashboard');

                
                foreach ($storeIds as $storeId => $storeName) {
                    $query = clone $viewQuery;

                    $query->where('stores_id', $storeId)
                        ->whereBetween('checklist_tasks_date', [
                            date('Y-m-d', strtotime($request->start)),
                            date('Y-m-d', strtotime($request->end))
                        ]);

                    if ($request->dom != 'all') {
                        $query->where('users_id', $request->dom);
                    }

                    if ($request->ops != 'all') {
                        $query->where('checklist_schedulings_checker_user_id', $request->ops);
                    }

                    if ($request->ltype != 'all') {
                        $query->where('stores_store_type', $request->ltype);
                    }

                    if ($request->lmodel != 'all') {
                        $query->where('stores_model_type', $request->lmodel);
                    }

                    if ($request->state != 'all') {
                        $query->where('cities_city_state', $request->state);
                    }

                    if ($request->city != 'all') {
                        $query->where('cities_city_id', $request->city);
                    }

                    if ($request->status != 'all') {
                        $query->whereIn('checklist_tasks_status', $request->status == 1 ? [1] : [2, 3]);
                    } else {
                        $query->whereIn('checklist_tasks_status', [Helper::$status['in-verification'], Helper::$status['completed'], Helper::$status['in-progress']]);
                    }

                    $results = $query->get();

                    $count_truthy_temp = 0;
                    $count_falsy_temp = 0;

                    foreach ($results as $row) {
                        $boolFields = Helper::getBooleanFields(json_decode($row->checklist_tasks_data, true));
                        $count_truthy_temp += count($boolFields['truthy']);
                        $count_falsy_temp += count($boolFields['falsy']);
                    }

                    $percent = ($count_truthy_temp + $count_falsy_temp) > 0
                        ? number_format((($count_truthy_temp / ($count_truthy_temp + $count_falsy_temp)) * 100), 2)
                        : 0;

                    $pendingBarChartData[] = $percent;
                    $barChartBarColour[] = $request->status == 1 ? '#FFC107' : '#03A9F4';
                }

                $data['bar_chart_data'] = [$pendingBarChartData];
                $data['bar_chart_label_bar_color'] = [$barChartBarColour];

        }
    }
}
