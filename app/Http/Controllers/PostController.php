<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Post;

class PostController extends Controller
{
    function List(Request $request){
        $posts = Post::all();
        return response() -> json($posts);
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
}
