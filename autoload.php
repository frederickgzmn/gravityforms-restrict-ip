<?php
declare(strict_types=1);
/**
 * Anonymous function that registers a custom autoloader
 * @param string $prefix
 * @param string $baseDir
 */
return function (string $prefix, string $baseDir) {
    spl_autoload_register(function (string $class) use ($prefix, $baseDir) {
        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) !== 0) {
            return;
        }

        $relativeClass = substr($class, $len);
        $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
        if (file_exists($file)) {
            require $file;
        }
    });
};
