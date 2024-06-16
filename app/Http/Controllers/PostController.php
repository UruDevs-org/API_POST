<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\Comment;

class PostController extends Controller
{
    function List(Request $request) {
        $page = $request -> has("page") ? $request -> get("page") : 1;
        $limit = 20;
        $posts = Post::where("is_comment", false) -> skip(($page - 1) * 20) -> take($limit) -> get();
        return response() -> json($posts);
    }

    function ListComments(Request $request, $id) {
        $page = $request -> has("page") ? $request -> get("page") : 1;
        $limit = 20;
        $comments = Comment::where("replies_to", $id) -> skip(($page -1) * 20) -> take($limit) -> pluck("post") -> toArray();
        $posts = Post::whereIn("id", $comments) -> get();
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
        try {
            $post = Post::findOrFail($id);
            if($post -> comments) {
                $comments = $post::pluck("comments") -> toArray();
                foreach ($comments as $comment) {
                    $this -> DeleteComment($request, $comment);
                }
            }
            $post -> delete();
            return response() -> json(["msg" => "Post deleted"]);
        } catch (\Throwable $th) {
            return response() -> json(["msg" => $th]);
        }
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
            $postId = $this -> Insert($request);
            $comment -> post = $postId;
            $comment -> replies_to = $id;
            $comment -> save();
            $post -> comments
                ? $comments = $post -> comments
                : $comments = [];
            array_push($comments, $comment -> id);
            $post -> comments = $comments;
            $post -> save();
            return response() -> json(["msg" => "Post commented"]);
        }
    }

    function DeleteComment(Request $request, $id) {
        $comment = Comment::findOrFail($id);
        $repliesTo = $comment -> replies_to;
        $post = Post::findOrFail($repliesTo);
        $comments = $post -> comments;
        $comments = array_values(array_filter($comments, function($var) use ($comment) {
            if ($var !== $comment -> id) return $var;
        }));
        $post -> comments = $comments;
        $post -> save();
        $this -> Delete($request, $comment -> post);
        $comment -> delete();
        return response() -> json(["msg" => "Comment deleted"]);
    }
}
