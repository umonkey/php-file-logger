<?php

/**
 * Simple PSR-3 compatible logger.
 *
 * Configuration stays in the 'logger' block.
 **/

declare(strict_types=1);

namespace Umonkey\Logger;

use Psr\Log\LoggerInterface;
use Psr\Log\InvalidArgumentException;

class FileLogger implements LoggerInterface
{
    /**
     * @var string
     **/
    protected $config = [];

    /**
     * Message count.
     *
     * @var int
     **/
    protected $count = 0;

    /**
     * Startup time.
     *
     * @var float
     **/
    protected $startup = null;

    public function __construct(array $config)
    {
        $this->config = array_replace([
            'path' => null,
            'symlink' => null,
            'keep_files' => 7,
            'mode' => 0640,
        ], $config);

        $this->startup = defined("STARTUP_TS") ? STARTUP_TS : microtime(true);
    }

    public function emergency($message, array $context = []): void
    {
        $this->log("EMG", $message, $context);
    }

    public function alert($message, array $context = []): void
    {
        $this->log("ALR", $message, $context);
    }

    public function critical($message, array $context = []): void
    {
        $this->log("CRI", $message, $context);
    }

    public function error($message, array $context = []): void
    {
        $this->log("ERR", $message, $context);
    }

    public function warning($message, array $context = []): void
    {
        $this->log("WRN", $message, $context);
    }

    public function notice($message, array $context = []): void
    {
        $this->log("NOT", $message, $context);
    }

    public function info($message, array $context = []): void
    {
        $this->log("INF", $message, $context);
    }

    public function debug($message, array $context = []): void
    {
        $this->log("DBG", $message, $context);
    }

    public function log($level, $message, array $context = []): void
    {
        $repl = [];
        foreach ($context as $k => $v) {
            $k = '{' . $k . '}';
            if (is_array($v)) {
                $v = json_encode($v, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            }
            $repl[$k] = $v;
        }

        $message = strtr($message, $repl);

        $prefix = sprintf("%s %06.2f %04u %s: ", strftime("%Y-%m-%d %H:%M:%S"), microtime(true) - $this->startup, ++$this->count, $level);

        if (!empty($this->config["path"])) {
            $this->logToFile($this->config["path"], $prefix, $message);
        } else {
            $this->logToStderr($prefix, $message);
        }
    }

    protected function logToFile($fn, $prefix, $message): void
    {
        $now = time();
        $fn = strtr($fn, [
            "%Y" => strftime("%Y", $now),
            "%m" => strftime("%m", $now),
            "%d" => strftime("%d", $now),
            "%H" => strftime("%H", $now),
        ]);

        if (file_exists($fn) and is_writable($fn)) {
        } elseif (!is_dir($dir = dirname($fn))) {
            throw new \RuntimeException("log dir {$dir} does not exist");
        } elseif (!is_writable($dir)) {
            throw new \RuntimeException("log dir {$dir} is not writable");
        }

        $text = "";
        foreach (explode("\n", rtrim($message)) as $line) {
            $text .= $prefix . rtrim($line) . PHP_EOL;
        }

        $umask = umask(0117);

        $isNew = !file_exists($fn);

        $fp = fopen($fn, "a");
        if ($fp === false) {
            throw new \RuntimeException("could not open log file {$fn} for writing");
        }

        umask($umask);

        if (flock($fp, LOCK_EX)) {
            chmod($fn, $this->config['mode']);
            fwrite($fp, $text);
            fflush($fp);
            flock($fp, LOCK_UN);
            fclose($fp);
        } else {
            fclose($f);
            throw new \RuntimeException("could not lock the log file {$fn} for writing");
        }

        if (php_sapi_name() === 'cli') {
            fwrite(STDERR, $text);
        }

        if ($isNew && !empty($this->config["symlink"])) {
            $this->cleanUpFiles();

            if (!file_exists($link = $this->config["symlink"])) {
                symlink(realpath($fn), $link);
            } elseif (realpath(readlink($link)) != realpath($fn)) {
                unlink($link);
                symlink(realpath($fn), $link);
            }
        }
    }

    protected function logToStderr($prefix, $message): void
    {
        $text = "";
        foreach (explode("\n", rtrim($message)) as $line) {
            $text .= $prefix . rtrim($line) . PHP_EOL;
        }

        error_log($text);
    }

    /**
     * Delete old files.
     **/
    protected function cleanUpFiles(): void
    {
        $pattern = $this->config['path'];
        $pattern = preg_replace('@(%.)+@', '*', $pattern);

        $limit = $this->config['keep_files'];

        $files = glob($pattern);
        if (count($files) > $limit) {
            $kill = array_slice($files, 0, -$limit);
            foreach ($kill as $fn) {
                unlink($fn);
            }
        }
    }
}
