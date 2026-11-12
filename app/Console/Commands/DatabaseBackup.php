<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class DatabaseBackup extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'pos:backup
                            {--database= : Specific database to backup}
                            {--retention=7 : Number of days to retain backups}
                            {--compress : Compress the output SQL file with gzip}';

    /**
     * The console command description.
     */
    protected $description = 'Safely backup MySQL database to storage/app/backups';

    public function handle(): int
    {
        $dbConnection = config('database.default');
        $dbConfig = config("database.connections.{$dbConnection}");

        $database = $this->option('database') ?: ($dbConfig['database'] ?? 'purepos');
        $username = $dbConfig['username'] ?? 'root';
        $password = $dbConfig['password'] ?? '';
        $host     = $dbConfig['host'] ?? '127.0.0.1';
        $port     = $dbConfig['port'] ?? '3306';

        $backupDir = storage_path('app/backups');
        if (!File::exists($backupDir)) {
            File::makeDirectory($backupDir, 0755, true);
        }

        $timestamp = now()->format('Y-m-d_His');
        $sqlFilename = "{$database}_{$timestamp}.sql";
        $sqlPath = "{$backupDir}/{$sqlFilename}";

        $this->info("Starting MySQL backup for '{$database}'...");

        // Try mysqldump binary first
        $mysqldumpPath = $this->findMysqldump();
        $success = false;

        if ($mysqldumpPath) {
            $this->line("Using mysqldump binary: {$mysqldumpPath}");
            $cmd = sprintf(
                '"%s" --host=%s --port=%s --user=%s %s --single-transaction --quick --skip-lock-tables --default-character-set=utf8mb4 %s > "%s"',
                $mysqldumpPath,
                escapeshellarg($host),
                escapeshellarg($port),
                escapeshellarg($username),
                $password !== '' ? '--password=' . escapeshellarg($password) : '',
                escapeshellarg($database),
                $sqlPath
            );

            exec($cmd, $output, $exitCode);
            if ($exitCode === 0 && File::exists($sqlPath) && File::size($sqlPath) > 0) {
                $success = true;
            } else {
                $this->warn("mysqldump exited with code {$exitCode}. Falling back to PHP table exporter...");
            }
        }

        // Safe PHP-based fallback dump
        if (!$success) {
            $this->line("Running safe PHP table exporter fallback...");
            $success = $this->dumpViaPhp($sqlPath);
        }

        if (!$success || !File::exists($sqlPath)) {
            $this->error("Backup failed. Could not generate backup file.");
            Log::error("Database backup failed for database: {$database}");
            return Command::FAILURE;
        }

        $finalPath = $sqlPath;
        if ($this->option('compress') && function_exists('gzopen')) {
            $gzPath = "{$sqlPath}.gz";
            $gz = gzopen($gzPath, 'w9');
            $fp = fopen($sqlPath, 'rb');
            while (!feof($fp)) {
                gzwrite($gz, fread($fp, 1024 * 512));
            }
            fclose($fp);
            gzclose($gz);
            File::delete($sqlPath);
            $finalPath = $gzPath;
            $this->line("Compressed backup with gzip.");
        }

        $size = round(File::size($finalPath) / 1024, 2);
        $this->info("Backup completed successfully!");
        $this->line("Location: {$finalPath}");
        $this->line("Size: {$size} KB");

        Log::info("Database backup created: {$finalPath} ({$size} KB)");

        // Prune old backups based on retention
        $retentionDays = (int) $this->option('retention');
        $this->pruneOldBackups($backupDir, $retentionDays);

        return Command::SUCCESS;
    }

    /**
     * Find mysqldump executable path.
     */
    protected function findMysqldump(): ?string
    {
        $commonPaths = [
            'mysqldump',
            'C:\\xampp\\mysql\\bin\\mysqldump.exe',
            'C:\\Program Files\\MySQL\\MySQL Server 8.0\\bin\\mysqldump.exe',
            '/usr/bin/mysqldump',
            '/usr/local/bin/mysqldump',
        ];

        foreach ($commonPaths as $path) {
            $cmd = (PHP_OS_FAMILY === 'Windows') ? "where \"{$path}\" 2>nul" : "which \"{$path}\" 2>/dev/null";
            exec($cmd, $out, $ret);
            if ($ret === 0) {
                return $path;
            }
            if (File::exists($path)) {
                return $path;
            }
        }

        return null;
    }

    /**
     * Safe PHP table exporter fallback.
     */
    protected function dumpViaPhp(string $filepath): bool
    {
        try {
            $handle = fopen($filepath, 'w');
            if (!$handle) return false;

            fwrite($handle, "-- Puretec Pure POS MySQL Database Dump\n");
            fwrite($handle, "-- Generated: " . now()->toIso8601String() . "\n");
            fwrite($handle, "SET FOREIGN_KEY_CHECKS=0;\n");
            fwrite($handle, "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n\n");

            $tables = DB::select('SHOW TABLES');
            $dbName = DB::getDatabaseName();
            $tableKey = "Tables_in_{$dbName}";

            foreach ($tables as $tableObj) {
                $table = $tableObj->$tableKey ?? current((array)$tableObj);

                // Table structure
                $createTable = DB::select("SHOW CREATE TABLE `{$table}`");
                $createSql = $createTable[0]->{'Create Table'} ?? null;
                if ($createSql) {
                    fwrite($handle, "DROP TABLE IF EXISTS `{$table}`;\n");
                    fwrite($handle, $createSql . ";\n\n");
                }

                // Table data (chunked for memory efficiency)
                DB::table($table)->orderBy(DB::raw('1'))->chunk(500, function ($rows) use ($handle, $table) {
                    foreach ($rows as $row) {
                        $values = array_map(function ($val) {
                            if (is_null($val)) return 'NULL';
                            return DB::getPdo()->quote($val);
                        }, (array)$row);

                        $cols = array_map(fn($c) => "`{$c}`", array_keys((array)$row));
                        $sql = "INSERT INTO `{$table}` (" . implode(', ', $cols) . ") VALUES (" . implode(', ', $values) . ");\n";
                        fwrite($handle, $sql);
                    }
                });

                fwrite($handle, "\n");
            }

            fwrite($handle, "SET FOREIGN_KEY_CHECKS=1;\n");
            fclose($handle);

            return true;
        } catch (\Throwable $e) {
            Log::error("PHP database dump failed: " . $e->getMessage());
            $this->error("PHP dump error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Prune backups older than $days.
     */
    protected function pruneOldBackups(string $dir, int $days): void
    {
        if ($days <= 0) return;

        $threshold = now()->subDays($days)->timestamp;
        $files = File::files($dir);
        $deleted = 0;

        foreach ($files as $file) {
            if ($file->getMTime() < $threshold) {
                File::delete($file->getPathname());
                $deleted++;
            }
        }

        if ($deleted > 0) {
            $this->line("Pruned {$deleted} backup file(s) older than {$days} days.");
        }
    }
}

