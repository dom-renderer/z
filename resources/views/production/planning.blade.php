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
        @if ( auth()->user()->can('production.planning-import') )
            <button class="btn btn-success btn-sm float-end me-2" style="background: #1d721d !important;border-color: #1d721d !important;" data-bs-toggle="modal" data-bs-target="#browser-file">Import Order Sheet</button>
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
                                    <select id="shift_filter" class="form-control">
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
                        <th>Product</th>
                        <th>UoM</th>
                        <th>SO Qty</th>
                        <th>Indent Qty</th>
                        <th>Total Order</th>
                        <th>Opening Stock</th>
                        <th>Production</th>
                        <th>Date</th>
                        <th>Shift</th>
                        <th>Added By</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="browser-file" tabindex="-1" aria-labelledby="browser-file" aria-hidden="true">
    <form id="fileUploader" method="POST" action="{{ route('production.planning-import') }}" enctype="multipart/form-data"> @csrf
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="exampleModalLabel">Import Order Sheet</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">

                    <div class="mb-3">
                        <label for="xlsxfile" class="form-label"> Select a Excel File </label>
                        <input type="file" name="xlsx" class="form-control" id="xlsx">
                    </div>

                    <div class="mb-3">
                        <label for="datetime" class="form-label"> Date </label>
                        @if(!in_array(\App\Helpers\Helper::$roles['admin'], auth()->user()->roles->pluck('id')->toArray()))
                            <input type="text" class="form-control" id="datetime" value="{{ date('Y-m-d H:i') }}" disabled>
                            <input type="hidden" name="datetime" value="{{ date('Y-m-d H:i') }}">
                        @else
                            <input type="datetime-local" name="datetime" class="form-control" id="datetime" value="{{ date('Y-m-d H:i') }}" >
                        @endif
                    </div>

                    <div class="mb-3">
                        <label for="shiftid" class="form-label"> Shift </label>
                        @if(!in_array(\App\Helpers\Helper::$roles['admin'], auth()->user()->roles->pluck('id')->toArray()))
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

                                @if($isInShift)
                                <input type="hidden" name="shiftid" value="{{ $shift->id }}">
                                @endif
                            @empty
                            @endforelse
                            <select id="shiftid" disabled>
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

                                    @if($isInShift)
                                        <option value="{{ $shift->id }}" selected> {{ $shift->title }} </option>
                                    @endif
                                @empty
                                @endforelse
                            </select>
                        @else
                            <select id="shiftid" name="shiftid">
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
                        @endif
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-success">Import</button>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('js')
<script src="{{ asset('assets/js/other/dataTables.bootstrap5.min.js') }}"></script>
<script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.5/js/dataTables.bootstrap5.min.js"></script>
<script src="{{ asset('assets/js/select2.min.js') }}"></script>
<script src="{{ asset('assets/js/jquery-validate.min.js') }}"></script>

<script>
$(document).ready(function() {

    jQuery.validator.addMethod("extension", function (value, element, param) {
    if (element.files.length > 0) {
        const file = element.files[0];
        const fileExtension = file.name.split('.').pop().toLowerCase();
        return fileExtension === param.toLowerCase();
    }
    return true;
    }, "Please upload a valid file type.");

    jQuery.validator.addMethod("filesize", function (value, element, param) {
    if (element.files.length > 0) {
        return element.files[0].size <= param;
    }
    return true;
    }, "File size must not exceed {0} bytes.");

    $('#fileUploader').validate({
        rules: {
            xlsx: {
                required: true,
                extension: 'xlsx'
            },
            datetime: {
                required: true
            },
            shiftid: {
                required: true
            }
        },
        messages: {
            xlsx: {
                required: "Please select a file",
                extension: 'Only .xlsx file is allowed for import'
            },
            datetime: {
                required: "Select a date"
            },
            shiftid: {
                required: "Select a shift"
            }
        },
        submitHandler: function(form, event) { 
            event.preventDefault();

            let formData = new FormData(form);

            $.ajax({
                url: "{{ route('production.planning-import') }}",
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                beforeSend: function () {
                    $('body').find('.LoaderSec').removeClass('d-none');
                },
                success: function(response) {
                    $('body').find('.LoaderSec').addClass('d-none');
                    
                    if (response.status) {
                        $('#browser-file').modal('hide');
                        $('form#fileUploader')[0].reset();
                        $('.modal-backdrop').remove();

                        if (response.is_partially_succeed) {
                            Swal.fire({
                                title: 'Warning',
                                text: 'Some orders are not imported. Check import history for details.',
                                icon: 'warning',
                                showCancelButton: true,
                                confirmButtonText: 'Go to Dashboard',
                                cancelButtonText: 'Import History',
                                reverseButtons: true
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    window.location.href = "{{ route('production-dashboard') }}";
                                } else if (result.dismiss === Swal.DismissReason.cancel) {
                                    window.location.href = "{{ route('imported-planning-history') }}";
                                }
                            });
                        } else {
                            Swal.fire({
                                title: 'Success',
                                text: response.message,
                                icon: 'success',
                                showCancelButton: true,
                                confirmButtonText: 'Go to Dashboard',
                                cancelButtonText: 'Close'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    window.location.href = "{{ route('production-dashboard') }}";
                                }
                            });
                        }

                    } else {
                        window.location.href = "{{ route('production.planning-import') }}";
                    }
                }
            });
        }
    });

    $('#production-table').DataTable({
        "dom": '<"d-flex justify-content-between mb-2"<"production-table-filter-container">f>rt<"d-flex flex-column float-start mt-3"pi><"clear">',
        processing: false,
        serverSide: true,
        ordering: false,
        ajax: {
            url: "{{ route('production.planning') }}",
            data: function ( d ) {
                return $.extend( {}, d, {
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
            { data: 'products', name: 'products', orderable: false, searchable: false },
            { data: 'units', name: 'units', orderable: false, searchable: false },
            { data: 'sales_order'},
            { data: 'indent'},
            { data: 'total'},
            { data: 'opening_stock'},
            { data: 'production'},
            { data: 'shift_time'},
            { data: 'shift_name'},
            { data: 'users'}
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
        width: "100%",
        theme: 'classic',
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

    $('#shiftid').select2({
        placeholder: 'Select Shift',
        allowClear: true,
        width: '100%',
        theme: 'classic',
        dropdownParent: $('#browser-file')
    });

    $('#uom_filter').select2({
        placeholder: 'Select UOM',
        allowClear: true,
        width: "100%",
        theme: 'classic',
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
        width: "100%",
        theme: 'classic',
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

    $('#category_filter').on('change', function() {
        $('#product_filter').val(null).trigger('change');
        $('#uom_filter').val(null).trigger('change');
    });

    $('#product_filter').on('change', function() {
        $('#uom_filter').val(null).trigger('change');
    });

    $('#apply_filters').on('click', function() {
        table.ajax.reload();
    });

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

    var table = $('#production-table').DataTable();
});
</script>
@endpush