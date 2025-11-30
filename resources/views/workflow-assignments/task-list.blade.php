@extends('layouts.app-master')

@push('css')
<link href="{{ asset('assets/css/select2.min.css') }}" rel="stylesheet" />
<link rel="stylesheet" href="{{ asset('assets/css/jquery.datetimepicker.css') }}">
<link rel="stylesheet" type="text/css" href="{{ asset('assets/css/twitter-bootstrap.min.css') }}"/>
<link rel="stylesheet" type="text/css" href="{{ asset('assets/css/datatable-bootstrap.css') }}"/>
@endpush

@section('content')

    <div class="bg-light p-4 rounded">
        <h1>{{ $page_title }} </h1>
        
        <div class="mt-2">
            @include('layouts.partials.messages')
        </div>

        <div class="row">
            <div class="col-2">
                <label class="col-form-label" for="asgmt-filter"> Assignment </label>
                <select id="asgmt-filter" multiple>
                </select>
            </div>

            <div class="col-2">
                <label class="col-form-label" for="section-filter"> Section </label>
                <select id="section-filter" multiple>
                </select>
            </div>

            <div class="col-2">
                <label class="col-form-label" for="checklist-filter"> Checklist </label>
                <select id="checklist-filter" multiple>
                </select>
            </div>

            <div class="col-2">
                <label class="col-form-label" for="user-filter"> Users </label>
                <select id="user-filter" multiple>
                </select>
            </div>

            <div class="col-2">
                <label class="col-form-label" for="fromdate-filter"> From Date </label>
                <input type="text" id="fromdate-filter" class="form-control" placeholder="Select Date" />
            </div>

            <div class="col-2">
                <label class="col-form-label" for="todate-filter"> To Date </label>
                <input type="text" id="todate-filter" class="form-control" placeholder="Select Date" />
            </div>

            <div class="col-2">
                <label class="col-form-label" for="status-filter"> Status </label>
                <select id="status-filter">
                    <option value=""></option>
                    <option value="0"> Pending </option>
                    <option value="1"> In-Progress </option>
                    <option value="2"> Completed </option>
                </select>
            </div>

            <div class="col-2">
                <button id="filter-data" class="btn btn-secondary" style="position: relative;top:34px;"> Search </button>
                <button id="filter-data-clear" class="btn btn-danger d-none" style="position: relative;top:34px;"> Clear </button>
            </div>
        </div>
        
        <div class="tab-content" id="myTabContent">
            <div class="tab-pane fade show active" id="users-tab-pane" role="tabpanel" aria-labelledby="users-tab" tabindex="0">
                <table class="table table-striped" id="workflow-assignment-table" cellspacing="0" width="100%">
                    <thead>
                    <tr>
                        <th>Code</th>
                        <th>Workflow Assignment</th>
                        <th>Section</th>
                        <th>Checklist</th>
                        <th>User</th>
                        <th width="5%">Completion Rate</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
        

    </div> 
@endsection


@push('js')
<script src="{{ asset('assets/js/jquery.datetimepicker.js') }}"></script>
<script src="{{ asset('assets/js/other/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('assets/js/other/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('assets/js/other/dataTables.bootstrap5.min.js') }}"></script>
<script src="{{ asset('assets/js/select2.min.js') }}"></script>
<script>
    
    $(document).ready(function($){

        $('#fromdate-filter').datetimepicker({
            format:'d-m-Y',
            timepicker: false,
        });

        $('#todate-filter').datetimepicker({
            format:'d-m-Y',
            timepicker: false,
        });

        $('#asgmt-filter').select2({
            placeholder: "Select a Assignment",
            allowClear: true,
            width: "100%",
            theme: 'classic',
            ajax: {
                url: "{{ route('workflow-assignments-list') }}",
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
                    params.page = params.page || 1;

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
                },
                cache: true
            },
            templateResult: function(data) {
                if (data.loading) {
                    return data.text;
                }

                var $result = $('<span></span>');
                $result.text(data.text);
                return $result;
            }
        });

        $('#section-filter').select2({
            placeholder: "Select a Section",
            allowClear: true,
            width: "100%",
            theme: 'classic',
            ajax: {
                url: "{{ route('sections-list') }}",
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
                    params.page = params.page || 1;

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
                },
                cache: true
            },
            templateResult: function(data) {
                if (data.loading) {
                    return data.text;
                }

                var $result = $('<span></span>');
                $result.text(data.text);
                return $result;
            }
        });

        $('#user-filter').select2({
            placeholder: "Select a User",
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
                        ignoreDesignation: 1,
                        roles: "{{ implode(',', [Helper::$roles['store-phone'], Helper::$roles['store-manager'], Helper::$roles['store-employee'], Helper::$roles['store-cashier'], Helper::$roles['divisional-operations-manager'], Helper::$roles['head-of-department'], Helper::$roles['operations-manager']]) }}"
                    };
                },
                processResults: function(data, params) {
                    params.page = params.page || 1;

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
                },
                cache: true
            },
            templateResult: function(data) {
                if (data.loading) {
                    return data.text;
                }

                var $result = $('<span></span>');
                $result.text(data.text);
                return $result;
            }
        });

        $('#checklist-filter').select2({
            placeholder: 'Select Checklist',
            allowClear: true,
            width: '100%',
            theme: 'classic',
            ajax: {
                url: "{{ route('checklists-list') }}",
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
                    params.page = params.page || 1;

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
                },
                cache: true
            },
            templateResult: function(data) {
                if (data.loading) {
                    return data.text;
                }

                var $result = $('<span></span>');
                $result.text(data.text);
                return $result;
            }
        });


        let usersTable = new DataTable('#workflow-assignment-table', {
            ajax: {
                url: "{{ route('workflow-assignments.tasks-list') }}",
                data: function ( d ) {
                    return $.extend( {}, d, {
                        user: $('#user-filter').val(),
                        checklist: $('#checklist-filter').val(),
                        from : $('#fromdate-filter').val(),
                        to : $('#todate-filter').val(),
                        status: $('#status-filter').val(),
                        asgmt: $('#asgmt-filter').val(),
                        section: $('#section-filter').val()
                    });
                }
            },
            processing: false,
            ordering: false,
            serverSide: true,
            columns: [
                 { data: 'code' },
                 { data: 'waname' },
                 { data: 'sname' },
                 { data: 'cname' },
                 { data: 'usr' },
                 { data: 'comprate' },
                 { data: 'date' },
                 { data: 'status' },
                 { data: 'action' }
            ],
            initComplete: function(settings) {

            }
        });

        $('#status-filter').select2({
            placeholder: 'Select status',
            allowClear: true,
            width: '100%',
            theme: 'classic'
        });

        $(document).on('click', '#filter-data', function () {
            usersTable.ajax.reload();

            let asgmtFilter = $('#asgmt-filter').val();
            let sectionFilter = $('#section-filter').val();
            let userFilter = $('#user-filter').val();
            let checklistFilter = $('#checklist-filter').val();
            let fromDate = $('#frequency-filter').val();
            let toDate = $('#frequency-filter').val();
            let status = $('#status-filter').val();

            if (anyIsset(userFilter, checklistFilter, fromDate, toDate, status, asgmtFilter, sectionFilter)) {
                $('#filter-data-clear').removeClass('d-none');
            } else {
                $('#filter-data-clear').addClass('d-none');
            }
        });

        $(document).on('click', '#filter-data-clear', function () {
            if (!$('#filter-data-clear').hasClass('d-none')) {
                $('#filter-data-clear').addClass('d-none');
            }

            $('#user-filter').val(null).trigger('change');
            $('#checklist-filter').val(null).trigger('change');
            $('#fromdate-filter').val(null).trigger('change');
            $('#todate-filter').val(null).trigger('change');
            $('#status-filter').val(null).trigger('change');
            $('#asgmt-filter').val(null).trigger('change');
            $('#section-filter').val(null).trigger('change');

            usersTable.ajax.reload();
        });
        
    });
 </script>  
@endpush