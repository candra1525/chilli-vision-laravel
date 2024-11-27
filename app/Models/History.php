<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class History extends Model
{
    use HasFactory;
    protected $primaryKey = 'id';
    protected $fillable = ['title', 'image', 'description'];
    protected $table = 'history';

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
