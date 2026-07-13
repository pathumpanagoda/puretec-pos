<?php

/*
|--------------------------------------------------------------------------
| Create The Application
|--------------------------------------------------------------------------
*/

$app = new Illuminate\Foundation\Application(
    $_ENV['APP_BASE_PATH'] ?? dirname(__DIR__)
);

if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
    $app->addAbsoluteCachePathPrefix(substr(__DIR__, 0, 2));
    foreach (['APP_SERVICES_CACHE', 'APP_PACKAGES_CACHE', 'APP_CONFIG_CACHE', 'APP_ROUTES_CACHE', 'APP_EVENTS_CACHE'] as $key) {
        $val = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);
        if ($val && preg_match('/^[a-zA-Z]:/', $val)) {
            $app->addAbsoluteCachePathPrefix(substr($val, 0, 2));
        }
    }
}

$electronStoragePath = $_ENV['ELECTRON_STORAGE_PATH'] ?? $_SERVER['ELECTRON_STORAGE_PATH'] ?? getenv('ELECTRON_STORAGE_PATH');
if ($electronStoragePath) {
    $app->useStoragePath($electronStoragePath);
}

/*
|--------------------------------------------------------------------------
| Bind Important Interfaces
|--------------------------------------------------------------------------
*/

$app->singleton(
    Illuminate\Contracts\Http\Kernel::class,
    App\Http\Kernel::class
);

$app->singleton(
    Illuminate\Contracts\Console\Kernel::class,
    App\Console\Kernel::class
);

$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    App\Exceptions\Handler::class
);

/*
|--------------------------------------------------------------------------
| Return The Application
|--------------------------------------------------------------------------
*/

return $app;
