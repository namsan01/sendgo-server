<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;

    protected $fillable = [
        'content_id',
        'user_id',
        'content',
        'is_admin'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function associatedContent()
    {
        return $this->belongsTo(Content::class, 'content_id');
    }
}
