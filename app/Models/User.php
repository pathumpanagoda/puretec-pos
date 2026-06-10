<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'tenant_id','store_id','name','email','username','phone','avatar',
        'role','permissions','payment_mode','password','is_active','commission_rate','pin',
        'last_login_at','last_login_ip','email_verified_at',
        'password_changed_at','password_changed_by',
        'security_question','security_answer'
    ];

    protected $hidden = ['password','remember_token','pin','security_answer'];
    protected $casts  = [
        'email_verified_at'   => 'datetime',
        'last_login_at'       => 'datetime',
        'password_changed_at' => 'datetime',
        'is_active'           => 'boolean',
        'password'            => 'hashed',
        'permissions'         => 'array',
    ];

    public function tenant(): BelongsTo  { return $this->belongsTo(Tenant::class); }
    public function store(): BelongsTo  { return $this->belongsTo(Store::class); }
    public function orders(): HasMany   { return $this->hasMany(Order::class); }
    public function expenses(): HasMany { return $this->hasMany(Expense::class); }

    public function isSuperAdmin(): bool { return $this->role === 'super_admin'; }
    public function isAdmin(): bool      { return in_array($this->role, ['super_admin','admin']); }
    public function isManager(): bool    { return in_array($this->role, ['super_admin','admin','manager']); }
    public function isCashier(): bool    { return $this->role === 'cashier'; }

    /**
     * Check if user is in bill-only mode.
     */
    public function isBillOnlyMode(): bool
    {
        // Check season mode first - if enabled, everyone has full access
        if ($this->store && ($this->store->settings['season_mode'] ?? false)) {
            return false;
        }
        return $this->payment_mode === 'bill_only';
    }

    /**
     * Check if user can process payments.
     */
    public function canProcessPayment(): bool
    {
        // Check season mode first
        if ($this->store && ($this->store->settings['season_mode'] ?? false)) {
            return true;
        }
        return $this->payment_mode === 'full' || $this->isManager();
    }

    /**
     * Check if user can access a specific permission.
     * Priority: 1) Super admin = all, 2) Custom permissions, 3) Default role permissions
     */
    public function canAccess(string $permission): bool
    {
        // 1. Super admin can do everything
        if ($this->role === 'super_admin') {
            return true;
        }

        // 2. If custom permissions are set, use those
        if (!empty($this->permissions) && is_array($this->permissions)) {
            return in_array($permission, $this->permissions);
        }

        // 3. Otherwise use default role permissions
        return in_array($permission, $this->getRolePermissions());
    }

    /**
     * Get default permissions for the user's role.
     */
    public function getRolePermissions(): array
    {
        $rolePermissions = [
            'super_admin' => ['*'],
            'admin'       => ['dashboard','pos','products','categories','customers','suppliers','orders','purchases','inventory','employees','expenses','reports','settings','users'],
            'manager'     => ['dashboard','pos','products','categories','customers','suppliers','orders','purchases','inventory','expenses','reports'],
            'cashier'     => ['dashboard','pos','customers','orders'],
            'inventory'   => ['dashboard','products','categories','suppliers','purchases','inventory'],
            'viewer'      => ['dashboard','reports'],
        ];

        return $rolePermissions[$this->role] ?? [];
    }

    /**
     * Get all available permissions for checkbox display.
     */
    public static function getAllPermissions(): array
    {
        return [
            'dashboard'  => 'Dashboard',
            'pos'        => 'Point of Sale (POS)',
            'products'   => 'Products',
            'categories' => 'Categories',
            'inventory'  => 'Inventory',
            'customers'  => 'Customers',
            'suppliers'  => 'Suppliers',
            'orders'     => 'Orders',
            'purchases'  => 'Purchases',
            'expenses'   => 'Expenses',
            'reports'    => 'Reports',
            'settings'   => 'Settings',
        ];
    }
}
