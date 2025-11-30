<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AutomateScheduling;
use App\Models\AutomateSchedulingItem;
use App\Models\ChecklistScheduling;
use App\Models\ChecklistSchedulingExtra;
use App\Models\ChecklistTask;
use App\Helpers\Helper;

class AutomateSchedulingRun extends Command
{
	protected $signature = 'automate:run';

	protected $description = 'Process automate schedulings and generate tasks';

	public function handle()
	{
		$now = now();
		$automates = AutomateScheduling::withTrashed()->where('status', 1)
			->where('start', '<=', $now)
			->where(function ($q) use ($now) {
				$q->whereNull('end')->orWhere('end', '>=', $now);
			})->get();

		foreach ($automates as $automate) {
			$notificationTitle = 'automate:' . $automate->id;
			$schedule = ChecklistScheduling::firstOrCreate(
				['notification_title' => $notificationTitle],
				[
					'notification_description' => null,
					'checklist_id' => $automate->checklist_id,
					'branch_type' => null,
					'branch_id' => 0,
					'user_id' => null,
					'interval' => $automate->interval,
					'weekdays' => $automate->weekdays,
					'weekday_time' => $automate->weekday_time,
					'frequency_type' => $automate->frequency_type,
					'perpetual' => $automate->perpetual ? 1 : 0,
					'start' => $automate->start,
					'end' => $automate->end,
					'completion_data' => [],
					'checker_branch_type' => null,
					'checker_branch_id' => null,
					'checker_user_id' => null,
					'allow_rescheduling' => 0,
					'is_import' => 0,
				]
			);

			$items = AutomateSchedulingItem::where('automate_scheduling_id', $automate->id)->get();
			foreach ($items as $item) {
				$roleType = 1;
				$branch = Helper::getFirstBranch($item->user_id, $roleType);
				$extra = ChecklistSchedulingExtra::firstOrCreate(
					[
						'checklist_scheduling_id' => $schedule->id,
						'user_id' => $branch['user_id'],
						'store_id' => $item->store_id,
					],
					[
						'branch_id' => $branch['branch_id'],
						'branch_type' => $branch['branch_type'],
					]
				);

				$latestTask = ChecklistTask::where('checklist_scheduling_id', $extra->id)->scheduling()->orderBy('date', 'DESC')->first();
				$startDate = date('Y-m-d H:i:s', strtotime($schedule->start));
				$typeSlug = 'hourly';
				$weekdayTime = null;
				$allDays = null;
				$lastDate = $startDate;

				if ($latestTask) {
					$startDate = $latestTask->date;
					if ($schedule->frequency_type == 0) {
						$startDate = date('Y-m-d H:i:s', strtotime("$startDate +1 hour"));
					} else if ($schedule->frequency_type == 1) {
						$startDate = date('Y-m-d H:i:s', strtotime("$startDate +{$schedule->interval} hours"));
					} else if ($schedule->frequency_type == 2) {
						$startDate = date('Y-m-d H:i:s', strtotime("$startDate +1 days"));
					} else if ($schedule->frequency_type == 3) {
						$startDate = date('Y-m-d H:i:s', strtotime("$startDate +{$schedule->interval} days"));
					} else if ($schedule->frequency_type == 4) {
						$startDate = date('Y-m-d H:i:s', strtotime("$startDate +7 days"));
					} else if ($schedule->frequency_type == 5) {
						$startDate = date('Y-m-d H:i:s', strtotime("$startDate +14 days"));
					} else if ($schedule->frequency_type == 6) {
						$startDate = date('Y-m-d H:i:s', strtotime("$startDate +30 days"));
					} else if ($schedule->frequency_type == 7) {
						$startDate = date('Y-m-d H:i:s', strtotime("$startDate +60 days"));
					} else if ($schedule->frequency_type == 8) {
						$startDate = date('Y-m-d H:i:s', strtotime("$startDate +90 days"));
					} else if ($schedule->frequency_type == 9) {
						$startDate = date('Y-m-d H:i:s', strtotime("$startDate +180 days"));
					} else if ($schedule->frequency_type == 10) {
						$startDate = date('Y-m-d H:i:s', strtotime("$startDate +365 days"));
					} else if ($schedule->frequency_type == 11) {
						$allDays = $schedule->weekdays ? explode(',', $schedule->weekdays) : [];
						$dt = new \DateTime($startDate);
						if (!empty($allDays)) {
							$dt->modify('next ' . $allDays[0]);
						}
						$startDate = $dt->format('Y-m-d H:i:s');
					}
				}

				if ($schedule->frequency_type == 0) {
					$typeSlug = 'hourly';
					$lastDate = date('Y-m-d H:i:s', strtotime("$startDate +1 hour"));
				} else if ($schedule->frequency_type == 1) {
					$typeSlug = $schedule->interval . ' hour';
					$lastDate = date('Y-m-d H:i:s', strtotime("$startDate +{$schedule->interval} hours"));
				} else if ($schedule->frequency_type == 2) {
					$typeSlug = 'daily';
					$lastDate = date('Y-m-d H:i:s', strtotime("$startDate +1 day"));
				} else if ($schedule->frequency_type == 3) {
					$typeSlug = $schedule->interval . ' day';
					$lastDate = date('Y-m-d H:i:s', strtotime("$startDate +{$schedule->interval} days"));
				} else if ($schedule->frequency_type == 4) {
					$typeSlug = 'weekly';
					$lastDate = date('Y-m-d H:i:s', strtotime("$startDate +7 days"));
				} else if ($schedule->frequency_type == 5) {
					$typeSlug = 'biweekly';
					$lastDate = date('Y-m-d H:i:s', strtotime("$startDate +14 days"));
				} else if ($schedule->frequency_type == 6) {
					$typeSlug = 'monthly';
					$lastDate = date('Y-m-d H:i:s', strtotime("$startDate +30 days"));
				} else if ($schedule->frequency_type == 7) {
					$typeSlug = 'bimonthly';
					$lastDate = date('Y-m-d H:i:s', strtotime("$startDate +60 days"));
				} else if ($schedule->frequency_type == 8) {
					$typeSlug = 'quarterly';
					$lastDate = date('Y-m-d H:i:s', strtotime("$startDate +90 days"));
				} else if ($schedule->frequency_type == 9) {
					$typeSlug = 'semiannually';
					$lastDate = date('Y-m-d H:i:s', strtotime("$startDate +180 days"));
				} else if ($schedule->frequency_type == 10) {
					$typeSlug = 'annually';
					$lastDate = date('Y-m-d H:i:s', strtotime("$startDate +365 days"));
				} else if ($schedule->frequency_type == 11) {
					$typeSlug = 'specific_days';
					$allDays = $schedule->weekdays ? explode(',', $schedule->weekdays) : [];
					$weekdayTime = $schedule->weekday_time;
				}

				$endBoundary = $schedule->end ? $schedule->end : $lastDate;
				$windowEnd = min(strtotime($endBoundary), strtotime($lastDate));
				$lastDate = date('Y-m-d H:i:s', $windowEnd);

				$timestamps = \App\Helpers\Frequency::generate($startDate, $lastDate, $typeSlug, $allDays, $weekdayTime);
				foreach ($timestamps as $timestamp) {
					$exists = ChecklistTask::where('checklist_scheduling_id', $extra->id)->where('date', $timestamp)->exists();
					if ($exists) {
						continue;
					}
					ChecklistTask::create([
						'code' => Helper::generateTaskNumber($timestamp, $extra->user_id),
						'checklist_scheduling_id' => $extra->id,
						'form' => $schedule->checklist->schema ?? [],
						'date' => $timestamp,
						'type' => 0
					]);
				}
			}
		}
		return 0;
	}
}
