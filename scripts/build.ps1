<#
.SYNOPSIS
    Full build script for PureTec POS Desktop Application.

.DESCRIPTION
    This script orchestrates the complete build process:
    1. Compiles Laravel Vite assets
    2. Installs Electron dependencies
    3. Downloads portable PHP (if needed)
    4. Runs pre-build checks
    5. Builds the Electron installer

.PARAMETER SkipPhp
    Skip PHP download step (use if already downloaded)

.PARAMETER SkipAssets
    Skip Vite asset compilation

.EXAMPLE
    .\build.ps1
    .\build.ps1 -SkipPhp
#>

param(
    [switch]$SkipPhp,
    [switch]$SkipAssets
)

$ErrorActionPreference = "Stop"
$rootDir = Split-Path -Parent $PSScriptRoot
$electronDir = Join-Path $rootDir "electron"

Write-Host ""
Write-Host "================================================" -ForegroundColor Cyan
Write-Host "  PureTec POS - Desktop Application Builder" -ForegroundColor Cyan
Write-Host "================================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "Root directory: $rootDir" -ForegroundColor Gray
Write-Host ""

# ── Auto-detect PHP & Composer ───────────────────────────────────────────────

$phpExePath = $null
$composerPath = $null

# Check common PHP locations
$phpLocations = @(
    "C:\xampp\php\php.exe",
    "C:\php\php.exe",
    "C:\wamp64\bin\php\php8.1\php.exe",
    "C:\wamp\bin\php\php8.1\php.exe",
    "C:\laragon\bin\php\php-8.1\php.exe"
)

foreach ($loc in $phpLocations) {
    if (Test-Path $loc) {
        $phpExePath = $loc
        break
    }
}

# Try system PATH
if (-not $phpExePath) {
    $phpExePath = (Get-Command php -ErrorAction SilentlyContinue).Source
}

if (-not $phpExePath) {
    Write-Host "[ERROR] PHP not found! Please install PHP or XAMPP." -ForegroundColor Red
    exit 1
}

$phpDir = Split-Path $phpExePath
Write-Host "PHP found: $phpExePath" -ForegroundColor Gray

# Add PHP to PATH for this session
$env:PATH = "$phpDir;$env:PATH"

# Find Composer
$composerLocations = @(
    "$phpDir\composer",
    "$phpDir\composer.phar",
    "$phpDir\composer.bat",
    "$env:APPDATA\Composer\vendor\bin\composer.bat",
    "C:\ProgramData\ComposerSetup\bin\composer.bat"
)

foreach ($loc in $composerLocations) {
    if (Test-Path $loc) {
        $composerPath = $loc
        break
    }
}

if (-not $composerPath) {
    $composerPath = (Get-Command composer -ErrorAction SilentlyContinue).Source
}

if (-not $composerPath) {
    Write-Host "[WARN] Composer not found. Skipping composer install step." -ForegroundColor Yellow
    Write-Host "       Vendor directory must already exist." -ForegroundColor Yellow
} else {
    Write-Host "Composer found: $composerPath" -ForegroundColor Gray
}

Write-Host ""

$startTime = Get-Date

# ── Step 1: Compile Vite Assets ──────────────────────────────────────────────

if (-not $SkipAssets) {
    Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" -ForegroundColor DarkGray
    Write-Host "[Step 1/6] Compiling Vite assets..." -ForegroundColor Yellow
    Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" -ForegroundColor DarkGray

    Push-Location $rootDir
    try {
        npm run build
        if ($LASTEXITCODE -ne 0) { throw "Vite build failed" }
        Write-Host "✅ Vite assets compiled" -ForegroundColor Green
    } finally {
        Pop-Location
    }
} else {
    Write-Host "[Step 1/6] Skipping Vite assets (--SkipAssets)" -ForegroundColor Gray
}

# ── Step 2: Composer Install (production) ────────────────────────────────────

Write-Host ""
Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" -ForegroundColor DarkGray
Write-Host "[Step 2/6] Installing Composer dependencies (production)..." -ForegroundColor Yellow
Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" -ForegroundColor DarkGray

Push-Location $rootDir
try {
    if ($composerPath) {
        if ($composerPath -match "\.phar$" -or -not ($composerPath -match "\.(bat|cmd|exe)$")) {
            & $phpExePath $composerPath install --no-dev --optimize-autoloader --no-interaction
        } else {
            & $composerPath install --no-dev --optimize-autoloader --no-interaction
        }
        if ($LASTEXITCODE -ne 0) { throw "Composer install failed" }
        Write-Host "✅ Composer dependencies installed" -ForegroundColor Green
    } else {
        if (Test-Path (Join-Path $rootDir "vendor")) {
            Write-Host "⏭️  Skipping composer (vendor/ already exists)" -ForegroundColor Yellow
        } else {
            throw "Composer not found and vendor/ directory missing!"
        }
    }
} finally {
    Pop-Location
}

# ── Step 3: Download Portable PHP ────────────────────────────────────────────

Write-Host ""
Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" -ForegroundColor DarkGray
Write-Host "[Step 3/6] Setting up portable PHP..." -ForegroundColor Yellow
Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" -ForegroundColor DarkGray

$phpExe = Join-Path $electronDir "php\php.exe"

if (-not $SkipPhp -or -not (Test-Path $phpExe)) {
    & "$PSScriptRoot\download-php.ps1"
    if ($LASTEXITCODE -ne 0) { throw "PHP download failed" }
} else {
    Write-Host "✅ PHP already exists, skipping download" -ForegroundColor Green
}

# ── Step 4: Install Electron Dependencies ────────────────────────────────────

Write-Host ""
Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" -ForegroundColor DarkGray
Write-Host "[Step 4/6] Installing Electron dependencies..." -ForegroundColor Yellow
Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" -ForegroundColor DarkGray

Push-Location $electronDir
try {
    npm install
    if ($LASTEXITCODE -ne 0) { throw "npm install failed" }
    Write-Host "✅ Electron dependencies installed" -ForegroundColor Green
} finally {
    Pop-Location
}

# ── Step 5: Pre-build Checks ────────────────────────────────────────────────

Write-Host ""
Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" -ForegroundColor DarkGray
Write-Host "[Step 5/6] Running pre-build checks..." -ForegroundColor Yellow
Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" -ForegroundColor DarkGray

Push-Location $electronDir
try {
    node prebuild.js
    if ($LASTEXITCODE -ne 0) { throw "Pre-build checks failed" }
} finally {
    Pop-Location
}

# ── Step 6: Build Electron Installer ─────────────────────────────────────────

Write-Host ""
Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" -ForegroundColor DarkGray
Write-Host "[Step 6/6] Building Electron installer..." -ForegroundColor Yellow
Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" -ForegroundColor DarkGray

Push-Location $electronDir
try {
    npm run build:win
    if ($LASTEXITCODE -ne 0) { throw "Electron build failed" }
    Write-Host "✅ Installer built successfully!" -ForegroundColor Green
} finally {
    Pop-Location
}

# ── Done ─────────────────────────────────────────────────────────────────────

$elapsed = (Get-Date) - $startTime

Write-Host ""
Write-Host "================================================" -ForegroundColor Green
Write-Host "  ✅ BUILD COMPLETE!" -ForegroundColor Green
Write-Host "================================================" -ForegroundColor Green
Write-Host ""
Write-Host "  Time: $($elapsed.Minutes)m $($elapsed.Seconds)s" -ForegroundColor Gray
Write-Host "  Output: $rootDir\dist\" -ForegroundColor Gray
Write-Host ""

# List output files
$distDir = Join-Path $rootDir "dist"
if (Test-Path $distDir) {
    Write-Host "  Generated files:" -ForegroundColor Gray
    Get-ChildItem $distDir -Filter "*.exe" | ForEach-Object {
        $sizeMB = [math]::Round($_.Length / 1MB, 1)
        Write-Host "    [Installer] $($_.Name) ($sizeMB MB)" -ForegroundColor White
    }
}

Write-Host ""
