<?php

namespace MadBob\LaravelLog2Rss;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;

use App;

class Log2RssController extends Controller
{
    /*
        Most of the following code:
        Copyright (C) 2017 Raphaël Huchet
        https://github.com/rap2hpoutre/laravel-log-viewer
    */
    private static function all()
    {
        $log = array();

        $files = glob(storage_path() . '/logs/*.log');
        if (empty($files))
            return $logs;

        $files = array_reverse($files);
        $files = array_filter($files, 'is_file');
        $target_file = $files[0];
        $file = app('files')->get($target_file);

        $pattern = '/\[\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}([\+-]\d{4})?\].*/';
        preg_match_all($pattern, $file, $headings);
        if (!is_array($headings))
            return $log;

        $log_data = preg_split($pattern, $file);
        if ($log_data[0] < 1) {
            array_shift($log_data);
        }

        foreach ($headings as $h) {
            for ($i = 0, $j = count($h); $i < $j; $i++) {
                preg_match('/^\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}([\+-]\d{4})?)\](?:.*?(\w+)\.|.*?\w+): (.*?)( in .*?:[0-9]+)?$/i', $h[$i], $current);
                if (!isset($current[4]))
                    continue;

                $log[] = array(
                    'context' => $current[3],
                    'date' => $current[1],
                    'text' => $current[4],
                    'in_file' => isset($current[5]) ? $current[5] : null,
                    'stack' => preg_replace("/^\n*/", '', $log_data[$i])
                );
            }
        }

        return array_reverse($log);
    }

    public function index(Request $request)
    {
        $logs = self::all();

        $feed = App::make("feed");
        $feed->title = 'Logs from  ' . env('APP_NAME');
        $feed->description = 'Logs from  ' . env('APP_NAME');
        $feed->link = $request->url();
        $feed->setDateFormat('datetime');

        if (!empty($logs) > 0)
            $feed->pubdate = $logs[0]['date'];
        else
            $feed->pubdate = '1970-01-01 00:00:00';

        for($i = 0; $i < count($logs) && $i < 20; $i++) {
            $l = $logs[$i];
            $feed->add($l['text'], env('APP_NAME'), '', $l['date'], nl2br($l['stack']), '');
        }

        return $feed->render('rss');
    }
}
