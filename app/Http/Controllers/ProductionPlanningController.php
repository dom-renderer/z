<?php

namespace App\Http\Controllers;

use Illuminate\Pagination\LengthAwarePaginator;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use App\Exports\ImportProductionPlanning;
use App\Models\ProductionPlanningHistory;
use App\Models\ProductionProductUom;
use App\Models\ProductionPlanning;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\ProductionCategory;
use App\Models\ProductionProduct;
use App\Models\SchedulingImport;
use App\Models\ProductionItem;
use App\Models\ProductionUom;
use Illuminate\Http\Request;
use App\Models\Production;
use App\Models\Shift;

class ProductionPlanningController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = ProductionPlanning::with(['shift', 'product', 'unit', 'user'])
                ->orderBy('id', 'DESC');
                
            if ($request->filled('shift_filter')) {
                $query->where('shift_id', $request->input('shift_filter'));
            }

            if ($request->filled('from_date')) {
                $query->whereDate('shift_time', '>=', date('Y-m-d', strtotime($request->input('from_date'))));
            }

            if ($request->filled('to_date')) {
                $query->whereDate('shift_time', '<=', date('Y-m-d', strtotime($request->input('to_date'))));
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
                $query->where('uom_id', $request->input('uom_id'));
            }

            if ($request->filled('user_id')) {
                $query->where('added_by', $request->input('user_id'));
            }

            return datatables()
                ->eloquent($query)
                ->addColumn('shift_time', function ($row) {
                    return date('d-m-Y H:i', strtotime($row->shift_time));
                })
                ->addColumn('products', function ($row) {
                    return $row->product->name ?? 'N/A';
                })
                ->addColumn('units', function ($row) {
                    return $row->unit->name ?? 'N/A';
                })
                ->addColumn('users', function ($row) {
                    return $row->user->name ?? 'N/A';
                })
                ->addColumn('shift_name', function ($row) {
                    return $row->shift->title ?? '';
                })
                ->toJson();
        }

        if ($request->method() == 'POST') {
            return $this->import($request);
        } else {

            $page_title = 'Production Planning';
            $page_description = 'Production planning overview';

            return view('production.planning', compact('page_title', 'page_description'));
        }
    }

    public function import(Request $request) {
        $file = $request->file('xlsx');
        $data = Excel::toArray(new ImportProductionPlanning(),$file);
        $response = [];
        $successCount = $errorCount = 0;
        $fileName = ($file?->getClientOriginalName() ?? 'SYS-GEN-' . date('Ymdhis') . uniqid()) . ($file?->getClientOriginalExtension() ?? '.file');

        $expectedHeaders = [
            'Product',
            'Sub Group',
            'SOQty',
            'IndentQty',
            'Totals',
            'OP',
            'PROD.'
        ];

        $morningShift = Shift::where(\DB::raw('LOWER(title)'), 'LIKE', '%morning%')->first()->id ?? null;
        $nightShift = Shift::where(\DB::raw('LOWER(title)'), 'LIKE', '%night%')->first()->id ?? null;

        $isFileValid = false;
        $data = [];

        $currentTime = date('H:i:s');
        $currentShift = Shift::where(function($query) use ($currentTime) {
            $query->whereTime('start', '<=', $currentTime)
                ->whereTime('end', '>=', $currentTime);
        })
        ->orWhere(function($query) use ($currentTime) {
            $query->whereTime('start', '<=', $currentTime)
                ->whereRaw('TIME(`end`) < TIME(`start`)');
        })
        ->orWhere(function($query) use ($currentTime) {
            $query->whereTime('end', '>=', $currentTime)
                ->whereRaw('TIME(`end`) < TIME(`start`)');
        })
        ->first()
        ->id ?? null;

        $shiftType = $request->shiftid ?? $currentShift;
        $shiftTime = date('Y-m-d H:i:s', strtotime($request->datetime));

        if ((\App\Models\Setting::first()->cims ?? 1) == 0 && 
            ProductionPlanning::whereDate('shift_time', date('Y-m-d', strtotime($request->datetime)))->where('shift_id', $shiftType)->exists()) {
            return response()->json(['status' => false, 'message' => 'Sheet for this shift has already uploaded']);
        }

        try {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getPathname());
            $worksheet = $spreadsheet->getActiveSheet();
            $highestRow = $worksheet->getHighestRow();
            $highestColumn = $worksheet->getHighestColumn();
            $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);

            for ($row = 1; $row <= $highestRow; $row++) {
                $rowData = [];
                for ($col = 1; $col <= $highestColumnIndex; $col++) {
                    $cellValue = $worksheet->getCellByColumnAndRow($col, $row)->getCalculatedValue();
                    $rowData[] = $cellValue;
                }
                
                $tempFilter = array_filter($rowData, function($value) {
                    return !is_null($value) && $value !== '';
                });
                
                if (!empty($tempFilter)) {
                    $data[] = $rowData;
                }
            }

            if (!empty($data) && isset($data[4])) { 
                $headerRow = $data[4];
                if (
                    strtolower(trim($headerRow[0])) == strtolower($expectedHeaders[0]) &&
                    strtolower(trim($headerRow[4])) == strtolower($expectedHeaders[1]) &&
                    strtolower(trim($headerRow[6])) == strtolower($expectedHeaders[2]) &&
                    strtolower(trim($headerRow[9])) == strtolower($expectedHeaders[3]) &&
                    strtolower(trim($headerRow[16])) == strtolower($expectedHeaders[4]) &&
                    strtolower(trim($headerRow[19])) == strtolower($expectedHeaders[5]) && 
                    strtolower(trim($headerRow[20])) == strtolower($expectedHeaders[6])
                ) {
                    $isFileValid = true;
                }
            }

        } catch (\Exception $e) {

            self::recordImport([
                'checklist_id' => null,
                'file_name' => $fileName,
                'success' => 0,
                'error' => 0,
                'status' => 2,
                'response' => [
                    'Error reading xlsx file: ' . $e->getMessage()
                ]
            ], $file);
            
            return response()->json(['status' => false, 'message' => 'Error reading xlsx file.']);
        }

        if (!$isFileValid) {
            self::recordImport([
                'checklist_id' => null,
                'file_name' => $fileName,
                'success' => 0,
                'error' => 0,
                'status' => 2,
                'response' => [
                    'Uploaded file headers do not match the expected format.'
                ]
            ], $file);
            return response()->json(['status' => false, 'message' => 'Uploaded file headers do not match the expected format.']);
        }

        $startFrom = 7;
        $data = array_splice($data, 5, count($data));

        if (empty($data)) {
            self::recordImport([
                'checklist_id' => null,
                'file_name' => $file->getClientOriginalName(),
                'success' => 0,
                'error' => 0,
                'status' => 2,
                'response' => [
                    'File has not data.'
                ]
            ], $file);

            return response()->json(['status' => false, 'message' => 'File has not data']);
        }

        \DB::beginTransaction();

        $pSucceed = false;

        foreach ($data as $key => $row) {
            $product = $row[0];
            $uom = $row[4];
            $soqty = floatval($row[6]);
            $indentqty = floatval($row[9]);
            $totals = $soqty + $indentqty;
            $op = floatval($row[19]);
            $prod = $totals - $op;

            if (ProductionProduct::where(\DB::raw('LOWER(sku)'), strtolower($product))->doesntExist()) {
                $pSucceed = true;
                $errorCount++;
                $response[$startFrom + $key] = 'Product does not exists at A' . ($startFrom + $key);
                continue;
            } else {
                $product = ProductionProduct::select('id')->where(\DB::raw('LOWER(sku)'), strtolower($product))->first()->id;
            }

            if (ProductionUom::where(\DB::raw('LOWER(code)'), strtolower($uom))->doesntExist()) {
                $pSucceed = true;
                $errorCount++;
                $response[$startFrom + $key] = 'UoM does not exists at E' . ($startFrom + $key);
                continue;
            } else {
                $uom = ProductionUom::select('id')->where(\DB::raw('LOWER(code)'), strtolower($uom))->first()->id;
            }

            if (ProductionProductUom::where('product_id', $product)->where('uom_id', $uom)->doesntExist()) {
                ProductionProductUom::create([
                    'product_id' => $product,
                    'uom_id' => $uom
                ]);
            }

            ProductionPlanning::create([
                'product_id' => $product,
                'uom_id' => $uom,
                'sales_order' => $soqty,
                'indent' => $indentqty,
                'total' => $totals,
                'opening_stock' => 0,
                'production' => $prod,
                'added_by' => auth()->user()->id,
                'shift_id' => $shiftType,
                'shift_time' => $shiftTime
            ]);

            ProductionPlanningHistory::create([
                'product_id' => $product,
                'uom_id' => $uom,
                'sales_order' => $soqty,
                'indent' => $indentqty,
                'total' => floatval($row[16]),
                'opening_stock' => $op,
                'production' => floatval($row[20]),
                'added_by' => auth()->user()->id,
                'shift_id' => $shiftType,
                'shift_time' => $shiftTime
            ]);

            $successCount++;
        }

        try {
            self::recordImport([
                'checklist_id' => null,
                'file_name' => $file->getClientOriginalName(),
                'success' => $successCount,
                'error' => $errorCount,
                'status' => $successCount == 0 ? 2 : (
                    $errorCount > 0 ? 3 : 1
                ),
                'response' => $response,
            ], $file, true);
            
            \DB::commit();
            return response()->json(['status' => true, 'message' => 'Import scheduled successfully.', 'is_partially_succeed' => $pSucceed]);

        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('ERROR ON SCHEDULE IMPORT:' . $e->getMessage() . ' ON LINE ' . $e->getLine());
            return response()->json(['status' => false, 'message' => 'Something went wrong.']);
        }
    }

    public static function recordImport($data, $file, $canRewrite = false) {
        try {
            $originalPath = storage_path('app/public/scheduling-imports/original');
            $modifiedPath = storage_path('app/public/scheduling-imports/modified');
            if (!file_exists($originalPath)) {
                mkdir($originalPath, 0777, true);
            }
            if (!file_exists($modifiedPath)) {
                mkdir($modifiedPath, 0777, true);
            }
            $modified = $original = null;
            if ($canRewrite) {
                $fileName = date('YmdHis') . uniqid() . '.' . $file->getClientOriginalExtension();
                $file->move($originalPath, $fileName);
                $modified = $original = $fileName;
                /**
                 * Update XLSX
                 ****/
                $inputPath = "{$originalPath}/{$fileName}";
                $outputPath = "{$modifiedPath}/{$fileName}";
                
                $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader('Xlsx');
                $spreadsheet = $reader->load($inputPath);
                $worksheet = $spreadsheet->getActiveSheet();
                
                $highestRow = $worksheet->getHighestRow();
                $highestColumn = $worksheet->getHighestColumn();
                $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);
                
                $worksheet->setCellValueByColumnAndRow($highestColumnIndex + 1, 1, 'Status');
                $worksheet->setCellValueByColumnAndRow($highestColumnIndex + 2, 1, 'Message');
                
                $iteration = 0;

                for ($row = 2; $row <= $highestRow; $row++) {
                    if (isset($data['response'][$iteration])) {
                        $worksheet->setCellValueByColumnAndRow($highestColumnIndex + 1, $row, 'Error');
                        $worksheet->setCellValueByColumnAndRow($highestColumnIndex + 2, $row, $data['response'][$iteration]);
                    } else {
                        $worksheet->setCellValueByColumnAndRow($highestColumnIndex + 1, $row, 'Success');
                        $worksheet->setCellValueByColumnAndRow($highestColumnIndex + 2, $row, '');
                    }
                    $iteration++;
                }
                
                $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
                $writer->save($outputPath);
                
                $spreadsheet->disconnectWorksheets();
                unset($spreadsheet);
                /**
                 * Update XLSX
                 ****/
                
            } else {
                $fileName = date('YmdHis') . uniqid() . '.' . $file->getClientOriginalExtension();
                $file->move($originalPath, $fileName);
                $modified = $original = $fileName;
            }

            SchedulingImport::create([
                'checklist_id' => null,
                'file_name' => $data['file_name'],
                'success' => $data['success'],
                'error' => $data['error'],
                'status' => $data['status'],
                'original_file' => $original,
                'modified_file' => $modified,
                'uploaded_by' => auth()->check() ? auth()->user()->id : null,
                'response' => $data['response'],
                'is_planning' => 1
            ]);

        } catch (\Exception $e) {
            \Log::error('SCHEDULING IMPORT ERROR WHILE LOGGING : ' . $e->getMessage() . ' ON LINE : ' . $e->getLine());
        }
    }

    public function statistics(Request $request) {
        if ($request->ajax()) {
            $query = ProductionPlanning::with(['shift', 'product', 'unit', 'user'])
                ->orderBy('id', 'DESC');

            $currentTime = date('H:i:s');
            $currentShift = Shift::where(function ($query) use ($currentTime) {
                    $query->whereTime('start', '<=', $currentTime)
                        ->whereTime('end', '>=', $currentTime);
                })
                ->orWhere(function ($query) use ($currentTime) {
                    $query->whereTime('start', '<=', $currentTime)
                        ->whereRaw('TIME(`end`) < TIME(`start`)');
                })
                ->orWhere(function ($query) use ($currentTime) {
                    $query->whereTime('end', '>=', $currentTime)
                        ->whereRaw('TIME(`end`) < TIME(`start`)');
                })
                ->first()
                ->id ?? null;

            $query->where('shift_id', $currentShift);
            $query->whereDate('shift_time', date('Y-m-d'));

            if ($request->filled('category_id')) {
                $query->whereHas('product', function ($q) use ($request) {
                    $q->where('category_id', $request->category_id);
                });
            }

            if ($request->filled('product_id')) {
                $query->where('product_id', $request->product_id);
            }

            if ($request->filled('uom_id')) {
                $query->where('uom_id', $request->uom_id);
            }

            $perPage = $request->input('length', 10);
            $page = ($request->input('start', 0) / $perPage) + 1;

            $paginated = $query->paginate($perPage, ['*'], 'page', $page);

            $data = [];

            $totalProduction = 0;
            $producedSoFar = 0;

            foreach ($paginated->items() as $row) {
                $producedQty = ProductionItem::where('product_id', $row->product_id)
                    ->where('unit_id', $row->uom_id)
                    ->whereHas('production', function ($innerQuery) {
                        $innerQuery->whereDate('production_date', date('Y-m-d'));
                    })
                    ->sum('quantity');

                $required = $row->production ?? 0;
                $remaining = max($required - $producedQty, 0);

                $totalProduction += $required;
                $producedSoFar += $producedQty;

                $data[] = [
                    'id' => $row->id,
                    'product_stat' => $row->product->name ?? 'N/A',
                    'unit_stat' => $row->unit->name ?? 'N/A',
                    'pr_stat' => number_format($required, 2),
                    'p_stat' => number_format($producedQty, 2),
                    'rp_stat' => number_format($remaining, 2),
                    'shift' => $row->shift->name ?? 'N/A',
                    'user' => $row->user->name ?? 'N/A',
                ];
            }

            $remainingToProduce = max($totalProduction - $producedSoFar, 0);

            return response()->json([
                'draw' => intval($request->input('draw')),
                'recordsTotal' => $paginated->total(),
                'recordsFiltered' => $paginated->total(),
                'data' => $data,
                'chart_data' => [
                    'totalProduction' => round($totalProduction, 2),
                    'producedSoFar' => round($producedSoFar, 2),
                    'remaining' => round($remainingToProduce, 2),
                ]
            ]);
        } else {
            if ($request->method() == 'POST') {

            } else {
                $page_title = 'Production Statistics';
                $page_description = 'View production statistics for this shift';

                return view('production.statistics', compact('page_title', 'page_description'));
            }
        }
    }
}
