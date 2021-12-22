<?php

namespace VCComponent\Laravel\Comment\Http\Controllers\Web;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use VCComponent\Laravel\Comment\Actions\CommentCountAction;
use VCComponent\Laravel\Comment\Entities\Comment;

class CommentController extends BaseController
{
    public function __construct(CommentCountAction $action)
    {
        $this->action = $action;
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'content' => 'required',
        ]);

        if ($validator->fails()) {
            return Redirect::back()->withErrors($validator);
        }

        $username = !$request['user'] ? 'user' : $request['user'];
        $parent_id = !$request['parent_id'] ? 0 : $request->input('parent_id');

        Comment::create([
            'commentable_id' => $request->input('commentable_id'),
            'commentable_type' => $request->input('commentable_type'),
            'email' => $request->input('email'),
            'name' => $username,
            'content' => $request->input('content'),
            'parent_id' => $parent_id,
        ]);

        $dataCount = [
            'commentable_id' => $request->input('commentable_id'),
            'commentable_type' => $request->input('commentable_type'),
        ];

        $this->action->addComment($dataCount);

        return redirect()->back();

    }
}
