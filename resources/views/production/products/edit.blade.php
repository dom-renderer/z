@extends('layouts.app-master')

@push('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .select2-selection--single { height: 38px!important; }
    .select2-container--default .select2-selection--single .select2-selection__arrow { top: 6px!important; right: 4px!important; }
    .select2-container--default .select2-selection--single { border: 1px solid #d7d4d4!important; }
    .select2-container--default .select2-selection--single .select2-selection__clear { position: relative!important; top: 6px!important; right: 1px!important; }
    .select2-container--default .select2-selection--single .select2-selection__rendered { line-height: 38px!important; }
</style>
@endpush

@section('content')
<div class="bg-light p-4 rounded">
    <div class="container mt-4">
        <form method="POST" action="{{ route('production.products.update', $id) }}">
            @csrf
            @method('PUT')

            <input type="hidden" name="id" value="{{ $id }}">

            <div class="mb-3">
                <label for="name" class="form-label">Product Name</label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $product->name) }}" placeholder="Enter product name" required>
                @if ($errors->has('name'))
                    <span class="text-danger">{{ $errors->first('name') }}</span>
                @endif
            </div>

            <div class="mb-3">
                <label for="sku" class="form-label">SKU</label>
                <input type="text" name="sku" class="form-control" value="{{ old('sku', $product->sku) }}" placeholder="Enter SKU" required>
                @if ($errors->has('sku'))
                    <span class="text-danger">{{ $errors->first('sku') }}</span>
                @endif
            </div>

            <div class="mb-3">
                <label for="category_id" class="form-label">Category</label>
                <select name="category_id" class="form-control select2" required>
                    <option value="">Select Category</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" @if(old('category_id', $product->category_id) == $category->id) selected @endif>{{ $category->name }}</option>
                    @endforeach
                </select>
                @if ($errors->has('category_id'))
                    <span class="text-danger">{{ $errors->first('category_id') }}</span>
                @endif
            </div>

            <div class="mb-3">
                <label for="uom_ids" class="form-label">UOMs</label>
                <select name="uom_ids[]" class="form-control select2" required multiple>
                    @php($selectedUoms = $product->uoms->pluck('id')->toArray())
                    @foreach($uoms as $uom)
                        <option value="{{ $uom->id }}" @if(collect(old('uom_ids', $selectedUoms))->contains($uom->id)) selected @endif>{{ $uom->code }} - {{ $uom->name }}</option>
                    @endforeach
                </select>
                @if ($errors->has('uom_ids'))
                    <span class="text-danger">{{ $errors->first('uom_ids') }}</span>
                @endif
            </div>

            <div class="mb-3">
                <label for="status" class="form-label">Status</label>
                <select name="status" class="form-control">
                    <option value="active" @if(old('status', $product->status) == 'active') selected @endif>Active</option>
                    <option value="inactive" @if(old('status', $product->status) == 'inactive') selected @endif>Inactive</option>
                </select>
                @if ($errors->has('status'))
                    <span class="text-danger">{{ $errors->first('status') }}</span>
                @endif
            </div>

            <button type="submit" class="btn btn-primary">Update</button>
            <a href="{{ route('production.products.index') }}" class="btn btn-default">Back</a>
        </form>
    </div>
</div>
@endsection

@push('js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    $('.select2').select2({ width: '100%', minimumResultsForSearch: -1 });
});
</script>
@endpush
