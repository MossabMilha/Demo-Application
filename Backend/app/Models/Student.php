<?php

namespace App\Models;

use DateTime;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;
    protected $fillable = ['user_id', 'student_code', 'birth_date', 'class_level', 'section'];

    public static function checkStudent_code($student_code){
        return preg_match('/^[A-Za-z][0-9]+$/', $student_code) === 1;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
