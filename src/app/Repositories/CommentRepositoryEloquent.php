<?php

namespace VCComponent\Laravel\Comment\Repositories;

use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Eloquent\BaseRepository;
use VCComponent\Laravel\Comment\Entities\Comment;
use VCComponent\Laravel\Comment\Repositories\CommentRepository;
use VCComponent\Laravel\Vicoders\Core\Exceptions\NotFoundException;

class CommentRepositoryEloquent extends BaseRepository implements CommentRepository
{
    public function model()
    {
        return config('comment.models.comment');
    }

    public function getEntity()
    {
        return $this->model;
    }

    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

    public function updateStatus($request, $id)
    {
        $updateStatus         = $this->find($id);
        $updateStatus->status = $request->input('status');
        $updateStatus->save();
    }

    public function bulkUpdateStatus($request)
    {
        $data     = $request->all();
        $comments = $this->findWhereIn("id", $request->id);

        if (count($request->id) > $comments->count()) {
            throw new NotFoundException("Comment");
        }

        $result = $this->model->whereIn("id", $request->id)->update(['status' => $data['status']]);

        return $result;
    }

    public function findById($id)
    {
        $comment = $this->model->find($id);
        if (!$comment) {
            throw new NotFoundException('Comment');
        }
        return $comment;
    }
}
