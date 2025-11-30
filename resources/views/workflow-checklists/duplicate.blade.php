@extends('layouts.app-master')

@push('css')
<link href="{{ asset('assets/css/select2.min.css') }}" rel="stylesheet" />
@endpush

@section('content')
    <div class="bg-light p-4 rounded">

        <div class="row mt-4">

            <form method="POST" action="{{ route('duplicate-workflow-checklist', $id) }}" id="form-builder-pages">
                @csrf @method('PUT')
                <input type="hidden" id="json" name="form_schema" class="form-control"
                    value="{{ json_encode($form->schema) }}" />

                    <div class="mb-3">
                        <label for="store" class="form-label">Location </label>
                        <select name="store" id="store"></select>
                    </div>

                <div class="mb-3">
                    <label for="name" class="form-label">Name <span class="text-danger"> * </span> </label>
                    <input type="text" name="name" class="form-control" id="name"
                        value="{{ old('name', $form->name) }}" placeholder="Enter name" required>
                </div>

                <div class="mb-3" style="background: #b1b1b1df;padding: 10px;">

                    <ul id="tabs">
                        @foreach ($form->schema as $page)
                        <li><a href="#page-{{ $loop->iteration }}">Page {{ $loop->iteration }}</a></li>
                        @endforeach

                        <li id="add-page-tab"><a href="#new-page">+ Page</a></li>
                    </ul>
                    @foreach ($form->schema as $page)
                    <div id="page-{{ $loop->iteration }}" class="fb-editor"></div>
                    @endforeach
                    <div id="new-page"></div>

                </div>

                <div class="save-all-wrap">
                    <button id="save-all" type="button" class="btn btn-primary">Save</button>
                    <a href="{{ route('workflow-checklists.index') }}" class="btn btn-default">Back</a>
                </div>
            </form>
        </div>

    </div>
@endsection

@push('js')
    <script src="{{ url('assets/form-builder/form-builder.min.js') }}"></script>
    <script src="{{ asset('assets/js/select2.min.js') }}"></script>
    <script type="text/javascript">
        jQuery(($) => {
            "use strict";



            $('#store').select2({
        placeholder: 'Select location',
        allowClear: true,
        width: '100%',
        theme: 'classic',
        ajax: {
            url: "{{ route('stores-list') }}",
            type: "POST",
            dataType: 'json',
            delay: 250,
            data: function(params) {
                return {
                    searchQuery: params.term,
                    page: params.page || 1,  
                    _token: "{{ csrf_token() }}",
                    showCode: 1
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
    }).on('change', function () {
        var selectedData = $('#store').select2('data');
        
        if (selectedData.length > 0) {
            $('#name').val(selectedData[0].text);
        }
    });



            var $fbPages = $(document.getElementById("form-builder-pages"));
            var addPageTab = document.getElementById("add-page-tab");
            var fbInstances = [];

            $fbPages.tabs({
                beforeActivate: function(event, ui) {
                    if (ui.newPanel.selector === "#new-page") {
                        return false;
                    }
                }
            });

            $fbPages.tabs("option", "active", 0);

            addPageTab.addEventListener(
                "click",
                (click) => {
                    const tabCount = document.getElementById("tabs").children.length;
                    const tabId = "page-" + tabCount.toString();
                    const $newPageTemplate = document.getElementById("new-page");
                    const $newTabTemplate = document.getElementById("add-page-tab");
                    const $newPage = $newPageTemplate.cloneNode(true);
                    $newPage.setAttribute("id", tabId);
                    $newPage.classList.add("fb-editor");
                    const $newTab = $newTabTemplate.cloneNode(true);
                    $newTab.removeAttribute("id");
                    const $tabLink = $newTab.querySelector("a");
                    $tabLink.setAttribute("href", "#" + tabId);
                    $tabLink.innerText = "Page " + tabCount;

                    $newPageTemplate.parentElement.insertBefore($newPage, $newPageTemplate);
                    $newTabTemplate.parentElement.insertBefore($newTab, $newTabTemplate);
                    $fbPages.tabs("refresh");
                    $fbPages.tabs("option", "active", tabCount - 1);
                    fbInstances.push($($newPage).formBuilder());
                },
                false
            );


            @foreach ($form->schema as $key => $page)
            fbInstances.push($("#page-{{ $loop->iteration }}").formBuilder({
                formData: @json($form->schema[$key]),
                dataType: 'json',
            }));
            @endforeach

            $(document.getElementById("save-all")).click(function() {
                const allData = fbInstances.map((fb) => {
                    console.log(fb.actions.getData());
                    return fb.formData;
                });

                $.ajax({
                    url: "{{ route('duplicate-workflow-checklist', $id) }}",
                    type: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}",
                        name: $('#name').val(),
                        form_schema: allData,
                        _method: 'PUT'
                    },
                    beforeSend: function() {
                        $('body').find('.LoaderSec').removeClass('d-none');
                    },
                    success: function(response) {
                        if (response.status) {
                            Swal.fire('Success', response.message, 'success');
                            window.location.replace("{{ route('workflow-checklists.index') }}");
                        } else {
                            Swal.fire('Error', response.message, 'error');
                        }
                    },
                    error: function(response) {
                        if ('responseJSON' in response && 'errors' in response.responseJSON) {
                            if ('name' in response.responseJSON.errors) {
                                if (response.responseJSON.errors.name.length > 0) {
                                    Swal.fire('Error', response.responseJSON.errors.name[0],
                                        'error');
                                }
                            }
                        }
                    },
                    complete: function(response) {
                        $('body').find('.LoaderSec').addClass('d-none');
                    }
                });

            });
        });
    </script>
@endpush
