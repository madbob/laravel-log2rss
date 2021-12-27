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

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;

use Rap2hpoutre\LaravelLogViewer\LaravelLogViewer;

use App;

class Log2RssController extends Controller
{
    private function initFeed()
    {
        $feed = App::make("feed");
        $feed->title = 'Logs from  ' . config('app.name');
        $feed->description = 'Logs from  ' . config('app.name');
        $feed->link = route('log2rss.index');
        $feed->setDateFormat('datetime');
        return $feed;
    }

    public function index(Request $request)
    {
        $feed = $this->initFeed();

        /*
            Those must be kept in order of priority
        */
        $levels = ['emergency', 'alert', 'critical', 'error', 'warning', 'notice', 'info', 'debug'];
        $log_level = config('log2rss.log_level');
        $log_level_int = array_search($log_level, $levels);
        if ($log_level_int === false) {
            throw new \Exception("Invalid log level provided for Log2RSS: " . $log_level, 1);
        }

        $log_viewer = new LaravelLogViewer();
        $files = $log_viewer->getFiles();
        rsort($files);

        $limit = config('log2rss.limit');
        $total_added = 0;
        $latest_date = null;

        foreach($files as $file) {
            $log_viewer->setFile($file);
            $logs = $log_viewer->all();

            for ($i = 0; $i < count($logs) && $total_added < $limit; $i++) {
                $line = $logs[$i];

                $line_log_level = array_search($line['level'], $levels);
                if ($line_log_level > $log_level_int) {
                    continue;
                }

                $date = date('r', strtotime($line['date']));

                if (is_null($latest_date)) {
                    $latest_date = $date;
                }

                $feed->addItem([
                    'title' => sprintf('%s - %s...', $line['level'], substr($line['text'], 0, 100)),
                    'author' => config('app.name'),
                    'link' => '',
                    'pubdate' => $date,
                    'description' => $line['text'] . "\n" . nl2br($line['stack']),
                ]);

                $total_added++;
            }

            if ($total_added >= $limit) {
                break;
            }
        }

        if ($latest_date) {
            $feed->pubdate = date('r', $latest_date);
        }

        return $feed->render('rss');
    }
}
