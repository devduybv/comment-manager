<?php

namespace VCComponent\Laravel\Comment\Test\Stubs\Validators;

use VCComponent\Laravel\Vicoders\Core\Validators\AbstractValidator;
use VCComponent\Laravel\Vicoders\Core\Validators\ValidatorInterface;

class CommentValidator extends AbstractValidator
{
    protected $rules = [
        ValidatorInterface::RULE_ADMIN_CREATE        => [
            'email'   => ['bail', 'required', 'email'],
            'name'    => ['bail', 'required', 'alpha_dash'],
            'content' => ['bail', 'required', 'filled'],
        ],
        ValidatorInterface::RULE_ADMIN_UPDATE        => [
            'email'   => ['bail', 'required', 'email'],
            'name'    => ['bail', 'required', 'alpha_dash'],
            'content' => ['bail', 'required', 'filled'],
        ],
        ValidatorInterface::RULE_CREATE        => [
            'email'   => ['bail', 'required', 'email'],
            'name'    => ['bail', 'required', 'alpha_dash'],
            'content' => ['bail', 'required', 'filled'],
        ],
        ValidatorInterface::RULE_UPDATE        => [
            'email'   => ['bail', 'required', 'email'],
            'name'    => ['bail', 'required', 'alpha_dash'],
            'content' => ['bail', 'required', 'filled'],
        ],
        ValidatorInterface::BULK_UPDATE_STATUS => [
            'id'     => ['required'],
            'status' => ['required'],
        ],
        ValidatorInterface::UPDATE_STATUS_ITEM => [
            'status' => ['required'],
        ]
    ];
}
