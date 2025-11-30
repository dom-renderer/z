<?php

namespace App\Http\Controllers;

use App\Models\Production;
use App\Models\ProductionItem;
use App\Models\ProductionLog;
use App\Models\ProductionProduct;
use App\Helpers\Helper;
use Illuminate\Http\Request;
use App\Models\Shift;

class ProductionController extends Controller
{
    public function index(Request $request)
    {
        $isDispatch = $request->has('dispatch') && $request->dispatch == '1';
        $isExpire = $request->has('expire') && $request->expire == '1';
        
        if ($request->ajax()) {
            $query = ProductionItem::with(['production.shift', 'product', 'unit', 'user'])
                ->orderBy('id', 'DESC');

            // Filter by status based on mode
            if ($isDispatch) {
                $query->whereHas('production', function ($q) use ($request) {
                    $q->where('status', 'dispatch');
                });
            } else {
                $query->whereHas('production', function ($q) use ($request) {
                    $q->whereIn('status', [ 'pending', 'expire' ]);
                });
            }

            // Date range filters
            if ($request->filled('shift_filter')) {
                $query->whereHas('production', function ($q) use ($request) {
                    $q->where('shift_id', $request->input('shift_filter'));
                });
            }

            // Date range filters
            if ($request->filled('from_date')) {
                $query->whereHas('production', function ($q) use ($request) {
                    $q->whereDate('production_date', '>=', date('Y-m-d', strtotime($request->input('from_date'))));
                });
            }

            if ($request->filled('to_date')) {
                $query->whereHas('production', function ($q) use ($request) {
                    $q->whereDate('production_date', '<=', date('Y-m-d', strtotime($request->input('to_date'))));
                });
            }

            // Category filter
            if ($request->filled('category_id')) {
                $query->whereHas('product', function ($q) use ($request) {
                    $q->where('category_id', $request->input('category_id'));
                });
            }

            // Product filter
            if ($request->filled('product_id')) {
                $query->where('product_id', $request->input('product_id'));
            }

            // UOM filter
            if ($request->filled('uom_id')) {
                $query->where('unit_id', $request->input('uom_id'));
            }

            // User filter
            if ($request->filled('user_id')) {
                $query->where('user_id', $request->input('user_id'));
            }

            return datatables()
                ->eloquent($query)
                ->addColumn('production_number', function ($row) {
                    return $row->production->production_number ?? '';
                })
                ->addColumn('production_date', function ($row) {
                    return date('d-m-Y H:i', strtotime($row->production->production_date));
                })
                ->addColumn('status', function ($row) {
                    $color = Helper::$productionStatusColors[$row->production->status] ?? 'secondary';
                    $statusText = Helper::$productionStatuses[$row->production->status] ?? ucfirst($row->production->status);
                    return "<span class='badge bg-{$color}'>" . $statusText . "</span>";
                })
                ->addColumn('total_items', function ($row) {
                    return $row->quantity;
                })
                ->addColumn('products', function ($row) {
                    return $row->product->name;
                })
                ->addColumn('units', function ($row) {
                    return $row->unit->name;
                })
                ->addColumn('users', function ($row) {
                    return $row->user->name ?? 'N/A';
                })
                ->addColumn('action', function ($row) use ($isDispatch, $isExpire) {
                    $id = encrypt($row->production->id);
                    $action = '';

                    $show_url = route( 'production.show', $id );
                    if ( $isDispatch ) {
                        $show_url .= "?dispatch=1";
                    } elseif ( $isExpire ) {
                        $show_url .= "?expire=1";
                    }

                    if (request()->has('is_web_view') && request()->is_web_view == '1') {
                        if (!empty($show_url) && strpos($show_url, '?') !== false) {
                            $show_url .= '&is_web_view=1';
                        } else {
                            $show_url .= '?is_web_view=1';
                        }
                    }

                    $action .= '<a href="' . $show_url . '" class="btn btn-info btn-sm me-2">Show</a>';

                    if (auth()->check()) {
                        if (!$isDispatch && auth()->check() && auth()->user()->can('production.expire.create') && $row->production->status == 'pending') {
                            $action .= '<form method="POST" action="'.route("production.expire", $id).'" style="display:inline;">'
                                    . csrf_field()
                                    . '<button type="submit" class="btn btn-danger btn-sm expire-btn">Wastage</button></form>';
                        }
                    }

                    return $action;
                })
                ->addColumn('shift_name', function ($row) {
                    return $row->production->shift->title ?? '';
                })
                ->rawColumns(['status', 'action'])
                ->toJson();
        }

        if ($isDispatch) {
            $page_title = 'Production Dispatch';
            $page_description = 'Manage dispatched productions here';
        } else {
            $page_title = 'Production Management';
            $page_description = 'Manage productions here';
        }

        if ($request->has('is_web_view') && $request->is_web_view == '1') {
            return view('production.web-view.index', compact('page_title', 'page_description', 'isDispatch', 'isExpire'));
        } else {
            return view('production.index', compact('page_title', 'page_description', 'isDispatch', 'isExpire'));
        }
    }

    public function create(Request $request)
    {
        $lastProduction = Production::select('id')->orderBy('id', 'DESC')->first();
        $productionNo = 'PRD-' . str_pad(($lastProduction ? $lastProduction->id + 1 : 1), 6, '0', STR_PAD_LEFT);
        $products = ProductionProduct::with('uoms')->where('status', 'active')->get();
        $isDispatch = $request->has('dispatch') && $request->dispatch == '1';
        $isExpire = $request->has('expire') && $request->expire == '1';
        $now = \Carbon\Carbon::now()->format('H:i:s');
        $shifts = Shift::get();
        
        if ($request->has('is_web_view') && $request->is_web_view == '1') {
            return view('production.web-view.create', compact('productionNo', 'products', 'isDispatch', 'isExpire', 'shifts', 'now'));
        } else {
            return view('production.create', compact('productionNo', 'products', 'isDispatch', 'isExpire', 'shifts', 'now'));
        }
    }

    public function store(Request $request)
    {
        $productionDate = !empty($request->production_date)
            ? date('Y-m-d H:i:s', strtotime($request->production_date))
            : now();

        if ($request->has('dispatch') && $request->dispatch == '1') {
            $status = 'dispatch';
        } elseif ($request->has('expire') && $request->expire == '1') {
            $status = 'expire';
        } else {
            $status = 'pending';
        }

        $production = Production::create([
            'production_number' => $request->production_number,
            'production_date'   => $productionDate,
            'shift_id'          => $request->shift_id,
            'status'            => $status,
        ]);

        $users    = $request->input('user');
        $products = $request->input('product');
        $units    = $request->input('unit');
        $qtys     = $request->input('qty');

        foreach ($products as $index => $productId) {
            if (!empty($productId) && !empty($qtys[$index])) {
                ProductionItem::create([
                    'production_id' => $production->id,
                    'user_id'       => $users[$index] ?? null,
                    'product_id'    => $productId,
                    'unit_id'       => $units[$index] ?? null,
                    'quantity'      => $qtys[$index] ?? 0,
                ]);
            }
        }

        $comment = 'Production created';
        $this->createProductionLog($production->id, $comment);

        if ($status === 'dispatch') {
            $comment = 'Production dispatched';
            $this->createProductionLog($production->id, $comment);
            $toPass = ['dispatch' => '1'];

            if ($request->has('is_web_view') && $request->is_web_view == '1') {
                $toPass['is_web_view'] = '1';
            }

            return redirect()->route('production.index', $toPass)->with('success', 'Production dispatched successfully.');
        } elseif ($status === 'expire') {
            $comment = 'Production Wastage';
            $this->createProductionLog($production->id, $comment);
            $toPass = ['expire' => '1'];

            if ($request->has('is_web_view') && $request->is_web_view == '1') {
                $toPass['is_web_view'] = '1';
            }

            return redirect()->route('production.index', $toPass)->with('success', 'Wastage added successfully.');
        }

        if ($request->has('is_web_view') && $request->is_web_view == '1') {
            return redirect()->route('production.index', ['is_web_view' => '1'])->with('success', 'Production created successfully.');
        } else {
            return redirect()->route('production.index')->with('success', 'Production created successfully.');
        }
    }

    public function show( Request $request, $id )
    {
        $production = Production::with(['items.product', 'items.unit', 'items.user'])->findOrFail(decrypt($id));
        $logs = ProductionLog::where('production_id', $production->id)->orderBy('created_at', 'DESC')->get();
        $isDispatch = $request->has('dispatch') && $request->dispatch == '1';
        $isExpire = $request->has('expire') && $request->expire == '1';

        $page_title = 'Production Details';

        if ($request->has('is_web_view') && $request->is_web_view == '1') {
            return view('production.web-view.show', compact('page_title', 'production', 'logs', 'id', 'isDispatch', 'isExpire'));
        } else {
            return view('production.show', compact('page_title', 'production', 'logs', 'id', 'isDispatch', 'isExpire'));
        }
    }

    public function dispatch($id)
    {
        $production = Production::findOrFail(decrypt($id));
        $production->update(['status' => 'dispatch']);

        $comment = 'Production dispatched';
        $this->createProductionLog($production->id, $comment);

        return redirect()->route('production.index')->with('success', 'Production dispatched successfully.');
    }

    public function expire($id)
    {
        $production = Production::findOrFail(decrypt($id));
        $production->update(['status' => 'expire']);

        $comment = 'Production added to wastage';
        $this->createProductionLog($production->id, $comment);

        return redirect()->route('production.index')->with('success', 'Production added to wastage successfully.');
    }

    public function exportExcel(Request $request)
    {
        $query = ProductionItem::with(['production.shift', 'product', 'unit', 'user'])->orderBy('id', 'DESC');

        $this->applyFilters($query, $request, true);

        $productions = $query->get();
        $t = $w = 0;

        $data = [];

        foreach ($productions as $item) {
            if ($item->production->status == 'pending') {
                $t += $item->quantity;
            } else if ($item->production->status == 'expire') {
                $w += $item->quantity;
            }

            $data[] = [
                'Production Number' => $item->production->production_number,
                'User' => $item->user->name ?? '',
                'Production Date' => date('d-m-Y H:i', strtotime($item->production->production_date)),
                'Production Shift' => $item->production->shift->title ?? 'N/A',
                'Product' => $item->product->name ?? 'N/A',
                'Unit' => $item->unit->name ?? 'N/A',
                'Quantity' => $item->quantity,
                'Status' => Helper::$productionStatuses[$item->production->status] ?? ucfirst($item->production->status),
                'Created At' => $item->production->created_at->format('d-m-Y H:i'),
            ];
        }        

        $data[] = [];

        $data[] = [
            'Production Number' => 'TOTAL',
            'User' => '',
            'Production Date' => '',
            'Production Shift' => '',
            'Product' => '',
            'Unit' => '',
            'Quantity' => '',
            'Status' => '',
            'Created At' => $t,
        ];

        $data[] = [
            'Production Number' => 'WASTAGE',
            'User' => '',
            'Production Date' => '',
            'Production Shift' => '',
            'Product' => '',
            'Unit' => '',
            'Quantity' => '',
            'Status' => '',
            'Created At' => $w,
        ];

        $data[] = [
            'Production Number' => 'GRAND TOTAL',
            'User' => '',
            'Production Date' => '',
            'Production Shift' => '',
            'Product' => '',
            'Unit' => '',
            'Quantity' => '',
            'Status' => '',
            'Created At' => $t - $w,
        ];

        $filename = 'productions_' . date('Y-m-d_H-i-s') . '.xlsx';
        
        return \Excel::download(new \App\Exports\ProductionExport($data), $filename);
    }

    public function exportPdf(Request $request)
    {
        $query = ProductionItem::with(['production.shift', 'product', 'unit', 'user'])->orderBy('id', 'DESC');

        $this->applyFilters($query, $request, true);

        $productions = $query->get();
        $total = $wastage = 0;

        $pdf = \PDF::loadView('production.pdf', compact('productions', 'total', 'wastage'));
        
        return $pdf->download('productions_' . date('Y-m-d_H-i-s') . '.pdf');
    }

    public static function applyFilters($query, $request, $isItemQuery = false)
    {
        if ($isItemQuery) {
            $isDispatch = $request->has('dispatch') && $request->dispatch == '1';

            if ($isDispatch) {
                $query->whereHas('production', function ($q) use ($request) {
                    $q->where('status', 'dispatch');
                });
            } else {
                $query->whereHas('production', function ($q) use ($request) {
                    $q->whereIn('status', ['pending', 'expire']);
                });
            }

            if ($request->filled('shift_id') && $request->input('shift_id') != 'null') {
                $query->whereHas('production', function ($q) use ($request) {
                    $q->where('shift_id', $request->input('shift_id'));
                });
            }

            if ($request->filled('from_date')) {
                $query->whereHas('production', function ($q) use ($request) {
                    $q->whereDate('production_date', '>=', date('Y-m-d H:i:s', strtotime($request->input('from_date') . ' 00:00:00')));
                });
            }

            if ($request->filled('to_date')) {
                $query->whereHas('production', function ($q) use ($request) {
                    $q->whereDate('production_date', '<=', date('Y-m-d H:i:s', strtotime($request->input('to_date') . ' 00:00:00')));
                });
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
                $query->where('unit_id', $request->input('uom_id'));
            }

            if ($request->filled('user_id')) {
                $query->where('user_id', $request->input('user_id'));
            }
        } else {
            $isDispatch = $request->has('dispatch') && $request->dispatch == '1';

            if ($isDispatch) {
                $query->where('status', 'dispatch');
            } else {
                $query->whereIn('status', ['pending', 'expire']);
            }

            if ($request->filled('shift_id') && $request->input('shift_id') != 'null') {
                $query->where('shift_id', $request->input('shift_id'));
            }

            if ($request->filled('from_date')) {
                $query->whereDate('production_date', '>=', $request->input('from_date'));
            }

            if ($request->filled('to_date')) {
                $query->whereDate('production_date', '<=', $request->input('to_date'));
            }

            if ($request->filled('category_id')) {
                $query->whereHas('items.product', function ($q) use ($request) {
                    $q->where('category_id', $request->input('category_id'));
                });
            }

            if ($request->filled('product_id')) {
                $query->whereHas('items', function ($q) use ($request) {
                    $q->where('product_id', $request->input('product_id'));
                });
            }

            if ($request->filled('uom_id')) {
                $query->whereHas('items', function ($q) use ($request) {
                    $q->where('unit_id', $request->input('uom_id'));
                });
            }

            if ($request->filled('user_id')) {
                $query->whereHas('items', function ($q) use ($request) {
                    $q->where('user_id', $request->input('user_id'));
                });
            }
        }
    }

    private function createProductionLog($productionId, $comment)
    {
        ProductionLog::create([
            'production_id' => $productionId,
            'added_by'      => auth()->check() ? auth()->id() : 1,
            'comment'       => $comment,
        ]);
    }

}
