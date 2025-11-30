@extends('layouts.app-master')


@section('content')
    <div class="bg-light p-4 rounded">
        <h2 class="mb-4">{{ $page_title }}</h2>

        <div class="mx-w-700 mx-auto">
            <div class="card mb-4">
                <div class="card-body">

                    <div class="row">
                        <div class="col-sm-3">
                            <p class="mb-0">Name</p>
                        </div>
                        <div class="col-sm-9">
                            <p class="text-muted mb-0"> 
                                {{ $product->name }}
                            </p>
                        </div>
                    </div><hr>

                    <div class="row">
                        <div class="col-sm-3">
                            <p class="mb-0">SKU</p>
                        </div>
                        <div class="col-sm-9">
                            <p class="text-muted mb-0"> 
                                {{ $product->sku }}
                            </p>
                        </div>
                    </div><hr>

                    <div class="row">
                        <div class="col-sm-3">
                            <p class="mb-0">Category</p>
                        </div>
                        <div class="col-sm-9">
                            <p class="text-muted mb-0"> 
                                {{ $product->category->name ?? '-' }}
                            </p>
                        </div>
                    </div><hr>

                    <div class="row">
                        <div class="col-sm-3">
                            <p class="mb-0">Units of Measure</p>
                        </div>
                        <div class="col-sm-9">
                            <p class="text-muted mb-0"> 
                                @if($product->uoms->count() > 0)
                                    @foreach($product->uoms as $uom)
                                        {{ $uom->code }} ({{ $uom->name }}){{ !$loop->last ? ', ' : '' }}
                                    @endforeach
                                @else
                                    -
                                @endif
                            </p>
                        </div>
                    </div><hr>

                    @if($product->description)
                    <div class="row">
                        <div class="col-sm-3">
                            <p class="mb-0">Description</p>
                        </div>
                        <div class="col-sm-9">
                            <p class="text-muted mb-0"> 
                                {{ $product->description }}
                            </p>
                        </div>
                    </div><hr>
                    @endif

                    <div class="row">
                        <div class="col-sm-3">
                            <p class="mb-0">Status</p>
                        </div>
                        <div class="col-sm-9">
                            <p class="text-muted mb-0"> 
                                <span class="badge {{ $product->status == 'active' ? 'bg-success' : 'bg-danger' }}">
                                    {{ ucfirst($product->status) }}
                                </span>
                            </p>
                        </div>
                    </div><hr>

                    <div class="row">
                        <div class="col-sm-3">
                            <p class="mb-0">Created At</p>
                        </div>
                        <div class="col-sm-9">
                            <p class="text-muted mb-0"> 
                                {{ $product->created_at->format('M d, Y H:i A') }}
                            </p>
                        </div>
                    </div><hr>

                    <div class="row">
                        <div class="col-sm-3">
                            <p class="mb-0">Last Updated</p>
                        </div>
                        <div class="col-sm-9">
                            <p class="text-muted mb-0"> 
                                {{ $product->updated_at->format('M d, Y H:i A') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            @if($product->uoms->count() > 0)
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Units of Measure Details</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Code</th>
                                    <th>Name</th>
                                    <th>Description</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($product->uoms as $uom)
                                <tr>
                                    <td>{{ $uom->code }}</td>
                                    <td>{{ $uom->name }}</td>
                                    <td>{{ $uom->description ?? '-' }}</td>
                                    <td>
                                        <span class="badge {{ $uom->status == 'active' ? 'bg-success' : 'bg-danger' }}">
                                            {{ ucfirst($uom->status) }}
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif

        </div>

        <a href="{{ route('production.products.index') }}" class="btn btn-primary"> Back </a>
    </div>
@endsection
