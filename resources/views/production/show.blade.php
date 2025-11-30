@extends('layouts.app-master')

@section('content')
@php
    use App\Helpers\Helper;
@endphp
<div class="bg-light p-4 rounded">
    <h1>{{ $page_title }}</h1>
    <div class="lead mb-3">
        <a href="{{ route('production.index') }}{{ !empty($isDispatch) && $isDispatch ? '?dispatch=1' : '' }}" class="btn btn-secondary btn-sm">Back</a>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <div class="row">
                <div class="col-md-4"><strong>Production Number:</strong> {{ $production->production_number }}</div>
                <div class="col-md-4"><strong>Status:</strong> 
                    <span class="badge bg-{{ Helper::$productionStatusColors[$production->status] ?? 'secondary' }}">
                        {{ Helper::$productionStatuses[$production->status] ?? ucfirst($production->status) }}
                    </span>
                </div>
                <div class="col-md-4"><strong>Total Items:</strong> {{ $production->items->count() }}</div>
            </div>
            <div class="row mt-2">
                <div class="col-md-4"><strong>Production Date:</strong> {{ !empty($production->production_date) ? date('d-m-Y H:i', strtotime($production->production_date)) : '-' }}</div>
                <div class="col-md-4"><strong>Created At:</strong> {{ $production->created_at->format('d-m-Y H:i') }}</div>
                <div class="col-md-4"><strong>Shift:</strong> {{ $production->shift->title ?? '' }}</div>
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header"><strong>Production Items</strong></div>
        <div class="card-body p-0">
            <table class="table table-striped mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Employee</th>
                        <th>Product</th>
                        <th>Unit</th>
                        <th>Quantity</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($production->items as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $item->user->name ?? '-' }}</td>
                        <td>{{ $item->product->name ?? '-' }}</td>
                        <td>{{ $item->unit->name ?? '-' }}</td>
                        <td>{{ $item->quantity }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="4"> <strong> Total </strong> </td>
                        <td> <strong> {{ number_format($production->items->sum('quantity') ?? 0, 2) }} </strong> </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><strong>Production Logs</strong></div>
        <div class="card-body p-0">
            <table class="table table-sm mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Comment</th>
                        <th>At</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $index => $log)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{!! $log->comment !!}</td>
                        <td>{{ $log->created_at->format('d-m-Y H:i') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="text-center">No logs available</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
