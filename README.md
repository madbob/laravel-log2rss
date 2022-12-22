Laravel Log2Rss
===============

This package generates an handy RSS feed from the Laravel logs.

Under the hood, extends Raphaël Huchet's [Laravel Log Viewer](https://github.com/rap2hpoutre/laravel-log-viewer) to extract logs from filesystem.

# Installation

```
composer require madbob/laravel-log2rss
php artisan vendor:publish --tag=log2rss-config
```

By defalt, the log is published in yourbaseurl.com/logs (is it suggested to include a prefix to your route, see below).

# Usage

In the `config/log2rss.php` file you find a few options.

* middleware: array of middleware groups to handle the RSS requests. You can use this to protect the route, enforce some cache or whatever. Default: [] (empty)
* prefix: by default the URL to which RSS is published is `/logs`, and you can here define a prefix for it (perhaps some random string, or even a substring of `env('APP_KEY')`, to hide the path and make it a little less accessible even without having to implement authenticated access in a middleware). Default: '' (empty)
* log_level: minimum log level for the items to include into the feed. Default: warning
* limit: limit of items to include into the feed. Default: 20

Please note this package doesn't performs well with Laravel's `single` logging channel, as it generates files too large to be handled and are rejected by [Laravel Log Viewer](https://github.com/rap2hpoutre/laravel-log-viewer). The adoption of `daily` channel is suggested.

# License

This code is free software, licensed under the The GNU General Public License version 3 (GPLv3). See the LICENSE.md file for more details.

Copyright (C) 2022 Roberto Guido <bob@linux.it>
