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


## Change log

2020-06-17, v 1.0.1:

- On CLI, duplicate output to stderr.
