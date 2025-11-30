<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title> Production Listing </title>

    <link href="{!! url('assets/css/bootstrap.min.css') !!}" rel="stylesheet">
    <link href="{!! url('assets/css/my-style.css') !!}" rel="stylesheet">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="{{ asset('assets/css/jquery-ui.css') }}">

    <!-- code added by binal start--->
    <meta name="msapplication-TileColor" content="#da532c">
    <meta name="theme-color" content="#ffffff">
    <!-- code added by binal end--->

    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css" />
    <link rel="stylesheet" href="{{ asset('assets/css/select2.min.css') }}">
    <link href="{{ asset('assets/css/font-awesome.min.css') }}" rel="stylesheet" />

    <style type="text/css">
        .numberCircle {
            font-family: "OpenSans-Semibold", Arial, "Helvetica Neue", Helvetica, sans-serif;
            display: inline-block;
            color: #fff;
            text-align: center;
            line-height: 0px;
            border-radius: 50%;
            font-size: 12px;
            font-weight: 700;
            min-width: 38px;
            min-height: 38px;
        }

        .numberCircle span {
            display: inline-block;
            padding-top: 50%;
            padding-bottom: 50%;
            margin-left: 1px;
            margin-right: 1px;
        }

        /* Some Back Ground Colors */
        .clrTotal {
            background: #51a529;
        }

        .clrLike {
            background: #60a949;
        }

        .clrDislike {
            background: #bd3728;
        }

        .clrUnknown {
            background: #58aeee;
        }

        .clrStatusPause {
            color: #bd3728;
        }

        .clrStatusPlay {
            color: #60a949;
        }

        .LoaderSec {
            position: fixed;
            background: #465b97c7;
            width: 100%;
            height: 100%;
            left: 0;
            top: 0;
            z-index: 99999999999;
        }

        .LoaderSec .loader {
            width: 55px;
            height: 55px;
            border: 6px solid #fff;
            border-bottom-color: #5f0000;
            border-radius: 50%;
            display: inline-block;
            -webkit-animation: rotation 1s linear infinite;
            animation: rotation 1s linear infinite;
            position: fixed;
            z-index: 9999999999999;
            transform: translate(-50%, -50%);
            top: 50%;
            left: 50%;
        }

        .content-wrapper {
            margin-left: 0px !important;
        }

        @keyframes rotation {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        .select2-container--classic .select2-selection--single {
            height: 40px !important;
        }

        .select2-container--classic .select2-selection--single .select2-selection__arrow {
            height: 38px !important;
        }

        .select2-container--classic .select2-selection--single .select2-selection__rendered {
            line-height: 39px !important;
        }

        .prod-card {
            background: #fde8ec;
            border-radius: 10px;
            padding: 12px;
            margin: 10px 0px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, .08);
        }

        .prod-title {
            font-weight: 700;
            letter-spacing: .4px;
            font-size: 14px;
            text-transform: uppercase;
            display: flex;
            justify-content: space-between;
            border-bottom: 1px solid rgba(0, 0, 0, .1);
            padding-bottom: 6px;
            margin-bottom: 8px;
        }

        .uom-row {
            display: flex;
            justify-content: space-between;
            padding: 2px 0;
            font-size: 13px;
        }

        .category-title {
            font-weight: 700;
            font-size: 18px;
            margin: 18px 0 8px;
        }

        .cards-wrap {
            display: flex;
            flex-wrap: wrap;
        }

        .wastage-material {
            color: red;
        }

        .wastage-material {
            color: red;
        }

        .space-y-2 .p-4 {
            padding: 1rem !important;
        }

        .grid-cols-4 .p-5 {
            padding: 1.25rem !important
        }

        .collapse {
            visibility: visible !important;
        }
    </style>

</head>

<body>
    <div class="wrapper">

        <div class="LoaderSec d-none">
            <span class="loader"></span>
        </div>

        <div class="content-wrapper">
            <div class="container-fluid">
                <div class="bg-light rounded">
                    <div class="lead mb-3">
                        <a href="{{ route('production.index') }}{{ request()->has('is_web_view') ? '?is_web_view=1' : '' }}{{ !empty($isDispatch) && $isDispatch ? '&dispatch=1' : '' }}" class="btn btn-secondary btn-sm">Back</a>
                    </div>

                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4"><strong>Production Number:</strong> {{ $production->production_number }}</div>
                                <div class="col-md-4"><strong>Status:</strong>
                                    <span class="badge bg-{{ Helper::$productionStatusColors[$production->status] ?? 'secondary' }}">
                                        {{ Helper::$productionStatuses[$production->status] ?? ucfirst($production->status) }}
                                    </span>
                                </div>
                                <div class="col-md-4"><strong>Total Items:</strong> {{ $production->items->count() }}</div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-md-4"><strong>Production Date:</strong> {{ !empty($production->production_date) ? date('d-m-Y H:i', strtotime($production->production_date)) : '-' }}</div>
                                <div class="col-md-4"><strong>Created At:</strong> {{ $production->created_at->format('d-m-Y H:i') }}</div>
                                <div class="col-md-4"><strong>Shift:</strong> {{ $production->shift->title ?? '' }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-3">
                        <div class="card-header"><strong>Production Items</strong></div>
                        <div class="card-body p-0">
                            <table class="table table-striped mb-0">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Employee</th>
                                        <th>Product</th>
                                        <th>Unit</th>
                                        <th>Quantity</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($production->items as $index => $item)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $item->user->name ?? '-' }}</td>
                                        <td>{{ $item->product->name ?? '-' }}</td>
                                        <td>{{ $item->unit->name ?? '-' }}</td>
                                        <td>{{ $item->quantity }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="4"> <strong> Total </strong> </td>
                                        <td> <strong> {{ number_format($production->items->sum('quantity') ?? 0, 2) }} </strong> </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header"><strong>Production Logs</strong></div>
                        <div class="card-body p-0">
                            <table class="table table-sm mb-0">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Comment</th>
                                        <th>At</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($logs as $index => $log)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{!! $log->comment !!}</td>
                                        <td>{{ $log->created_at->format('d-m-Y H:i') }}</td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="3" class="text-center">No logs available</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="{{ asset('assets/js/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/js/other/dataTables.bootstrap5.min.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="{{ asset('assets/js/jquery-ui.js') }}"></script>
    <script src="{{ asset('assets/js/select2.min.js') }}"></script>
    <script src="{{ url('assets/js/jquery-validate.min.js') }}"></script>



</body>

</html>