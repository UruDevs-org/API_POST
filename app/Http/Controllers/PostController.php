<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Post;

class PostController extends Controller
{
    function List(Request $request){
        $page = $request->has("page") ? $request->get("page") : 1;
        $limit = 20;
        $posts = Post::skip(($page - 1) * 20)->take($limit)->get();
        return response() -> json($posts);
    }

    function Show(Request $request, $id){
        $post = Post::findOrFail($id);
        return response() -> json($post);
    }

    function Create(Request $request){
        $post = new Post();
        $post -> content = $request -> post("content");
        $post -> author = $request -> post("author");
        if($request -> post("attachments"))
            $post -> attachments = $request -> post("attachments");
        $post -> save();
        return response() -> json(["msg" => "Post created"]);
    }

    function Delete(Request $request, $id){
        $post = Post::findOrFail($id);
        $post -> delete();
        return response() -> json(["msg" => "Post deleted"]);
    }
}
