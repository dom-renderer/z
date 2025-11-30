<?php

use Illuminate\Support\Facades\Route;
use App\Models\TicketSetting;

    $main_route = TicketSetting::grab('main_route');
    $main_route_path = TicketSetting::grab('main_route_path');
    $admin_route = TicketSetting::grab('admin_route');
    $admin_route_path = TicketSetting::grab('admin_route_path');

    Route::group(['prefix' => "ticket-system", 'middleware' => "permission:Ticket System"], function () use ($main_route, $main_route_path, $admin_route, $admin_route_path) {

        Route::get("$main_route_path/complete", 'App\Http\Controllers\TicketsController@indexComplete')
            ->name("$main_route-complete");
        Route::get("$main_route_path/data/{id?}", 'App\Http\Controllers\TicketsController@data')
            ->name("$main_route.data");

        $field_name = last(explode('/', $main_route_path));
        Route::resource($main_route_path, 'App\Http\Controllers\TicketsController', [
            'names' => [
                    'index'   => $main_route.'.index',
                    'store'   => $main_route.'.store',
                    'create'  => $main_route.'.create',
                    'update'  => $main_route.'.update',
                    'show'    => $main_route.'.show',
                    'destroy' => $main_route.'.destroy',
                    'edit'    => $main_route.'.edit',
            ],
            'parameters' => [
                $field_name => 'ticket',
            ],
        ]);

        $field_name = last(explode('/', "$main_route_path-comment"));
        Route::resource("$main_route_path-comment", 'App\Http\Controllers\CommentsController', [
            'names' => [
                'index'   => "$main_route-comment.index",
                'store'   => "$main_route-comment.store",
                'create'  => "$main_route-comment.create",
                'update'  => "$main_route-comment.update",
                'show'    => "$main_route-comment.show",
                'destroy' => "$main_route-comment.destroy",
                'edit'    => "$main_route-comment.edit",
            ],
            'parameters' => [
                $field_name => 'ticket_comment',
            ],
        ]);

        Route::get("$main_route_path/{id}/complete", 'App\Http\Controllers\TicketsController@complete')
            ->name("$main_route.complete");

        Route::post("$main_route_path/data/get_admin_agent", 'App\Http\Controllers\TicketsController@get_admin_agent')
            ->name("$main_route.get_admin_agent");

        Route::get("$main_route_path/{id}/reopen", 'App\Http\Controllers\TicketsController@reopen')
            ->name("$main_route.reopen");

        Route::group(['middleware' => 'App\Http\Middleware\IsAgentMiddleware'], function () use ($main_route, $main_route_path) {

            Route::get("$main_route_path/agents/list/{category_id?}/{ticket_id?}", [
                'as'   => $main_route.'agentselectlist',
                'uses' => 'App\Http\Controllers\TicketsController@agentSelectList',
            ]);
        });

        Route::group(['middleware' => 'App\Http\Middleware\IsAdminMiddleware'], function () use ($admin_route, $admin_route_path) {
            Route::get("$admin_route_path/indicator/{indicator_period?}", [
                    'as'   => $admin_route.'.dashboard.indicator',
                'uses' => 'App\Http\Controllers\DashboardController@index',
            ]);
            Route::get($admin_route_path, 'App\Http\Controllers\DashboardController@index');

            Route::resource("$admin_route_path/status", 'App\Http\Controllers\StatusesController', [
                'names' => [
                    'index'   => "$admin_route.status.index",
                    'store'   => "$admin_route.status.store",
                    'create'  => "$admin_route.status.create",
                    'update'  => "$admin_route.status.update",
                    'show'    => "$admin_route.status.show",
                    'destroy' => "$admin_route.status.destroy",
                    'edit'    => "$admin_route.status.edit",
                ],
            ]);

             Route::resource("$admin_route_path/priority", 'App\Http\Controllers\PrioritiesController', [
                 'names' => [
                     'index'   => "$admin_route.priority.index",
                     'store'   => "$admin_route.priority.store",
                     'create'  => "$admin_route.priority.create",
                     'update'  => "$admin_route.priority.update",
                     'show'    => "$admin_route.priority.show",
                     'destroy' => "$admin_route.priority.destroy",
                     'edit'    => "$admin_route.priority.edit",
                 ],
             ]);

            Route::resource("$admin_route_path/agent", 'App\Http\Controllers\AgentsController', [
                'names' => [
                    'index'   => "$admin_route.agent.index",
                    'store'   => "$admin_route.agent.store",
                    'create'  => "$admin_route.agent.create",
                    'update'  => "$admin_route.agent.update",
                    'show'    => "$admin_route.agent.show",
                    'destroy' => "$admin_route.agent.destroy",
                    'edit'    => "$admin_route.agent.edit",
                ],
            ]);

            Route::resource("$admin_route_path/category", 'App\Http\Controllers\CategoriesController', [
                'names' => [
                    'index'   => "$admin_route.category.index",
                    'store'   => "$admin_route.category.store",
                    'create'  => "$admin_route.category.create",
                    'update'  => "$admin_route.category.update",
                    'show'    => "$admin_route.category.show",
                    'destroy' => "$admin_route.category.destroy",
                    'edit'    => "$admin_route.category.edit",
                ],
            ]);

            Route::resource("$admin_route_path/configuration", 'App\Http\Controllers\ConfigurationsController', [
                'names' => [
                    'index'   => "$admin_route.configuration.index",
                    'store'   => "$admin_route.configuration.store",
                    'create'  => "$admin_route.configuration.create",
                    'update'  => "$admin_route.configuration.update",
                    'show'    => "$admin_route.configuration.show",
                    'destroy' => "$admin_route.configuration.destroy",
                    'edit'    => "$admin_route.configuration.edit",
                ],
            ]);

            Route::resource("$admin_route_path/administrator", 'App\Http\Controllers\AdministratorsController', [
                'names' => [
                    'index'   => "$admin_route.administrator.index",
                    'store'   => "$admin_route.administrator.store",
                    'create'  => "$admin_route.administrator.create",
                    'update'  => "$admin_route.administrator.update",
                    'show'    => "$admin_route.administrator.show",
                    'destroy' => "$admin_route.administrator.destroy",
                    'edit'    => "$admin_route.administrator.edit",
                ],
            ]);
        });
        Route::post('add-estimatetime',[App\Http\Controllers\TicketsController::class, 'add_estimatetime'])->name('tickets.add-estimatetime');
    });
