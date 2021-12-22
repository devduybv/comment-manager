<?php

namespace VCComponent\Laravel\Comment\Http\Controllers\Api\Fontend;

use Illuminate\Http\Request;
use VCComponent\Laravel\Comment\Actions\CommentCountAction;
use VCComponent\Laravel\Comment\Events\CommentCreatedEvent;
use VCComponent\Laravel\Comment\Repositories\CommentRepository;
use VCComponent\Laravel\Comment\Validators\CommentValidator;
use VCComponent\Laravel\Vicoders\Core\Controllers\ApiController;

class CommentController extends ApiController
{
    protected $repository;
    protected $validator;

    public function __construct(CommentRepository $repository, CommentValidator $validator, CommentCountAction $action)
    {
        $this->repository = $repository;
        $this->entity = $repository->getEntity();
        $this->validator = $validator;
        $this->transformer = config('comment.transformers.comment');
        $this->action = $action;

        if (config('comment.auth_middleware.frontend.middleware') !== '') {
            $this->middleware(
                config('comment.auth_middleware.frontend.middleware'),
                ['except' => config('comment.auth_middleware.frontend.except')]
            );
        }

    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function index(Request $request)
    {
        $query = $this->entity;

        $query = $this->applyConstraintsFromRequest($query, $request);
        $query = $this->applySearchFromRequest($query, [], $request);
        $query = $this->applyOrderByFromRequest($query, $request);

        $per_page = $request->has('per_page') ? (int) $request->get('per_page') : 15;
        $comments = $query->paginate($per_page);

        return $this->response->paginator($comments, new $this->transformer);
    }

    function list(Request $request) {
        $query = $this->entity;

        $query = $this->applyConstraintsFromRequest($query, $request);
        $query = $this->applySearchFromRequest($query, [], $request);
        $query = $this->applyOrderByFromRequest($query, $request);

        if ($request->has('order_id')) {
            $query->where('order_id', $request->query('order_id'));
        }

        $comments = $query->get();

        return $this->response->collection($comments, new $this->transformer);
    }

    public function store(Request $request)
    {

        $this->validator->isValid($request, 'RULE_CREATE');

        $data = $request->all();
        $comment = $this->repository->create($data);

        $this->action->addComment($data);

        event(new CommentCreatedEvent($comment));

        return $this->response->item($comment, new $this->transformer);
    }

    public function show($id)
    {
        $comment = $this->repository->findById($id);
        return $this->response->item($comment, new $this->transformer);
    }
    public function report(Request $request, $id)
    {
        $this->validator->isValid($request, 'UPDATE_STATUS_ITEM');

        $this->repository->findById($id);

        $this->repository->updateStatus($request, $id);
        return $this->success();
    }
}
