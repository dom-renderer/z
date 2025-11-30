@extends('layouts.app-master')

@section('content')
    <div class="bg-light p-4 rounded">
        <h1>{{ $page_title }}</h1>
        <div class="mt-2">
            @include('layouts.partials.messages')
        </div>
        <div class="card mt-3">
            <div class="card-body">
                <div class="mb-2"><strong>Category:</strong> {{ optional($product->category)->name }}</div>
                <div class="mb-2"><strong>Name:</strong> {{ $product->name }}</div>
                <div class="mb-2"><strong>SKU:</strong> {{ $product->sku }}</div>
                <div class="mb-2"><strong>UoM:</strong> {{ $product->uom }}</div>
                <div class="mb-2"><strong>Description:</strong> {{ $product->description }}</div>
            </div>
        </div>
        <a href="{{ route('products.index') }}" class="btn btn-default mt-3">Back</a>
    </div>
@endsection


