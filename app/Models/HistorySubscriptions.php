<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class HistorySubscriptions extends Model
{
    use HasApiTokens, HasFactory, SoftDeletes, HasUuids;

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['id', 'start_date', 'end_date', 'status', 'payment_method', 'image_transaction', 'user_id', 'subscription_id'];

    protected $table = 'history_subscriptions';

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function subscriptions()
    {
        return $this->belongsTo(Subscriptions::class, 'subscription_id', 'id');
    }
}
