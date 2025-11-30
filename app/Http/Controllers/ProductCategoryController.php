<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProductCategory;

class ProductCategoryController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return datatables()
                ->eloquent(ProductCategory::query())
                ->addColumn('action', function ($row) {
                    $action = '';

                    if (auth()->user()->can('product-categories.show')) {
                        $action .= '<a href="' . route("product-categories.show", encrypt($row->id)) . '" class="btn btn-warning btn-sm me-2"> Show </a>';
                    }

                    if (auth()->user()->can('product-categories.edit')) {
                        $action .= '<a href="' . route('product-categories.edit', encrypt($row->id)) . '" class="btn btn-info btn-sm me-2">Edit</a>';
                    }

                    if (auth()->user()->can('product-categories.destroy')) {
                        $action .= '<form method="POST" action="' . route("product-categories.destroy", encrypt($row->id)) . '" style="display:inline;"><input type="hidden" name="_method" value="DELETE"><input type="hidden" name="_token" value="' . csrf_token() . '"><button type="submit" class="btn btn-danger btn-sm deleteGroup">Delete</button></form>';
                    }

                    return $action;
                })
                ->rawColumns(['action'])
                ->toJson();
        }

        $page_title = 'Product Categories';
        $page_description = 'Manage product categories here';
        return view('product-categories.index', compact('page_title', 'page_description'));
    }

    public function create()
    {
        $page_title = 'Category Add';
        return view('product-categories.create', compact('page_title'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:product_categories,name'
        ]);

        ProductCategory::create([
            'name' => $request->name,
            'description' => $request->description
        ]);

        return redirect()->route('product-categories.index')->with('success', 'Category created successfully');
    }

    public function show($id)
    {
        $page_title = 'Category Show';
        $category = ProductCategory::find(decrypt($id));
        return view('product-categories.show', compact('category', 'page_title'));
    }

    public function edit($id)
    {
        $page_title = 'Category Edit';
        $category = ProductCategory::find(decrypt($id));
        return view('product-categories.edit', compact('category', 'page_title', 'id'));
    }

    public function update(Request $request, $id)
    {
        $cId = decrypt($id);

        $request->validate([
            'name' => "required|unique:product_categories,name,{$cId}"
        ]);

        $category = ProductCategory::find($cId);
        $category->update($request->only(['name', 'description']));

        return redirect()->route('product-categories.index')->with('success', 'Category updated successfully');
    }

    public function destroy($id)
    {
        $category = ProductCategory::find(decrypt($id));
        $category->delete();
        return redirect()->route('product-categories.index')->with('success', 'Category deleted successfully');
    }

    public function select2List(Request $request)
    {
        $queryString = trim($request->searchQuery);
        $page = $request->input('page', 1);
        $limit = 10;
        $getAll = $request->getall;

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

        if ($getAll && $page == 1) {
            $response->push(['id' => 'all', 'text' => 'All']);
        }

        return response()->json([
            'items' => $response->reverse()->values(),
            'pagination' => [
                'more' => $data->hasMorePages()
            ]
        ]);
    }
}


