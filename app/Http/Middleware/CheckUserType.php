<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CheckUserType
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure(Request): (Response|RedirectResponse) $next
     * @param mixed ...$disallowedUserTypes
     * @return Response|RedirectResponse
     */
    public function handle(Request $request, Closure $next, ...$disallowedUserTypes)
    {
        $userType = $request->user()->user_type;

        // Check if the user's type is within the disallowed array
        if (in_array($userType, $disallowedUserTypes)) {
            // Redirect or return error response
            abort(403, 'Unauthorized action.');
            //return redirect()->route('home')->with('error', 'Access denied.');
        }

        return $next($request);
    }


//    public function handle($request, Closure $next, ...$disallowedUserTypes)
//    {
//        $userType = $request->user()->user_type;
//
//        // Check if the user's type is within the disallowed array
//        if (in_array($userType, $disallowedUserTypes)) {
//            // Redirect or return error response
//            return redirect()->route('home')->with('error', 'Access denied.');
//        }
//
//        return $next($request);
//    }
}
