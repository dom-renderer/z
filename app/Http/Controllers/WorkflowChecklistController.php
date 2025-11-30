<?php

namespace App\Http\Controllers;

use App\Models\NotificationTemplate;
use App\Models\ChecklistEscalation;
use Illuminate\Support\Facades\DB;
use App\Models\ChecklistTask;
use Illuminate\Http\Request;
use App\Models\DynamicForm;

class WorkflowChecklistController extends Controller
{
    public function index(Request $request)
    {   
        if ($request->ajax()) {

            return datatables()
            ->eloquent(DynamicForm::workflow()->orderBy('id', 'DESC'))
            ->addColumn('action', function ($row) {
                $action = '';

                if (auth()->user()->can('workflow-checklists.edit')) {
                    $action .= '<a href="'.route("workflow-checklists.edit", encrypt($row->id)).'" class="btn btn-info btn-sm me-2"> Edit </a>';
                }

                if (auth()->user()->can('workflow-checklists.show')) {
                    $action .= '<a href="'.route("workflow-checklists.show", encrypt($row->id)).'" class="btn btn-warning btn-sm me-2"> Show </a>';
                }

                if (auth()->user()->can('checklists.show')) {
                    $action .= '<a href="'.route('checklists.render', encrypt($row->id)).'" class="btn btn-danger btn-sm me-2">Render</a>';
                }

                $action .= '<a href="'.route('duplicate-workflow-checklist', encrypt($row->id)).'" class="btn btn-success btn-sm me-2">Duplicate</a>';

                if (auth()->user()->can('workflow-checklists.destroy')) {
                    $action .= '<form method="POST" action="'.route("workflow-checklists.destroy", encrypt($row->id)).'" style="display:inline;"><input type="hidden" name="_method" value="DELETE"><input type="hidden" name="_token" value="'.csrf_token().'"><button type="submit" class="btn btn-danger btn-sm deleteGroup">Delete</button></form>';
                }

                return $action;
            })
            ->rawColumns(['action'])
            ->toJson();
        }

        $page_title = 'Checklists';
        $page_description = 'Manage checklists here';
        return view('workflow-checklists.index',compact('page_title', 'page_description'));
    }

    public function create()
    {
        $page_title = 'Checklist Add';

        return view('workflow-checklists.create', compact( 'page_title'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'form_schema' => 'required'
        ]);
        
        DB::beginTransaction();

        try {

            $schema = collect($request->form_schema)->transform(function ($element) {
                $object = json_decode($element);
                foreach ($object as &$value) {
                    if (property_exists($value, 'label')) {
                        $value->label = trim(str_replace(["\n", "\r"], "", strip_tags($value->label)));
                    }
                }
    
                return $object;
            })->values()->toArray();
    
            $form = new DynamicForm();
            $form->name = $request->name;
            $form->type = 1;
            $form->schema = $schema;
            $form->save();
    
            DB::commit();
            return response()->json(['status' => true, 'message' => 'Checklist created successfully.']);
        
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('WORKFLOW CHECKLIST CREATION ERROR: ' . $e->getMessage() . ' ON LINE : ' . $e->getLine());
            return response()->json(['status' => false, 'message' => 'Something went wrong! please try again later.']);
        }
    }

    public function edit(Request $request, $id)
    {
        $form = DynamicForm::find(decrypt($id));

        $page_title = 'Checklist Edit';

        return view('workflow-checklists.edit', compact('form', 'page_title', 'id'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required',
            'form_schema' => 'required'
        ]);

        $schema = collect($request->form_schema)->transform(function ($element) {
            $object = json_decode($element);

            foreach ($object as &$value) {
                if (property_exists($value, 'label')) {
                    $value->label = trim(str_replace(["\n", "\r"], "", strip_tags($value->label)));
                }
            }

            return $object;
        })->values()->toArray();

        $form = DynamicForm::find(decrypt($id));
        $form->name = $request->name;
        $form->schema = $schema;

        ChecklistTask::whereHas('workflowclist', function ($builder) use ($form) {
            $builder->where('checklist_id', $form->id ?? null);
        })
        ->whereIn('status', [0])
        ->update(['form' => $schema]);

        $form->save();

        return response()->json(['status' => true, 'message' => 'Checklist updated successfully.']);
    }

    public function show(Request $request, $id)
    {
        $form = DynamicForm::find(decrypt($id));
        $page_title = 'Checklist Show';

        return view('workflow-checklists.show', compact('form', 'page_title', 'id'));
    }

    public function destroy(Request $request, $id)
    {
        $form = DynamicForm::find(decrypt($id));
        $form->delete();

        return redirect()->route('workflow-checklists.index')->with('success', 'Checklist deleted successfully');
    }

    public function renderForViewOnly(Request $request, $id)
    {
        $form = DynamicForm::find(decrypt($id));
        $page_title = 'Checklist Render';

        return view('workflow-checklists.view-only-render', compact('form', 'page_title', 'id'));
    }

    public function duplicate(Request $request, $id) {
        if ($request->method() == 'GET') {
            $form = DynamicForm::find(decrypt($id));
            $page_title = 'Duplicate Checklist';

            return view('workflow-checklists.duplicate', compact('form', 'page_title', 'id'));
        } else if ($request->method() == 'PUT') {
            $request->validate([
                'name' => 'required',
                'form_schema' => 'required'
            ]);
    
            $schema = collect($request->form_schema)->transform(function ($element) {
                $object = json_decode($element);
    
                foreach ($object as &$value) {
                    if (property_exists($value, 'label')) {
                        $value->label = trim(str_replace(["\n", "\r"], "", strip_tags($value->label)));
                    }
                }
    
                return $object;
            })->values()->toArray();
    
            $form = new DynamicForm();
            $form->type = 1;
            $form->name = $request->name;
            $form->schema = $schema;
            $form->save();
    
            return response()->json(['status' => true, 'message' => 'Checklist duplicated successfully.']);
        } else {
            return redirect()->route('workflow-checklists.index');
        }
    }

    public function select2List(Request $request) {
        $queryString = trim($request->searchQuery);
        $page = $request->input('page', 1);
        $limit = 10;
    
        $query = DynamicForm::query();
    
        if (!empty($queryString)) {
            $query->where('name', 'LIKE', "%{$queryString}%");
        }
    
        $data = $query->paginate($limit, ['*'], 'page', $page);
    
        return response()->json([
            'items' => $data->map(function ($item) {
                return [
                    'id' => $item->id,
                    'text' => $item->name
                ];
            }),
            'pagination' => [
                'more' => $data->hasMorePages()
            ]
        ]);
    }    
}