<?php

namespace App\Http\Controllers;

use App\Http\Requests\ShiftRequest;
use App\Models\Shift;
use Illuminate\Http\Request;

class ShiftController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:shifts.index')->only(['index']);
        $this->middleware('permission:shifts.create')->only(['create', 'store']);
        $this->middleware('permission:shifts.show')->only(['show']);
        $this->middleware('permission:shifts.edit')->only(['edit', 'update']);
        $this->middleware('permission:shifts.destroy')->only(['destroy']);
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            return datatables()
                ->eloquent(Shift::query())
                ->addColumn('action', function ($row) {
                    $action = '';

                    if (auth()->user()->can('shifts.show')) {
                        $action .= '<a href="' . route("shifts.show", encrypt($row->id)) . '" class="btn btn-warning btn-sm me-2"> Show </a>';
                    }

                    if (auth()->user()->can('shifts.edit')) {
                        $action .= '<a href="' . route('shifts.edit', encrypt($row->id)) . '" class="btn btn-info btn-sm me-2">Edit</a>';
                    }

                    if (auth()->user()->can('shifts.destroy')) {
                        $action .= '<form method="POST" action="' . route("shifts.destroy", encrypt($row->id)) . '" style="display:inline;"><input type="hidden" name="_method" value="DELETE"><input type="hidden" name="_token" value="' . csrf_token() . '"><button type="submit" class="btn btn-danger btn-sm deleteGroup">Delete</button></form>';
                    }

                    return $action;
                })
                ->editColumn('start', function ($row) {
                    return $row->start ? date('H:i A', strtotime($row->start)) : '';
                })
                ->editColumn('end', function ($row) {
                    return $row->end ? date('H:i A', strtotime($row->end)) : '';
                })
                ->rawColumns(['action'])
                ->toJson();
        }

        $page_title = 'Shifts';
        $page_description = 'Manage shifts here';
        return view('shifts.index', compact('page_title', 'page_description'));
    }

    public function create()
    {
        $page_title = 'Shift Add';

        return view('shifts.create', compact('page_title'));
    }

    public function store(ShiftRequest $request)
    {
        Shift::create([
            'title' => $request->title,
            'start' => $request->start,
            'end' => $request->end,
        ]);

        return redirect()->route('shifts.index')->with('success', 'Shift created successfully');
    }

    public function show($id)
    {
        $page_title = 'Shift Show';
        $shift = Shift::find(decrypt($id));

        return view('shifts.show', compact('shift', 'page_title'));
    }

    public function edit($id)
    {
        $page_title = 'Shift Edit';
        $shift = Shift::find(decrypt($id));

        return view('shifts.edit', compact('shift', 'page_title', 'id'));
    }

    public function update(ShiftRequest $request, $id)
    {
        $cId = decrypt($id);

        $shift = Shift::findOrFail($cId);
        $shift->update($request->only(['title', 'start', 'end']));

        return redirect()->route('shifts.index')->with('success', 'Shift updated successfully');
    }

    public function destroy($id)
    {
        $shift = Shift::find(decrypt($id));
        $shift->delete();

        return redirect()->route('shifts.index')->with('success', 'Shift deleted successfully');
    }

    public function shiftSelect(Request $request)
    {
        $queryString = trim($request->searchQuery);
        $page = $request->input('page', 1);
        $limit = env('SELECT2_PAGE_LENGTH', 5);
        $onlyActive = $request->onlyactive;
        $except = $request->except;

        $query = Shift::query();

        if (!empty($queryString)) {
            $query->where('title', 'LIKE', "%{$queryString}%");
        }

        if (!empty($except) && is_string($except)) {
            $except = explode(',', $except);
            if (count($except) > 0) {
                $query->whereNotIn('id', $except);
            }
        }

        $data = $query->paginate($limit, ['*'], 'page', $page);

        return response()->json([
            'items' => $data->map(function ($pro) {
                return [
                    'id' => $pro->id,
                    'text' => $pro->title
                ];
            }),
            'pagination' => [
                'more' => $data->hasMorePages()
            ]
        ]);
    }
}
