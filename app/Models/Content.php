<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Content extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'content',
        'status',
        'user_id',
    ];

    /**
     * 작성자와의 관계를 정의합니다.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
