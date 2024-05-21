<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'upload_ref',
        'image_name', 
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
