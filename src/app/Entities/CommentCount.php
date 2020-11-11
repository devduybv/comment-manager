<?php

namespace VCComponent\Laravel\Comment\Entities;

use Illuminate\Database\Eloquent\Model;
use VCComponent\Laravel\Comment\Traits\HasCommentTrait;

class CommentCount extends Model
{
    use HasCommentTrait;

    const STATUS_PENDING = 0;
    const STATUS_ACTIVE  = 1;

    protected $fillable = [
        'commentable_id',
        'commentable_type',
        'count'
    ];

    public function commentCountable()
    {
        return $this->morphTo();
    }
}
