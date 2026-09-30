<?php

namespace Pterodactyl\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Foundation\Application;

class LanguageMiddleware
{
    /**
     * LanguageMiddleware constructor.
     */
    public function __construct(private Application $app)
    {
    }

    /**
     * Handle an incoming request and set the user's preferred language.
     */
    public function handle(Request $request, \Closure $next): mixed
    {
        // setLocale() overwrites config('app.locale'), so remember the panel-wide default before it is replaced.
        if (config('app.default_locale') === null) {
            config(['app.default_locale' => config('app.locale', 'en')]);
        }

        $this->app->setLocale(optional($request->user())->language ?: config('app.default_locale'));

        return $next($request);
    }
}
