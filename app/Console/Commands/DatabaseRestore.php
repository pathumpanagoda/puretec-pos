<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class DatabaseRestore extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'pos:restore
                            {file : Backup file path or filename in storage/app/backups}
                            {--force : Force restore without interactive confirmation}';

    /**
     * The console command description.
     */
    protected $description = 'Safely restore MySQL database from a backup file';

    public function handle(): int
    {
        $input = $this->argument('file');
        $backupDir = storage_path('app/backups');

        $filePath = File::exists($input) ? $input : "{$backupDir}/{$input}";

        if (!File::exists($filePath)) {
            $this->error("Backup file not found at: {$filePath}");
            return Command::FAILURE;
        }

        if (!$this->option('force')) {
            if (!$this->confirm("Are you sure you want to restore database from '{$filePath}'? Existing tables may be overwritten.")) {
                $this->info("Restore cancelled.");
                return Command::SUCCESS;
            }
        }

        $this->info("Starting database restore from '{$filePath}'...");

        // If file is gzip compressed, decompress to temporary file
        $sqlPath = $filePath;
        $isTemp = false;

        if (str_ends_with($filePath, '.gz')) {
            if (!function_exists('gzopen')) {
                $this->error("Zlib extension not available to decompress .gz backup.");
                return Command::FAILURE;
            }

            $sqlPath = storage_path('app/backups/temp_restore_' . time() . '.sql');
            $isTemp = true;

            $gz = gzopen($filePath, 'rb');
            $out = fopen($sqlPath, 'wb');
            while (!gzeof($gz)) {
                fwrite($out, gzread($gz, 1024 * 512));
            }
            fclose($out);
            gzclose($gz);
        }

        try {
            DB::unprepared(File::get($sqlPath));
            $this->info("Database successfully restored from backup!");
            Log::info("Database restored from: {$filePath}");

            if ($isTemp && File::exists($sqlPath)) {
                File::delete($sqlPath);
            }

            return Command::SUCCESS;
        } catch (\Throwable $e) {
            if ($isTemp && File::exists($sqlPath)) {
                File::delete($sqlPath);
            }
            $this->error("Restore failed: " . $e->getMessage());
            Log::error("Database restore failed: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}

