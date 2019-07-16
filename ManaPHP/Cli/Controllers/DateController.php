<?php

namespace ManaPHP\Cli\Controllers;

use ManaPHP\Cli\Controller;

class DateController extends Controller
{
    /**
     * @param string $url
     * @param bool   $onlyOnce
     *
     * @return int|false
     */
    protected function _getRemoteTimestamp($url, $onlyOnce = false)
    {
        if (strpos($url, '://') === false) {
            $url = 'http://' . $url;
        }

        $prev_timestamp = 0;
        do {
            try {
                $timestamp = strtotime($this->httpClient->head($url)->getHeaders()['Date']);
            } catch (\Exception $exception) {
                return false;
            }

            if ($prev_timestamp !== 0 && $prev_timestamp !== $timestamp) {
                break;
            }

            $prev_timestamp = $timestamp;
        } while (!$onlyOnce);

        return $timestamp;
    }

    /**
     * sync system time with http server time clock
     *
     * @param string $url the time original
     *
     * @return  int
     */
    public function syncCommand($url = 'http://www.baidu.com')
    {
        $timestamp = $this->_getRemoteTimestamp($url);
        if ($timestamp === false) {
            return $this->console->error(['fetch remote timestamp failed: `:url`', 'url' => $url]);
        } else {
            $this->_updateDate($timestamp);
            $this->console->write(date('Y-m-d H:i:s'));
            return 0;
        }
    }

    /**
     * show remote time
     *
     * @param string $url the time original
     *
     * @return int
     */
    public function remoteCommand($url = 'http://www.baidu.com')
    {
        $timestamp = $this->_getRemoteTimestamp($url);
        if ($timestamp === false) {
            return $this->console->error(['fetch remote timestamp failed: `:url`', 'url' => $url]);
        } else {
            $this->console->writeLn(date('Y-m-d H:i:s', $timestamp));
            return 0;
        }
    }

    /**
     *  show local and remote diff
     *
     * @param string $url the time original
     *
     * @return string
     */
    public function diffCommand($url = 'http://www.baidu.com')
    {
        $remote_ts = $this->_getRemoteTimestamp($url);
        $local_ts = time();
        if ($remote_ts === false) {
            return $this->console->error(['fetch remote timestamp failed: `:url`', 'url' => $url]);
        } else {
            $this->console->writeLn(' local: ' . date('Y-m-d H:i:s', $local_ts));
            $this->console->writeLn('remote: ' . date('Y-m-d H:i:s', $remote_ts));
            $this->console->writeLn('  diff: ' . ($local_ts - $remote_ts));
            return 0;
        }
    }

    /**
     * set the system time
     *
     * @param string $time time
     * @param string $date date
     *
     * @return int
     */
    public function setCommand($date, $time)
    {
        $arguments = $this->request->getValues();
        if (count($arguments) === 1) {
            $argument = $arguments[0];
            if ($argument[0] === 't') {
                $date = '';
                $time = substr($argument, 1);
            } else {
                $str = trim(strtr($argument, 'Tt', '  '));
                if (strpos($str, ' ') === false) {
                    if (strpos($str, ':') !== false) {
                        $date = '';
                        $time = $str;
                    } else {
                        $date = $str;
                        $time = '';
                    }
                } else {
                    list($date, $time) = explode(' ', $str);
                }
            }
        }

        $date = $date ? strtr($date, '/', '-') : (string)date('Y-m-d');
        $date = trim(trim($date), '-');

        switch (substr_count($date, '-')) {
            case 0:
                $date = date('Y-m-') . $date;
                break;
            case 1:
                $date = date('Y-') . $date;
                break;
        }
        $parts = explode('-', $date);
        $parts[0] = substr(date('Y'), 0, 4 - strlen($parts[0])) . $parts[0];
        $date = $parts[0] . '-' . str_pad($parts[1], 2, '0', STR_PAD_LEFT) . '-' . str_pad($parts[2], 2, '0', STR_PAD_LEFT);

        $time = $time ? trim($time) : (string)date('H:i:s');
        if ($time[0] === ':') {
            $time = date('H') . $time;
        }
        switch (substr_count($time, ':')) {
            case 0:
                $time .= date(':i:s');
                break;
            case 1:
                $time .= date(':s');
                break;
        }
        $parts = explode(':', $time);

        $time = str_pad($parts[0], 2, '0', STR_PAD_LEFT) . ':' . str_pad($parts[1], 2, '0', STR_PAD_LEFT) . ':' . str_pad($parts[2], 2, '0', STR_PAD_LEFT);

        $str = $date . ' ' . $time;
        $timestamp = strtotime($str);
        if ($timestamp === false || preg_match('#^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$#', $str) !== 1) {
            return $this->console->error(['`:time` time format is invalid', 'time' => $str]);
        } else {
            $this->_updateDate($timestamp);
            $this->console->writeLn(date('Y-m-d H:i:s'));

            return 0;
        }
    }

    /**
     * @param int $timestamp
     */
    protected function _updateDate($timestamp)
    {
        if (DIRECTORY_SEPARATOR === '\\') {
            system('date ' . date('Y-m-d', $timestamp));
            system('time ' . date('H:i:s', $timestamp));
        } else {
            system('date --set "' . date('Y-m-d H:i:s', $timestamp) . '"');
        }
    }
}