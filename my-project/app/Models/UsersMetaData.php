<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UsersMetaData extends Model
{
    use HasFactory;

    protected $fillable = [
        'surname',
        'nickname',
        'phone',
        'address',
        'city',
        'state',
        'zip',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
