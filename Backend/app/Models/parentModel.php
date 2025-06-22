<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class parentModel extends Model
{
    use HasFactory;
    protected $table = 'parents';
    protected $fillable = ['user_id', 'phone', 'occupation'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
