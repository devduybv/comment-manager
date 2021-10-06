<?php

namespace VCComponent\Laravel\Comment\Http\Controllers\Api\Admin;

use Exception;
use Illuminate\Http\Request;
use VCComponent\Laravel\Comment\Actions\CommentCountAction;
use VCComponent\Laravel\Comment\Entities\Comment;
use VCComponent\Laravel\Comment\Entities\CommentCount;
use VCComponent\Laravel\Comment\Events\CommentCreatedByAdminEvent;
use VCComponent\Laravel\Comment\Events\CommentDeletedByAdminEvent;
use VCComponent\Laravel\Comment\Events\CommentUpdatedByAdminEvent;
use VCComponent\Laravel\Comment\Repositories\CommentRepository;
use VCComponent\Laravel\Comment\Validators\CommentValidator;
use VCComponent\Laravel\Vicoders\Core\Controllers\ApiController;

class CommentController extends ApiController
{
    protected $repository;
    protected $validator;

    public function __construct(CommentRepository $repository, CommentValidator $validator, CommentCountAction $action)
    {
        $this->repository  = $repository;
        $this->entity      = $repository->getEntity();
        $this->validator   = $validator;
        $this->transformer = config('comment.transformers.comment');
        $this->action      = $action;

        if (config('comment.auth_middleware.admin.middleware') !== '') {
            $this->middleware(
                config('comment.auth_middleware.admin.middleware'),
                ['except' => config('comment.auth_middleware.admin.except')]
            );
        }
        else{
            throw new Exception("Admin middleware configuration is required");
        }

    }

    public function index(Request $request)
    {
        $query = $this->entity;

        $query    = $this->applyConstraintsFromRequest($query, $request);
        $query    = $this->applySearchFromRequest($query, [], $request);
        $query    = $this->applyOrderByFromRequest($query, $request);
        $per_page = $request->has('per_page') ? (int) $request->get('per_page') : 15;
        $comments = $query->paginate($per_page);

        if ($request->has('includes')) {
            $transformer = new $this->transformer(explode(',', $request->get('includes')));
        } else {
            $transformer = new $this->transformer;
        }

        return $this->response->paginator($comments, $transformer);
    }

    function list(Request $request) {

        $query = $this->entity;

        $query = $this->applyConstraintsFromRequest($query, $request);
        $query = $this->applySearchFromRequest($query, ['name'], $request);
        $query = $this->applyOrderByFromRequest($query, $request);

        if ($request->has('order_id')) {
            $query->where('order_id', $request->query('order_id'));
        }

        $comments = $query->get();

        if ($request->has('includes')) {
            $transformer = new $this->transformer(explode(',', $request->get('includes')));
        } else {
            $transformer = new $this->transformer;
        }

        return $this->response->collection($comments, $transformer);
    }

    public function store(Request $request)
    {
        $this->validator->isValid($request, 'RULE_ADMIN_CREATE');

        $data    = $request->all();
        $comment = $this->repository->create($data);

        $this->action->addComment($data);

        event(new CommentCreatedByAdminEvent($comment));

        return $this->response->item($comment, new $this->transformer);
    }

    public function update(Request $request, $id)
    {
        $this->validator->isValid($request, 'RULE_ADMIN_UPDATE');

        $this->repository->findById($id);

        $data    = $request->all();
        $comment = $this->repository->update($data, $id);

        event(new CommentUpdatedByAdminEvent($comment));

        return $this->response->item($comment, new $this->transformer);
    }

    public function show($id, Request $request)
    {
        $comment = $this->repository->findById($id);

        if ($request->has('includes')) {
            $transformer = new $this->transformer(explode(',', $request->get('includes')));
        } else {
            $transformer = new $this->transformer;
        }

        return $this->response->item($comment, $transformer);
    }

    public function destroy($id)
    {
        $comment = $this->repository->findById($id);

        $comments = Comment::where('commentable_id', $comment->commentable_id)->where('commentable_type', $comment->commentable_type)->first();

        $data = [
            'commentable_id'   => $comments->commentable_id,
            'commentable_type' => $comments->commentable_type,
        ];

        $this->action->delComment($data);
        $comment->delete();

        event(new CommentDeletedByAdminEvent($comment));

        return $this->success();
    }

    public function updateStatus(Request $request, $id)
    {
        $this->validator->isValid($request, 'UPDATE_STATUS_ITEM');

        $this->repository->findById($id);

        $this->repository->updateStatus($request, $id);
        return $this->success();
    }

    public function bulkUpdateStatus(Request $request)
    {
        $this->validator->isValid($request, 'BULK_UPDATE_STATUS');

        $this->repository->bulkUpdateStatus($request);

        return $this->success();
    }
}
