@extends('layouts.app-master')

@push('css')
<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css"/>
<link rel="stylesheet" href="{{ asset('assets/css/select2.min.css') }}">
<link href="{{ asset('assets/css/font-awesome.min.css') }}" rel="stylesheet" />
<style>
    .select2-container--classic .select2-selection--single { height: 40px!important; }
    .select2-container--classic .select2-selection--single .select2-selection__arrow { height: 38px!important; }
    .select2-container--classic .select2-selection--single .select2-selection__rendered { line-height: 39px!important; }
    .prod-card { background:#fde8ec; border-radius:10px; padding:12px; margin: 10px 0px; box-shadow:0 2px 6px rgba(0,0,0,.08); }
    .prod-title { font-weight:700; letter-spacing:.4px; font-size:14px; text-transform:uppercase; display:flex; justify-content:space-between; border-bottom:1px solid rgba(0,0,0,.1); padding-bottom:6px; margin-bottom:8px; }
    .uom-row { display:flex; justify-content:space-between; padding:2px 0; font-size:13px; }
    .category-title { font-weight:700; font-size:18px; margin:18px 0 8px; }
    .cards-wrap { display:flex; flex-wrap:wrap; }
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
@endpush

@section('content')
<div class="rounded">
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
                            <div class="col-md-6 col-lg-3">
                                <label>From Date</label>
                                <input type="date" id="from_date" class="form-control" placeholder="From Date" value="{{ date('Y-m-d') }}">
                            </div>
                            <div class="col-md-6 col-lg-3">
                                <label>To Date</label>
                                <input type="date" id="to_date" class="form-control" placeholder="To Date" value="{{ date('Y-m-d') }}">
                            </div>
                            <div class="col-md-6 col-lg-3">
                                <label>Category</label>
                                <select id="category_filter" class="form-control">
                                    <option value="">All Categories</option>
                                </select>
                            </div>
                            <div class="col-md-6 col-lg-3">
                                <label>Product</label>
                                <select id="product_filter" class="form-control">
                                    <option value="">All Products</option>
                                </select>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-6 col-lg-3">
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
                            <div class="col-md-6 col-lg-3">
                                <label>UOM</label>
                                <select id="uom_filter" class="form-control">
                                    <option value="">All UOMs</option>
                                </select>
                            </div>
                            <div class="col-md-12  col-lg-3">
                                <label>Employee</label>
                                <select id="user_filter" class="form-control">
                                    <option value="">All Employees</option>
                                </select>
                            </div>
                          
                        </div>
                        <div class="row mt-3 align-items-center">
                              <div class="col-md-12 col-lg-12 col-xl-6">
                                <label>&nbsp;</label>
                                <button type="button" id="apply_filters" class="btn btn-primary btn-sm">Apply Filters</button>
                                <button type="button" id="clear_filters" class="btn btn-secondary btn-sm">Clear Filters</button>
                                <div class="btn-group ms-2 float-end">
                                    <!-- <button type="button" class="btn btn-success btn-sm me-2" id="export_excel">Export Excel</button>
                                    <button type="button" class="btn btn-danger btn-sm" id="export_pdf">Export PDF</button> -->
                                </div>
                            </div>

                            <div class="col-md-12 col-lg-12 col-xl-6 flot-nn-sm">
                                <div class="btn-group ms-2 float-end ">
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

    <div id="dashboard-content" class=""></div>
</div>
@endsection

@push('js')
<script src="https://cdn.tailwindcss.com"></script>
<script src="{{ asset('assets/js/other/dataTables.bootstrap5.min.js') }}"></script>
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

    function render(data) {
        let html = '';
        data.forEach((cat, ci) => {

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

    $('#category_filter').select2({ placeholder:'Select Category', allowClear:true, theme:'classic', width:'100%',
        ajax:{ url:"{{ route('production.categories-select2') }}", type:"POST", dataType:'json', delay:250,
            data:params=>({ searchQuery: params.term, page: params.page||1, _token: "{{ csrf_token() }}" }),
            processResults:(data, params)=>({ results: $.map(data.items, item=>({id:item.id, text:item.text})), pagination:{more:data.pagination.more} })
        }
    });

    $('#product_filter').select2({ placeholder:'Select Product', allowClear:true, theme:'classic', width:'100%',
        ajax:{ url:"{{ route('production.products-select2') }}", type:"POST", dataType:'json', delay:250,
            data:params=>({ searchQuery: params.term, category_id: $('#category_filter').val(), page: params.page||1, _token: "{{ csrf_token() }}" }),
            processResults:(data, params)=>({ results: $.map(data.items, item=>({id:item.id, text:item.text})), pagination:{more:data.pagination.more} })
        }
    });

    $('#shift_filter').select2({ placeholder:'Select Shift', allowClear:true, width:'100%', theme:'classic'});

    $('#uom_filter').select2({ placeholder:'Select UOM', allowClear:true, theme:'classic', width:'100%',
        ajax:{ url:"{{ route('production.uoms-select2') }}", type:"POST", dataType:'json', delay:250,
            data:params=>({ searchQuery: params.term, product_id: $('#product_filter').val(), page: params.page||1, _token: "{{ csrf_token() }}" }),
            processResults:(data, params)=>({ results: $.map(data.items, item=>({id:item.id, text:item.text})), pagination:{more:data.pagination.more} })
        }
    });

    $('#user_filter').select2({ placeholder:'Select employee', allowClear:true, theme:'classic', width:'100%',
        ajax:{ url:"{{ route('users-list') }}", type:"POST", dataType:'json', delay:250,
            data:params=>({ searchQuery: params.term, page: params.page||1, _token: "{{ csrf_token() }}", ignoreDesignation:1 }),
            processResults:(data, params)=>({ results: $.map(data.items, item=>({id:item.id, text:item.text})), pagination:{more:data.pagination.more} })
        }
    });

    $('#category_filter').on('change', function(){ $('#product_filter').val(null).trigger('change'); $('#uom_filter').val(null).trigger('change'); });
    $('#product_filter').on('change', function(){ $('#uom_filter').val(null).trigger('change'); });

    $('#apply_filters').on('click', fetchData);
    $('#clear_filters').on('click', function(){
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
        
        window.open("{{ route('production.export.excel') }}?" + params.toString(), '_blank');
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
        
        window.open("{{ route('production.export.pdf') }}?" + params.toString(), '_blank');
    });

    fetchData();
});
</script>
@endpush
