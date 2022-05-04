<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::get('opcache_reset', function () {
    if (request()->ip() == '127.0.0.1') {
        $status = opcache_reset();

        return response('status ' . $status);
    }

    return response('error');
});
