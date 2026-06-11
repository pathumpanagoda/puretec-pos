<?php
/**
 * Database Migration Runner
 * Upload this file to your server root and visit it in browser
 * DELETE THIS FILE AFTER MIGRATION IS COMPLETE!
 */

// Prevent timeout
set_time_limit(300);

echo "<h2>Pure POS - Database Migration</h2>";
echo "<pre>";

try {
    require __DIR__.'/vendor/autoload.php';
    $app = require_once __DIR__.'/bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

    echo "Running migrations...\n\n";

    $status = $kernel->call('migrate', ['--force' => true]);

    echo "\n";
    if ($status === 0) {
        echo "✓ Migration completed successfully!\n\n";
        echo "<strong style='color:red'>IMPORTANT: Delete this file (run-migrate.php) now!</strong>\n";
    } else {
        echo "⚠ Migration finished with status: $status\n";
    }

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "\nStack trace:\n" . $e->getTraceAsString();
}

echo "</pre>";
