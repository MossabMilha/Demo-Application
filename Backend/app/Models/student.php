<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class student extends Model
{
    use HasFactory;
    protected $fillable = ['user_id', 'student_code', 'birth_date', 'class_level', 'section'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
