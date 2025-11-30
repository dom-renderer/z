<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Agent;
use App\Models\TicketSetting;

class IsAgentMiddleware
{
    /**
     * Run the request filter.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (Agent::isAgent() || Agent::isTicketAdmin()) {
            return $next($request);
        }

        return redirect()->route(TicketSetting::grab('main_route'). '.index')
            ->with('warning', 'You are not permitted to do this action.');
    }
}
