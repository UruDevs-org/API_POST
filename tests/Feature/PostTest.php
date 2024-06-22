<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostTest extends TestCase
{
    public function test_ListPosts()
    {
        $postStructure = [
            '*' => [
                'id',
                'content',
                'author',
                'attachments',
                'comments',
                'likes',
                'is_event',
                'is_comment',
                'reports',
                'reports',
                'published_in_group',
                'created_at',
                'updated_at',
                'deleted_at',
            ]
        ];

        $response = $this->get('/api/post');
        $response->assertStatus(200);
        $response->assertJsonStructure($postStructure);
    }

    public function test_GetPost()
    {
        $postStructure = [
            'id',
            'content',
            'author',
            'attachments',
            'comments',
            'likes',
            'is_event',
            'is_comment',
            'reports',
            'reports',
            'published_in_group',
            'created_at',
            'updated_at',
            'deleted_at',
        ];

        $response = $this->get('/api/post/1');
        $response->assertStatus(200);
        $response->assertJsonStructure($postStructure);
    }

    public function test_GetNonExistantPost()
    {
        $responseStructure = ['error'];
        $responseData = ['error' => "Culdn't find the post"];

        $response = $this->get('/api/post/999999999999');
        $response->assertStatus(404);
        $response->assertJsonStructure($responseStructure);
        $response->assertJsonFragment($responseData);
    }

    public function test_CreatePost()
    {
        $responseStructure = ['msg'];
        $responseData = ['msg' => 'Post created'];
        $postData = [
            'content' => 'Content of the Post',
            'author' => '1',
        ];

        $response = $this->post('/api/post', $postData);
        $response->assertStatus(201);
        $response->assertJsonStructure($responseStructure);
        $response->assertJsonFragment($responseData);
        $this->assertDatabaseHas('posts', $postData);
    }

    public function test_CreatePostFailResponse()
    {
        $responseStructure = ['error'];
        $responseData = ['error' => 'There are required params incomplete on the request'];
        $postData = ['content' => 'Contrent of the Post Without an Author'];

        $response = $this->post('/api/post', $postData);
        $response->assertStatus(400);
        $response->assertJsonStructure($responseStructure);
        $response->assertJsonFragment($responseData);
        $this->assertDatabaseMissing('posts', $postData);
    }

    public function test_() {

    }
}
