<?php

namespace App\Http\Controllers;

use Illuminate\Pagination\LengthAwarePaginator;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use App\Exports\ImportProductionPlanning;
use App\Models\ProductionPlanningHistory;
use App\Models\ProductionProductUom;
use Illuminate\Support\Facades\DB;
use App\Models\ProductionPlanning;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\ProductionCategory;
use App\Models\ProductionProduct;
use App\Models\SchedulingImport;
use App\Models\ProductionItem;
use App\Models\ProductionUom;
use Illuminate\Http\Request;
use App\Models\Production;
use App\Models\Shift;
use Carbon\Carbon;

class ProductionDashboardController extends Controller
{
    public function index()
    {
        $page_title = 'Production Dashboard';
        $page_description = 'View aggregated production by category, product and unit';
        return view('production.dashboard', compact('page_title', 'page_description'));
    }

    public function data(Request $request) {
        $query = ProductionPlanning::with(['shift', 'product.category', 'unit', 'user'])
            ->orderBy('id', 'DESC');

        if ($request->filled('from_date')) {
            $query->whereDate('shift_time', '>=', date('Y-m-d', strtotime($request->input('from_date'))));
        }

        if ($request->filled('to_date')) {
            $query->whereDate('shift_time', '<=', date('Y-m-d', strtotime($request->input('to_date'))));
        }

        if ($request->filled('category_id')) {
            $query->whereHas('product', function ($q) use ($request) {
                $q->where('category_id', $request->input('category_id'));
            });
        }

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->input('product_id'));
        }

        if ($request->filled('uom_id')) {
            $query->where('uom_id', $request->input('uom_id'));
        }

        if ($request->filled('user_id')) {
            $query->where('added_by', $request->input('user_id'));
        }

        $viewType = $request->view_type == 'table' ? 'table' : 'card';

        $uniqueQuery = $query->select('product_id', 'uom_id', 'shift_id', 'added_by')->groupBy('product_id', 'uom_id')->get()->toArray();
        $productsToShow = [];

        $allOverOrdered = $allOverProductionRequired = $allOverProduced = $allOverRemaining = 0;

        $categoriesToShow = ProductionCategory::select('id', 'name')->when($request->filled('category_id'), function ($builder) {
            $builder->where('id', request('category_id'));
        })->toBase()->pluck('name', 'id')->toArray();

        foreach ($categoriesToShow as $cId => &$category) {
            $category = [
                'name' => $category,
                'ordered' => 0,
                'produced' => 0,
                'pending' => 0
            ];
        }

        $mrngShift = Shift::where(\DB::raw('LOWER(title)'), 'LIKE', '%morning%')->first();
        $nytShift = Shift::where(\DB::raw('LOWER(title)'), 'LIKE', '%night%')->first();

        $morningShiftStartTime = $mrngShift->start ?? '08:00:00';
        $morningShiftEndTime = $mrngShift->end ?? '19:59:59';

        $nightShiftStartTime = $nytShift->start ?? '20:00:00';
        $nightShiftEndTime = $nytShift->start ?? '07:59:59';

        $morningShift = $mrngShift->id ?? null;
        $nightShift = $nytShift->id ?? null;

        foreach ($uniqueQuery as $row) {
            $startDate = Carbon::parse($request->input('from_date'));
            $endDate = Carbon::parse($request->input('to_date'));

            if (!$startDate->greaterThan($endDate)) {
                $currentDate = $startDate;
                
                $orderedP = $producedP = $pendingP = $ordered = $produced = $pending = 0;

                while ($currentDate <= $endDate) {

                    /**
                     * Calculation
                     * **/

                    if ($morningShift == $request->shift_filter) {
                        $productionRequiredForThisRow = ProductionPlanning::where('product_id', $row['product_id'])->where('uom_id', $row['uom_id'])
                        ->whereDate('shift_time', $currentDate->format('Y-m-d'))
                        ->where('shift_id', $request->shift_filter)
                        ->sum('total');

                        $producedForThisRowPending = ProductionItem::where('product_id', $row['product_id'])->where('unit_id', $row['uom_id'])
                        ->whereHas('production', function ($innerBuilder) use ($row, $currentDate) {
                            $innerBuilder->whereDate('production_date', $currentDate->format('Y-m-d'))
                            ->where('shift_id', request('shift_filter'))
                            ->where('status', 'pending');
                        })
                        ->sum('quantity');

                        $producedForThisRowWastage = ProductionItem::where('product_id', $row['product_id'])->where('unit_id', $row['uom_id'])
                        ->whereHas('production', function ($innerBuilder) use ($row, $currentDate) {
                            $innerBuilder->whereDate('production_date', $currentDate->format('Y-m-d'))
                            ->where('shift_id', request('shift_filter'))
                            ->where('status', 'expire');
                        })
                        ->sum('quantity');

                        $producedForThisRow = $producedForThisRowPending - $producedForThisRowWastage;

                    } else if ($nightShift == $request->shift_filter) {
                        $productionRequiredForThisRow = ProductionPlanning::where('product_id', $row['product_id'])->where('uom_id', $row['uom_id'])
                        ->whereIn('shift_id', [$morningShift, $nightShift])
                        ->where(function ($innerBuilder) use ($currentDate) {
                            $innerBuilder->whereDate('shift_time', $currentDate->format('Y-m-d'))
                            ->orWhereDate('shift_time', date('Y-m-d', strtotime($currentDate->format('Y-m-d') . ' +1 day')));
                        })
                        ->sum('total');

                        $producedForThisRowPending = ProductionItem::where('product_id', $row['product_id'])->where('unit_id', $row['uom_id'])
                        ->whereHas('production', function ($innerBuilder) use ($currentDate, $morningShift, $nightShift) {
                            $innerBuilder->whereIn('shift_id', [$morningShift, $nightShift])
                            ->where('status', 'pending')
                            ->where(function ($innerInnerBuilder) use ($currentDate) {
                                $innerInnerBuilder->whereDate('production_date', $currentDate->format('Y-m-d'))
                                ->orWhereDate('production_date', date('Y-m-d', strtotime($currentDate->format('Y-m-d') . ' +1 day')));
                            });
                        })
                        ->sum('quantity');

                        $producedForThisRowWastage = ProductionItem::where('product_id', $row['product_id'])->where('unit_id', $row['uom_id'])
                        ->whereHas('production', function ($innerBuilder) use ($currentDate, $morningShift, $nightShift) {
                            $innerBuilder->whereIn('shift_id', [$morningShift, $nightShift])
                            ->where('status', 'expire')
                            ->where(function ($innerInnerBuilder) use ($currentDate) {
                                $innerInnerBuilder->whereDate('production_date', $currentDate->format('Y-m-d'))
                                ->orWhereDate('production_date', date('Y-m-d', strtotime($currentDate->format('Y-m-d') . ' +1 day')));
                            });
                        })
                        ->sum('quantity');

                        $producedForThisRow = $producedForThisRowPending - $producedForThisRowWastage;
                    } else {
                        $productionRequiredForThisRow = ProductionPlanning::where('product_id', $row['product_id'])->where('uom_id', $row['uom_id'])
                        ->whereIn('shift_id', [$morningShift, $nightShift])
                        ->where(function ($innerBuilder) use ($currentDate) {
                            $innerBuilder->whereDate('shift_time', $currentDate->format('Y-m-d'))
                            ->orWhereDate('shift_time', date('Y-m-d', strtotime($currentDate->format('Y-m-d') . ' +1 day')));
                        })
                        ->sum('total');

                        $producedForThisRowPending = ProductionItem::where('product_id', $row['product_id'])->where('unit_id', $row['uom_id'])
                        ->whereHas('production', function ($innerBuilder) use ($currentDate, $morningShift, $nightShift) {
                            $innerBuilder->whereIn('shift_id', [$morningShift, $nightShift])
                            ->where('status', 'pending')
                            ->where(function ($innerInnerBuilder) use ($currentDate) {
                                $innerInnerBuilder->whereDate('production_date', $currentDate->format('Y-m-d'))
                                ->orWhereDate('production_date', date('Y-m-d', strtotime($currentDate->format('Y-m-d') . ' +1 day')));
                            });
                        })
                        ->sum('quantity');

                        $producedForThisRowWastage = ProductionItem::where('product_id', $row['product_id'])->where('unit_id', $row['uom_id'])
                        ->whereHas('production', function ($innerBuilder) use ($currentDate, $morningShift, $nightShift) {
                            $innerBuilder->whereIn('shift_id', [$morningShift, $nightShift])
                            ->where('status', 'expire')
                            ->where(function ($innerInnerBuilder) use ($currentDate) {
                                $innerInnerBuilder->whereDate('production_date', $currentDate->format('Y-m-d'))
                                ->orWhereDate('production_date', date('Y-m-d', strtotime($currentDate->format('Y-m-d') . ' +1 day')));
                            });
                        })
                        ->sum('quantity');

                        $producedForThisRow = $producedForThisRowPending - $producedForThisRowWastage;
                    }


                    if ($morningShift == $request->shift_filter) {
                        $ordered = $productionRequiredForThisRow;
                        $produced = $producedForThisRow;
                        $pending = $ordered - $produced;
                    } else if ($nightShift == $request->shift_filter) {
                        $ordered = $productionRequiredForThisRow;
                        $produced = $producedForThisRow;
                        $pending = $ordered - $produced;
                    } else {
                        $ordered = $productionRequiredForThisRow;
                        $produced = $producedForThisRow;
                        $pending = $ordered - $produced;
                    }

                    $orderedP += $ordered;
                    $producedP += $produced;
                    $pendingP + $pending;

                    $allOverOrdered += $ordered;
                    $allOverProductionRequired += $ordered;
                    $allOverProduced += $produced;
                    $allOverRemaining += $pending;

                    /**
                     * Calculation
                     * **/

                    $currentDate->addDay();
                }

                if (isset($categoriesToShow[$row['product']['category_id']])) {
                    $categoriesToShow[$row['product']['category_id']]['ordered'] += $orderedP;
                    $categoriesToShow[$row['product']['category_id']]['produced'] += $producedP;
                    $categoriesToShow[$row['product']['category_id']]['pending'] += $orderedP - $producedP;
                }

                $productsToShow[] = [
                    'product_id' => $row['product']['id'] ?? null,
                    'product_name' => $row['product']['name'] ?? 'N/A',
                    'category_name' => $row['product']['category']['name'] ?? 'N/A',
                    'unit_id' => $row['unit']['id'] ?? null,
                    'unit_name' => $row['unit']['name'] ?? 'N/A',
                    'ordered' => $orderedP,
                    'produced' => $producedP,
                    'pending' => $orderedP - $producedP,
                    'percentage' => round($ordered > 0 ? (($produced / $ordered) * 100) : 0)
                ];
            }
        }

        $totalOrders = ProductionPlanning::with(['shift', 'product.category', 'unit', 'user'])
            ->orderBy('id', 'DESC');

        $totalOrders->when($request->shift_filter == $morningShift, function ($builder) use ($morningShift) {
            $builder->where('shift_id', $morningShift);
        });

        if ($request->filled('category_id')) {
            $totalOrders->whereHas('product', function ($q) use ($request) {
                $q->where('category_id', $request->category_id);
            });
        }

        if ($request->filled('from_date')) {
            $totalOrders->whereDate('shift_time', '>=', date('Y-m-d', strtotime($request->input('from_date'))));
        }

        if ($request->filled('to_date')) {
            $totalOrders->whereDate('shift_time', '<=', date('Y-m-d', strtotime($request->input('to_date'))));
        }

        if ($request->filled('product_id')) {
            $totalOrders->where('product_id', $request->product_id);
        }

        if ($request->filled('uom_id')) {
            $totalOrders->where('uom_id', $request->uom_id);
        };

        $otherStatistics = [
            'total_orders' => $totalOrders->count(),
            'production_required' => $allOverProductionRequired,
            'produced' => $allOverProduced,
            'required' => $allOverRemaining
        ];

        $html = view('production.appendable-dashboard', compact('categoriesToShow', 'productsToShow', 'otherStatistics', 'viewType'))->render();

        return response()->json(['html' => $html]);
    }

    public static function calculateShiftDateTime($date, $shiftStartTime, $shiftEndTime)
    {
        $date = date('Y-m-d', strtotime($date));

        $morningShift = Shift::where(\DB::raw('LOWER(title)'), 'LIKE', '%morning%')->first()->end ?? '19:59:00';
        $nightShift = Shift::where(\DB::raw('LOWER(title)'), 'LIKE', '%night%')->first()->end ?? '07:59:00';

        $start = Carbon::parse("$date $shiftStartTime");
        $end = Carbon::parse("$date $shiftEndTime");

        if ($end->lessThan($start)) {
            $end->addDay();
        }

        if ($shiftEndTime === $morningShift) {
            $end->setSecond(59);
        } elseif ($shiftEndTime === $nightShift) {
            $end->setSecond(59);
        }

        return [
            $start->format('Y-m-d H:i:s'),
            $end->format('Y-m-d H:i:s')
        ];
    }
}
