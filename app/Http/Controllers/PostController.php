<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\Comment;

class PostController extends Controller
{
    function List(Request $request){
        $page = $request -> has("page") ? $request -> get("page") : 1;
        $limit = 20;
        $posts = Post::skip(($page - 1) * 20) -> take($limit) -> get();
        return response() -> json($posts);
    }

    function Show(Request $request, $id) {
        $post = Post::findOrFail($id);
        return response() -> json($post);
    }

    function Create(Request $request) {
        $this -> Insert($request);
        return response() -> json(["msg" => "Post created"]);
    }

    function Insert($request) {
        $post = new Post();
        $post -> content = $request -> post("content");
        $post -> author = $request -> post("author");
        if($request -> has("attachments"))
            $post -> attachments = $request -> post("attachments");
        if($request -> has("is_comment"))
            $post -> is_comment = $request -> post("is_comment");
        $post -> save();
        return $post -> id;
    }

    function Delete(Request $request, $id) {
        $post = Post::findOrFail($id);
        $post -> delete();
        return response() -> json(["msg" => "Post deleted"]);
    }

    function Update(Request $request, $id) {
        $post = Post::findOrFail($id);
        if($request -> has("content"))
            $post -> content = $request -> post("content");
        if($request -> has("attachments"))
            $post -> attachments = $request -> post("attachments");
        $post -> save();
        return response() -> json(["msg" => "Post updated"]);
    }

    function Comment(Request $request, $id) {
        $post = Post::findOrFail($id);
        if ($post) {
            $comment = new Comment();
            $comment -> $this -> Insert($request);
            $comment -> replies_to = $id;
            return response() -> json(["msg" => "Post commented"]);
        }
    }
}
