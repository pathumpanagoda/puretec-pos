/**
 * Pre-build script for PureTec POS Electron app
 * 
 * This script prepares the Laravel application for packaging:
 * 1. Ensures storage directories exist
 * 2. Creates .env.electron if it doesn't exist
 * 3. Validates PHP is available in the php/ directory
 */

const fs = require('fs');
const path = require('path');

const rootDir = path.join(__dirname, '..');

console.log('🔧 PureTec POS Pre-build Script');
console.log('================================\n');

// 1. Check PHP directory
const phpDir = path.join(__dirname, 'php');
const phpExe = path.join(phpDir, 'php.exe');

if (!fs.existsSync(phpExe)) {
  console.error('❌ PHP not found at:', phpExe);
  console.error('   Run "npm run download-php" first to download portable PHP.\n');
  console.error('   Or manually place PHP 8.1+ files in the electron/php/ directory.');
  process.exit(1);
}
console.log('✅ PHP found at:', phpExe);

// 2. Check/Create .env.electron
const envElectronPath = path.join(rootDir, '.env.electron');
if (!fs.existsSync(envElectronPath)) {
  console.log('📝 Creating .env.electron template...');
  const envContent = `APP_NAME="PureTec POS"
APP_ENV=production
APP_KEY=base64:Xq6DGtI/974B4m62m/0DWrwgGD6dUzOnIsmzjU8yRfk=
APP_DEBUG=false
APP_URL=http://127.0.0.1:8000

LOG_CHANNEL=single
LOG_LEVEL=error

DB_CONNECTION=sqlite
DB_DATABASE=

SESSION_DRIVER=file
SESSION_LIFETIME=120
CACHE_DRIVER=file
QUEUE_CONNECTION=sync
FILESYSTEM_DISK=local

POS_CURRENCY=LKR
POS_CURRENCY_SYMBOL=Rs.
POS_TIMEZONE=Asia/Colombo
POS_BUSINESS_NAME="PureTec POS"
`;
  fs.writeFileSync(envElectronPath, envContent);
  console.log('✅ .env.electron created');
} else {
  console.log('✅ .env.electron exists');
}

// 3. Ensure storage structure exists
const storageDirs = [
  'storage/app/public',
  'storage/framework/cache/data',
  'storage/framework/sessions',
  'storage/framework/views',
  'storage/logs',
];

storageDirs.forEach(dir => {
  const fullPath = path.join(rootDir, dir);
  if (!fs.existsSync(fullPath)) {
    fs.mkdirSync(fullPath, { recursive: true });
    console.log(`📁 Created: ${dir}`);
  }
});
console.log('✅ Storage directories ready');

// 4. Check vendor directory
const vendorDir = path.join(rootDir, 'vendor');
if (!fs.existsSync(vendorDir)) {
  console.error('❌ vendor/ directory not found!');
  console.error('   Run "composer install --no-dev" in the project root.');
  process.exit(1);
}
console.log('✅ Vendor directory exists');

// 5. Check public/build directory (compiled assets)
const buildDir = path.join(rootDir, 'public', 'build');
if (!fs.existsSync(buildDir)) {
  console.warn('⚠️  public/build/ not found. Run "npm run build" in the project root to compile Vite assets.');
} else {
  console.log('✅ Compiled assets found');
}

// 6. Copy splash screen logo
const logoSrc = path.join(rootDir, 'public', 'icons', 'logo png.png');
const logoDst = path.join(__dirname, 'logo.png');
if (fs.existsSync(logoSrc)) {
  fs.copyFileSync(logoSrc, logoDst);
  console.log('✅ Splash screen logo copied to electron/logo.png');
} else {
  console.warn('⚠️  Splash screen logo source not found at:', logoSrc);
}

console.log('\n================================');
console.log('✅ Pre-build checks complete! Ready to package.\n');

