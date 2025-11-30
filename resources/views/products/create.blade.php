@extends('layouts.app-master')

@push('css')
    <link href="{{ asset('assets/css/select2.min.css') }}" rel="stylesheet" />
@endpush

@section('content')
    <div class="bg-light p-4 rounded">
        <div class="container mt-4">
            <form method="POST" action="{{ route('products.store') }}" id="productForm" novalidate>
                @csrf
                <div class="mb-3">
                    <label for="category_id" class="form-label">Category <span class="text-danger"> * </span></label>
                    <select name="category_id" id="category_id" class="form-control" required></select>
                </div>
                <div class="mb-3">
                    <label for="name" class="form-label">Name <span class="text-danger"> * </span></label>
                    <input type="text" name="name" class="form-control" id="name" value="{{ old('name') }}" placeholder="Enter name" required>
                </div>
                <div class="mb-3">
                    <label for="sku" class="form-label">SKU <span class="text-danger"> * </span></label>
                    <input type="text" name="sku" class="form-control" id="sku" value="{{ old('sku') }}" placeholder="Enter SKU" required>
                </div>
                <div class="mb-3">
                    <label for="uom" class="form-label">UoM</label>
                    <select name="uom" id="uom" class="form-control"></select>
                </div>
                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea name="description" class="form-control" placeholder="Description" style="resize: vertical!important;">{{ old('description') }}</textarea>
                </div>
                <button type="submit" class="btn btn-primary">Save</button>
                <a href="{{ route('products.index') }}" class="btn btn-default">Back</a>
            </form>
        </div>
    </div>
@endsection

@push('js')
    <script src="{{ asset('assets/js/select2.min.js') }}"></script>
    <script>
        $(function(){
            $('#category_id').select2({
                placeholder: 'Select Category',
                allowClear: true,
                width: '100%',
                theme: 'classic',
                ajax: {
                    url: "{{ route('products-categories-select2') }}",
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
                                return { id: item.id, text: item.text };
                            }),
                            pagination: { more: data.pagination.more }
                        };
                    },
                    cache: true
                }
            });

            $('#uom').select2({
                placeholder: 'Select or type UoM',
                tags: true,
                allowClear: true,
                width: '100%',
                theme: 'classic',
                ajax: {
                    url: "{{ route('products-uom-suggest') }}",
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
                                return { id: item.id, text: item.text };
                            }),
                            pagination: { more: data.pagination.more }
                        };
                    },
                    cache: true
                }
            });

            $('#productForm').validate({
                rules: {
                    category_id: { required: true },
                    name: { required: true },
                    sku: { required: true }
                },
                messages: {
                    category_id: { required: 'Please select category' },
                    name: { required: 'Please enter name' },
                    sku: { required: 'Please enter SKU' }
                },
                ignore: [],
                errorPlacement: function(error, element) {
                    error.appendTo(element.parent("div"));
                }
            });
        });
    </script>
@endpush


