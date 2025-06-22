<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'description', 'coefficient', 'choice_id'];

    public function choice()
    {
        return $this->belongsTo(Choice::class);
    }
}
