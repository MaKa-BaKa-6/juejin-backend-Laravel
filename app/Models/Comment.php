<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;
    protected $guarded = [];
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
    public function replies()
    {
        return $this->hasMany(Comment::class, 'parent_id', 'id');
    }
    public function replyToUser()
    {
        return $this->belongsTo(User::class, 'reply_to_user_id', 'id');
    }
}
