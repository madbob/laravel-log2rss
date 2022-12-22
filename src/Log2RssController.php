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
use Illuminate\Support\Facades\Log;

use Rap2hpoutre\LaravelLogViewer\LaravelLogViewer;
use Carbon\Carbon;
use FeedIo\Factory\Builder\GuzzleClientBuilder;
use FeedIo\FeedIo;
use FeedIo\Feed;
use FeedIo\Feed\Item\Author;

class Log2RssController extends Controller
{
    private function initFeed()
    {
        $feed = new Feed();
        $feed->setTitle('Logs from  ' . config('app.name'));
        $feed->setDescription('Logs from  ' . config('app.name'));
        $feed->setLink(route('log2rss.index'));
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

        $author = new Author();
        $author->setName(config('app.name'));

        foreach($files as $file) {
            $log_viewer->setFile($file);
            $logs = $log_viewer->all();

            for ($i = 0; $i < count($logs) && $total_added < $limit; $i++) {
                $line = $logs[$i];

                $line_log_level = array_search($line['level'], $levels);
                if ($line_log_level > $log_level_int) {
                    continue;
                }

                $date = Carbon::parse($line['date']);
                $identifier = md5($line['date'] . $line['text']);

                if (is_null($latest_date)) {
                    $latest_date = $date;
                }

				$item = $feed->newItem();

                $item->setTitle(sprintf('%s - %s...', $line['level'], substr($line['text'], 0, 100)));
                $item->setAuthor($author);

                /*
                    A unique link is required to enforce RSS feed readers to
                    handle individually each item. But not a random one,
                    otherwise to each update all existing items are handled
                    as completely new ones (resulting in duplications): a
                    reproducible MD5 hash is used
                */
                $item->setLink(url('/') . '?' . $identifier);

                $item->setLastModified($date);
                $item->setContent($line['text'] . "\n\n" . $line['stack']);

                $feed->add($item);
                $total_added++;
            }

            if ($total_added >= $limit) {
                break;
            }
        }

        if ($latest_date) {
            $feed->setLastModified($latest_date);
        }

        $feedIo = new FeedIo((new GuzzleClientBuilder())->getClient(), Log::getLogger());
        return $feedIo->getPsrResponse($feed, 'rss');
    }
}
