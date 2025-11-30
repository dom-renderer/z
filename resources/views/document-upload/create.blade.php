@extends('layouts.app-master')

@push('css')
<link href="{{ asset('assets/css/select2.min.css') }}" rel="stylesheet" />
<style>
    .select2-container .select2-search--inline .select2-search__field {
        height: 20px !important;
    }

    .select2-container--classic .select2-selection--single .select2-selection__arrow {
        height: 38px !important;
    }

    .select2-container--classic .select2-selection--single {
        height: 40px !important;
    }

    .select2-container--classic .select2-selection--single .select2-selection__clear {
        height: 37px !important;
    }

    .select2-container--classic .select2-selection--single .select2-selection__rendered {
        line-height: 39px !important;
    }    
    
    .select2-container {
        background: none;
        border: none;
    }
</style>
@endpush

@section('content')
    <div class="bg-light p-4 rounded">

        <div class="container mt-4">

            <form method="POST" action="{{ route('document-upload.store') }}" id="documentUploadForm" enctype="multipart/form-data">
                @csrf

                <div class="mb-3">
                    <label for="zp_document_file" class="form-label">Document File<span class="text-danger"> *</span></label>
                    <input type="file" name="zp_document_file" id="zp_document_file" class="form-control">

                    @if ( $errors->has( 'zp_document_file' ) )
                        <span class="text-danger text-left">{{ $errors->first( 'zp_document_file' ) }}</span>
                    @endif
                </div>

                <div class="mb-3">
                    <label for="zp_document" class="form-label">Document<span class="text-danger"> *</span></label>
                    <select name="zp_document" id="zp_document" class="form-control">
                        <option value=""></option>
                        @if( !empty($document_arr) )
                            @foreach ( $document_arr as $document_row )
                                <option value="{{ $document_row->id }}" {{ old('zp_document') == $document_row->id ? 'selected' : '' }}>{{ $document_row->name }}</option>
                            @endforeach
                        @endif
                    </select>

                    @if ( $errors->has( 'zp_document' ) )
                        <span class="text-danger text-left">{{ $errors->first( 'zp_document' ) }}</span>
                    @endif
                </div>

                <div class="mb-3">
                    <label class="form-label" for="zp_location_category">Location Category<span class="text-danger"> *</span></label>
                    <select name="zp_location_category" id="zp_location_category" class="form-control">
                        <option value=""></option>
                        @if( !empty($location_category_arr) )
                            @foreach ( $location_category_arr as $location_category_row )
                                <option value="{{ $location_category_row->id }}" {{ old('zp_location_category') == $location_category_row->id ? 'selected' : '' }}>{{ $location_category_row->name }}</option>
                            @endforeach
                        @endif
                    </select>

                    @if ( $errors->has( 'zp_location_category' ) )
                        <span class="text-danger text-left">{{ $errors->first( 'zp_location_category' ) }}</span>
                    @endif
                </div>

                <div class="mb-3">
                    <label class="form-label" for="zp_location">Location<span class="text-danger"> *</span></label>
                    <select name="zp_location" id="zp_location"></select>

                    @if ( $errors->has( 'zp_location' ) )
                        <span class="text-danger text-left">{{ $errors->first( 'zp_location' ) }}</span>
                    @endif
                </div>

                <div class="mb-3">
                    <label class="form-label" for="zp_expiry_date">Expiry Date<span class="text-danger"> *</span></label>
                    <input type="text" class="form-control zp_datepicker" placeholder="Select Expiry Date" name="zp_expiry_date" id="zp_expiry_date" autocomplete="off" value="{{ old( 'zp_expiry_date' ) }}">

                    @if ( $errors->has( 'zp_expiry_date' ) )
                        <span class="text-danger text-left">{{ $errors->first( 'zp_expiry_date' ) }}</span>
                    @endif
                </div>

                <div class="mb-3">
                    <label class="form-label" for="zp_issue_date">Issue Date<span class="text-danger"> *</span></label>
                    <input type="text" class="form-control zp_datepicker" placeholder="Select Issue Date" name="zp_issue_date" id="zp_issue_date" autocomplete="off" value="{{ old( 'zp_issue_date' ) }}">

                    @if ( $errors->has( 'zp_issue_date' ) )
                        <span class="text-danger text-left">{{ $errors->first( 'zp_issue_date' ) }}</span>
                    @endif
                </div>

                <div class="mb-3">
                    <label class="form-label" for="zp_remark">Remark<span class="text-danger"> *</span></label>
                    <textarea name="zp_remark" id="zp_remark" class="form-control" placeholder="Enter Remark">{{ old('zp_remark') }}</textarea>

                    @if ( $errors->has( 'zp_remark' ) )
                        <span class="text-danger text-left">{{ $errors->first( 'zp_remark' ) }}</span>
                    @endif
                </div>

                <div class="mb-3">
                    <label class="form-label" for="zp_users">Users</label>
                    <select name="zp_users[]" id="zp_users" multiple></select>

                    @if ( $errors->has( 'zp_users' ) )
                        <span class="text-danger text-left">{{ $errors->first( 'zp_users' ) }}</span>
                    @endif
                </div>

                <div class="mb-3">
                    <label class="form-label" for="zp_template">Template</label>
                    <select name="zp_template[]" id="zp_template" multiple>
                        @if( !empty($notification_template_arr) )
                            @foreach ( $notification_template_arr as $notification_template_row )
                                <option value="{{ $notification_template_row->id }}">{{ $notification_template_row->title }}</option>
                            @endforeach
                        @endif
                    </select>

                    @if ( $errors->has( 'zp_template' ) )
                        <span class="text-danger text-left">{{ $errors->first( 'zp_template' ) }}</span>
                    @endif
                </div>

                <button type="submit" class="btn btn-primary">Save</button>
                <a href="{{ route('document-upload.index') }}" class="btn btn-default">Back</a>
            </form>
        </div>

    </div>
@endsection

@push('js')
    <script src="{{ asset('assets/js/select2.min.js') }}"></script>
    <script type="text/javascript">
        $(document).ready(function() {
            $('#documentUploadForm').validate({
                rules: {
                    zp_document_file: { required: true },
                    zp_document: { required: true },
                    zp_location_category: { required: true },
                    zp_location: { required: true },
                    zp_expiry_date: { required: true },
                    zp_issue_date: { required: true },
                    zp_remark: { required: true, minlength: 5 }
                },
                errorPlacement: function(error, element) {
                    error.appendTo(element.parent("div"));
                }
            });
            $('.select2').select2({
                width: '100%',
                theme: 'classic',
            });
            $('#zp_document').select2({
                placeholder: 'Select document',
                allowClear: true,
                width: '100%',
                theme: 'classic',
            });
            $('#zp_location_category').select2({
                placeholder: 'Select location category',
                allowClear: true,
                width: '100%',
                theme: 'classic',
            });
            $('#zp_template').select2({
                placeholder: 'Select Notification Template',
                allowClear: true,
                width: '100%',
                theme: 'classic',
            });
            $('#zp_location').select2({
                placeholder: 'Select location',
                allowClear: true,
                width: '100%',
                theme: 'classic',
                ajax: {
                    url: "{{ route('stores-list') }}",
                    type: "POST",
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            searchQuery: params.term,
                            page: params.page || 1,  
                            _token: "{{ csrf_token() }}",
                            showCode: 1
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
            $('#zp_users').select2({
                placeholder: 'Select Users',
                allowClear: true,
                width: '100%',
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
            $('.zp_datepicker').datepicker({
                dateFormat: "yy-mm-dd",
                todayBtn: true,
                todayHighlight: true,
                orientation: "bottom auto",
                autoclose: true,
                startDate: '1d'
            });
        });
    </script>
@endpush