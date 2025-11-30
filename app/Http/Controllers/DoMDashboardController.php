<?php

namespace App\Http\Controllers;

use App\Models\ChecklistTask;
use Illuminate\Http\Request;
use App\Models\DynamicForm;
use App\Helpers\Helper;
use App\Models\Store;

class DoMDashboardController extends Controller
{
    public function index(Request $request) {
        if ($request->ajax()) {
            $tableData = '';
            $flaggedArray = [];
            $totalFlagged = 0;
            $data = [
                'bar_chart_label' => ['Location A'],
                'bar_chart_label_bar' => ['MAX SCORE'],
                'bar_chart_label_bar_color' => ['#8dc1e9'],
                'bar_chart_data' => [
                    [10]
                ]
            ];

            $stores = Store::select('name', 'id')
                    ->when(!auth()->user()->isAdmin(), function ($builder) {
                        $builder->where('dom_id', auth()->user()->id);
                    })
                    ->when($request->loc != 'all', function ($builder) {
                        $builder->where('id', request('loc'));
                    })
                    ->when($request->ltype != 'all', function ($builder) {
                        $builder->where('store_type', request('ltype'));
                    })
                    ->when($request->lmodel != 'all', function ($builder) {
                        $builder->where('model_type', request('lmodel'));
                    })
                    ->when($request->state != 'all', function ($builder) {
                        $builder->whereHas('thecity', function ($innerBuilder) {
                            return $innerBuilder->where('city_state', request('state'));
                        });
                    })
                    ->when($request->city != 'all', function ($builder) {
                        return $builder->where('city', request('city'));
                    })
                    ->orderBy('name','ASC')
                    ->pluck('name', 'id')
                    ->toArray();

            $data['bar_chart_label'] = array_values($stores);
            $data['bar_chart_store_ids'] = array_keys($stores);

            $pendingBarChartData = $barChartBarColour = [];

            if ($stores) {

                foreach ($stores as $thisStore => $val) {
                    $totalChecklists = ChecklistTask::with(['parent.parent.checklist', 'parent.actstore', 'parent.user'])
                    ->scheduling()
                    ->when(!auth()->user()->isAdmin(), function ($builder) {
                        $builder->whereHas('parent.actstore', function ($innerBuilder) {
                            return $innerBuilder->where('dom_id', auth()->user()->id);
                        });
                    }, function ($outerBuilder) {
                        $outerBuilder->when(request('dom') != 'all', function ($builder) {
                            $builder->whereHas('parent', function ($innerBuilder) {
                                return $innerBuilder->where('user_id', request('dom'));
                            });
                        })->when(request('loc') != 'all', function ($builder) {
                            $builder->whereHas('parent.actstore', function ($innerBuilder) {
                                return $innerBuilder->where('id', request('loc'));
                            });
                        });
                    })
                    ->when($request->ops != 'all', function ($builder) {
                        $builder->whereHas('parent.parent', function ($innerBuilder) {
                            return $innerBuilder->where('checker_user_id', request('ops'));
                        });
                    })
                    ->when($request->ltype != 'all', function ($builder) {
                        $builder->whereHas('parent.actstore', function ($innerBuilder) {
                            return $innerBuilder->where('store_type', request('ltype'));
                        });
                    })
                    ->when($request->lmodel != 'all', function ($builder) {
                        $builder->whereHas('parent.actstore', function ($innerBuilder) {
                            return $innerBuilder->where('model_type', request('lmodel'));
                        });
                    })
                    ->when($request->state != 'all', function ($builder) {
                        $builder->whereHas('parent.actstore.thecity', function ($innerBuilder) {
                            return $innerBuilder->where('city_state', request('state'));
                        });
                    })
                    ->when($request->city != 'all', function ($builder) {
                        $builder->whereHas('parent.actstore', function ($innerBuilder) {
                            return $innerBuilder->where('city', request('city'));
                        });
                    })
                    ->when($request->clist != 'all', function ($builder) {
                        $builder->whereHas('parent.parent', function ($innerBuilder) {
                            return $innerBuilder->where('checklist_id', request('clist'));
                        });
                    })
                    ->whereHas('parent.actstore', function ($innerBuilder) use ($thisStore) {
                        return $innerBuilder->where('id', $thisStore);
                    })
                    ->whereIn('status', [Helper::$status['in-verification'], Helper::$status['completed']])
                    ->where(\DB::raw("DATE_FORMAT(date, '%Y-%m-%d')"), '>=', date('Y-m-d', strtotime($request->start)))
                    ->where(\DB::raw("DATE_FORMAT(date, '%Y-%m-%d')"), '<=', date('Y-m-d', strtotime($request->end)));

                    $count_truthy_temp = 0;
                    $count_falsy_temp = 0;

                    foreach ($totalChecklists->orderBy('date', 'DESC')->get() as $count_pending_inspection_temp_row) {
                        $count_truthy_temp += count(Helper::getBooleanFields($count_pending_inspection_temp_row['data'])['truthy']);
                        $count_falsy_temp += count(Helper::getBooleanFields($count_pending_inspection_temp_row['data'])['falsy']);
                    }

                    $pendingBarChartData[] = ($count_truthy_temp + $count_falsy_temp > 0 ? number_format((($count_truthy_temp / ($count_truthy_temp + $count_falsy_temp)) * 100), 2) : 0);
                    $barChartBarColour[] = '#03A9F4';
                }

                $data['bar_chart_data'] = [
                    $pendingBarChartData
                ];

                $data['bar_chart_label_bar_color'] = [
                    $barChartBarColour
                ];
            }

            return response()->json(['data' => $data]);
        }

        $page_title = 'DoM Dashboard';
        return view('dashboard.dom-dashboard', compact('page_title'));
    }

    public function index3(Request $request) {
            $store = Store::find($request->store);

            if ($store) {
                $totalChecklists = ChecklistTask::query()
                ->scheduling()
                ->whereHas('parent.actstore', function ($innerBuilder) use ($store) {
                    return $innerBuilder->where('id', $store->id);
                })
                ->whereIn('status', [Helper::$status['in-verification'], Helper::$status['completed']])
                ->when($request->clist != 'all', function ($builder) {
                    $builder->whereHas('parent.parent', function ($innerBuilder) {
                        $innerBuilder->where('checklist_id', request('clist'));
                    });
                })
                ->where(\DB::raw("DATE_FORMAT(date, '%Y-%m-%d')"), '>=', date('Y-m-d', strtotime($request->start)))
                ->where(\DB::raw("DATE_FORMAT(date, '%Y-%m-%d')"), '<=', date('Y-m-d', strtotime($request->end)))
                ->orderBy('date', 'DESC')
                ->limit(6)
                ->get();

                $barChartData = $barChartBarColour = $barChartLabels = [];

                foreach ($totalChecklists as $row) {
                    
                    //Calculation
                        $varients = Helper::categorizePoints($row->data ?? []);

                        $total = count(Helper::selectPointsQuestions($row->data));
                        $toBeCounted = $total - count($varients['na']);

                        $failed = abs(count(array_column($varients['negative'], 'value')));
                        $achieved = $toBeCounted - abs($failed);

                        if ($failed <= 0) {
                            $achieved = array_sum(array_column($varients['positive'], 'value'));
                        }

                        if ($toBeCounted > 0) {
                            $percentage = ($achieved / $toBeCounted) * 100;
                        } else {
                            $percentage = 0;
                        }
                    //Calculation

                    $barChartData[] = $percentage;
                    $barChartBarColour[] = $row->status == 1 ? '#FFC107' : '#03A9F4';
                    $barChartLabels[] = date('d F, Y', strtotime($row->date));
                }
            }

            return response()->json(['data' => $barChartData, 'color' => $barChartBarColour, 'labels' => $barChartLabels, 'store_name' => $store->name]);
    }

    public function detail(Request $request) {
        $Task = ChecklistTask::find($request->id);

        if ($Task) {
            $inlineTable = '<table class="table w-100 table-striped table-bordered">
            <thead>
               <tr> 
               <th> Questions </th>
               <th> Answer </th>
               </tr>
            </thead>
            <tbody>';

            $iterableData = collect($Task->data)->filter(function ($item) {
                return ((strtolower($item->value) === 'fail') || (isset($item->label_value) && strtolower($item['label_value']) === 'fail') || strtolower($item->value) === 'no') || (isset($item->label_value) && strtolower($item['label_value']) === 'no');
            })->pluck('label')->all();

            $iterableData2 = collect($Task->data)->filter(function ($item) {
                return ((strtolower($item->value) === 'fail') || (isset($item->label_value) && strtolower($item['label_value']) === 'fail') || strtolower($item->value) === 'no') || (isset($item->label_value) && strtolower($item['label_value']) === 'no');
            })->map(function ($builder) {
                return isset($builder->label_value) ? $builder->label_value : $builder->value;
            })
            ->values();

            foreach ($iterableData as $thisKey => $thisQuestion) {
                $inlineTable .= "<tr><td>
            {$thisQuestion}
                </td>
                <td>" . (isset($iterableData2[$thisKey]) ? $iterableData2[$thisKey] : '') . "</td>
                </tr>";
            }

            $inlineTable .= '
            </tbody>
            </table>';

            return response()->json(['status' => true, 'html' => $inlineTable]);
        }

        return response()->json(['status' => false]);
    }





    // New Dashboard


    public function index2(Request $request) {
        if ($request->ajax()) {
            $tableData = '';
            $flaggedArray = [];
            $totalFlagged = 0;
            $data = [
                'flagged_items' => 0,
                'bar_chart_label' => ['Location A'],
                'bar_chart_label_bar' => ['MAX SCORE'],
                'bar_chart_label_bar_color' => ['#8dc1e9'],
                'bar_chart_data' => [
                    [10]
                ],
                'flagged_items_table' => '<tr> <td colspan="5" align="center"> No items found </td> </tr>'
            ];

            $stores = Store::when($request->store != 'all', function ($builder) {
                $builder->where('id', request('store'));
            })->pluck('name', 'id')->toArray();

            $data['bar_chart_label'] = array_values($stores);

            $pendingBarChartData = [];

            if ($stores) {

                foreach ($stores as $thisStore => $val) {
                    $totalChecklists = ChecklistTask::with(['parent.parent.checklist', 'parent.actstore', 'parent.user'])
                    ->scheduling()
                    ->when($request->dom != 'all', function ($builder) {
                        $builder->whereHas('parent', function ($innerBuilder) {
                            return $innerBuilder->where('user_id', request('dom'));
                        });
                    })
                    ->when($request->sop != 'all', function ($builder) {
                        $builder->whereHas('parent.parent.checklist', function ($innerBuilder) {
                            return $innerBuilder->where('id', request('sop'));
                        });
                    })
                    ->when($request->sop == 'all', function ($builder) use ($thisStore) {
                        $builder->whereHas('parent', function ($innerBuilder) use ($thisStore) {
                            return $innerBuilder->where('store_id', $thisStore);
                        });
                    })
                    ->whereIn('status', [Helper::$status['in-verification'], Helper::$status['completed']])
                    ->where(\DB::raw("DATE_FORMAT(date, '%Y-%m-%d')"), '>=', date('Y-m-d', strtotime($request->start)))
                    ->where(\DB::raw("DATE_FORMAT(date, '%Y-%m-%d')"), '<=', date('Y-m-d', strtotime($request->end)));

                    $allFlaggedItemData = $totalChecklists->clone()->whereIn('status', [Helper::$status['in-verification'], Helper::$status['completed']])->get();

                    $count_truthy_temp = 0;
                    $count_falsy_temp = 0;

                    foreach ($totalChecklists->clone()->whereIn('status', [Helper::$status['in-verification'], Helper::$status['completed']])->orderBy('date', 'DESC')->get() as $count_pending_inspection_temp_row) {
                        $count_truthy_temp += count(Helper::getBooleanFields($count_pending_inspection_temp_row['data'])['truthy']);
                        $count_falsy_temp += count(Helper::getBooleanFields($count_pending_inspection_temp_row['data'])['falsy']);
                    }

                    $pendingBarChartData[] = ($count_truthy_temp + $count_falsy_temp > 0 ? number_format((($count_truthy_temp / ($count_truthy_temp + $count_falsy_temp)) * 100), 2) : 0);
                    // $pendingBarChartData[] = $count_truthy_temp;
    
                    $theStore = Store::find($thisStore);

                    foreach ($totalChecklists->clone()->whereIn('status', [Helper::$status['in-verification'], Helper::$status['completed']])->get() as $thisCList) {
                        $tempTotal = count(Helper::getBooleanFields($thisCList->data)['falsy']);

                        if ($tempTotal > 0) {
                            $flaggedArray[] = [
                                'location_name' => isset($thisCList->parent->actstore->name) ? $thisCList->parent->actstore->name : '-',
                                'inspected_by' => isset($thisCList->parent->user->name) ? $thisCList->parent->user->name : '-',
                                'checklist_name' => isset($thisCList->parent->parent->checklist->name) ? $thisCList->parent->parent->checklist->name : '-',
                                'total_no' =>  $tempTotal,
                                'button' => '<button type="submit" class="btn btn-sm btn-primary open-detail" data-bs-target="#viewData" data-bs-toggle="modal" data-id="' . $thisCList->id . '"> View </button>'
                            ];   
                            $totalFlagged += $tempTotal;
                        }
                    }
                }

                if (count($flaggedArray) > 0) {
                    foreach ($flaggedArray as $row) {
                        $tableData .= '<tr>
                        <td> ' . $row['location_name'] . ' </td>
                        <td> ' . $row['inspected_by'] . ' </td>
                        <td> ' . $row['checklist_name'] . ' </td>
                        <td> ' . $row['total_no'] . ' </td>
                        <td> ' . $row['button'] . ' </td>                        
                        </tr>';
                    }
                }

                $data['flagged_items'] = $totalFlagged;
                $data['flagged_items_table'] = $tableData;
                $data['bar_chart_data'] = [
                    $pendingBarChartData
                ];
            }

            return response()->json(['data' => $data]);
        }

        $page_title = 'DoM Dashboard';
        return view('dashboard.index', compact('page_title'));
    }

    public function detail2(Request $request) {
        $Task = ChecklistTask::find($request->id);

        if ($Task) {
            $inlineTable = '<table class="table w-100 table-striped table-bordered">
            <thead>
               <tr> 
               <th> Questions </th>
               <th> Answer </th>
               </tr>
            </thead>
            <tbody>';

            $iterableData = collect(Helper::getBooleanFields($Task->data)['falsy'])->pluck('label')->all();
            $iterableData2 = collect(Helper::getBooleanFields($Task->data)['falsy'])->pluck('value_label')->all();

            foreach ($iterableData as $thisKey => $thisQuestion) {
                $inlineTable .= "<tr><td>
            {$thisQuestion}
                </td>
                <td>" . (isset($iterableData2[$thisKey]) ? $iterableData2[$thisKey] : '') . "</td>
                </tr>";
            }

            $inlineTable .= '
            </tbody>
            </table>';

            return response()->json(['status' => true, 'html' => $inlineTable]);
        }

        return response()->json(['status' => false]);
    }










    
}
