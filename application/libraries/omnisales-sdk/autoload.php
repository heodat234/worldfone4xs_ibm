<?php
function my_app_autoloader($class){
    $root = __DIR__.'/';//'/var/www/omnisales/portal/application/libraries/omnisales-sdk/';
    $prefix = 'Omnisales\\';

    $classWithoutPrefix = preg_replace('/^' . preg_quote($prefix) . '/', '', $class);
    $file = str_replace('\\', DIRECTORY_SEPARATOR, $classWithoutPrefix) . '.php';
    $path = $root . $file;
    if (file_exists($path)) {
        require_once $path;
    }
}

spl_autoload_register('my_app_autoloader');
