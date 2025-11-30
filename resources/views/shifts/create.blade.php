@extends('layouts.app-master')

@push('css')
    <link href="{{ asset('assets/css/clockpicker.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/css/standalone.css') }}" rel="stylesheet" />
@endpush

@section('content')
    <div class="bg-light p-4 rounded">

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif    

        <div class="container mt-4">

            <form method="POST" action="{{ route('shifts.store') }}">
                @csrf

                <div class="mb-3">
                    <label for="title" class="form-label">Title <span class="text-danger"> * </span> </label>
                    <input type="text" name="title" class="form-control" id="title" value="{{ old('title') }}" placeholder="Enter title">
                </div>
                
                <div class="mb-3">
                    <label for="start" class="form-label">Start <span class="text-danger"> * </span> </label>
                    <input type="text" name="start" class="clockpicker form-control" id="start" value="{{ old('start') }}" readonly>
                </div>

                <div class="mb-3">
                    <label for="end" class="form-label">End <span class="text-danger"> * </span> </label>
                    <input type="text" name="end" class="clockpicker form-control" id="end" value="{{ old('end') }}" readonly>
                </div>

                <button type="submit" class="btn btn-primary">Save</button>
                <a href="{{ route('shifts.index') }}" class="btn btn-default">Back</a>
            </form>
        </div>

    </div>
@endsection

@push('js')
    <script src="{{ asset('assets/js/jquery.datetimepicker.js') }}"></script>
    <script src="{{ asset('assets/js/clockpicker.js') }}"></script>
    <script type="text/javascript">
        $(document).ready(function() {

            $('.clockpicker').clockpicker({
                placement: 'top',
                align: 'left',
                donetext: 'Done'
            });
        });
    </script>
@endpush
