<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Store extends Model
{
    protected $fillable = [
        'tenant_id','name','code','address','city','country','phone','email',
        'logo','currency','currency_symbol','tax_rate',
        'receipt_header','receipt_footer','is_active','settings'
    ];

    protected $casts = ['settings' => 'array', 'is_active' => 'boolean'];

    // Tenant relationship
    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }

    public function users(): HasMany    { return $this->hasMany(User::class); }
    public function products(): HasMany { return $this->hasMany(Product::class); }
    public function categories(): HasMany { return $this->hasMany(Category::class); }
    public function customers(): HasMany { return $this->hasMany(Customer::class); }
    public function orders(): HasMany   { return $this->hasMany(Order::class); }
    public function suppliers(): HasMany{ return $this->hasMany(Supplier::class); }
    public function expenses(): HasMany { return $this->hasMany(Expense::class); }
    public function settings(): HasMany { return $this->hasMany(Setting::class); }
}
