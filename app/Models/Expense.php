<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'store_id','expense_category_id','user_id','title','amount','expense_date',
        'payment_method','reference_number','attachment','notes','is_recurring','recurring_interval'
    ];
    protected $casts = ['expense_date' => 'date', 'amount' => 'decimal:2', 'is_recurring' => 'boolean'];
    public function store(): BelongsTo    { return $this->belongsTo(Store::class); }
    public function category(): BelongsTo { return $this->belongsTo(ExpenseCategory::class, 'expense_category_id'); }
    public function user(): BelongsTo     { return $this->belongsTo(User::class); }
}
