@extends('layouts.app-master')

@section('content')
    <div class="bg-light p-4 rounded">
        <h1>{{ $page_title }}</h1>
        <div class="mt-2">
            @include('layouts.partials.messages')
        </div>
        <div class="card mt-3">
            <div class="card-body">
                <div class="mb-2"><strong>Name:</strong> {{ $category->name }}</div>
                <div class="mb-2"><strong>Description:</strong> {{ $category->description }}</div>
            </div>
        </div>
        <a href="{{ route('product-categories.index') }}" class="btn btn-default mt-3">Back</a>
    </div>
@endsection


