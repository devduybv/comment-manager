<?php

namespace VCComponent\Laravel\Comment\Test\Features\Web;

use Illuminate\Foundation\Testing\RefreshDatabase;
use VCComponent\Laravel\Comment\Entities\Comment;
use VCComponent\Laravel\Comment\Test\TestCase;

class CommentControllerTest extends TestCase {

    use RefreshDatabase;

    /** @test */
    public function can_comment_on_website() {
        $comment = factory(Comment::class)->state('post')->make()->toArray();
        unset($comment['updated_at']);
        unset($comment['created_at']);
        
        $comment['user'] = $comment['name'];
        unset($comment['name']);

        $response = $this->from('post')->post('comment', $comment);

        $response->assertStatus(302);
        $response->assertRedirect('post');

        $comment['name'] = $comment['user'];
        unset($comment['user']);

        $this->assertDatabaseHas('comments', $comment);
    }

    /** @test */
    public function can_comment_with_any_name() {
        $comment = factory(Comment::class)->state('post')->make()->toArray();
        unset($comment['updated_at']);
        unset($comment['created_at']);
        
        $comment['user'] = 'name hahaha !@#$%&#($*((%__-::""[].,<>?/{}{}{';
        unset($comment['name']);

        $response = $this->from('post')->post('comment', $comment);

        $response->assertStatus(302);
        $response->assertRedirect('post');

        $comment['name'] = $comment['user'];
        unset($comment['user']);

        $this->assertDatabaseHas('comments', $comment);
        $this->assertDatabaseCount('comment_counts', 1);
    }

    /** @test */
    public function should_not_comment_without_content() {
        $comment = factory(Comment::class)->state('post')->make()->toArray();
        unset($comment['updated_at']);
        unset($comment['created_at']);
        $comment['user'] = $comment['name'];
        unset($comment['name']);
        $comment['name'] = 'name hahaha !@#$%&#($*((%__-::""[].,<>?/{}{}{';
        $response = $this->from('post')->post('comment', $comment);

        $response->assertStatus(302);
        $response->assertRedirect('post');
    }
}