<?php

namespace App\Services;

use App\Models\Tenant;
use App\Models\Store;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ClientProvisioningService
{
    protected FeatureService $featureService;

    public function __construct(FeatureService $featureService)
    {
        $this->featureService = $featureService;
    }

    /**
     * Provision a new client.
     *
     * This creates the tenant record, store, admin user, and initializes features.
     *
     * For the SHARED-DATABASE architecture (current):
     *   Creates records in the existing database.
     *
     * For the PER-CLIENT-DATABASE architecture (future):
     *   Would create a new database, run migrations, and seed data.
     *   This requires server/hosting access and is documented as a manual step.
     *
     * @param array $data Client provisioning data
     * @return array Result with tenant, store, user info
     */
    public function provision(array $data): array
    {
        return DB::transaction(function () use ($data) {
            // 1. Create tenant
            $tenant = Tenant::create([
                'code'                => Tenant::generateCode(),
                'business_name'       => $data['business_name'],
                'owner_name'          => $data['owner_name'],
                'email'               => $data['email'],
                'phone'               => $data['phone'] ?? null,
                'address'             => $data['address'] ?? null,
                'subscription_plan'   => $data['plan'] ?? 'basic',
                'monthly_fee'         => $data['monthly_fee'] ?? 0,
                'payment_due_day'     => $data['payment_due_day'] ?? 1,
                'is_active'           => true,
                'is_locked'           => false,
                'trial_ends_at'       => $data['trial_days'] ? now()->addDays($data['trial_days']) : null,
            ]);

            // 2. Create default store
            $store = Store::create([
                'tenant_id'       => $tenant->id,
                'name'            => $data['business_name'],
                'address'         => $data['address'] ?? null,
                'phone'           => $data['phone'] ?? null,
                'email'           => $data['email'],
                'currency'        => $data['currency'] ?? 'LKR',
                'currency_symbol' => $data['currency_symbol'] ?? 'Rs.',
                'tax_rate'        => $data['tax_rate'] ?? 0,
                'is_active'       => true,
            ]);

            // 3. Create admin user
            $password = $data['password'] ?? Str::random(12);
            $user = User::create([
                'tenant_id'  => $tenant->id,
                'store_id'   => $store->id,
                'name'       => $data['owner_name'],
                'email'      => $data['email'],
                'username'   => $data['username'] ?? Str::slug($data['owner_name'], '_'),
                'phone'      => $data['phone'] ?? null,
                'role'       => 'super_admin',
                'password'   => $password, // Will be auto-hashed by model cast
                'is_active'  => true,
                'payment_mode' => 'full',
            ]);

            // 4. Initialize features for this tenant
            $this->featureService->initializeForTenant($tenant->id);

            // 5. Enable selected features
            if (!empty($data['features'])) {
                foreach ($data['features'] as $featureCode => $enabled) {
                    if ($enabled) {
                        $this->featureService->enableFeature($tenant->id, $featureCode);
                    } else {
                        $this->featureService->disableFeature($tenant->id, $featureCode);
                    }
                }
            }

            Log::info("Client provisioned: {$tenant->code} — {$tenant->business_name}");

            return [
                'tenant'   => $tenant,
                'store'    => $store,
                'user'     => $user,
                'password' => $password,
                'deployment' => $this->generateDeploymentConfig($tenant),
            ];
        });
    }

    /**
     * Generate deployment configuration for a client.
     *
     * This documents what would be needed for a per-client deployment.
     */
    public function generateDeploymentConfig(Tenant $tenant): array
    {
        $slug = Str::slug($tenant->business_name);

        return [
            'subdomain'     => $slug . '.puretec.lk',
            'database_name' => 'purepos_' . Str::snake($tenant->code),
            'app_url'       => 'https://' . $slug . '.puretec.lk',
            'api_url'       => 'https://' . $slug . '.puretec.lk/api/v1',

            // These are documentation for manual server setup:
            'manual_steps' => [
                '1. Create database: CREATE DATABASE ' . 'purepos_' . Str::snake($tenant->code) . ';',
                '2. Clone master codebase to /var/www/' . $slug,
                '3. Configure .env with client-specific database credentials',
                '4. Run: php artisan migrate --seed',
                '5. Configure Nginx server block for ' . $slug . '.puretec.lk',
                '6. Configure SSL certificate (Let\'s Encrypt)',
                '7. Set APP_URL=' . 'https://' . $slug . '.puretec.lk',
                '8. Activate client in Puretec admin',
            ],

            'nginx_config' => $this->generateNginxConfig($slug),
            'env_template' => $this->generateEnvTemplate($tenant, $slug),
            'provisioning_script' => $this->generateBashScript($tenant, $slug),
        ];
    }

    /**
     * Generate automated bash provisioning script for VPS deployment.
     */
    protected function generateBashScript(Tenant $tenant, string $slug): string
    {
        $dbName = 'purepos_' . Str::snake($tenant->code);
        $domain = $slug . '.puretec.lk';

        return "#!/bin/bash\n" .
               "# =============================================================================\n" .
               "# Puretec Pure POS - VPS Client Provisioning Automation Script\n" .
               "# Tenant: {$tenant->business_name} ({$tenant->code})\n" .
               "# Subdomain: {$domain}\n" .
               "# Database: {$dbName}\n" .
               "# =============================================================================\n\n" .
               "set -euo pipefail\n\n" .
               "TENANT_CODE=\"{$tenant->code}\"\n" .
               "SLUG=\"{$slug}\"\n" .
               "DOMAIN=\"{$domain}\"\n" .
               "DB_NAME=\"{$dbName}\"\n" .
               "DB_USER=\"purepos_{$slug}\"\n" .
               "DB_PASS=\"\$(openssl rand -hex 12)\"\n" .
               "TARGET_DIR=\"/var/www/\${SLUG}\"\n" .
               "MASTER_DIR=\"/var/www/purepos-master\"\n" .
               "PHP_FPM_SOCK=\"/var/run/php/php8.2-fpm.sock\"\n\n" .
               "echo \"[1/6] Creating MySQL Database and User...\"\n" .
               "mysql -e \"CREATE DATABASE IF NOT EXISTS \\\`\${DB_NAME}\\\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;\"\n" .
               "mysql -e \"CREATE USER IF NOT EXISTS '\${DB_USER}'@'localhost' IDENTIFIED BY '\${DB_PASS}';\"\n" .
               "mysql -e \"GRANT ALL PRIVILEGES ON \\\`\${DB_NAME}\\\`.* TO '\${DB_USER}'@'localhost'; FLUSH PRIVILEGES;\"\n\n" .
               "echo \"[2/6] Copying codebase to \${TARGET_DIR}...\"\n" .
               "mkdir -p \"\${TARGET_DIR}\"\n" .
               "if [ -d \"\${MASTER_DIR}\" ]; then\n" .
               "    rsync -av --exclude='.git' --exclude='storage/logs/*' --exclude='storage/framework/cache/data/*' --exclude='.env' \"\${MASTER_DIR}/\" \"\${TARGET_DIR}/\"\n" .
               "fi\n\n" .
               "echo \"[3/6] Generating .env configuration...\"\n" .
               "cat << 'EOF' > \"\${TARGET_DIR}/.env\"\n" .
               $this->generateEnvTemplate($tenant, $slug) .
               "EOF\n\n" .
               "sed -i \"s/CHANGE_ME_TO_SECURE_PASSWORD/\${DB_PASS}/g\" \"\${TARGET_DIR}/.env\"\n\n" .
               "echo \"[4/6] Initializing Laravel, migrations and storage link...\"\n" .
               "cd \"\${TARGET_DIR}\"\n" .
               "php artisan key:generate --force\n" .
               "php artisan migrate --force\n" .
               "php artisan storage:link || true\n" .
               "chown -R www-data:www-data \"\${TARGET_DIR}\"\n" .
               "chmod -R 775 \"\${TARGET_DIR}/storage\" \"\${TARGET_DIR}/bootstrap/cache\"\n\n" .
               "echo \"[5/6] Deploying Nginx server configuration...\"\n" .
               "cat << 'EOF' > \"/etc/nginx/sites-available/\${DOMAIN}\"\n" .
               $this->generateNginxConfig($slug) .
               "EOF\n" .
               "ln -sf \"/etc/nginx/sites-available/\${DOMAIN}\" \"/etc/nginx/sites-enabled/\${DOMAIN}\"\n" .
               "nginx -t && systemctl reload nginx\n\n" .
               "echo \"[6/6] Requesting Let's Encrypt SSL Certificate...\"\n" .
               "certbot --nginx -d \"\${DOMAIN}\" --non-interactive --agree-tos -m \"admin@puretec.lk\" --redirect || echo \"Certbot can be executed once DNS resolves.\"\n\n" .
               "echo \"Provisioning complete for \${DOMAIN}\"\n";
    }

    /**
     * Generate Nginx configuration template.
     */
    protected function generateNginxConfig(string $slug): string
    {
        return "server {\n" .
               "    listen 80;\n" .
               "    listen [::]:80;\n" .
               "    server_name {$slug}.puretec.lk;\n" .
               "    return 301 https://\$server_name\$request_uri;\n" .
               "}\n\n" .
               "server {\n" .
               "    listen 443 ssl http2;\n" .
               "    listen [::]:443 ssl http2;\n" .
               "    server_name {$slug}.puretec.lk;\n\n" .
               "    root /var/www/{$slug}/public;\n" .
               "    index index.php;\n\n" .
               "    ssl_certificate /etc/letsencrypt/live/{$slug}.puretec.lk/fullchain.pem;\n" .
               "    ssl_certificate_key /etc/letsencrypt/live/{$slug}.puretec.lk/privkey.pem;\n\n" .
               "    location / {\n" .
               "        try_files \$uri \$uri/ /index.php?\$query_string;\n" .
               "    }\n\n" .
               "    location ~ \\.php\$ {\n" .
               "        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;\n" .
               "        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;\n" .
               "        include fastcgi_params;\n" .
               "    }\n\n" .
               "    location ~ /\\.(?!well-known).* {\n" .
               "        deny all;\n" .
               "    }\n" .
               "}\n";
    }

    /**
     * Generate .env template for a client deployment.
     */
    protected function generateEnvTemplate(Tenant $tenant, string $slug): string
    {
        return "APP_NAME=\"{$tenant->business_name} POS\"\n" .
               "APP_ENV=production\n" .
               "APP_KEY=base64:" . base64_encode(random_bytes(32)) . "\n" .
               "APP_DEBUG=false\n" .
               "APP_URL=https://{$slug}.puretec.lk\n\n" .
               "DB_CONNECTION=mysql\n" .
               "DB_HOST=127.0.0.1\n" .
               "DB_PORT=3306\n" .
               "DB_DATABASE=purepos_" . Str::snake($tenant->code) . "\n" .
               "DB_USERNAME=purepos_{$slug}\n" .
               "DB_PASSWORD=CHANGE_ME_TO_SECURE_PASSWORD\n\n" .
               "CACHE_DRIVER=file\n" .
               "SESSION_DRIVER=file\n" .
               "QUEUE_CONNECTION=sync\n\n" .
               "SANCTUM_STATEFUL_DOMAINS={$slug}.puretec.lk\n" .
               "CORS_ALLOWED_ORIGINS=https://{$slug}.puretec.lk\n\n" .
               "POS_CURRENCY=LKR\n" .
               "POS_CURRENCY_SYMBOL=Rs.\n" .
               "POS_TIMEZONE=Asia/Colombo\n";
    }

    /**
     * Suspend a client.
     */
    public function suspend(Tenant $tenant, string $reason = 'Suspended by admin'): void
    {
        $tenant->update([
            'is_active' => false,
            'is_locked' => true,
            'locked_at' => now(),
            'lock_reason' => $reason,
        ]);
        Log::warning("Client suspended: {$tenant->code} — {$reason}");
    }

    /**
     * Activate a client.
     */
    public function activate(Tenant $tenant): void
    {
        $tenant->update([
            'is_active' => true,
            'is_locked' => false,
            'locked_at' => null,
            'lock_reason' => null,
        ]);
        Log::info("Client activated: {$tenant->code}");
    }
}
