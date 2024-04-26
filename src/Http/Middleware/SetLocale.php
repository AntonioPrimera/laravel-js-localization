<?php
namespace AntonioPrimera\LaravelJsLocalization\Http\Middleware;

use AntonioPrimera\LaravelJsLocalization\Facades\LocaleManager;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;


class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        //app()->setLocale(session()->get('locale', config('app.locale')));
		app()->setLocale(LocaleManager::getSessionLocale());
        return $next($request);
    }
}
