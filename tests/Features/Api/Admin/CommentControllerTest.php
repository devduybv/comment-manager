<?php

namespace VCComponent\Laravel\Comment\Test\Features\Api\Admin;

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

        $response = $this->call('GET', 'api/admin/comments');

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

        $response = $this->call('GET', 'api/admin/comments?constraints='.$constraints);

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

        $response = $this->call('GET', 'api/admin/comments?search='.$comments[0]['name']);

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


        $listIds = array_column($comments, 'id');
        array_multisort($listIds, SORT_ASC, $comments);

        $listName = array_column($comments, 'name');
        array_multisort($listName, SORT_DESC, $comments);

        $response = $this->call('GET', 'api/admin/comments?order_by='.$order_by);

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
    public function can_create_comment_by_admin() {
        $data = factory(Comment::class)->state('post')->make()->toArray();

        $response = $this->json('POST', 'api/admin/comments', $data);

        $response->assertStatus(200);
        
        $response->assertJson([
            'data' => $data
        ]);
    }

    /** @test */
    public function should_not_create_comment_by_admin_with_invalid_email() {
        $data = factory(Comment::class)->state('post')->make()->toArray();
        $data['email'] = "Invalid email data"; 
        $response = $this->json('POST', 'api/admin/comments', $data);

        $response->assertStatus(422);
        
        $response->assertJson([
            "message" => "The given data was invalid."
        ]);
    }

    /** @test */
    public function should_not_create_comment_by_admin_with_invalid_name() {
        $data = factory(Comment::class)->state('post')->make()->toArray();
        $data['name'] = "The name may only contain letters, numbers, dashes and underscores."; 
        $response = $this->json('POST', 'api/admin/comments', $data);

        $response->assertStatus(422);
        
        $response->assertJson([
            "message" => "The given data was invalid."
        ]);
    }

    /** @test */
    public function should_not_create_comment_by_admin_with_empty_content() {
        $data = factory(Comment::class)->state('post')->make()->toArray();
        $data['content'] = ""; 
        $response = $this->json('POST', 'api/admin/comments', $data);

        $response->assertStatus(422);
        
        $response->assertJson([
            "message" => "The given data was invalid."
        ]);
    }

    /** @test */
    public function can_update_comment_by_admin() {
        $comment = factory(Comment::class)->state('post')->create()->toArray();

        unset($comment['updated_at']);
        unset($comment['created_at']);

        $updated_comment = $comment;
        $updated_comment['name'] = "updated_comment";
        $updated_comment['email'] = "updated_email@email.com";
        $updated_comment['content'] = "updated content";

        $response = $this->json('PUT', 'api/admin/comments/'.$comment['id'], $updated_comment);

        $response->assertStatus(200);
        $response->assertJson([
            'data' => $updated_comment
        ]);
        
        $this->assertDatabaseHas('comments',$updated_comment);
    }

    /** @test */
    public function should_not_update_comment_with_invalid_email() {
        $comment = factory(Comment::class)->state('post')->create()->toArray();

        unset($comment['updated_at']);
        unset($comment['created_at']);

        $updated_comment = $comment;
        $updated_comment['name'] = "updated_comment";
        $updated_comment['email'] = "invalid email @email.com";
        $updated_comment['content'] = "updated content";

        $response = $this->json('PUT', 'api/admin/comments/'.$comment['id'], $updated_comment);

        $response->assertStatus(422);
        $response->assertJson([
            'message' => 'The given data was invalid.'
        ]);
        
        $this->assertDatabaseHas('comments',$comment);
    }

    /** @test */
    public function should_not_update_comment_with_invalid_name() {
        $comment = factory(Comment::class)->state('post')->create()->toArray();

        unset($comment['updated_at']);
        unset($comment['created_at']);

        $updated_comment = $comment;
        $updated_comment['name'] = "This is invalid name";
        $updated_comment['email'] = "updated_email@email.com";
        $updated_comment['content'] = "updated content";

        $response = $this->json('PUT', 'api/admin/comments/'.$comment['id'], $updated_comment);

        $response->assertStatus(422);
        $response->assertJson([
            'message' => 'The given data was invalid.',
            'errors' => [
                'name' => []
            ]
        ]);
        
        $this->assertDatabaseHas('comments',$comment);
    }

    /** @test */
    public function should_not_update_comment_with_empty_content() {
        $comment = factory(Comment::class)->state('post')->create()->toArray();

        unset($comment['updated_at']);
        unset($comment['created_at']);

        $updated_comment = $comment;
        $updated_comment['name'] = "updated_name";
        $updated_comment['email'] = "updated_email@email.com";
        $updated_comment['content'] = "";

        $response = $this->json('PUT', 'api/admin/comments/'.$comment['id'], $updated_comment);

        $response->assertStatus(422);
        $response->assertJson([
            'message' => 'The given data was invalid.',
            'errors' => [
                'content' => []
            ]
        ]);
        
        $this->assertDatabaseHas('comments',$comment);
    }

    /** @test */
    public function can_get_comment_by_admin() {
        $comment = factory(Comment::class)->state('post')->create()->toArray();

        unset($comment['updated_at']);
        unset($comment['created_at']);

        $response = $this->call('GET', 'api/admin/comments/'.$comment['id']);

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

        $response = $this->call('GET', 'api/admin/comments/2');

        $response->assertStatus(400);
        $response->assertJson([
            'message' => "Comment not found"
        ]);
    }

    /** @test */
    public function can_delete_comment_by_admin() {
        $comment = factory(comment::class)->state('post')->create();

        $comment = $comment->toArray();

        unset($comment['created_at']);
        unset($comment['updated_at']);

        $this->assertDatabaseHas('comments', $comment);

        $response = $this->call('DELETE', 'api/admin/comments/'.$comment['id']);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDeleted('comments', $comment);
    }

    /** @test */
    public function can_update_comment_status_by_admin() {
        $comment = factory(Comment::class)->state('post')->create()->toArray();

        unset($comment['updated_at']);
        unset($comment['created_at']);

        $updated_comment = $comment;
        $updated_comment['status'] = "updated_status";

        $response = $this->json('PUT', 'api/admin/comments/status/'.$comment['id'], $updated_comment);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true
        ]);
        
        $this->assertDatabaseHas('comments',$updated_comment);
    }

    /** @test */
    public function should_not_update_comment_status_without_status() {
        $comment = factory(Comment::class)->state('post')->create()->toArray();

        unset($comment['updated_at']);
        unset($comment['created_at']);

        $updated_comment = $comment;
        $updated_comment['status'] = "";

        $response = $this->json('PUT', 'api/admin/comments/status/'.$comment['id'], $updated_comment);

        $response->assertStatus(422);
        $response->assertJson([
            'message' => 'The given data was invalid.',
            'errors'  => [
                'status' => []
            ]
        ]);
        
        $this->assertDatabaseHas('comments',$comment);
    }

    /** @test */
    public function should_not_update_undefined_comment_status() {
        $comment = factory(Comment::class)->state('post')->create()->toArray();

        unset($comment['updated_at']);
        unset($comment['created_at']);

        $fake_comment_id = -99;

        $updated_comment = $comment;
        $updated_comment['status'] = "updated_status";

        $response = $this->json('PUT', 'api/admin/comments/status/'.$fake_comment_id, $updated_comment);

        $response->assertStatus(400);
        $response->assertJson([
            'message' => 'Comment not found'
        ]);
        $this->assertDatabaseHas('comments',$comment);
    }

    /** @test */
    public function can_bulk_update_comments_status_by_admin() {
        $comments = factory(Comment::class, 10)->state('post')->create();

        $comments = $comments->map(function ($comment){
            unset($comment['updated_at']);
            unset($comment['created_at']);
            return $comment;
        })->toArray();

        $updated_comment['status'] = "updated_status";
        $updated_comment['id']    = [
            $comments[0]['id'],
            $comments[1]['id'],
            $comments[2]['id'],
        ];

        $response = $this->json('PUT', 'api/admin/comments/status/bulk',  $updated_comment);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true
        ]);
        
        $this->assertDatabaseHas('comments',$updated_comment);
    }

    /** @test */
    public function should_not_bulk_update_comments_status_without_statu() {
        $comments = factory(Comment::class, 10)->state('post')->create();

        $comments = $comments->map(function ($comment){
            unset($comment['updated_at']);
            unset($comment['created_at']);
            return $comment;
        })->toArray();

        
        $listIds = array_column($comments, 'id');
        array_multisort($listIds, SORT_DESC, $comments);

        $updated_comment['status'] = "";
        $updated_comment['id']    = [
            $comments[0]['id'],
            $comments[1]['id'],
            $comments[2]['id'],
        ];

        $response = $this->json('PUT', 'api/admin/comments/status/bulk',  $updated_comment);

        $response->assertStatus(422);
        $response->assertJson([
            'message' => "The given data was invalid.",
            'errors'  => [
                'status' => []
            ]
        ]);
        foreach ($comments as $item) {
            $this->assertDatabaseHas('comments',$item);
        }
    }
}