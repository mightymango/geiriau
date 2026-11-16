<?php

namespace App\Http\Middleware;

use App\Services\StudyPlanner;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOnboarded
{
    public function __construct(private StudyPlanner $planner) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->planner->isOnboarded()) {
            return redirect('/welcome');
        }

        return $next($request);
    }
}
