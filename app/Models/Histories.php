<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Sanctum\HasApiTokens;

class Histories extends Model
{
    use HasFactory, SoftDeletes, HasApiTokens, HasUuids;
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['id', 'title', 'image', 'description', 'user_id'];
    protected $table = 'histories';

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
