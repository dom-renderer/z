@extends('layouts.app-master')

@push('css')
    <link href="{{ asset('assets/css/select2.min.css') }}" rel="stylesheet" />
@endpush

@section('content')
    <div class="bg-light p-4 rounded">
        <div class="container mt-4">
            <form method="POST" action="{{ route('product-categories.update', $id) }}" id="categoryForm" novalidate>
                @csrf
                @method('PUT')
                <div class="mb-3">
                    <label for="name" class="form-label">Name <span class="text-danger"> * </span> </label>
                    <input type="text" name="name" class="form-control" id="name" value="{{ old('name', $category->name) }}" placeholder="Enter name" required>
                </div>
                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea name="description" class="form-control" placeholder="Description" style="resize: vertical!important;">{{ old('description', $category->description) }}</textarea>
                </div>
                <button type="submit" class="btn btn-primary">Update</button>
                <a href="{{ route('product-categories.index') }}" class="btn btn-default">Back</a>
            </form>
        </div>
    </div>
@endsection

@push('js')
    <script src="{{ asset('assets/js/select2.min.js') }}"></script>
    <script>
        $(function(){
            $('#categoryForm').validate({
                rules: {
                    name: { required: true }
                },
                messages: {
                    name: { required: 'Please enter name' }
                },
                errorPlacement: function(error, element) {
                    error.appendTo(element.parent("div"));
                }
            });
        });
    </script>
@endpush


