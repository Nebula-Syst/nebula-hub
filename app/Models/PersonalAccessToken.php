<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PersonalAccessToken extends Model
{
    protected $fillable = [
        'name',
        'token',
        'last_used_at',
    ];

    protected $hidden = [
        'token',
    ];

    protected $casts = [
        'last_used_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
