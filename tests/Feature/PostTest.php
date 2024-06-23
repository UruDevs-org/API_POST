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
        $requestData = [
            'content' => 'Content of the Post',
            'author' => '1',
        ];

        $response = $this->post('/api/post', $requestData);
        $response->assertStatus(201);
        $response->assertJsonStructure($responseStructure);
        $response->assertJsonFragment($responseData);
        $this->assertDatabaseHas('posts', $requestData);
    }

    public function test_CreatePostBadRequest()
    {
        $responseStructure = ['error'];
        $responseData = [
            'error' => 'There are required params incomplete on the request'
        ];
        $requestData = [
            'content' => 'Content of the Post Without an Author',
            'author' => '',
        ];

        $response = $this->post('/api/post', $requestData);
        $response->assertStatus(400);
        $response->assertJsonStructure($responseStructure);
        $response->assertJsonFragment($responseData);
        $this->assertDatabaseMissing('posts', $requestData);
    }

    public function test_UpdatePost()
    {
        $responseStructure = ['msg'];
        $responseData = ['msg' => 'Post updated'];
        $requestData = [
            'id' => '1',
            'content' => 'Updated Content of the Post'
        ];

        $response = $this->post('/api/post/update/1', $requestData);
        $response->assertStatus(200);
        $response->assertJsonStructure($responseStructure);
        $response->assertJsonFragment($responseData);
        $this->assertDatabaseHas('posts', $requestData);
    }

    public function test_UpdateNonExistantPost()
    {
        $responseStructure = ['error'];
        $responseData = [
            'error' => "Culdn't find the post you're trying to update"
        ];
        $requestData = [
            'id' => '999999999',
            'content' => 'Updated Content of the Post'
        ];

        $response = $this->post('/api/post/update/999999999', $requestData);
        $response->assertStatus(404);
        $response->assertJsonStructure($responseStructure);
        $response->assertJsonFragment($responseData);
        $this->assertDatabaseMissing('posts', $requestData);
    }

    public function test_UpdatePostBadRequest()
    {
        $responseStructure = ['error'];
        $responseData = [
            'error' => 'There are required params incomplete on the request'
        ];
        $requestData = [
            'id' => '1',
            'content' => ''
        ];

        $response = $this->post('/api/post/update/1', $requestData);
        $response->assertStatus(400);
        $response->assertJsonStructure($responseStructure);
        $response->assertJsonFragment($responseData);
        $this->assertDatabaseMissing('posts', $requestData);
    }

    public function test_DeletePost()
    {
        $responseStructure = ['msg'];
        $responseData = ['msg' => 'Post deleted'];

        $response = $this->get('/api/post/delete/15');
        $response->assertStatus(200);
        $response->assertJsonStructure($responseStructure);
        $response->assertJsonFragment($responseData);
        $this->assertSoftDeleted('posts', ['id' => 15]);
    }

    public function test_DeleteNonExistantPost()
    {
        $responseStructure = ['error'];
        $responseData = [
            'error' => "Culdn't find the post you're trying to delete"
        ];

        $response = $this->get('/api/post/delete/99999999999');
        $response->assertStatus(404);
        $response->assertJsonStructure($responseStructure);
        $response->assertJsonFragment($responseData);
        $this->assertDatabaseMissing('posts', ['id' => 99999999999]);
    }

    public function test_LikePost()
    {
        $responseStructure = ['msg'];
        $responseData = ['msg' => 'Post Liked'];
        $requestData = [
            'userId' => '1',
        ];
        $postData = [
            'id' => '1',
            'likes' => [1]
        ];

        $response = $this->post('/api/post/update/1', $requestData);
        $response->assertStatus(200);
        $response->assertJsonStructure($responseStructure);
        $response->assertJsonFragment($responseData);
        $this->assertDatabaseHas('posts', $postData);
    }

    public function test_LikeNonExistantPost()
    {
        $responseStructure = ['error'];
        $responseData = [
            'error' => "Culdn't find the post you're trying to update"
        ];
        $requestData = [
            'userId' => '1'
        ];

        $response = $this->post('/api/post/update/999999999', $requestData);
        $response->assertStatus(404);
        $response->assertJsonStructure($responseStructure);
        $response->assertJsonFragment($responseData);
        $this->assertDatabaseMissing('posts', ['id' => 999999999]);
    }

    public function test_LikePostBadRequest()
    {
        $responseStructure = ['error'];
        $responseData = [
            'error' => 'There are required params incomplete on the request'
        ];
        $requestData = [
            'userId' => '',
        ];

        $response = $this->post('/api/post/update/1', $requestData);
        $response->assertStatus(400);
        $response->assertJsonStructure($responseStructure);
        $response->assertJsonFragment($responseData);
    }
}
