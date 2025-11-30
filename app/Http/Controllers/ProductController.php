<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\ProductCategory;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return datatables()
                ->eloquent(Product::with('category'))
                ->addColumn('category', function ($row) {
                    return optional($row->category)->name;
                })
                ->addColumn('action', function ($row) {
                    $action = '';

                    if (auth()->user()->can('products.show')) {
                        $action .= '<a href="' . route("products.show", encrypt($row->id)) . '" class="btn btn-warning btn-sm me-2"> Show </a>';
                    }

                    if (auth()->user()->can('products.edit')) {
                        $action .= '<a href="' . route('products.edit', encrypt($row->id)) . '" class="btn btn-info btn-sm me-2">Edit</a>';
                    }

                    if (auth()->user()->can('products.destroy')) {
                        $action .= '<form method="POST" action="' . route("products.destroy", encrypt($row->id)) . '" style="display:inline;"><input type="hidden" name="_method" value="DELETE"><input type="hidden" name="_token" value="' . csrf_token() . '"><button type="submit" class="btn btn-danger btn-sm deleteGroup">Delete</button></form>';
                    }

                    return $action;
                })
                ->rawColumns(['action'])
                ->toJson();
        }

        $page_title = 'Products';
        $page_description = 'Manage products here';
        return view('products.index', compact('page_title', 'page_description'));
    }

    public function create()
    {
        $page_title = 'Product Add';
        return view('products.create', compact('page_title'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:product_categories,id',
            'name' => 'required',
            'sku' => 'required|unique:products,sku',
            'uom' => 'nullable'
        ]);

        Product::create([
            'category_id' => $request->category_id,
            'name' => $request->name,
            'sku' => $request->sku,
            'uom' => $request->uom,
            'description' => $request->description
        ]);

        return redirect()->route('products.index')->with('success', 'Product created successfully');
    }

    public function show($id)
    {
        $page_title = 'Product Show';
        $product = Product::find(decrypt($id));
        return view('products.show', compact('product', 'page_title'));
    }

    public function edit($id)
    {
        $page_title = 'Product Edit';
        $product = Product::find(decrypt($id));
        return view('products.edit', compact('product', 'page_title', 'id'));
    }

    public function update(Request $request, $id)
    {
        $pId = decrypt($id);

        $request->validate([
            'category_id' => 'required|exists:product_categories,id',
            'name' => 'required',
            'sku' => "required|unique:products,sku,{$pId}",
            'uom' => 'nullable'
        ]);

        $product = Product::find($pId);
        $product->update($request->only(['category_id', 'name', 'sku', 'uom', 'description']));

        return redirect()->route('products.index')->with('success', 'Product updated successfully');
    }

    public function destroy($id)
    {
        $product = Product::find(decrypt($id));
        $product->delete();
        return redirect()->route('products.index')->with('success', 'Product deleted successfully');
    }

    public function categorySelect2(Request $request)
    {
        $queryString = trim($request->searchQuery);
        $page = $request->input('page', 1);
        $limit = 10;

        $query = ProductCategory::query();
        if (!empty($queryString)) {
            $query->where('name', 'LIKE', "%{$queryString}%");
        }

        $data = $query->paginate($limit, ['*'], 'page', $page);
        $response = $data->map(function ($item) {
            return [
                'id' => $item->id,
                'text' => $item->name
            ];
        });

        return response()->json([
            'items' => $response->values(),
            'pagination' => [
                'more' => $data->hasMorePages()
            ]
        ]);
    }

    public function uomSuggest(Request $request)
    {
        $term = trim($request->searchQuery);
        $page = $request->input('page', 1);
        $limit = 10;

        $query = Product::query();
        if (!empty($term)) {
            $query->where('uom', 'LIKE', "%{$term}%");
        }
        $query->whereNotNull('uom')->groupBy('uom');
        $data = $query->paginate($limit, ['uom'], 'page', $page);
        $items = $data->map(function ($item) {
            return ['id' => $item->uom, 'text' => $item->uom];
        })->values();

        return response()->json([
            'items' => $items,
            'pagination' => [
                'more' => $data->hasMorePages()
            ]
        ]);
    }
}


