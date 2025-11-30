<?php

namespace App\Http\Controllers;

use App\Models\WorkflowTemplate;
use Illuminate\Http\Request;

class WorkflowTemplateController extends Controller
{
    public function index(Request $request) {
        if ($request->ajax()) {

            $checklistScheduling = WorkflowTemplate::with(['section']);

            return datatables()
            ->eloquent($checklistScheduling)
            ->addColumn('sectionname', function ($row) {
                return $row->section->name ?? '-';
            })
            ->editColumn('status', function ($row) {
                if ($row->status) {
                    return '<span class="badge bg-success"> Active </span>';
                } else {
                    return '<span class="badge bg-danger"> InActive </span>';
                }
            })
            ->addColumn('action', function ($row) {
                $action = '';

                if (auth()->user()->can('workflow-templates.show')) {
                    $action .= '<a href="'.route("workflow-templates.show", encrypt($row->id)).'" class="btn btn-warning btn-sm me-2"> Show </a>';
                }

                if (auth()->user()->can('workflow-templates.edit')) {
                    $action .= '<a href="'.route('workflow-templates.edit', encrypt($row->id)).'" class="btn btn-info btn-sm me-2">Edit</a>';
                }

                if (auth()->user()->can('workflow-templates.destroy')) {
                    $action .= '<form method="POST" action="'.route("workflow-templates.destroy", encrypt($row->id)).'" style="display:inline;"><input type="hidden" name="_method" value="DELETE"><input type="hidden" name="_token" value="'.csrf_token().'"><button type="submit" class="btn btn-danger btn-sm deleteGroup">Delete</button></form>';
                }

                return $action;
            })

            ->rawColumns(['action', 'status'])
            ->toJson();
        }

        $page_title = 'Workflow Template';
        $page_description = 'Manage workflow template here';
    return view('workflow-templates.index',compact('page_title', 'page_description'));
    }

    public function create() {
        $page_title = 'Template Add';

        return view('workflow-templates.create', compact( 'page_title'));
    }

    public function store(Request $request) {
        $validated = $request->validate([
            'name' => 'required',
            'section' => 'required',
            'status' => 'required'
        ]);

        WorkflowTemplate::create([
            'name' => $validated['name'],
            'section_id' => $validated['section'],
            'status' => $validated['status']
        ]);

        return redirect()->route('workflow-templates.index')->with('success', 'Template created successfully.');
    }

    public function edit(Request $request, $id) {
        $page_title = 'Template Edit';
        $template = WorkflowTemplate::find(decrypt($id));

        return view('workflow-templates.edit', compact( 'page_title', 'template', 'id'));
    }

    public function update(Request $request, $id) {
        $validated = $request->validate([
            'name' => 'required',
            'section' => 'required',
            'status' => 'required'
        ]);

        WorkflowTemplate::where('id', decrypt($id))->update([
            'name' => $validated['name'],
            'section_id' => $validated['section'],
            'status' => $validated['status']
        ]);

        return redirect()->route('workflow-templates.index')->with('success', 'Template update successfully.');
    }

    public function show(Request $request, $id) {
        $page_title = 'Template Show';
        $template = WorkflowTemplate::find(decrypt($id));

        return view('workflow-templates.show', compact( 'page_title', 'template'));
    }

    public function destroy($id)
    {
        $temp = WorkflowTemplate::find(decrypt($id));
        $temp->delete();

        return redirect()->route('workflow-templates.index')->with('success', 'Template deleted successfully.');
    }

    public function templateLists(Request $request) {
        $queryString = trim($request->searchQuery);
        $page = $request->input('page', 1);
        $limit = 10;
    
        $query = WorkflowTemplate::where('status', 1);
    
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
