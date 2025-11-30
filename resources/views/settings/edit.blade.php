@extends('layouts.app-master')

@push('css')
    <link rel="stylesheet" href="{{ asset('assets/css/jquery.datetimepicker.css') }}">
    <link href="{{ asset('assets/css/select2.min.css') }}" rel="stylesheet" />
@endpush

@section('content')
<div class="container mt-4">
<form method="POST" action="{{ route('settings.update') }}">
    @csrf
    <!-- <div class="mb-3">
        <label for="ticket_watchers">Select Users (Max 5)</label>
        <select name="ticket_watchers[]" id="filterDom" class="form-control" multiple required>
            @if(!empty($setting->ticket_watchers))
                @foreach (\App\Models\User::whereIn('id', $setting->ticket_watchers)->get() as $user)
                <option value="{{ $user->id }}" selected> {{ $user->employee_id }} - {{ $user->name }} {{ $user->middle_name }} {{ $user->last_name }} </option>                
                @endforeach
            @endif
        </select>
    </div>

    <div class="mb-3">
        <label for="send_mail_at">Send Mail At</label>
        <input type="text" name="send_mail_at" id="send_mail_at" class="form-control" value="{{ isset($setting->send_mail_at) ? $setting->send_mail_at : '' }}" required />
    </div>

    <div class="form-check mb-3">
        <input class="form-check-input" type="checkbox" name="should_send_ticket_mail" id="should_send_ticket_mail"
               value="1" {{ isset($setting) && $setting->should_send_ticket_mail ? 'checked' : '' }}>
        <label class="form-check-label" for="should_send_ticket_mail">
            Enable Ticket Mail Sending
        </label>
    </div> -->

    <div class="form-check mb-3">
        <input class="form-check-input" type="checkbox" name="cims" id="cims"
               value="1" {{ isset($setting) && $setting->cims ? 'checked' : '' }}>
        <label class="form-check-label" for="cims">
            Can import order sheet more than once in a shift
        </label>
    </div>

    <button class="btn btn-primary">Save Settings</button>
</form>

</div>
@endsection

@push('js')
    <script src="{{ asset('assets/js/jquery.datetimepicker.js') }}"></script>
    <script src="{{ asset('assets/js/select2.min.js') }}"></script>

    <script>
        $('#filterDom').select2({
            placeholder: 'Select Users',
            maximumSelectionLength: 5,
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
                        roles: "{{ implode(',', [Helper::$roles['store-phone'], Helper::$roles['store-manager'],Helper::$roles['store-employee'], Helper::$roles['store-cashier'], Helper::$roles['divisional-operations-manager'], Helper::$roles['head-of-department'], Helper::$roles['operations-manager']]) }}"
                    };
                },
                processResults: function(data, params) {
                    return {
                        results: $.map(data.items, function(item) {
                            return { id: item.id, text: item.text };
                        }),
                        pagination: {
                            more: data.pagination.more
                        }
                    };
                }
            }
        });

        $('#send_mail_at').datetimepicker({
            datepicker: false,
            format: 'H:i',
            step: 15
        });
    </script>
@endpush
