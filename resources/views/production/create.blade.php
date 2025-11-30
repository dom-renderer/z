@extends('layouts.app-master')

@push('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .select2-container--classic .select2-selection--single { height: 40px!important; }
    .select2-container--classic .select2-selection--single .select2-selection__arrow { height: 38px!important; }
    .select2-container--classic .select2-selection--single .select2-selection__rendered { line-height: 39px!important; }
    label.error { color: red; }
</style>
@endpush

@section('content')
@php
    $itemKey = 0;
    $pageTitle = 'Create Production';
    if (!empty($isDispatch) && $isDispatch) {
        $pageTitle = 'Create Dispatch';
    } elseif (!empty($isExpire) && $isExpire) {
        $pageTitle = 'Add Wastage';
    }
@endphp
<div class="container-fluid">
    <h2>{{ $pageTitle }}</h2>

    <form method="POST" action="{{ route('production.store') }}" id="productionCreationForm">
        @csrf
        @if(!empty($isDispatch) && $isDispatch)
            <input type="hidden" name="dispatch" value="1">
        @endif
        @if(!empty($isExpire) && $isExpire)
            <input type="hidden" name="expire" value="1">
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
                <select name="shift_id" id="shift_id" @if(!in_array(\App\Helpers\Helper::$roles['admin'], auth()->user()->roles->pluck('id')->toArray())) readonly @endif>
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
                <a href="{{ route('production.index') }}{{ !empty($isDispatch) && $isDispatch ? '?dispatch=1' : '' }}" class="btn btn-secondary">Back</a>
            </center>
        </div>
    </form>
</div>
@endsection

@push('js')
<script src="{{ asset('assets/js/jquery-validate.min.js') }}"></script>
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
        errorPlacement: function (error, element) {
            error.appendTo(element.parent("div"));
        },
        submitHandler: function (form, event) {
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
                    return { searchQuery: params.term, onlyactive: '1', page: params.page || 1, _token: "{{ csrf_token() }}" };
                },
                processResults: function(data, params) {
                    params.page = params.page || 1;
                    return {
                        results: $.map(data.items, function(item){ return {id: item.id, text: item.text, uoms: item.uoms}; }),
                        pagination: { more: data.pagination.more }
                    };
                },
                cache: true
            }
        })
        .on('select2:select', function (e) {
            var selected = e.params.data || {};
            var uoms = selected.uoms || [];
            var $tr = $(this).closest('tr');
            var $unitSelect = $tr.find('.item-unit');
            $unitSelect.empty();
            if (uoms.length === 0) {
                $unitSelect.append('<option value="">No units</option>');
            } else {
                $.each(uoms, function(_, u){
                    var optionText = (u.name ? u.name : (u.code || '')) + (u.code && u.name ? ' ('+u.code+')' : '');
                    $unitSelect.append('<option value="'+ u.id +'">'+ optionText +'</option>');
                });
            }
            $unitSelect.trigger('change');
        })
        .on('select2:clear', function(){
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
        $(selector).select2({ placeholder: 'Select a unit', theme: 'classic', width: '100%' });
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
@endpush