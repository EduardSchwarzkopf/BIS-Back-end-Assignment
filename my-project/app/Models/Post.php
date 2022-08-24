<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Post extends Model
{
    use HasFactory;


    protected $fillable = [
        'subject',
        'content',
        'description'
    ];


    protected static function booted()
    {
        static::creating(function ($post) {
            $post->user_id = Auth::id();
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
