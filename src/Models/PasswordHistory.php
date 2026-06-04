<?php

namespace ImranSaleem\SecuritySuite\Models;

use Illuminate\Database\Eloquent\Model;

class PasswordHistory extends Model
{
    protected $fillable = ['user_id', 'password'];

    public function user()
    {
        return $this->belongsTo(config('auth.providers.users.model'));
    }
}
