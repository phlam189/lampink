<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Team extends Model
{
    protected $fillable = [
        'name',
        'leader_id',
    ];

    public function leader(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'leader_id');
    }

    public function members(): HasMany
    {
        return $this->hasMany(User::class, 'team_id');
    }
}
