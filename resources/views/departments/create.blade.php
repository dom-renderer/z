@extends('layouts.app-master')

@push('css')
    <link href="{{ asset('assets/css/select2.min.css') }}" rel="stylesheet" />
@endpush

@section('content')
    <div class="bg-light p-4 rounded">

        <div class="container mt-4">

            <form method="POST" action="{{ route('departments.store') }}">
                @csrf

                <div class="mb-3">
                    <label for="name" class="form-label">Name <span class="text-danger"> * </span> </label>
                    <input type="text" name="name" class="form-control" id="name" value="{{ old('name') }}" placeholder="Enter name">
                </div>
                
                <div class="mb-3">
                    <label for="description" class="form-label">Description <span class="text-danger"> * </span> </label>
                    <textarea name="description" class="form-control" placeholder="Description" style="resize: vertical!important;" required>{{ old('description') }}</textarea>
                </div>

                <div class="mb-3">
                    <label for="users" class="form-label"> Users <span class="text-danger"> * </span> </label>
                    <select name="users[]" id="users" multiple>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary">Save</button>
                <a href="{{ route('departments.index') }}" class="btn btn-default">Back</a>
            </form>
        </div>

    </div>
@endsection

@push('js')
    <script src="{{ asset('assets/js/select2.min.js') }}"></script>
    <script type="text/javascript">
        $(document).ready(function() {

            $('#users').select2({
                placeholder: 'Select Employee',
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

        });
    </script>
@endpush