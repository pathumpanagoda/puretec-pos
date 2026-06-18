<?php
/**
 * Pure POS - Browser-Based Setup Script
 *
 * This script handles all installation tasks without requiring SSH access.
 * It performs: dependency installation, database migration, seeding, and optimization.
 *
 * SECURITY WARNING: DELETE THIS FILE AFTER INSTALLATION IS COMPLETE!
 */

// Increase limits for installation
@ini_set('max_execution_time', 600);
@ini_set('memory_limit', '512M');
@set_time_limit(600);

// Error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define base path
define('BASE_PATH', __DIR__);

// Session for tracking progress
session_start();

class PurePOSInstaller
{
    private $errors = [];
    private $messages = [];
    private $baseUrl;

    public function __construct()
    {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'];
        $this->baseUrl = $protocol . '://' . $host;
    }

    /**
     * Check system requirements
     */
    public function checkRequirements()
    {
        $requirements = [
            'php_version' => [
                'name' => 'PHP Version (8.1+)',
                'check' => version_compare(PHP_VERSION, '8.1.0', '>='),
                'current' => PHP_VERSION
            ],
            'pdo' => [
                'name' => 'PDO Extension',
                'check' => extension_loaded('pdo'),
                'current' => extension_loaded('pdo') ? 'Installed' : 'Not Installed'
            ],
            'pdo_mysql' => [
                'name' => 'PDO MySQL Extension',
                'check' => extension_loaded('pdo_mysql'),
                'current' => extension_loaded('pdo_mysql') ? 'Installed' : 'Not Installed'
            ],
            'mbstring' => [
                'name' => 'Mbstring Extension',
                'check' => extension_loaded('mbstring'),
                'current' => extension_loaded('mbstring') ? 'Installed' : 'Not Installed'
            ],
            'openssl' => [
                'name' => 'OpenSSL Extension',
                'check' => extension_loaded('openssl'),
                'current' => extension_loaded('openssl') ? 'Installed' : 'Not Installed'
            ],
            'tokenizer' => [
                'name' => 'Tokenizer Extension',
                'check' => extension_loaded('tokenizer'),
                'current' => extension_loaded('tokenizer') ? 'Installed' : 'Not Installed'
            ],
            'json' => [
                'name' => 'JSON Extension',
                'check' => extension_loaded('json'),
                'current' => extension_loaded('json') ? 'Installed' : 'Not Installed'
            ],
            'ctype' => [
                'name' => 'Ctype Extension',
                'check' => extension_loaded('ctype'),
                'current' => extension_loaded('ctype') ? 'Installed' : 'Not Installed'
            ],
            'xml' => [
                'name' => 'XML Extension',
                'check' => extension_loaded('xml'),
                'current' => extension_loaded('xml') ? 'Installed' : 'Not Installed'
            ],
            'fileinfo' => [
                'name' => 'Fileinfo Extension',
                'check' => extension_loaded('fileinfo'),
                'current' => extension_loaded('fileinfo') ? 'Installed' : 'Not Installed'
            ],
            'bcmath' => [
                'name' => 'BCMath Extension',
                'check' => extension_loaded('bcmath'),
                'current' => extension_loaded('bcmath') ? 'Installed' : 'Not Installed'
            ],
            'gd' => [
                'name' => 'GD Extension (for images)',
                'check' => extension_loaded('gd'),
                'current' => extension_loaded('gd') ? 'Installed' : 'Not Installed'
            ],
            'zip' => [
                'name' => 'Zip Extension',
                'check' => extension_loaded('zip'),
                'current' => extension_loaded('zip') ? 'Installed' : 'Not Installed'
            ],
            'env_file' => [
                'name' => '.env File Exists',
                'check' => file_exists(BASE_PATH . '/.env'),
                'current' => file_exists(BASE_PATH . '/.env') ? 'Found' : 'Not Found'
            ],
            'storage_writable' => [
                'name' => 'Storage Directory Writable',
                'check' => is_writable(BASE_PATH . '/storage'),
                'current' => is_writable(BASE_PATH . '/storage') ? 'Writable' : 'Not Writable'
            ],
            'bootstrap_cache_writable' => [
                'name' => 'Bootstrap/Cache Writable',
                'check' => is_writable(BASE_PATH . '/bootstrap/cache'),
                'current' => is_writable(BASE_PATH . '/bootstrap/cache') ? 'Writable' : 'Not Writable'
            ],
        ];

        return $requirements;
    }

    /**
     * Check database connection
     */
    public function checkDatabase()
    {
        $envPath = BASE_PATH . '/.env';
        if (!file_exists($envPath)) {
            return ['success' => false, 'message' => '.env file not found'];
        }

        $env = $this->parseEnvFile($envPath);

        $host = $env['DB_HOST'] ?? 'localhost';
        $port = $env['DB_PORT'] ?? '3306';
        $database = $env['DB_DATABASE'] ?? '';
        $username = $env['DB_USERNAME'] ?? '';
        $password = $env['DB_PASSWORD'] ?? '';

        if (empty($database) || empty($username)) {
            return ['success' => false, 'message' => 'Database credentials not configured in .env file'];
        }

        try {
            $dsn = "mysql:host={$host};port={$port};dbname={$database}";
            $pdo = new PDO($dsn, $username, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return ['success' => true, 'message' => 'Database connection successful'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Database connection failed: ' . $e->getMessage()];
        }
    }

    /**
     * Parse .env file
     */
    private function parseEnvFile($path)
    {
        $env = [];
        $content = file_get_contents($path);
        $lines = explode("\n", $content);

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line) || strpos($line, '#') === 0) {
                continue;
            }
            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value, " \t\n\r\0\x0B\"'");
                $env[$key] = $value;
            }
        }

        return $env;
    }

    /**
     * Generate APP_KEY
     */
    public function generateAppKey()
    {
        $key = 'base64:' . base64_encode(random_bytes(32));

        $envPath = BASE_PATH . '/.env';
        $content = file_get_contents($envPath);

        // Check if APP_KEY is empty or placeholder
        if (preg_match('/^APP_KEY=(.*)$/m', $content, $matches)) {
            $currentKey = trim($matches[1]);
            if (empty($currentKey) || strpos($currentKey, '[CHANGE_THIS') !== false) {
                $content = preg_replace('/^APP_KEY=.*$/m', 'APP_KEY=' . $key, $content);
                file_put_contents($envPath, $content);
                return ['success' => true, 'message' => 'Application key generated successfully', 'key' => $key];
            } else {
                return ['success' => true, 'message' => 'Application key already exists', 'key' => $currentKey];
            }
        }

        return ['success' => false, 'message' => 'Could not find APP_KEY in .env file'];
    }

    /**
     * Install Composer dependencies
     */
    public function installComposer()
    {
        // Check if vendor directory already exists
        if (is_dir(BASE_PATH . '/vendor') && file_exists(BASE_PATH . '/vendor/autoload.php')) {
            return ['success' => true, 'message' => 'Composer dependencies already installed'];
        }

        // Try to find composer
        $composerPaths = [
            'composer',
            'composer.phar',
            '/usr/local/bin/composer',
            '/usr/bin/composer',
            BASE_PATH . '/composer.phar'
        ];

        $composerFound = false;
        $composerPath = '';

        foreach ($composerPaths as $path) {
            if ($this->commandExists($path)) {
                $composerPath = $path;
                $composerFound = true;
                break;
            }
        }

        if (!$composerFound) {
            // Try to download composer
            $downloadResult = $this->downloadComposer();
            if ($downloadResult['success']) {
                $composerPath = BASE_PATH . '/composer.phar';
            } else {
                return [
                    'success' => false,
                    'message' => 'Composer not found and could not be downloaded. Please ask your hosting provider to install Composer, or manually upload the vendor folder.',
                    'manual_instructions' => $this->getManualComposerInstructions()
                ];
            }
        }

        // Run composer install
        $command = "cd " . escapeshellarg(BASE_PATH) . " && php {$composerPath} install --no-dev --optimize-autoloader 2>&1";
        $output = [];
        $returnCode = 0;
        exec($command, $output, $returnCode);

        if ($returnCode === 0 && is_dir(BASE_PATH . '/vendor')) {
            return ['success' => true, 'message' => 'Composer dependencies installed successfully'];
        } else {
            return [
                'success' => false,
                'message' => 'Composer install failed. Output: ' . implode("\n", $output),
                'manual_instructions' => $this->getManualComposerInstructions()
            ];
        }
    }

    /**
     * Download Composer
     */
    private function downloadComposer()
    {
        $composerUrl = 'https://getcomposer.org/composer.phar';
        $destination = BASE_PATH . '/composer.phar';

        // Try with file_get_contents
        $context = stream_context_create([
            'http' => [
                'timeout' => 120
            ]
        ]);

        $composerPhar = @file_get_contents($composerUrl, false, $context);

        if ($composerPhar !== false) {
            if (file_put_contents($destination, $composerPhar)) {
                chmod($destination, 0755);
                return ['success' => true];
            }
        }

        // Try with cURL
        if (function_exists('curl_init')) {
            $ch = curl_init($composerUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 120);
            $composerPhar = curl_exec($ch);
            curl_close($ch);

            if ($composerPhar !== false) {
                if (file_put_contents($destination, $composerPhar)) {
                    chmod($destination, 0755);
                    return ['success' => true];
                }
            }
        }

        return ['success' => false];
    }

    /**
     * Get manual composer instructions
     */
    private function getManualComposerInstructions()
    {
        return "
        <h4>Manual Vendor Installation</h4>
        <ol>
            <li>On your local computer, open terminal/command prompt</li>
            <li>Navigate to your project folder</li>
            <li>Run: <code>composer install --no-dev --optimize-autoloader</code></li>
            <li>ZIP the entire <code>vendor</code> folder</li>
            <li>Upload vendor.zip to your server via File Manager</li>
            <li>Extract it in the same directory as composer.json</li>
            <li>Refresh this page and continue</li>
        </ol>
        ";
    }

    /**
     * Check if command exists
     */
    private function commandExists($command)
    {
        $whereIs = (PHP_OS_FAMILY === 'Windows') ? 'where' : 'which';
        $process = @shell_exec("$whereIs $command 2>/dev/null");
        return !empty($process);
    }

    /**
     * Run database migrations
     */
    public function runMigrations()
    {
        // Check if vendor exists
        if (!file_exists(BASE_PATH . '/vendor/autoload.php')) {
            return ['success' => false, 'message' => 'Vendor directory not found. Please install Composer dependencies first.'];
        }

        try {
            // Bootstrap Laravel
            require BASE_PATH . '/vendor/autoload.php';
            $app = require_once BASE_PATH . '/bootstrap/app.php';
            $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

            // Run migrations
            $exitCode = Artisan::call('migrate', ['--force' => true]);
            $output = Artisan::output();

            if ($exitCode === 0) {
                return ['success' => true, 'message' => 'Database migrations completed successfully', 'output' => $output];
            } else {
                return ['success' => false, 'message' => 'Migration failed', 'output' => $output];
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Migration error: ' . $e->getMessage()];
        }
    }

    /**
     * Seed database
     */
    public function seedDatabase()
    {
        try {
            $exitCode = Artisan::call('db:seed', ['--force' => true]);
            $output = Artisan::output();

            if ($exitCode === 0) {
                return ['success' => true, 'message' => 'Database seeded successfully', 'output' => $output];
            } else {
                return ['success' => false, 'message' => 'Seeding failed', 'output' => $output];
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Seeding error: ' . $e->getMessage()];
        }
    }

    /**
     * Create storage link
     */
    public function createStorageLink()
    {
        $target = BASE_PATH . '/storage/app/public';
        $link = BASE_PATH . '/public/storage';

        // Check if link already exists
        if (file_exists($link)) {
            if (is_link($link)) {
                return ['success' => true, 'message' => 'Storage link already exists'];
            } else {
                // It's a directory, not a link - remove it
                $this->deleteDirectory($link);
            }
        }

        // Create storage directories if they don't exist
        $directories = [
            BASE_PATH . '/storage/app/public',
            BASE_PATH . '/storage/app/public/products',
            BASE_PATH . '/storage/app/public/avatars',
            BASE_PATH . '/storage/app/public/logos',
            BASE_PATH . '/storage/framework/cache',
            BASE_PATH . '/storage/framework/sessions',
            BASE_PATH . '/storage/framework/views',
            BASE_PATH . '/storage/logs',
        ];

        foreach ($directories as $dir) {
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
        }

        // Try to create symlink
        if (@symlink($target, $link)) {
            return ['success' => true, 'message' => 'Storage link created successfully'];
        }

        // Symlink failed (common on shared hosting), try alternative methods
        // Method 2: Try Artisan command
        try {
            if (class_exists('Artisan')) {
                $exitCode = Artisan::call('storage:link');
                if ($exitCode === 0) {
                    return ['success' => true, 'message' => 'Storage link created via Artisan'];
                }
            }
        } catch (Exception $e) {
            // Continue to next method
        }

        // Method 3: Create a redirect index.php in public/storage
        if (!is_dir($link)) {
            mkdir($link, 0755, true);
        }

        // Copy files instead of linking (for shared hosting)
        $this->copyDirectory($target, $link);

        // Create .htaccess for URL rewriting
        $htaccess = $link . '/.htaccess';
        file_put_contents($htaccess, "Options +FollowSymLinks\n");

        return ['success' => true, 'message' => 'Storage directory created (copy mode for shared hosting)'];
    }

    /**
     * Copy directory recursively
     */
    private function copyDirectory($source, $destination)
    {
        if (!is_dir($destination)) {
            mkdir($destination, 0755, true);
        }

        $dir = opendir($source);
        while (($file = readdir($dir)) !== false) {
            if ($file != '.' && $file != '..') {
                $srcFile = $source . '/' . $file;
                $destFile = $destination . '/' . $file;

                if (is_dir($srcFile)) {
                    $this->copyDirectory($srcFile, $destFile);
                } else {
                    copy($srcFile, $destFile);
                }
            }
        }
        closedir($dir);
    }

    /**
     * Delete directory recursively
     */
    private function deleteDirectory($dir)
    {
        if (!is_dir($dir)) {
            return;
        }

        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            is_dir($path) ? $this->deleteDirectory($path) : unlink($path);
        }
        rmdir($dir);
    }

    /**
     * Optimize application
     */
    public function optimize()
    {
        $results = [];

        try {
            // Clear caches first
            Artisan::call('config:clear');
            $results[] = 'Config cache cleared';

            Artisan::call('route:clear');
            $results[] = 'Route cache cleared';

            Artisan::call('view:clear');
            $results[] = 'View cache cleared';

            // Cache config and routes for production
            Artisan::call('config:cache');
            $results[] = 'Config cached';

            Artisan::call('route:cache');
            $results[] = 'Routes cached';

            Artisan::call('view:cache');
            $results[] = 'Views cached';

            return ['success' => true, 'message' => implode(', ', $results)];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Optimization error: ' . $e->getMessage()];
        }
    }

    /**
     * Create .htaccess for main directory
     */
    public function createMainHtaccess()
    {
        $htaccessPath = BASE_PATH . '/.htaccess';

        if (file_exists($htaccessPath)) {
            return ['success' => true, 'message' => 'Main .htaccess already exists'];
        }

        $content = '<IfModule mod_rewrite.c>
    RewriteEngine On

    # Redirect all requests to public folder
    RewriteRule ^(.*)$ public/$1 [L]
</IfModule>

# Deny access to sensitive files
<FilesMatch "^\.env|composer\.(json|lock)$">
    Order allow,deny
    Deny from all
</FilesMatch>
';

        if (file_put_contents($htaccessPath, $content)) {
            return ['success' => true, 'message' => 'Main .htaccess created successfully'];
        }

        return ['success' => false, 'message' => 'Could not create main .htaccess'];
    }
}

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');

    $installer = new PurePOSInstaller();
    $action = $_POST['action'];

    switch ($action) {
        case 'check_requirements':
            echo json_encode(['success' => true, 'data' => $installer->checkRequirements()]);
            break;

        case 'check_database':
            echo json_encode($installer->checkDatabase());
            break;

        case 'generate_key':
            echo json_encode($installer->generateAppKey());
            break;

        case 'install_composer':
            echo json_encode($installer->installComposer());
            break;

        case 'run_migrations':
            echo json_encode($installer->runMigrations());
            break;

        case 'seed_database':
            echo json_encode($installer->seedDatabase());
            break;

        case 'create_storage_link':
            echo json_encode($installer->createStorageLink());
            break;

        case 'optimize':
            echo json_encode($installer->optimize());
            break;

        case 'create_htaccess':
            echo json_encode($installer->createMainHtaccess());
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Unknown action']);
    }
    exit;
}

// Display HTML interface
$installer = new PurePOSInstaller();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pure POS - Installation</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 25px 50px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px;
            text-align: center;
        }
        .header h1 { font-size: 2.5em; margin-bottom: 10px; }
        .content { padding: 40px; }

        .step-container { margin-bottom: 20px; }
        .step-header {
            display: flex;
            align-items: center;
            padding: 15px 20px;
            background: #f8f9fa;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s;
        }
        .step-header:hover { background: #e9ecef; }
        .step-number {
            width: 35px;
            height: 35px;
            background: #667eea;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-right: 15px;
        }
        .step-title { flex: 1; font-weight: 600; }
        .step-status {
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.85em;
            font-weight: 500;
        }
        .status-pending { background: #e9ecef; color: #6c757d; }
        .status-running { background: #fff3cd; color: #856404; }
        .status-success { background: #d4edda; color: #155724; }
        .status-error { background: #f8d7da; color: #721c24; }

        .step-content {
            padding: 20px;
            margin-top: 10px;
            background: #f8f9fa;
            border-radius: 10px;
            display: none;
        }
        .step-content.active { display: block; }

        .req-table { width: 100%; border-collapse: collapse; }
        .req-table th, .req-table td { padding: 10px; text-align: left; border-bottom: 1px solid #dee2e6; }
        .req-table th { background: #e9ecef; }
        .req-pass { color: #28a745; }
        .req-fail { color: #dc3545; }

        .btn {
            display: inline-block;
            padding: 12px 30px;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            font-size: 1em;
            font-weight: 500;
            transition: all 0.3s;
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4); }
        .btn-primary:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }

        .btn-danger {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            color: white;
        }

        .btn-success {
            background: linear-gradient(135deg, #28a745 0%, #218838 100%);
            color: white;
        }

        .log-box {
            background: #1a1a2e;
            color: #16c79a;
            padding: 15px;
            border-radius: 10px;
            font-family: monospace;
            max-height: 200px;
            overflow-y: auto;
            margin-top: 15px;
        }

        .alert {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .alert-danger { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-warning { background: #fff3cd; color: #856404; border: 1px solid #ffc107; }
        .alert-info { background: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }

        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid #f3f3f3;
            border-top: 3px solid #667eea;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-right: 10px;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .credentials-box {
            background: #1a1a2e;
            color: #fff;
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
        }
        .credentials-box h4 { color: #16c79a; margin-bottom: 15px; }
        .credentials-box table { width: 100%; }
        .credentials-box td { padding: 8px 0; }
        .credentials-box code { background: #2d3748; padding: 3px 8px; border-radius: 4px; color: #f9ca24; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Pure POS</h1>
            <p>Installation Wizard</p>
        </div>

        <div class="content">
            <div class="alert alert-warning">
                <strong>Security Notice:</strong> Delete this file (setup.php) immediately after installation is complete!
            </div>

            <!-- Step 1: Requirements -->
            <div class="step-container" id="step1">
                <div class="step-header" onclick="toggleStep(1)">
                    <span class="step-number">1</span>
                    <span class="step-title">Check Requirements</span>
                    <span class="step-status status-pending" id="status1">Pending</span>
                </div>
                <div class="step-content" id="content1">
                    <p>Checking server requirements...</p>
                    <div id="requirements-result"></div>
                    <button class="btn btn-primary" onclick="checkRequirements()" id="btn-check-req">Check Requirements</button>
                </div>
            </div>

            <!-- Step 2: Database -->
            <div class="step-container" id="step2">
                <div class="step-header" onclick="toggleStep(2)">
                    <span class="step-number">2</span>
                    <span class="step-title">Check Database Connection</span>
                    <span class="step-status status-pending" id="status2">Pending</span>
                </div>
                <div class="step-content" id="content2">
                    <p>Testing database connection using settings from .env file...</p>
                    <div id="database-result"></div>
                    <button class="btn btn-primary" onclick="checkDatabase()" id="btn-check-db">Test Connection</button>
                </div>
            </div>

            <!-- Step 3: Composer -->
            <div class="step-container" id="step3">
                <div class="step-header" onclick="toggleStep(3)">
                    <span class="step-number">3</span>
                    <span class="step-title">Install Dependencies</span>
                    <span class="step-status status-pending" id="status3">Pending</span>
                </div>
                <div class="step-content" id="content3">
                    <p>Installing Composer dependencies (this may take several minutes)...</p>
                    <div id="composer-result"></div>
                    <button class="btn btn-primary" onclick="installComposer()" id="btn-composer">Install Dependencies</button>
                </div>
            </div>

            <!-- Step 4: App Key -->
            <div class="step-container" id="step4">
                <div class="step-header" onclick="toggleStep(4)">
                    <span class="step-number">4</span>
                    <span class="step-title">Generate Application Key</span>
                    <span class="step-status status-pending" id="status4">Pending</span>
                </div>
                <div class="step-content" id="content4">
                    <p>Generating encryption key for secure sessions and data...</p>
                    <div id="key-result"></div>
                    <button class="btn btn-primary" onclick="generateKey()" id="btn-key">Generate Key</button>
                </div>
            </div>

            <!-- Step 5: Migrations -->
            <div class="step-container" id="step5">
                <div class="step-header" onclick="toggleStep(5)">
                    <span class="step-number">5</span>
                    <span class="step-title">Run Database Migrations</span>
                    <span class="step-status status-pending" id="status5">Pending</span>
                </div>
                <div class="step-content" id="content5">
                    <p>Creating database tables...</p>
                    <div id="migration-result"></div>
                    <button class="btn btn-primary" onclick="runMigrations()" id="btn-migrate">Run Migrations</button>
                </div>
            </div>

            <!-- Step 6: Seeding -->
            <div class="step-container" id="step6">
                <div class="step-header" onclick="toggleStep(6)">
                    <span class="step-number">6</span>
                    <span class="step-title">Seed Database</span>
                    <span class="step-status status-pending" id="status6">Pending</span>
                </div>
                <div class="step-content" id="content6">
                    <p>Adding default data (categories, users, settings)...</p>
                    <div id="seed-result"></div>
                    <button class="btn btn-primary" onclick="seedDatabase()" id="btn-seed">Seed Database</button>
                </div>
            </div>

            <!-- Step 7: Storage Link -->
            <div class="step-container" id="step7">
                <div class="step-header" onclick="toggleStep(7)">
                    <span class="step-number">7</span>
                    <span class="step-title">Create Storage Link</span>
                    <span class="step-status status-pending" id="status7">Pending</span>
                </div>
                <div class="step-content" id="content7">
                    <p>Setting up file storage for uploads...</p>
                    <div id="storage-result"></div>
                    <button class="btn btn-primary" onclick="createStorageLink()" id="btn-storage">Create Storage Link</button>
                </div>
            </div>

            <!-- Step 8: htaccess -->
            <div class="step-container" id="step8">
                <div class="step-header" onclick="toggleStep(8)">
                    <span class="step-number">8</span>
                    <span class="step-title">Create Security Files</span>
                    <span class="step-status status-pending" id="status8">Pending</span>
                </div>
                <div class="step-content" id="content8">
                    <p>Creating .htaccess for URL routing and security...</p>
                    <div id="htaccess-result"></div>
                    <button class="btn btn-primary" onclick="createHtaccess()" id="btn-htaccess">Create .htaccess</button>
                </div>
            </div>

            <!-- Step 9: Optimize -->
            <div class="step-container" id="step9">
                <div class="step-header" onclick="toggleStep(9)">
                    <span class="step-number">9</span>
                    <span class="step-title">Optimize Application</span>
                    <span class="step-status status-pending" id="status9">Pending</span>
                </div>
                <div class="step-content" id="content9">
                    <p>Caching configuration and routes for better performance...</p>
                    <div id="optimize-result"></div>
                    <button class="btn btn-primary" onclick="optimize()" id="btn-optimize">Optimize</button>
                </div>
            </div>

            <!-- Completion -->
            <div id="completion" style="display: none;">
                <div class="alert alert-success">
                    <strong>Installation Complete!</strong> Your Pure POS system is ready to use.
                </div>

                <div class="credentials-box">
                    <h4>Default Login Credentials</h4>
                    <table>
                        <tr>
                            <td>Super Admin Email:</td>
                            <td><code>admin@purepos.lk</code></td>
                        </tr>
                        <tr>
                            <td>Super Admin Password:</td>
                            <td><code>admin123</code></td>
                        </tr>
                        <tr>
                            <td>Super Admin PIN:</td>
                            <td><code>1234</code></td>
                        </tr>
                        <tr>
                            <td>Cashier Email:</td>
                            <td><code>cashier@purepos.lk</code></td>
                        </tr>
                        <tr>
                            <td>Cashier Password:</td>
                            <td><code>cashier123</code></td>
                        </tr>
                    </table>
                </div>

                <div class="alert alert-danger">
                    <strong>IMPORTANT:</strong>
                    <ol style="margin-top: 10px; margin-left: 20px;">
                        <li>Change the default passwords immediately after login!</li>
                        <li>DELETE this setup.php file NOW for security!</li>
                    </ol>
                </div>

                <div style="text-align: center; margin-top: 30px;">
                    <a href="/" class="btn btn-success">Go to Pure POS</a>
                </div>
            </div>

            <!-- Run All Button -->
            <div style="text-align: center; margin-top: 30px; padding-top: 30px; border-top: 1px solid #eee;">
                <button class="btn btn-primary" onclick="runAllSteps()" id="btn-run-all" style="padding: 15px 50px; font-size: 1.1em;">
                    Run All Steps Automatically
                </button>
            </div>
        </div>
    </div>

    <script>
        function toggleStep(num) {
            const content = document.getElementById('content' + num);
            content.classList.toggle('active');
        }

        function setStatus(step, status, text) {
            const el = document.getElementById('status' + step);
            el.className = 'step-status status-' + status;
            el.textContent = text || status.charAt(0).toUpperCase() + status.slice(1);
        }

        async function makeRequest(action) {
            const formData = new FormData();
            formData.append('action', action);

            const response = await fetch('setup.php', {
                method: 'POST',
                body: formData
            });

            return await response.json();
        }

        async function checkRequirements() {
            setStatus(1, 'running', 'Checking...');
            document.getElementById('btn-check-req').disabled = true;

            const result = await makeRequest('check_requirements');
            const container = document.getElementById('requirements-result');

            let html = '<table class="req-table"><tr><th>Requirement</th><th>Status</th><th>Current</th></tr>';
            let allPassed = true;

            for (const [key, req] of Object.entries(result.data)) {
                const statusClass = req.check ? 'req-pass' : 'req-fail';
                const statusText = req.check ? 'PASS' : 'FAIL';
                if (!req.check) allPassed = false;

                html += `<tr>
                    <td>${req.name}</td>
                    <td class="${statusClass}">${statusText}</td>
                    <td>${req.current}</td>
                </tr>`;
            }
            html += '</table>';

            container.innerHTML = html;

            if (allPassed) {
                setStatus(1, 'success', 'Passed');
            } else {
                setStatus(1, 'error', 'Failed');
            }

            return allPassed;
        }

        async function checkDatabase() {
            setStatus(2, 'running', 'Testing...');
            document.getElementById('btn-check-db').disabled = true;

            const result = await makeRequest('check_database');
            const container = document.getElementById('database-result');

            if (result.success) {
                container.innerHTML = '<div class="alert alert-success">' + result.message + '</div>';
                setStatus(2, 'success', 'Connected');
            } else {
                container.innerHTML = '<div class="alert alert-danger">' + result.message + '</div>';
                setStatus(2, 'error', 'Failed');
            }

            return result.success;
        }

        async function installComposer() {
            setStatus(3, 'running', 'Installing...');
            document.getElementById('btn-composer').disabled = true;
            document.getElementById('btn-composer').innerHTML = '<span class="loading"></span>Installing (this may take a few minutes)...';

            const result = await makeRequest('install_composer');
            const container = document.getElementById('composer-result');

            if (result.success) {
                container.innerHTML = '<div class="alert alert-success">' + result.message + '</div>';
                setStatus(3, 'success', 'Installed');
            } else {
                let html = '<div class="alert alert-danger">' + result.message + '</div>';
                if (result.manual_instructions) {
                    html += result.manual_instructions;
                }
                container.innerHTML = html;
                setStatus(3, 'error', 'Failed');
            }

            document.getElementById('btn-composer').textContent = 'Install Dependencies';
            return result.success;
        }

        async function generateKey() {
            setStatus(4, 'running', 'Generating...');
            document.getElementById('btn-key').disabled = true;

            const result = await makeRequest('generate_key');
            const container = document.getElementById('key-result');

            if (result.success) {
                container.innerHTML = '<div class="alert alert-success">' + result.message + '</div>';
                setStatus(4, 'success', 'Generated');
            } else {
                container.innerHTML = '<div class="alert alert-danger">' + result.message + '</div>';
                setStatus(4, 'error', 'Failed');
            }

            return result.success;
        }

        async function runMigrations() {
            setStatus(5, 'running', 'Migrating...');
            document.getElementById('btn-migrate').disabled = true;
            document.getElementById('btn-migrate').innerHTML = '<span class="loading"></span>Running migrations...';

            const result = await makeRequest('run_migrations');
            const container = document.getElementById('migration-result');

            if (result.success) {
                container.innerHTML = '<div class="alert alert-success">' + result.message + '</div>';
                if (result.output) {
                    container.innerHTML += '<div class="log-box">' + result.output.replace(/\n/g, '<br>') + '</div>';
                }
                setStatus(5, 'success', 'Complete');
            } else {
                container.innerHTML = '<div class="alert alert-danger">' + result.message + '</div>';
                setStatus(5, 'error', 'Failed');
            }

            document.getElementById('btn-migrate').textContent = 'Run Migrations';
            return result.success;
        }

        async function seedDatabase() {
            setStatus(6, 'running', 'Seeding...');
            document.getElementById('btn-seed').disabled = true;
            document.getElementById('btn-seed').innerHTML = '<span class="loading"></span>Seeding database...';

            const result = await makeRequest('seed_database');
            const container = document.getElementById('seed-result');

            if (result.success) {
                container.innerHTML = '<div class="alert alert-success">' + result.message + '</div>';
                setStatus(6, 'success', 'Complete');
            } else {
                container.innerHTML = '<div class="alert alert-danger">' + result.message + '</div>';
                setStatus(6, 'error', 'Failed');
            }

            document.getElementById('btn-seed').textContent = 'Seed Database';
            return result.success;
        }

        async function createStorageLink() {
            setStatus(7, 'running', 'Creating...');
            document.getElementById('btn-storage').disabled = true;

            const result = await makeRequest('create_storage_link');
            const container = document.getElementById('storage-result');

            if (result.success) {
                container.innerHTML = '<div class="alert alert-success">' + result.message + '</div>';
                setStatus(7, 'success', 'Created');
            } else {
                container.innerHTML = '<div class="alert alert-danger">' + result.message + '</div>';
                setStatus(7, 'error', 'Failed');
            }

            return result.success;
        }

        async function createHtaccess() {
            setStatus(8, 'running', 'Creating...');
            document.getElementById('btn-htaccess').disabled = true;

            const result = await makeRequest('create_htaccess');
            const container = document.getElementById('htaccess-result');

            if (result.success) {
                container.innerHTML = '<div class="alert alert-success">' + result.message + '</div>';
                setStatus(8, 'success', 'Created');
            } else {
                container.innerHTML = '<div class="alert alert-danger">' + result.message + '</div>';
                setStatus(8, 'error', 'Failed');
            }

            return result.success;
        }

        async function optimize() {
            setStatus(9, 'running', 'Optimizing...');
            document.getElementById('btn-optimize').disabled = true;

            const result = await makeRequest('optimize');
            const container = document.getElementById('optimize-result');

            if (result.success) {
                container.innerHTML = '<div class="alert alert-success">' + result.message + '</div>';
                setStatus(9, 'success', 'Optimized');
            } else {
                container.innerHTML = '<div class="alert alert-danger">' + result.message + '</div>';
                setStatus(9, 'error', 'Failed');
            }

            return result.success;
        }

        async function runAllSteps() {
            document.getElementById('btn-run-all').disabled = true;
            document.getElementById('btn-run-all').innerHTML = '<span class="loading"></span>Installing Pure POS...';

            // Open all steps
            for (let i = 1; i <= 9; i++) {
                document.getElementById('content' + i).classList.add('active');
            }

            // Run each step
            const steps = [
                { fn: checkRequirements, name: 'Requirements' },
                { fn: checkDatabase, name: 'Database' },
                { fn: installComposer, name: 'Composer' },
                { fn: generateKey, name: 'App Key' },
                { fn: runMigrations, name: 'Migrations' },
                { fn: seedDatabase, name: 'Seeding' },
                { fn: createStorageLink, name: 'Storage' },
                { fn: createHtaccess, name: 'htaccess' },
                { fn: optimize, name: 'Optimize' }
            ];

            let allSuccess = true;

            for (const step of steps) {
                try {
                    const result = await step.fn();
                    if (!result) {
                        allSuccess = false;
                        // Don't stop - continue with other steps
                    }
                } catch (e) {
                    console.error(step.name + ' failed:', e);
                    allSuccess = false;
                }

                // Small delay between steps
                await new Promise(resolve => setTimeout(resolve, 500));
            }

            document.getElementById('btn-run-all').textContent = 'Run All Steps Automatically';

            if (allSuccess) {
                document.getElementById('completion').style.display = 'block';
                document.getElementById('btn-run-all').style.display = 'none';
            } else {
                document.getElementById('btn-run-all').disabled = false;
                document.getElementById('btn-run-all').textContent = 'Retry Failed Steps';
            }
        }

        // Show first step on load
        document.getElementById('content1').classList.add('active');
    </script>
</body>
</html>
