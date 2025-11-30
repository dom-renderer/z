@extends('layouts.app-master')

@push('css')
    <link href="{{ asset('assets/css/select2.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/css/custom-select-style.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/css/font-awesome.min.css') }}" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('assets/css/jquery.datetimepicker.css') }}">
@endpush

@section('content')
    <div class="bg-light p-4 rounded">

        <div class="row mt-4">

            <form method="POST" id="formBuilder"> @csrf

                <div class="mb-3">
                    <label for="name" class="form-label"> Name <span class="text-danger"> * </span> </label>
                    <input type="text" name="name" id="name" class="form-control" placeholder="Name"
                        value="{{ old('name') }}" required>
                    @if ($errors->has('name'))
                        <span class="text-danger text-left">{{ $errors->first('name') }}</span>
                    @endif
                </div>

                <div class="mb-3">
                    <label for="template" class="form-label"> Workflow Template <span class="text-danger"> * </span>
                    </label>
                    <select name="template" id="template" required></select>
                    @if ($errors->has('template'))
                        <span class="text-danger text-left">{{ $errors->first('template') }}</span>
                    @endif
                </div>

                <div class="mb-3 row">
                    <div class="col-6">
                        <label for="start" class="form-label"> Start Date <span class="text-danger"> * </span> </label>
                        <input type="text" id="start" name="start" class="form-control" placeholder="Start"
                            value="{{ old('start') }}" required>
                        @if ($errors->has('start'))
                            <span class="text-danger text-left">{{ $errors->first('start') }}</span>
                        @endif
                    </div>
                    <div class="col-6">
                        <label for="end" class="form-label"> End Date <span class="text-danger"> * </span> </label>
                        <input type="text" id="end" name="end" class="form-control" placeholder="End"
                            value="{{ old('end') }}" required>
                        @if ($errors->has('end'))
                            <span class="text-danger text-left">{{ $errors->first('end') }}</span>
                        @endif
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-header">
                        Checklist Mapping
                    </div>
                    <div class="card-body" id="template-configuration">

                    </div>
                </div>

                <div>
                    <button type="submit" class="btn btn-primary">Save</button>
                    <a href="{{ route('workflow-assignments.index') }}" class="btn btn-default">Back</a>
                </div>
            </form>
        </div>

    </div>


    <div class="modal fade" id="notificationModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel"> Set Escalation </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">

                    <form action="" id="escalationForm">
                        <table class="w-100 table table-stripped table-bordered">
                            <thead>
                                <tr>
                                    <th>
                                        <div>
                                            <label for="modal-branch-type" style="position: relative;bottom:10px;"> Location Type </label>
                                            <select id="modal-branch-type" name="modal_branch_type">
                                                <option value="" selected></option>
                                                <option value="1"> Location </option>
                                                <option value="2"> Department </option>
                                            </select>
                                        </div>
                                    </th>

                                    <th>
                                        <div>
                                            <label for="modal-selected-branch" style="position: relative;bottom:10px;"> Location / Department </label>
                                            <select id="modal-selected-branch" name="modal_selected_branch">
                                            </select>
                                        </div>
                                    </th>

                                    <th>
                                        <div>
                                            <label for="modal-selected-branch-user" style="position: relative;bottom:10px;"> User </label>
                                            <select id="modal-selected-branch-user" name="modal_selected_branch_user">
                                            </select>
                                        </div>
                                    </th>

                                    <th>
                                        <div>
                                            <label for="modal-time" style="position: relative;bottom:10px;"> Time </label>
                                            <input type="number" class="form-control" id="modal-time" placeholder="5"
                                                min="1" name="modal_time">
                                        </div>
                                    </th>

                                    <th>
                                        <div>
                                            <label for="modal-time-type" style="position: relative;bottom:10px;"> Time
                                                Type </label>
                                            <select id="modal-time-type" name="modal_time_type">
                                                <option value="" selected></option>
                                                <option value="0"> Minute </option>
                                                <option value="1"> Hour </option>
                                                <option value="2"> Day </option>
                                            </select>
                                        </div>
                                    </th>

                                    <th>
                                        <div>
                                            <label for="modal-email-template" style="position: relative;bottom:10px;">
                                                Mail Notification Templates </label>
                                            <select id="modal-email-template" name="modal_email_template[]" multiple>
                                            </select>
                                        </div>
                                    </th>

                                    <th>
                                        <div>
                                            <label for="modal-pn-template" style="position: relative;bottom:10px;"> Push
                                                Notification Templates </label>
                                            <select id="modal-pn-template" name="modal_pn_template[]" multiple>
                                            </select>
                                        </div>
                                    </th>

                                    <th>
                                        <center>
                                            <button type="submit" class="btn btn-success w-100 escalation-add"
                                                style="margin-bottom:10px;"> Add </button> <br>
                                            <button type="reset" class="btn btn-danger w-100 escalation-reset"> Reset
                                            </button>
                                        </center>
                                    </th>

                                </tr>
                            </thead>
                        </table>
                    </form>

                    <table class="w-100 table table-bordered" class="escalations-table">
                        <thead>
                            <tr>
                                <th>Location Type</th>
                                <th>Location / Department</th>
                                <th>User</th>
                                <th>Time</th>
                                <th>Time Type</th>
                                <th>Mail Notification Templates</th>
                                <th>Push Notification Templates</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="escalations">

                        </tbody>
                    </table>

                </div>
            </div>

            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"> Completion Notification </h5>
                </div>
                <div class="modal-body">


                    <div class="card">
                        <div class="card-header">
                            <p style="position: relative;top: 8px;"> Checklist Completion Notification </p>
                        </div>
                        <div class="card-body">
                            <form id="completionForm">
                                <div class="mb-3">
                                    <label for="branch2" class="form-label"> Location Type <span class="text-danger"> *
                                        </span> </label>
                                    <select id="branch2" name="comp_1" required>
                                        <option value="" selected></option>
                                        <option value="1"> Location </option>
                                        <option value="2"> Department </option>
                                    </select>
                                </div>

                                <div class="mb-3" id="store-department-html-container2">
                                    <label for="thisStoreDepartmentCOffice2" class="form-label"> Location / Department <span
                                            class="text-danger"> * </span> </label>
                                    <select id="thisStoreDepartmentCOffice2" name="comp_2" required>
                                    </select>
                                    </label>
                                </div>

                                <div class="mb-3" id="store-department-user-html-container2">
                                    <label for="thisStoreDepartmentCOfficeUser2" class="form-label"> User <span
                                            class="text-danger"> * </span> </label>
                                    <select id="thisStoreDepartmentCOfficeUser2" name="comp_3" required>
                                    </select>
                                    </label>
                                </div>

                                <div class="mb-3">
                                    <label for="compnotimail" class="form-label"> Mail Notification Templates <span
                                            class="text-danger"> * </span> </label>
                                    <select id="compnotimail" name="comp_4[]" multiple required>

                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="compnotipush" class="form-label"> Push Notification Templates <span
                                            class="text-danger"> * </span> </label>
                                    <select id="compnotipush" name="comp_5[]" multiple required>

                                    </select>
                                </div>

                                <div class="mb-3">
                                    <button type="submit" class="btn btn-sm btn-success"> Save </button>
                                    <button type="button" class="btn btn-sm btn-danger reset-completion"> Reset </button>
                                </div>
                            </form>
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script src="{{ asset('assets/js/jquery.datetimepicker.js') }}"></script>
    <script src="{{ asset('assets/js/jquery-validate.min.js') }}"></script>
    <script src="{{ asset('assets/js/select2.min.js') }}"></script>
    <script type="text/javascript">
        let escalationObj = [];
        let completionObj = [];
        let currentUniqId = null;
        let escalationRequestAdder = (currentUniqId = null) => {
            if (currentUniqId != null) {
                $(`#template_tat-${currentUniqId}`).val(JSON.stringify(escalationObj[currentUniqId]));
            }
        }

        let compRequestAdder = (currentUniqId = null) => {
            if (currentUniqId != null) {
                $(`#template_ctat-${currentUniqId}`).val(JSON.stringify(completionObj[currentUniqId]));
            }
        }

        let isJsonParsable = (text) => {
            if (typeof text !== "string") {
                return false;
            }
            try {
                var json = JSON.parse(text);
                return (typeof json === 'object');
            } catch (error) {
                return false;
            }
        }


        $(document).ready(function() {

            const Toast = Swal.mixin({
                toast: true,
                position: "top-end",
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.onmouseenter = Swal.stopTimer;
                    toast.onmouseleave = Swal.resumeTimer;
                }
            });

            let thisTemplateSelection = $('#template').select2({
                allowClear: true,
                placeholder: "Select template",
                width: '100%',
                theme: 'classic',
                ajax: {
                    url: "{{ route('workflow-templates-list') }}",
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

            $(thisTemplateSelection).on('change', function(e) {

                let thisWorkflowTemplateId = $(this, 'option:selected').val();

                if (!isNaN(thisWorkflowTemplateId) && thisWorkflowTemplateId > 0) {
                    $.ajax({
                        url: "{{ route('configure-workflow-assignment') }}",
                        type: 'POST',
                        data: {
                            _token: "{{ csrf_token() }}",
                            id: thisWorkflowTemplateId
                        },
                        beforeSend: function() {
                            $('body').find('.LoaderSec').removeClass('d-none');
                        },
                        success: function(response) {
                            if (response.status) {
                                $('#template-configuration').html(response.html);
                            } else {
                                Swal.fire('Error', response.message, 'error');
                                $('#template-configuration').html('');
                                escalationObj = [];
                                completionObj = [];
                                currentUniqId = null;
                            }
                        },
                        complete: function() {
                            initializeJavascriptForTemplate();
                            $('body').find('.LoaderSec').addClass('d-none');
                        }
                    });
                } else {
                    $('#template-configuration').html('');
                    escalationObj = [];
                    completionObj = [];
                    currentUniqId = null;
                }
            });

            $('#start').datetimepicker({
                format: 'd-m-Y H:i'
            });

            $('#end').datetimepicker({
                format: 'd-m-Y H:i'
            });

            $('#notificationModal').on('shown.bs.modal', function(e) {
                if (e.namespace == 'bs.modal') {
                    currentUniqId = $(e.relatedTarget).data('uid');

                    let expectedJsonValue = $(`#template_tat-${currentUniqId}`).val();
                    if (isJsonParsable(expectedJsonValue)) {
                        let jsonParsedString = JSON.parse(expectedJsonValue);

                        if (typeof jsonParsedString == 'object') {
                            Object.values(jsonParsedString).forEach((objectValue, objectIndex) => {
                                $('#escalations').append(`<tr>
                                    <td data-modal-branch-type="${objectIndex}"> ${objectValue.branch_type_name} </td>
                                    <td data-modal-selected-branch="${objectIndex}"> ${objectValue.branch_name} </td>
                                    <td data-modal-selected-branch-user="${objectIndex}"> ${objectValue.branch_user_name} </td>
                                    <td data-modal-time="${objectIndex}"> ${objectValue.time} </td>
                                    <td data-modal-time-type="${objectIndex}"> ${objectValue.time_type_name} </td>
                                    <td data-modal-email-template="${objectIndex}"> ${objectValue.mail_templates_name} </td>
                                    <td data-modal-pn-template="${objectIndex}"> ${objectValue.pn_templates_name} </td>
                                    <td data-=""> <button class="btn btn-danger delete-escalation" type="button"> <i class="fa fa-trash"> </i> </button> </td>
                                </tr>`);
                            });
                        }
                    }


                    let expectedJsonValue2 = $(`#template_ctat-${currentUniqId}`).val();
                    if (isJsonParsable(expectedJsonValue2)) {
                        let jsonParsedString2 = JSON.parse(expectedJsonValue2);

                        if (typeof jsonParsedString2 == 'object') {
                            if ('branch_type' in jsonParsedString2) {
                                $('#branch2').val(jsonParsedString2.branch_type).trigger('change');
                            }

                            if ('branch' in jsonParsedString2 && 'branch_name' in jsonParsedString2) {
                                $('#thisStoreDepartmentCOffice2').html(`<option value="${jsonParsedString2.branch}"> ${jsonParsedString2.branch_name} </option>`);
                                $('#thisStoreDepartmentCOffice2').val(jsonParsedString2.branch).trigger('change');
                            }

                            if ('branch_user' in jsonParsedString2 && 'branch_user_name' in jsonParsedString2) {
                                $('#thisStoreDepartmentCOfficeUser2').html(`<option value="${jsonParsedString2.branch_user}"> ${jsonParsedString2.branch_user_name} </option>`);
                                $('#thisStoreDepartmentCOfficeUser2').val(jsonParsedString2.branch_user).trigger('change');
                            }

                            if ('mail_templates' in jsonParsedString2 && 'mail_templates_name' in jsonParsedString2) {
                                let optString = '';
                                let explodedTexts = jsonParsedString2.mail_templates_name.split('<br/>');

                                jsonParsedString2.mail_templates.forEach((element, thisInd) => {
                                    optString += `<option value="${element}"> ${explodedTexts[thisInd]} </option>`;
                                });

                                $('#compnotimail').html(optString);
                                $('#compnotimail').val(jsonParsedString2.mail_templates).trigger('change');
                            }

                            if ('pn_templates' in jsonParsedString2 && 'pn_templates_name' in jsonParsedString2) {
                                let optString = '';
                                let explodedTexts = jsonParsedString2.pn_templates_name.split('<br/>');

                                jsonParsedString2.pn_templates.forEach((element, thisInd) => {
                                    optString += `<option value="${element}"> ${explodedTexts[thisInd]} </option>`;
                                });

                                $('#compnotipush').html(optString);
                                $('#compnotipush').val(jsonParsedString2.pn_templates).trigger('change');                                
                            }
                        }
                    }
                }
            });

            $('#formBuilder').on('submit', function(e) {
                e.preventDefault();

                let formData = $(this).serializeArray()
                let fillable = {};

                if (formData.length > 0) {
                    formData.forEach(element => {
                        fillable[element.name] = element.value;
                    });
                }

                $.ajax({
                    url: "{{ route('save-configured-workflow-assignment') }}",
                    type: 'POST',
                    data: fillable,
                    beforeSend: function() {
                        $('body').find('.LoaderSec').removeClass('d-none');
                    },
                    success: function(response) {
                        if (response.status) {
                            Swal.fire('Success', response.message, 'success');
                            window.location.replace(
                                "{{ route('workflow-assignments.index') }}");
                        } else {
                            Swal.fire('Error', response.message, 'error');
                        }
                    },
                    complete: function() {
                        $('body').find('.LoaderSec').addClass('d-none');
                    }
                });
            });

            /* Configuration JS */
            function initializeJavascriptForTemplate() {

                $('.template_branch_type').select2({
                    placeholder: 'Select Location Type',
                    allowClear: true,
                    width: '100%',
                    theme: 'classic'
                }).on('change', function() {
                    if ($(this, 'option:selected').val() == 1) {
                        $(`#template_branch-${$(this).attr('data-uid')}`).val(null).trigger('change');
                        $(`#template_branch-${$(this).attr('data-uid')}`).html('');
                        $(`#template_user-${$(this).attr('data-uid')}`).val(null).trigger('change');
                        $(`#template_user-${$(this).attr('data-uid')}`).html('');
                        $(`#template_branch-${$(this).attr('data-uid')}`).attr("data-placeholder","Select Location");

                        let thisStoreSelection = $(
                            `#template_branch-${$(this, 'option:selected').data('uid')}`).select2({
                            placeholder: "Select Location",
                            allowClear: true,
                            width: "100%",
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
                        });

                        $(thisStoreSelection).on('change', function() {
                            if (!isNaN($(thisStoreSelection, 'option:selected').val()) && $(
                                    thisStoreSelection, 'option:selected').val() > 0) {
                                $(`#template_user-${$(thisStoreSelection, 'option:selected').data('uid')}`)
                                    .select2({
                                        placeholder: "Select User",
                                        allowClear: true,
                                        width: "100%",
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
                                                    branchType: 1,
                                                    branchId: $(thisStoreSelection,
                                                        'option:selected').val()
                                                };
                                            },
                                            processResults: function(data, params) {
                                                params.page = params.page || 1;

                                                return {
                                                    results: $.map(data.items, function(
                                                        item) {
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
                            } else {
                                $(`#template_user-${$(thisStoreSelection).attr('data-uid')}`).val(
                                    null).trigger('change');
                            }
                        });

                    } else if ($(this, 'option:selected').val() == 2) {
                        $(`#template_branch-${$(this).attr('data-uid')}`).val(null).trigger('change');
                        $(`#template_branch-${$(this).attr('data-uid')}`).html('');
                        $(`#template_user-${$(this).attr('data-uid')}`).val(null).trigger('change');
                        $(`#template_user-${$(this).attr('data-uid')}`).html('');
                        $(`#template_branch-${$(this).attr('data-uid')}`).attr("data-placeholder","Select Department");

                        let thisDepartmentSelection = $(
                            `#template_branch-${$(this, 'option:selected').data('uid')}`).select2({
                            placeholder: "Select Department",
                            allowClear: true,
                            width: "100%",
                            theme: 'classic',
                            ajax: {
                                url: "{{ route('departments-list') }}",
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

                        $(thisDepartmentSelection).on('change', function() {
                            if (!isNaN($(thisDepartmentSelection, 'option:selected').val()) && $(
                                    thisDepartmentSelection, 'option:selected').val() > 0) {
                                $(`#template_user-${$(thisDepartmentSelection, 'option:selected').data('uid')}`)
                                    .select2({
                                        placeholder: "Select User",
                                        allowClear: true,
                                        width: "100%",
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
                                                    branchType: 3,
                                                    branchId: $(thisDepartmentSelection,
                                                        'option:selected').val()
                                                };
                                            },
                                            processResults: function(data, params) {
                                                params.page = params.page || 1;

                                                return {
                                                    results: $.map(data.items, function(
                                                        item) {
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
                            } else {
                                $(`#template_user-${$(thisDepartmentSelection).attr('data-uid')}`)
                                    .val(null).trigger('change');
                            }
                        });
                    } else {
                        $(`#template_branch-${$(this).attr('data-uid')}`).val(null).trigger('change');
                        $(`#template_user-${$(this).attr('data-uid')}`).val(null).trigger('change');
                        $(`#template_branch-${$(this).attr('data-uid')}`).attr("data-placeholder","Select Location / Department");
                    }
                });

                $('#thisStoreDepartmentCOffice2').select2({
                    placeholder: "Select Location Type",
                    allowClear: true,
                    width: "100%",
                    theme: 'classic',
                });

                $('#thisStoreDepartmentCOfficeUser2').select2({
                    placeholder: "Select User",
                    allowClear: true,
                    width: "100%",
                    theme: 'classic',
                });

                $('.template_branch').select2({
                    placeholder: "Select Location / Department",
                    allowClear: true,
                    width: "100%",
                    theme: 'classic',
                });

                $('.template_user').select2({
                    placeholder: "Select User",
                    allowClear: true,
                    width: "100%",
                    theme: 'classic',
                });

                $('.template_time_type').select2({
                    placeholder: "Time Type",
                    allowClear: true,
                    width: "100%",
                    theme: 'classic',
                });



                $('#completionForm').validate({
                    rules: {
                        comp_1: {
                            required: true
                        },
                        comp_2: {
                            required: true
                        },
                        comp_3: {
                            required: true
                        },
                        'comp_4[]': {
                            required: function() {
                                return !$('#compnotipush').val().length;
                            }
                        },
                        'comp_5[]': {
                            required: function() {
                                return !$('#compnotimail').val().length;
                            }
                        }
                    },
                    messages: {
                        comp_1: {
                            required: "Select location type"
                        },
                        comp_2: {
                            required: "Select location / department"
                        },
                        comp_3: {
                            required: "Select user"
                        },
                        'comp_4[]': {
                            required: "Select template"
                        },
                        'comp_5[]': {
                            required: "Select template"
                        }
                    },
                    errorPlacement: function(error, element) {
                        error.appendTo(element.parent("div"));
                    },
                    submitHandler: function(validate, form) {
                        form.preventDefault();

                        let thisNewIndex = $('#escalations tr:last').index() + 1;

                        if (!completionObj[currentUniqId]) {
                            completionObj[currentUniqId] = {};
                        }

                        completionObj[currentUniqId] = {
                            branch_type: $('#branch2 option:selected').val(),
                            branch: $('#thisStoreDepartmentCOffice2 option:selected').val(),
                            branch_user: $('#thisStoreDepartmentCOfficeUser2 option:selected').val(),
                            mail_templates: $('#compnotimail').val(),
                            pn_templates: $('#compnotipush').val(),

                            branch_type_name: $('#branch2 option:selected').text(),
                            branch_name: $('#thisStoreDepartmentCOffice2 option:selected').text(),
                            branch_user_name: $('#thisStoreDepartmentCOfficeUser2 option:selected').text(),
                            mail_templates_name: $('#compnotimail').select2('data').map(
                                thisEl => thisEl.text).join('<br/>'),
                            pn_templates_name: $('#compnotipush').select2('data').map(thisEl =>
                                thisEl.text).join('<br/>')
                        };

                        $('#completionForm > label.error').remove();
                        compRequestAdder(currentUniqId);

                        Toast.fire({
                            icon: "success",
                            title: "Saved successfully!"
                        });
                    }
                });


                $('#escalationForm').validate({
                    rules: {
                        modal_branch_type: {
                            required: true
                        },
                        modal_selected_branch: {
                            required: true
                        },
                        modal_selected_branch_user: {
                            required: true
                        },
                        modal_time: {
                            required: true,
                            min: 1
                        },
                        modal_time_type: {
                            required: true
                        },
                        'modal_email_template[]': {
                            required: function() {
                                return !$('#modal-pn-template').val().length;
                            }
                        },
                        'modal_pn_template[]': {
                            required: function() {
                                return !$('#modal-email-template').val().length;
                            }
                        }
                    },
                    messages: {
                        modal_branch_type: {
                            required: "Select location type"
                        },
                        modal_selected_branch: {
                            required: "Select location / department"
                        },
                        modal_selected_branch_user: {
                            required: "Select user"
                        },
                        modal_time: {
                            required: "Enter time",
                            min: "Minimum time should be greater than or equal 1"
                        },
                        modal_time_type: {
                            required: "Select time type"
                        },
                        'modal_email_template[]': {
                            required: "Select template"
                        },
                        'modal_pn_template[]': {
                            required: "Select template"
                        }
                    },
                    errorPlacement: function(error, element) {
                        error.appendTo(element.parent("div"));
                    },
                    submitHandler: function(validate, form) {
                        form.preventDefault();

                        let thisNewIndex = $('#escalations tr:last').index() + 1;

                        $('#escalations').append(`<tr>
                            <td data-modal-branch-type="${thisNewIndex}"> ${$('#modal-branch-type option:selected').text()} </td>
                            <td data-modal-selected-branch="${thisNewIndex}"> ${$('#modal-selected-branch option:selected').text()} </td>
                            <td data-modal-selected-branch-user="${thisNewIndex}"> ${$('#modal-selected-branch-user option:selected').text()} </td>
                            <td data-modal-time="${thisNewIndex}"> ${$('#modal-time').val()} </td>
                            <td data-modal-time-type="${thisNewIndex}"> ${$('#modal-time-type option:selected').text()} </td>
                            <td data-modal-email-template="${thisNewIndex}"> ${$('#modal-email-template').select2('data').map(thisEl => thisEl.text).join('<br/>')} </td>
                            <td data-modal-pn-template="${thisNewIndex}"> ${$('#modal-pn-template').select2('data').map(thisEl => thisEl.text).join('<br/>')} </td>
                            <td data-=""> <button class="btn btn-danger delete-escalation" type="button"> <i class="fa fa-trash"> </i> </button> </td>
                        </tr>`);

                        if (!escalationObj[currentUniqId]) {
                            escalationObj[currentUniqId] = {};
                        }

                        escalationObj[currentUniqId][thisNewIndex] = {
                            branch_type: $('#modal-branch-type option:selected').val(),
                            branch: $('#modal-selected-branch option:selected').val(),
                            branch_user: $('#modal-selected-branch-user option:selected').val(),
                            time: $('#modal-time').val(),
                            time_type: $('#modal-time-type option:selected').val(),
                            mail_templates: $('#modal-email-template').val(),
                            pn_templates: $('#modal-pn-template').val(),

                            branch_type_name: $('#modal-branch-type option:selected').text(),
                            branch_name: $('#modal-selected-branch option:selected').text(),
                            branch_user_name: $('#modal-selected-branch-user option:selected')
                            .text(),
                            time_type_name: $('#modal-time-type option:selected').text(),
                            mail_templates_name: $('#modal-email-template').select2('data').map(
                                thisEl => thisEl.text).join('<br/>'),
                            pn_templates_name: $('#modal-pn-template').select2('data').map(thisEl =>
                                thisEl.text).join('<br/>')
                        };

                        $('#modal-branch-type').val(null).trigger('change');
                        $('#modal-selected-branch').val(null).trigger('change');
                        $('#modal-selected-branch-user').val(null).trigger('change');
                        $('#modal-time-type').val(null).trigger('change');
                        $('#modal-email-template').val(null).trigger('change');
                        $('#modal-pn-template').val(null).trigger('change');
                        $('#modal-time').val(null);

                        $('#modal-selected-branch').select2('data', null);
                        $('#modal-selected-branch').empty();
                        $('#modal-selected-branch-user').select2('data', null);
                        $('#modal-selected-branch-user').empty();

                        $('#notificationModal label.error').remove();
                        escalationRequestAdder(currentUniqId);

                        Toast.fire({
                            icon: "success",
                            title: "Added successfully!"
                        });
                    }
                });

                $('#compnotimail').select2({
                    placeholder: 'Select Template',
                    allowClear: true,
                    width: '100%',
                    theme: 'classic',
                    dropdownParent: $('#notificationModal'),
                    ajax: {
                        url: "{{ route('notification-template-list') }}",
                        type: "POST",
                        dataType: 'json',
                        delay: 250,
                        data: function(params) {
                            return {
                                searchQuery: params.term,
                                page: params.page || 1,
                                _token: "{{ csrf_token() }}",
                                completion_type: 1,
                                type: 0
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

                $('#compnotipush').select2({
                    placeholder: 'Select Template',
                    allowClear: true,
                    width: '100%',
                    theme: 'classic',
                    dropdownParent: $('#notificationModal'),
                    ajax: {
                        url: "{{ route('notification-template-list') }}",
                        type: "POST",
                        dataType: 'json',
                        delay: 250,
                        data: function(params) {
                            return {
                                searchQuery: params.term,
                                page: params.page || 1,
                                _token: "{{ csrf_token() }}",
                                completion_type: 1,
                                type: 1
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

                $('#modal-email-template').select2({
                    placeholder: 'Select Template',
                    allowClear: true,
                    width: '100%',
                    theme: 'classic',
                    dropdownParent: $('#notificationModal')
                });

                $('#modal-pn-template').select2({
                    placeholder: 'Select Template',
                    allowClear: true,
                    width: '100%',
                    theme: 'classic',
                    dropdownParent: $('#notificationModal')
                });

                $('#modal-branch-type').select2({
                    placeholder: 'Select Location Type',
                    allowClear: true,
                    width: '100%',
                    theme: 'classic',
                    dropdownParent: $('#notificationModal')
                }).on('change', function() {
                    $('#modal-selected-branch').val(null).trigger('change');
                    $('#modal-selected-branch-user').val(null).trigger('change');

                    $('#modal-selected-branch').select2('data', null);
                    $('#modal-selected-branch').empty();
                    $('#modal-selected-branch-user').select2('data', null);
                    $('#modal-selected-branch-user').empty();
                });

                $('#modal-selected-branch').select2({
                    placeholder: 'Select Location / Department',
                    allowClear: true,
                    width: '100%',
                    theme: 'classic',
                    dropdownParent: $('#notificationModal'),
                    ajax: {
                        url: () => {
                            if ($('#modal-branch-type option:selected').val() == 1) {
                                return "{{ route('stores-list') }}";
                            } else if ($('#modal-branch-type option:selected').val() == 2) {
                                return "{{ route('departments-list') }}";
                            } else {
                                return false;
                            }
                        },
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
                    $('#modal-selected-branch-user').val(null).trigger('change');
                    $('#modal-selected-branch-user').select2('data', null);
                    $('#modal-selected-branch-user').empty();
                });

                $('#modal-selected-branch-user').select2({
                    placeholder: 'Select User',
                    allowClear: true,
                    width: '100%',
                    theme: 'classic',
                    dropdownParent: $('#notificationModal'),
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
                                branchType: () => {
                                    if ($('#modal-branch-type option:selected').val() == 1) {
                                        return 1;
                                    } else if ($('#modal-branch-type option:selected').val() == 2) {
                                        return 3;
                                    } else {
                                        return 0;
                                    }
                                },
                                branchId: $('#modal-selected-branch option:selected').val()
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

                $('#modal-email-template').select2({
                    placeholder: 'Select Templates',
                    allowClear: true,
                    width: '100%',
                    theme: 'classic',
                    dropdownParent: $('#notificationModal'),
                    ajax: {
                        url: "{{ route('notification-template-list') }}",
                        type: "POST",
                        dataType: 'json',
                        delay: 250,
                        data: function(params) {
                            return {
                                searchQuery: params.term,
                                page: params.page || 1,
                                _token: "{{ csrf_token() }}",
                                completion_type: 0,
                                type: 0
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

                $('#modal-pn-template').select2({
                    placeholder: 'Select Templates',
                    allowClear: true,
                    width: '100%',
                    theme: 'classic',
                    dropdownParent: $('#notificationModal'),
                    ajax: {
                        url: "{{ route('notification-template-list') }}",
                        type: "POST",
                        dataType: 'json',
                        delay: 250,
                        data: function(params) {
                            return {
                                searchQuery: params.term,
                                page: params.page || 1,
                                _token: "{{ csrf_token() }}",
                                completion_type: 0,
                                type: 1
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

                $('#modal-time-type').select2({
                    placeholder: 'Select Time',
                    allowClear: true,
                    width: '100%',
                    theme: 'classic',
                    dropdownParent: $('#notificationModal')
                });

                $('.escalation-reset').on('click', function() {
                    $('#modal-branch-type').val(null).trigger('change');
                    $('#modal-selected-branch').val(null).trigger('change');
                    $('#modal-selected-branch-user').val(null).trigger('change');
                    $('#modal-time-type').val(null).trigger('change');
                    $('#modal-email-template').val(null).trigger('change');
                    $('#modal-pn-template').val(null).trigger('change');

                    $('#notificationModal label.error').remove();

                    $('#modal-selected-branch').select2('data', null);
                    $('#modal-selected-branch').empty();
                    $('#modal-selected-branch-user').select2('data', null);
                    $('#modal-selected-branch-user').empty();
                });

                $('#notificationModal').on('hidden.bs.modal', function(e) {
                    if (e.namespace == 'bs.modal') {

                        $('#modal-branch-type').val(null).trigger('change');
                        $('#modal-selected-branch').val(null).trigger('change');
                        $('#modal-selected-branch-user').val(null).trigger('change');
                        $('#modal-time-type').val(null).trigger('change');
                        $('#modal-email-template').val(null).trigger('change');
                        $('#modal-pn-template').val(null).trigger('change');

                        $('#notificationModal label.error').remove();

                        $('#modal-selected-branch').select2('data', null);
                        $('#modal-selected-branch').empty();
                        $('#modal-selected-branch-user').select2('data', null);
                        $('#modal-selected-branch-user').empty();
                        $('#escalations').html('');

                        $('#branch2').val(null).trigger('change');

                        $('#thisStoreDepartmentCOffice2').val(null).trigger('change');
                        $('#thisStoreDepartmentCOffice2').html('');

                        $('#thisStoreDepartmentCOfficeUser2').val(null).trigger('change');
                        $('#thisStoreDepartmentCOfficeUser2').html('');

                        $('#compnotimail').val(null).trigger('change');
                        $('#compnotimail').html('');

                        $('#compnotipush').val(null).trigger('change');
                        $('#compnotipush').html('');
                    }
                });

                $(document).on('click', '.delete-escalation', function() {
                    Swal.fire({
                        title: 'Are you sure you want to remove this escalation?',
                        text: "You won't be able to revert this!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Yes, remove it!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            let thisIndex = $(this).parent().parent().index();

                            if (typeof escalationObj == 'object' && currentUniqId in
                                escalationObj && thisIndex in escalationObj[currentUniqId]) {
                                $(this).parent().parent().remove();
                                delete escalationObj[currentUniqId][thisIndex];
                                escalationRequestAdder(currentUniqId);
                            }

                            Toast.fire({
                                icon: "success",
                                title: "Deleted successfully!"
                            });

                            return true;
                        } else {
                            return false;
                        }
                    })
                });

                $(document).on('click', '.reset-completion', function (e) {
                    e.preventDefault();

                    if (currentUniqId != null) {
                        if (completionObj[currentUniqId]) {
                            delete completionObj[currentUniqId];
                            compRequestAdder(currentUniqId);

                            $('#branch2').val(null).trigger('change');

                            $('#thisStoreDepartmentCOffice2').val(null).trigger('change');
                            $('#thisStoreDepartmentCOffice2').html('');

                            $('#thisStoreDepartmentCOfficeUser2').val(null).trigger('change');
                            $('#thisStoreDepartmentCOfficeUser2').html('');

                            $('#compnotimail').val(null).trigger('change');
                            $('#compnotimail').html('');

                            $('#compnotipush').val(null).trigger('change');
                            $('#compnotipush').html('');

                            Toast.fire({
                                icon: "success",
                                title: "Reset successfully!"
                            });
                        }   
                    }
                });

                /* Completion JS */
                $('#branch2').select2({
                    placeholder: 'Select Location Type',
                    allowClear: true,
                    width: '100%',
                    theme: 'classic',
                    dropdownParent: $('#notificationModal'),
                }).on('change', function() {
                    if ($('#branch2 option:selected').val() == 1) {
                        if ($('#thisStoreDepartmentCOffice2').length > 0) {
                            $('#thisStoreDepartmentCOffice2').select2({
                                placeholder: "Select Location/Department",
                                allowClear: true,
                                width: "100%",
                                theme: 'classic',
                                dropdownParent: $('#notificationModal'),
                                ajax: {
                                    url: "{{ route('stores-list') }}",
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
                                $('#thisStoreDepartmentCOfficeUser2').val(null).trigger('change');
                                $('#thisStoreDepartmentCOfficeUser2').val('');

                                if (!isNaN($('#thisStoreDepartmentCOffice2 option:selected')
                                .val()) && $(
                                        '#thisStoreDepartmentCOffice2 option:selected').val() > 0) {
                                    if ($('#thisStoreDepartmentCOfficeUser2').length > 0) {
                                        $('#thisStoreDepartmentCOfficeUser2').select2({
                                            placeholder: "Select User",
                                            allowClear: true,
                                            width: "100%",
                                            theme: 'classic',
                                            dropdownParent: $('#notificationModal'),
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
                                                        branchType: 1,
                                                        branchId: $(
                                                            '#thisStoreDepartmentCOffice2 option:selected'
                                                        ).val()
                                                    };
                                                },
                                                processResults: function(data, params) {
                                                    params.page = params.page || 1;
                                                    return {
                                                        results: $.map(data.items,
                                                            function(
                                                                item) {
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
                                }
                            });
                        }
                    } else {

                        if ($('#thisStoreDepartmentCOffice2').length > 0) {
                            $('#thisStoreDepartmentCOffice2').select2({
                                placeholder: "Select Department",
                                allowClear: true,
                                width: "100%",
                                theme: 'classic',
                                dropdownParent: $('#notificationModal'),
                                ajax: {
                                    url: "{{ route('departments-list') }}",
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
                                $('#thisStoreDepartmentCOfficeUser2').val(null).trigger('change');
                                $('#thisStoreDepartmentCOfficeUser2').val('');

                                if (!isNaN($('#thisStoreDepartmentCOffice2 option:selected')
                                .val()) && $(
                                        '#thisStoreDepartmentCOffice2 option:selected').val() > 0) {
                                    if ($('#thisStoreDepartmentCOfficeUser2').length > 0) {
                                        $('#thisStoreDepartmentCOfficeUser2').select2({
                                            placeholder: "Select Employee",
                                            allowClear: true,
                                            width: "100%",
                                            theme: 'classic',
                                            dropdownParent: $('#notificationModal'),
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
                                                        branchType: 3,
                                                        branchId: $(
                                                            '#thisStoreDepartmentCOffice2 option:selected'
                                                        ).val()
                                                    };
                                                },
                                                processResults: function(data, params) {
                                                    params.page = params.page || 1;
                                                    return {
                                                        results: $.map(data.items,
                                                            function(
                                                                item) {
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
                                }
                            });
                        }
                    }

                    $('#thisStoreDepartmentCOffice2').val(null).trigger('change');
                    $('#thisStoreDepartmentCOffice2').html('');
                    $('#thisStoreDepartmentCOfficeUser2').val(null).trigger('change');
                    $('#thisStoreDepartmentCOfficeUser2').val('');
                });
                /* Completion JS */

            }
            /* Configuration JS */
        });
    </script>
@endpush
