@extends('layouts.app-master')

@push('css')
<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css"/>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.5/css/dataTables.bootstrap5.min.css"/>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .select2-container--classic .select2-selection--single { height: 40px!important; }
    .select2-container--classic .select2-selection--single .select2-selection__arrow { height: 38px!important; }
    .select2-container--classic .select2-selection--single .select2-selection__rendered { line-height: 39px!important; }
    label.error { color: red; }
    td:nth-child(3), td:nth-child(4), td:nth-child(5),
    th:nth-child(3), th:nth-child(4), th:nth-child(5) {
      text-align: center!important;
    }
    .container {
        text-align: center;
    }

    .chart-container {
        position: relative;
        width: 300px;
        height: 300px;
    }

    .progress-message {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        font-size: 18px;
        font-weight: bold;
        color: #333;
    }
</style>
@endpush

@section('content')
<div class="bg-light p-4 rounded">
    <h1>{{ $page_title }}</h1>
    <div class="lead">
        {{ $page_description }}
    </div>
    <div class="mt-2">
        @include('layouts.partials.messages')
    </div>

    <div class="card mt-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <label>Category</label>
                    <select id="category_filter" class="form-control">
                        <option value="">All Categories</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label>Product</label>
                    <select id="product_filter" class="form-control">
                        <option value="">All Products</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label>UOM</label>
                    <select id="uom_filter" class="form-control">
                        <option value="">All UOMs</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label>&nbsp;</label><br>
                    <button type="button" id="apply_filters" class="btn btn-primary btn-sm">Apply Filters</button>
                    <button type="button" id="clear_filters" class="btn btn-secondary btn-sm">Clear Filters</button>
                </div>
            </div>
        </div>
    </div>

    <div class="chart-container">
      <canvas id="doughnutChart"></canvas>
      <div id="progressMessage" class="progress-message">
        Shift Progress: 70% completed
      </div>
    </div>

    <div class="tab-content" id="myTabContent">
        <div class="tab-pane fade show active" id="production-tab-pane" role="tabpanel" aria-labelledby="production-tab" tabindex="0">
            <table id="production-table" class="table table-striped">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>UoM</th>
                        <th>Production Required</th>
                        <th>Produced</th>
                        <th>Remaining to Produce</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>


@endsection

@push('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.5/js/dataTables.bootstrap5.min.js"></script>
<script src="{{ asset('assets/js/select2.min.js') }}"></script>
<script src="{{ asset('assets/js/jquery-validate.min.js') }}"></script>
<script src="{{ asset('assets/js/chart.js') }}"></script>

<script>
    let doughnutChartInstance;
$(document).ready(function() {

    $('#production-table').DataTable({
        "dom": '<"d-flex justify-content-between mb-2"<"production-table-filter-container">f>rt<"d-flex flex-column float-start mt-3"pi><"clear">',
        processing: false,
        serverSide: true,
        ordering: false,
        ajax: {
            url: "{{ route('production-statistics') }}",
            data: function ( d ) {
                return $.extend( {}, d, {
                    category_id: $('#category_filter').val(),
                    product_id: $('#product_filter').val(),
                    uom_id: $('#uom_filter').val()
                });
            },
            error: function(xhr, status, error) {
                $('#progressMessage').text("Error fetching shift data.");
            }
        },
        columns: [
            { data: 'product_stat', orderable: false, searchable: false },
            { data: 'unit_stat', orderable: false, searchable: false },
            { data: 'pr_stat'},
            { data: 'p_stat'},
            { data: 'rp_stat'}
        ],
        drawCallback: function (settings) {
            if (settings.json && settings.json.chart_data) {
                const totalProduction = settings.json.chart_data.totalProduction;
                const producedSoFar = settings.json.chart_data.producedSoFar;
                renderChart(totalProduction, producedSoFar);
            }
        }
    });

    $('#category_filter').select2({
        placeholder: 'Select Category',
        allowClear: true,
        theme: 'classic',
        ajax: {
            url: "{{ route('production.categories-select2') }}",
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
                return {
                    results: $.map(data.items, function(item) {
                        return { id: item.id, text: item.text };
                    }),
                    pagination: { more: data.pagination.more }
                };
            }
        }
    });

    $('#product_filter').select2({
        placeholder: 'Select Product',
        allowClear: true,
        theme: 'classic',
        ajax: {
            url: "{{ route('production.products-select2') }}",
            type: "POST",
            dataType: 'json',
            delay: 250,
            data: function(params) {
                return {
                    searchQuery: params.term,
                    category_id: $('#category_filter').val(),
                    page: params.page || 1,
                    _token: "{{ csrf_token() }}"
                };
            },
            processResults: function(data, params) {
                return {
                    results: $.map(data.items, function(item) {
                        return { id: item.id, text: item.text };
                    }),
                    pagination: { more: data.pagination.more }
                };
            }
        }
    });

    $('#uom_filter').select2({
        placeholder: 'Select UOM',
        allowClear: true,
        theme: 'classic',
        ajax: {
            url: "{{ route('production.uoms-select2') }}",
            type: "POST",
            dataType: 'json',
            delay: 250,
            data: function(params) {
                return {
                    searchQuery: params.term,
                    product_id: $('#product_filter').val(),
                    page: params.page || 1,
                    _token: "{{ csrf_token() }}"
                };
            },
            processResults: function(data, params) {
                return {
                    results: $.map(data.items, function(item) {
                        return { id: item.id, text: item.text };
                    }),
                    pagination: { more: data.pagination.more }
                };
            }
        }
    });

    $('#category_filter').on('change', function() {
        $('#product_filter').val(null).trigger('change');
        $('#uom_filter').val(null).trigger('change');
    });

    $('#product_filter').on('change', function() {
        $('#uom_filter').val(null).trigger('change');
    });

    $('#apply_filters').on('click', function() {
        table.ajax.reload();
    });

    $('#clear_filters').on('click', function() {
        $('#category_filter').val(null).trigger('change');
        $('#product_filter').val(null).trigger('change');
        $('#uom_filter').val(null).trigger('change');
        table.ajax.reload();
    });

    var table = $('#production-table').DataTable();

    function renderChart(totalProduction, producedSoFar) {
        const progressPercentage = (producedSoFar / totalProduction) * 100;

        const ctx = document.getElementById('doughnutChart').getContext('2d');

        if (doughnutChartInstance) {
            doughnutChartInstance.data.datasets[0].data = [
                producedSoFar,
                totalProduction - producedSoFar
            ];
            doughnutChartInstance.update();
        } else {
            doughnutChartInstance = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Produced', 'Remaining'],
                    datasets: [{
                        data: [producedSoFar, totalProduction - producedSoFar],
                        backgroundColor: ['#500a0aff', '#ddd'],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        tooltip: { enabled: false },
                        legend: { display: false }
                    },
                    cutout: '70%'
                }
            });
        }

        $('#progressMessage').text(`Shift Progress: ${isNaN(progressPercentage) ? 0 : Math.round(progressPercentage)}% completed`);
    }

});
</script>
@endpush