<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title> Production Create </title>

    <link href="{!! url('assets/css/bootstrap.min.css') !!}" rel="stylesheet">
    <link href="{!! url('assets/css/my-style.css') !!}" rel="stylesheet">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="{{ asset('assets/css/jquery-ui.css') }}">

    <!-- code added by binal start--->
    <meta name="msapplication-TileColor" content="#da532c">
    <meta name="theme-color" content="#ffffff">
    <!-- code added by binal end--->

    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css" />
    <link rel="stylesheet" href="{{ asset('assets/css/select2.min.css') }}">
    <link href="{{ asset('assets/css/font-awesome.min.css') }}" rel="stylesheet" />

    <style type="text/css">
        .numberCircle {
            font-family: "OpenSans-Semibold", Arial, "Helvetica Neue", Helvetica, sans-serif;
            display: inline-block;
            color: #fff;
            text-align: center;
            line-height: 0px;
            border-radius: 50%;
            font-size: 12px;
            font-weight: 700;
            min-width: 38px;
            min-height: 38px;
        }

        .numberCircle span {
            display: inline-block;
            padding-top: 50%;
            padding-bottom: 50%;
            margin-left: 1px;
            margin-right: 1px;
        }

        /* Some Back Ground Colors */
        .clrTotal {
            background: #51a529;
        }

        .clrLike {
            background: #60a949;
        }

        .clrDislike {
            background: #bd3728;
        }

        .clrUnknown {
            background: #58aeee;
        }

        .clrStatusPause {
            color: #bd3728;
        }

        .clrStatusPlay {
            color: #60a949;
        }

        .LoaderSec {
            position: fixed;
            background: #465b97c7;
            width: 100%;
            height: 100%;
            left: 0;
            top: 0;
            z-index: 99999999999;
        }

        .LoaderSec .loader {
            width: 55px;
            height: 55px;
            border: 6px solid #fff;
            border-bottom-color: #5f0000;
            border-radius: 50%;
            display: inline-block;
            -webkit-animation: rotation 1s linear infinite;
            animation: rotation 1s linear infinite;
            position: fixed;
            z-index: 9999999999999;
            transform: translate(-50%, -50%);
            top: 50%;
            left: 50%;
        }

        .content-wrapper {
            margin-left: 0px !important;
        }

        @keyframes rotation {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        .select2-container--classic .select2-selection--single {
            height: 40px !important;
        }

        .select2-container--classic .select2-selection--single .select2-selection__arrow {
            height: 38px !important;
        }

        .select2-container--classic .select2-selection--single .select2-selection__rendered {
            line-height: 39px !important;
        }

        .prod-card {
            background: #fde8ec;
            border-radius: 10px;
            padding: 12px;
            margin: 10px 0px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, .08);
        }

        .prod-title {
            font-weight: 700;
            letter-spacing: .4px;
            font-size: 14px;
            text-transform: uppercase;
            display: flex;
            justify-content: space-between;
            border-bottom: 1px solid rgba(0, 0, 0, .1);
            padding-bottom: 6px;
            margin-bottom: 8px;
        }

        .uom-row {
            display: flex;
            justify-content: space-between;
            padding: 2px 0;
            font-size: 13px;
        }

        .category-title {
            font-weight: 700;
            font-size: 18px;
            margin: 18px 0 8px;
        }

        .cards-wrap {
            display: flex;
            flex-wrap: wrap;
        }

        .wastage-material {
            color: red;
        }

        .wastage-material {
            color: red;
        }

        .space-y-2 .p-4 {
            padding: 1rem !important;
        }

        .grid-cols-4 .p-5 {
            padding: 1.25rem !important
        }

        .collapse {
            visibility: visible !important;
        }
    </style>

</head>

<body>
    <div class="wrapper">

        <div class="LoaderSec d-none">
            <span class="loader"></span>
        </div>

        <div class="content-wrapper">
            @php
            $itemKey = 0;
            $pageTitle = 'Add Production';
            if (!empty($isDispatch) && $isDispatch) {
            $pageTitle = 'Add Dispatch';
            } elseif (!empty($isExpire) && $isExpire) {
            $pageTitle = 'Add Wastage';
            }
            @endphp


                <div class="container-fluid">
                <div class="p-2 mt-2 mb-2" style="text-transform:uppercase;text-align:center;font-size:30px;color:white;background-color:{{ $pageTitle == 'Add Production' ? '#1f8657' : '#aa162f' }}">
                    <span>{{ $pageTitle }}</span>
                </div>

                <form method="POST" action="{{ route('production.store') }}" id="productionCreationForm">
                    @csrf
                    @if(!empty($isDispatch) && $isDispatch)
                    <input type="hidden" name="dispatch" value="1">
                    @endif
                    @if(!empty($isExpire) && $isExpire)
                    <input type="hidden" name="expire" value="1">
                    @endif
                    @if(request()->has('is_web_view') && request()->is_web_view == 1)
                        <input type="hidden" name="is_web_view" value="1">
                    @endif                    

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label>Production Number</label>
                            <input type="text" class="form-control" name="production_number" value="{{ $productionNo }}" readonly>
                        </div>
                        <div class="col-md-4">
                            <label>Production Date</label>
                            <input type="datetime-local" class="form-control" name="production_date" value="{{ date('Y-m-d\\TH:i') }}">
                        </div>
                        <div class="col-md-4">
                            <label>Shift</label>
                            <select name="shift_id" id="shift_id" readonly>
                                @forelse($shifts as $shift)
                                @php
                                $start = \Carbon\Carbon::createFromFormat('H:i:s', $shift->start);
                                $end = \Carbon\Carbon::createFromFormat('H:i:s', $shift->end);
                                $current = \Carbon\Carbon::createFromFormat('H:i:s', $now);

                                $isInShift = $end->greaterThan($start)
                                ? $current->between($start, $end)
                                : $current->greaterThanOrEqualTo($start) || $current->lessThanOrEqualTo($end);
                                @endphp
                                <option value="{{ $shift->id }}" @if($isInShift) selected @endif> {{ $shift->title }} </option>
                                @empty
                                <option value="" disabled> No Shifts Available </option>
                                @endforelse
                            </select>
                        </div>
                    </div>

                    <hr>

                    <table class="table table-bordered" id="production-items-table">
                        <thead>
                            <tr>
                                <th width="15%">Employee</th>
                                <th width="15%">Product</th>
                                <th width="15%">Unit</th>
                                <th width="15%">Quantity</th>
                                <th width="15%">Action</th>
                            </tr>
                        </thead>
                        <tbody class="upsertable">
                            <tr class="hr_table_row">
                                <td>
                                    <div class="item-user-parent">
                                        <select name="user[0]" id="user_0" class="form-control user-select2 item-user" required></select>
                                    </div>
                                </td>
                                <td>
                                    <div class="item-product-parent">
                                        <select name="product[0]" id="product_0" class="form-control product-select2 item-product" required></select>
                                    </div>
                                </td>
                                <td>
                                    <div class="item-unit-parent">
                                        <select name="unit[0]" id="unit_0" class="form-control unit-select2 item-unit" required></select>
                                    </div>
                                </td>
                                <td>
                                    <input type="number" name="qty[0]" id="qty_0" class="form-control item-quantity" min="1" required>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-primary btn-sm addRow">+</button>
                                    <button type="button" class="btn btn-danger btn-sm removeRow">-</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="mb-3 row">
                        <center>
                            <button type="submit" style="width: 90px;" class="btn btn-primary"> Add </button>
                            <a href="{{ route('production.index') }}{{ request()->has('is_web_view') ? '?is_web_view=1' : '' }}{{ !empty($isDispatch) && $isDispatch ? '&dispatch=1' : '' }}" class="btn btn-secondary">Back</a>
                        </center>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="{{ asset('assets/js/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/js/jquery-ui.js') }}"></script>

    <script src="{{ url('assets/js/jquery-validate.min.js') }}"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('assets/js/select2.min.js') }}"></script>
    <script>
        let lastElementIndex = {{ $itemKey }};

        $(document).ready(function() {
            $('#productionCreationForm').validate({
                rules: {
                    production_number: {
                        required: true
                    }
                },
                messages: {
                    production_number: {
                        required: "Production number is required"
                    }
                },
                errorPlacement: function(error, element) {
                    error.appendTo(element.parent("div"));
                },
                submitHandler: function(form, event) {
                    event.preventDefault();
                    form.submit();
                }
            });

            function makeProductSelect(selector) {
                $(selector).select2({
                        placeholder: 'Select a product',
                        theme: 'classic',
                        width: '100%',
                        ajax: {
                            url: "{{ route('production.products-select2') }}",
                            type: "POST",
                            dataType: 'json',
                            delay: 250,
                            data: function(params) {
                                return {
                                    searchQuery: params.term,
                                    onlyactive: '1',
                                    page: params.page || 1,
                                    _token: "{{ csrf_token() }}"
                                };
                            },
                            processResults: function(data, params) {
                                params.page = params.page || 1;
                                return {
                                    results: $.map(data.items, function(item) {
                                        return {
                                            id: item.id,
                                            text: item.text,
                                            uoms: item.uoms
                                        };
                                    }),
                                    pagination: {
                                        more: data.pagination.more
                                    }
                                };
                            },
                            cache: true
                        }
                    })
                    .on('select2:select', function(e) {
                        var selected = e.params.data || {};
                        var uoms = selected.uoms || [];
                        var $tr = $(this).closest('tr');
                        var $unitSelect = $tr.find('.item-unit');
                        $unitSelect.empty();
                        if (uoms.length === 0) {
                            $unitSelect.append('<option value="">No units</option>');
                        } else {
                            $.each(uoms, function(_, u) {
                                var optionText = (u.name ? u.name : (u.code || '')) + (u.code && u.name ? ' (' + u.code + ')' : '');
                                $unitSelect.append('<option value="' + u.id + '">' + optionText + '</option>');
                            });
                        }
                        $unitSelect.trigger('change');
                    })
                    .on('select2:clear', function() {
                        var $tr = $(this).closest('tr');
                        var $unitSelect = $tr.find('.item-unit');
                        $unitSelect.empty().trigger('change');
                    });
            }

            $('#shift_id').select2({
                placeholder: 'Select Shift',
                width: '100%',
                theme: 'classic'
            });

            function makeUnitSelect(selector) {
                $(selector).select2({
                    placeholder: 'Select a unit',
                    theme: 'classic',
                    width: '100%'
                });
            }

            function makeUserSelect(selector) {
                $(selector).select2({
                    placeholder: 'Select employee',
                    theme: 'classic',
                    width: '100%',
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
                                ignoreDesignation: 1
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
                });
            }

            makeUserSelect('.item-user');
            makeProductSelect('.item-product');
            makeUnitSelect('.item-unit');

            $(document).on('click', '.addRow', function() {
                let cloned = $('.upsertable').find('tr').eq(0).clone();
                lastElementIndex++;

                cloned.find('.item-user-parent').empty().append(`<select class="user-select2 item-user" required></select>`);
                makeUserSelect(cloned.find('.item-user'));
                cloned.find('.item-user').attr('name', `user[${lastElementIndex}]`);
                cloned.find('.item-user').attr('id', `user_${lastElementIndex}`);
                $(`#user_${lastElementIndex}`).val(null).trigger('change');

                cloned.find('.item-product-parent').empty().append(`<select class="product-select2 item-product" required></select>`);
                makeProductSelect(cloned.find('.item-product'));
                cloned.find('.item-product').attr('name', `product[${lastElementIndex}]`);
                cloned.find('.item-product').attr('id', `product_${lastElementIndex}`);
                $(`#product_${lastElementIndex}`).val(null).trigger('change');

                cloned.find('.item-unit-parent').empty().append(`<select class="unit-select2 item-unit" required></select>`);
                makeUnitSelect(cloned.find('.item-unit'));
                cloned.find('.item-unit').attr('name', `unit[${lastElementIndex}]`);
                cloned.find('.item-unit').attr('id', `unit_${lastElementIndex}`);
                $(`#unit_${lastElementIndex}`).val(null).trigger('change');

                cloned.find('.item-quantity').attr('name', `qty[${lastElementIndex}]`).attr('id', `qty_${lastElementIndex}`).val(null);

                $('.upsertable').append(cloned.get(0));
            });

            $(document).on('click', '.removeRow', function() {
                if ($('.upsertable tr').length > 1) $(this).closest('tr').remove();
            });
        });
    </script>

</body>

</html>