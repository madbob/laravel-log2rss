<?php

/*
 * laravel-log2rss - Laravel logs accessible in RSS
 * Copyright (C) 2021  Roberto Guido
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

$middleware = config('log2rss.middleware', []);

Route::middleware($middleware)->group(function () {
    $prefix = config('log2rss.prefix', '');
    Route::get($prefix . '/logs', [\MadBob\LaravelLog2Rss\Log2RssController::class, 'index'])->name('log2rss.index');
});
