<?php
// app/Http/Middleware/ApplyTheme.php

namespace App\Http\Middleware;

use App\Models\UserTheme;
use App\Models\ThemeTemplate;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApplyTheme
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()) {
            $theme = UserTheme::with('template')
                ->firstOrCreate(
                    ['user_id' => $request->user()->id],
                    ['theme_template_id' => ThemeTemplate::where('is_default', true)->value('id')]
                );

            $colors = $theme->is_custom 
                ? $theme->custom_colors 
                : ($theme->template ? $theme->template->colors : null);

            if ($colors) {
                view()->share('themeColors', $colors);
            }
        }

        return $next($request);
    }
}