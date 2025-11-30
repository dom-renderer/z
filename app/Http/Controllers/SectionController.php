<?php

namespace App\Http\Controllers;

use App\Models\SectionChecklist;
use Illuminate\Http\Request;
use App\Models\Section;
use App\Helpers\Helper;

class SectionController extends Controller
{
    public function index(Request $request) {
        if ($request->ajax()) {

            $checklistScheduling = Section::query();

            return datatables()
            ->eloquent($checklistScheduling)
            ->addColumn('name', function ($row) {
                return $row->name;
            })
            ->addColumn('parentname', function ($row) {
                return $row->parent->name ?? '-';
            })
            ->addColumn('action', function ($row) {
                $action = '';

                if (auth()->user()->can('sections.show')) {
                    $action .= '<a href="'.route("sections.show", encrypt($row->id)).'" class="btn btn-warning btn-sm me-2"> Show </a>';
                }

                if (auth()->user()->can('sections.edit')) {
                    $action .= '<a href="'.route('sections.edit', encrypt($row->id)).'" class="btn btn-info btn-sm me-2">Edit</a>';
                }

                if (auth()->user()->can('sections.destroy')) {
                    $action .= '<form method="POST" action="'.route("sections.destroy", encrypt($row->id)).'" style="display:inline;"><input type="hidden" name="_method" value="DELETE"><input type="hidden" name="_token" value="'.csrf_token().'"><button type="submit" class="btn btn-danger btn-sm deleteGroup">Delete</button></form>';
                }

                return $action;
            })
            ->rawColumns(['action', 'status'])
            ->toJson();
        }

        $page_title = 'Sections';
        $page_description = 'Manage sections here';
        return view('sections.index',compact('page_title', 'page_description'));
    }

    public function create()
    {
        $page_title = 'Section Add';

        return view('sections.create', compact( 'page_title'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', function ($name, $value, $fail) {
                if (Section::where('slug', Helper::slug($value))->withTrashed()->exists()) {
                    $fail("This section is already exists.");
                }
            }]
        ]);

        \DB::beginTransaction();

        try {
            $sec = new Section();
            $sec->name = $request->name;
            $sec->slug = Helper::slug($request->name);
            
            if (!empty($request->parent)) {
                $sec->parent_id = $request->parent;
            }
    
            $sec->save();
    
            if (is_array($request->checklist) && !empty($request->checklist)) {
                foreach ($request->checklist as $checklist) {
                    SectionChecklist::updateOrCreate([
                        'section_id' => $sec->id,
                        'checklist_id' => $checklist
                    ]);
                }
            }

            \DB::commit();
            return redirect()->route('sections.index')->with('success', 'Section added successfully.');
        } catch (\Exception $e) {
            \DB::rollBack();
            return redirect()->route('sections.index')->with('error', Helper::$error);
        }
    }

    public function edit($id)
    {
        $page_title = 'Section Edit';
        $moduleLink = route('sections.index');
        $decryptedId = decrypt($id);
        $category = Section::with(['parent' => function ($builder) {
            return $builder->withTrashed();
        }, 'checklists' => function ($builder) {
            return $builder->withTrashed();
        }, 'checklists.checklist' => function ($builder) {
            return $builder->withTrashed();
        }])->where('id', $decryptedId)->first();

        $totalSubCategories = Section::where('parent_id', $decryptedId)->count();

        return view('sections.edit', compact('page_title', 'id', 'category','moduleLink', 'totalSubCategories'));
    }

    public function getSubSecCount(Request $request) {
        $count = 0;
        $category = Section::firstWhere('id', $request->id);

        if ($category != null) {
            if (!empty($request->parent) && is_numeric($request->parent) && $request->parent != '0' && $request->parent != $category->parent_id) {
                $count = Section::where('parent_id', $category->id)->count();
            }            
        }

        return response(['count' => $count]);
    }

    public function update(Request $request, $id)
    {
        $id = decrypt($id);

        $request->validate([
            'name' => ['name' => ['required', function ($name, $value, $fail) use ($id) {
                if (Section::where('slug', \App\Helpers\Helper::slug($value))->where('id', '!=', $id)->withTrashed()->exists()) {
                    $fail("This section is already exists.");
                }
            }]]
        ]);

        $parent = trim($request->parent);

        \DB::beginTransaction();

        try {
            $user = Section::find($id);

            if (Section::where('id', $parent)->where('parent_id', $user->id)->exists()) {
                \DB::rollBack();
                return redirect()->route('sections.index')->with('success', 'You can\'t assign selected category as parent.');   
            }

            if (!empty($parent) && is_numeric($parent) && $parent != '0' && $user->parent_id != $parent) {
                $user->parent_id = $parent;
            }

            $idsToKeep = [];

            if (is_array($request->checklist) && !empty($request->checklist)) {
                foreach ($request->checklist as $checklist) {
                    $idsToKeep[] = SectionChecklist::updateOrCreate([
                        'section_id' => $user->id,
                        'checklist_id' => $checklist
                    ])->id;
                }
            }

            SectionChecklist::whereNotIn('id', $idsToKeep)->where('section_id', $user->id)->delete();
    
            $user->name = $request->name;
            $user->slug = Helper::slug($request->name);
            $user->save();

            \DB::commit();
            return redirect()->route('sections.index')->with('success', 'Section updated successfully.');
        } catch (\Exception $e) {

            \DB::rollBack();
            return redirect()->route('sections.index')->with('success', 'Something went wrong. please try again later.');
        }
    }

    public function show($id)
    {
        $page_title = 'Section Show';
        $moduleLink = route('sections.index');
        $section = Section::with('children')->where('id', decrypt($id))->first();

        return view('sections.show', compact('page_title', 'section','moduleLink'));
    }

    public function destroy($id)
    {
        $sec = Section::find(decrypt($id));

        if (SectionChecklist::where('section_id', $sec->id)->exists()) {
            return redirect()->back()->with('error', 'This section contains a checklist.');
        }

        Section::where('parent_id', $sec->id)->update(['parent_id' => null]);
        $deleted = $sec->delete();

        if ($deleted) {
            return redirect()->back()->with('success', 'Section deleted successfully.');
        } else {
            return redirect()->back()->with('error', 'Something went wrong. please try again later.');
        }
    }

    public function sectionLists(Request $request) {
        $queryString = trim($request->searchQuery);
        $page = $request->input('page', 1);
        $limit = env('SELECT2_PAGE_LENGTH', 5);
        $except = $request->except;
    
        $query = Section::query();
    
        if (!empty($queryString)) {
            $query->where('name', 'LIKE', "%{$queryString}%");
        }

        if (is_numeric($except) && $except > 0) {
            $query->where('id', '!=', $except);
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

    public function sectionChecklistLists(Request $request) {
        $queryString = trim($request->searchQuery);
        $page = $request->input('page', 1);
        $limit = env('SELECT2_PAGE_LENGTH', 5);
    
        $query = SectionChecklist::with(['section', 'checklist']);
    
        if (!empty($queryString)) {
            $query->whereHas('section', function ($builder) use ($queryString) {
                $builder->where('name', 'LIKE', "%{$queryString}%");
            })
            ->orWhereHas('checklist', function ($builder) use ($queryString) {
                $builder->where('name', 'LIKE', "%{$queryString}%");
            });
        }
        
        $data = $query->paginate($limit, ['*'], 'page', $page);
    
        return response()->json([
            'items' => $data->map(function ($pro) {
                return [
                    'id' => $pro->section->id,
                    'text' => ($pro->section->name ?? '') . ' - ' . ($pro->checklist->name ?? '')
                ];
            }),
            'pagination' => [
                'more' => $data->hasMorePages()
            ]
        ]);
    }
}
