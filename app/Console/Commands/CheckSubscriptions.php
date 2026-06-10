<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tenant;
use App\Models\TenantSubscription;
use Carbon\Carbon;

class CheckSubscriptions extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'subscriptions:check
                            {--lock : Auto-lock tenants with overdue payments}
                            {--generate : Generate subscriptions for current month}';

    /**
     * The console command description.
     */
    protected $description = 'Check subscription status and optionally lock overdue tenants';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Checking subscription status...');

        // Generate subscriptions for current month if flag is set
        if ($this->option('generate')) {
            $this->generateCurrentMonthSubscriptions();
        }

        // Mark overdue subscriptions
        $this->markOverdueSubscriptions();

        // Auto-lock tenants if flag is set
        if ($this->option('lock')) {
            $this->lockOverdueTenants();
        }

        $this->info('Subscription check completed.');

        return Command::SUCCESS;
    }

    /**
     * Generate subscriptions for the current month.
     */
    private function generateCurrentMonthSubscriptions(): void
    {
        $currentMonth = Carbon::now()->format('Y-m');

        $tenants = Tenant::where('is_active', true)
            ->whereDoesntHave('subscriptions', function ($query) use ($currentMonth) {
                $query->where('billing_month', $currentMonth);
            })
            ->get();

        $count = 0;
        foreach ($tenants as $tenant) {
            // Skip tenants still on trial
            if ($tenant->isOnTrial()) {
                continue;
            }

            TenantSubscription::createForMonth($tenant, $currentMonth);
            $count++;
        }

        $this->info("Generated {$count} subscriptions for {$currentMonth}.");
    }

    /**
     * Mark pending subscriptions as overdue if past due date.
     */
    private function markOverdueSubscriptions(): void
    {
        $overdueCount = TenantSubscription::where('status', 'pending')
            ->where('due_date', '<', Carbon::today())
            ->update(['status' => 'overdue']);

        $this->info("Marked {$overdueCount} subscriptions as overdue.");
    }

    /**
     * Lock tenants with overdue payments (end of month check).
     */
    private function lockOverdueTenants(): void
    {
        $today = Carbon::today();

        // Only auto-lock on the last day of the month or after
        if (!$today->isLastOfMonth() && $today->day < 28) {
            $this->info('Auto-lock only runs on the last day of the month.');
            return;
        }

        // Find tenants with overdue subscriptions who aren't already locked
        $tenantsToLock = Tenant::where('is_active', true)
            ->where('is_locked', false)
            ->whereHas('subscriptions', function ($query) {
                $query->whereIn('status', ['pending', 'overdue'])
                    ->where('due_date', '<', Carbon::today());
            })
            ->get();

        $lockedCount = 0;
        foreach ($tenantsToLock as $tenant) {
            $tenant->lock('Auto-locked: Payment overdue');

            // Mark subscription as locked
            $tenant->subscriptions()
                ->whereIn('status', ['pending', 'overdue'])
                ->update(['status' => 'locked']);

            $lockedCount++;
            $this->warn("Locked tenant: {$tenant->business_name} ({$tenant->code})");
        }

        $this->info("Auto-locked {$lockedCount} tenants with overdue payments.");
    }
}
