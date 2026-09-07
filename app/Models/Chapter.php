<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Chapter extends Model
{
    protected $fillable = [
        'novel_id',
        'chapter_number',
        'title',
        'price_coins',
        'is_vip',
        'next_chapter_id',
        'prev_chapter_id',
        'views_count',
    ];

    public function novel()
    {
        return $this->belongsTo(Novel::class);
    }

    public function nextChapter()
    {
        return $this->belongsTo(Chapter::class, 'next_chapter_id');
    }

    public function prevChapter()
    {
        return $this->belongsTo(Chapter::class, 'prev_chapter_id');
    }

}
