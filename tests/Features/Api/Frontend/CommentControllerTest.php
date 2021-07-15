<?php

namespace VCComponent\Laravel\Comment\Test\Features\Api\Frontend;

use Illuminate\Foundation\Testing\RefreshDatabase;
use VCComponent\Laravel\Comment\Entities\Comment;
use VCComponent\Laravel\Comment\Test\TestCase;

class CommentControllerTest extends TestCase {

    use RefreshDatabase;

    /** @test */
    public function can_get_pagianted_comments() {
        $comments = factory(Comment::class, 5)->state('post')->create();
        $comments = $comments->map(function ($comment) {
            unset($comment['created_at']);
            unset($comment['updated_at']);
            return $comment;
        })->toArray();

        $listIds = array_column($comments, 'id');
        array_multisort($listIds, SORT_DESC, $comments);

        $response = $this->call('GET', 'api/comments');

        $response->assertStatus(200);
        $response->assertJson([
            'data'=> $comments
        ]);
        $response->assertJsonStructure([
            'data' => [],
            'meta' => [
                'pagination' => [
                    'total', 'count', 'per_page', 'current_page', 'total_pages', 'links' => [],
                ],
            ],
        ]);
    }

    /** @test */
    public function can_get_pagianted_comments_with_constraints() {
        $comments = factory(Comment::class, 5)->state('post')->create();
        $name_constraints = $comments[0]->name;
        $email_constraints = $comments[0]->email;
        $comments = $comments->map(function ($comment) {
            unset($comment['created_at']);
            unset($comment['updated_at']);
            return $comment;
        })->toArray();

        $constraints = '{"name":"'.$name_constraints.'", "email":"'.$email_constraints.'"}';

        $response = $this->call('GET', 'api/comments?constraints='.$constraints);

        $response->assertStatus(200);
        $response->assertJson([
            'data'=> [$comments[0]]
        ]);
        $response->assertJsonStructure([
            'data' => [],
            'meta' => [
                'pagination' => [
                    'total', 'count', 'per_page', 'current_page', 'total_pages', 'links' => [],
                ],
            ],
        ]);
    }

    /** @test */
    public function can_get_panigated_comments_with_search()
    {
        $comments = factory(Comment::class, 5)->state('post')->create();

        $comments = $comments->map(function ($s) {
            unset($s['updated_at']);
            unset($s['created_at']);
            return $s;
        })->toArray();

        $listIds = array_column($comments, 'id');
        array_multisort($listIds, SORT_DESC, $comments);

        $response = $this->call('GET', 'api/comments?search='.$comments[0]['name']);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [],
            'meta' => [
                'pagination' => [
                    'total', 'count', 'per_page', 'current_page', 'total_pages', 'links' => [],
                ],
            ],
        ]);
        $response->assertJson([
            'data' => [$comments[0]]
        ]);
    }

    /** @test */
    public function can_get_paginated_comments_with_order_by()
    {
        $comments = factory(comment::class, 5)->state('post')->create();

        $comments = $comments->map(function ($s) {
            unset($s['updated_at']);
            unset($s['created_at']);
            return $s;
        })->toArray();

        $order_by = '{"name":"desc"}';


        $listName = array_column($comments, 'name');
        array_multisort($listName, SORT_DESC, $comments);

        $response = $this->call('GET', 'api/comments?order_by='.$order_by);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [],
            'meta' => [
                'pagination' => [
                    'total', 'count', 'per_page', 'current_page', 'total_pages', 'links' => [],
                ],
            ],
        ]);

        $response->assertJson([
            'data' => $comments,
        ]);
    }

    /** @test */
    public function can_get_comment() {
        $comment = factory(Comment::class)->state('post')->create()->toArray();

        unset($comment['updated_at']);
        unset($comment['created_at']);

        $response = $this->call('GET', 'api/comments/'.$comment['id']);

        $response->assertStatus(200);
        $response->assertJson([
            'data' => $comment
        ]);
    }

    /** @test */
    public function should_not_get_undefined_comment() {
        // $comment = factory(Comment::class)->state('post')->create()->toArray();

        // unset($comment['updated_at']);
        // unset($comment['created_at']);

        $response = $this->call('GET', 'api/comments/2');

        $response->assertStatus(400);
        $response->assertJson([
            'message' => "Comment not found"
        ]);
    }

    /** @test */
    public function can_create_comment() {
        $data = factory(Comment::class)->state('post')->make()->toArray();

        $response = $this->json('POST', 'api/comments', $data);

        $response->assertStatus(200);
        
        $response->assertJson([
            'data' => $data
        ]);
    }

    /** @test */
    public function should_not_create_comment_with_invalid_email() {
        $data = factory(Comment::class)->state('post')->make()->toArray();
        $data['email'] = "Invalid email data"; 
        $response = $this->json('POST', 'api/comments', $data);

        $response->assertStatus(422);
        
        $response->assertJson([
            "message" => "The given data was invalid."
        ]);
    }

    /** @test */
    public function should_not_create_comment_with_invalid_name() {
        $data = factory(Comment::class)->state('post')->make()->toArray();
        $data['name'] = "The name may only contain letters, numbers, dashes and underscores."; 
        $response = $this->json('POST', 'api/comments', $data);

        $response->assertStatus(422);
        
        $response->assertJson([
            "message" => "The given data was invalid."
        ]);
    }

    /** @test */
    public function should_not_create_comment_with_empty_content() {
        $data = factory(Comment::class)->state('post')->make()->toArray();
        $data['content'] = ""; 
        $response = $this->json('POST', 'api/comments', $data);

        $response->assertStatus(422);
        
        $response->assertJson([
            "message" => "The given data was invalid."
        ]);
    }
}