<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Category extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'uuid','store_id','parent_id','name','slug','code','description',
        'image','color','icon','sort_order','is_active'
    ];
    protected $casts = ['is_active' => 'boolean'];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($category) {
            if (empty($category->uuid)) {
                $category->uuid = Str::uuid()->toString();
            }
        });
    }

    public function store(): BelongsTo    { return $this->belongsTo(Store::class); }
    public function parent(): BelongsTo  { return $this->belongsTo(Category::class, 'parent_id'); }
    public function children(): HasMany  { return $this->hasMany(Category::class, 'parent_id'); }
    public function products(): HasMany  { return $this->hasMany(Product::class); }

    public function getProductCountAttribute(): int
    {
        return $this->products()->where('is_active', true)->count();
    }
}
