<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Register extends Model
{
    protected $fillable = [
        'store_id','user_id','name','opening_balance','closing_balance','expected_balance',
        'cash_in','cash_out','total_sales','total_refunds','status','opened_at','closed_at','notes'
    ];
    protected $casts = ['opened_at' => 'datetime', 'closed_at' => 'datetime'];
    public function store(): BelongsTo { return $this->belongsTo(Store::class); }
    public function user(): BelongsTo  { return $this->belongsTo(User::class); }
    public function isOpen(): bool     { return $this->status === 'open'; }
}
