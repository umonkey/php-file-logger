# Simple PSR-3 compliant file logger

Component for use in personal projects.

Installation:

```
$ composer require umonkey/php-file-logger
```

Usage:

```
$logger = new Umonkey\Logger\FileLogger([
    'path' => __DIR__ . '/var/app-%Y%m%d-%H.log',
    'symlink' => __DIR__ . '/var/app.log',
]);

$logger->info('Hello, world.');
```
