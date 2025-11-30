<?php

namespace App\Http\Controllers;

use App\Models\ProductionProduct;
use App\Models\ProductionCategory;
use App\Models\ProductionProductUom;
use App\Models\ProductionUom;
use Illuminate\Http\Request;

class ProductionProductController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $products = ProductionProduct::with(['category', 'uoms']);

            return datatables()
                ->eloquent($products)
                ->addIndexColumn()
                ->editColumn('category', function ($row) {
                    return $row->category->name ?? '-';
                })
                ->addColumn('uoms', function ($row) {
                    return $row->uoms->pluck('code')->implode(', ') ?: '-';
                })
                ->editColumn('status', function ($row) {
                    return $row->status == 'active'
                        ? '<span class="bg-success badge">Active</span>'
                        : '<span class="bg-danger badge">Inactive</span>';
                })
                ->addColumn('action', function ($row) {
                    $id = encrypt($row->id);
                    $action = '';
                    $show_url = route('production.products.show', $id);
                    $edit_url = route('production.products.edit', $id);
                    $destroy_url = route('production.products.destroy', $id);

                    if ( auth()->user()->can('production.product.show') ) {
                        $action .= '<a href="'.$show_url.'" class="btn btn-warning btn-sm me-2">Show</a>';
                    }

                    if ( auth()->user()->can('production.product.edit') ) {
                        $action .= '<a href="'.$edit_url.'" class="btn btn-info btn-sm me-2">Edit</a>';
                    }

                    if ( auth()->user()->can('production.product.delete') ) {
                        $action .= '<form method="POST" action="'.$destroy_url.'" style="display:inline;">'
                            . csrf_field()
                            . method_field('DELETE')
                            . '<button type="submit" class="btn btn-danger btn-sm deleteGroup">Delete</button></form>';
                    }

                    return $action;
                })
                ->rawColumns(['status', 'action'])
                ->make(true);
        }

        $page_title = 'Production Products';
        $page_description = 'Manage production products here';

        return view('production.products.index', compact('page_title', 'page_description'));
    }

    public function create()
    {
        $categories = ProductionCategory::active()->get();
        $uoms = ProductionUom::where('status', 'active')->get();
        $page_title = 'Add Production Product';

        return view('production.products.create', compact('categories', 'uoms', 'page_title'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:production_categories,id',
            'sku' => 'required|string|max:255|unique:production_products,sku',
            'name' => 'required|string|max:255',
            'status' => 'nullable|in:active,inactive',
            'uom_ids' => 'nullable|array',
            'uom_ids.*' => 'integer|exists:production_uoms,id',
        ]);

        $product = ProductionProduct::create([
            'category_id' => $validated['category_id'],
            'sku' => $validated['sku'],
            'name' => $validated['name'],
            'status' => $validated['status'] ?? 'active',
            'added_by' => auth()->id(),
            'updated_by' => null,
        ]);

        if (!empty($validated['uom_ids'])) {
            $product->uoms()->sync(
                collect($validated['uom_ids'] ?? [])
                    ->mapWithKeys(fn($id) => [
                        $id => [
                            'created_at' => now(),
                            'updated_at' => now(),
                        ],
                    ])
                    ->toArray()
            );
        }

        return redirect()->route('production.products.index')->with('success', 'Product added successfully.');
    }

    public function edit($id)
    {
        $did = decrypt($id);
        $product = ProductionProduct::with('uoms')->findOrFail($did);
        $categories = ProductionCategory::active()->get();
        $uoms = ProductionUom::active()->get();
        $page_title = 'Edit Production Product';

        return view('production.products.edit', compact('product', 'categories', 'uoms', 'page_title', 'id'));
    }

    public function update(Request $request, $id)
    {
        $did = decrypt($id);
        $validated = $request->validate([
            'category_id' => 'required|exists:production_categories,id',
            'sku' => 'required|string|max:255|unique:production_products,sku,' . $did,
            'name' => 'required|string|max:255',
            'status' => 'nullable|in:active,inactive',
            'uom_ids' => 'nullable|array',
            'uom_ids.*' => 'integer|exists:production_uoms,id',
        ]);

        $product = ProductionProduct::findOrFail($did);
        $product->update([
            'category_id' => $validated['category_id'],
            'sku' => $validated['sku'],
            'name' => $validated['name'],
            'status' => $validated['status'] ?? $product->status,
            'updated_by' => auth()->id(),
        ]);

        $product->uoms()->sync(
            collect($validated['uom_ids'] ?? [])
                ->mapWithKeys(fn($id) => [
                    $id => [
                        'updated_at' => now(),
                    ],
                ])
                ->toArray()
        );

        return redirect()->route('production.products.index')->with('success', 'Product updated successfully.');
    }

    public function destroy($id)
    {
        $product = ProductionProduct::findOrFail(decrypt($id));
        ProductionProductUom::where('product_id', $product->id)->delete();
        $product->delete();

        return redirect()->route('production.products.index')->with('success', 'Product deleted successfully.');
    }

    public function show($id)
    {
        $product = ProductionProduct::with(['category', 'uoms'])->findOrFail(decrypt($id));
        $page_title = 'Production Product Details';
        return view('production.products.view', compact('product', 'page_title'));
    }

    public function productSelect2(Request $request)
    {
        $queryString = trim($request->searchQuery);
        $page = $request->input('page', 1);
        $limit = 5;
    
        $query = ProductionProduct::active()->with( 'uoms:id,name,code' );
    
        if (!empty($queryString)) {
            $query->where('name', 'LIKE', "%{$queryString}%");
        }
        if ($request->category_id) {
            $query->where('category_id', $request->category_id);
        }
    
        $data = $query->paginate($limit, ['*'], 'page', $page);
    
        return response()->json([
            'items' => $data->map(function ($pro) {
                return [
                    'id' => $pro->id,
                    'text' => $pro->name,
                    'uoms' => $pro->uoms,
                ];
            }),
            'pagination' => [
                'more' => $data->hasMorePages()
            ]
        ]);
    }
}
