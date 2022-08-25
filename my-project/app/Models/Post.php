<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Post extends Model
{
    use HasFactory, SoftDeletes, Prunable;


    protected $fillable = [
        'subject',
        'content',
        'description'
    ];

    public function prunable()
    {
        return static::where('created_at', '<=', now()->subHours(3));
    }

    protected $with = ['user', 'comments'];

    protected static function booted()
    {
        static::creating(function ($post) {

            $post->user_id = Auth::id();

            if (app()->runningInConsole()) {
                $post->user_id = User::all('id')->first()->id;
            }
        });
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
