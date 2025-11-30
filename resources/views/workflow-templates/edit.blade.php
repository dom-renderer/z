@extends('layouts.app-master')

@push('css')
<link rel="stylesheet" href="{{ asset('assets/css/select2.min.css') }}">
<link rel='stylesheet' href="{{ asset('assets/css/font-awesome.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/custom-select-style.css') }}">
@endpush

@section('content')
    <div class="bg-light p-4 rounded">

        <div class="row mt-4">

            <form method="POST" action="{{ route('workflow-templates.update', $id) }}" id="formBuilder"> @csrf @method('PUT')

                <div class="mb-3">
                    <label for="name"> Name </label>
                    <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $template->name) }}" required>
                    @if ($errors->has('name'))
                        <span class="text-danger text-left">{{ $errors->first('name') }}</span>
                    @endif
                </div>

                <div class="mb-3">
                    <label for="section"> Section </label>
                    <select name="section" id="section" required>
                        @if(isset($template->section->id))
                            <option value="{{ $template->section->id }}" selected> {{ $template->section->name }} </option>
                        @endif
                    </select>
                    @if ($errors->has('section'))
                        <span class="text-danger text-left">{{ $errors->first('section') }}</span>
                    @endif
                </div>

                <div class="mb-3">
                    <label for="status"> Status </label>
                    <select name="status" id="status" class="form-control" required>
                        <option value="1" @if($template->status) selected @endif > Active </option>
                        <option value="0" @if(!$template->status) selected @endif > InActive </option>
                    </select>
                    @if ($errors->has('status'))
                        <span class="text-danger text-left">{{ $errors->first('status') }}</span>
                    @endif
                </div>

                <div class="save-all-wrap">
                    <button type="submit" class="btn btn-primary">Save</button>
                    <a href="{{ route('workflow-templates.index') }}" class="btn btn-default">Back</a>
                </div>
            </form>
        </div>

    </div>

@endsection

@push('js')
    <script src="{{ url('assets/form-builder/form-builder.min.js') }}"></script>
    <script src="{{ asset('assets/js/jquery-validate.min.js') }}"></script>
    <script src="{{ asset('assets/js/select2.min.js') }}"></script>
    <script type="text/javascript">

        $(document).ready(function () {

            $('#section').select2({
                placeholder: 'Select Section',
                allowClear: true,
                width: '100%',
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

        });

    </script>
@endpush
