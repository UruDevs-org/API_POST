<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Models\Post;
use App\Models\Comment;

class PostController extends Controller
{
    function List(Request $request)
    {
        $page = $request->has("page") && $request->post("page")
            ? $request->get("page")
            : 1;
        $limit = 20;
        $posts = Post::where("is_comment", false)
            ->where("is_event", false)
            ->skip(($page - 1) * 20)
            ->take($limit)
            ->get();
        return response()->json($posts);
    }

    function Show(Request $request, $id)
    {
        try {
            $post = Post::findOrFail($id);
            return response()->json($post);
        } catch (ModelNotFoundException $e) {
            return response()->json(["error" => "Culdn't find the post"], 404);
        }
    }

    function Create(Request $request)
    {
        if (
            $request->has("content") && $request->post("content") &&
            $request->has("author") && $request->post("author")
        ) {
            $this->InsertPost($request);
            return response()->json(["msg" => "Post created"], 201);
        }
        return response()->json([
            "error" => "There are required params incomplete on the request"
        ], 400);
    }

    function InsertPost($request)
    {
        $post = new Post();
        $post->content = $request->post("content");
        $post->author = $request->post("author");
        if ($request->has("attachments") && $request->post("attachments"))
            $post->attachments = $request->post("attachments");
        if ($request->has("is_comment"))
            $post->is_comment = $request->post("is_comment");
        if ($request->has("is_event"))
            $post->is_event = $request->post("is_event");
        if (
            $request->has("published_in_group") &&
            $request->post("published_in_group")
        )
            $post->published_in_group = $request->post("published_in_group");
        $post->save();
        return $post->id;
    }

    function Update(Request $request, $id)
    {
        try {
            $post = Post::findOrFail($id);
            if ($request->has("content") && $request->post("content")) {
                $post->content = $request->post("content");
                if ($request->has("attachments") && $request->post("attachments"))
                    $post->attachments = $request->post("attachments");
                $post->save();
                return response()->json(["msg" => "Post updated"]);
            }
            return response()->json([
                "error" => "There are required params incomplete on the request"
            ], 400);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                "error" => "Culdn't find the post you're trying to update"
            ], 404);
        }
    }

    function Delete(Request $request, $id)
    {
        try {
            $post = Post::findOrFail($id);
            if ($post->comments) {
                $comments = $post::pluck("comments")->toArray();
                foreach ($comments as $comment) {
                    $this->DeleteComment($request, $comment);
                }
            }
            $post->delete();
            return response()->json(["msg" => "Post deleted"]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                "error" => "Culdn't find the post you're trying to delete"
            ], 404);
        }
    }

    function Like(Request $request, $id)
    {
        try {
            if ($request->has("userId") && $request->post("userId")) {
                $userId = $request->post("userId");
                $post = Post::findOrFail($id);
                $post->likes
                    ? $likes = $post->likes
                    : $likes = [];
                $likes = $this->ToggleLike($userId, $likes);
                $response = $this->LikeState($userId, $likes);
                $post->likes = $likes;
                $post->save();
                return response()->json(["msg" => $response]);
            }
            return response()->json([
                "error" => "There are required params incomplete on the request"
            ], 400);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                "error" => "Culdn't find the post you're trying to like"
            ], 404);
        }
    }

    function ToggleLike($userId, $likes)
    {
        in_array($userId, $likes)
            ? $likes = array_values(
                array_filter($likes, function ($var) use ($userId) {
                    if ($var !== $userId) return $var;
                })
            )
            : array_push($likes, $userId);
        return $likes;
    }

    function LikeState($userId, $likes)
    {
        in_array($userId, $likes)
            ? $response = "Post Liked"
            : $response = "Post Disliked";
        return $response;
    }

    function ListComments(Request $request, $id)
    {
        try {
            Post::findOrFail($id);
            $page = $request->has("page") && $request->post("page")
                ? $request->get("page")
                : 1;
            $limit = 20;
            $comments = Comment::where("replies_to", $id)
                ->skip(($page - 1) * 20)
                ->take($limit)
                ->pluck("post")
                ->toArray();
            $posts = Post::whereIn("id", $comments)->get();
            return response()->json($posts);
        } catch (ModelNotFoundException $e) {
            return response()->json(["error" => "Culdn't find the post"], 404);
        }
    }

    function Comment(Request $request, $id)
    {
        try {
            $post = Post::findOrFail($id);
            if (
                $request->has("content") && $request->post("content") &&
                $request->has("author") && $request->post("author")
            ) {
                $request->request->add(["is_comment" => true]);
                $postId = $this->InsertPost($request);
                $commentId = $this->InsertComment($postId, $id);
                $this->AddCommentToThePostArray($post, $commentId);
                return response()->json(["msg" => "Post commented"], 201);
            }
            return response()->json([
                "error" => "There are required params incomplete on the request"
            ], 400);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                "error" => "Culdn't find the post you're trying to comment"
            ], 404);
        }
    }

    function InsertComment($post, $repliesTo)
    {
        $comment = new Comment();
        $comment->post = $post;
        $comment->replies_to = $repliesTo;
        $comment->save();
        return $comment->id;
    }

    function AddCommentToThePostArray($post, $commentId)
    {
        $post->comments
            ? $comments = $post->comments
            : $comments = [];
        array_push($comments, $commentId);
        $post->comments = $comments;
        $post->save();
    }

    function DeleteComment(Request $request, $id)
    {
        try {
            $comment = Comment::findOrFail($id);
            $this->RemoveCommentFromThePostArray($comment);
            $this->Delete($request, $comment->post);
            $comment->delete();
            return response()->json(["msg" => "Comment deleted"]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                "error" => "Culdn't find the comment you're trying to delete"
            ], 404);
        }
    }

    function RemoveCommentFromThePostArray($comment)
    {
        $post = Post::findOrFail($comment->replies_to);
        $comments = $post->comments;
        $comments = array_values(
            array_filter($comments, function ($var) use ($comment) {
                if ($var !== $comment->id) return $var;
            })
        );
        $post->comments = $comments;
        $post->save();
    }
}
