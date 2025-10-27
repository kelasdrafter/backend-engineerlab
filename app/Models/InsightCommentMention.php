<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InsightCommentMention extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'comment_id',
        'mentioned_user_id',
    ];

    /**
     * Relationship: Mention belongs to comment
     */
    public function comment()
    {
        return $this->belongsTo(InsightComment::class, 'comment_id');
    }

    /**
     * Relationship: Mention belongs to mentioned user
     */
    public function mentionedUser()
    {
        return $this->belongsTo(User::class, 'mentioned_user_id');
    }
}