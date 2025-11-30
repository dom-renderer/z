@extends('layouts.app-master')

@push('css')
    <link rel="stylesheet" href="{{ asset('assets/css/jquery.datetimepicker.css') }}">
    <link href="{{ asset('assets/css/select2.min.css') }}" rel="stylesheet" />
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/twitter-bootstrap.min.css') }}"/>
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/datatable-bootstrap.css') }}"/>
    <style>
        .section-d, .section-d2 {
            margin-bottom: 20px;
            padding: 20px;
            border-radius: 8px;
            background-color: #f8f9fa;
            box-shadow: 0 2px 4px #0000001a;
        }

        .section-d h2, .section-d2 h2 {
            color: #5f0000;
        }

        .select2-container--classic .select2-selection--single .select2-selection__arrow {
            height: 38px !important;
        }

        .select2-container--classic .select2-selection--single {
            height: 40px !important;
        }

        .select2-container--classic .select2-selection--single .select2-selection__clear {
            height: 37px !important;
        }

        .select2-container--classic .select2-selection--single .select2-selection__rendered {
            line-height: 39px !important;
        }

        .chartScroll {
            overflow: auto;
            scrollbar-width: thin;
            scrollbar-color: #5f0000b0 #f0f0f0;
        }

        .chartScroll::-webkit-scrollbar {
            width: 10px;
            height: 20px;
        }

        .chartScroll::-webkit-scrollbar-track {
            background: #f0f0f0;
            border-radius: 10px;
        }

        .chartScroll::-webkit-scrollbar-thumb {
            background: linear-gradient(180deg, #5f0000b0, #5f0000);
            border-radius: 10px;
            border: 2px solid #f0f0f0;
            transition: background 0.3s ease;
        }

        .chartScroll::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(180deg, #5f0000, #5f0000b0);
        }

        .section-d, .section-d2 {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            transition: all 0.6s ease;
            pointer-events: none;
        }

        .section-d2 {
            transform: translateX(100%);
            opacity: 0;
            z-index: 1;
        }

        .section-d.active {
            transform: translateX(0);
            opacity: 1;
            z-index: 2;
            pointer-events: auto;
        }

        .section-d.hide-left {
            transform: translateX(-100%);
            opacity: 0;
            z-index: 1;
        }

        .section-d2.active {
            transform: translateX(0);
            opacity: 1;
            z-index: 2;
            pointer-events: auto;
        }

        .section-d2.hide-right {
            transform: translateX(100%);
            opacity: 0;
            z-index: 1;
        }

        #row-for-section-ds {
            position: relative;
        }
    </style>
@endpush

@section('content')
    <div class="row">



        <div class="">
            <div class="section">
                <h2>Filters</h2>
                <div class="row mb-3">


                    <div class="col-md-2">
                        <label for="filterStart" class="form-label">Start Date</label>
                        <input type="text" id="filterStart" class="form-control" value="{{ \Carbon\Carbon::now()->startOfMonth()->format('d-m-Y') }}">
                    </div>
                    
                    <div class="col-md-2">
                        <label for="filterEnd" class="form-label">End Date</label>
                        <input type="text" id="filterEnd" class="form-control" value="{{ date('d-m-Y') }}">
                    </div>

                    <div class="col-md-2">
                        <label for="filterDom" class="form-label">DoM</label>
                        <select id="filterDom" class="form-select">
                            <option value="all" selected> All </option>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label for="filterChecklist" class="form-label">Checklist</label>
                        <select id="filterChecklist" class="form-select">
                            <option value="all" selected> All </option>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label for="filterOps" class="form-label">Ops. Manager</label>
                        <select id="filterOps" class="form-select">
                            <option value="all" selected> All </option>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label for="filterState" class="form-label">State</label>
                        <select id="filterState">
                            <option value="all" selected> All </option>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label for="filterCity" class="form-label">City</label>
                        <select id="filterCity">
                            <option value="all" selected> All </option>
                        </select>
                    </div>      

                    <div class="col-md-2">
                        <label for="filterLoc" class="form-label">Location</label>
                        <select id="filterLoc">
                            <option value="all" selected> All </option>
                            @forelse(\App\Models\Store::select('id', 'code', 'name')->when(!auth()->user()->isAdmin(), function ($builder) {
                                $builder->where('dom_id', auth()->user()->id);
                            })->get() as $store)
                                <option value="{{ $store->id }}"> {{ $store->code }} {{ $store->name }} </option>
                            @empty
                            @endforelse
                        </select>
                    </div>
                    
                    <div class="col-md-2">
                        <label for="filterLtype" class="form-label">Location Type</label>
                        <select id="filterLtype">
                            <option value="all" selected> All </option>
                            @forelse(\App\Models\StoreType::get() as $lType)
                                <option value="{{ $lType->id }}"> {{ $lType->name }} </option>
                            @empty
                            @endforelse
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label for="filterLmodel" class="form-label">Location Model</label>
                        <select id="filterLmodel">
                            <option value="all" selected> All </option>
                            @forelse(\App\Models\ModelType::get() as $lType)
                                <option value="{{ $lType->id }}"> {{ $lType->name }} </option>
                            @empty
                            @endforelse
                        </select>
                    </div>


                </div>
            </div>


            <div id="row-for-section-ds">
                <div class="section-d active" id="section1">
                    <h2>Inspection Statistics</h2>
                    <div style="overflow-x: auto;width:100%;" class="chartScroll">
                        <canvas id="complianceChart" width="6000" height="700"></canvas>
                    </div>
                </div>

                <div class="section-d2" id="section2">
                    <h2>
                        <button id="backToSection1" class="btn btn-sm btn-outline-secondary">⬅ Back</button>
                        <span id="n-store-title"> Store </span> Statistics
                    </h2>
                    <canvas id="complianceChart2"></canvas>
                </div>
            </div>

        
        </div>



    </div>




@endsection

@push('js')
    <script src="{{ asset('assets/js/jquery.datetimepicker.js') }}"></script>
    <script src="{{ asset('assets/js/select2.min.js') }}"></script>
    <script src="{{ url('assets/js/chart.js') }}"></script>
    <script src="{{ asset('assets/js/other/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/js/other/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/js/other/dataTables.bootstrap5.min.js') }}"></script>
    <script>
        $(document).ready(function() {

        $("#backToSection1").on("click", function () {
            $("#section2").removeClass("active").addClass("hide-right");
            $("#section1").removeClass("hide-left").addClass("active");
        });

            let chartStoreIds = [0, 0, 0];
            let nStoreId = 0;

            const ctx = document.getElementById('complianceChart').getContext('2d');
            const complianceChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Location A', 'Location B', 'Location C'],
                    datasets: [
                        {
                            label: 'Score',
                            data: [1, 2, 3],
                            backgroundColor: ['#dd2d20', '#dd2d20', '#dd2d20']
                        }
                    ]
                },
                options: {
                    responsive: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        x: {
                            ticks: {
                                autoSkip: false
                            },
                            grid: {
                                display: false
                            }
                        },
                       y: {
                            beginAtZero: true
                        }
                    },
                    onClick: function(evt, elements) {
                        if (elements.length > 0) {
                            const index = elements[0].index;
                            const storeId = chartStoreIds[index];
                            nStoreId = storeId;

                            loadSingleStoreStatistics()
                        }
                    }
                }
            });

            const ctx2 = document.getElementById('complianceChart2').getContext('2d');
            const complianceChart2 = new Chart(ctx2, {
                type: 'bar',
                data: {
                    labels: [],
                    datasets: [
                        {
                            label: 'Score',
                            data: [],
                            backgroundColor: []
                        }
                    ]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    onClick: function(evt, elements) {
                        if (elements.length > 0) {
                            const index = elements[0].index;
                            const storeId = chartStoreIds[index];
                            console.log("Clicked Store ID:", storeId);
                        }
                    }
                }
            });            

            function getResult() {
                let start = $('#filterStart').val();
                let end = $('#filterEnd').val();
                let dom = $('#filterDom').val();
                let clist = $('#filterChecklist').val();
                let ops = $('#filterOps').val();
                let ltype = $('#filterLtype').val();
                let loc = $('#filterLoc').val();
                let lmodel = $('#filterLmodel').val();
                let state = $('#filterState').val();
                let city = $('#filterCity').val();

                $.ajax({
                    url: "{{ route('dom-dashboard') }}",
                    type: 'GET',
                    data: {
                        start: start,
                        end: end,
                        clist: clist,
                        dom: dom,
                        ops: ops,
                        state: state,
                        city: city,
                        loc: loc,
                        ltype: ltype,
                        lmodel: lmodel
                    },
                    beforeSend: function() {
                        $('body').find('.LoaderSec').removeClass('d-none');
                    },
                    success: function(response) {

                        const tempObj = [];

                        for (i = 0; i < response.data.bar_chart_data.length; i++) {
                            tempObj.push({
                                'data': response.data.bar_chart_data[i], 
                                'label' : response.data.bar_chart_label_bar[i], 
                                'backgroundColor' : response.data.bar_chart_label_bar_color[i],
                                'barPercentage': 0.3,
                                'categoryPercentage' : 2.7
                            });
                        }
                        
                        chartStoreIds = response.data.bar_chart_store_ids;

                        complianceChart.data.datasets = tempObj;
                        complianceChart.data.labels = response.data.bar_chart_label;
                        complianceChart.update();

                        loadSingleStoreStatistics();
                    },
                    complete: function(response) {
                        $('body').find('.LoaderSec').addClass('d-none');
                    }
                });
            }

            function loadSingleStoreStatistics() {
                storeId = nStoreId;
                if (!isNaN(storeId) && storeId > 0) {

                let start = $('#filterStart').val();
                let end = $('#filterEnd').val();
                let clist = $('#filterChecklist').val();
                storeId = storeId;

                    $.ajax({
                        url: "{{ route('dom-dashboard-2-specific-store') }}",
                        type: 'GET',
                        data: {
                            store: storeId,
                            start: start,
                            end: end,
                            clist: clist
                        },
                        beforeSend: function() {
                            $('body').find('.LoaderSec').removeClass('d-none');
                        },
                        success: function(response) {

                            $('#n-store-title').text(response.store_name);

                            complianceChart2.data.datasets = [{
                                label: 'Score',
                                data: response.data,
                                backgroundColor: response.color
                            }];
                            
                            complianceChart2.data.labels = response.labels;
                            complianceChart2.update();


                        },
                        complete: function(response) {
                            $('body').find('.LoaderSec').addClass('d-none');

                            $("#section1").removeClass("active").addClass("hide-left");
                            $("#section2").removeClass("hide-right").addClass("active");                            
                        }
                    });                    
                }
            }

            getResult();

            $('#filterStart').datetimepicker({
                format: 'd-m-Y',
                timepicker: false,
                onChangeDateTime: function() {
                    getResult();
                }
            });

            $('#filterEnd').datetimepicker({
                format: 'd-m-Y',
                timepicker: false,
                onChangeDateTime: function() {
                    getResult();
                }
            });

            $('#filterState').select2({
                placeholder: 'Select State',
                width: '100%',
                theme: 'classic',
                ajax: {
                    url: "{{ route('state-list') }}",
                    type: "POST",
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            searchQuery: params.term,
                            page: params.page || 1,
                            _token: "{{ csrf_token() }}",
                            getall: true
                        };
                    },
                    processResults: function(data, params) {
                        params.page = params.page || 1;

                        return {
                            results: $.map(data.items, function(item) {
                                return {
                                    id: item.id,
                                    text: item.text
                                };
                            }),
                            pagination: {
                                more: data.pagination.more
                            }
                        };
                    },
                    cache: true
                },
                templateResult: function(data) {
                    if (data.loading) {
                        return data.text;
                    }

                    var $result = $('<span></span>');
                    $result.text(data.text);
                    return $result;
                }
            }).on('change', function() {

                if ($("#filterCity option[value='all']").length === 0) {
                    $('#filterCity').append('<option value="all">All</option>'); 
                }

                $('#filterCity').val('all').trigger('change');
                
                getResult();
            });

            $('#filterCity').select2({
                placeholder: 'Select City',
                allowClear: true,
                width: '100%',
                theme: 'classic',
                ajax: {
                    url: "{{ route('city-list') }}",
                    type: "POST",
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            searchQuery: params.term,
                            page: params.page || 1,
                            _token: "{{ csrf_token() }}",
                            state: function() {
                                return $('#filterState').val();
                            },
                            getall: true
                        };
                    },
                    processResults: function(data, params) {
                        params.page = params.page || 1;

                        return {
                            results: $.map(data.items, function(item) {
                                return {
                                    id: item.id,
                                    text: item.text
                                };
                            }),
                            pagination: {
                                more: data.pagination.more
                            }
                        };
                    },
                    cache: true
                },
                templateResult: function(data) {
                    if (data.loading) {
                        return data.text;
                    }

                    var $result = $('<span></span>');
                    $result.text(data.text);
                    return $result;
                }
            }).on('change', function() {
                getResult();
            });

            $('#filterDom').select2({
                placeholder: 'Select DOM',
                allowClear: true,
                width: '100%',
                theme: 'classic',
                ajax: {
                    url: "{{ route('users-list') }}",
                    type: "POST",
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            searchQuery: params.term,
                            page: params.page || 1,
                            _token: "{{ csrf_token() }}",
                            ignoreDesignation: 1,
                            getall: true
                        };
                    },
                    processResults: function(data, params) {
                        params.page = params.page || 1;

                        return {
                            results: $.map(data.items, function(item) {
                                return {
                                    id: item.id,
                                    text: item.text
                                };
                            }),
                            pagination: {
                                more: data.pagination.more
                            }
                        };
                    },
                    cache: true
                },
                templateResult: function(data) {
                    if (data.loading) {
                        return data.text;
                    }

                    var $result = $('<span></span>');
                    $result.text(data.text);
                    return $result;
                }
            }).on('change', function() {
                getResult();
            });

            $('#filterChecklist').select2({
                placeholder: 'Select Checklist',
                allowClear: true,
                width: '100%',
                theme: 'classic',
                ajax: {
                    url: "{{ route('checklists-list') }}",
                    type: "POST",
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            searchQuery: params.term,
                            page: params.page || 1,  
                            type: 1,
                            getall: 1,
                            _token: "{{ csrf_token() }}"
                        };
                    },
                    processResults: function(data, params) {
                        params.page = params.page || 1;

                        return {
                            results: $.map(data.items, function(item) {
                                return {
                                    id: item.id,
                                    text: item.text
                                };
                            }),
                            pagination: {
                                more: data.pagination.more
                            }
                        };
                    },
                    cache: true
                },
                templateResult: function(data) {
                    if (data.loading) {
                        return data.text;
                    }

                    var $result = $('<span></span>');
                    $result.text(data.text);
                    return $result;
                }
            }).on('change', function() {
                getResult();
            });

            $('#filterOps').select2({
                placeholder: 'Select OPS Manager',
                allowClear: true,
                width: '100%',
                theme: 'classic',
                ajax: {
                    url: "{{ route('users-list') }}",
                    type: "POST",
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            searchQuery: params.term,
                            page: params.page || 1,
                            _token: "{{ csrf_token() }}",
                            ignoreDesignation: 1,
                            getall: true
                        };
                    },
                    processResults: function(data, params) {
                        params.page = params.page || 1;

                        return {
                            results: $.map(data.items, function(item) {
                                return {
                                    id: item.id,
                                    text: item.text
                                };
                            }),
                            pagination: {
                                more: data.pagination.more
                            }
                        };
                    },
                    cache: true
                },
                templateResult: function(data) {
                    if (data.loading) {
                        return data.text;
                    }

                    var $result = $('<span></span>');
                    $result.text(data.text);
                    return $result;
                }
            }).on('change', function() {
                getResult();
            });

            $('#filterLoc').select2({
                placeholder: 'Select Location',
                allowClear: true,
                width: '100%',
                theme: 'classic'
            }).on('change', function() {
                getResult();
            });

            $('#filterLtype').select2({
                placeholder: 'Select Location Type',
                width: '100%',
                theme: 'classic'
            }).on('change', function() {
                getResult();
            });

            $('#filterLmodel').select2({
                placeholder: 'Select Location Model',
                width: '100%',
                theme: 'classic'
            }).on('change', function() {
                getResult();
            });
            
            $('#nStoreStatusFilter').select2({
                placeholder: 'Select Status',
                width: '100%',
                theme: 'classic'
            }).on('change', function() {
                loadSingleStoreStatistics();
            });

            $('#nStoreStartDateFilter').datetimepicker({
                format: 'd-m-Y',
                timepicker: false,
                onChangeDateTime: function() {
                    loadSingleStoreStatistics();
                }
            });

            $('#nStoreEndDateFilter').datetimepicker({
                format: 'd-m-Y',
                timepicker: false,
                onChangeDateTime: function() {
                    loadSingleStoreStatistics();
                }
            });            

        });
    </script>
@endpush
