<aside class="main-sidebar">
    <!-- Brand Logo -->
    <div class="logo" style="display:none;">
        <a href="#" class="brand-link"><img src="{!! url('assets/images/fursaa_newLogo.png') !!}" alt="Fursa Logo" class="img-logo" style="width:220px;"></a>
    </div>
    <h1 class="panel-title">{{strtoupper(auth()->user()->roles[0]->name ?? '')}} PANEL</h1>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <ul class="nav" role="menu">

                @auth

                @if(auth()->user()->can('production-dashboard'))
                <li class="nav-item">
                    <a href="{{ route('production-dashboard') }}" class="nav-link"> Production Dashboard </a>
                </li>
                @endif

                <!-- @if(auth()->user()->can('dom-dashboard'))
                <li class="nav-item">
                    <a href="{{ route('dom-dashboard') }}" class="nav-link"> Dashboard </a>
                </li>
                @endif -->

                <!-- @if(auth()->user()->can('flagged-items-dashboard'))
                <li class="nav-item">
                    <a href="{{ route('flagged-items-dashboard') }}" class="nav-link"> Inspection Dashboard </a>
                </li>
                @endif -->

                @if(auth()->user()->can('document-dashboard'))
                <li class="nav-item">
                    <a href="{{ route('document-dashboard') }}" class="nav-link"> Document Dashboard </a>
                </li>
                @endif

                <!-- @if(auth()->user()->can('monthly-report-dom-checklists'))
                <li class="nav-item">
                    <a href="{{ route('monthly-report-dom-checklists') }}" class="nav-link"> Monthly Reports </a>
                </li>
                @endif -->

                @if(auth()->user()->can('users.index') || auth()->user()->can('roles.index'))
                <li class="nav-item">
                    <a href="#" class="nav-link"> User Management <i class="bi bi-chevron-down"></i></a>
                    <ul class="nav nav-dropdown">
                        @if(auth()->user()->can('users.index'))
                            <li class="nav-item"><a href="{{ route('users.index') }}" class="nav-link"> Users </a></li>
                        @endif

                        @if(auth()->user()->can('roles.index'))
                            <li class="nav-item"><a href="{{ route('roles.index') }}" class="nav-link"> Roles </a></li>
                        @endif
                    </ul>
                </li>
                @endif

                @if(true)
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        Products Management <i class="bi bi-chevron-down"></i>
                    </a>
                    <ul class="nav nav-dropdown">
                        @if(auth()->user()->can('shifts.index'))
                            <li class="nav-item"><a href="{{ route('shifts.index') }}" class="nav-link"> Shifts </a></li>
                        @endif

                        @can('production.category.index')
                            <li class="nav-item">
                                <a href="{{ route('production.categories.index') }}" class="nav-link"> Categories </a>
                            </li>
                        @endcan

                        @can('production.product.index')
                            <li class="nav-item">
                                <a href="{{ route('production.products.index') }}" class="nav-link"> Products </a>
                            </li>
                        @endcan

                        @can('production.uom.index')
                            <li class="nav-item">
                                <a href="{{ route('production.uoms.index') }}" class="nav-link"> UOM </a>
                            </li>
                        @endcan
                        <!-- @can('production.dispatch.index')
                            <li class="nav-item">
                                <a href="{{ route('production.index') }}?dispatch=1" class="nav-link">Dispatch</a>
                            </li>
                        @endcan -->

                    </ul>
                </li>
                @endif

                @if(true)
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        Production Management <i class="bi bi-chevron-down"></i>
                    </a>
                    <ul class="nav nav-dropdown">                       
                        @can('production.planning')
                            <li class="nav-item">
                                <a href="{{ route('production.planning') }}" class="nav-link">Planning</a>
                            </li>
                        @endcan

                        @can('production.index')
                            <li class="nav-item">
                                <a href="{{ route('production.index') }}" class="nav-link">Production</a>
                            </li>
                        @endcan

                        @if(auth()->user()->can('imported-planning-history'))
                            <li class="nav-item"><a href="{{ route('imported-planning-history') }}" class="nav-link"> Order Sheet History </a></li>
                        @endif

                    </ul>
                </li>
                @endif

                @if(auth()->user()->can('stores.index') || auth()->user()->can('corporate-office.index') || auth()->user()->can('departments.index') || auth()->user()->can('store-types.index') || auth()->user()->can('model-types.index'))
                <li class="nav-item">
                    <a href="#" class="nav-link"> Branch Management <i class="bi bi-chevron-down"></i></a>
                    <ul class="nav nav-dropdown">
                        @if( auth()->user()->can( 'store-categories.index' ) )
                            <li class="nav-item"><a href="{{ route( 'store-categories.index' ) }}" class="nav-link">Locations Categories</a></li>
                        @endif

                        @if(auth()->user()->can('store-types.index'))
                            <li class="nav-item"><a href="{{ route('store-types.index') }}" class="nav-link"> Locations Types </a></li>
                        @endif

                        @if(auth()->user()->can('model-types.index'))
                            <li class="nav-item"><a href="{{ route('model-types.index') }}" class="nav-link"> Locations Model Types </a></li>
                        @endif

                        @if(auth()->user()->can('stores.index'))
                            <li class="nav-item"><a href="{{ route('stores.index') }}" class="nav-link"> Locations </a></li>
                        @endif
        
                        @if(auth()->user()->can('corporate-office.index'))
                            <li class="nav-item"><a href="{{ route('corporate-office.index') }}" class="nav-link"> Corporate Offices </a></li>
                        @endif
        
                        @if(auth()->user()->can('departments.index'))
                            <li class="nav-item"><a href="{{ route('departments.index') }}" class="nav-link"> Departments </a></li>
                        @endif

                        @if( auth()->user()->can( 'documents.index' ) )
                            <li class="nav-item"><a href="{{ route( 'documents.index' ) }}" class="nav-link">Documents</a></li>
                        @endif

                        @if( auth()->user()->can( 'document-upload.index' ) )
                            <li class="nav-item"><a href="{{ route( 'document-upload.index' ) }}" class="nav-link">Document Upload</a></li>
                        @endif
                    </ul>
                </li>
                @endif

                <!-- @if(auth()->user()->can('product-categories.index') || auth()->user()->can('products.index'))
                <li class="nav-item">
                    <a href="#" class="nav-link"> Inventory Management <i class="bi bi-chevron-down"></i></a>
                    <ul class="nav nav-dropdown">
                        @if(auth()->user()->can('product-categories.index'))
                            <li class="nav-item"><a href="{{ route('product-categories.index') }}" class="nav-link"> Category </a></li>
                        @endif

                        @if(auth()->user()->can('products.index'))
                            <li class="nav-item"><a href="{{ route('products.index') }}" class="nav-link"> Product </a></li>
                        @endif
                    </ul>
                </li>
                @endif -->

                @if(auth()->user()->can('checklists.index') || auth()->user()->can('checklist-scheduling.index') || auth()->user()->can('scheduled-tasks.index') || auth()->user()->can('reassignments.index') || auth()->user()->can('reschedules'))
                <li class="nav-item">
                    <a href="#" class="nav-link"> Checklist Management <i class="bi bi-chevron-down"></i></a>
                    <ul class="nav nav-dropdown">
                        @if(auth()->user()->can('checklists.index'))
                            <li class="nav-item"><a href="{{ route('checklists.index') }}" class="nav-link"> Checklists Templates </a></li>
                        @endif
        
                        @if(auth()->user()->can('scheduled-tasks.index'))
                            <li class="nav-item"><a href="{{ route('scheduled-tasks.index') }}" class="nav-link"> Scheduled Tasks </a></li>
                        @endif

                        @if(auth()->user()->can('reschedules'))
                            <li class="nav-item"><a href="{{ route('reschedules') }}" class="nav-link"> Rescheduled Tasks </a></li>
                        @endif

                        @if(auth()->user()->can('reassignments.index'))
                            <li class="nav-item"><a href="{{ route('reassignments.index') }}" class="nav-link"> Re-Do </a></li>
                        @endif
                    </ul>
                </li>
                @endif

                @if(auth()->user()->can('workflow-checklists.index') || auth()->user()->can('sections.index') || auth()->user()->can('workflow-templates.index') || auth()->user()->can('workflow-assignments.index') || auth()->user()->can('workflow-assignments.tasks-list'))
                <li class="nav-item">
                    <a href="#" class="nav-link"> Workflow Management <i class="bi bi-chevron-down"></i></a>
                    <ul class="nav nav-dropdown">
                        @if(auth()->user()->can('workflow-checklists.index'))
                            <li class="nav-item"><a href="{{ route('workflow-checklists.index') }}" class="nav-link"> Checklists </a></li>
                        @endif

                        @if(auth()->user()->can('sections.index'))
                            <li class="nav-item"><a href="{{ route('sections.index') }}" class="nav-link"> Sections </a></li>
                        @endif

                        @if(auth()->user()->can('workflow-templates.index'))
                            <li class="nav-item"><a href="{{ route('workflow-templates.index') }}" class="nav-link"> Workflow Templates </a></li>
                        @endif

                        @if(auth()->user()->can('workflow-assignments.index'))
                            <li class="nav-item"><a href="{{ route('workflow-assignments.index') }}" class="nav-link"> Workflow Assignments </a></li>
                        @endif

                        @if(auth()->user()->can('workflow-assignments.tasks-list'))
                            <li class="nav-item"><a href="{{ route('workflow-assignments.tasks-list') }}" class="nav-link"> Workflow Tasks </a></li>
                        @endif
                    </ul>
                </li>
                @endif


                @if(auth()->user()->can('topics.index') || auth()->user()->can('contents.index') || auth()->user()->can('content-analytics'))
                <li class="nav-item">
                    <a href="#" class="nav-link"> Learning Management <i class="bi bi-chevron-down"></i></a>
                    <ul class="nav nav-dropdown">
                        @if(auth()->user()->can('topics.index'))
                            <li class="nav-item"><a href="{{ route('topics.index') }}" class="nav-link"> Categories </a></li>
                        @endif

                        @if(auth()->user()->can('contents.index'))
                            <li class="nav-item"><a href="{{ route('contents.index') }}" class="nav-link"> Content </a></li>
                        @endif

                        @if(auth()->user()->can('content-analytics'))
                            <li class="nav-item"><a href="{{ route('content-analytics') }}" class="nav-link"> View Analytics </a></li>
                        @endif
                    </ul>
                </li>
                @endif


                @if(1)
                <li class="nav-item">
                    <a href="#" class="nav-link"> Ticket System <i class="bi bi-chevron-down"></i></a>
                    <ul class="nav nav-dropdown">
                        @if(1)
                            <li class="nav-item"><a href="{{ url('ticket-system/tickets') }}" class="nav-link"> Tickets </a></li>
                        @endif

                        @if(1)
                            <li class="nav-item"><a href="{{ url('ticket-system/tickets-admin/priority') }}" class="nav-link"> Ticket Priorities </a></li>
                        @endif

                        @if(1)
                            <li class="nav-item"><a href="{{ url('ticket-system/tickets-admin/status') }}" class="nav-link"> Ticket Statuses </a></li>
                        @endif
                    </ul>
                </li>
                @endif

                @if(auth()->user()->can('notification-templates.index') || auth()->user()->can('imported-schedulings-history'))
                <li class="nav-item">
                    <a href="#" class="nav-link"> Settings <i class="bi bi-chevron-down"></i></a>
                    <ul class="nav nav-dropdown">
                        @if(auth()->user()->can('notification-templates.index'))
                            <li class="nav-item"><a href="{{ route('notification-templates.index') }}" class="nav-link"> Notification Templates </a></li>
                        @endif

                        @if(auth()->user()->can('imported-schedulings-history'))
                            <li class="nav-item"><a href="{{ route('imported-schedulings-history') }}" class="nav-link"> XLSX Import History </a></li>
                        @endif

                        @if(1)
                            <li class="nav-item"><a href="{{ route('settings.edit') }}" class="nav-link"> Settings </a></li>
                        @endif
                    </ul>
                </li>
                @endif

                <li class="nav-item">
                    <ul class="nav nav-dropdown">
                        <li class="nav-item"><a href="{{ route('logout') }}" class="nav-link">Logout</a></li>
                    </ul>
                </li>
                @endauth

            </ul>
        </nav>
        
        <div class="version"><img src="{!! url('assets/images/version.svg') !!}"> VERSION 1.0.0</div>
        <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
</aside>
