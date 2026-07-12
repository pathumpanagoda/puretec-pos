<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Electron Desktop App Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration specific to the Electron desktop application wrapper.
    | These values are used when the app is running inside Electron.
    |
    */

    // Whether the app is running inside Electron
    'is_electron' => env('ELECTRON_STORAGE_PATH') !== null,

    // Custom storage path when running inside Electron
    // This points to the user's AppData directory for persistent storage
    'storage_path' => env('ELECTRON_STORAGE_PATH'),

    // PHP server port
    'port' => env('ELECTRON_PORT', 8000),

];
