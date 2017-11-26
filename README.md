Laravel Log2Rss
===============

This package generates an handy RSS feed from the Laravel logs.

Largely inspired from Raphaël Huchet's [Laravel Log Viewer](https://github.com/rap2hpoutre/laravel-log-viewer).

# Installation

`composer require madbob/laravel-log2rss`

# Usage

Include a new route pointing directly to the main controller of this package:

```
Route::get('logs', '\MadBob\LaravelLog2Rss\Log2RssController@index');
```

It may be a good idea to hide this path to limit public access. Eventually, use the latest N characters from your unique app key:

```
Route::get(substr(env('APP_KEY'), -5) . '/logs', '\MadBob\LaravelLog2Rss\Log2RssController@index');
```

# License

This code is free software, licensed under the The GNU General Public License version 3 (GPLv3). See the LICENSE.md file for more details.

Copyright (C) 2017 Roberto Guido <bob@linux.it>
