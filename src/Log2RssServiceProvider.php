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

namespace MadBob\LaravelLog2Rss;

use Log;

use Illuminate\Support\ServiceProvider;

use MadBob\LaravelQueue\Loopback\Connectors\LoopbackConnector;

class Log2RssServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/log2rss.php' => config_path('log2rss.php'),
        ], 'log2rss-config');

        $this->loadRoutesFrom(__DIR__ . '/routes.php');
    }

    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/log2rss.php', 'log2rss'
        );
    }
}
