<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Novel extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'title',
        'slug',
        'author_id',
        'uploader_id',
        'team_id',
        'status',
        'type',
        'revenue_share_rate',
        'views_total',
        'rating_avg',
        'is_hot',
    ];

    public function author()
    {
        return $this->belongsTo(Author::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploader_id');
    }

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function chapters()
    {
        return $this->hasMany(Chapter::class);
    }
}
