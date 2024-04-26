<?php
namespace AntonioPrimera\LaravelJsLocalization\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        app()->setLocale(session()->get('locale', config('app.locale')));
        return $next($request);
    }
}
