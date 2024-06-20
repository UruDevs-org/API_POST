<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Models\Post;
use App\Models\Comment;

class PostController extends Controller
{
    function List(Request $request) {
        $page = $request -> has("page") ? $request -> get("page") : 1;
        $limit = 20;
        $posts = Post::where("is_comment", false)
            -> where("is_event", false)
            -> skip(($page - 1) * 20)
            -> take($limit)
            -> get();
        return response() -> json($posts);
    }

    function ListComments(Request $request, $id) {
        $page = $request -> has("page") ? $request -> get("page") : 1;
        $limit = 20;
        $comments = Comment::where("replies_to", $id)
            -> skip(($page -1) * 20)
            -> take($limit)
            -> pluck("post")
            -> toArray();
        $posts = Post::whereIn("id", $comments) -> get();
        return response() -> json($posts);
    }

    function Show(Request $request, $id) {
        try{
        $post = Post::findOrFail($id);
        return response() -> json($post);
        } catch (ModelNotFoundException $e) {
            throw $e;
            return response() -> json(["msg" => "El post que intenta encontrar no existe"]);
        }
    }

    function Create(Request $request) {
        if($request -> has("content") && $request -> has("author")){
            $this -> InsertPost($request);
            return response() -> json(["msg" => "Post created"]);
        }
        return response() -> json(["msg" => "Los campos requeridos se encuentran vacíos"]);
    }

    function InsertPost($request) {
        $post = new Post();
        $post -> content = $request -> post("content");
        $post -> author = $request -> post("author");
        if($request -> has("attachments"))
            $post -> attachments = $request -> post("attachments");
        if($request -> has("is_comment"))
            $post -> is_comment = $request -> post("is_comment");
        if($request -> has("is_event"))
            $post -> is_event = $request -> post("is_event");
        if($request -> has("published_in_group"))
            $post -> published_in_group = $request -> post("published_in_group");
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
        } catch (ModelNotFoundException $e) {
            throw $e;
            return response() -> json(["msg" => "El post que intenta eliminar no existe"]);
        }
    }

    function Update(Request $request, $id) {
        try {
            $post = Post::findOrFail($id);
            if($request -> has("content")) {
                $post -> content = $request -> post("content");
                if($request -> has("attachments"))
                    $post -> attachments = $request -> post("attachments");
                $post -> save();
                return response() -> json(["msg" => "Post updated"]);
            }
            return response() -> json(["msg" => "La request se encuentra incompleta"]);
        } catch (ModelNotFoundException $e) {
            throw $e;
            return response() -> json(["msg" => "El post que intenta modificar no existe"]);
        }
    }

    function Comment(Request $request, $id) {
        try {
            $post = Post::findOrFail($id);
            if ($request -> has("content") && $request -> has("author") && $request -> has("is_comment")) {
                $postId = $this -> InsertPost($request);
                $commentId = $this -> InsertComment($postId, $id);
                $this -> AddCommentToThePostArray($post, $commentId);
                return response() -> json(["msg" => "Post commented"]);
            }
            return response() -> json(["msg" => "Hay datos vacíos en la request"]);
        } catch (ModelNotFoundException $e) {
            throw $e;
            return response() -> json(["msg" => "El post que intenta comentar no existe"]);
        }
    }

    function InsertComment($post, $repliesTo) {
        $comment = new Comment();
        $comment -> post = $post;
        $comment -> replies_to = $repliesTo;
        $comment -> save();
        return $comment -> id;
    }

    function AddCommentToThePostArray($post, $commentId) {
        $post -> comments
                ? $comments = $post -> comments
                : $comments = [];
        array_push($comments, $commentId);
        $post -> comments = $comments;
        $post -> save();
    }

    function DeleteComment(Request $request, $id) {
        try{
            $comment = Comment::findOrFail($id);
            $this -> RemoveCommentFromThePostArray($comment);
            $this -> Delete($request, $comment -> post);
            $comment -> delete();
            return response() -> json(["msg" => "Comment deleted"]);
        } catch (ModelNotFoundException $e) {
            throw $e;
            return response() -> json(["msg" => "El comentario que intenta eliminar no existe"]);
        }
    }

    function RemoveCommentFromThePostArray($comment) {
        $post = Post::findOrFail($comment -> replies_to);
        $comments = $post -> comments;
        $comments = array_values(
            array_filter($comments, function($var) use ($comment) {
                if ($var !== $comment -> id) return $var;
        }));
        $post -> comments = $comments;
        $post -> save();
    }

    function Like(Request $request, $id) {
        try {
            $userId = $request -> post("userId");
            $post = Post::findOrFail($id);
            $post -> likes
                ? $likes = $post -> likes
                : $likes = [];
            in_array($userId, $likes)
                ? $likes = array_values(
                    array_filter($likes, function($var) use ($userId) {
                        if ($var !== $userId) return $var;
                    })
                )
                : array_push($likes, $userId);
            $post -> likes = $likes;
            $post -> save();
        } catch (ModelNotFoundException $e) {
            throw $e;
            return response() -> json(["msg" => "El post que intenta likear no existe"]);
        }
    }
}
