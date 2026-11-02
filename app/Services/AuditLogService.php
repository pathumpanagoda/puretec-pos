<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditLogService
{
    /**
     * Log an action to the activity_logs table.
     *
     * @param string      $action      e.g. 'login', 'product.create', 'order.refund'
     * @param string|null $description Human-readable description
     * @param string|null $modelType   e.g. 'Product', 'Order'
     * @param int|null    $modelId     ID of the affected model
     * @param array|null  $oldValues   Previous values (for updates)
     * @param array|null  $newValues   New values (for creates/updates)
     * @param string      $severity    'info', 'warning', 'critical'
     */
    public function log(
        string  $action,
        ?string $description = null,
        ?string $modelType = null,
        ?int    $modelId = null,
        ?array  $oldValues = null,
        ?array  $newValues = null,
        string  $severity = 'info'
    ): ActivityLog {
        $user = Auth::user();

        return ActivityLog::create([
            'store_id'    => $user?->store_id,
            'user_id'     => $user?->id,
            'action'      => $action,
            'model_type'  => $modelType,
            'model_id'    => $modelId,
            'old_values'  => $oldValues,
            'new_values'  => $newValues,
            'ip_address'  => Request::ip(),
            'device_id'   => Request::header('X-Device-ID'),
            'user_agent'  => substr(Request::userAgent() ?? '', 0, 255),
            'description' => $description,
            'severity'    => $severity,
        ]);
    }

    /**
     * Log a login event.
     */
    public function logLogin(string $method = 'web'): ActivityLog
    {
        return $this->log(
            'auth.login',
            'User logged in via ' . $method,
            'User',
            Auth::id()
        );
    }

    /**
     * Log a logout event.
     */
    public function logLogout(): ActivityLog
    {
        return $this->log(
            'auth.logout',
            'User logged out'
        );
    }

    /**
     * Log a failed login attempt.
     */
    public function logFailedLogin(string $identifier): ActivityLog
    {
        return $this->log(
            'auth.failed_login',
            "Failed login attempt for: {$identifier}",
            null,
            null,
            null,
            ['identifier' => $identifier],
            'warning'
        );
    }

    /**
     * Log a model creation.
     */
    public function logCreated(string $modelType, $model, ?string $description = null): ActivityLog
    {
        return $this->log(
            strtolower($modelType) . '.create',
            $description ?? "{$modelType} created",
            $modelType,
            $model->id,
            null,
            $this->summarizeModel($model)
        );
    }

    /**
     * Log a model update (with old/new diff).
     */
    public function logUpdated(string $modelType, $model, array $oldValues, ?string $description = null): ActivityLog
    {
        $changes = $model->getChanges();
        // Don't log unchanged or sensitive fields
        unset($changes['updated_at'], $changes['password'], $changes['remember_token']);
        unset($oldValues['updated_at'], $oldValues['password'], $oldValues['remember_token']);

        if (empty($changes)) {
            return $this->log(
                strtolower($modelType) . '.update',
                $description ?? "{$modelType} updated (no visible changes)",
                $modelType,
                $model->id
            );
        }

        return $this->log(
            strtolower($modelType) . '.update',
            $description ?? "{$modelType} updated",
            $modelType,
            $model->id,
            array_intersect_key($oldValues, $changes),
            $changes
        );
    }

    /**
     * Log a model deletion.
     */
    public function logDeleted(string $modelType, $model, ?string $description = null): ActivityLog
    {
        return $this->log(
            strtolower($modelType) . '.delete',
            $description ?? "{$modelType} deleted",
            $modelType,
            $model->id,
            $this->summarizeModel($model),
            null,
            'warning'
        );
    }

    /**
     * Log a critical action (refund, stock adjustment, permission change).
     */
    public function logCritical(string $action, string $description, ?string $modelType = null, ?int $modelId = null, ?array $data = null): ActivityLog
    {
        return $this->log(
            $action,
            $description,
            $modelType,
            $modelId,
            null,
            $data,
            'critical'
        );
    }

    /**
     * Log sync operations.
     */
    public function logSync(string $direction, string $entityType, string $status, ?string $details = null): ActivityLog
    {
        return $this->log(
            "sync.{$direction}",
            "Sync {$direction}: {$entityType} — {$status}" . ($details ? " ({$details})" : ''),
            null,
            null,
            null,
            ['entity_type' => $entityType, 'status' => $status],
            $status === 'failed' ? 'warning' : 'info'
        );
    }

    /**
     * Extract key fields from a model for logging (avoid logging huge objects).
     */
    protected function summarizeModel($model): array
    {
        $data = $model->toArray();
        // Remove sensitive/large fields
        unset($data['password'], $data['remember_token'], $data['pin'],
              $data['security_answer'], $data['gallery'], $data['settings']);
        // Truncate arrays
        if (count($data) > 20) {
            $data = array_slice($data, 0, 20, true);
            $data['_truncated'] = true;
        }
        return $data;
    }
}
