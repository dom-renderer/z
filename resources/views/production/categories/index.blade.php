@extends('layouts.app-master')

@push('css')
<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css"/>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.5/css/dataTables.bootstrap5.min.css"/>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endpush

@section('content')

    <div class="bg-light p-4 rounded">
        <h1>{{ $page_title }} </h1>
        <div class="lead">
            {{ $page_description }}
            @if (auth()->user()->can('production.categories.create'))
                <a href="{{ route('production.categories.create') }}" class="btn btn-primary btn-sm float-end">Add Category</a>
            @endif
        </div>
        
        <div class="mt-2">
            @include('layouts.partials.messages')
        </div>

        <div class="tab-content" id="myTabContent">
            <div class="tab-pane fade show active" id="users-tab-pane" role="tabpanel" aria-labelledby="users-tab" tabindex="0">
                <table class="table table-striped" id="category-table" cellspacing="0" width="100%">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Parent</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
        

    </div> 
@endsection


@push('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.5/js/dataTables.bootstrap5.min.js"></script>

<script>
    
    $(document).ready(function($){

        $(document).on('click', '.status-changer', function(e) {
            e.preventDefault();
            Swal.fire({
                title: $(this).attr('data-description'),
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: $(this).attr('data-blable')
            }).then((result) => {
                if (result.isConfirmed) {
                    $(this).parents('form').submit();
                    return true;
                } else {
                    return false;
                }
            })
        });

        $(document).on('click', '.deleteGroup', function(e) {
            e.preventDefault();
            Swal.fire({
                title: 'Are you sure you want to delete this Category?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $(this).parents('form').submit();
                    return true;
                } else {
                    return false;
                }
            })
        });


        let usersTable = new DataTable('#category-table', {
            "dom": '<"d-flex justify-content-between mb-2"<"user-role-table-filter-container">f>rt<"d-flex flex-column float-start mt-3"pi><"clear">',
            ajax: {
                url: "{{ route('production.categories.index') }}",
                data: function ( d ) {
                    return $.extend( {}, d, {

                    });
                }
            },
            processing: false,
            ordering: false,
            serverSide: true,
            columns: [
                 { data: 'id', searchable: false },
                 { data: 'name' },
                 { data: 'parentcat', searchable: false },
                 { data: 'status'},
                 { data: 'action', searchable: false }
            ],
            initComplete: function(settings) {

            }
        });
        
    });
 </script>  
@endpush