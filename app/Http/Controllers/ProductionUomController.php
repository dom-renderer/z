<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProductionUom;
use App\Http\Requests\ProductionUomRequest;
use Yajra\DataTables\Facades\DataTables;

class ProductionUomController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = ProductionUom::query();

            return DataTables::of($query)
                ->addIndexColumn()
                ->editColumn('status', function ($row) {
                    return $row->status == 'active' 
                        ? '<span class="bg-success badge">Active</span>' 
                        : '<span class="bg-danger badge">Inactive</span>';
                })
                ->addColumn('action', function ($row) {
                    $action = '';
                    if (auth()->user()->can('production.uom.edit')) {
                        $action .= '<a href="'.route('production.uoms.edit', encrypt($row->id)).'" class="btn btn-info btn-sm me-2">Edit</a>';
                    }
                    if (auth()->user()->can('production.uom.edit')) {
                        $action .= '<form style="display:inline;" method="POST" action="'.route("production.uoms.enable-disable", encrypt($row->id)).'">'
                            . csrf_field();
                        if ($row->status == 'active') {
                            $action .= '<button type="submit" class="btn btn-danger btn-sm me-2 status-changer" data-description="Disable this UOM" data-blable="Disable">Disable</button>';
                        } else {
                            $action .= '<button type="submit" class="btn btn-success btn-sm me-2 status-changer" data-description="Enable this UOM" data-blable="Enable">Enable</button>';
                        }
                        $action .= '</form>';
                    }
                    if (auth()->user()->can('production.uom.delete')) {
                        $action .= '<form method="POST" action="'.route('production.uoms.destroy', encrypt($row->id)).'" style="display:inline;">'
                            . csrf_field()
                            . method_field('DELETE')
                            . '<button type="submit" class="btn btn-danger btn-sm deleteGroup">Delete</button></form>';
                    }
                    return $action;
                })
                ->rawColumns(['status','action'])
                ->make(true);
        }

        $page_title = 'Production UOMs';
        $page_description = 'Manage Units of Measure here';
        return view('production.uoms.index', compact('page_title', 'page_description'));
    }

    public function create()
    {
        $page_title = 'Add UOM';
        $moduleLink = route('production.uoms.index');
        return view('production.uoms.create', compact('page_title', 'moduleLink'));
    }

    public function store(ProductionUomRequest $request)
    {
        $uom = new ProductionUom();
        $uom->code = $request->code;
        $uom->name = $request->name;
        $uom->status = $request->status;
        $uom->save();

        return redirect()->route('production.uoms.index')->with('success', 'UOM added successfully.');
    }

    public function edit($id)
    {
        $did = decrypt($id);
        $uom = ProductionUom::findOrFail($did);
        $page_title = 'Edit UOM';
        $moduleLink = route('production.uoms.index');
        return view('production.uoms.edit', compact('page_title','uom','moduleLink','id'));
    }

    public function update(ProductionUomRequest $request, $id)
    {
        $uom = ProductionUom::findOrFail(decrypt($id));
        $uom->code = $request->code;
        $uom->name = $request->name;
        $uom->status = $request->status;
        $uom->save();

        return redirect()->route('production.uoms.index')->with('success', 'UOM updated successfully.');
    }

    public function destroy($id)
    {
        $uom = ProductionUom::findOrFail(decrypt($id));

        if ($uom->products()->exists()) {
            return redirect()->route('production.uoms.index')->with('error', 'Cannot delete UOM because products are assigned.');
        }

        $uom->delete();
        return redirect()->route('production.uoms.index')->with('success', 'UOM deleted successfully.');
    }

    public function enableDisable($id)
    {
        $uom = ProductionUom::findOrFail(decrypt($id));
        $uom->status = $uom->status == 'active' ? 'inactive' : 'active';
        $uom->save();

        return redirect()->back()->with('success','UOM status changed successfully.');
    }

    public function uomSelect2(Request $request)
    {
        $searchQuery = $request->input('searchQuery', '');
        $page = $request->input('page', 1);
        $limit = 10;
        $offset = ($page - 1) * $limit;

        $query = ProductionUom::where('status', 'active');

        if (!empty($searchQuery)) {
            $query->where(function($q) use ($searchQuery) {
                $q->where('name', 'LIKE', "%{$searchQuery}%")
                  ->orWhere('code', 'LIKE', "%{$searchQuery}%");
            });
        }

        // Filter by product if provided
        if ($request->filled('product_id')) {
            $query->whereHas('products', function($q) use ($request) {
                $q->where('production_products.id', $request->input('product_id'));
            });
        }

        $total = $query->count();
        $uoms = $query->offset($offset)->limit($limit)->get();

        $items = $uoms->map(function($uom) {
            return [
                'id' => $uom->id,
                'text' => $uom->name . ($uom->code ? ' (' . $uom->code . ')' : '')
            ];
        });

        return response()->json([
            'items' => $items,
            'pagination' => [
                'more' => ($offset + $limit) < $total
            ]
        ]);
    }
}
