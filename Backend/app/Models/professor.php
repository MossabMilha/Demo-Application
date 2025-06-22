<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class professor extends Model
{
    use HasFactory;
    protected $fillable = ['user_id', 'department', 'grade', 'phone'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
