<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title> Production Dashboard </title>

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
            margin-left: 0px!important;
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
            padding: 1rem!important;
        }
        .grid-cols-4 .p-5 {
            padding:1.25rem!important
        }
        .collapse {
            visibility: visible!important;
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


                                
                            </div>
                            <div class="row mt-3">
                                <div class="col-md-12 col-lg-6">
                                        <label>&nbsp;</label>
                                        <button type="button" id="apply_filters" class="btn btn-primary btn-sm">Apply Filters</button>
                                        <button type="button" id="clear_filters" class="btn btn-secondary btn-sm">Clear Filters</button>
                                        <div class="btn-group ms-2 float-end">
                                            <button type="button" class="btn btn-success btn-sm me-2" id="export_excel">Export Excel</button>
                                            <button type="button" class="btn btn-danger btn-sm" id="export_pdf">Export PDF</button>
                                        </div>
                                    </div>

                                    <div class="col-md-12 col-lg-6 flont-mn">
                                        <div class="btn-group ms-2 float-end">
                                            <button class="btn btn-primary d-flex my-tbl-card-view">
                                                <svg style="position: relative;top: 5px;margin-right: 10px;" width="16" height="16" class="svg-inline--fa fa-table-cells" aria-hidden="true" focusable="false" data-prefix="fas" data-icon="table-cells" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" data-fa-i2svg=""><path fill="currentColor" d="M64 32C28.7 32 0 60.7 0 96V416c0 35.3 28.7 64 64 64H448c35.3 0 64-28.7 64-64V96c0-35.3-28.7-64-64-64H64zm88 64v64H64V96h88zm56 0h88v64H208V96zm240 0v64H360V96h88zM64 224h88v64H64V224zm232 0v64H208V224h88zm64 0h88v64H360V224zM152 352v64H64V352h88zm56 0h88v64H208V352zm240 0v64H360V352h88z"></path></svg></i>
                                                <span>
                                                    Card View
                                                </span>
                                            </button>
                                            <button class="flex item-center my-tbl-table-view d-flex btn-outline-primary" style="border-color: #5e0002!important;border: 1px solid;border-radius: 5px;">
                                                <svg style="position: relative;top: 10px;margin-right: 10px;margin-left:10px;" width="16" height="16" class="svg-inline--fa fa-table" aria-hidden="true" focusable="false" data-prefix="fas" data-icon="table" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" data-fa-i2svg=""><path fill="currentColor" d="M64 256V160H224v96H64zm0 64H224v96H64V320zm224 96V320H448v96H288zM448 256H288V160H448v96zM64 32C28.7 32 0 60.7 0 96V416c0 35.3 28.7 64 64 64H448c35.3 0 64-28.7 64-64V96c0-35.3-28.7-64-64-64H64z"></path></svg></i>
                                                <span style="position: relative;top: 5px;margin-right: 10px;">
                                                    Table View
                                                </span>
                                            </button>
                                        </div>
                                    </div>
                            </div>    
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

                    <div id="dashboard-content" class="mt-3"></div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="{{ asset('assets/js/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/js/jquery-ui.js') }}"></script>

    <script src="{{ url('assets/js/jquery-validate.min.js') }}"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('assets/js/select2.min.js') }}"></script>
    <script>
        let currentView = 'card';
        $(document).ready(function() {
            function fetchData() {
                const params = {
                    from_date: $('#from_date').val(),
                    to_date: $('#to_date').val(),
                    category_id: $('#category_filter').val(),
                    product_id: $('#product_filter').val(),
                    uom_id: $('#uom_filter').val(),
                    user_id: $('#user_filter').val(),
                    shift_filter: $('#shift_filter').val(),
                    view_type: currentView
                };
                $('#dashboard-content').html('<div class="text-center py-5">Loading...</div>');
                $.get("{{ route('production.dashboard.data') }}", params, function(resp) {
                    if (!resp.html) {
                        html = '<div class="text-center py-5">No data found for selected filters.</div>';
                    } else {
                        html = resp.html;
                    }
                    
                    $('#dashboard-content').html(html);
                });
            }

            $(document).on('click', '.my-tbl-card-view', function () {
                currentView = 'card';
                fetchData();
            });

            $(document).on('click', '.my-tbl-table-view', function () {
                currentView = 'table';
                fetchData();
            });

            function render(categories) {
                let html = '';
                categories.forEach((cat, ci) => {
                    html += `<div class="category-block"><div class="category-title">${cat.category}</div><div class="cards-wrap row">`;
                    (cat.products || []).forEach((p, pi) => {
                        let uoms = '';
                        let wastageRows = '';
                        let totalQtys = 0;
                        let totalWstgQtys = 0;
                        (p.uoms || []).forEach(u => {
                            uoms += `<div class="uom-row"><div>${u.uom}</div><div><strong>${u.qty}</strong></div></div>`;
                            if (u.wastage > 0) {
                                wastageRows += `<div class="uom-row"><div>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;${u.uom}</div><div><strong>${u.wastage}</strong></div></div>`;
                            }
                            totalQtys += u.qty;
                            totalWstgQtys += u.wastage;
                        });
                        const accId = `wstg-${ci}-${pi}`;
                        html += `<div class="col-md-3"> <div class="prod-card">
                            <div class="prod-title">
                                <span>${p.product}</span>                                
                            </div>
                            ${uoms}
                            <hr>
                        <strong>
                         <div class="uom-row"><div>Total</div><div><strong>${totalQtys}</strong></div></div>
                         <div class="uom-row"><div>Wastage 
                            <button type="button" class="btn btn-link btn-sm p-0 ms-1 align-baseline toggle-collapse" 
                            data-target="#${accId}">
                            <i class="fa fa-chevron-circle-down" style="color:#5e0002;" aria-hidden="true"></i>
                            </button>
                         </div><div><strong>${totalWstgQtys}</strong></div></div>
                         <div class="collapse wastage-material" id="${accId}">
                            <div class="mt-2">
                                ${wastageRows || '<div class="text-muted small">No wastage.</div>'}
                            </div>
                         </div>
                         <div class="uom-row" style="font-size:17px;"><div>Grand Total</div><div><strong>${totalQtys - totalWstgQtys}</strong></div></div>
                        </strong>
                         </div></div>
                         `;
                    });
                    html += `</div></div>`;
                });
                if (!html) html = '<div class="text-center py-5">No data found for selected filters.</div>';
                $('#dashboard-content').html(html);
            }

            $(document).on('click', '.toggle-collapse', function(e) {
                e.preventDefault();
                const target = $(this).data('target');
                const $collapse = $(target);
                const $icon = $(this).find('i');

                if ($collapse.hasClass('show')) {
                    $collapse.collapse('hide');
                    $icon.removeClass('fa-chevron-circle-up').addClass('fa-chevron-circle-down');
                } else {
                    $collapse.collapse('show');
                    $icon.removeClass('fa-chevron-circle-down').addClass('fa-chevron-circle-up');
                }
            });

            $('#category_filter').select2({
                placeholder: 'Select Category',
                allowClear: true,
                width: '100%',
                theme: 'classic',
                ajax: {
                    url: "{{ route('production.categories-select2') }}",
                    type: "GET",
                    dataType: 'json',
                    delay: 250,
                    data: params => ({
                        searchQuery: params.term,
                        page: params.page || 1
                    }),
                    processResults: (data, params) => ({
                        results: $.map(data.items, item => ({
                            id: item.id,
                            text: item.text
                        })),
                        pagination: {
                            more: data.pagination.more
                        }
                    })
                }
            });

            $('#product_filter').select2({
                placeholder: 'Select Product',
                allowClear: true,
                width: '100%',
                theme: 'classic',
                ajax: {
                    url: "{{ route('production.products-select2') }}",
                    type: "GET",
                    dataType: 'json',
                    delay: 250,
                    data: params => ({
                        searchQuery: params.term,
                        category_id: $('#category_filter').val(),
                        page: params.page || 1
                    }),
                    processResults: (data, params) => ({
                        results: $.map(data.items, item => ({
                            id: item.id,
                            text: item.text
                        })),
                        pagination: {
                            more: data.pagination.more
                        }
                    })
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
                width: '100%',
                theme: 'classic',
                ajax: {
                    url: "{{ route('production.uoms-select2') }}",
                    type: "GET",
                    dataType: 'json',
                    delay: 250,
                    data: params => ({
                        searchQuery: params.term,
                        product_id: $('#product_filter').val(),
                        page: params.page || 1
                    }),
                    processResults: (data, params) => ({
                        results: $.map(data.items, item => ({
                            id: item.id,
                            text: item.text
                        })),
                        pagination: {
                            more: data.pagination.more
                        }
                    })
                }
            });

            $('#user_filter').select2({
                placeholder: 'Select employee',
                allowClear: true,
                width: '100%',
                theme: 'classic',
                ajax: {
                    url: "{{ route('users-list') }}",
                    type: "GET",
                    dataType: 'json',
                    delay: 250,
                    data: params => ({
                        searchQuery: params.term,
                        page: params.page || 1,
                        ignoreDesignation: 1
                    }),
                    processResults: (data, params) => ({
                        results: $.map(data.items, item => ({
                            id: item.id,
                            text: item.text
                        })),
                        pagination: {
                            more: data.pagination.more
                        }
                    })
                }
            });

            $('#category_filter').on('change', function() {
                $('#product_filter').val(null).trigger('change');
                $('#uom_filter').val(null).trigger('change');
            });
            $('#product_filter').on('change', function() {
                $('#uom_filter').val(null).trigger('change');
            });

            $('#apply_filters').on('click', fetchData);
            $('#clear_filters').on('click', function() {
                $('#category_filter').val(null).trigger('change');
                $('#product_filter').val(null).trigger('change');
                $('#uom_filter').val(null).trigger('change');
                $('#shift_filter').val(null).trigger('change');
                $('#user_filter').val(null).trigger('change');
                fetchData();
            });

            $('#export_excel').on('click', function() {
              var params = new URLSearchParams({
                dispatch: 0,
                from_date: $('#from_date').val(),
                to_date: $('#to_date').val(),
                category_id: $('#category_filter').val(),
                product_id: $('#product_filter').val(),
                uom_id: $('#uom_filter').val(),
                user_id: $('#user_filter').val(),
                shift_id: $('#shift_filter').val()
              });
             
              var pdfUrl = "{{ url('production-export-excel?') }}" + params.toString();
             
              if (window.Flutter) {
                Flutter.postMessage(pdfUrl);
              } else {
                alert('Flutter WebView bridge not available.');
              }
            });

            $('#export_pdf').on('click', function() {
              var params = new URLSearchParams({
                dispatch: 0,
                from_date: $('#from_date').val(),
                to_date: $('#to_date').val(),
                category_id: $('#category_filter').val(),
                product_id: $('#product_filter').val(),
                uom_id: $('#uom_filter').val(),
                user_id: $('#user_filter').val(),
                shift_id: $('#shift_filter').val()
              });
             
              var pdfUrl = "{{ url('production-export-pdf?') }}" + params.toString();
             
              if (window.Flutter) {
                Flutter.postMessage(pdfUrl);
              } else {
                alert('Flutter WebView bridge not available.');
              }
            });

            fetchData();
        });
    </script>

</body>

</html>