<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Sanctum\HasApiTokens;

class HistoryDetails extends Model
{
    use HasFactory, SoftDeletes, HasApiTokens, HasUuids;
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['id', 'name_disease', 'another_name_disease', 'symptom', 'reason', 'preventive_measure', 'source', 'confidence_score', 'history_id'];
    protected $table = 'history_details';

    public function history()
    {
        return $this->belongsTo(Histories::class, 'history_id', 'id');
    }
}
