<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Comment extends Model
{
    use HasFactory, SoftDeletes, Prunable;

    protected $fillable = [
        'post_id',
        'content',
    ];

    public function prunable()
    {
        return static::where('created_at', '<=', now()->subHours(3));
    }

    protected static function booted()
    {
        static::creating(function ($comment) {
            $comment->user_id = Auth::id();
        });
    }

    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
