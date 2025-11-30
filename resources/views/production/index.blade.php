@extends('layouts.app-master')

@push('css')
<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css"/>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.5/css/dataTables.bootstrap5.min.css"/>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .select2-container--classic .select2-selection--single { height: 40px!important; }
    .select2-container--classic .select2-selection--single .select2-selection__arrow { height: 38px!important; }
    .select2-container--classic .select2-selection--single .select2-selection__rendered { line-height: 39px!important; }
    label.error { color: red; }
</style>
@endpush

@section('content')
<div class="bg-light p-4 rounded">
    <h1>{{ $page_title }}</h1>
    <div class="lead">
        {{ $page_description }}
        @if (auth()->check() && auth()->user()->can('production.create') && !$isDispatch)
            <a href="{{ route('production.create') }}" class="btn btn-primary btn-sm float-end">Add Production</a>
            @if ( auth()->user()->can('production.expire.create') )
                <a href="{{ route('production.create', ['expire' => '1']) }}" class="btn btn-danger btn-sm float-end me-2">Add Wastage</a>
            @endif
        @endif
        @if (auth()->check() && auth()->user()->can('production.dispatch.create') && $isDispatch)
            <a href="{{ route('production.create', ['dispatch' => '1']) }}" class="btn btn-success btn-sm float-end">Add Dispatch</a>
        @endif
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
@endsection

@push('js')
<script src="{{ asset('assets/js/other/dataTables.bootstrap5.min.js') }}"></script>
<script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.5/js/dataTables.bootstrap5.min.js"></script>
<script src="{{ asset('assets/js/select2.min.js') }}"></script>

<script>
$(document).ready(function() {
    $('#production-table').DataTable({
        "dom": '<"d-flex justify-content-between mb-2"<"production-table-filter-container">f>rt<"d-flex flex-column float-start mt-3"pi><"clear">',
        processing: false,
        serverSide: true,
        ordering: false,
        ajax: {
            url: "{{ route('production.index') }}",
            data: function ( d ) {
                return $.extend( {}, d, {
                    dispatch: "{{ $isDispatch ? '1' : '0' }}",
                    from_date: $('#from_date').val(),
                    to_date: $('#to_date').val(),
                    category_id: $('#category_filter').val(),
                    product_id: $('#product_filter').val(),
                    uom_id: $('#uom_filter').val(),
                    user_id: $('#user_filter').val(),
                    shift_filter: $('#shift_filter').val()
                });
            }
        },
        columns: [
            { data: 'production_number' },
            { data: 'users', name: 'users', orderable: false, searchable: false },
            { data: 'production_date' },
            { data: 'shift_name' },
            { data: 'products', name: 'products', orderable: false, searchable: false },
            { data: 'units', name: 'units', orderable: false, searchable: false },
            { data: 'total_items', name: 'total_items', orderable: false, searchable: false },
            { data: 'status', orderable: false, searchable: false },
            { data: 'action', name: 'action', orderable: false, searchable: false }
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
                        return { id: item.id, text: item.text };
                    }),
                    pagination: { more: data.pagination.more }
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
                        return { id: item.id, text: item.text };
                    }),
                    pagination: { more: data.pagination.more }
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
                        return { id: item.id, text: item.text };
                    }),
                    pagination: { more: data.pagination.more }
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
                        return { id: item.id, text: item.text };
                    }),
                    pagination: { more: data.pagination.more }
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
            dispatch: "{{ $isDispatch ? '1' : '0' }}",
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
            dispatch: "{{ $isDispatch ? '1' : '0' }}",
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

    // Store table reference for filter functions
    var table = $('#production-table').DataTable();
});
</script>
@endpush
