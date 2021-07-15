<?php

use Faker\Generator as Faker;
use VCComponent\Laravel\Comment\Entities\Comment;

$factory->define(Comment::class, function (Faker $faker) {
    return [
        'commentable_id'    => rand(1, 2),
        'email'             => $faker->email,
        'name'              => $faker->word,
        'content'           => $faker->paragraphs(rand(4, 7), true),
    ];
});

$factory->state(Comment::class, 'product', function (Faker $faker) {
    return [
        'commentable_type' => 'product',
    ];
});

$factory->state(Comment::class, 'post', function (Faker $faker) {
    return [
        'commentable_type' => 'post',
    ];
});