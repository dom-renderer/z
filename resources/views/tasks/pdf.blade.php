<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> Tea Post Inspection Report - {{ $task->code ?? '-' }} </title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f8f8f8;
        }
        .header {
            background-color: #174C3C;
            color: #fff;
            padding: 20px;
            text-align: center;
            border-radius: 5px;
            position: relative;
            height: 32px;
        }
        .header img {
            width: 50px;
            height: auto;
            position: absolute;
            left: 20px;
            top: 10px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }

        .header p {
            margin: 0;
            font-size: 15px;
            float: right;
            position: relative;
            bottom: 23px;
        }
        .summary {
            display: flex;
            justify-content: space-between;
            background-color: #e8f5e9;
            padding: 15px;
            border-radius: 5px;
            margin-top: 20px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        .summary div {
            flex: 1;
            text-align: center;
            font-weight: bold;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background-color: white;
            margin-top: 20px;
            border-radius: 5px;
            overflow: hidden;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        th, td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }
        th {
            background-color: #174C3C;
            color: white;
            text-transform: uppercase;
        }
        .pass {
            background-color: #c8e6c9;
        }
        .fail {
            background-color: #ffccbc;
        }
        .bolder {
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="header">
        <img src="{{ public_path('assets/logo.webp') }}" alt="Tea Post Logo">
        <h1> {{ $task->parent->parent->checklist->name ?? '' }} </h1>
        <p> {{ $task->code }} </p>
    </div>
    
    @php
    $date1 = \Carbon\Carbon::parse($task->started_at);
    $date2 = \Carbon\Carbon::parse($task->completion_date);
    $diff = $date1->diff($date2);
    $isPointChecklist = \App\Helpers\Helper::isPointChecklist($task->form);
    @endphp


    <table style="margin-top:5px!important;">
        <tbody>
            <tr class="pass">
                <td> 
                    <center>
                        <strong>
                            {{ $task->parent->actstore->name ?? '' }} - {{ $task->parent->actstore->code ?? '' }}
                        </strong>
                    </center>    
                </td>
            </tr>
        </tbody>
    </table>

    <table style="margin-top:5px!important;">
        <tbody>
            <tr class="pass">
                <td> <span class="bolder"> Start Time </span>: {{ $date1->format('d F Y H:i') }} </td>
                <td>  <span class="bolder"> End Time </span> : {{ $task->status == 1 ? '-' : $date2->format('d F Y H:i') }} </td>
                <td> <span class="bolder"> Ops Time: </span> 
                    @if($task->status == 1)
                        -
                    @else
                        @if($diff->d > 0)
                        {{ $diff->d }} days,
                        @endif
                        @if($diff->h > 0)
                        {{ $diff->h }} hours,
                        @endif
                        @if($diff->i > 0)
                        {{ $diff->i }} minutes
                        @endif
                        @if($diff->d <= 0 && $diff->h <= 0 && $diff->i <= 0)
                            -
                        @endif
                    @endif
                   </td>
            </tr>
            <tr class="pass">
                <td colspan="2"> <span class="bolder"> Conducted By: </span> {{ $task->parent->user->name ?? '' }} </td>
                <td> <span class="bolder"> Total Questions: </span> {{ $toBeCounted }} </td>
            </tr>
        </tbody>
    </table>

    <table style="margin-top:5px!important;">
        <tbody>

            @if($isPointChecklist)
            <tr class="pass">
                <td> <span class="bolder"> Pass </span> </td>
                <td> {{ $finalResultData['passed'] }} </td>
            </tr>
            <tr class="pass">
                <td>  <span class="bolder"> N/A </span>  </td>
                <td> {{ $finalResultData['na'] }} </td>
            </tr>
            <tr class="pass">
                <td>  <span class="bolder"> Fail </span> </td>
                <td> {{ $finalResultData['failed'] }} </td>
            </tr>
            <tr class="pass">
                <td>  <span class="bolder"> Percentage </span>  </td>
                <td> {{ $finalResultData['percentage'] }} </td>
            </tr>
            <tr @if($finalResultData['final_result'] == 'Pass') class="pass" @else class="fail" @endif>
                <td>  <span class="bolder"> Result </span>  </td>
                <td> {{ $finalResultData['final_result'] }} </td>
            </tr>
            @endif
        </tbody>
    </table>

    @php
        if (empty($data)) {
            $maxColumns = 3;
        } else {
            $maxColumns = max(array_map('count', $data));
        }
    @endphp

    {{-- FAILED ITEMS --}}

    <br>
    @if(isset($task->parent->parent->checklist->is_point_checklist) && $task->parent->parent->checklist->is_point_checklist == 1)

    @php
        $hasSectionWise = collect($task->data ?? [])->groupBy('page')->values()->toArray();
    @endphp

    @if(count($hasSectionWise) > 0)
    {{-- SECTION WISE RESULT --}}
    <center>
        <span class="bolder" > --- SECTION WISE RESULT --- </span>
    </center>

    <table class="table table-bordered">
        <thead>
            <tr>
                <td class="pass">
                    <strong>
                        Section
                    </strong>
                </td>
                <td class="pass">
                    <strong>
                        Result
                    </strong>
                </td>
            </tr>
        </thead>
        <tbody>
            @forelse ($hasSectionWise as $pKey => $totalSectionsRow)
                @if(!($loop->first || $loop->iteration == 2))
                @php
                    $thisVarients = \App\Helpers\Helper::categorizePoints($totalSectionsRow ?? []);
                    $thisTotal = count(\App\Helpers\Helper::selectPointsQuestions($totalSectionsRow));
                    $thisToBeCounted = $thisTotal - count($thisVarients['na']);

                    $thisFailed = abs(count(array_column($thisVarients['negative'], 'value')));
                    $thisAchieved = $thisToBeCounted - abs($thisFailed);

                    if ($thisFailed <= 0) {
                        $thisAchieved = array_sum(array_column($thisVarients['positive'], 'value'));
                    }
                    
                    if ($thisToBeCounted > 0) {
                        $thisPer = number_format(($thisAchieved / $thisToBeCounted) * 100, 2);
                    } else {
                        $thisPer = 0;
                    }

                    $titleOfSection = '';

                    if (is_array($task->form) && isset($task->form[$pKey])) {
                        $titleOfSection = collect($task->form[$pKey])->where('type', 'header')->get(0)->label ?? '';
                    }

                @endphp

                    <tr>
                        <td class="@if($thisPer > 80) pass @else fail @endif">
                            {{ html_entity_decode($titleOfSection) }}
                        </td>
                        <td class="@if($thisPer > 80) pass @else fail @endif">
                            {{ number_format($thisPer, 2) }}%
                        </td>
                    </tr>
                @endif
            @empty
                <tr>
                    <td>
                        No Data Found
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
    {{-- SECTION WISE RESULT --}}
    @endif

    <br><br>

    <center>
        <span class="bolder" > --- FAILED ITEMS --- </span>
    </center>

    <table>
        <thead>
            <tr>
                <th>Inspection Item</th>
                <th>Result</th>
                <th colspan="{{ $maxColumns - 2 }}">Remark</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($data as $row)
                @if(is_string($row[1]) && (strtolower($row[1]) == 'no' || strtolower($row[1]) == 'fail'))
                    <tr class="fail">
                        @foreach ($row as $key => $value)
                            <td colspan="{{ $loop->last && count($row) < $maxColumns ? $maxColumns - count($row) + 1 : 1 }}"
                                style="font-weight: {{ $loop->first ? 'bold' : 'normal' }}">
                                @if(is_string($value) && strpos($value, 'SIGN-20') !== false)
                                    @if(file_exists(storage_path("app/public/workflow-task-uploads/{$value}")) && is_file(storage_path("app/public/workflow-task-uploads/{$value}")))
                                        <img src="{{ public_path("storage/workflow-task-uploads/{$value}") }}" style="height: 100px;">
                                    @else
                                        <img src="{{ public_path("no-image-found.png") }}" style="height: 100px;">                                    
                                    @endif
                                @elseif(is_array($value))
                                    @foreach ($value as $vl)
                                        @if(strpos($vl, 'SIGN-20') !== false)
                                            @if(file_exists(storage_path("app/public/workflow-task-uploads/{$vl}")) && is_file(storage_path("app/public/workflow-task-uploads/{$vl}")))
                                                <img src="{{ public_path("storage/workflow-task-uploads/{$vl}") }}" style="height: 100px;">
                                            @else
                                                <img src="{{ public_path("no-image-found.png") }}" style="height: 100px;">
                                            @endif
                                        @else
                                            {!! $vl !!}
                                        @endif
                                    @endforeach
                                @else
                                    {!! $value !!}
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @endif
            @endforeach
        </tbody>
    </table>
    @endif
    {{-- FAILED ITEMS --}}

    {{-- FULL REPORT --}}
    <div style="page-break-before:always !important;">
    <br>

    <center>
        <span class="bolder" > --- FULL REPORT --- </span>
    </center>    

        <table>
            <thead>
                <tr>
                    <th>Inspection Item</th>
                    <th>Result</th>
                    <th colspan="{{ $maxColumns - 2 }}">Remark</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($data as $row)
                    <tr 
                    @if(is_string($row[1]) && (strtolower($row[1]) == 'pass' || strtolower($row[1]) == 'yes')) 
                    class="pass" 
                    @elseif(is_string($row[1]) && (strtolower($row[1]) == 'no' || strtolower($row[1]) == 'fail'))
                    class="fail" 
                    @else 
                    class="pass" 
                    @endif 
                    >
                        @foreach ($row as $key => $value)
                            <td colspan="{{ $loop->last && count($row) < $maxColumns ? $maxColumns - count($row) + 1 : 1 }}"
                                style="font-weight: {{ $loop->first ? 'bold' : 'normal' }}">
                                @if(is_string($value) && strpos($value, 'SIGN-20') !== false)
                                    @if(file_exists(storage_path("app/public/workflow-task-uploads/{$value}")) && is_file(storage_path("app/public/workflow-task-uploads/{$value}")))
                                        <img src="{{ public_path("storage/workflow-task-uploads/{$value}") }}" style="height: 100px;">
                                    @else
                                        <img src="{{ public_path("no-image-found.png") }}" style="height: 100px;">                                    
                                    @endif
                                @elseif(is_array($value))
                                    @foreach ($value as $vl)
                                        @if(strpos($vl, 'SIGN-20') !== false)
                                            @if(file_exists(storage_path("app/public/workflow-task-uploads/{$vl}")) && is_file(storage_path("app/public/workflow-task-uploads/{$vl}")))
                                                <img src="{{ public_path("storage/workflow-task-uploads/{$vl}") }}" style="height: 100px;">
                                            @else
                                                <img src="{{ public_path("no-image-found.png") }}" style="height: 100px;">
                                            @endif
                                        @else
                                            {!! $vl !!}
                                        @endif
                                    @endforeach
                                @else
                                    {!! $value !!}
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>    
    </div>
    {{-- FULL REPORT --}}

    {{-- CUSTOM FORMs --}}
    <div style="page-break-before:always !important;">
    @forelse (\App\Helpers\Helper::getCustomFormListing($task->form) as $rowOfCustomForm)
        @php
            $dataToIterate = resolve(\App\Helpers\Helper::$customFormWithEloquent[$rowOfCustomForm])->select(\App\Helpers\Helper::$customFormFields[$rowOfCustomForm])->where('task_id', $task->id)->get();
        @endphp

        <hr>
        <h4>
            {{ \App\Helpers\Helper::$customFormTitle[$rowOfCustomForm] }}
        </h4>

        <table style="margin-top:5px!important;">
            <thead>
                <tr class="pass">
                    @forelse ($dataToIterate as $dataToIterateKey => $dataToIterateValue)
                        @foreach (array_keys($dataToIterateValue->getOriginal()) as $dataToIterateKeyRow)
                            <th> {{ ucwords(str_replace(['_id', '_'], ' ', $dataToIterateKeyRow)) }} </th>
                        @endforeach
                    @break
                    @empty
                    @endforelse
                </tr>
            </thead>
            <tbody>
                    @forelse ($dataToIterate as $dataToIterateValue)
                        <tr class="pass">
                            @foreach ($dataToIterateValue->toArray() as $dataToIterateValueRow)
                                <td> {{ $dataToIterateValueRow }} </td>
                            @endforeach
                        </tr>
                    @empty
                    @endforelse
            </tbody>
        </table>

    @empty
    @endforelse
    </div>
    {{-- CUSTOM FORMs --}}

    <br><br>

    <center>
        <span class="bolder" > --- End of Report --- </span>
    </center>

</body>
</html>