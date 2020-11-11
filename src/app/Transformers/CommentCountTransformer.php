<?php

namespace VCComponent\Laravel\Comment\Transformers;

use League\Fractal\TransformerAbstract;
use VCComponent\Laravel\Comment\Entities\CommentCount;

class CommentCountTransformer extends TransformerAbstract
{
    public function transform(CommentCount $model)
    {
        return [
            'id'               => (int) $model->id,
            'commentable_id'   => (int) $model->commentable_id,
            'commentable_type' => $model->commentable_type,
            'count'            => $model->count,
            'timestamps'       => [
                'created_at' => $model->created_at,
                'updated_at' => $model->updated_at,
            ],
        ];
    }
}
