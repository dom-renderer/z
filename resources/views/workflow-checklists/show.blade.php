@extends('layouts.app-master')

@push('css')
<link rel="stylesheet" href="{{ asset('assets/css/select2.min.css') }}">
<link rel='stylesheet' href="{{ asset('assets/css/font-awesome.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/custom-select-style.css') }}">
@endpush

@section('content')
    <div class="bg-light p-4 rounded">

        <div class="row mt-4">

                <div class="mb-3">
                    <label for="name" class="form-label">Name <span class="text-danger"> * </span> </label>
                    <input type="text" name="name" class="form-control" value="{{ $form->name }}" readonly>
                </div>

                <form id="form-builder-pages">
                    <div class="mb-3 position-relative" style="background: #b1b1b1df;padding: 10px;">
                        <div class="overlay"></div>
                        <ul id="tabs">
                            @foreach ($form->schema as $page)
                            <li><a href="#page-{{ $loop->iteration }}">Page {{ $loop->iteration }}</a></li>
                            @endforeach
    
                            <li id="add-page-tab"><a href="#new-page">+ Page</a></li>
                        </ul>
                        @foreach ($form->schema as $page)
                        <div id="page-{{ $loop->iteration }}" class="fb-editor"></div>
                        @endforeach
    
                    </div>
                </form>

                <div class="save-all-wrap">
                    <button id="save-all" type="button" class="btn btn-primary">Save</button>
                    <a href="{{ route('workflow-checklists.index') }}" class="btn btn-default">Back</a>
                </div>
        </div>

    </div>

@endsection

@push('js')
    <script src="{{ url('assets/form-builder/form-builder.min.js') }}"></script>
    <script src="{{ asset('assets/js/jquery-validate.min.js') }}"></script>
    <script src="{{ asset('assets/js/select2.min.js') }}"></script>
    <script type="text/javascript">
        jQuery(($) => {
            "use strict";
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
        });
    </script>
@endpush
