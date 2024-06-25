<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Nette\Schema\Elements\Structure;
use Tests\TestCase;
use App\Models\Post;

class PostTest extends TestCase
{
    public function test_ListPosts()
    {
        $responseStructure = [
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
        $response->assertJsonStructure($responseStructure);
    }

    public function test_GetPost()
    {
        $responseStructure = [
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
        $response->assertJsonStructure($responseStructure);
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

    public function test_DislikePost()
    {
        $responseStructure = ['msg'];
        $responseData = ['msg' => 'Post Disliked'];
        $requestData = [
            'userId' => 1,
        ];

        $response = $this->post('/api/post/like/1', $requestData);
        $response->assertStatus(200);
        $response->assertJsonStructure($responseStructure);
        $response->assertJsonFragment($responseData);
        $this->assertEquals([], Post::findOrFail(1)->likes);
    }

    public function test_LikePost()
    {
        $responseStructure = ['msg'];
        $responseData = ['msg' => 'Post Liked'];
        $requestData = [
            'userId' => 1,
        ];
        $postData = [
            'id' => 1,
            'likes' => [1],
        ];

        $response = $this->post('/api/post/like/1', $requestData);
        $response->assertStatus(200);
        $response->assertJsonStructure($responseStructure);
        $response->assertJsonFragment($responseData);
        $this->assertEquals([1], Post::findOrFail(1)->likes);
    }

    public function test_LikeNonExistantPost()
    {
        $responseStructure = ['error'];
        $responseData = [
            'error' => "Culdn't find the post you're trying to like"
        ];
        $requestData = [
            'userId' => 1
        ];

        $response = $this->post('/api/post/like/999999999', $requestData);
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

        $response = $this->post('/api/post/like/1', $requestData);
        $response->assertStatus(400);
        $response->assertJsonStructure($responseStructure);
        $response->assertJsonFragment($responseData);
    }

    public function test_ListComments()
    {
        $responseStructure = [
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

        $response = $this->get('/api/post/1/comments');
        $response->assertStatus(200);
        $response->assertJsonStructure($responseStructure);
    }

    public function test_ListCommentsFromNonExistantPost()
    {
        $responseStructure = ['error'];
        $responseData = ['error' => "Culdn't find the post"];

        $response = $this->get('/api/post/999999999/comments');
        $response->assertStatus(404);
        $response->assertJsonStructure($responseStructure);
        $response->assertJsonFragment($responseData);
    }

    public function test_Comment()
    {
        $responseStructure = ['msg'];
        $responseData = ['msg' => 'Post commented'];
        $requestData = [
            'content' => 'Content of the Comment',
            'author' => '1',
        ];

        $response = $this->post('/api/post/comment/1', $requestData);
        $response->assertStatus(201);
        $response->assertJsonStructure($responseStructure);
        $response->assertJsonFragment($responseData);
        $this->assertDatabaseHas('posts', $requestData);
    }

    public function test_CreateCommentBadRequest()
    {
        $responseStructure = ['error'];
        $responseData = [
            'error' => 'There are required params incomplete on the request'
        ];
        $requestData = [
            'content' => 'Content of the Comment Without an Author',
            'author' => '',
        ];

        $response = $this->post('/api/post/comment/1', $requestData);
        $response->assertStatus(400);
        $response->assertJsonStructure($responseStructure);
        $response->assertJsonFragment($responseData);
        $this->assertDatabaseMissing('posts', $requestData);
    }

    public function test_CreateCommentOnNonExistantPost()
    {
        $responseStructure = ['error'];
        $responseData = [
            'error' => "Culdn't find the post you're trying to comment"
        ];
        $requestData = [
            'content' => 'Content of the Comment on a non existant post',
            'author' => '1',
        ];

        $response = $this->post('/api/post/comment/99999999', $requestData);
        $response->assertStatus(404);
        $response->assertJsonStructure($responseStructure);
        $response->assertJsonFragment($responseData);
        $this->assertDatabaseMissing('posts', $requestData);
    }

    public function test_DeleteComment()
    {
        $responseStructure = ['msg'];
        $responseData = ['msg' => 'Comment deleted'];

        $response = $this->get('/api/post/delete/comment/10');
        $response->assertStatus(200);
        $response->assertJsonStructure($responseStructure);
        $response->assertJsonFragment($responseData);
        $this->assertSoftDeleted('comments', ['id' => 10]);
    }

    public function test_DeleteNonExistantComment()
    {
        $responseStructure = ['error'];
        $responseData = [
            'error' => "Culdn't find the comment you're trying to delete"
        ];

        $response = $this->get('/api/post/delete/comment/99999999999');
        $response->assertStatus(404);
        $response->assertJsonStructure($responseStructure);
        $response->assertJsonFragment($responseData);
        $this->assertDatabaseMissing('comments', ['id' => 99999999999]);
    }
}
