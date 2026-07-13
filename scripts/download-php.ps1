<#
.SYNOPSIS
    Downloads portable PHP 8.1 for Windows and sets up required extensions
    for PureTec POS Electron application.

.DESCRIPTION
    This script downloads a portable PHP build from the official PHP releases,
    extracts it to the electron/php directory, and configures the required
    extensions for the Laravel application.
#>

param(
    [string]$PhpVersion = "8.2.12",
    [string]$OutputDir = "$PSScriptRoot\..\electron\php"
)

$ErrorActionPreference = "Stop"

Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "  PureTec POS - PHP Downloader" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# PHP download URL (Windows x64 Thread Safe)
$phpZipName = "php-$PhpVersion-Win32-vs16-x64.zip"
$phpUrl = "https://windows.php.net/downloads/releases/php-$PhpVersion-Win32-vs16-x64.zip"
$phpArchiveUrl = "https://windows.php.net/downloads/releases/archives/$phpZipName"

$tempDir = "$env:TEMP\puretec-php-download"
$zipPath = "$tempDir\$phpZipName"

# Create temp and output directories
if (-not (Test-Path $tempDir)) {
    New-Item -ItemType Directory -Path $tempDir -Force | Out-Null
}

if (Test-Path $OutputDir) {
    Write-Host "[!] Removing existing PHP directory..." -ForegroundColor Yellow
    Remove-Item -Recurse -Force $OutputDir
}
New-Item -ItemType Directory -Path $OutputDir -Force | Out-Null

# Download PHP
Write-Host "[1/4] Downloading PHP $PhpVersion..." -ForegroundColor Green

if (-not (Test-Path $zipPath)) {
    try {
        Write-Host "       Trying releases URL..." -ForegroundColor Gray
        Invoke-WebRequest -Uri $phpUrl -OutFile $zipPath -UseBasicParsing
    } catch {
        Write-Host "       Trying archive URL..." -ForegroundColor Gray
        try {
            Invoke-WebRequest -Uri $phpArchiveUrl -OutFile $zipPath -UseBasicParsing
        } catch {
            Write-Host ""
            Write-Host "[ERROR] Could not download PHP $PhpVersion automatically." -ForegroundColor Red
            Write-Host ""
            Write-Host "Please download PHP manually:" -ForegroundColor Yellow
            Write-Host "  1. Go to: https://windows.php.net/download/" -ForegroundColor White
            Write-Host "  2. Download PHP 8.1.x (VS16 x64 Thread Safe) ZIP" -ForegroundColor White
            Write-Host "  3. Extract all files to: $OutputDir" -ForegroundColor White
            Write-Host ""
            exit 1
        }
    }
    Write-Host "       Download complete!" -ForegroundColor Green
} else {
    Write-Host "       Using cached download." -ForegroundColor Gray
}

# Extract PHP
Write-Host "[2/4] Extracting PHP..." -ForegroundColor Green
Expand-Archive -Path $zipPath -DestinationPath $OutputDir -Force
Write-Host "       Extracted to: $OutputDir" -ForegroundColor Gray

# Configure PHP (create php.ini)
Write-Host "[3/4] Configuring PHP..." -ForegroundColor Green

$phpIni = @"
[PHP]
; PureTec POS - Embedded PHP Configuration
; ==========================================

; Error handling
error_reporting = E_ALL & ~E_DEPRECATED & ~E_STRICT
display_errors = Off
log_errors = On
error_log = php_errors.log

; Resource limits
memory_limit = 256M
max_execution_time = 300
max_input_time = 60
post_max_size = 64M
upload_max_filesize = 64M
max_file_uploads = 20

; Extensions directory
extension_dir = "ext"

; Required extensions for Laravel
extension=curl
extension=fileinfo
extension=gd
extension=intl
extension=mbstring
extension=exif
extension=openssl
extension=pdo_sqlite
extension=sqlite3
extension=zip
extension=sodium

; Date
date.timezone = Asia/Colombo

; Session
session.save_handler = files
session.gc_maxlifetime = 7200

; OPcache for performance
zend_extension=opcache
opcache.enable=1
opcache.enable_cli=1
opcache.memory_consumption=128
opcache.interned_strings_buffer=8
opcache.max_accelerated_files=10000
opcache.revalidate_freq=0
opcache.validate_timestamps=0

; Misc
realpath_cache_size = 4096K
realpath_cache_ttl = 600
"@

$phpIniPath = Join-Path $OutputDir "php.ini"
Set-Content -Path $phpIniPath -Value $phpIni -Encoding UTF8
Write-Host "       php.ini created" -ForegroundColor Gray

# Verify PHP
Write-Host "[4/4] Verifying PHP installation..." -ForegroundColor Green

$phpExe = Join-Path $OutputDir "php.exe"
if (Test-Path $phpExe) {
    $phpVersionOutput = & $phpExe -v 2>&1
    Write-Host "       $($phpVersionOutput[0])" -ForegroundColor Gray

    # Test SQLite extension
    $sqliteTest = & $phpExe -r "echo extension_loaded('pdo_sqlite') ? 'SQLite OK' : 'SQLite MISSING';" 2>&1
    Write-Host "       $sqliteTest" -ForegroundColor Gray
} else {
    Write-Host "[ERROR] php.exe not found at: $phpExe" -ForegroundColor Red
    exit 1
}

# Cleanup
Write-Host ""
Write-Host "========================================" -ForegroundColor Green
Write-Host "  PHP $PhpVersion installed successfully!" -ForegroundColor Green
Write-Host "  Location: $OutputDir" -ForegroundColor Gray
Write-Host "========================================" -ForegroundColor Green
Write-Host ""
