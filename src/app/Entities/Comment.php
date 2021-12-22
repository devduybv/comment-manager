<?php

namespace VCComponent\Laravel\Comment\Entities;

use Illuminate\Database\Eloquent\Model;
use VCComponent\Laravel\Comment\Traits\HasCommentTrait;
use VCComponent\Laravel\Post\Entities\Post;
use VCComponent\Laravel\Product\Entities\Product;

class Comment extends Model
{
    use HasCommentTrait;

    const STATUS_PENDING = 0;
    const STATUS_ACTIVE = 1;

    protected $fillable = [
        'commentable_id',
        'commentable_type',
        'email',
        'name',
        'content',
        'parent_id',
    ];

    public function commentable()
    {
        return $this->morphTo();
    }

    public function post()
    {
        return $this->belongsTo(Post::class, 'commentable_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'commentable_id');
    }
    public function children()
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('id', 'desc');
    }
}
