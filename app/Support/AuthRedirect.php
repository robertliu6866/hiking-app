<?php

namespace App\Support;

use Illuminate\Http\Request;

class AuthRedirect
{
    public static function remember(Request $request): void
    {
        $target = $request->query('redirect_to');
        if (! is_string($target) || preg_match('/[\x00-\x20\x7f\\\\]/', $target)) {
            return;
        }

        // Require an exact origin boundary, rather than a matching hostname prefix.
        $origin = rtrim(url('/'), '/');
        if ((str_starts_with($target, '/') && ! str_starts_with($target, '//'))
            || str_starts_with($target, $origin.'/')
            || str_starts_with($target, $origin.'?')
            || str_starts_with($target, $origin.'#')
            || $target === $origin) {
            $request->session()->put('url.intended', $target);
        }
    }
}
