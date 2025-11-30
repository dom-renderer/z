<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\Helper;
use App\Http\Requests\ProductionCategoryRequest;
use App\Models\ProductionCategory;
use App\Models\ProductionProduct;

class ProductionCategoryController extends Controller
{
    public $moduleName = 'Production Categories';
    public $route = "production.categories";
    public $view = "production/categories";
    public $permission = "production.categories";

    public function index(Request $request) {
        if (session()->has('selected_category')) {
            session()->forget('selected_category');
        }

        if (!$request->ajax()) {
            $page_title = $this->moduleName;
            $page_description = 'Manage production categories here';
            
            return view( $this->route . '.index', compact('page_title', 'page_description'));
        }

        $categories = ProductionCategory::with('parent');

        return dataTables()->eloquent($categories)
            ->addColumn('parentcat', function ($row) {
                return $row->parent->name ?? '-';
            })
            ->editColumn('status', function ($row) {
                if ($row->status) {
                    return '<span class="bg-success badge"> Enable </span>';
                } else {
                    return '<span class="bg-danger badge"> Disable </span>';
                }
            })
            ->addColumn('action', function ($row) {
                $action = '';

                if ( auth()->user()->can( $this->permission . '.show' ) ) {
                    $action .= '<a href="'.route( $this->route . ".show", encrypt($row->id)).'" class="btn btn-warning btn-sm me-2"> Show </a>';
                }

                if ( auth()->user()->can( $this->permission . '.edit' ) ) {
                    $action .= '<form style="display:inline;" method="POST" action="'.route( $this->route . ".enable-disable", encrypt($row->id)).'" ><input type="hidden" name="_token" value="'.csrf_token().'">';
                    if ($row->status) {
                        $action .= '<button type="submit" class="btn btn-danger btn-sm me-2 status-changer" data-description="Disable this category" data-blable="Disable"> Disable </button>';
                    } else {
                        $action .= '<button type="submit" class="btn btn-success btn-sm me-2 status-changer" data-description="Enable this category" data-blable="Enable"> Enable </button>';
                    }
                    $action .= '</form>';
                }

                if ( auth()->user()->can( $this->permission . '.edit' ) ) {
                    $action .= '<a href="'.route($this->route . '.edit', encrypt($row->id)).'" class="btn btn-info btn-sm me-2">Edit</a>';
                }

                if ( auth()->user()->can( $this->permission . '.destroy' ) ) {
                    $action .= '<form method="POST" action="'.route( $this->route . ".destroy", encrypt($row->id) ).'" style="display:inline;"><input type="hidden" name="_method" value="DELETE"><input type="hidden" name="_token" value="'.csrf_token().'"><button type="submit" class="btn btn-danger btn-sm deleteGroup">Delete</button></form>';
                }

                return $action;
            })
            ->rawColumns(['action', 'status'])
            ->make(true);
    }

    public function create(Request $request) {
        $page_title = 'Production Category Add';
        $moduleLink = route( $this->route . '.index');
        return view( $this->route . '.create', compact( 'page_title', 'moduleLink' ) );
    }

    public function store(ProductionCategoryRequest $request)
    {
        $user = new ProductionCategory();
        $user->name = $request->name;
        $user->status = $request->status;
        $user->slug = Helper::slug($request->name);
        $user->added_by = auth()->user()->id;
        
        if (!empty($request->parent)) {
            $user->parent_id = $request->parent;
        }

        $user->save();

        return redirect()->route( $this->route . '.index')->with('success', 'Category added successfully.');
    }

    public function edit($id)
    {
        $page_title = 'Production Category Edit';
        $moduleLink = route( $this->route . '.index');
        $decryptedId = decrypt($id);
        $category = ProductionCategory::with(['parent'])->where('id', $decryptedId)->first();
        $totalSubCategories = ProductionCategory::where('parent_id', $decryptedId)->count();

        return view( $this->route . '.edit', compact('page_title', 'id', 'category','moduleLink', 'totalSubCategories'));
    }

    public function getSubCatCount(Request $request) {
        $count = 0;
        $category = ProductionCategory::firstWhere('id', $request->id);

        if ($category != null) {
            if (!empty($request->parent) && is_numeric($request->parent) && $request->parent != '0' && $request->parent != $category->parent_id) {
                $count = ProductionCategory::where('parent_id', $category->id)->count();
            }            
        }

        return response(['count' => $count]);
    }

    public function update(ProductionCategoryRequest $request, $id)
    {
        $id = decrypt($id);
        $parent = trim($request->parent);

        \DB::beginTransaction();

        try {
            $user = ProductionCategory::find($id);

            if (!empty($parent) && is_numeric($parent) && $parent != '0' && $user->parent_id != $parent) {
                // ProductionCategory::where('parent_id', $id)->update(['parent_id' => 0]);
                $user->parent_id = $parent;
            }
    
            $user->name = $request->name;
            $user->status = $request->status;
            $user->slug = Helper::slug($request->name);
            $user->updated_by = auth()->user()->id;
            $user->save();

            \DB::commit();
            return redirect()->route( $this->route . '.index')->with('success', 'Category updated successfully.');
        } catch (\Exception $e) {

            \DB::rollBack();
            return redirect()->route( $this->route . '.index')->with('success', Helper::$errorMessage);
        }
    }

    public function show($id)
    {
        $page_title = 'Production Category Show';
        $moduleLink = route( $this->route . '.index');
        $category = ProductionCategory::with('children')->where('id', decrypt($id))->first();

        return view( $this->route . '.view', compact('page_title', 'category','moduleLink'));
    }

    public function destroy($id)
    {
        $category = ProductionCategory::find(decrypt($id));

        if (ProductionProduct::where('category_id', $category->id)->exists()) {
            return redirect()->back()->with('error', 'This category contains a product.');
        } else if (Helper::productionSubCategoryHasProduct($category->id)) {
            return redirect()->back()->with('error', 'This category\'s sub category contains a product.');
        } else if (Helper::hasProductionSubCategory($category->id)) {
            return redirect()->back()->with('error', 'This category contains sub categories.');
        }

        if ($category->delete()) {
            return redirect()->back()->with('success', 'Category deleted successfully.');
        } else {
            return redirect()->back()->with('error', 'Something went wrong.');
        }
    }

    public function enableDisable($id) {
        $category = ProductionCategory::find(decrypt($id));
        $category->status = $category->status == 1 ? 0 :  1;
        $category->save();

        return redirect()->back()->with('success', 'Category status changed successfully.');
    }

    public static function getChildren($category) {
        return [
            'id' => $category->id,
            'name' => $category->name,
            'status' => $category->status,
            'children' => $category->children->map(function ($child) {
                return self::getChildren($child);
            })->values()->toArray()
        ];
    }

    public function categorySelect2(Request $request) 
    {
        $queryString = trim($request->searchQuery);
        $page = $request->input('page', 1);
        $limit = 5;
        $onlyActive = $request->onlyactive;
    
        $query = ProductionCategory::query();
    
        if (!empty($queryString)) {
            $query->where('name', 'LIKE', "%{$queryString}%");
        }

        if ($onlyActive == '1') {
            $query->where('status', 1);
        }
    
        $data = $query->paginate($limit, ['*'], 'page', $page);
    
        return response()->json([
            'items' => $data->map(function ($pro) {
                return [
                    'id' => $pro->id,
                    'text' => $pro->name
                ];
            }),
            'pagination' => [
                'more' => $data->hasMorePages()
            ]
        ]);
    }
}
