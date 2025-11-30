@extends('layouts.app-master')

@push('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .select2-selection--single {
        height: 38px!important;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        top: 6px!important;
        right: 4px!important;
    }
    .select2-container--default .select2-selection--single {
        border: 1px solid #d7d4d4!important;
    }
    .select2-container--default .select2-selection--single .select2-selection__clear {
        position: relative!important;
        top: 6px!important;
        right: 1px!important;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 38px!important;
    }
</style>
@endpush

@section('content')
<div class="bg-light p-4 rounded">
    <div class="container mt-4">
        <form method="POST" action="{{ route('production.uoms.store') }}"> 
            @csrf

            <div class="mb-3">
                <label for="code" class="form-label">UOM Code</label>
                <input type="text" name="code" id="code" class="form-control" value="{{ old('code') }}" placeholder="Enter code" required>
                @if ($errors->has('code'))
                    <span class="text-danger">{{ $errors->first('code') }}</span>
                @endif
            </div>

            <div class="mb-3">
                <label for="name" class="form-label">UOM Name</label>
                <input type="text" name="name" id="name" class="form-control" value="{{ old('name') }}" placeholder="Enter name" required>
                @if ($errors->has('name'))
                    <span class="text-danger">{{ $errors->first('name') }}</span>
                @endif
            </div>

            <div class="mb-3">
                <label for="status" class="form-label">Status</label>
                <select name="status" class="form-control" id="status">
                    <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
                @if ($errors->has('status'))
                    <span class="text-danger">{{ $errors->first('status') }}</span>
                @endif
            </div>

            <button type="submit" class="btn btn-primary">Save</button>
            <a href="{{ route('production.uoms.index') }}" class="btn btn-secondary">Back</a>
        </form>
    </div>
</div>
@endsection

@push('js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    $('#status').select2({ minimumResultsForSearch: -1 });
});
</script>
@endpush
