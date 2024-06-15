<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Friend extends Model
{
    protected $fillable = [
        'user_id',
        'friend_id',
        'status'
    ];
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}


