<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Setting extends Model
{
    protected $fillable = ['store_id','key','value','type'];
    public function store(): BelongsTo { return $this->belongsTo(Store::class); }

    public static function get(int $storeId, string $key, $default = null)
    {
        $setting = static::where('store_id', $storeId)->where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    public static function set(int $storeId, string $key, $value, string $type = 'string'): void
    {
        static::updateOrCreate(
            ['store_id' => $storeId, 'key' => $key],
            ['value' => $value, 'type' => $type]
        );
    }
}
