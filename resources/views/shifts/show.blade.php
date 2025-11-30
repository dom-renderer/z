@extends('layouts.app-master')

@push('css')
@endpush

@section('content')
    <div class="bg-light p-4 rounded">
        <div class="container mt-4">
            <div class="mb-3">
                <label class="form-label">Title</label>
                <div>{{ $shift->title }}</div>
            </div>
            <div class="mb-3">
                <label class="form-label">Start</label>
                <div>{{ date('H:i A', strtotime($shift->start)) }}</div>
            </div>
            <div class="mb-3">
                <label class="form-label">End</label>
                <div>{{ date('H:i A', strtotime($shift->end)) }}</div>
            </div>
            <a href="{{ route('shifts.index') }}" class="btn btn-default">Back</a>
        </div>
    </div>
@endsection

@push('js')
@endpush
