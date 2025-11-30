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
                    <h1>{{ $page_title ?? 'Production' }}</h1>
                    <div class="lead">
                        {{ $page_description ?? 'Production Listing' }}
                        <a href="{{ route('production.create', ['is_web_view' => 1]) }}" class="btn btn-primary btn-sm float-end">Add Production</a>
                        <a href="{{ route('production.create', ['expire' => '1', 'is_web_view' => 1]) }}" class="btn btn-danger btn-sm float-end me-2">Add Wastage</a>
                    </div>
                    <div class="mt-2">
                        @include('layouts.partials.messages')
                    </div>

                    <div class="card mt-4">
                        <div class="accordion" id="accordionExample">
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button" style="background-color: #fde8ec;" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                        Filters
                                    </button>
                                </h2>
                                <div id="collapseOne" class="accordion-collapse collapse" data-bs-parent="#accordionExample">
                                    <div class="accordion-body">
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-3">
                                                    <label>From Date</label>
                                                    <input type="date" id="from_date" class="form-control" placeholder="From Date" value="{{ date('Y-m-d') }}">
                                                </div>
                                                <div class="col-md-3">
                                                    <label>To Date</label>
                                                    <input type="date" id="to_date" class="form-control" placeholder="To Date" value="{{ date('Y-m-d') }}">
                                                </div>
                                                <div class="col-md-3">
                                                    <label>Category</label>
                                                    <select id="category_filter" class="form-control">
                                                        <option value="">All Categories</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-3">
                                                    <label>Product</label>
                                                    <select id="product_filter" class="form-control">
                                                        <option value="">All Products</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="row mt-3">
                                                <div class="col-md-3">
                                                    <label>Shift</label>
                                                    <select name="shift_filter" id="shift_filter">
                                                        @forelse(\App\Models\Shift::get() as $shift)
                                                        @php
                                                        $now = \Carbon\Carbon::now()->format('H:i:s');

                                                        $start = \Carbon\Carbon::createFromFormat('H:i:s', $shift->start);
                                                        $end = \Carbon\Carbon::createFromFormat('H:i:s', $shift->end);
                                                        $current = \Carbon\Carbon::createFromFormat('H:i:s', $now);

                                                        $isInShift = $end->greaterThan($start)
                                                        ? $current->between($start, $end)
                                                        : $current->greaterThanOrEqualTo($start) || $current->lessThanOrEqualTo($end);
                                                        @endphp
                                                        <option value="{{ $shift->id }}" @if($isInShift) selected @endif> {{ $shift->title }} </option>
                                                        @empty
                                                        @endforelse
                                                    </select>
                                                </div>
                                                <div class="col-md-3">
                                                    <label>UOM</label>
                                                    <select id="uom_filter" class="form-control">
                                                        <option value="">All UOMs</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-3">
                                                    <label>Employee</label>
                                                    <select id="user_filter" class="form-control">
                                                        <option value="">All Employees</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-6">
                                                    <label>&nbsp;</label><br>
                                                    <button type="button" id="apply_filters" class="btn btn-primary btn-sm">Apply Filters</button>
                                                    <button type="button" id="clear_filters" class="btn btn-secondary btn-sm">Clear Filters</button>
                                                    <div class="btn-group ms-2 float-end">
                                                        <button type="button" class="btn btn-success btn-sm me-2" id="export_excel">Export Excel</button>
                                                        <button type="button" class="btn btn-danger btn-sm" id="export_pdf">Export PDF</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="tab-content" id="myTabContent">
                        <div class="tab-pane fade show active" id="production-tab-pane" role="tabpanel" aria-labelledby="production-tab" tabindex="0">
                            <table id="production-table" class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Production Number</th>
                                        <th>Employee</th>
                                        <th>Production Date</th>
                                        <th>Production Shift</th>
                                        <th>Product</th>
                                        <th>Unit</th>
                                        <th>Quantity</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="{{ asset('assets/js/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/js/jquery-ui.js') }}"></script>
    <script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}"></script>
    <script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
    <script src="{{ asset('assets/js/other/dataTables.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('assets/js/select2.min.js') }}"></script>
    <script src="{{ url('assets/js/jquery-validate.min.js') }}"></script>
    
    <script>
        $(document).ready(function() {
            $('#production-table').DataTable({
                "dom": '<"d-flex justify-content-between mb-2"<"production-table-filter-container">f>rt<"d-flex flex-column float-start mt-3"pi><"clear">',
                processing: false,
                serverSide: true,
                ordering: false,
                ajax: {
                    url: "{{ route('production.index') }}",
                    data: function(d) {
                        return $.extend({}, d, {
                            dispatch: "{{ isset($isDispatch) && $isDispatch ? '1' : '0' }}",
                            from_date: $('#from_date').val(),
                            to_date: $('#to_date').val(),
                            category_id: $('#category_filter').val(),
                            product_id: $('#product_filter').val(),
                            uom_id: $('#uom_filter').val(),
                            user_id: $('#user_filter').val(),
                            shift_filter: $('#shift_filter').val(),
                            is_web_view: {{ request()->has('is_web_view') ? '1' : '0' }}
                        });
                    }
                },
                columns: [{
                        data: 'production_number'
                    },
                    {
                        data: 'users',
                        name: 'users',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'production_date'
                    },
                    {
                        data: 'shift_name'
                    },
                    {
                        data: 'products',
                        name: 'products',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'units',
                        name: 'units',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'total_items',
                        name: 'total_items',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'status',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    }
                ],
            });

            $(document).on('click', '.deleteGroup', function(e) {
                e.preventDefault();
                Swal.fire({
                    title: 'Are you sure you want to delete this Production?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $(this).parents('form').submit();
                    }
                })
            });

            $(document).on('click', '.dispatch-btn', function(e) {
                e.preventDefault();
                Swal.fire({
                    title: 'Are you sure you want to dispatch this Production?',
                    text: "This action will mark the production as dispatched!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, dispatch it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $(this).parents('form').submit();
                    }
                })
            });

            $(document).on('click', '.expire-btn', function(e) {
                e.preventDefault();
                Swal.fire({
                    title: 'Are you sure you want to add this production to wastage?',
                    text: "This action will mark the production as wastage!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, Add it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $(this).parents('form').submit();
                    }
                })
            });

            // Initialize Select2 for filters
            $('#category_filter').select2({
                placeholder: 'Select Category',
                allowClear: true,
                width: "100%",
                theme: 'classic',
                ajax: {
                    url: "{{ route('production.categories-select2') }}",
                    type: "POST",
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            searchQuery: params.term,
                            page: params.page || 1,
                            _token: "{{ csrf_token() }}"
                        };
                    },
                    processResults: function(data, params) {
                        return {
                            results: $.map(data.items, function(item) {
                                return {
                                    id: item.id,
                                    text: item.text
                                };
                            }),
                            pagination: {
                                more: data.pagination.more
                            }
                        };
                    }
                }
            });

            $('#product_filter').select2({
                placeholder: 'Select Product',
                allowClear: true,
                theme: 'classic',
                width: "100%",
                ajax: {
                    url: "{{ route('production.products-select2') }}",
                    type: "POST",
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            searchQuery: params.term,
                            category_id: $('#category_filter').val(),
                            page: params.page || 1,
                            _token: "{{ csrf_token() }}"
                        };
                    },
                    processResults: function(data, params) {
                        return {
                            results: $.map(data.items, function(item) {
                                return {
                                    id: item.id,
                                    text: item.text
                                };
                            }),
                            pagination: {
                                more: data.pagination.more
                            }
                        };
                    }
                }
            });

            $('#shift_filter').select2({
                placeholder: 'Select Shift',
                allowClear: true,
                width: '100%',
                theme: 'classic'
            });

            $('#uom_filter').select2({
                placeholder: 'Select UOM',
                allowClear: true,
                theme: 'classic',
                width: "100%",
                ajax: {
                    url: "{{ route('production.uoms-select2') }}",
                    type: "POST",
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            searchQuery: params.term,
                            product_id: $('#product_filter').val(),
                            page: params.page || 1,
                            _token: "{{ csrf_token() }}"
                        };
                    },
                    processResults: function(data, params) {
                        return {
                            results: $.map(data.items, function(item) {
                                return {
                                    id: item.id,
                                    text: item.text
                                };
                            }),
                            pagination: {
                                more: data.pagination.more
                            }
                        };
                    }
                }
            });

            $('#user_filter').select2({
                placeholder: 'Select an employee',
                allowClear: true,
                theme: 'classic',
                width: "100%",
                ajax: {
                    url: "{{ route('users-list') }}",
                    type: "POST",
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            searchQuery: params.term,
                            page: params.page || 1,
                            _token: "{{ csrf_token() }}",
                            ignoreDesignation: 1
                        };
                    },
                    processResults: function(data, params) {
                        return {
                            results: $.map(data.items, function(item) {
                                return {
                                    id: item.id,
                                    text: item.text
                                };
                            }),
                            pagination: {
                                more: data.pagination.more
                            }
                        };
                    }
                }
            });

            // Category change event - reset product and UOM
            $('#category_filter').on('change', function() {
                $('#product_filter').val(null).trigger('change');
                $('#uom_filter').val(null).trigger('change');
            });

            // Product change event - reset UOM
            $('#product_filter').on('change', function() {
                $('#uom_filter').val(null).trigger('change');
            });

            // Apply filters
            $('#apply_filters').on('click', function() {
                table.ajax.reload();
            });

            // Clear filters
            $('#clear_filters').on('click', function() {
                $('#from_date').val('');
                $('#to_date').val('');
                $('#category_filter').val(null).trigger('change');
                $('#product_filter').val(null).trigger('change');
                $('#uom_filter').val(null).trigger('change');
                $('#shift_filter').val(null).trigger('change');
                $('#user_filter').val(null).trigger('change');
                table.ajax.reload();
            });

            // Export functions
            $('#export_excel').on('click', function() {
                var params = new URLSearchParams({
                    dispatch: "{{ isset($isDispatch) && $isDispatch ? '1' : '0' }}",
                    from_date: $('#from_date').val(),
                    to_date: $('#to_date').val(),
                    category_id: $('#category_filter').val(),
                    product_id: $('#product_filter').val(),
                    uom_id: $('#uom_filter').val(),
                    user_id: $('#user_filter').val(),
                    shift_id: $('#shift_filter').val()
                });

                var pdfUrl = "https://zeppoli.digitalsummation.com/production-export-excel?" + params.toString();
                
                if (window.Flutter) {
                    Flutter.postMessage(pdfUrl);
                } else {
                    alert('Flutter WebView bridge not available.');
                }
            });

            $('#export_pdf').on('click', function() {
                var params = new URLSearchParams({
                    dispatch: "{{ isset($isDispatch) && $isDispatch ? '1' : '0' }}",
                    from_date: $('#from_date').val(),
                    to_date: $('#to_date').val(),
                    category_id: $('#category_filter').val(),
                    product_id: $('#product_filter').val(),
                    uom_id: $('#uom_filter').val(),
                    user_id: $('#user_filter').val(),
                    shift_id: $('#shift_filter').val()
                });

                var pdfUrl = "https://zeppoli.digitalsummation.com/production-export-pdf?" + params.toString();
                
                if (window.Flutter) {
                    Flutter.postMessage(pdfUrl);
                } else {
                    alert('Flutter WebView bridge not available.');
                }
            });

            // Store table reference for filter functions
            var table = $('#production-table').DataTable();
        });
    </script>

</body>

</html>