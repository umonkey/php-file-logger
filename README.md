# Simple PSR-3 compliant file logger

Component for use in personal projects.

Installation:

```
$ composer require umonkey/php-file-logger
```

Usage:

```
$logger = new Umonkey\Logger\FileLogger([
    'path' => __DIR__ . '/../var/app-%Y%m%d-%H.log',
    // Optional:
    'symlink' => __DIR__ . '/../var/app.log',
    'keep_files' => 7,  // defaults to 10
    'mode' => 0664,  // defaults to 0640
]);

$logger->info('Hello, world.');
```


## Change log

2020-07-07, v 1.1.0:

- Rotate log files, set permissions, create symlinks.

2020-06-17, v 1.0.1:

- On CLI, duplicate output to stderr.


## TODO

- [x] Log rotation.
- [ ] Keep session logs in a buffer.
